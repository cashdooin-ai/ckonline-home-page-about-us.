<?php
// Admin-only: save AI-suggested (or manually edited) courses for a college.
// Upserts into `courses` (matched by name, case-insensitive) and the
// `college_courses` junction table (matched by college_id + course_id).
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

if (empty($_SESSION['ck_admin_auth'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin login required']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST method required']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$collegeId = (int)($input['college_id'] ?? 0);
$courses   = $input['courses'] ?? [];

if (!$collegeId || !is_array($courses) || empty($courses)) {
    http_response_code(400);
    echo json_encode(['error' => 'college_id and courses[] are required']);
    exit;
}

$allowedCategories = ['engineering','management','medical','law','arts','science','commerce','design','other'];
$allowedLevels      = ['certificate','diploma','ug','pg','phd'];

function ccsSlugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

try {
    $db = getDB();

    $chk = $db->prepare("SELECT id FROM colleges WHERE id = ?");
    $chk->execute([$collegeId]);
    if (!$chk->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'College not found']);
        exit;
    }

    $findCourse   = $db->prepare("SELECT id FROM courses WHERE name = ? LIMIT 1");
    $insertCourse = $db->prepare("INSERT INTO courses (name, slug, category, duration_years, degree_level) VALUES (?,?,?,?,?)");
    $findSlug     = $db->prepare("SELECT id FROM courses WHERE slug = ?");
    $linkCourse   = $db->prepare(
        "INSERT INTO college_courses (college_id, course_id, annual_fees, eligibility)
         VALUES (?,?,?,?)
         ON DUPLICATE KEY UPDATE annual_fees = VALUES(annual_fees), eligibility = VALUES(eligibility), is_active = 1"
    );

    $saved = 0;
    foreach (array_slice($courses, 0, 20) as $c) {
        $name = trim($c['name'] ?? '');
        if ($name === '') continue;

        $category = strtolower(trim($c['category'] ?? 'other'));
        if (!in_array($category, $allowedCategories, true)) $category = 'other';

        $level = strtolower(trim($c['degree_level'] ?? 'ug'));
        if (!in_array($level, $allowedLevels, true)) $level = 'ug';

        $duration = is_numeric($c['duration_years'] ?? null) ? (float)$c['duration_years'] : 4.0;
        $fees        = is_numeric($c['annual_fees'] ?? null) ? (float)$c['annual_fees'] : 0;
        $eligibility = trim($c['eligibility'] ?? '');

        $findCourse->execute([$name]);
        $existing = $findCourse->fetch();

        if ($existing) {
            $courseId = (int)$existing['id'];
        } else {
            $baseSlug = ccsSlugify($name);
            $slug = $baseSlug;
            $n = 1;
            while (true) {
                $findSlug->execute([$slug]);
                if (!$findSlug->fetch()) break;
                $slug = $baseSlug . '-' . (++$n);
            }
            $insertCourse->execute([$name, $slug, $category, $duration, $level]);
            $courseId = (int)$db->lastInsertId();
        }

        $linkCourse->execute([$collegeId, $courseId, $fees, $eligibility]);
        $saved++;
    }

    echo json_encode(['success' => true, 'saved' => $saved]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
