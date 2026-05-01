<?php
require_once '../config/db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT * FROM inventory_items ORDER BY name");
$items = $stmt->fetchAll();
echo json_encode(['success' => true, 'data' => $items]);
?>