<?php
// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo strpos($current_page, 'pages/') !== false ? '../index.php' : 'index.php'; ?>">
            <i class="bi bi-palette-fill"></i> Prime Home Hub
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" 
                       href="<?php echo strpos($current_page, 'pages/') !== false ? '../index.php' : 'index.php'; ?>">
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'about.php' ? 'active' : ''; ?>" 
                       href="<?php echo strpos($current_page, 'pages/') !== false ? 'about.php' : 'pages/about.php'; ?>">
                        About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'services.php' ? 'active' : ''; ?>" 
                       href="<?php echo strpos($current_page, 'pages/') !== false ? 'services.php' : 'pages/services.php'; ?>">
                        Services
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'portfolio.php' ? 'active' : ''; ?>" 
                       href="<?php echo strpos($current_page, 'pages/') !== false ? 'portfolio.php' : 'pages/portfolio.php'; ?>">
                        Portfolio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'shop.php' ? 'active' : ''; ?>" 
                       href="<?php echo strpos($current_page, 'pages/') !== false ? 'shop.php' : 'pages/shop.php'; ?>">
                        Shop
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'visualizer.php' ? 'active' : ''; ?>" 
                       href="<?php echo strpos($current_page, 'pages/') !== false ? 'visualizer.php' : 'pages/visualizer.php'; ?>">
                        Visualizer
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'contact.php' ? 'active' : ''; ?>" 
                       href="<?php echo strpos($current_page, 'pages/') !== false ? 'contact.php' : 'pages/contact.php'; ?>">
                        Contact
                    </a>
                </li>
                
                <?php if ($is_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo strpos($current_page, 'pages/') !== false ? '../admin.php' : 'admin.php'; ?>">
                            <i class="bi bi-gear-fill"></i> Admin
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if ($is_logged_in): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo strpos($current_page, 'pages/') !== false ? 'profile.php' : 'pages/profile.php'; ?>">Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo strpos($current_page, 'pages/') !== false ? 'orders.php' : 'pages/orders.php'; ?>">Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo strpos($current_page, 'pages/') !== false ? '../logout.php' : 'logout.php'; ?>">Logout</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?php echo strpos($current_page, 'pages/') !== false ? 'cart.php' : 'pages/cart.php'; ?>">
                            <i class="bi bi-cart3"></i> Cart
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-badge">0</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo strpos($current_page, 'pages/') !== false ? '../login.php' : 'login.php'; ?>">
                            <i class="bi bi-person-circle"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo strpos($current_page, 'pages/') !== false ? '../register.php' : 'register.php'; ?>">
                            Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
