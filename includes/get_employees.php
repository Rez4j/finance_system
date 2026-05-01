<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT e.*, d.name as department_name 
                      FROM employees e 
                      JOIN departments d ON e.department_id = d.department_id 
                      ORDER BY e.last_name, e.first_name");
$employees = $stmt->fetchAll();
echo json_encode(['success' => true, 'data' => $employees]);
?>