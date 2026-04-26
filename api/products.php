<?php
/**
 * Prime Home Hub - Products API
 * Handles product listings, filtering, search, and admin management
 */

header('Content-Type: application/json');

require_once '../includes/database.php';
require_once '../includes/auth.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$product_id = $_GET['id'] ?? null;

$input = json_decode(file_get_contents("php://input"), true) ?? $_POST;

// Route requests
switch ($action) {
    case 'list':
        list_products();
        break;

    case 'get':
        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID required']);
            exit;
        }
        get_product($product_id);
        break;

    case 'search':
        search_products($_GET['q'] ?? '');
        break;

    case 'create':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        require_admin();
        create_product($input);
        break;

    case 'update':
        if ($method !== 'POST' && $method !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID required']);
            exit;
        }
        require_admin();
        update_product($product_id, $input);
        break;

    case 'delete':
        if ($method !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID required']);
            exit;
        }
        require_admin();
        delete_product($product_id);
        break;

    case 'by-category':
        $category = $_GET['category'] ?? '';
        list_products_by_category($category);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
        exit;
}

/**
 * List all products with pagination and filters
 */
function list_products() {
    global $db;

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, (int)($_GET['limit'] ?? 20));
    $offset = ($page - 1) * $limit;
    
    $category = $_GET['category'] ?? '';
    $min_price = (float)($_GET['min_price'] ?? 0);
    $max_price = (float)($_GET['max_price'] ?? PHP_INT_MAX);
    $sort = $_GET['sort'] ?? 'name';
    $order = strtoupper($_GET['order'] ?? 'ASC');

    // Build query
    $where = "p.status = 'active'";
    $params = [];

    if (!empty($category)) {
        $where .= " AND p.category_id = ?";
        $params[] = $category;
    }

    if ($min_price > 0) {
        $where .= " AND p.price >= ?";
        $params[] = $min_price;
    }

    if ($max_price < PHP_INT_MAX) {
        $where .= " AND p.price <= ?";
        $params[] = $max_price;
    }

    // Safe sort/order
    $allowed_sorts = ['name', 'price', 'rating', 'created_at'];
    $sort = in_array($sort, $allowed_sorts) ? $sort : 'name';
    $order = in_array($order, ['ASC', 'DESC']) ? $order : 'ASC';

    // Get total count
    $count_query = "SELECT COUNT(*) as count FROM products p WHERE $where";
    $count_result = $db->fetch_one($count_query, $params);
    $total = $count_result['count'] ?? 0;

    // Get products
    $sql = "SELECT p.id, p.name, p.description, p.price, p.image_url, p.category_id, p.rating, p.stock
            FROM products p 
            WHERE $where
            ORDER BY p.$sort $order
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;

    $products = $db->fetch_all($sql, $params);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $products,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

/**
 * Get single product
 */
function get_product($product_id) {
    global $db;

    $product = $db->fetch_one(
        "SELECT p.*, c.name as category_name 
         FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.id = ? AND p.status = 'active'",
        [$product_id]
    );

    if ($product) {
        // Get reviews
        $reviews = $db->fetch_all(
            "SELECT id, user_id, rating, comment, created_at FROM reviews WHERE product_id = ? ORDER BY created_at DESC",
            [$product_id]
        );

        $product['reviews'] = $reviews;

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $product
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
}

/**
 * Search products
 */
function search_products($query) {
    global $db;

    if (empty($query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Search query required']);
        return;
    }

    $search_term = '%' . $query . '%';

    $products = $db->fetch_all(
        "SELECT id, name, description, price, image_url, category_id, rating, stock 
         FROM products 
         WHERE (name LIKE ? OR description LIKE ?) AND status = 'active'
         LIMIT 50",
        [$search_term, $search_term]
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'query' => $query,
        'results' => $products
    ]);
}

/**
 * Create product (admin only)
 */
function create_product($input) {
    global $db;

    $name = trim($input['name'] ?? '');
    $description = trim($input['description'] ?? '');
    $price = (float)($input['price'] ?? 0);
    $category_id = (int)($input['category_id'] ?? 0);
    $image_url = trim($input['image_url'] ?? '');
    $stock = (int)($input['stock'] ?? 0);

    // Validate
    if (empty($name) || empty($description) || $price <= 0 || $category_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing or invalid required fields']);
        return;
    }

    // Check category exists
    $category = $db->fetch_one("SELECT id FROM categories WHERE id = ?", [$category_id]);
    if (!$category) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid category']);
        return;
    }

    $result = $db->execute(
        "INSERT INTO products (name, description, price, category_id, image_url, stock, status, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())",
        [$name, $description, $price, $category_id, $image_url, $stock]
    );

    if ($result['success']) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Product created',
            'product_id' => $result['insert_id']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create product']);
    }
}

/**
 * Update product (admin only)
 */
function update_product($product_id, $input) {
    global $db;

    $fields = [];
    $params = [];

    if (isset($input['name'])) {
        $fields[] = "name = ?";
        $params[] = trim($input['name']);
    }
    if (isset($input['description'])) {
        $fields[] = "description = ?";
        $params[] = trim($input['description']);
    }
    if (isset($input['price'])) {
        $fields[] = "price = ?";
        $params[] = (float)$input['price'];
    }
    if (isset($input['stock'])) {
        $fields[] = "stock = ?";
        $params[] = (int)$input['stock'];
    }
    if (isset($input['image_url'])) {
        $fields[] = "image_url = ?";
        $params[] = trim($input['image_url']);
    }
    if (isset($input['status'])) {
        $fields[] = "status = ?";
        $params[] = $input['status'];
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No fields to update']);
        return;
    }

    $params[] = $product_id;
    $sql = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = ?";

    $result = $db->execute($sql, $params);

    if ($result['success']) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Product updated']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update product']);
    }
}

/**
 * Delete product (admin only)
 */
function delete_product($product_id) {
    global $db;

    // Soft delete
    $result = $db->execute(
        "UPDATE products SET status = 'deleted' WHERE id = ?",
        [$product_id]
    );

    if ($result['success']) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Product deleted']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete product']);
    }
}

/**
 * List products by category
 */
function list_products_by_category($category_id) {
    global $db;

    if (empty($category_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Category ID required']);
        return;
    }

    $products = $db->fetch_all(
        "SELECT id, name, description, price, image_url, rating, stock 
         FROM products 
         WHERE category_id = ? AND status = 'active'
         ORDER BY name ASC",
        [$category_id]
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'category_id' => $category_id,
        'products' => $products
    ]);
}
?>
