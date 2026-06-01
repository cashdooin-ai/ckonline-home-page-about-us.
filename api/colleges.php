<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$search    = trim($_GET['search'] ?? '');
$state     = trim($_GET['state'] ?? '');
$type      = trim($_GET['type'] ?? '');
$category  = trim($_GET['category'] ?? '');
$min_fees  = intval($_GET['min_fees'] ?? 0);
$max_fees  = intval($_GET['max_fees'] ?? 0);
$page      = max(1, intval($_GET['page'] ?? 1));
$limit     = min(24, max(1, intval($_GET['limit'] ?? 12)));
$offset    = ($page - 1) * $limit;

try {
    $db = getDB();

    // Detect available columns gracefully
    $cols = [];
    $stmt = $db->query("SHOW COLUMNS FROM colleges");
    foreach ($stmt->fetchAll() as $row) {
        $cols[] = $row['Field'];
    }
    $hasIsActive   = in_array('is_active', $cols);
    $hasType       = in_array('type', $cols);
    $hasCity       = in_array('city', $cols);
    $hasState      = in_array('state', $cols);
    $hasMinFees    = in_array('min_fees', $cols);
    $hasMaxFees    = in_array('max_fees', $cols);
    $hasEstYear    = in_array('established_year', $cols);
    $hasAccr       = in_array('accreditation', $cols);
    $hasSlug       = in_array('slug', $cols);

    $select  = "c.id, c.name";
    if ($hasCity)    $select .= ", c.city";
    if ($hasState)   $select .= ", c.state";
    if ($hasType)    $select .= ", c.type";
    if ($hasMinFees) $select .= ", c.min_fees";
    if ($hasMaxFees) $select .= ", c.max_fees";
    if ($hasEstYear) $select .= ", c.established_year";
    if ($hasAccr)    $select .= ", c.accreditation";
    if ($hasSlug)    $select .= ", c.slug";

    // Try to get top courses via subquery
    $courseSubquery = '';
    try {
        $db->query("SELECT 1 FROM college_courses LIMIT 1");
        $courseSubquery = ", (SELECT GROUP_CONCAT(cr.name ORDER BY cr.name SEPARATOR ', ') 
                              FROM college_courses cc 
                              JOIN courses cr ON cr.id = cc.course_id 
                              WHERE cc.college_id = c.id LIMIT 3) AS top_courses";
    } catch (Exception $e) {
        // college_courses table may not exist
    }

    $where  = [];
    $params = [];

    if ($hasIsActive) {
        $where[] = "c.is_active = 1";
    }
    if ($search !== '') {
        $w = ["c.name LIKE ?"];
        if ($hasCity)  $w[] = "c.city LIKE ?";
        if ($hasState) $w[] = "c.state LIKE ?";
        $where[] = '(' . implode(' OR ', $w) . ')';
        $like = '%' . $search . '%';
        foreach ($w as $_) $params[] = $like;
    }
    if ($state !== '' && $hasState) {
        $where[] = "c.state = ?";
        $params[] = $state;
    }
    if ($type !== '' && $hasType) {
        $types = array_filter(array_map('trim', explode(',', $type)));
        if ($types) {
            $ph = implode(',', array_fill(0, count($types), '?'));
            $where[] = "c.type IN ($ph)";
            foreach ($types as $t) $params[] = $t;
        }
    }
    if ($min_fees > 0 && $hasMinFees) {
        $where[] = "c.min_fees >= ?";
        $params[] = $min_fees;
    }
    if ($max_fees > 0 && $hasMaxFees) {
        $where[] = "c.max_fees <= ?";
        $params[] = $max_fees;
    }

    $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Count
    $countSql = "SELECT COUNT(*) FROM colleges c $whereStr";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // Data
    $sql = "SELECT $select $courseSubquery FROM colleges c $whereStr ORDER BY c.name LIMIT ? OFFSET ?";
    $dataParams = array_merge($params, [$limit, $offset]);
    $dataStmt = $db->prepare($sql);
    $dataStmt->execute($dataParams);
    $colleges = $dataStmt->fetchAll();

    // Format fees display
    foreach ($colleges as &$col) {
        $min = $col['min_fees'] ?? null;
        $max = $col['max_fees'] ?? null;
        if ($min && $max) {
            $col['fees_display'] = '₹' . number_format($min) . ' – ₹' . number_format($max);
        } elseif ($min) {
            $col['fees_display'] = 'From ₹' . number_format($min);
        } elseif ($max) {
            $col['fees_display'] = 'Upto ₹' . number_format($max);
        } else {
            $col['fees_display'] = null;
        }
    }
    unset($col);

    echo json_encode(['success' => true, 'colleges' => $colleges, 'total_count' => $total, 'page' => $page, 'limit' => $limit]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}
