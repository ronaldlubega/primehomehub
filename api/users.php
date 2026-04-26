<?php
/**
 * Prime Home Hub - User Authentication API
 * Handles registration, login, logout, and user profile
 */

header('Content-Type: application/json');

require_once '../includes/database.php';
require_once '../includes/auth.php';

// Initialize database connection
$db = new Database();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'profile';

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get raw POST data
$input = json_decode(file_get_contents("php://input"), true) ?? $_POST;

// Route requests
switch ($action) {
    case 'register':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        register_user($input);
        break;

    case 'login':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        login_user($input);
        break;

    case 'logout':
        logout_user();
        break;

    case 'profile':
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        get_user_profile();
        break;

    case 'update':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        update_user_profile($input);
        break;

    case 'create-admin':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        create_admin_user($input);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
        exit;
}

/**
 * Register a new user
 */
function register_user($input) {
    global $db;

    // Validate input
    $email = trim($input['email'] ?? '');
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $confirm_password = $input['confirm_password'] ?? '';
    $first_name = trim($input['first_name'] ?? '');
    $last_name = trim($input['last_name'] ?? '');

    if (empty($email) || empty($username) || empty($password) || empty($first_name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }

    if ($password !== $confirm_password) {
        http_response_code(400);
        echo json_encode(['error' => 'Passwords do not match']);
        return;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 8 characters']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        return;
    }

    // Check if user already exists
    $existing = $db->fetch_one(
        "SELECT id FROM users WHERE email = ? OR username = ?",
        [$email, $username]
    );

    if ($existing) {
        http_response_code(409);
        echo json_encode(['error' => 'Email or username already exists']);
        return;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $result = $db->execute(
        "INSERT INTO users (username, email, password_hash, first_name, last_name, role, created_at) 
         VALUES (?, ?, ?, ?, ?, 'customer', NOW())",
        [$username, $email, $hashed_password, $first_name, $last_name]
    );

    if ($result['success']) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully',
            'user_id' => $result['insert_id']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed']);
    }
}

/**
 * Login user
 */
function login_user($input) {
    global $db;

    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password required']);
        return;
    }

    // Get user
    $user = $db->fetch_one(
        "SELECT id, name, email, password, role FROM users WHERE email = ?",
        [$email]
    );

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password']);
        return;
    }

    // Create session token
    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);

    $db->execute(
        "UPDATE users SET session_token = ?, last_login = NOW() WHERE id = ?",
        [$token_hash, $user['id']]
    );

    // Set secure cookie
    setcookie('auth_token', $token, [
        'expires' => time() + (30 * 24 * 60 * 60),
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ],
        'token' => $token
    ]);
}

/**
 * Logout user
 */
function logout_user() {
    global $db;

    // Get token from cookie or header
    $token = $_COOKIE['auth_token'] ?? '';
    
    if (empty($token)) {
        http_response_code(400);
        echo json_encode(['error' => 'No active session']);
        return;
    }

    $token_hash = hash('sha256', $token);

    // Clear session
    $db->execute(
        "UPDATE users SET session_token = NULL WHERE session_token = ?",
        [$token_hash]
    );

    // Clear cookie
    setcookie('auth_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true
    ]);

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
}

/**
 * Get user profile
 */
function get_user_profile() {
    global $db;

    $user = get_authenticated_user();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        return;
    }

    $profile = $db->fetch_one(
        "SELECT id, username, email, first_name, last_name, role, avatar_url, phone, address, city, country, created_at FROM users WHERE id = ?",
        [$user['id']]
    );

    if ($profile) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'user' => $profile
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }
}

/**
 * Update user profile
 */
function update_user_profile($input) {
    global $db;

    $user = get_authenticated_user();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        return;
    }

    $first_name = trim($input['first_name'] ?? '');
    $last_name = trim($input['last_name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $address = trim($input['address'] ?? '');
    $city = trim($input['city'] ?? '');
    $country = trim($input['country'] ?? '');

    $result = $db->execute(
        "UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, country = ? WHERE id = ?",
        [$first_name, $last_name, $phone, $address, $city, $country, $user['id']]
    );

    if ($result['success']) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update profile']);
    }
}

/**
 * Create an admin user (for setup)
 */
function create_admin_user($input) {
    global $db;

    // Validate input
    $email = trim($input['email'] ?? '');
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $first_name = trim($input['first_name'] ?? '');
    $last_name = trim($input['last_name'] ?? '');

    if (empty($email) || empty($username) || empty($password) || empty($first_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        return;
    }

    // Check if admin already exists
    $check = $db->query("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
    if ($check && count($check) > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Admin user already exists']);
        return;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $role = 'admin';

    // Insert admin user
    $result = $db->execute(
        "INSERT INTO users (username, email, password_hash, first_name, last_name, role, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, NOW())",
        [$username, $email, $password_hash, $first_name, $last_name, $role]
    );

    if ($result['success']) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Admin user created successfully',
            'admin_id' => $result['id'] ?? 0,
            'username' => $username,
            'email' => $email,
            'role' => $role
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create admin user']);
    }
}
?>
