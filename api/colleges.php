<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$search    = trim($_GET['search']   ?? '');
$state     = trim($_GET['state']    ?? '');
$type      = trim($_GET['type']     ?? '');   // college_type
$category  = trim($_GET['category'] ?? '');
$min_fees  = intval($_GET['min_fees'] ?? 0);
$max_fees  = intval($_GET['max_fees'] ?? 0);
$featured   = intval($_GET['featured']  ?? 0);
$online_only = intval($_GET['online']   ?? 1);  // default: show only online colleges
$page       = max(1, intval($_GET['page']  ?? 1));
$limit     = min(24, max(1, intval($_GET['limit'] ?? 12)));
$offset    = ($page - 1) * $limit;

try {
    $db = getDB();

    // ── Detect available columns once ─────────────────────────────────
    $colRows = $db->query("SHOW COLUMNS FROM colleges")->fetchAll(PDO::FETCH_COLUMN);
    $has = array_flip($colRows);   // O(1) lookup

    // ── SELECT clause using real column names ─────────────────────────
    $sel = "c.id, c.name";
    if (isset($has['slug']))             $sel .= ", c.slug";
    if (isset($has['short_name']))       $sel .= ", c.short_name";
    if (isset($has['city']))             $sel .= ", c.city";
    if (isset($has['state']))            $sel .= ", c.state";
    if (isset($has['district']))         $sel .= ", c.district";
    if (isset($has['college_type']))     $sel .= ", c.college_type AS type";
    if (isset($has['institution_type'])) $sel .= ", c.institution_type";
    if (isset($has['min_fees']))         $sel .= ", c.min_fees";
    if (isset($has['max_fees']))         $sel .= ", c.max_fees";
    if (isset($has['established_year'])) $sel .= ", c.established_year";
    if (isset($has['accreditation']))    $sel .= ", c.accreditation";
    if (isset($has['naac_grade']))       $sel .= ", c.naac_grade";
    if (isset($has['nirf_rank']))        $sel .= ", c.nirf_rank";
    if (isset($has['rating']))           $sel .= ", c.rating";
    if (isset($has['total_reviews']))    $sel .= ", c.total_reviews";
    if (isset($has['logo_url']))         $sel .= ", c.logo_url";
    if (isset($has['cover_image_url']))  $sel .= ", c.cover_image_url";
    if (isset($has['placement_rate']))   $sel .= ", c.placement_rate";
    if (isset($has['avg_package']))      $sel .= ", c.avg_package";
    if (isset($has['is_featured']))      $sel .= ", c.is_featured";
    if (isset($has['is_partner']))       $sel .= ", c.is_partner";
    if (isset($has['is_online']))        $sel .= ", c.is_online";
    if (isset($has['online_mode']))      $sel .= ", c.online_mode";
    if (isset($has['ugc_approved']))     $sel .= ", c.ugc_approved";

    // ── Top streams/courses subquery (uses college_streams) ───────────
    $coursesSub = '';
    try {
        $streamCols = $db->query("SHOW COLUMNS FROM college_streams")->fetchAll(PDO::FETCH_COLUMN);
        $streamHas  = array_flip($streamCols);

        // college_streams likely has: college_id, stream_name / name / stream
        $nameCol = 'stream_name';
        if (!isset($streamHas['stream_name']) && isset($streamHas['name']))        $nameCol = 'name';
        if (!isset($streamHas[$nameCol])       && isset($streamHas['stream']))     $nameCol = 'stream';

        if (isset($streamHas['college_id']) && isset($streamHas[$nameCol])) {
            $coursesSub = ", (SELECT GROUP_CONCAT(DISTINCT cs.$nameCol ORDER BY cs.$nameCol SEPARATOR ', ')
                              FROM college_streams cs
                              WHERE cs.college_id = c.id
                              LIMIT 1) AS top_courses";
        }
    } catch (Throwable $e) {}

    // Fallback: try college_courses → courses join
    if ($coursesSub === '') {
        try {
            $db->query("SELECT 1 FROM college_courses LIMIT 1");
            $ccCols  = $db->query("SHOW COLUMNS FROM college_courses")->fetchAll(PDO::FETCH_COLUMN);
            $ccHas   = array_flip($ccCols);

            // course name might be in college_courses itself or joined from a courses table
            if (isset($ccHas['college_id']) && isset($ccHas['course_name'])) {
                $coursesSub = ", (SELECT GROUP_CONCAT(DISTINCT cc.course_name SEPARATOR ', ')
                                  FROM college_courses cc
                                  WHERE cc.college_id = c.id
                                  LIMIT 1) AS top_courses";
            } elseif (isset($ccHas['college_id']) && isset($ccHas['course_id'])) {
                // Try joining a courses or ck_exams table
                try {
                    $db->query("SELECT 1 FROM courses LIMIT 1");
                    $coursesSub = ", (SELECT GROUP_CONCAT(DISTINCT cr.name SEPARATOR ', ')
                                      FROM college_courses cc
                                      JOIN courses cr ON cr.id = cc.course_id
                                      WHERE cc.college_id = c.id
                                      LIMIT 1) AS top_courses";
                } catch (Throwable $e) {}
            }
        } catch (Throwable $e) {}
    }

    // ── WHERE clause ──────────────────────────────────────────────────
    $where  = [];
    $params = [];

    // Only filter is_active / status if the column exists
    if (isset($has['is_active']))  { $where[] = "c.is_active = 1"; }
    if (isset($has['status']))     { $where[] = "c.status = 'active'"; }

    // Default: only show online colleges on this portal
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
        $where[]  = '(' . implode(' OR ', $w) . ')';
        $like     = '%' . $search . '%';
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

    // ── COUNT ─────────────────────────────────────────────────────────
    $countSql  = "SELECT COUNT(*) FROM colleges c $whereStr";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    // ── DATA ──────────────────────────────────────────────────────────
    $orderBy = isset($has['nirf_rank'])
        ? "ORDER BY CASE WHEN c.nirf_rank IS NULL OR c.nirf_rank = 0 THEN 1 ELSE 0 END, c.nirf_rank ASC, c.name ASC"
        : "ORDER BY c.name ASC";

    $sql        = "SELECT $sel $coursesSub FROM colleges c $whereStr $orderBy LIMIT ? OFFSET ?";
    $dataParams = array_merge($params, [$limit, $offset]);
    $dataStmt   = $db->prepare($sql);
    $dataStmt->execute($dataParams);
    $colleges   = $dataStmt->fetchAll();

    // ── Format for output ─────────────────────────────────────────────
    foreach ($colleges as &$col) {
        // Fees display
        $min = $col['min_fees'] ?? null;
        $max = $col['max_fees'] ?? null;
        if ($min && $max)   $col['fees_display'] = '₹' . number_format($min) . ' – ₹' . number_format($max);
        elseif ($min)       $col['fees_display'] = 'From ₹' . number_format($min);
        elseif ($max)       $col['fees_display'] = 'Upto ₹' . number_format($max);
        else                $col['fees_display'] = null;

        // Package display
        if (!empty($col['avg_package'])) {
            $col['avg_package_display'] = '₹' . number_format($col['avg_package'] / 100000, 1) . 'L';
        }

        // Use slug for URLs, fallback to id
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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
