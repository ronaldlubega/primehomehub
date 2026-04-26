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

// Handle Actions
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'mark_read' && isset($_GET['id'])) {
        $db->execute("UPDATE notifications SET is_read = 1 WHERE id = ?", [$_GET['id']]);
    } elseif ($_GET['action'] === 'mark_all_read') {
        $db->execute("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    } elseif ($_GET['action'] === 'clear_all') {
        $db->execute("DELETE FROM notifications");
        $message = "All notifications cleared.";
    }
    header('Location: admin_notifications.php');
    exit();
}

// Fetch notifications
$notifications = $db->fetch_all("SELECT * FROM notifications ORDER BY created_at DESC");
// Debug: Trace the notifications
$debug_count = $db->count("SELECT COUNT(*) FROM notifications");
$debug_error = $db->get_error();
?>
<!-- DIAGNOSTIC OVERLAY -->
<?php if (isset($_GET['debug'])): ?>
<div class="alert alert-info rounded-0 m-0 py-1 small">
    <strong>DEBUG:</strong> Table Count: <?php echo $debug_count; ?> | Error: <?php echo $debug_error ?: 'None'; ?> | DB: <?php echo DB_NAME; ?>
</div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Century Gothic', 'Segoe UI', sans-serif; background-color: #f1f4f9; }
        .navbar-brand { font-family: 'Century Gothic', sans-serif; font-weight: 900; text-transform: uppercase; }
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
        .glass-card { background: rgba(255, 255, 255, 0.95); border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; }
        .notification-item { transition: all 0.2s; border-left: 4px solid transparent; }
        .notification-item.unread { border-left-color: #0d6efd; background-color: #f8fbff; }
        .notification-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .btn-premium { background: #000; color: #fff; border: none; font-weight: bold; border-radius: 10px; transition: all 0.3s; padding: 6px 16px; text-decoration: none; display: inline-block; font-size: 0.875rem; }
        .btn-premium:hover { background: #333; color: #fff; transform: translateY(-2px); }
    </style>
</head>
<body>
    <?php include 'includes/admin_nav.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">System Notifications</h2>
                <p class="text-muted">Stay updated with the latest activity across the platform</p>
            </div>
            <div class="d-flex gap-2">
                <?php if (!empty($notifications)): ?>
                    <a href="?action=mark_all_read" class="btn-premium">Mark all as read</a>
                    <a href="?action=clear_all" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Clear all notifications?')" style="border-radius: 10px !important;">Clear all</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="glass-card overflow-hidden">
            <div class="list-group list-group-flush">
                <?php if (empty($notifications)): ?>
                    <div class="p-5 text-center">
                        <i class="bi bi-bell-slash display-1 text-muted mb-4"></i>
                        <h3>No Notifications</h3>
                        <p class="text-muted">You are all caught up! New orders and system alerts will appear here.</p>
                        <a href="admin.php" class="btn-premium py-2 px-4 mt-3">Back to Dashboard</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                        <div class="list-group-item notification-item p-4 <?php echo $n['is_read'] ? '' : 'unread'; ?>">
                            <div class="d-flex gap-3">
                                <div class="notification-icon bg-<?php 
                                    echo ($n['type'] === 'order' ? 'success' : ($n['type'] === 'contact' ? 'warning' : 'primary')); 
                                ?> text-white">
                                    <i class="bi bi-<?php 
                                        echo ($n['type'] === 'order' ? 'cart-check' : ($n['type'] === 'contact' ? 'envelope-paper' : 'bell')); 
                                    ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h5 class="mb-0 fw-bold"><?php echo htmlspecialchars($n['title']); ?></h5>
                                        <small class="text-muted"><?php echo date('M j, Y H:i', strtotime($n['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-2 text-dark"><?php echo htmlspecialchars($n['message']); ?></p>
                                    <div class="d-flex gap-2">
                                        <?php if (!$n['is_read']): ?>
                                            <a href="?action=mark_read&id=<?php echo $n['id']; ?>" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-bold small">Mark as read</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
