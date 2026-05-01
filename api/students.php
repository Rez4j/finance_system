<?php
require_once '../config/db.php';
header('Content-Type: application/json');

// Fetches students for selection dropdowns
$stmt = $pdo->query("SELECT student_id, first_name, last_name FROM students ORDER BY last_name ASC");
echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
