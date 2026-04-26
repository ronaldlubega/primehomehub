<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Check if user is logged in and is admin or officer
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'officer'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$message = '';
$messageType = '';

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $orderId = $_POST['order_id'];
    $action = $_POST['action'];
    $newStatus = ($action === 'approve') ? 'processing' : 'cancelled';
    $paymentStatus = ($action === 'approve') ? 'paid' : 'unpaid'; // Simplified logic

    $sql = "UPDATE orders SET status = ?, payment_status = ? WHERE id = ?";
    $result = $db->execute($sql, [$newStatus, $paymentStatus, $orderId]);

    if ($result['success']) {
        $message = "Order #$orderId has been " . ($action === 'approve' ? 'Approved' : 'Declined') . ".";
        $messageType = ($action === 'approve' ? 'success' : 'danger');
    } else {
        $message = "Error updating order: " . $result['error'];
        $messageType = 'danger';
    }
}

// Fetch all orders using denormalized customer data (Fast & Robust)
$orders = $db->fetch_all(
    "SELECT *, id as order_actual_id FROM orders ORDER BY created_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Century Gothic', 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-family: 'Century Gothic', sans-serif;
            font-weight: 900;
            text-transform: uppercase;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 50px;
        }
        .navbar-nav .nav-link { position: relative; padding-bottom: 5px; }
        .navbar-nav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 25%;
            right: 25%;
            height: 3px;
            background-color: #fff;
            border-radius: 2px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .stat-card { border: none; padding: 1.25rem; background: #2d3436; color: #fff; border-radius: 15px; }
        .btn-premium { background: #000; color: #fff; border: none; font-weight: bold; border-radius: 10px; transition: all 0.3s; padding: 8px 16px; text-decoration: none; display: inline-block; }
        .btn-premium:hover { background: #333; color: #fff; transform: translateY(-2px); }
        .table thead th {
            background-color: #f1f4f9;
            border-bottom: none;
            color: #495057;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        .order-row:hover {
            background-color: #f1f4f9;
        }
        .btn-action {
            padding: 4px 12px;
            font-size: 0.85rem;
            font-weight: 600;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_nav.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Order Management</h2>
                <p class="text-muted">Review and process customer orders</p>
            </div>
            <a href="admin.php" class="btn-premium">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card glass-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        No orders found in the system.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr class="order-row">
                                        <td class="ps-4">
                                            <span class="fw-bold"><?php echo htmlspecialchars($order['order_number']); ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold fs-6"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            <div class="small text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                        </td>
                                        <td><?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td class="fw-bold">UGX <?php echo number_format($order['total_amount']); ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = 'bg-secondary';
                                            if ($order['status'] === 'pending') $statusClass = 'bg-warning text-dark';
                                            if ($order['status'] === 'processing') $statusClass = 'bg-success';
                                            if ($order['status'] === 'cancelled') $statusClass = 'bg-danger';
                                            ?>
                                            <span class="badge status-badge <?php echo $statusClass; ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'muted'; ?> small fw-bold">
                                                <i class="bi bi-<?php echo $order['payment_status'] === 'paid' ? 'check-circle' : 'clock'; ?> me-1"></i>
                                                <?php echo strtoupper($order['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-primary btn-action me-1" onclick="loadOrderDetails(<?php echo $order['order_actual_id']; ?>)">
                                                <i class="bi bi-eye"></i> Details
                                            </button>
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['order_actual_id']; ?>">
                                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-action me-1">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                    <button type="submit" name="action" value="decline" class="btn btn-danger btn-action">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal (Portrait) -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-dark text-white p-4" style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                    <h5 class="modal-title fw-bold" id="modalOrderNumber">Order Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="modalContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <div id="modalFooterActions"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
        
        async function loadOrderDetails(orderId) {
            document.getElementById('modalOrderNumber').innerText = 'Loading...';
            document.getElementById('modalContent').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
            orderModal.show();

            try {
                const response = await fetch(`get_order_details.php?id=${orderId}`);
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || `HTTP Error ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success && data.order) {
                    const o = data.order;
                    document.getElementById('modalOrderNumber').innerText = `Order #${o.order_number}`;
                    
                    let itemsHtml = '';
                    data.items.forEach(item => {
                        itemsHtml += `
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div class="small">
                                    <span class="fw-bold d-block">${item.product_name}</span>
                                    <span class="text-muted">Quantity: ${item.quantity}</span>
                                </div>
                                <span class="fw-bold small">UGX ${new Intl.NumberFormat().format(item.subtotal)}</span>
                            </div>
                        `;
                    });

                    document.getElementById('modalContent').innerHTML = `
                        <div class="mb-4">
                            <h6 class="text-primary fw-bold mb-2">CUSTOMER INFORMATION</h6>
                            <p class="mb-0 fw-bold">${o.customer_name}</p>
                            <p class="text-muted mb-0 small"><i class="bi bi-envelope"></i> ${o.customer_email}</p>
                            <p class="text-muted small"><i class="bi bi-telephone"></i> ${o.user_phone || 'N/A'}</p>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-primary fw-bold mb-2">DELIVERY LOCATION</h6>
                            <p class="text-muted small mb-0"><i class="bi bi-geo-alt-fill"></i> ${o.shipping_address}</p>
                            <p class="text-muted small mb-0"><i class="bi bi-truck"></i> Delivery Cost: UGX ${new Intl.NumberFormat().format(o.shipping_cost)}</p>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-primary fw-bold mb-2">ORDER DEMANDS</h6>
                            <div class="bg-light p-3 rounded-3">
                                ${itemsHtml}
                                <div class="d-flex justify-content-between mt-3">
                                    <span class="fw-bold">TOTAL AMOUNT</span>
                                    <span class="fw-bold text-primary fs-5">UGX ${new Intl.NumberFormat().format(o.total_amount)}</span>
                                </div>
                            </div>
                        </div>

                        ${o.notes ? `
                        <div class="mb-0">
                            <h6 class="text-primary fw-bold mb-2">CUSTOMER NOTES</h6>
                            <p class="text-muted small italic" style="font-style: italic;">"${o.notes}"</p>
                        </div>
                        ` : ''}
                    `;

                    let footerHtml = `
                        <a href="mailto:${o.customer_email}?subject=Regarding your Order #${o.order_number}" class="btn btn-primary rounded-pill px-4 me-2">
                            <i class="bi bi-envelope-fill me-2"></i> Email
                        </a>
                    `;

                    if (o.user_phone) {
                        // Create WhatsApp link (Uganda prefix +256 if not present)
                        let phone = o.user_phone.replace(/\D/g, '');
                        if (phone.startsWith('0')) phone = '256' + phone.substring(1);
                        else if (!phone.startsWith('256')) phone = '256' + phone;

                        footerHtml += `
                            <a href="https://wa.me/${phone}?text=Hello ${o.customer_name.split(' ')[0]}, I'm reaching out from Prime Home Hub regarding your Order #${o.order_number}" target="_blank" class="btn btn-success rounded-pill px-4">
                                <i class="bi bi-whatsapp me-2"></i> WhatsApp
                            </a>
                        `;
                    }

                    document.getElementById('modalFooterActions').innerHTML = footerHtml;
                } else {
                    throw new Error(data.message || 'Unknown server error');
                }
            } catch (error) {
                console.error('Order Load Error:', error);
                document.getElementById('modalContent').innerHTML = `
                    <div class="alert alert-danger p-4 text-center border-0 shadow-sm" style="border-radius: 12px;">
                        <i class="bi bi-exclamation-triangle-fill display-6 d-block mb-2 text-danger"></i>
                        <h6 class="fw-bold mb-1">DATA PIPELINE ERROR</h6>
                        <small class="d-block text-muted opacity-75">${error.message}</small>
                    </div>`;
            }
        }
    </script>
</body>
</html>
