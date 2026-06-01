<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$pdo = getPDO();

$search   = trim($_GET['search']   ?? '');
$state    = trim($_GET['state']    ?? '');
$type     = trim($_GET['type']     ?? '');
$category = trim($_GET['category'] ?? '');
$featured = isset($_GET['featured']) ? (int)$_GET['featured'] : 0;
$maxFee   = isset($_GET['max_fee']) ? (int)$_GET['max_fee'] : 0;
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;
$offset   = ($page - 1) * $perPage;

$allowedTypes = ['private','government','deemed'];
if ($type && !in_array($type, $allowedTypes)) $type = '';
$allowedCategories = ['engineering','management','medical','law','arts','science','commerce','design','other'];
if ($category && !in_array($category, $allowedCategories)) $category = '';

$where  = ['c.is_active = 1'];
$params = [];

if ($search) {
    $where[]    = '(c.name LIKE :search OR c.city LIKE :search OR c.state LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}
if ($state) {
    $where[]    = 'c.state = :state';
    $params[':state'] = $state;
}
if ($type) {
    $where[]    = 'c.type = :type';
    $params[':type'] = $type;
}
if ($featured) {
    $where[]    = 'c.is_featured = 1';
}
if ($category) {
    $where[]    = 'EXISTS (SELECT 1 FROM college_courses cc2 JOIN courses cr2 ON cc2.course_id = cr2.id WHERE cc2.college_id = c.id AND cr2.category = :category AND cc2.is_active = 1)';
    $params[':category'] = $category;
}
if ($maxFee > 0) {
    $where[]    = 'EXISTS (SELECT 1 FROM college_courses cc3 WHERE cc3.college_id = c.id AND cc3.annual_fees <= :maxfee AND cc3.is_active = 1)';
    $params[':maxfee'] = $maxFee;
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM colleges c $whereSQL");
foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT c.id, c.name, c.slug, c.logo_url, c.city, c.state, c.type,
           c.accreditation, c.ranking_score, c.established_year, c.is_featured,
           MIN(cc.annual_fees) AS min_fees,
           MAX(cc.annual_fees) AS max_fees
    FROM colleges c
    LEFT JOIN college_courses cc ON cc.college_id = c.id AND cc.is_active = 1
    $whereSQL
    GROUP BY c.id
    ORDER BY c.ranking_score DESC, c.name ASC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$colleges = $stmt->fetchAll();

if ($colleges) {
    $ids  = array_column($colleges, 'id');
    $in   = implode(',', array_fill(0, count($ids), '?'));
    $cStmt = $pdo->prepare("
        SELECT cc.college_id, cr.name
        FROM college_courses cc
        JOIN courses cr ON cc.course_id = cr.id
        WHERE cc.college_id IN ($in) AND cc.is_active = 1
        ORDER BY cc.annual_fees DESC
    ");
    $cStmt->execute($ids);
    $courseRows = $cStmt->fetchAll();

    $courseMap = [];
    foreach ($courseRows as $row) {
        $cid = $row['college_id'];
        if (!isset($courseMap[$cid])) $courseMap[$cid] = [];
        if (count($courseMap[$cid]) < 3) $courseMap[$cid][] = $row['name'];
    }
    foreach ($colleges as &$col) {
        $col['top_courses'] = $courseMap[$col['id']] ?? [];
        $col['ranking_score'] = (float)$col['ranking_score'];
        $col['min_fees'] = $col['min_fees'] ? (float)$col['min_fees'] : null;
        $col['max_fees'] = $col['max_fees'] ? (float)$col['max_fees'] : null;
    }
    unset($col);
}

echo json_encode([
    'total'    => $total,
    'page'     => $page,
    'per_page' => $perPage,
    'colleges' => $colleges ?: [],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
