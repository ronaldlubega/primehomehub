<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
require_once 'includes/footer.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=orders');
    exit();
}

// Get database connection
$db = new Database();
$userId = $_SESSION['user_id'];

// Get user's orders with items
$orders = $db->fetch_all(
    "SELECT o.*, u.name as customer_name, u.email as customer_email,
     (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     WHERE o.user_id = ? 
     ORDER BY o.created_at DESC",
    [$userId]
);

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];
    
    // Update order status
    $result = $db->execute(
        "UPDATE orders SET status = ? WHERE id = ? AND user_id = ?",
        [$newStatus, $orderId, $userId]
    );
    
    if ($result['success']) {
        $_SESSION['success'] = "Order status updated successfully!";
        header('Location: orders.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">My Orders</h1>
            <p class="lead">Track and manage your orders</p>
        </div>
    </section>

    <!-- Orders Content -->
    <section class="py-5">
        <div class="container">
            <!-- Success Message -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filter and Search -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchOrders" placeholder="Search orders...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="dateFilter">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                </div>
            </div>

            <!-- Orders List -->
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
                            <table class="table table-hover" id="ordersTable">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr class="order-row" data-status="<?php echo $order['status']; ?>" data-date="<?php echo $order['created_at']; ?>">
                                            <td>
                                                <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                            </td>
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
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                        <i class="bi bi-eye"></i> View
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success" onclick="trackOrder(<?php echo $order['id']; ?>)">
                                                        <i class="bi bi-truck"></i> Track
                                                    </button>
                                                    <?php if ($order['status'] === 'pending'): ?>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                            <i class="bi bi-x-circle"></i> Cancel
                                                        </button>
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
    </section>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="orderDetailsContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Track Order Modal -->
    <div class="modal fade" id="trackOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Track Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="trackOrderContent"></div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/navigation.js"></script>
    <script>
        // Helper function to get status color (PHP equivalent)
        function getStatusColor(status) {
            const colors = {
                'pending': 'warning',
                'processing': 'info',
                'shipped': 'primary',
                'delivered': 'success',
                'cancelled': 'danger'
            };
            return colors[status] || 'secondary';
        }
        
        // Search functionality
        document.getElementById('searchOrders').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.order-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function(e) {
            const status = e.target.value;
            const rows = document.querySelectorAll('.order-row');
            
            rows.forEach(row => {
                if (status === '' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Date filter
        document.getElementById('dateFilter').addEventListener('change', function(e) {
            const filter = e.target.value;
            const rows = document.querySelectorAll('.order-row');
            const now = new Date();
            
            rows.forEach(row => {
                const orderDate = new Date(row.dataset.date);
                let show = true;
                
                switch(filter) {
                    case 'today':
                        show = orderDate.toDateString() === now.toDateString();
                        break;
                    case 'week':
                        const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                        show = orderDate >= weekAgo;
                        break;
                    case 'month':
                        const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                        show = orderDate >= monthAgo;
                        break;
                    case 'year':
                        const yearAgo = new Date(now.getTime() - 365 * 24 * 60 * 60 * 1000);
                        show = orderDate >= yearAgo;
                        break;
                }
                
                row.style.display = show ? '' : 'none';
            });
        });
        
        function viewOrderDetails(orderId) {
            // In a real application, this would fetch order details from the server
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="text-center">
                    <i class="bi bi-box display-1 text-muted"></i>
                    <h4 class="mt-3">Order #${orderId}</h4>
                    <p class="text-muted">Detailed order information would be displayed here.</p>
                    <div class="mt-4">
                        <h6>Order Items:</h6>
                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Sample Product 1</span>
                                <span>UGX 50,000</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Sample Product 2</span>
                                <span>UGX 30,000</span>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong>UGX 80,000</strong>
                        </div>
                    </div>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            modal.show();
        }
        
        function trackOrder(orderId) {
            // In a real application, this would fetch tracking information
            document.getElementById('trackOrderContent').innerHTML = `
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6>Order Placed</h6>
                            <small class="text-muted">${new Date().toLocaleDateString()}</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6>Processing</h6>
                            <small class="text-muted">Expected: Tomorrow</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6>Shipped</h6>
                            <small class="text-muted">Expected: In 3-5 days</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-secondary"></div>
                        <div class="timeline-content">
                            <h6>Delivered</h6>
                            <small class="text-muted">Expected: In 5-7 days</small>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Tracking Number: #TRK${orderId}${Math.random().toString(36).substr(2, 9).toUpperCase()}</h6>
                    <small class="text-muted">You can use this number to track your package on our delivery partner's website.</small>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('trackOrderModal'));
            modal.show();
        }
        
        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
                // In a real application, this would send a cancel request to the server
                console.log('Cancelling order:', orderId);
                alert('Order cancellation request submitted. You will receive an email confirmation shortly.');
                location.reload();
            }
        }
    </script>
    
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-marker {
            position: absolute;
            left: -25px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .timeline-content {
            margin-left: 0;
        }
    </style>
</body>
</html>

<?php
function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}
?>
