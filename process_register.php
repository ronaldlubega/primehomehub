<?php
session_start();
require_once 'data_handler.php';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Check if data is coming through
    if (!isset($_POST['role']) || !isset($_POST['name']) || !isset($_POST['password'])) {
        echo "Error: Missing required fields<br>";
        echo "POST data: " . print_r($_POST, true);
        exit();
    }
    
    $role = $_POST['role'];
    $name = $_POST['name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Initialize user data array
    $userData = [
        'id' => time() + rand(100, 999), // Unique ID
        'name' => $name,
        'password' => $password, // Now hashed
        'role' => $role,
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'last_login' => null,
        'login_count' => 0,
        'avatar' => null,
        'preferences' => [
            'currency' => 'UGX',
            'newsletter' => false,
            'notifications' => true
        ]
    ];
    
    // Add role-specific fields
    if ($role === 'client') {
        $userData['email'] = $_POST['email'] ?? '';
        $userData['phone'] = $_POST['contact'] ?? '';
        $userData['location'] = $_POST['location'] ?? '';
        $userData['gender'] = $_POST['gender'] ?? '';
        $userData['address'] = [
            'street' => '',
            'city' => $userData['location'],
            'country' => 'Uganda',
            'postal_code' => '256'
        ];
    } elseif ($role === 'admin') {
        $userData['email'] = $_POST['email'] ?? '';
        $userData['phone'] = $_POST['contact'] ?? '';
        $userData['location'] = 'Kampala, Uganda';
        $userData['gender'] = '';
        $userData['address'] = [
            'street' => '',
            'city' => 'Kampala',
            'country' => 'Uganda',
            'postal_code' => '256'
        ];
    }
    
    // Save user data
    $users = loadData('users.json');
    array_unshift($users, $userData);
    saveData('users.json', $users);
    
    // Alert admin about new user
    alertNewUser($userData);
    
    // Create session for immediate login
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['name'] = $userData['name'];
    $_SESSION['email'] = $userData['email'];
    $_SESSION['role'] = $userData['role'];
    
    // Redirect based on role
    if ($role === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: index.php');
    }
    exit();
} else {
    echo "Error: Not a POST request";
    exit();
}
?>
