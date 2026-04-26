<?php
/**
 * Prime Home Hub - Authentication Helper Functions
 */

/**
 * Get authenticated user from session token
 */
function get_authenticated_user() {
    global $db;

    // Get token from cookie or Authorization header
    $token = $_COOKIE['auth_token'] ?? '';
    
    if (empty($token)) {
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? '';
        if (preg_match('/Bearer\s+(.+)/', $auth_header, $matches)) {
            $token = $matches[1];
        }
    }

    if (empty($token)) {
        return null;
    }

    $token_hash = hash('sha256', $token);

    $user = $db->fetch_one(
        "SELECT id, username, email, role, first_name, last_name FROM users WHERE session_token = ?",
        [$token_hash]
    );

    return $user;
}

/**
 * Check if user is authenticated
 */
function is_authenticated() {
    return get_authenticated_user() !== null;
}

/**
 * Check if authenticated user is admin
 */
function is_admin() {
    $user = get_authenticated_user();
    return $user && $user['role'] === 'admin';
}

/**
 * Require authentication
 */
function require_auth() {
    if (!is_authenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
}

/**
 * Require admin role
 */
function require_admin() {
    if (!is_admin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit;
    }
}

/**
 * Log activity
 */
function log_activity($user_id, $action, $description) {
    global $db;

    $db->execute(
        "INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (?, ?, ?, NOW())",
        [$user_id, $action, $description]
    );
}
?>
