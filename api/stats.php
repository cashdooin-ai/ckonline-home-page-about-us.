<?php
/**
 * Stats API — returns live counts for the admin/partner dashboards.
 * Called by admin portal or partner portal via AJAX.
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

try {
    $db = getDB();

    $stats = [];

    // Total online colleges
    try {
        $cols = $db->query("SHOW COLUMNS FROM colleges LIKE 'is_online'")->rowCount();
        if ($cols) {
            $stats['online_colleges'] = (int) $db->query("SELECT COUNT(*) FROM colleges WHERE is_online = 1")->fetchColumn();
        }
        $stats['total_colleges'] = (int) $db->query("SELECT COUNT(*) FROM colleges")->fetchColumn();
    } catch (Throwable $e) {}

    // Total leads
    try {
        $stats['total_leads'] = (int) $db->query("SELECT COUNT(*) FROM leads")->fetchColumn();
        $stats['leads_today'] = (int) $db->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    } catch (Throwable $e) {}

    // Total streams
    try {
        $stats['total_streams'] = (int) $db->query("SELECT COUNT(*) FROM streams WHERE is_active = 1")->fetchColumn();
    } catch (Throwable $e) {}

    // Top 5 colleges by leads
    try {
        $top = $db->query("
            SELECT c.name, c.id, COUNT(l.id) AS lead_count
            FROM leads l
            JOIN colleges c ON c.id = l.college_id
            GROUP BY l.college_id
            ORDER BY lead_count DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);
        $stats['top_colleges_by_leads'] = $top;
    } catch (Throwable $e) {}

    echo json_encode(['success' => true, 'stats' => $stats]);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
