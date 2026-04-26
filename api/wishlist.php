<?php
/**
 * Prime Home Hub - Wishlist API
 * Handles saving and managing user wishlist items
 */

header('Content-Type: application/json');

require_once '../includes/database.php';
require_once '../includes/auth.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$product_id = $_GET['product_id'] ?? null;

$input = json_decode(file_get_contents("php://input"), true) ?? $_POST;
$user = get_authenticated_user();

// Route requests
switch ($action) {
    case 'list':
        get_wishlist();
        break;

    case 'add':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        add_to_wishlist($input);
        break;

    case 'remove':
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
        remove_from_wishlist($product_id);
        break;

    case 'check':
        if (!$product_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID required']);
            exit;
        }
        check_wishlist($product_id);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
        exit;
}

/**
 * Get user's wishlist
 */
function get_wishlist() {
    global $db, $user;

    $items = $db->fetch_all(
        "SELECT w.id, w.product_id, w.created_at, p.name, p.price, p.image_url, p.rating, p.stock
         FROM wishlist w
         JOIN products p ON w.product_id = p.id
         WHERE w.user_id = ? AND p.status = 'active'
         ORDER BY w.created_at DESC",
        [$user['id']]
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($items),
        'items' => $items
    ]);
}

/**
 * Add product to wishlist
 */
function add_to_wishlist($input) {
    global $db, $user;

    $product_id = (int)($input['product_id'] ?? 0);

    if ($product_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Product ID required']);
        return;
    }

    // Check product exists
    $product = $db->fetch_one(
        "SELECT id FROM products WHERE id = ? AND status = 'active'",
        [$product_id]
    );

    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        return;
    }

    // Check if already in wishlist
    $existing = $db->fetch_one(
        "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?",
        [$user['id'], $product_id]
    );

    if ($existing) {
        http_response_code(409);
        echo json_encode(['error' => 'Product already in wishlist']);
        return;
    }

    $result = $db->execute(
        "INSERT INTO wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())",
        [$user['id'], $product_id]
    );

    if ($result['success']) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Added to wishlist',
            'wishlist_id' => $result['insert_id']
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add to wishlist']);
    }
}

/**
 * Remove product from wishlist
 */
function remove_from_wishlist($product_id) {
    global $db, $user;

    $product_id = (int)$product_id;

    $result = $db->execute(
        "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?",
        [$user['id'], $product_id]
    );

    if ($result['success'] && $result['affected_rows'] > 0) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found in wishlist']);
    }
}

/**
 * Check if product is in wishlist
 */
function check_wishlist($product_id) {
    global $db, $user;

    $product_id = (int)$product_id;

    $item = $db->fetch_one(
        "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?",
        [$user['id'], $product_id]
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'in_wishlist' => $item !== null
    ]);
}
?>
