<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=user-dashboard');
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

// Get user's wishlist
$wishlist = $db->fetch_all(
    "SELECT w.*, p.name, p.price, p.image_url 
     FROM wishlist w 
     JOIN products p ON w.product_id = p.id 
     WHERE w.user_id = ? 
     ORDER BY w.created_at DESC",
    [$userId]
);

// Calculate total spent
$totalSpent = $db->fetch_one(
    "SELECT SUM(total_amount) as total FROM orders WHERE user_id = ? AND status != 'cancelled'",
    [$userId]
)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">My Dashboard</h1>
            <p class="lead">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>
    </section>

    <!-- Dashboard Content -->
    <section class="py-5">
        <div class="container">
            <!-- Stats Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-box-seam display-4 text-primary"></i>
                            <h5 class="mt-2"><?php echo count($orders); ?></h5>
                            <p class="text-muted">Total Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-heart display-4 text-danger"></i>
                            <h5 class="mt-2"><?php echo count($wishlist); ?></h5>
                            <p class="text-muted">Wishlist Items</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-currency-dollar display-4 text-success"></i>
                            <h5 class="mt-2">UGX <?php echo number_format($totalSpent); ?></h5>
                            <p class="text-muted">Total Spent</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="bi bi-person-circle display-4 text-info"></i>
                            <h5 class="mt-2"><?php echo htmlspecialchars($_SESSION['name']); ?></h5>
                            <p class="text-muted">Account Status</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Orders</h5>
                            <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($orders)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-box display-1 text-muted"></i>
                                    <h5 class="mt-3">No Orders Yet</h5>
                                    <p class="text-muted">Start shopping to see your orders here!</p>
                                    <a href="shop.php" class="btn btn-primary">Start Shopping</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
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
                                            <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                                    <td><?php echo $order['item_count']; ?></td>
                                                    <td>UGX <?php echo number_format($order['total_amount']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $order['status'] === 'completed' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'info'); ?>">
                                                            <?php echo ucfirst($order['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                                            View
                                                        </button>
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

                <!-- Wishlist -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">My Wishlist</h5>
                            <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($wishlist)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-heart display-1 text-muted"></i>
                                    <h6 class="mt-3">Wishlist Empty</h6>
                                    <p class="text-muted small">Add items you love to your wishlist!</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($wishlist, 0, 3) as $item): ?>
                                        <div class="list-group-item d-flex align-items-center">
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                     class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <small class="text-muted">UGX <?php echo number_format($item['price']); ?></small>
                                            </div>
                                            <button class="btn btn-sm btn-outline-danger" onclick="removeFromWishlist(<?php echo $item['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="shop.php" class="btn btn-outline-primary">
                                    <i class="bi bi-cart3"></i> Continue Shopping
                                </a>
                                <a href="profile.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-person"></i> Edit Profile
                                </a>
                                <a href="#" class="btn btn-outline-info">
                                    <i class="bi bi-headset"></i> Contact Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Order Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="orderDetails"></div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/navigation.js"></script>
    <script>
        function viewOrder(orderId) {
            // In a real application, this would fetch order details from the server
            document.getElementById('orderDetails').innerHTML = `
                <div class="text-center">
                    <i class="bi bi-box display-1 text-muted"></i>
                    <h4 class="mt-3">Order Details</h4>
                    <p>Order ID: ${orderId}</p>
                    <p class="text-muted">Detailed order information would be displayed here.</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('orderModal'));
            modal.show();
        }
        
        function removeFromWishlist(itemId) {
            if (confirm('Are you sure you want to remove this item from your wishlist?')) {
                // In a real application, this would send a request to the server
                alert('Item removed from wishlist!');
                location.reload();
            }
        }
    </script>
</body>
</html>
