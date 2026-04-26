<?php
// Simple test for debugging registration
session_start();
require_once 'data_handler.php';

echo "<h2>Registration Debug Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    if (isset($_POST['role']) && isset($_POST['name']) && isset($_POST['password'])) {
        echo "<h3>All required fields present!</h3>";
        
        // Try to save user
        $userData = [
            'id' => time(),
            'name' => $_POST['name'],
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role' => $_POST['role'],
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Add role-specific fields
        if ($_POST['role'] === 'client') {
            $userData['email'] = $_POST['email'] ?? '';
            $userData['phone'] = $_POST['contact'] ?? '';
            $userData['location'] = $_POST['location'] ?? '';
            $userData['gender'] = $_POST['gender'] ?? '';
        } else {
            $userData['email'] = $_POST['email'] ?? '';
            $userData['phone'] = $_POST['contact'] ?? '';
            $userData['location'] = 'Kampala, Uganda';
            $userData['gender'] = '';
        }
        
        echo "<h3>User Data to Save:</h3>";
        echo "<pre>" . print_r($userData, true) . "</pre>";
        
        // Save user
        $users = loadData('users.json');
        array_unshift($users, $userData);
        saveData('users.json', $users);
        
        echo "<h3>User saved successfully!</h3>";
        echo "<p><a href='index.php'>Go to Home</a></p>";
        
    } else {
        echo "<h3>Missing required fields!</h3>";
    }
} else {
    echo "<h3>Not a POST request</h3>";
}
?>

<p><a href="register.php">Back to Registration</a></p>
