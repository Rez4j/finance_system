<?php
require_once '../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// API Key Authentication (optional)
function authenticateAPI() {
    $api_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
    
    // For demo, accept a simple key. In production, use database validation
    $valid_keys = ['fin_sys_2024', 'demo_api_key'];
    
    if (!in_array($api_key, $valid_keys)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid API key']);
        exit();
    }
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get endpoint from URL
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/api';
$endpoint = str_replace($base_path, '', parse_url($request_uri, PHP_URL_PATH));
$endpoint = trim($endpoint, '/');
$segments = explode('/', $endpoint);

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
?>