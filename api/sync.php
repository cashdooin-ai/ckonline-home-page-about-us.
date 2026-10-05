<?php
/**
 * Sync API — called by admin/partner portals to notify this portal of data changes.
 * Accepts POST with a shared secret key.
 * Currently: clears any opcode/server-side cache for the colleges data.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/../config/db.php';

define('SYNC_SECRET', getenv('SYNC_SECRET') ?: 'ck_sync_2025_secret');

$input  = json_decode(file_get_contents('php://input'), true) ?: [];
$secret = $_POST['secret'] ?? $input['secret'] ?? $_GET['secret'] ?? '';
$event  = $_POST['event']  ?? $input['event']  ?? 'refresh';

if ($secret !== SYNC_SECRET) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$log = [];

try {
    $db = getDB();

    switch ($event) {
        case 'college_updated':
            $collegeId = intval($input['college_id'] ?? 0);
            if ($collegeId) {
                // Touch updated_at so CDN/client caches know to refresh
                $db->prepare("UPDATE colleges SET updated_at = NOW() WHERE id = ?")->execute([$collegeId]);
                $log[] = "Refreshed college $collegeId";
            }
            break;

        case 'college_created':
            $log[] = 'New college detected — live data will reflect on next page load';
            break;

        case 'stream_updated':
            $log[] = 'Stream update acknowledged';
            break;

        case 'lead_assigned':
            // Admin portal assigned a lead — just acknowledge
            $log[] = 'Lead assignment acknowledged';
            break;

        default:
            $log[] = 'Generic sync received';
    }

    echo json_encode([
        'success' => true,
        'event'   => $event,
        'log'     => $log,
        'ts'      => date('Y-m-d H:i:s'),
    ]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
