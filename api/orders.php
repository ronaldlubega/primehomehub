<?php
/**
 * Prime Home Hub - Orders API
 * Handles order creation, retrieval, and tracking
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

require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$order_id = $_GET['id'] ?? null;

$input = json_decode(file_get_contents("php://input"), true) ?? $_POST;
$user = get_authenticated_user();

// Route requests
switch ($action) {
    case 'list':
        list_user_orders();
        break;

    case 'get':
        if (!$order_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Order ID required']);
            exit;
        }
        get_order($order_id);
        break;

    case 'create':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        create_order($input);
        break;

    case 'update-status':
        if ($method !== 'POST' && $method !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        if (!$order_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Order ID required']);
            exit;
        }
        require_admin();
        update_order_status($order_id, $input);
        break;

    case 'cancel':
        if (!$order_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Order ID required']);
            exit;
        }
        cancel_order($order_id);
        break;

    case 'payment':
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        if (!$order_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Order ID required']);
            exit;
        }
        process_payment($order_id, $input);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
        exit;
}

/**
 * List user's orders
 */
function list_user_orders() {
    global $db, $user;

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, (int)($_GET['limit'] ?? 10));
    $offset = ($page - 1) * $limit;

    $status = $_GET['status'] ?? '';

    // Build query
    $where = "o.user_id = ?";
    $params = [$user['id']];

    if (!empty($status)) {
        $where .= " AND o.status = ?";
        $params[] = $status;
    }

    // Get total
    $count = $db->fetch_one(
        "SELECT COUNT(*) as count FROM orders o WHERE $where",
        $params
    );
    $total = $count['count'] ?? 0;

    // Get orders
    $params[] = $limit;
    $params[] = $offset;

    $orders = $db->fetch_all(
        "SELECT o.id, o.order_number, o.user_id, o.total_amount, o.status, o.payment_status, o.created_at, o.updated_at
         FROM orders o 
         WHERE $where
         ORDER BY o.created_at DESC
         LIMIT ? OFFSET ?",
        $params
    );

    // Get items for each order
    foreach ($orders as &$order) {
        $order['items'] = $db->fetch_all(
            "SELECT oi.id, oi.product_id, oi.quantity, oi.price, p.name, p.image_url 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = ?",
            [$order['id']]
        );
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total
        ]
    ]);
}

/**
 * Get single order
 */
function get_order($order_id) {
    global $db, $user;

    $order = $db->fetch_one(
        "SELECT o.* FROM orders o WHERE o.id = ? AND o.user_id = ?",
        [$order_id, $user['id']]
    );

    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        return;
    }

    // Get items
    $order['items'] = $db->fetch_all(
        "SELECT oi.id, oi.product_id, oi.quantity, oi.price, p.name, p.image_url, p.description
         FROM order_items oi 
         JOIN products p ON oi.product_id = p.id 
         WHERE oi.order_id = ?",
        [$order_id]
    );

    // Get shipping info
    $order['shipping'] = $db->fetch_one(
        "SELECT * FROM shipping_info WHERE order_id = ?",
        [$order_id]
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'order' => $order
    ]);
}

/**
 * Create new order
 */
