<?php
// Admin-only: AI course/fee suggestions for a college, using the same
// Claude-direct call pattern as api/ai-generate.php. Suggestions are
// returned for admin review — nothing is written to the database here.
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

$collegeId = (int)($_POST['college_id'] ?? 0);
if (!$collegeId) {
    http_response_code(400);
    echo json_encode(['error' => 'college_id is required']);
    exit;
}

$apiKey = getenv('ANTHROPIC_API_KEY') ?: '';
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'ANTHROPIC_API_KEY not set on server. Contact the administrator.']);
    exit;
}

try {
    $db = getDB();

    $stmt = $db->prepare("SELECT name, city, state, type FROM colleges WHERE id = ?");
    $stmt->execute([$collegeId]);
    $college = $stmt->fetch();
    if (!$college) {
        http_response_code(404);
        echo json_encode(['error' => 'College not found']);
        exit;
    }

    $existingNames = [];
    try {
        // college_courses is a flat table (course_name is a plain column,
        // no separate courses catalog - see api/college-courses-save.php).
        $es = $db->prepare("SELECT course_name FROM college_courses WHERE college_id = ? AND is_active = 1");
        $es->execute([$collegeId]);
        $existingNames = array_column($es->fetchAll(), 'course_name');
    } catch (Throwable $e) {}

    $cName = $college['name'];
    $cCity = $college['city'] ?? '';
    $cState = $college['state'] ?? '';
    $cType = $college['type'] ?? '';
    $existingNote = $existingNames ? ('Already listed — do not repeat these: ' . implode(', ', $existingNames) . '.') : '';

    $prompt = <<<PROMPT
List the online / distance-education degree programmes offered by "{$cName}" in {$cCity}, {$cState}, India ({$cType} institution). {$existingNote}

For each course, provide:
- "name": full programme name
- "category": one of engineering, management, medical, law, arts, science, commerce, design, other
- "degree_level": one of certificate, diploma, ug, pg, phd
- "duration_years": a decimal number, e.g. 2 or 3.5
- "annual_fees": a realistic whole-number INR estimate for this specific institution based on your knowledge of it (or, if you are not confident about this exact institution, a reasonable estimate consistent with similar {$cType} institutions offering online/distance programmes in India) — this is an estimate for an admin to review, not a guaranteed figure
- "eligibility": one sentence

Return ONLY a valid JSON array, at most 8 courses, no markdown code fences, no commentary:
[{"name":"...","category":"...","degree_level":"...","duration_years":0,"annual_fees":0,"eligibility":"..."}]
PROMPT;

    $payload = json_encode([
        'model'      => 'claude-sonnet-4-6',
        'max_tokens' => 2048,
        'messages'   => [['role' => 'user', 'content' => $prompt]],
    ]);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_HTTPHEADER     => [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'content-type: application/json',
        ],
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        http_response_code(500);
        echo json_encode(['error' => 'Network error contacting AI: ' . $curlErr]);
        exit;
    }

    $data = json_decode($response, true);
    if ($httpCode !== 200) {
        http_response_code(502);
        echo json_encode(['error' => 'Claude API error (' . $httpCode . '): ' . ($data['error']['message'] ?? $response)]);
        exit;
    }

    $text = $data['content'][0]['text'] ?? '';
    $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
    $text = preg_replace('/\s*```$/', '', $text);
    $suggestions = json_decode(trim($text), true);

    if (!is_array($suggestions)) {
        http_response_code(500);
        echo json_encode(['error' => 'AI returned an unexpected format. Please try again.']);
        exit;
    }

    echo json_encode(['success' => true, 'suggestions' => $suggestions, 'college_name' => $cName]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
