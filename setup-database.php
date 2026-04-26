<?php
/**
 * Prime Home Hub - Complete Database Setup Script
 * This script will:
 * 1. Create the database
 * 2. Import the schema
 * 3. Create admin user
 * 4. Test connectivity
 */

echo "=== Prime Home Hub Database Setup ===\n\n";

// Step 1: Test MySQL connection
echo "1. Testing MySQL connection...\n";

try {
    // Connect to MySQL without specifying database first
    $conn = new mysqli('localhost', 'root', '', '', 3306);
    
    if ($conn->connect_error) {
        throw new Exception("MySQL Connection Failed: " . $conn->connect_error);
    }
    echo "✓ MySQL connection successful\n";
    
    // Step 2: Create database if not exists
    echo "\n2. Creating database...\n";
    
    $sql = "CREATE DATABASE IF NOT EXISTS prime_home_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql)) {
        echo "✓ Database 'prime_home_hub' created/verified\n";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db('prime_home_hub');
    
    // Step 3: Import schema
    echo "\n3. Importing database schema...\n";
    
    // Read the SQL file
    $sql_file = __DIR__ . '/database/database.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("Database schema file not found: $sql_file");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Remove the CREATE DATABASE and USE statements since we already created it
    $sql_content = preg_replace('/^CREATE DATABASE.*?\n.*?\n.*?\n/m', '', $sql_content);
    $sql_content = preg_replace('/^USE prime_home_hub;\n/m', '', $sql_content);
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || preg_match('/^--/', $statement)) {
            continue;
        }
        
        if ($conn->query($statement)) {
            $executed++;
        } else {
            $errors++;
            echo "⚠ SQL Warning: " . $conn->error . "\n";
            echo "Statement: " . substr($statement, 0, 100) . "...\n";
        }
    }
    
    echo "✓ Schema imported: $executed statements executed\n";
    if ($errors > 0) {
        echo "⚠ $errors warnings encountered (usually normal)\n";
    }
    
    // Step 4: Verify tables
    echo "\n4. Verifying database tables...\n";
    
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    $required_tables = [
        'categories', 'products', 'users', 'orders', 'order_items', 
        'wishlist', 'room_plans', 'mood_boards', 'analytics', 'backup_logs'
    ];
    
    foreach ($required_tables as $table) {
        if (in_array($table, $tables)) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Missing table '$table'\n";
        }
    }
    
    // Step 5: Create admin user
    echo "\n5. Creating admin user...\n";
    
    $admin_email = 'admin@primehomehub.com';
    $admin_password = 'Admin@123456';
    $admin_name = 'Admin User';
    $admin_role = 'admin';
    
    // Check if admin exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "ℹ Admin user already exists\n";
        $admin = $result->fetch_assoc();
        echo "  Admin ID: " . $admin['id'] . "\n";
        echo "  Email: $admin_email\n";
    } else {
        // Create admin user
        $password_hash = password_hash($admin_password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, password, role, created_at) 
             VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->bind_param("ssss", $admin_name, $admin_email, $password_hash, $admin_role);
        
        if ($stmt->execute()) {
            $admin_id = $stmt->insert_id;
            echo "✓ Admin user created successfully!\n";
            echo "  Admin ID: $admin_id\n";
            echo "  Email: $admin_email\n";
            echo "  Password: $admin_password\n";
            echo "  ⚠ IMPORTANT: Change password after first login!\n";
        } else {
            echo "✗ Error creating admin: " . $stmt->error . "\n";
        }
    }
    
    // Step 6: Test sample data
    echo "\n6. Verifying sample data...\n";
    
    // Check categories
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    $categories_count = $result->fetch_assoc()['count'];
    echo "✓ Categories: $categories_count\n";
    
    // Check products
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    $products_count = $result->fetch_assoc()['count'];
    echo "✓ Products: $products_count\n";
    
    // Step 7: Final test
    echo "\n7. Testing database operations...\n";
    
    try {
        // Test a simple query
        $result = $conn->query("SELECT name, price FROM products LIMIT 3");
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo "✓ Database queries working correctly\n";
        echo "  Sample products:\n";
        foreach ($products as $product) {
            echo "    - {$product['name']}: \${$product['price']}\n";
        }
    } catch (Exception $e) {
        echo "✗ Database query test failed: " . $e->getMessage() . "\n";
    }
    
    $conn->close();
    
    echo "\n=== Database Setup Complete! ===\n";
    echo "\n🎉 Your Prime Home Hub database is ready!\n";
    echo "\nNext steps:\n";
    echo "1. Test your website at http://localhost/furn/\n";
    echo "2. Login to admin panel:\n";
    echo "   URL: http://localhost/furn/pages/admin-dashboard.html\n";
    echo "   Email: $admin_email\n";
    echo "   Password: $admin_password\n";
    echo "3. Change the admin password after first login\n";
    echo "4. Test user registration and shopping features\n";
    
} catch (Exception $e) {
    echo "✗ Setup failed: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure MySQL/WAMP server is running\n";
    echo "2. Check MySQL credentials (default: root, no password)\n";
    echo "3. Verify file permissions\n";
    echo "4. Check if database/sql files exist\n";
    exit(1);
}

?>
