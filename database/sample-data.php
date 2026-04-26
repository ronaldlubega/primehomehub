<?php
/**
 * Prime Home Hub - Sample Data for Testing
 * Run this file to populate database with test data
 * Usage: php database/sample-data.php
 */

require_once __DIR__ . '/../includes/database.php';

echo "=== Prime Home Hub - Sample Data Loader ===\n\n";

// Test database connection
if (!$db) {
    echo "❌ Database connection failed!\n";
    exit;
}

echo "✅ Database connection successful\n\n";

// Check if tables exist
$tables = $db->fetch_all(
    "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'prime_home_hub'"
);

echo "✅ Found " . count($tables) . " tables\n";
if (count($tables) < 13) {
    echo "❌ Not all tables created. Please import database.sql first!\n";
    exit;
}

echo "\n=== Loading Sample Data ===\n\n";

// 1. Clear existing sample data (optional)
$response = prompt("Clear existing sample data? (y/n): ");
if (strtolower($response) === 'y') {
    echo "Clearing existing data...\n";
    $db->execute("DELETE FROM activity_logs");
    $db->execute("DELETE FROM order_items");
    $db->execute("DELETE FROM orders");
    $db->execute("DELETE FROM wishlist");
    $db->execute("DELETE FROM reviews");
    $db->execute("DELETE FROM shipping_info");
    $db->execute("DELETE FROM room_plans");
    $db->execute("DELETE FROM mood_boards");
    $db->execute("DELETE FROM products");
    $db->execute("DELETE FROM categories");
    $db->execute("DELETE FROM users");
    echo "✅ Data cleared\n\n";
}

// 2. Insert categories
echo "Loading categories...\n";
$categories = [
    ['name' => 'Living Room', 'slug' => 'living-room', 'description' => 'Comfortable seating and entertainment furniture'],
    ['name' => 'Bedroom', 'slug' => 'bedroom', 'description' => 'Beds, wardrobes, and bedroom essentials'],
    ['name' => 'Office', 'slug' => 'office', 'description' => 'Desks, chairs, and office furniture'],
    ['name' => 'Decor', 'slug' => 'decor', 'description' => 'Decorative items and accents']
];

foreach ($categories as $cat) {
    $existing = $db->fetch_one("SELECT id FROM categories WHERE slug = ?", [$cat['slug']]);
    if (!$existing) {
        $db->execute(
            "INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)",
            [$cat['name'], $cat['slug'], $cat['description']]
        );
        echo "  ✓ " . $cat['name'] . "\n";
    }
}

