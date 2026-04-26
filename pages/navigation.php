<?php
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

// Determine current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Calculate cart count for logged-in users
$cart_count = 0;
if ($is_logged_in && isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += ($item['quantity'] ?? 1);
    }
}

// Update cart count in session
if ($is_logged_in) {
    $_SESSION['cart_count'] = $cart_count;
}
?>

<!-- Universal Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="../index.php" style="font-weight: 900; font-family: 'Century Gothic', sans-serif; text-transform: uppercase; letter-spacing: 1px; font-size: 1.5rem;">
            PRIME HOME HUB
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="../index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page === 'about.php' ? 'active' : ''; ?>" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page === 'services.php' ? 'active' : ''; ?>" href="services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page === 'portfolio.php' ? 'active' : ''; ?>" href="portfolio.php">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page === 'shop.php' ? 'active' : ''; ?>" href="shop.php">Shop</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page === 'visualizer.php' ? 'active' : ''; ?>" href="visualizer.php">Visualizer</a></li>
                <li class="nav-item"><a class="nav-link <?php echo $current_page === 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a></li>
                <?php if ($is_logged_in): ?>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'admin.php' ? 'active' : ''; ?>" href="../admin.php">
                                <i class="bi bi-gear-fill"></i> Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="bi bi-cart3"></i> Cart
                            <?php if ($is_logged_in && $cart_count > 0): ?>
                                <span class="badge bg-danger ms-1" id="cart-badge"><?php echo $cart_count; ?></span>
                            <?php elseif ($is_logged_in): ?>
                                <span class="badge bg-secondary ms-1" id="cart-badge">0</span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'login.php' ? 'active' : ''; ?>" href="../login.php">Login</a>
                    </li>
                <?php endif; ?>
                <?php if ($is_logged_in): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> Profile
                            <span class="badge bg-primary ms-1"><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name'][0]) : 'U'; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
                            </a></li>
                            <li><a class="dropdown-item" href="#">
                                <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
