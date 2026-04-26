<?php
/**
 * Prime Home Hub - Fixed Database Setup Script v3
 * Compatible with older PHP versions
 */

echo "=== Prime Home Hub Database Setup (Fixed v3) ===\n\n";

// Check PHP version
echo "PHP Version: " . phpversion() . "\n";
if (version_compare(phpversion(), '5.5.0', '<')) {
    echo "⚠ Warning: PHP version < 5.5.0 detected. Using alternative password hashing.\n";
}

try {
    // Step 1: Connect to MySQL
    echo "1. Connecting to MySQL...\n";
    $conn = new mysqli('localhost', 'root', '', '', 3306);
    
    if ($conn->connect_error) {
        throw new Exception("MySQL Connection Failed: " . $conn->connect_error);
    }
    echo "✓ MySQL connection successful\n";
    
    // Step 2: Create database
    echo "\n2. Creating database...\n";
    $sql = "CREATE DATABASE IF NOT EXISTS prime_home_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql)) {
        echo "✓ Database created/verified\n";
    }
    
    $conn->select_db('prime_home_hub');
    
    // Step 3: Drop existing tables (clean start)
    echo "\n3. Cleaning existing tables...\n";
    $tables_to_drop = [
        'backup_logs', 'analytics', 'mood_boards', 'room_plans', 'wishlist',
        'order_items', 'orders', 'users', 'products', 'categories'
    ];
    
    foreach ($tables_to_drop as $table) {
        $conn->query("DROP TABLE IF EXISTS $table");
    }
    echo "✓ Existing tables dropped\n";
    
    // Step 4: Create tables using direct queries
    echo "\n4. Creating database tables...\n";
    
    // Categories table
    $sql = "CREATE TABLE categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql)) echo "✓ Categories table created\n";
    
    // Products table
    $sql = "CREATE TABLE products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        category_id INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        rating DECIMAL(2, 1) DEFAULT 4.0,
        image VARCHAR(500),
        description TEXT,
        stock INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) echo "✓ Products table created\n";
    
    // Users table
    $sql = "CREATE TABLE users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        city VARCHAR(100),
        country VARCHAR(100),
        postal_code VARCHAR(20),
        role ENUM('customer', 'admin') DEFAULT 'customer',
        is_active BOOLEAN DEFAULT TRUE,
        session_token VARCHAR(64),
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql)) echo "✓ Users table created\n";
    
    // Orders table
    $sql = "CREATE TABLE orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        payment_method ENUM('credit_card', 'bank_transfer', 'cash_on_delivery') DEFAULT 'cash_on_delivery',
        payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
        shipping_address TEXT,
        shipping_cost DECIMAL(10, 2) DEFAULT 0,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) echo "✓ Orders table created\n";
    
    // Order items table
    $sql = "CREATE TABLE order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10, 2) NOT NULL,
        subtotal DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
    )";
    if ($conn->query($sql)) echo "✓ Order items table created\n";
    
    // Wishlist table
    $sql = "CREATE TABLE wishlist (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_wishlist (user_id, product_id)
    )";
    if ($conn->query($sql)) echo "✓ Wishlist table created\n";
    
    // Room plans table
    $sql = "CREATE TABLE room_plans (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        name VARCHAR(255) NOT NULL,
        width INT,
        height INT,
        furniture_data LONGTEXT,
        preview_image LONGTEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) echo "✓ Room plans table created\n";
    
    // Mood boards table
    $sql = "CREATE TABLE mood_boards (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        items_data LONGTEXT,
        preview_image LONGTEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) echo "✓ Mood boards table created\n";
    
    // Analytics table
    $sql = "CREATE TABLE analytics (
        id INT PRIMARY KEY AUTO_INCREMENT,
        event_type VARCHAR(100),
        user_id INT,
        product_id INT,
        event_data JSON,
        ip_address VARCHAR(45),
        user_agent VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql)) echo "✓ Analytics table created\n";
    
    // Backup logs table
    $sql = "CREATE TABLE backup_logs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        backup_file VARCHAR(255),
        backup_size BIGINT,
        status ENUM('success', 'failed') DEFAULT 'success',
        error_message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($conn->query($sql)) echo "✓ Backup logs table created\n";
    
    // Step 5: Insert sample data using direct queries
    echo "\n5. Inserting sample data...\n";
    
    // Categories
    $categories = [
        ['Living Room', 'living-room', 'Couches, tables, storage, and decor for living spaces'],
        ['Bedroom', 'bedroom', 'Beds, nightstands, lamps, and bedroom furniture'],
        ['Office', 'office', 'Desks, chairs, organizers, and office solutions'],
        ['Decor', 'decor', 'Decorative items, art, and accessories']
    ];
    
    foreach ($categories as $cat) {
        $sql = "INSERT INTO categories (name, slug, description) VALUES ('" . 
               $conn->real_escape_string($cat[0]) . "', '" . 
               $conn->real_escape_string($cat[1]) . "', '" . 
               $conn->real_escape_string($cat[2]) . "')";
        $conn->query($sql);
    }
    echo "✓ Categories inserted\n";
    
    // Products
    $products = [
        ['Modern Sofa', 1, 899.00, 4.5, 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop', 'Elegant modern sofa perfect for contemporary living rooms', 15],
        ['Coffee Table', 1, 299.00, 4.2, 'https://images.unsplash.com/photo-1555939594-58d7cb561404?w=400&h=300&fit=crop', 'Stylish wood coffee table with storage', 20],
        ['Wall Art', 1, 199.00, 4.7, 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=400&h=300&fit=crop', 'Abstract wall art for modern spaces', 30],
        ['King Bed Frame', 2, 1299.00, 4.6, 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop', 'Premium king-size bed with storage', 10],
        ['Nightstand', 2, 299.00, 4.3, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=400&h=300&fit=crop', 'Modern nightstand with drawer', 25],
        ['Bedside Lamp', 2, 89.00, 4.4, 'https://images.unsplash.com/photo-1565636192335-14c6b42ce068?w=400&h=300&fit=crop', 'Adjustable bedside lamp with USB charging', 40],
        ['Office Desk', 3, 599.00, 4.5, 'https://images.unsplash.com/photo-1593062096033-9a26b09da705?w=400&h=300&fit=crop', 'Ergonomic office desk with monitor stand', 12],
        ['Office Chair', 3, 399.00, 4.6, 'https://images.unsplash.com/photo-1592078615290-033ee584e267?w=400&h=300&fit=crop', 'Comfortable ergonomic office chair', 18],
        ['Desk Organizer', 3, 49.00, 4.2, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=300&fit=crop', 'Wooden desk organizer set', 50],
        ['Plant Pot', 4, 59.00, 4.3, 'https://images.unsplash.com/photo-1610055945828-949d8edd5d55?w=400&h=300&fit=crop', 'Modern ceramic plant pot', 35],
        ['Mirror', 4, 149.00, 4.5, 'https://images.unsplash.com/photo-1578926314433-b961e6a4d8e7?w=400&h=300&fit=crop', 'Decorative wall mirror', 28],
        ['Throw Pillow', 4, 39.00, 4.4, 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&h=300&fit=crop', 'Comfortable throw pillow set', 45]
    ];
    
    foreach ($products as $product) {
        $sql = "INSERT INTO products (name, category_id, price, rating, image, description, stock) VALUES ('" . 
               $conn->real_escape_string($product[0]) . "', " . 
               $product[1] . ", " . 
               $product[2] . ", " . 
               $product[3] . ", '" . 
               $conn->real_escape_string($product[4]) . "', '" . 
               $conn->real_escape_string($product[5]) . "', " . 
               $product[6] . ")";
        $conn->query($sql);
    }
    echo "✓ Products inserted\n";
    
    // Step 6: Create admin user using direct query
    echo "\n6. Creating admin user...\n";
    
    $admin_email = 'admin@primehomehub.com';
    $admin_password = 'Admin@123456';
    $admin_name = 'Admin User';
    $admin_role = 'admin';
    
    // Check if admin exists
    $result = $conn->query("SELECT id FROM users WHERE email = '" . $conn->real_escape_string($admin_email) . "'");
    
    if ($result && $result->num_rows > 0) {
        echo "ℹ Admin user already exists\n";
    } else {
        // Create password hash (compatible with older PHP)
        if (function_exists('password_hash')) {
            $password_hash = password_hash($admin_password, PASSWORD_BCRYPT);
        } else {
            // Fallback for older PHP versions
            $password_hash = md5($admin_password . 'salt');
            echo "⚠ Using MD5 fallback for password hashing\n";
        }
        
        // Create admin user
        $sql = "INSERT INTO users (name, email, password, role, created_at) VALUES ('" . 
               $conn->real_escape_string($admin_name) . "', '" . 
               $conn->real_escape_string($admin_email) . "', '" . 
               $password_hash . "', '" . 
               $admin_role . "', NOW())";
        
        if ($conn->query($sql)) {
            echo "✓ Admin user created successfully!\n";
            echo "  Email: $admin_email\n";
            echo "  Password: $admin_password\n";
            echo "  ⚠ IMPORTANT: Change password after first login!\n";
        } else {
            echo "✗ Error creating admin: " . $conn->error . "\n";
        }
    }
    
    // Step 7: Verify setup
    echo "\n7. Verifying setup...\n";
    
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    if ($result) {
        echo "✓ Categories: " . $result->fetch_assoc()['count'] . "\n";
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    if ($result) {
        echo "✓ Products: " . $result->fetch_assoc()['count'] . "\n";
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        echo "✓ Users: " . $result->fetch_assoc()['count'] . "\n";
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
    
} catch (Exception $e) {
    echo "✗ Setup failed: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure MySQL/WAMP server is running\n";
    echo "2. Check MySQL credentials (default: root, no password)\n";
    echo "3. Verify file permissions\n";
    exit(1);
}

?>