// 3. Insert products
echo "\nLoading products...\n";
$products = [
    ['name' => 'Modern Leather Sofa', 'category' => 'Living Room', 'price' => 1500000, 'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400', 'description' => 'Premium leather sofa with comfortable seating for 3+ people', 'stock' => 10],
    ['name' => 'Elegant Dining Table', 'category' => 'Living Room', 'price' => 2000000, 'image' => 'https://images.unsplash.com/photo-1551632786-4adcccfe66fa?w=400', 'description' => 'Wooden dining table perfect for family gatherings', 'stock' => 8],
    ['name' => 'Queen Size Bed Frame', 'category' => 'Bedroom', 'price' => 1200000, 'image' => 'https://images.unsplash.com/photo-1540932239986-fd128078519b?w=400', 'description' => 'Sturdy queen size bed frame with metal support', 'stock' => 15],
    ['name' => 'Wooden Wardrobe', 'category' => 'Bedroom', 'price' => 1800000, 'image' => 'https://images.unsplash.com/photo-1578500494198-246f612d03b3?w=400', 'description' => 'Spacious wooden wardrobe with multiple shelves', 'stock' => 6],
    ['name' => 'Executive Office Desk', 'category' => 'Office', 'price' => 1300000, 'image' => 'https://images.unsplash.com/photo-1593062096033-9a26b09da705?w=400', 'description' => 'Professional desk with storage compartments', 'stock' => 12],
    ['name' => 'Ergonomic Office Chair', 'category' => 'Office', 'price' => 800000, 'image' => 'https://images.unsplash.com/photo-1598618664857-1c86b60ae5d0?w=400', 'description' => 'Comfortable chair with lumbar support', 'stock' => 20],
    ['name' => 'Coffee Table', 'category' => 'Living Room', 'price' => 600000, 'image' => 'https://images.unsplash.com/photo-1532372320572-cda40c42a183?w=400', 'description' => 'Modern glass coffee table for living rooms', 'stock' => 25],
    ['name' => 'Wall Mirror', 'category' => 'Decor', 'price' => 400000, 'image' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?w=400', 'description' => 'Decorative wall mirror with wooden frame', 'stock' => 30],
    ['name' => 'Bedside Table', 'category' => 'Bedroom', 'price' => 500000, 'image' => 'https://images.unsplash.com/photo-1516939884455-1245ba92dfaf?w=400', 'description' => 'Compact bedside table with drawer', 'stock' => 18],
    ['name' => 'Bookshelf', 'category' => 'Office', 'price' => 1100000, 'image' => 'https://images.unsplash.com/photo-1585399866281-e5242caab36c?w=400', 'description' => 'Wooden bookshelf with 5 compartments', 'stock' => 10],
    ['name' => 'Throw Pillow Set', 'category' => 'Decor', 'price' => 250000, 'image' => 'https://images.unsplash.com/photo-1567016432779-094269c27277?w=400', 'description' => 'Set of 4 decorative throw pillows', 'stock' => 50],
    ['name' => 'Floor Lamp', 'category' => 'Decor', 'price' => 350000, 'image' => 'https://images.unsplash.com/photo-1565636192335-14c08be7dd02?w=400', 'description' => 'Modern floor lamp with adjustable height', 'stock' => 22]
];

foreach ($products as $prod) {
    $cat_id = $db->fetch_one(
        "SELECT id FROM categories WHERE name = ?",
        [$prod['category']]
    );

    if ($cat_id) {
        $existing = $db->fetch_one(
            "SELECT id FROM products WHERE name = ?",
            [$prod['name']]
        );

        if (!$existing) {
            $db->execute(
                "INSERT INTO products (name, category_id, price, image, description, stock, rating) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$prod['name'], $cat_id['id'], $prod['price'], $prod['image'], $prod['description'], $prod['stock'], 4.2 + mt_rand(0, 5) / 10]
            );
            echo "  ✓ " . $prod['name'] . " (" . $prod['category'] . ")\n";
        }
    }
}

// 4. Create test users
echo "\nLoading test users...\n";
$test_users = [
    [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'phone' => '+256700123456',
        'role' => 'customer'
    ],
    [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'password' => password_hash('password123', PASSWORD_BCRYPT),
        'phone' => '+256701234567',
        'role' => 'customer'
    ],
    [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => password_hash('admin123', PASSWORD_BCRYPT),
        'phone' => '+256702345678',
        'role' => 'admin'
    ]
];

foreach ($test_users as $user) {
    $existing = $db->fetch_one(
        "SELECT id FROM users WHERE email = ?",
        [$user['email']]
    );

    if (!$existing) {
        $db->execute(
            "INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)",
            [$user['name'], $user['email'], $user['password'], $user['phone'], $user['role']]
        );
        echo "  ✓ " . $user['email'] . " (Password: password123)\n";
    }
}

echo "\n✅ Sample data loaded successfully!\n";
echo "\n=== Test Accounts ===\n";
echo "Customer: john@example.com / password123\n";
echo "Customer: jane@example.com / password123\n";
echo "Admin: admin@example.com / admin123\n";

/**
 * Simple prompt function for CLI
 */
function prompt($text) {
    if (php_sapi_name() !== 'cli') {
        return 'n';
    }
    echo $text;
    return trim(fgets(STDIN));
}
?>
