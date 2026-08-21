<?php
// Admin-only: save AI-suggested (or manually edited) courses for a college.
//
// Writes directly into `college_courses` - there is no separate `courses`
// catalog table on this shared DB (an earlier version of this endpoint
// assumed one, matching this repo's own database/schema.sql, but that
// table was never actually created on the production DB and every save
// failed with "table 'courses' doesn't exist"). `college_courses` itself
// is NOT this repo's table either - it's owned by explore.collegekampus.com
// (see dashboard/config/schema.sql in the ckampus-dasboard repo) and
// already holds every regular college's course/fee data, flatly:
// college_id, course_name, stream, duration, degree_type, annual_fees,
// seats_available, eligibility, is_active - no course_id/catalog join.
// Writing into that real shape means this data also surfaces correctly in
// explore's own stream-based browsing, instead of silently going nowhere.
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

try {
    $db = getDB();

    $chk = $db->prepare("SELECT id FROM colleges WHERE id = ?");
    $chk->execute([$collegeId]);
    if (!$chk->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'College not found']);
        exit;
    }

    $findExisting = $db->prepare("SELECT id FROM college_courses WHERE college_id = ? AND course_name = ? LIMIT 1");
    $updateRow    = $db->prepare(
        "UPDATE college_courses SET stream = ?, duration = ?, degree_type = ?, annual_fees = ?, eligibility = ?, is_active = 1 WHERE id = ?"
    );
    $insertRow    = $db->prepare(
        "INSERT INTO college_courses (college_id, course_name, stream, duration, degree_type, annual_fees, eligibility, is_active)
         VALUES (?,?,?,?,?,?,?,1)"
    );

    $saved = 0;
    foreach (array_slice($courses, 0, 20) as $c) {
        $name = trim($c['name'] ?? '');
        if ($name === '') continue;

        $category = strtolower(trim($c['category'] ?? 'other'));
        if (!in_array($category, $allowedCategories, true)) $category = 'other';
        // college_courses.stream is free text elsewhere on this shared DB
        // (admin/courses.php's placeholder: "Stream (Engineering, Medical...)"),
        // so match that Title Case convention rather than storing our lowercase
        // category value verbatim.
        $stream = ucfirst($category);

        $level = strtolower(trim($c['degree_level'] ?? 'ug'));
        if (!in_array($level, $allowedLevels, true)) $level = 'ug';
        $degreeType = strtoupper($level);

        $durationYears = is_numeric($c['duration_years'] ?? null) ? (float)$c['duration_years'] : 4.0;
        $duration      = rtrim(rtrim(number_format($durationYears, 1), '0'), '.') . ' yr';
        $fees          = is_numeric($c['annual_fees'] ?? null) ? (float)$c['annual_fees'] : 0;
        $eligibility   = trim($c['eligibility'] ?? '');

        $findExisting->execute([$collegeId, $name]);
        $existing = $findExisting->fetch();

        if ($existing) {
            $updateRow->execute([$stream, $duration, $degreeType, $fees, $eligibility, $existing['id']]);
        } else {
            $insertRow->execute([$collegeId, $name, $stream, $duration, $degreeType, $fees, $eligibility]);
        }
        $saved++;
    }

    echo json_encode(['success' => true, 'saved' => $saved]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
