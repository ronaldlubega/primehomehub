<?php
if (!class_exists('Database')) {
    require_once 'includes/database.php';
}

// Reuse existing connection if available, otherwise create one
if (!isset($db) || !($db instanceof Database)) {
    $db = new Database();
}

// Get counts for badges using the safer count() method
$pendingOrdersCount = $db->count('orders', "status = 'pending'");
$unreadNotificationsCount = $db->count('notifications', "is_read = 0");
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php" style="font-family: 'Century Gothic', sans-serif; font-weight: 900; text-transform: uppercase;">PRIME HOME HUB</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto gap-2">
                <li class="nav-item">
                    <a class="nav-link fw-bold <?php echo $current_page == 'admin.php' ? 'active' : ''; ?>" href="admin.php">Dashboard</a>
                </li>
                <li class="nav-item position-relative">
                    <a class="nav-link fw-bold <?php echo $current_page == 'admin_orders.php' ? 'active' : ''; ?>" href="admin_orders.php">
                        Orders
                        <?php if ($pendingOrdersCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                <?php echo $pendingOrdersCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold <?php echo $current_page == 'admin_products.php' ? 'active' : ''; ?>" href="admin_products.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold <?php echo $current_page == 'admin_categories.php' ? 'active' : ''; ?>" href="admin_categories.php">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold <?php echo $current_page == 'admin_services.php' ? 'active' : ''; ?>" href="admin_services.php">Services</a>
                </li>
                <li class="nav-item position-relative">
                    <a class="nav-link fw-bold <?php echo $current_page == 'admin_notifications.php' ? 'active' : ''; ?>" href="admin_notifications.php">
                        Notifications
                        <?php if ($unreadNotificationsCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size: 0.6rem;">
                                <?php echo $unreadNotificationsCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold" href="logout.php">LogOut</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
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
