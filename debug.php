<?php
// TEMPORARY debug file — DELETE after fixing DB connection
// Access: online.collegekampus.com/dashboard/debug.php
// IMPORTANT: Delete this file once DB is working!

header('Content-Type: text/plain');
require_once __DIR__ . '/config/db.php';

echo "=== CollegeKampus DB Debug ===\n\n";
echo "SITE_BASE: " . SITE_BASE . "\n";
echo "DB_HOST: " . DB_HOST . "\n";
echo "DB_NAME: " . DB_NAME . "\n";
echo "DB_USER: " . DB_USER . "\n";
echo "DB_PASS: " . (DB_PASS ? '(set)' : '(empty)') . "\n\n";

try {
    $db = getDB();
    echo "✅ DB Connection: SUCCESS\n\n";

    // Show all tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found (" . count($tables) . "):\n";
    foreach ($tables as $t) echo "  - $t\n";

    echo "\n";

    // If colleges table exists, show its columns
    if (in_array('colleges', $tables)) {
        echo "colleges table columns:\n";
        $cols = $db->query("SHOW COLUMNS FROM colleges")->fetchAll();
        foreach ($cols as $c) echo "  - {$c['Field']} ({$c['Type']})\n";

        $count = $db->query("SELECT COUNT(*) FROM colleges")->fetchColumn();
        echo "\nTotal colleges: $count\n";
    } else {
        echo "❌ 'colleges' table NOT FOUND\n";
        echo "Possible table names with 'college':\n";
        foreach ($tables as $t) {
            if (stripos($t, 'college') !== false) echo "  - $t\n";
        }
    }

} catch (Throwable $e) {
    echo "❌ DB Connection FAILED:\n";
    echo $e->getMessage() . "\n";
}
