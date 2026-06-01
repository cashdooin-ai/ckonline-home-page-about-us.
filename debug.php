<?php
// TEMPORARY debug file — DELETE after fixing DB connection
header('Content-Type: text/plain');
require_once __DIR__ . '/config/db.php';

echo "=== CollegeKampus DB Debug ===\n\n";

try {
    $db = getDB();
    echo "✅ DB Connection: SUCCESS\n\n";

    // colleges columns
    echo "=== COLLEGES table columns ===\n";
    $cols = $db->query("SHOW COLUMNS FROM colleges")->fetchAll();
    foreach ($cols as $c) echo "  {$c['Field']}  ({$c['Type']})\n";

    // Sample row
    echo "\n=== Sample college row ===\n";
    $row = $db->query("SELECT * FROM colleges LIMIT 1")->fetch();
    if ($row) {
        foreach ($row as $k => $v) echo "  $k: " . substr((string)$v, 0, 80) . "\n";
    }

    // college_courses columns
    echo "\n=== COLLEGE_COURSES table columns ===\n";
    $cols2 = $db->query("SHOW COLUMNS FROM college_courses")->fetchAll();
    foreach ($cols2 as $c) echo "  {$c['Field']}  ({$c['Type']})\n";

    // leads columns
    echo "\n=== LEADS table columns ===\n";
    $cols3 = $db->query("SHOW COLUMNS FROM leads")->fetchAll();
    foreach ($cols3 as $c) echo "  {$c['Field']}  ({$c['Type']})\n";

    // college_streams columns
    echo "\n=== COLLEGE_STREAMS table columns ===\n";
    $cols4 = $db->query("SHOW COLUMNS FROM college_streams")->fetchAll();
    foreach ($cols4 as $c) echo "  {$c['Field']}  ({$c['Type']})\n";

    // college counts
    echo "\n=== Row counts ===\n";
    foreach (['colleges','college_courses','leads','college_streams','college_fee_details'] as $t) {
        try {
            $n = $db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
            echo "  $t: $n rows\n";
        } catch (Throwable $e) { echo "  $t: ERROR\n"; }
    }

} catch (Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
