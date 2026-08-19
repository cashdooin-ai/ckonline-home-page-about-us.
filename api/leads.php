<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/leads-schema.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$name    = trim($_POST['name'] ?? $_POST['full_name'] ?? $_POST['student_name'] ?? '');
$email   = trim($_POST['email'] ?? $_POST['student_email'] ?? '');
$phone   = trim($_POST['phone'] ?? $_POST['student_phone'] ?? '');
$source  = trim($_POST['source'] ?? 'website');
$message = trim($_POST['message'] ?? '');
$course  = trim($_POST['course_interest'] ?? $_POST['interested_course'] ?? '');
$state   = trim($_POST['state'] ?? $_POST['preferred_state'] ?? '');
$qualification = trim($_POST['current_qualification'] ?? '');
$college_id = intval($_POST['college_id'] ?? 0);
$course_id  = intval($_POST['course_id'] ?? 0);
// lead-form.php's widget sends this as "referrer_url"; other forms may send
// "page_url" directly - either way, this is what shows as the source page
// in admin's Leads list (the online_leads.page_url column already exists
// for this, but nothing was ever writing to it).
$page_url = trim($_POST['page_url'] ?? $_POST['referrer_url'] ?? '');

// Validate
$errors = [];
if ($name === '')  $errors[] = 'Name is required';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
// Several forms (exit-popup, lead-form, coupons, online-mba) prepend +91
// client-side before sending, so the digits-only value is 12 chars
// (91XXXXXXXXXX), not 10 - strip a leading 91 of that length before
// validating, so those forms' phone numbers aren't rejected outright.
$phoneDigits = preg_replace('/\D/', '', $phone);
if (strlen($phoneDigits) === 12 && str_starts_with($phoneDigits, '91')) {
    $phoneDigits = substr($phoneDigits, 2);
}
if ($phone === '' || !preg_match('/^[6-9]\d{9}$/', $phoneDigits)) {
    $errors[] = 'Valid 10-digit phone is required';
} else {
    $phone = $phoneDigits; // store the normalized bare 10-digit form regardless of which form/prefix it came in with
}

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

try {
    $db = getDB();
    onlineLeadsEnsureSchema($db);

    // Insert into online_leads table (flexible column detection)
    $leadCols = [];
    $stmt = $db->query("SHOW COLUMNS FROM online_leads");
    foreach ($stmt->fetchAll() as $row) $leadCols[] = $row['Field'];

    $insertCols = ['name', 'email', 'phone'];
    $insertVals = [$name, $email, $phone];

    if (in_array('source', $leadCols)) { $insertCols[] = 'source'; $insertVals[] = $source; }
    if (in_array('message', $leadCols) && $message !== '') { $insertCols[] = 'message'; $insertVals[] = $message; }
    if (in_array('course_interest', $leadCols) && $course !== '') { $insertCols[] = 'course_interest'; $insertVals[] = $course; }
    if (in_array('state', $leadCols) && $state !== '') { $insertCols[] = 'state'; $insertVals[] = $state; }
    if (in_array('qualification', $leadCols) && $qualification !== '') { $insertCols[] = 'qualification'; $insertVals[] = $qualification; }
    if (in_array('college_id', $leadCols) && $college_id > 0) { $insertCols[] = 'college_id'; $insertVals[] = $college_id; }
    if (in_array('course_id', $leadCols) && $course_id > 0) { $insertCols[] = 'course_id'; $insertVals[] = $course_id; }
    if (in_array('page_url', $leadCols) && $page_url !== '') { $insertCols[] = 'page_url'; $insertVals[] = substr($page_url, 0, 500); }
    if (in_array('created_at', $leadCols)) { $insertCols[] = 'created_at'; $insertVals[] = date('Y-m-d H:i:s'); }

    $ph = implode(',', array_fill(0, count($insertCols), '?'));
    $colStr = implode(',', $insertCols);
    $stmt = $db->prepare("INSERT INTO online_leads ($colStr) VALUES ($ph)");
    $stmt->execute($insertVals);
    $leadId = $db->lastInsertId();

    // If this is an application, also insert into applications table if exists
    $appId = null;
    if ($source === 'application' || $college_id > 0) {
        try {
            $appCols = [];
            $st2 = $db->query("SHOW COLUMNS FROM applications");
            foreach ($st2->fetchAll() as $r) $appCols[] = $r['Field'];

            $aCols = [];
            $aVals = [];
            if (in_array('lead_id', $appCols)) { $aCols[] = 'lead_id'; $aVals[] = $leadId; }
            if (in_array('college_id', $appCols) && $college_id > 0) { $aCols[] = 'college_id'; $aVals[] = $college_id; }
            if (in_array('course_id', $appCols) && $course_id > 0) { $aCols[] = 'course_id'; $aVals[] = $course_id; }
            if (in_array('name', $appCols)) { $aCols[] = 'name'; $aVals[] = $name; }
            if (in_array('email', $appCols)) { $aCols[] = 'email'; $aVals[] = $email; }
            if (in_array('phone', $appCols)) { $aCols[] = 'phone'; $aVals[] = $phone; }
            if (in_array('status', $appCols)) { $aCols[] = 'status'; $aVals[] = 'pending'; }
            if (in_array('created_at', $appCols)) { $aCols[] = 'created_at'; $aVals[] = date('Y-m-d H:i:s'); }

            if ($aCols) {
                $ph2 = implode(',', array_fill(0, count($aCols), '?'));
                $st3 = $db->prepare("INSERT INTO applications (" . implode(',', $aCols) . ") VALUES ($ph2)");
                $st3->execute($aVals);
                $appId = $db->lastInsertId();
            }
        } catch (Throwable $e) {
            // applications table may not exist; ignore
        }
    }

    echo json_encode(['success' => true, 'lead_id' => $leadId, 'application_id' => $appId ?: ('CK' . $leadId)]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}
