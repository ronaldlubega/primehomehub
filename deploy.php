<?php
/**
 * Prime Home Hub - Final Deployment Script
 * This script sets up everything needed for production deployment
 */

echo "=== Prime Home Hub Deployment Script ===\n\n";

// Step 1: Check requirements
echo "1. Checking requirements...\n";

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die("Error: PHP 7.4.0 or higher required. Current version: " . PHP_VERSION . "\n");
}
echo "✓ PHP Version: " . PHP_VERSION . "\n";

// Check required extensions
$required_extensions = ['mysqli', 'json', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        die("Error: Required PHP extension '$ext' is not loaded.\n");
    }
    echo "✓ Extension '$ext' loaded\n";
}

// Step 2: Database setup
echo "\n2. Setting up database...\n";

try {
    require_once 'includes/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "✓ Database connection successful\n";
        
        // Check if tables exist
        $result = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        $required_tables = ['categories', 'products', 'users', 'orders', 'wishlist'];
        foreach ($required_tables as $table) {
            if (in_array($table, $tables)) {
                echo "✓ Table '$table' exists\n";
            } else {
                echo "⚠ Table '$table' missing - run database.sql first\n";
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please configure database settings in includes/database.php\n";
}

// Step 3: Check file permissions
echo "\n3. Checking file permissions...\n";

$writable_dirs = ['api', 'includes'];
foreach ($writable_dirs as $dir) {
    if (is_writable($dir)) {
        echo "✓ Directory '$dir' is writable\n";
    } else {
        echo "⚠ Directory '$dir' may not be writable\n";
    }
}

// Step 4: Create admin user if needed
echo "\n4. Setting up admin user...\n";

if (isset($conn)) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "Admin user not found. Please run create-admin.php to create admin account.\n";
    } else {
        echo "✓ Admin user exists\n";
    }
}

// Step 5: Check configuration files
echo "\n5. Checking configuration...\n";

$config_files = [
    'includes/database.php' => 'Database configuration',
    'includes/auth.php' => 'Authentication system',
    'api/products.php' => 'Products API',
    'api/users.php' => 'Users API',
    'api/orders.php' => 'Orders API'
];

foreach ($config_files as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $description ($file)\n";
    } else {
        echo "✗ Missing: $description ($file)\n";
    }
}

// Step 6: Frontend files check
echo "\n6. Checking frontend files...\n";

$frontend_files = [
    'index.html' => 'Homepage',
    'styles.css' => 'Main stylesheet',
    'js/app.js' => 'Main JavaScript',
    'pages/shop.html' => 'Shop page',
    'pages/cart.html' => 'Cart page',
    'pages/admin-dashboard.html' => 'Admin dashboard',
    'pages/user-dashboard.html' => 'User dashboard',
    'pages/room-planner.html' => 'Room planner',
    'pages/visualizer.html' => 'Product visualizer'
];

foreach ($frontend_files as $file => $description) {
    if (file_exists($file)) {
        echo "✓ $description ($file)\n";
    } else {
        echo "✗ Missing: $description ($file)\n";
    }
}

// Step 7: Security check
echo "\n7. Security recommendations...\n";

$security_checks = [
    'Database password should be strong and not empty',
    'Admin password should be changed from default',
    'HTTPS should be enabled in production',
    'Error reporting should be disabled in production',
    'File uploads should be restricted if enabled'
];

foreach ($security_checks as $check) {
    echo "⚠ $check\n";
}

echo "\n=== Deployment Check Complete ===\n";
echo "\nNext steps:\n";
echo "1. Import database.sql if not done already\n";
echo "2. Run create-admin.php to create admin account\n";
echo "3. Update admin password from default\n";
echo "4. Test all functionality at http://localhost/furn/\n";
echo "5. Configure HTTPS for production\n";
echo "6. Set up proper error logging\n";

?>
