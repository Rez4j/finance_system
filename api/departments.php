<?php
require_once '../config/db.php';
header('Content-Type: application/json');

// Simple GET to list departments for dropdowns[cite: 1]
$stmt = $pdo->query("SELECT department_id, name FROM departments ORDER BY name ASC");
echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);