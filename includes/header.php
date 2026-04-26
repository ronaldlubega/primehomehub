<?php
session_start();
// Basic helper to generate dynamic title
$page_title = isset($page_title) ? $page_title . " | Design Haven" : "Design Haven | Premium Furniture";
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    
    <!-- Google Fonts: Playfair Display (Serif) & Inter (Sans-serif) for an elegant mix -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Premium CSS -->
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/pages.css">
</head>
<body>

<!-- Navigation Bar -->
<header class="site-header">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <i class="fa-solid fa-couch"></i> 
            <span>Design</span>Haven
        </a>

        <div class="menu-toggle" id="mobile-menu-btn">
            <i class="fa-solid fa-bars"></i>
        </div>

        <nav class="main-nav" id="main-nav">
            <ul class="nav-links">
                <li><a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a></li>
                <li><a href="shop.php" class="<?= $current_page == 'shop.php' ? 'active' : '' ?>">Shop</a></li>
                <li><a href="room-planner.php" class="<?= $current_page == 'room-planner.php' ? 'active' : '' ?>">Room Planner</a></li>
                <li><a href="mood-boards.php" class="<?= $current_page == 'mood-boards.php' ? 'active' : '' ?>">Mood Boards</a></li>
            </ul>

            <div class="nav-actions">
                <button class="icon-btn theme-toggle" id="theme-toggle" aria-label="Toggle Dark Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <a href="wishlist.php" class="icon-btn" aria-label="Wishlist">
                    <i class="fa-regular fa-heart"></i>
                    <span class="badge" id="wishlist-count">0</span>
                </a>
                <div class="cart-dropdown-wrapper">
                    <button class="icon-btn" id="cart-toggle-btn" aria-label="Cart">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span class="badge" id="cart-count">0</span>
                    </button>
                    <!-- Cart Preview Dropdown -->
                    <div class="cart-dropdown" id="cart-preview">
                        <div class="cart-dropdown-header">
                            <h4>Your Cart</h4>
                        </div>
                        <div class="cart-dropdown-items" id="cart-dropdown-items">
                            <!-- Populated by JS -->
                            <div class="empty-cart-msg">Your cart is empty.</div>
                        </div>
                        <div class="cart-dropdown-footer">
                            <div class="total">Total: $<span id="cart-dropdown-total">0.00</span></div>
                            <a href="cart.php" class="btn btn-primary btn-block">Go to Checkout</a>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn btn-outline btn-sm">Profile</a>
                    <button id="logout-btn" class="btn btn-text btn-sm">Logout</button>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline btn-sm">Log In</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
<main class="main-content">
