<?php
// Simple test API - no database required
header('Content-Type: application/json');

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? 'test';

switch ($action) {
    case 'test':
        echo json_encode([
            'success' => true,
            'message' => 'Simple API is working!',
            'method' => $_SERVER['REQUEST_METHOD'],
            'action' => $action,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        break;
        
    case 'login':
        $input = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        
        echo json_encode([
            'success' => true,
            'message' => 'Login test successful',
            'received' => $input,
            'note' => 'This is a test - database not connected yet'
        ]);
        break;
        
    case 'admin-check':
        echo json_encode([
            'success' => true,
            'message' => 'Admin check endpoint',
            'note' => 'This would check if admin user exists in database'
        ]);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Unknown action',
            'action' => $action
        ]);
}
?>
