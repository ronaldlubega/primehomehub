<?php
session_start();
require_once 'data_handler.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
$db = new Database();

// Determine current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user is logged in and is admin or officer
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'officer'])) {
    header('Location: login.php');
    exit();
}

// Load system data
$contacts = loadData('contacts.json');
$users = loadData('users.json');
$sqlOrders = $db->fetch_all("SELECT id FROM orders");
$orderCount = count($sqlOrders);

// Counts for stats handled below
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Century Gothic', 'Segoe UI', sans-serif;
            background-color: #f1f4f9;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
            transition: transform 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
        }
        .navbar-brand {
            font-family: 'Century Gothic', sans-serif;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-card {
            border: none;
            padding: 1.25rem;
            background-color: #2d3436 !important;
            color: #ffffff !important;
            position: relative;
            overflow: hidden;
        }
        .stat-icon {
            font-size: 1.25rem;
            position: absolute;
            top: 1rem;
            right: 1rem;
            opacity: 0.4;
        }
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;
        }
        .btn-action {
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/admin_nav.php'; ?>

    <!-- Personalized Welcome (Simple Greeting) -->
    <div class="container pt-4 pb-0 mt-2">
        <h1 class="fw-bold fs-2"><?php echo get_time_greeting(); ?>, <?php echo explode(' ', $_SESSION['name'])[0]; ?>!</h1>
        <p class="text-muted mb-0">Welcome back to your dashboard. System is nominal.</p>
    </div>

    <!-- Content -->
    <div class="container py-3">
        <div class="row g-2 g-md-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="glass-card stat-card h-100">
                    <i class="bi bi-people stat-icon"></i>
                    <h2 class="display-5 fw-bold mb-0"><?php echo count($users); ?></h2>
                    <p class="mb-0 small opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Global Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card stat-card h-100">
                    <i class="bi bi-cart3 stat-icon"></i>
                    <h2 class="display-5 fw-bold mb-0"><?php echo $db->fetch_one("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count']; ?></h2>
                    <p class="mb-0 small opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Pending Orders</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card stat-card h-100">
                    <i class="bi bi-tags stat-icon"></i>
                    <h2 class="display-5 fw-bold mb-0"><?php echo count($db->fetch_all("SELECT id FROM categories")); ?></h2>
                    <p class="mb-0 small opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Categories</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card stat-card h-100">
                    <i class="bi bi-box-seam stat-icon"></i>
                    <h2 class="display-5 fw-bold mb-0"><?php echo count($db->fetch_all("SELECT id FROM products")); ?></h2>
                    <p class="mb-0 small opacity-75 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Live Products</p>
                </div>
            </div>
        </div>

            <!-- Notifications Preview -->
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold">Recent Notifications</h5>
                        <a href="admin_notifications.php" class="btn btn-sm btn-outline-light border-0">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php 
                            $recentNotifications = $db->fetch_all("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5");
                            if (empty($recentNotifications)): 
                            ?>
                                <div class="list-group-item text-center py-5 text-muted">
                                    <i class="bi bi-bell-slash display-4"></i>
                                    <p class="mb-0 mt-2">No notifications yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentNotifications as $n): ?>
                                    <div class="list-group-item list-group-item-action d-flex align-items-start gap-3 py-3 <?php echo $n['is_read'] ? 'opacity-75' : 'bg-light'; ?>">
                                        <div class="rounded-circle p-2 bg-<?php 
                                            echo ($n['type'] === 'order' ? 'success' : ($n['type'] === 'user' ? 'primary' : 'info')); 
                                        ?> text-white">
                                            <i class="bi bi-<?php 
                                                echo ($n['type'] === 'order' ? 'cart-check' : ($n['type'] === 'user' ? 'person-plus' : 'info-circle')); 
                                            ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($n['title']); ?></h6>
                                                <small class="text-muted"><?php echo date('M j, H:i', strtotime($n['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-0 small text-muted"><?php echo htmlspecialchars($n['message']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
