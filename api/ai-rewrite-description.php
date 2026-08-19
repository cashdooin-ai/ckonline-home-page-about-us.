<?php
// Admin-only: AI-generated college description draft, using the same
// Claude-direct call pattern as api/ai-generate.php.
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

    $cName = $college['name'];
    $cCity = $college['city'] ?? '';
    $cState = $college['state'] ?? '';
    $cType = $college['type'] ?? '';

    $prompt = "Write a 3-4 paragraph overview of \"{$cName}\" ({$cType} institution) in {$cCity}, {$cState}, India, for students researching online/distance-education degree programmes. Cover: a brief institutional overview, the kind of online/distance programmes it is known for, and why a prospective student might consider it. Write plain text only (no HTML tags, no markdown headings) — this will be inserted as a plain description field. Do not fabricate specific statistics (fee amounts, rankings, placement percentages) you are not confident about; keep those general if unsure.";

    $payload = json_encode([
        'model'      => 'claude-sonnet-4-6',
        'max_tokens' => 800,
        'messages'   => [['role' => 'user', 'content' => $prompt]],
    ]);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 60,
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

    $text = trim($data['content'][0]['text'] ?? '');
    if ($text === '') {
        http_response_code(500);
        echo json_encode(['error' => 'AI returned an empty response. Please try again.']);
        exit;
    }

    echo json_encode(['success' => true, 'description' => $text]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
