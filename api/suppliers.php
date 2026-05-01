<?php
/**
 * API for Supplier Management
 * Supports: GET (List/Single), POST (Create), PUT (Update), DELETE
 */
require_once '../config/db.php';

// Set headers for JSON response
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

// Simple API Key Check
$api_key = $_GET['api_key'] ?? '';
if ($api_key !== 'fin_sys_2024') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

switch($method) {
    case 'GET':
        if ($id) {
            // Fetch a single supplier
            $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
            $stmt->execute([$id]);
            $supplier = $stmt->fetch();
            
            if ($supplier) {
                echo json_encode(['success' => true, 'data' => $supplier]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Supplier not found']);
            }
        } else {
            // List all suppliers ordered by name
            $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC");
            $suppliers = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $suppliers]);
        }
        break;

    case 'POST':
        // Create new supplier entry
        $name = $input['name'] ?? '';
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Supplier name is required']);
            exit();
        }

        $sql = "INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES (?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([
            $name,
            $input['contact_person'] ?? null,
            $input['phone'] ?? null,
            $input['email'] ?? null,
            $input['address'] ?? null
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Supplier added', 'id' => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        // Update existing supplier details[cite: 1]
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID required for update']);
            exit();
        }

        $sql = "UPDATE suppliers SET name = ?, contact_person = ?, phone = ?, email = ?, address = ? WHERE supplier_id = ?";
        $pdo->prepare($sql)->execute([
            $input['name'],
            $input['contact_person'] ?? null,
            $input['phone'] ?? null,
            $input['email'] ?? null,
            $input['address'] ?? null,
            $id
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Supplier details updated']);
        break;

    case 'DELETE':
        // Remove supplier from database[cite: 1]
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID required for deletion']);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Supplier deleted']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}