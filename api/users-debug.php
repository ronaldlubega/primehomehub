<?php
/**
 * Debug Users API - Prime Home Hub
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type
header('Content-Type: application/json');

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Include database
    require_once '../includes/database.php';
    
    // Initialize database
    $db = new Database();
    
    // Get action
    $action = $_GET['action'] ?? 'test';
    
    // Get input
    $input = json_decode(file_get_contents("php://input"), true) ?? $_POST;
    
    // Debug info
    $debug = [
        'method' => $_SERVER['REQUEST_METHOD'],
        'action' => $action,
        'input' => $input,
        'database_connection' => 'OK'
    ];
    
    switch ($action) {
        case 'test':
            echo json_encode([
                'success' => true,
                'message' => 'API is working',
                'debug' => $debug
            ]);
            break;
            
        case 'login':
            $email = trim($input['email'] ?? '');
            $password = $input['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                http_response_code(400);
                echo json_encode(['error' => 'Email and password required']);
                break;
            }
            
            // Get user
            $user = $db->fetch_one(
                "SELECT id, name, email, password, role FROM users WHERE email = ?",
                [$email]
            );
            
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'User not found']);
                break;
            }
            
            // Check password (try both password_hash and MD5 fallback)
            $password_valid = false;
            if (function_exists('password_verify')) {
                $password_valid = password_verify($password, $user['password']);
            } else {
                // MD5 fallback
                $password_valid = (md5($password . 'salt') === $user['password']);
            }
            
            if (!$password_valid) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid password']);
                break;
            }
            
            // Create session token
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            
            // Update user with session token
            $db->execute(
                "UPDATE users SET session_token = ?, last_login = NOW() WHERE id = ?",
                [$token_hash, $user['id']]
            );
            
            // Success response
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'token' => $token,
                'debug' => $debug
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found', 'debug' => $debug]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
