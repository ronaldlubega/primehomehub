<?php
session_start();
// Use the feature-rich mysqli-based Database class meant for transactional orders
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$success = false;
$orderNo = '';
$error = '';

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->begin_transaction();

        $userId = $_SESSION['user_id'];
        $orderNo = generate_order_number($db);
        $customerName = $_POST['customer_name'] ?? '';
        $customerEmail = $_POST['customer_email'] ?? '';
        $customerPhone = $_POST['customer_phone'] ?? '';
        $deliveryAddress = $_POST['delivery_address'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? 'cash_on_delivery';
        $totalAmount = (float)($_POST['total'] ?? 0);
        $shippingCost = 50000; // Fixed delivery cost from cart.php
        
        // Get items from JSON
        $items = json_decode($_POST['items'] ?? '[]', true);

        if (empty($items)) {
            throw new Exception("Cart is empty");
        }

        // 1. Create Order with denormalized customer info
        $orderSql = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, order_number, total_amount, shipping_address, payment_method, shipping_cost, status, payment_status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid')";
        
        $orderResult = $db->execute($orderSql, [
            $userId, 
            $customerName,
            $customerEmail,
            $customerPhone,
            $orderNo, 
            $totalAmount, 
            $deliveryAddress, 
            $paymentMethod, 
            $shippingCost
        ]);

        if (!$orderResult['success']) {
            throw new Exception("Failed to save order: " . $orderResult['error']);
        }

        $orderId = $orderResult['insert_id'];

        // 2. Save Order Items
        $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                    VALUES (?, ?, ?, ?, ?)";
        
        foreach ($items as $item) {
            $itemResult = $db->execute($itemSql, [
                $orderId, 
                $item['id'], 
                $item['quantity'], 
                $item['price'], 
                $item['subtotal']
            ]);
            
            if (!$itemResult['success']) {
                throw new Exception("Failed to save order item: " . $itemResult['error']);
            }
        }

        // 3. Optional: Add to shipping_info table if required
        $shippingSql = "INSERT INTO shipping_info (order_id, address, city, country, postal_code) 
                        VALUES (?, ?, ?, ?, ?)";
        $db->execute($shippingSql, [$orderId, $deliveryAddress, 'Kampala', 'Uganda', '256']);

        $db->commit();
        
        // Create Notification for Admin
        create_notification(
            'order', 
            'New Order Placed', 
            "Customer $customerName placed order #$orderNo for " . format_price($totalAmount)
        );
        
        // Clear Cart
        unset($_SESSION['cart']);
        unset($_SESSION['cart_count']);
        
        $success = true;

    } catch (Exception $e) {
        $db->rollback();
        $error = $e->getMessage();
    }
} else {
    // If accessed directly without post, redirect to cart
    header('Location: cart.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Century Gothic', 'Segoe UI', sans-serif;
            background: var(--primary-gradient) fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .status-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 800px;
            aspect-ratio: 16 / 9;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .status-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            display: inline-block;
        }

        .success-icon { color: #00ff88; }
        .error-icon { color: #ff4444; }

        .brand-title {
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 30px;
            font-size: 1.2rem;
            opacity: 0.8;
        }

        .order-number {
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 1rem;
            margin: 15px 0;
        }

        .btn-portal {
            background: white;
            color: #764ba2;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 30px;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-portal:hover {
            transform: translateY(-3px);
            background: #f8f9fa;
            color: #667eea;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
        }

        .details-text {
            max-width: 500px;
            opacity: 0.9;
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <div class="status-card">
        <div class="brand-title">Prime Home Hub</div>

        <?php if ($success): ?>
            <div class="status-icon success-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h1 class="fw-bold mb-2">Order Confirmed!</h1>
            <div class="order-number">ORDER #<?php echo $orderNo; ?></div>
            <p class="details-text">
                Thank you, <strong><?php echo htmlspecialchars($customerName); ?></strong>! Your order has been placed successfully. 
                We'll reach out to you shortly.
            </p>
            <div class="mt-2">
                <a href="shop.php" class="btn-portal">Continue Shopping</a>
                <a href="orders.php" class="ms-3 text-white text-decoration-none fw-bold small">View My Orders</a>
            </div>
        <?php else: ?>
            <div class="status-icon error-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <h1 class="fw-bold mb-2">Checkout Failed</h1>
            <p class="details-text text-danger">
                <?php echo $error ?: "An unexpected error occurred while processing your order."; ?>
            </p>
            <a href="cart.php" class="btn-portal">Return to Cart</a>
        <?php endif; ?>
    </div>

</body>
</html>
