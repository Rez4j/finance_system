<?php
require_once '../config/db.php';
header('Content-Type: application/json');

// Fetches the employee list for the dropdown[cite: 1]
$stmt = $pdo->query("SELECT employee_id, first_name, last_name FROM employees ORDER BY last_name ASC");
echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);