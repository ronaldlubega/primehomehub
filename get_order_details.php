<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'officer'])) {
        throw new Exception('Unauthorized: Please log in as an administrator.', 403);
    }

    if (!isset($_GET['id'])) {
        throw new Exception('Invalid Request: Order ID is missing.', 400);
    }

    $db = new Database();
    $orderId = (int)$_GET['id'];

    // Fetch order with its own denormalized customer info (No join needed for core details)
    $order = $db->fetch_one(
        "SELECT 
            id, order_number, total_amount, status, payment_method, 
            payment_status, shipping_address, shipping_cost, notes, created_at,
            customer_name, customer_email, customer_phone as user_phone
         FROM orders 
         WHERE id = ?",
        [$orderId]
    );

    if (!$order) {
        $dbError = $db->get_error();
        throw new Exception("Order #$orderId exists in database but detailed fetch failed. " . ($dbError ? "SQL Error: $dbError" : "Possible field mismatch."), 500);
    }

    // Fetch order items (Left join in case product was removed)
    $items = $db->fetch_all(
        "SELECT oi.product_id, oi.quantity, oi.unit_price, oi.subtotal, 
                p.name as product_name, p.image_url 
         FROM order_items oi 
         LEFT JOIN products p ON oi.product_id = p.id 
         WHERE oi.order_id = ?",
        [$orderId]
    );

    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
