<?php
// Create Admin User Script
// Run from command line or browser: http://localhost/create-admin.php

require_once 'includes/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Admin credentials
    $email = 'admin@primehomehub.com';
    $username = 'admin';
    $password = 'Admin@123456'; // Change this to a secure password
    $first_name = 'Admin';
    $last_name = 'User';
    
    // Hash the password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Check if admin already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "Admin user already exists.\n";
        echo "Email: $email\n";
        exit(1);
    }
    
    // Insert admin user
    $role = 'admin';
    $name = $first_name . ' ' . $last_name;
    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, password, role, created_at) 
         VALUES (?, ?, ?, ?, NOW())"
    );
    
    $stmt->bind_param("ssss", $name, $email, $password_hash, $role);
    
    if ($stmt->execute()) {
        $admin_id = $stmt->insert_id;
        echo "✓ Admin user created successfully!\n";
        echo "─────────────────────────────────\n";
        echo "Admin ID: $admin_id\n";
        echo "Username: $username\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
        echo "Role: $role\n";
        echo "\nIMPORTANT: Change the password after first login!\n";
        exit(0);
    } else {
        echo "Error creating admin user: " . $stmt->error . "\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
