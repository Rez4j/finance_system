<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name");
$suppliers = $stmt->fetchAll();
echo json_encode(['success' => true, 'data' => $suppliers]);
?>