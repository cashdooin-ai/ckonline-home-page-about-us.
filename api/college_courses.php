<?php
// INTEGRATION: Admin portal reads this DB. Partner portal writes to colleges/courses tables.
// Shared DB: collegekampus -- see database/schema.sql

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$pdo = getPDO();
$collegeId = !empty($_GET['college_id']) ? (int)$_GET['college_id'] : 0;

if (!$collegeId) { echo json_encode([]); exit; }

$stmt = $pdo->prepare("
    SELECT cr.id, cr.name, cc.annual_fees
    FROM college_courses cc
    JOIN courses cr ON cc.course_id = cr.id
    WHERE cc.college_id = ? AND cc.is_active = 1
    ORDER BY cr.name
");
$stmt->execute([$collegeId]);
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
