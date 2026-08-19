<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: colleges.php');
    exit;
}

$id   = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');

if ($name === '') {
    $_SESSION['flash'] = 'Error: College name is required.';
    header('Location: colleges.php' . ($id ? "?id=$id" : ''));
    exit;
}

function csSlugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

try {
    $db = getDB();

    $allCols = $db->query("SHOW COLUMNS FROM colleges")->fetchAll(PDO::FETCH_COLUMN);
    $has = array_flip($allCols);

    // Same field list as colleges.php's edit form, plus checkbox/text/number
    // fields not shown there. Only columns that actually exist get written -
    // this install's colleges table added college_type/institution_type/
    // online_mode/etc. via a seed migration, not the base schema.sql, so
    // hardcoding a fixed column set (as this file used to) silently fails
    // on any install where the shape differs.
    $textFields    = ['description', 'short_name', 'city', 'state', 'accreditation', 'naac_grade', 'website'];
    $numberFields  = ['established_year', 'min_fees', 'max_fees', 'rating'];
    $checkboxFields = ['ugc_approved'];
    $selectFields  = [
        'institution_type' => ['university', 'college'],
        'online_mode'      => ['online', 'distance', 'hybrid'],
    ];
    $typeCol = isset($has['college_type']) ? 'college_type' : (isset($has['type']) ? 'type' : null);
    if ($typeCol !== null) {
        $selectFields[$typeCol] = ['government', 'private', 'deemed', 'autonomous'];
    }

    $cols = ['name' => $name];

    foreach ($textFields as $f) {
        if (isset($has[$f])) $cols[$f] = trim($_POST[$f] ?? '');
    }
    foreach ($numberFields as $f) {
        if (isset($has[$f])) $cols[$f] = ($_POST[$f] ?? '') !== '' ? (float)$_POST[$f] : null;
    }
    foreach ($checkboxFields as $f) {
        if (isset($has[$f])) $cols[$f] = isset($_POST[$f]) ? 1 : 0;
    }
    foreach ($selectFields as $f => $allowed) {
        if (!isset($has[$f])) continue;
        $v = trim($_POST[$f] ?? '');
        $cols[$f] = in_array($v, $allowed, true) ? $v : null;
    }

    if ($id > 0) {
        $setParts = [];
        $params = [];
        foreach ($cols as $col => $val) { $setParts[] = "`$col` = ?"; $params[] = $val; }
        $params[] = $id;
        $stmt = $db->prepare("UPDATE colleges SET " . implode(', ', $setParts) . " WHERE id = ?");
        $stmt->execute($params);
        $_SESSION['flash'] = 'College updated.';
    } else {
        $baseSlug = csSlugify($name);
        $slug = $baseSlug;
        $n = 1;
        $findSlug = $db->prepare("SELECT id FROM colleges WHERE slug = ?");
        while (true) {
            $findSlug->execute([$slug]);
            if (!$findSlug->fetch()) break;
            $slug = $baseSlug . '-' . (++$n);
        }
        $cols['slug'] = $slug;
        if (isset($has['is_active'])) $cols['is_active'] = 1;

        $colNames = array_map(fn($c) => "`$c`", array_keys($cols));
        $placeholders = implode(',', array_fill(0, count($cols), '?'));
        $stmt = $db->prepare("INSERT INTO colleges (" . implode(',', $colNames) . ") VALUES ($placeholders)");
        $stmt->execute(array_values($cols));
        $id = (int)$db->lastInsertId();
        $_SESSION['flash'] = 'College created.';
    }
} catch (Throwable $e) {
    $_SESSION['flash'] = 'Error: ' . $e->getMessage();
}

header('Location: colleges.php?id=' . $id);
