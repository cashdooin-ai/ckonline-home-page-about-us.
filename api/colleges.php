<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$search      = trim($_GET['search']    ?? '');
$state       = trim($_GET['state']     ?? '');
$type        = trim($_GET['type']      ?? '');
$stream      = trim($_GET['stream']    ?? '');
$min_fees    = intval($_GET['min_fees'] ?? 0);
$max_fees    = intval($_GET['max_fees'] ?? 0);
$featured    = intval($_GET['featured']  ?? 0);
$online_only = intval($_GET['online']    ?? 1);
$page        = max(1, intval($_GET['page']  ?? 1));
$limit       = min(24, max(1, intval($_GET['limit'] ?? 12)));
$offset      = ($page - 1) * $limit;

try {
    $db = getDB();

    // Detect available columns
    $colRows = $db->query("SHOW COLUMNS FROM colleges")->fetchAll(PDO::FETCH_COLUMN);
    $has = array_flip($colRows);

    // SELECT clause
    $sel = "c.id, c.name";
    foreach (['slug','short_name','city','state','district','established_year',
              'accreditation','naac_grade','nirf_rank','rating','total_reviews',
              'logo_url','cover_image_url','placement_rate','avg_package',
              'is_featured','is_partner','is_online','online_mode','ugc_approved',
              'min_fees','max_fees'] as $col) {
        if (isset($has[$col])) $sel .= ", c.$col";
    }
    if (isset($has['college_type'])) $sel .= ", c.college_type AS type";
    if (isset($has['institution_type'])) $sel .= ", c.institution_type";

    // Streams subquery — completely isolated
    $coursesSub = '';
    try {
        $db->query("SELECT 1 FROM college_streams LIMIT 1");
        foreach (['streams','course_streams','stream_categories','ck_streams'] as $t) {
            try {
                $cols = $db->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
                $nameField = null;
                foreach (['name','stream_name','title'] as $f) {
                    if (in_array($f, $cols)) { $nameField = $f; break; }
                }
                if ($nameField) {
                    $coursesSub = ", (SELECT GROUP_CONCAT(DISTINCT st.`$nameField` ORDER BY st.`$nameField` SEPARATOR ', ')
                                      FROM college_streams cs
                                      JOIN `$t` st ON st.id = cs.stream_id
                                      WHERE cs.college_id = c.id) AS top_courses";
                    break;
                }
            } catch (Throwable $e) { continue; }
        }
    } catch (Throwable $e) {}

    // Stream filter via college_streams JOIN
    $streamJoin = '';
    if ($stream !== '') {
        try {
            $db->query("SELECT 1 FROM college_streams LIMIT 1");
            foreach (['streams','course_streams','stream_categories','ck_streams'] as $t) {
                try {
                    $cols = $db->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_COLUMN);
                    $nameField = null;
                    foreach (['name','stream_name','title'] as $f) {
                        if (in_array($f, $cols)) { $nameField = $f; break; }
                    }
                    if ($nameField) {
                        $streamJoin = "JOIN college_streams _cs ON _cs.college_id = c.id
                                       JOIN `$t` _st ON _st.id = _cs.stream_id AND _st.`$nameField` = " . $db->quote($stream);
                        break;
                    }
                } catch (Throwable $e) { continue; }
            }
        } catch (Throwable $e) {}
    }

    // WHERE clause
    $where  = [];
    $params = [];

    if (isset($has['is_active']))  $where[] = "c.is_active = 1";
    if (isset($has['status']))     $where[] = "c.status = 'active'";

    if ($online_only && isset($has['is_online'])) {
        $where[] = "c.is_online = 1";
    }

    if ($featured && isset($has['is_featured'])) {
        $where[] = "c.is_featured = 1";
    }

    if ($search !== '') {
        $w = ["c.name LIKE ?"];
        if (isset($has['city']))       $w[] = "c.city LIKE ?";
        if (isset($has['state']))      $w[] = "c.state LIKE ?";
        if (isset($has['short_name'])) $w[] = "c.short_name LIKE ?";
        $where[] = '(' . implode(' OR ', $w) . ')';
        $like = '%' . $search . '%';
        foreach ($w as $_) $params[] = $like;
    }

    if ($state !== '' && isset($has['state'])) {
        $where[]  = "c.state = ?";
        $params[] = $state;
    }

    if ($type !== '' && isset($has['college_type'])) {
        $types = array_filter(array_map('trim', explode(',', $type)));
        if ($types) {
            $ph      = implode(',', array_fill(0, count($types), '?'));
            $where[] = "c.college_type IN ($ph)";
            foreach ($types as $t) $params[] = $t;
        }
    }

    if ($min_fees > 0 && isset($has['min_fees'])) {
        $where[]  = "c.min_fees >= ?";
        $params[] = $min_fees;
    }
    if ($max_fees > 0 && isset($has['max_fees'])) {
        $where[]  = "c.max_fees <= ?";
        $params[] = $max_fees;
    }

    $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // COUNT
    $countSql  = "SELECT COUNT(DISTINCT c.id) FROM colleges c $streamJoin $whereStr";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // DATA
    $orderBy = isset($has['nirf_rank'])
        ? "ORDER BY CASE WHEN c.nirf_rank IS NULL OR c.nirf_rank = 0 THEN 1 ELSE 0 END, c.nirf_rank ASC, c.name ASC"
        : "ORDER BY c.name ASC";

    $sql        = "SELECT DISTINCT $sel $coursesSub FROM colleges c $streamJoin $whereStr $orderBy LIMIT ? OFFSET ?";
    $dataParams = array_merge($params, [$limit, $offset]);
    $dataStmt   = $db->prepare($sql);
    $dataStmt->execute($dataParams);
    $colleges   = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($colleges as &$col) {
        $min = $col['min_fees'] ?? null;
        $max = $col['max_fees'] ?? null;
        if ($min && $max)   $col['fees_display'] = '₹' . number_format($min) . ' – ₹' . number_format($max);
        elseif ($min)       $col['fees_display'] = 'From ₹' . number_format($min);
        elseif ($max)       $col['fees_display'] = 'Upto ₹' . number_format($max);
        else                $col['fees_display'] = null;

        if (!empty($col['avg_package'])) {
            $col['avg_package_display'] = '₹' . number_format($col['avg_package'] / 100000, 1) . 'L';
        }

        $col['url_key'] = !empty($col['slug']) ? $col['slug'] : $col['id'];
    }
    unset($col);

    echo json_encode([
        'success'     => true,
        'colleges'    => $colleges,
        'total_count' => $total,
        'page'        => $page,
        'limit'       => $limit,
        'pages'       => ceil($total / $limit),
    ]);

} catch (Throwable $e) {
    // Never return 500 — always valid JSON
    http_response_code(200);
    echo json_encode([
        'success'     => false,
        'colleges'    => [],
        'total_count' => 0,
        'page'        => 1,
        'limit'       => $limit ?? 12,
        'pages'       => 0,
        'error'       => $e->getMessage(),
    ]);
}
