<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Method not allowed.']); exit; }

require_once __DIR__ . '/../config/db.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$studentName    = trim($input['student_name']    ?? '');
$email          = trim($input['email']           ?? '');
$phone          = trim($input['phone']           ?? '');
$courseInterest = trim($input['course_interest'] ?? '');
$collegeId      = !empty($input['college_id'])   ? (int)$input['college_id'] : null;
$message        = trim($input['message']         ?? '');
$source         = trim($input['source']          ?? 'website');

$errors = [];
if (!$studentName) $errors[] = 'Name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if ($phone && !preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/', '', $phone))) $errors[] = 'Enter a valid 10-digit Indian mobile number.';

if ($errors) { http_response_code(422); echo json_encode(['error' => implode(' ', $errors)]); exit; }

$allowedSources = ['website','college_detail','apply','contact','api'];
if (!in_array($source, $allowedSources)) $source = 'website';

try {
    $pdo = getPDO();
    if ($collegeId) {
        $chk = $pdo->prepare('SELECT id FROM colleges WHERE id = ? AND is_active = 1');
        $chk->execute([$collegeId]);
        if (!$chk->fetch()) $collegeId = null;
    }
    $stmt = $pdo->prepare('INSERT INTO leads (student_name, email, phone, course_interest, college_id, message, source) VALUES (:name, :email, :phone, :course, :college_id, :msg, :source)');
    $stmt->execute([':name'=>$studentName,':email'=>$email,':phone'=>$phone,':course'=>$courseInterest,':college_id'=>$collegeId,':msg'=>$message,':source'=>$source]);
    $leadId = (int)$pdo->lastInsertId();
    echo json_encode(['success' => true, 'lead_id' => $leadId, 'message' => 'Enquiry submitted successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not save enquiry. Please try again later.']);
}
