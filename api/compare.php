<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
// Mirrors compare.php's own HTML-page gate (same $_SESSION key) - without
// this, anyone could call this JSON endpoint directly and get every gated
// field (fees, established year, accreditation, courses) for free,
// bypassing the lead-capture form entirely (Codex finding on PR #18).
$compareUnlocked = !empty($_SESSION['ck_compare_unlocked']);

$idsParam = trim($_GET['ids'] ?? '');
if ($idsParam === '') {
    echo json_encode(['success' => false, 'message' => 'No IDs provided']);
    exit;
}

$rawIds = array_slice(array_filter(array_map('intval', explode(',', $idsParam))), 0, 3);
if (empty($rawIds)) {
    echo json_encode(['success' => false, 'message' => 'Invalid IDs']);
    exit;
}

try {
    $db = getDB();

    $cols = [];
    $stmt = $db->query("SHOW COLUMNS FROM colleges");
    foreach ($stmt->fetchAll() as $row) $cols[] = $row['Field'];

    $select = "c.id, c.name";
    if (in_array('city', $cols)) $select .= ", c.city";
    if (in_array('state', $cols)) $select .= ", c.state";
    if (in_array('type', $cols)) $select .= ", c.type";
    if (in_array('min_fees', $cols)) $select .= ", c.min_fees";
    if (in_array('max_fees', $cols)) $select .= ", c.max_fees";
    if (in_array('established_year', $cols)) $select .= ", c.established_year";
    if (in_array('accreditation', $cols)) $select .= ", c.accreditation";
    if (in_array('total_seats', $cols)) $select .= ", c.total_seats";
    if (in_array('description', $cols)) $select .= ", c.description";

    $courseSubquery = '';
    try {
        // college_courses is a flat table - course_name is a plain column,
        // no separate courses catalog to join (see
        // api/college-courses-save.php for the full story).
        $db->query("SELECT 1 FROM college_courses LIMIT 1");
        $courseSubquery = ", (SELECT GROUP_CONCAT(course_name SEPARATOR ', ') FROM college_courses cc WHERE cc.college_id = c.id AND cc.is_active = 1) AS all_courses";
    } catch (Exception $e) {}

    $ph = implode(',', array_fill(0, count($rawIds), '?'));
    $sql = "SELECT $select $courseSubquery FROM colleges c WHERE c.id IN ($ph)";
    $stmt = $db->prepare($sql);
    $stmt->execute($rawIds);
    $colleges = $stmt->fetchAll();

    foreach ($colleges as &$col) {
        $min = $col['min_fees'] ?? null;
        $max = $col['max_fees'] ?? null;
        if ($min && $max) $col['fees_display'] = '₹' . number_format($min) . ' – ₹' . number_format($max);
        elseif ($min) $col['fees_display'] = 'From ₹' . number_format($min);
        elseif ($max) $col['fees_display'] = 'Upto ₹' . number_format($max);
        else $col['fees_display'] = 'N/A';
    }
    unset($col);

    // Everything beyond basic identity (name/city/state/type) is gated,
    // same as the HTML page's first-row-only view for a visitor who
    // hasn't submitted the lead form.
    if (!$compareUnlocked) {
        $gatedFields = ['min_fees', 'max_fees', 'established_year', 'accreditation', 'total_seats', 'description', 'all_courses', 'fees_display'];
        foreach ($colleges as &$col) {
            foreach ($gatedFields as $f) unset($col[$f]);
            $col['locked'] = true;
        }
        unset($col);
    }

    echo json_encode(['success' => true, 'unlocked' => $compareUnlocked, 'colleges' => $colleges]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