function create_order($input) {
    global $db, $user;

    $items = $input['items'] ?? [];
    $shipping_address = $input['shipping_address'] ?? '';
    $shipping_city = $input['shipping_city'] ?? '';
    $shipping_country = $input['shipping_country'] ?? '';
    $shipping_postal = $input['shipping_postal'] ?? '';

    if (empty($items)) {
        http_response_code(400);
        echo json_encode(['error' => 'No items in order']);
        return;
    }

    // Calculate total
    $total = 0;
    $order_items = [];

    foreach ($items as $item) {
        $product_id = (int)$item['product_id'];
        $quantity = (int)$item['quantity'];

        if ($quantity <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid quantity']);
            return;
        }

        $product = $db->fetch_one(
            "SELECT price, stock FROM products WHERE id = ?",
            [$product_id]
        );

        if (!$product) {
            http_response_code(400);
            echo json_encode(['error' => "Product $product_id not found"]);
            return;
        }

        if ($product['stock'] < $quantity) {
            http_response_code(400);
            echo json_encode(['error' => "Insufficient stock for product $product_id"]);
            return;
        }

        $item_total = $product['price'] * $quantity;
        $total += $item_total;

        $order_items[] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $product['price']
        ];
    }

    // Begin transaction
    $db->begin_transaction();

    try {
        // Generate order number
        $order_number = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);

        // Create order
        $db->execute(
            "INSERT INTO orders (user_id, order_number, total_amount, status, payment_status, created_at, updated_at)
             VALUES (?, ?, ?, 'pending', 'pending', NOW(), NOW())",
            [$user['id'], $order_number, $total]
        );

        $order_id = $db->last_insert_id();

        // Add order items
        foreach ($order_items as $item) {
            $db->execute(
                "INSERT INTO order_items (order_id, product_id, quantity, price)
                 VALUES (?, ?, ?, ?)",
                [$order_id, $item['product_id'], $item['quantity'], $item['price']]
            );

            // Update stock
            $db->execute(
                "UPDATE products SET stock = stock - ? WHERE id = ?",
                [$item['quantity'], $item['product_id']]
            );
        }

        // Add shipping info
        if (!empty($shipping_address)) {
            $db->execute(
                "INSERT INTO shipping_info (order_id, address, city, country, postal_code, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [$order_id, $shipping_address, $shipping_city, $shipping_country, $shipping_postal]
            );
        }

        // Log activity
        log_activity($user['id'], 'order_created', "Order $order_number created");

        $db->commit();

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $order_id,
            'order_number' => $order_number,
            'total' => $total
        ]);
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create order']);
    }
}

/**
 * Update order status (admin only)
 */
function update_order_status($order_id, $input) {
    global $db;

    $status = trim($input['status'] ?? '');
    $allowed_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

    if (empty($status) || !in_array($status, $allowed_statuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        return;
    }

    $result = $db->execute(
        "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?",
        [$status, $order_id]
    );

    if ($result['success']) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Order status updated']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update order']);
    }
}

/**
 * Cancel order
 */
function cancel_order($order_id) {
    global $db, $user;

    // Check order belongs to user
    $order = $db->fetch_one(
        "SELECT status FROM orders WHERE id = ? AND user_id = ?",
        [$order_id, $user['id']]
    );

    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        return;
    }

    if ($order['status'] !== 'pending') {
        http_response_code(400);
        echo json_encode(['error' => 'Can only cancel pending orders']);
        return;
    }

    // Begin transaction
    $db->begin_transaction();

    try {
        // Update order status
        $db->execute(
            "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?",
            [$order_id]
        );

        // Restore stock
        $items = $db->fetch_all(
            "SELECT product_id, quantity FROM order_items WHERE order_id = ?",
            [$order_id]
        );

        foreach ($items as $item) {
            $db->execute(
                "UPDATE products SET stock = stock + ? WHERE id = ?",
                [$item['quantity'], $item['product_id']]
            );
        }

        $db->commit();

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Order cancelled successfully']);
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to cancel order']);
    }
}

/**
 * Process payment
 */
function process_payment($order_id, $input) {
    global $db, $user;

    // Check order
    $order = $db->fetch_one(
        "SELECT * FROM orders WHERE id = ? AND user_id = ?",
        [$order_id, $user['id']]
    );

    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        return;
    }

    if ($order['payment_status'] !== 'pending') {
        http_response_code(400);
        echo json_encode(['error' => 'Payment already processed']);
        return;
    }

    // In production, integrate with Stripe/PayPal
    // For now, just mark as paid
    $payment_method = $input['payment_method'] ?? 'card';
    $transaction_id = 'TXN-' . bin2hex(random_bytes(8));

    $db->execute(
        "UPDATE orders SET payment_status = 'completed', updated_at = NOW() WHERE id = ?",
        [$order_id]
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully',
        'transaction_id' => $transaction_id,
        'amount' => $order['total_amount']
    ]);
}
?>
