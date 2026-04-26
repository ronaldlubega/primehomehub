<?php
require_once 'navigation.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=orders');
    exit();
}

// Get database connection
$db = new Database();
$userId = $_SESSION['user_id'];

// Get user's orders
$orders = $db->fetch_all(
    "SELECT o.*, COUNT(oi.id) as item_count 
     FROM orders o 
     LEFT JOIN order_items oi ON o.id = oi.order_id 
     WHERE o.user_id = ? 
     GROUP BY o.id 
     ORDER BY o.created_at DESC",
    [$userId]
);

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderId = $_POST['cancel_order'];
    // Verify order belongs to user and is pending
    $order = $db->fetch_one("SELECT status FROM orders WHERE id = ? AND user_id = ?", [$orderId, $userId]);
    if ($order && $order['status'] === 'pending') {
        $db->execute("UPDATE orders SET status = 'cancelled' WHERE id = ?", [$orderId]);
        header("Location: orders.php?msg=cancelled");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>


        <div class="container py-5">
            <div class="row mb-4">
                <div class="col-12 text-center">
                    <h1 class="fw-bold text-uppercase letter-spacing-1">Order History</h1>
                    <p class="text-muted">Track and manage your luxury furniture acquisitions</p>
                    <hr class="w-25 mx-auto opacity-10">
                </div>
            </div>


            <div class="row">
                <div class="col-12">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-box display-1 text-muted"></i>
                            <h3 class="mt-3">No Orders Yet</h3>
                            <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                            <a href="shop.php" class="btn btn-primary">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo $order['item_count']; ?> items</td>
                                            <td>UGX <?php echo number_format($order['total_amount']); ?></td>
                                            <td>
                                                 <?php 
                                                    $statusClass = 'bg-secondary';
                                                    if ($order['status'] === 'pending') $statusClass = 'bg-warning text-dark';
                                                    if ($order['status'] === 'processing') $statusClass = 'bg-success';
                                                    if ($order['status'] === 'cancelled') $statusClass = 'bg-danger';
                                                    if ($order['status'] === 'completed') $statusClass = 'bg-primary';
                                                 ?>
                                                 <span class="badge <?php echo $statusClass; ?>">
                                                     <?php echo ucfirst($order['status']); ?>
                                                 </span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex gap-3 align-items-center">
                                                    <a href="javascript:void(0)" class="action-link text-primary text-decoration-none small fw-bold" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                                        VIEW
                                                    </a>
                                                    <?php if ($order['status'] === 'pending'): ?>
                                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                                            <input type="hidden" name="cancel_order" value="<?php echo $order['id']; ?>">
                                                            <button type="submit" class="action-link text-danger border-0 bg-transparent p-0 small fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                                                CANCEL
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
                </div>
            </div>
        </div>

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        .order-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
            border: 1px solid #eee;
        }
        .order-card:hover {
            transform: translateY(-3px);
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .action-link {
            transition: opacity 0.2s;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.75rem;
        }
        .action-link:hover {
            opacity: 0.7;
            text-decoration: underline !important;
        }
        .letter-spacing-1 { letter-spacing: 1.5px; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewOrder(orderId) {
            alert('Order tracking details for #' + orderId + ' will be available once the system is fully synchronized.');
        }
    </script>
</body>
</html>
