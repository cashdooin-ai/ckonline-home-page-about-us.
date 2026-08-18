<?php
/**
 * TEMPORARY diagnostic-only endpoint - no personal data, just schema/count
 * info. Delete once the fix is confirmed working on the live site.
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/colleges-seed.php';
require_once __DIR__ . '/../includes/streams-schema.php';

$out = [];
try {
    $db = getDB();

    $out['count_total_before'] = (int) $db->query("SELECT COUNT(*) FROM colleges")->fetchColumn();
    $out['count_is_online_1_before'] = (int) $db->query("SELECT COUNT(*) FROM colleges WHERE is_online = 1")->fetchColumn();

    try {
        $slugToId = collegesEnsureSeed($db);
        streamsEnsureSchema($db);
        $out['seeder_threw'] = false;
    } catch (Throwable $e) {
        $slugToId = [];
        $out['seeder_threw'] = $e->getMessage();
    }

    $out['resolved_college_count'] = count($slugToId);
    $out['sample_slug_to_id'] = array_slice($slugToId, 0, 5, true);

    $out['count_is_online_1_after'] = (int) $db->query("SELECT COUNT(*) FROM colleges WHERE is_online = 1")->fetchColumn();

    if ($slugToId) {
        $ignouId = $slugToId['ignou'] ?? null;
        if ($ignouId) {
            $out['sample_row_ignou'] = $db->query("SELECT id,name,is_online,online_mode,is_active,slug FROM colleges WHERE id = $ignouId")->fetch(PDO::FETCH_ASSOC);
        }
        $ph = implode(',', array_fill(0, count($slugToId), '?'));
        $stmt = $db->prepare("SELECT COUNT(*) FROM college_streams WHERE college_id IN ($ph)");
        $stmt->execute(array_values($slugToId));
        $out['college_streams_row_count_for_real_ids'] = (int) $stmt->fetchColumn();
    }

    // Replicate api/colleges.php's default (no-filter) WHERE clause exactly
    $has = array_flip($db->query("SHOW COLUMNS FROM colleges")->fetchAll(PDO::FETCH_COLUMN));
    $where = [];
    if (isset($has['is_active']))  $where[] = "c.is_active = 1";
    if (isset($has['status']))     $where[] = "c.status = 'active'";
    $onlineConds = [];
    if (isset($has['is_online']))    $onlineConds[] = "c.is_online = 1";
    if (isset($has['online_mode']))  $onlineConds[] = "c.online_mode IN ('online','hybrid')";
    if ($onlineConds) $where[] = '(' . implode(' OR ', $onlineConds) . ')';
    $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $out['replicated_default_where'] = $whereStr;
    $out['replicated_default_count'] = (int) $db->query("SELECT COUNT(*) FROM colleges c $whereStr")->fetchColumn();

    echo json_encode(['success' => true] + $out, JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'fatal' => $e->getMessage()] + $out, JSON_PRETTY_PRINT);
}
