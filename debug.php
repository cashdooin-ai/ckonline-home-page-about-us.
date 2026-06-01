<?php
// Quick DB diagnostic — visit this URL on live server to see exact error
// DELETE this file after debugging!
// URL: online.collegekampus.com/dashboard/debug.php

require_once __DIR__ . '/config/db.php';

echo '<pre style="font-family:monospace;padding:20px;">';
echo "SITE_BASE: " . SITE_BASE . "\n\n";

try {
    $db = getDB();
    echo "✅ DB Connected successfully\n\n";

    // Show all tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found:\n";
    foreach ($tables as $t) echo "  - $t\n";
    echo "\n";

    // Show colleges table columns if it exists
    if (in_array('colleges', $tables)) {
        echo "colleges table columns:\n";
        $cols = $db->query("SHOW COLUMNS FROM colleges")->fetchAll();
        foreach ($cols as $c) echo "  - {$c['Field']} ({$c['Type']})\n";
        echo "\n";
        $count = $db->query("SELECT COUNT(*) FROM colleges")->fetchColumn();
        echo "Total colleges rows: $count\n";
    } else {
        echo "❌ 'colleges' table NOT FOUND\n";
        echo "Your actual table names are listed above.\n";
    }

} catch (Throwable $e) {
    echo "❌ DB Error: " . $e->getMessage() . "\n\n";
    echo "Check config/db.php — DB_HOST, DB_NAME, DB_USER, DB_PASS\n";
}

echo '</pre>';
