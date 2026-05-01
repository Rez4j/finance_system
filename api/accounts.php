<?php
// GET /api/accounts - List all accounts
// GET /api/accounts/{id} - Get single account
// POST /api/accounts - Create account
// PUT /api/accounts/{id} - Update account
// DELETE /api/accounts/{id} - Delete account

// Add this to the top of api/accounts.php if not already there
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if ($id) {
            // Get single account
            $stmt = $pdo->prepare("SELECT * FROM accounts WHERE account_id = ?");
            $stmt->execute([$id]);
            $account = $stmt->fetch();
            
            if ($account) {
                echo json_encode(['success' => true, 'data' => $account]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Account not found']);
            }
        } else {
            // List all accounts
            $type = $_GET['type'] ?? '';
            $sql = "SELECT * FROM accounts";
            $params = [];
            
            if ($type) {
                $sql .= " WHERE type = ?";
                $params[] = $type;
            }
            
            $sql .= " ORDER BY account_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $accounts = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => $accounts,
                'total' => count($accounts)
            ]);
        }
        break;
        
    case 'POST':
        $name = $input['name'] ?? '';
        $type = $input['type'] ?? '';
        
        if (empty($name) || empty($type)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name and type are required']);
            exit();
        }
        
        $stmt = $pdo->prepare("INSERT INTO accounts (name, type) VALUES (?, ?)");
        $stmt->execute([$name, $type]);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Account created',
            'data' => ['account_id' => $pdo->lastInsertId()]
        ]);
        break;
        
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Account ID required']);
            exit();
        }
        
        $name = $input['name'] ?? '';
        $type = $input['type'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE accounts SET name = ?, type = ? WHERE account_id = ?");
        $stmt->execute([$name, $type, $id]);
        
        echo json_encode(['success' => true, 'message' => 'Account updated']);
        break;
        
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Account ID required']);
            exit();
        }
        
        $stmt = $pdo->prepare("DELETE FROM accounts WHERE account_id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Account deleted']);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>