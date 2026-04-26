<?php
require_once 'navigation.php';

require_once '../includes/database.php';
$db = new Database();

// Fetch live products from DB
$products = $db->fetch_all(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     JOIN categories c ON p.category_id = c.id 
     WHERE p.status = 'active' 
     ORDER BY p.created_at DESC"
);

// Fetch live categories for filters
$categories = $db->fetch_all("SELECT * FROM categories ORDER BY name ASC");

// Handle cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php?msg=auth_required');
        exit();
    }
    
    $product_id = $_POST['add_to_cart'];
    
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
    
    header('Location: shop.php?added=1');
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
    <title>Shop - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Currency Toggle Button -->
    <button class="btn btn-outline-light position-fixed" id="currency-toggle" onclick="toggleCurrency()" style="top: 20px; right: 20px; z-index: 1000; border-radius: 8px; padding: 8px 16px;">
        <i class="bi bi-currency-exchange me-1"></i> Show USD
    </button>
    
    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Shop</h1>
            <p class="lead">Browse our premium furniture collection</p>
        </div>
    </section>

    <!-- Success Message -->
    <?php if ($added_message): ?>
        <div class="container mt-3">
            <?php echo $added_message; ?>
        </div>
    <?php endif; ?>

    <!-- Shop Content -->
    <section class="py-5">
        <div class="container">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="categoryFilter" class="form-label">Category</label>
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="priceRange" class="form-label">Max Price</label>
                    <select class="form-select" id="priceRange">
                        <option value="">All Prices</option>
                        <option value="100000">Under UGX 100,000</option>
                        <option value="200000">Under UGX 200,000</option>
                        <option value="500000">Under UGX 500,000</option>
                        <option value="1000000">Under UGX 1,000,000</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sortBy" class="form-label">Sort By</label>
                    <select class="form-select" id="sortBy">
                        <option value="name">Name</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="rating">Rating</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="searchInput" class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search products...">
                </div>
            </div>

            <!-- Products Grid -->
            <div class="row g-4" id="productsGrid">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 col-lg-4 product-item" 
                         data-category="<?php echo $product['category_id']; ?>"
                         data-price="<?php echo $product['price']; ?>"
                         data-name="<?php echo strtolower($product['name']); ?>"
                         data-rating="<?php echo $product['rating']; ?>">
                        <div class="card h-100">
                            <div class="position-relative">
                                <?php if ($product['image_url']): ?>
                                    <img src="../<?php echo $product['image_url']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                         class="card-img-top" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="bi bi-image display-4 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="position-absolute top-0 end-0 p-2">
                                    <span class="badge bg-<?php echo $product['stock'] > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $product['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </span>
                                </div>
                                
                                <?php if (isset($product['featured']) && $product['featured']): ?>
                                    <div class="position-absolute top-0 start-0 p-2">
                                        <span class="badge bg-warning">Featured</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <small class="text-muted mb-2"><?php echo htmlspecialchars($product['category_name']); ?></small>
                                <p class="card-text"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="h5 text-primary mb-0">UGX <?php echo number_format($product['price']); ?></span>
                                        <div class="text-warning">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i < $product['rating'] ? '-fill' : ''; ?>"></i>
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
            </div>
        </div>
    </section>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Product Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="productDetails"></div>
                </div>
            </div>
        </div>
    </div>

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
                        <li><a href="../index.php" class="text-decoration-none text-light">Home</a></li>
                        <li><a href="shop.php" class="text-decoration-none text-light">Shop</a></li>
                        <li><a href="services.php" class="text-decoration-none text-light">Services</a></li>
                        <li><a href="contact.php" class="text-decoration-none text-light">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p><i class="bi bi-telephone"></i> (555) 123-4567<br>
                    <i class="bi bi-envelope"></i> info@primehomehub.com</p>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="text-center">
                <p>&copy; 2026 Prime Home Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter functionality
        function filterProducts() {
            const category = document.getElementById('categoryFilter').value;
            const maxPrice = document.getElementById('priceRange').value;
            const sortBy = document.getElementById('sortBy').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            const products = document.querySelectorAll('.product-item');
            
            products.forEach(product => {
                const productCategory = product.dataset.category;
                const productPrice = parseInt(product.dataset.price);
                const productName = product.dataset.name;
                const productRating = parseInt(product.dataset.rating);
                
                let show = true;
                
                // Category filter
                if (category && productCategory !== category) {
                    show = false;
                }
                
                // Price filter
                if (maxPrice && productPrice > maxPrice) {
                    show = false;
                }
                
                // Search filter
                if (searchTerm && !productName.includes(searchTerm)) {
                    show = false;
                }
                
                product.style.display = show ? '' : 'none';
            });
            
            // Sort products
            const visibleProducts = Array.from(document.querySelectorAll('.product-item')).filter(p => p.style.display !== 'none');
            
            visibleProducts.sort((a, b) => {
                switch(sortBy) {
                    case 'price-low':
                        return parseInt(a.dataset.price) - parseInt(b.dataset.price);
                    case 'price-high':
                        return parseInt(b.dataset.price) - parseInt(a.dataset.price);
                    case 'rating':
                        return parseInt(b.dataset.rating) - parseInt(a.dataset.rating);
                    case 'name':
                    default:
                        return a.dataset.name.localeCompare(b.dataset.name);
                }
            });
            
            const container = document.getElementById('productsGrid');
            visibleProducts.forEach(product => {
                container.appendChild(product);
            });
        }
        
        // View product details
        function viewProduct(productId) {
            const products = <?php echo json_encode($products); ?>;
            const product = products.find(p => p.id === productId);
            
            if (product) {
                document.getElementById('productDetails').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            ${product.image_url ? 
                                `<img src="${product.image_url}" class="img-fluid rounded" alt="${product.name}">` : 
                                `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                    <i class="bi bi-image display-4 text-muted"></i>
                                </div>`
                            }
                        </div>
                        <div class="col-md-6">
                            <h4>${product.name}</h4>
                            <p class="text-muted">${product.category_name}</p>
                            <div class="text-warning mb-3">
                                ${Array(product.rating).fill('<i class="bi bi-star-fill"></i>').join('')}
                                ${Array(5 - product.rating).fill('<i class="bi bi-star"></i>').join('')}
                            </div>
                            <p>${product.description}</p>
                            <h4 class="text-primary">UGX ${number_format(product.price)}</h4>
                            <p><strong>Stock:</strong> ${product.stock > 0 ? product.stock + ' available' : 'Out of stock'}</p>
                            ${product.stock > 0 ? 
                                `<form method="post" style="display: inline;">
                                    <input type="hidden" name="add_to_cart" value="1">
                                    <input type="hidden" name="product_id" value="${product.id}">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                </form>` : 
                                `<button class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-x-circle"></i> Out of Stock
                                </button>`
                            }
                        </div>
                    </div>
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('productModal'));
                modal.show();
            }
        }
        
        // Add event listeners
        document.getElementById('categoryFilter').addEventListener('change', filterProducts);
        document.getElementById('priceRange').addEventListener('change', filterProducts);
        document.getElementById('sortBy').addEventListener('change', filterProducts);
        document.getElementById('searchInput').addEventListener('input', filterProducts);
        
        // Make product cards clickable for details
        document.querySelectorAll('.card').forEach(card => {
            card.style.cursor = 'pointer';
            card.addEventListener('click', function(e) {
                if (!e.target.closest('button')) {
                    const productId = parseInt(this.querySelector('.btn-primary').getAttribute('onclick').match(/\d+/)[0]);
                    viewProduct(productId);
                }
            });
        });
    </script>
</body>
</html>
