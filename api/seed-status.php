<?php
/**
 * TEMPORARY diagnostic-only endpoint - no personal data, just schema/count
 * info, to find out why collegesEnsureSeed() (includes/colleges-seed.php)
 * silently isn't populating the colleges table on production. Delete this
 * file once the root cause is found.
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

$out = [];
try {
    $db = getDB();

    $out['columns'] = $db->query("SHOW COLUMNS FROM colleges")->fetchAll(PDO::FETCH_COLUMN);
    $out['count_10001_10050_before'] = (int) $db->query("SELECT COUNT(*) FROM colleges WHERE id BETWEEN 10001 AND 10050")->fetchColumn();
    $out['count_total_before'] = (int) $db->query("SELECT COUNT(*) FROM colleges")->fetchColumn();

    // Can we ALTER at all?
    try {
        $db->exec("ALTER TABLE colleges ADD COLUMN _seed_probe_col TINYINT(1) NULL");
        $db->exec("ALTER TABLE colleges DROP COLUMN _seed_probe_col");
        $out['can_alter'] = true;
    } catch (Throwable $e) {
        $out['can_alter'] = false;
        $out['alter_error'] = $e->getMessage();
    }

    // Can we insert a probe row with id 999999 (safely outside the real range)?
    try {
        $db->exec("DELETE FROM colleges WHERE id = 999999");
        $has = array_flip($out['columns']);
        $nameCol = isset($has['name']) ? 'name' : null;
        if ($nameCol) {
            $db->exec("INSERT INTO colleges (id, $nameCol) VALUES (999999, 'SEED_PROBE_DELETE_ME')");
            $out['can_insert'] = true;
            $db->exec("DELETE FROM colleges WHERE id = 999999");
        } else {
            $out['can_insert'] = 'no name column found';
        }
    } catch (Throwable $e) {
        $out['can_insert'] = false;
        $out['insert_error'] = $e->getMessage();
    }

    // Now actually run the real seeder and capture what happens
    require_once __DIR__ . '/../includes/colleges-seed.php';
    try {
        collegesEnsureSeed($db);
        $out['seeder_threw'] = false;
    } catch (Throwable $e) {
        $out['seeder_threw'] = $e->getMessage();
    }

    $out['count_10001_10050_after'] = (int) $db->query("SELECT COUNT(*) FROM colleges WHERE id BETWEEN 10001 AND 10050")->fetchColumn();

    // Also probe the streams tables the same way
    try {
        $out['streams_table_exists'] = (bool) $db->query("SHOW TABLES LIKE 'streams'")->fetchColumn();
        $out['college_streams_table_exists'] = (bool) $db->query("SHOW TABLES LIKE 'college_streams'")->fetchColumn();
        if ($out['college_streams_table_exists']) {
            $out['college_streams_row_count'] = (int) $db->query("SELECT COUNT(*) FROM college_streams")->fetchColumn();
        }
    } catch (Throwable $e) {
        $out['streams_probe_error'] = $e->getMessage();
    }

    echo json_encode(['success' => true] + $out, JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'fatal' => $e->getMessage()] + $out, JSON_PRETTY_PRINT);
}
