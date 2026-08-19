<?php
require_once __DIR__ . '/auth.php';
require_once dirname(__DIR__) . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: colleges.php');
    exit;
}

$id          = (int)($_POST['id'] ?? 0);
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$city        = trim($_POST['city'] ?? '');
$state       = trim($_POST['state'] ?? '');
$type        = trim($_POST['type'] ?? 'private');
$minFees     = ($_POST['min_fees'] ?? '') !== '' ? (float)$_POST['min_fees'] : null;
$maxFees     = ($_POST['max_fees'] ?? '') !== '' ? (float)$_POST['max_fees'] : null;

if (!in_array($type, ['private','government','deemed'], true)) $type = 'private';

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

    if ($id > 0) {
        $stmt = $db->prepare("UPDATE colleges SET name=?, description=?, city=?, state=?, type=?, min_fees=?, max_fees=? WHERE id=?");
        $stmt->execute([$name, $description, $city, $state, $type, $minFees, $maxFees, $id]);
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
        $stmt = $db->prepare("INSERT INTO colleges (name, slug, description, city, state, type, min_fees, max_fees) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$name, $slug, $description, $city, $state, $type, $minFees, $maxFees]);
        $id = (int)$db->lastInsertId();
        $_SESSION['flash'] = 'College created.';
    }
} catch (Throwable $e) {
    $_SESSION['flash'] = 'Error: ' . $e->getMessage();
}

header('Location: colleges.php?id=' . $id);
