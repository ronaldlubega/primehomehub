<?php
session_start();
require_once 'data_handler.php';
require_once 'includes/database.php';
$db = new Database();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
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

// Fetch featured products from MySQL
$featuredProducts = $db->fetch_all(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     JOIN categories c ON p.category_id = c.id 
     WHERE p.status = 'active' 
     LIMIT 3"
);


// Handle cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!$is_logged_in) {
        header('Location: login.php?msg=auth_required');
        exit();
    }
    
    $product_id = $_POST['product_id'];
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add or update item in cart
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$product_id] = ['quantity' => 1];
        }
        
        // Calculate cart count
        $cart_count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cart_count += ($item['quantity'] ?? 1);
        }
        $_SESSION['cart_count'] = $cart_count;
        
        header('Location: index.php?added=1');
        exit();
}

// Show success message if item was added
$added_message = '';
if (isset($_GET['added']) && $_GET['added'] == 1) {
    $added_message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> Product added to cart successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prime Home Hub - Premium Furniture & Interior Design</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Currency Toggle Button -->
    <button class="btn btn-outline-light position-fixed" id="currency-toggle" onclick="toggleCurrency()" style="top: 20px; right: 20px; z-index: 1000; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-currency-exchange me-1"></i> Show USD
    </button>
    
    <!-- Simple Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <!-- ANTIGRAVITY BRAND UPDATE -->
            <a class="navbar-brand brand-white-bold" href="index.php" style="color: #ffffff !important; font-weight: 900 !important; font-family: 'Century Gothic', 'Segoe UI', sans-serif !important; text-transform: uppercase !important; font-size: min(1.8rem, 5vw) !important; letter-spacing: 1px !important; -webkit-text-fill-color: #ffffff !important;">
                <i class="bi bi-palette-fill" style="color: #ffffff !important; -webkit-text-fill-color: #ffffff !important;"></i> Prime Home Hub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $current_page === 'about.php' ? 'active' : ''; ?>" href="pages/about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $current_page === 'services.php' ? 'active' : ''; ?>" href="pages/services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $current_page === 'portfolio.php' ? 'active' : ''; ?>" href="pages/portfolio.php">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $current_page === 'shop.php' ? 'active' : ''; ?>" href="pages/shop.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $current_page === 'visualizer.php' ? 'active' : ''; ?>" href="pages/visualizer.php">Visualizer</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $current_page === 'contact.php' ? 'active' : ''; ?>" href="pages/contact.php">Contact</a></li>
                    <?php if ($is_logged_in): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $current_page === 'admin.php' ? 'active' : ''; ?>" href="admin.php">
                                    <i class="bi bi-gear-fill"></i> Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/cart.php">
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
                            <a class="nav-link <?php echo $current_page === 'login.php' ? 'active' : ''; ?>" href="login.php">Login</a>
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
                                <li><a class="dropdown-item" href="logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($added_message): ?>
        <div class="container mt-3">
            <?php echo $added_message; ?>
        </div>
    <?php endif; ?>
    
    <!-- Hero Section -->
    <section class="hero-section bg-dark text-white">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="carousel-content">
                        <div class="container">
                            <div class="row align-items-center min-vh-100">
                                <div class="col-lg-6">
                                    <h1 class="display-3 fw-bold mb-4" style="font-size: clamp(2rem, 8vw, 4.5rem);">Transform Your Home</h1>
                                    <p class="lead mb-4" style="font-size: clamp(1rem, 4vw, 1.25rem);">Discover premium furniture and expert interior design solutions that reflect your style</p>
                                    <div class="d-flex gap-3">
                                        <a href="pages/shop.php" class="btn btn-primary btn-lg">Shop Now</a>
                                        <a href="pages/services.php" class="btn btn-outline-light btn-lg">Our Services</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="carousel-content">
                        <div class="container">
                            <div class="row align-items-center min-vh-100">
                                <div class="col-lg-6">
                                    <h1 class="display-3 fw-bold mb-4">Expert Design Services</h1>
                                    <p class="lead mb-4">Professional interior designers ready to transform your space into something extraordinary</p>
                                    <div class="d-flex gap-3">
                                        <a href="pages/services.php" class="btn btn-primary btn-lg">Get Started</a>
                                        <a href="pages/portfolio.php" class="btn btn-outline-light btn-lg">View Portfolio</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="carousel-content">
                        <div class="container">
                            <div class="row align-items-center min-vh-100">
                                <div class="col-lg-6">
                                    <h1 class="display-3 fw-bold mb-4">Quality You Can Trust</h1>
                                    <p class="lead mb-4">Handpicked furniture collections that combine style, comfort, and durability</p>
                                    <div class="d-flex gap-3">
                                        <a href="pages/shop.php" class="btn btn-primary btn-lg">Explore Collection</a>
                                        <a href="pages/contact.php" class="btn btn-outline-light btn-lg">Contact Us</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Featured Products</h2>
                <p class="lead text-muted">Handpicked selections from our premium collection</p>
            </div>
            
            <div class="row g-4">
                <?php if (empty($featuredProducts)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-box display-1 text-muted"></i>
                            <h3 class="mt-3">No Featured Products Yet</h3>
                            <p class="text-muted">Check back soon for our latest featured items!</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 product-card">
                                <div class="position-relative">
                                    <?php if ($product['image_url']): ?>
                                        <img src="<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="card-img-top" style="height: 250px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 250px;">
                                            <i class="bi bi-image display-4 text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-<?php echo $product['stock'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <?php if ($product['category_name']): ?>
                                        <small class="text-muted mb-2"><?php echo htmlspecialchars($product['category_name']); ?></small>
                                    <?php endif; ?>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 100)); ?>...</p>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="h4 text-primary mb-0 price-display" data-original-price="<?php echo $product['price']; ?>">UGX <?php echo number_format($product['price']); ?></span>
                                            <div class="text-warning">
                                                <?php for ($i = 0; $i < 5; $i++): ?>
                                                    <i class="bi bi-star<?php echo $i < ($product['rating'] ?? 4) ? '-fill' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ($product['stock'] > 0): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="add_to_cart" value="1">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100" disabled>
                                                <i class="bi bi-x-circle"></i> Out of Stock
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="pages/shop.php" class="btn btn-outline-primary btn-lg">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Services</h2>
                <p class="lead text-muted">Professional design solutions for every space</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-palette display-4 text-primary"></i>
                            </div>
                            <h5>Interior Design</h5>
                            <p class="text-muted">Complete interior design solutions tailored to your style</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-house display-4 text-success"></i>
                            </div>
                            <h5>Room Planning</h5>
                            <p class="text-muted">Professional space planning and layout optimization</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-cart3 display-4 text-warning"></i>
                            </div>
                            <h5>Furniture Sourcing</h5>
                            <p class="text-muted">Curated furniture selection and procurement</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="bi bi-headset display-4 text-info"></i>
                            </div>
                            <h5>Consultation</h5>
                            <p class="text-muted">Expert advice and design consultations</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Transform Your Space?</h2>
            <p class="lead mb-4">Get a free consultation with our design experts</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="pages/contact.php" class="btn btn-light btn-lg">Get Free Consultation</a>
                <a href="pages/shop.php" class="btn btn-outline-light btn-lg">Shop Collection</a>
            </div>
        </div>
    </section>

    <!-- Simple Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="bi bi-palette-fill"></i> Prime Home Hub</h5>
                    <p>Your premier destination for interior design expertise and premium furniture collections.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="pages/shop.php" class="text-decoration-none text-light">Shop</a></li>
                        <li><a href="pages/portfolio.php" class="text-decoration-none text-light">Portfolio</a></li>
                        <li><a href="pages/services.php" class="text-decoration-none text-light">Services</a></li>
                        <li><a href="pages/contact.php" class="text-decoration-none text-light">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p><i class="bi bi-telephone"></i> 256-708292123<br>
                    <i class="bi bi-envelope"></i> primehomehub@gmail.com</p>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="text-center">
                <p>&copy; 2026 Prime Home Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/navigation.js"></script>
    <script>
        // Currency conversion functionality
        let exchangeRate = 3800; // Default UGX to USD rate (will be updated from API)
        let currentCurrency = 'UGX';
        
        // Fetch real-time exchange rate
        async function fetchExchangeRate() {
            try {
                const response = await fetch('https://api.exchangerate-api.com/v4/latest/USD');
                const data = await response.json();
                exchangeRate = data.rates.UGX;
                console.log('Exchange rate updated:', exchangeRate);
            } catch (error) {
                console.log('Using default exchange rate:', exchangeRate);
            }
        }
        
        // Convert price based on selected currency
        function convertPrice(priceInUGX) {
            if (currentCurrency === 'USD') {
                return (priceInUGX / exchangeRate).toFixed(2);
            }
            return priceInUGX;
        }
        
        // Format price with currency symbol
        function formatPrice(price) {
            const convertedPrice = convertPrice(price);
            if (currentCurrency === 'USD') {
                return `$${convertedPrice}`;
            }
            return `UGX ${Number(convertedPrice).toLocaleString()}`;
        }
        
        // Update all prices on the page
        function updateAllPrices() {
            document.querySelectorAll('.price-display').forEach(element => {
                const originalPrice = parseInt(element.dataset.originalPrice);
                element.textContent = formatPrice(originalPrice);
            });
        }
        
        // Toggle currency
        function toggleCurrency() {
            currentCurrency = currentCurrency === 'UGX' ? 'USD' : 'UGX';
            document.getElementById('currency-toggle').textContent = currentCurrency === 'UGX' ? 'Show USD' : 'Show UGX';
            updateAllPrices();
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            fetchExchangeRate();
        });
    </script>
</body>
</html>