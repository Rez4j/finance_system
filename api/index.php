<?php
require_once 'config.php';

// Authenticate all requests
authenticateAPI();

// Route requests
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;

try {
    switch($resource) {
        case 'accounts':
            require_once 'accounts.php';
            break;
            
        case 'transactions':
            require_once 'transactions.php';
            break;
            
        case 'budgets':
            require_once 'budgets.php';
            break;
            
        case 'payments':
            require_once 'payments.php';
            break;
            
        case 'reports':
            require_once 'reports.php';
            break;
            
        case 'dashboard':
            require_once 'dashboard.php';
            break;

        case 'resources':
            require_once 'resources.php';
            break;
            
        case 'reservations':
            require_once 'reservations.php';
            break;
            
        case 'availability':
            require_once 'availability.php';
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Endpoint not found',
                'available_endpoints' => [
                    'accounts',
                    'transactions',
                    'budgets',
                    'payments',
                    'reports',
                    'dashboard'
                ]
            ]);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>