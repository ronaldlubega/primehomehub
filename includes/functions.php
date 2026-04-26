<?php
// Authentication functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_admin() {
    if (!is_admin()) {
        redirect('login.php');
    }
}

// Price formatting
function format_price($price) {
    return 'UGX ' . number_format($price);
}

// Generate random string
function generate_random_string($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
}

// Image upload helper
function upload_image($file, $target_dir = 'uploads/') {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $filename = generate_random_string() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $target_file;
    }
    return false;
}

// Email validation
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Pagination helper
function get_pagination($page, $total, $per_page = 10) {
    $total_pages = ceil($total / $per_page);
    $offset = ($page - 1) * $per_page;
    
    return [
        'offset' => $offset,
        'limit' => $per_page,
        'total_pages' => $total_pages,
        'current_page' => $page
    ];
}

// Activity logging
function log_activity($user_id, $action, $details = '') {
    // In a real application, this would log to database
    error_log("User $user_id: $action - $details");
}

// Get client IP
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Phone validation
function validate_phone($phone) {
    // Basic phone validation - adjust as needed
    return preg_match('/^[0-9\-\+\(\)\s]+$/', $phone);
}

// Generate order number (Format: O[Sequence]-[Month]/[Year] e.g., O1-04/26)
function generate_order_number($db = null) {
    if (!$db) {
        if (!class_exists('Database')) {
            require_once dirname(__FILE__) . '/database.php';
        }
        $db = new Database();
    }
    
    $currentMonth = date('m');
    $currentYear = date('Y');
    
    // Count existing orders for the current month
    $sql = "SELECT COUNT(*) as count FROM orders WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?";
    $result = $db->fetch_one($sql, [$currentMonth, $currentYear]);
    $nextSequence = ($result['count'] ?? 0) + 1;
    
    $month = date('m');
    $year = date('y'); // 2-digit year
    
    return "O$nextSequence-$month/$year";
}

// Create notification
function create_notification($type, $title, $message, $user_id = null) {
    if (!class_exists('Database')) {
        require_once dirname(__FILE__) . '/database.php';
    }
    $db = new Database();
    return $db->execute(
        "INSERT INTO notifications (type, title, message, user_id) VALUES (?, ?, ?, ?)",
        [$type, $title, $message, $user_id]
    );
}

// Get time-based greeting
function get_time_greeting() {
    $hour = (int)date('H');
    if ($hour < 12) return 'Good morning';
    if ($hour < 17) return 'Good afternoon';
    return 'Good evening';
}
?>
