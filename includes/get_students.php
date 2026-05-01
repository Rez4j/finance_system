<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT * FROM students ORDER BY last_name, first_name");
$students = $stmt->fetchAll();
echo json_encode(['success' => true, 'data' => $students]);
?>