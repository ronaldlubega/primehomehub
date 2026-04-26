<?php
/**
 * Database Connection Test
 */

echo "=== Database Connection Test ===\n\n";

// Test 1: Basic MySQL connection
echo "1. Testing basic MySQL connection...\n";
try {
    $conn = new mysqli('localhost', 'root', '', '', 3306);
    
    if ($conn->connect_error) {
        echo "❌ MySQL Connection Failed: " . $conn->connect_error . "\n";
        exit(1);
    }
    echo "✅ MySQL connection successful\n";
    
    // Test 2: Database exists
    echo "\n2. Testing database existence...\n";
    $result = $conn->query("SHOW DATABASES LIKE 'prime_home_hub'");
    if ($result->num_rows > 0) {
        echo "✅ Database 'prime_home_hub' exists\n";
        
        // Select database
        $conn->select_db('prime_home_hub');
        
        // Test 3: Tables exist
        echo "\n3. Testing table existence...\n";
        $result = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        if (empty($tables)) {
            echo "❌ No tables found - need to run setup\n";
        } else {
            echo "✅ Found " . count($tables) . " tables:\n";
            foreach ($tables as $table) {
                echo "   - $table\n";
            }
            
            // Test 4: Admin user exists
            echo "\n4. Testing admin user...\n";
            $result = $conn->query("SELECT id, name, email, role FROM users WHERE email = 'admin@primehomehub.com'");
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                echo "✅ Admin user found:\n";
                echo "   ID: " . $admin['id'] . "\n";
                echo "   Name: " . $admin['name'] . "\n";
                echo "   Email: " . $admin['email'] . "\n";
                echo "   Role: " . $admin['role'] . "\n";
            } else {
                echo "❌ Admin user not found\n";
            }
        }
        
    } else {
        echo "❌ Database 'prime_home_hub' does not exist\n";
        echo "   Need to run database setup first\n";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure WAMP/XAMPP is running\n";
    echo "2. Check MySQL service is active\n";
    echo "3. Verify MySQL credentials (root, no password)\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";
?>
