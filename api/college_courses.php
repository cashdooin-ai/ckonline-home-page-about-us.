<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$pdo = getPDO();
$collegeId = !empty($_GET['college_id']) ? (int)$_GET['college_id'] : 0;

if (!$collegeId) { echo json_encode([]); exit; }

// college_courses is a flat table - no separate courses catalog to join
// (see api/college-courses-save.php for the full story).
$stmt = $pdo->prepare("
    SELECT id, course_name AS name, annual_fees
    FROM college_courses
    WHERE college_id = ? AND is_active = 1
    ORDER BY course_name
");
$stmt->execute([$collegeId]);
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
