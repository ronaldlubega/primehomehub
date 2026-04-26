<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=wishlist');
    exit();
}

// Get database connection
$db = new Database();
$userId = $_SESSION['user_id'];

// Handle wishlist actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_wishlist'])) {
        $productId = $_POST['product_id'];
        
        // Check if already in wishlist
        $exists = $db->fetch_one(
            "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );
        
        if (!$exists) {
            $db->execute(
                "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)",
                [$userId, $productId]
            );
            $_SESSION['success'] = "Product added to wishlist!";
        } else {
            $_SESSION['error'] = "Product already in wishlist!";
        }
        header('Location: wishlist.php');
        exit();
    }
    
    if (isset($_POST['remove_from_wishlist'])) {
        $itemId = $_POST['item_id'];
        $db->execute(
            "DELETE FROM wishlist WHERE id = ? AND user_id = ?",
            [$itemId, $userId]
        );
        $_SESSION['success'] = "Item removed from wishlist!";
        header('Location: wishlist.php');
        exit();
    }
    
    if (isset($_POST['add_all_to_cart'])) {
        // Get all wishlist items
        $wishlistItems = $db->fetch_all(
            "SELECT product_id FROM wishlist WHERE user_id = ?",
            [$userId]
        );
        
        // Add each to cart (in a real app, this would use cart session/API)
        foreach ($wishlistItems as $item) {
            // Simulate adding to cart
            $_SESSION['cart'][] = ['product_id' => $item['product_id'], 'quantity' => 1];
        }
        
        $_SESSION['success'] = "All items added to cart!";
        header('Location: cart.php');
        exit();
    }
}

// Get wishlist items with product details
$wishlistItems = $db->fetch_all(
    "SELECT w.*, p.name, p.price, p.image_url, p.stock, c.name as category_name
     FROM wishlist w 
     JOIN products p ON w.product_id = p.id 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE w.user_id = ? 
     ORDER BY w.created_at DESC",
    [$userId]
);

// Calculate total value
$totalValue = 0;
foreach ($wishlistItems as $item) {
    $totalValue += $item['price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">My Wishlist</h1>
            <p class="lead">Items you love and want to buy later</p>
        </div>
    </section>

    <!-- Wishlist Content -->
    <section class="py-5">
        <div class="container">
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Wishlist Summary -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-primary"><?php echo count($wishlistItems); ?></h3>
                            <p class="text-muted">Items in Wishlist</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success">UGX <?php echo number_format($totalValue); ?></h3>
                            <p class="text-muted">Total Value</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-info"><?php echo date('M j, Y'); ?></h3>
                            <p class="text-muted">Last Updated</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wishlist Actions -->
            <?php if (!empty($wishlistItems)): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>Your Wishlist Items</h5>
                    <div class="d-flex gap-2">
                        <form method="POST" class="d-inline">
                            <button type="submit" name="add_all_to_cart" class="btn btn-success">
                                <i class="bi bi-cart-plus"></i> Add All to Cart
                            </button>
                        </form>
                        <button class="btn btn-outline-primary" onclick="shareWishlist()">
                            <i class="bi bi-share"></i> Share Wishlist
                        </button>
                        <button class="btn btn-outline-info" onclick="exportWishlist()">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Wishlist Items -->
            <div class="row">
                <?php if (empty($wishlistItems)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-heart display-1 text-muted"></i>
                            <h3 class="mt-3">Your Wishlist is Empty</h3>
                            <p class="text-muted">Start adding items you love to your wishlist!</p>
                            <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($wishlistItems as $item): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="position-relative">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             class="card-img-top" style="height: 200px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                             style="height: 200px;">
                                            <i class="bi bi-image display-4 text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-<?php echo $item['stock'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $item['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <?php if ($item['category_name']): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($item['category_name']); ?></small>
                                    <?php endif; ?>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="h5 text-primary mb-0">UGX <?php echo number_format($item['price']); ?></span>
                                            <small class="text-muted">Added <?php echo date('M j', strtotime($item['created_at'])); ?></small>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary" onclick="addToCart(<?php echo $item['product_id']; ?>)" 
                                                    <?php echo $item['stock'] <= 0 ? 'disabled' : ''; ?>>
                                                <i class="bi bi-cart-plus"></i> 
                                                <?php echo $item['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                                            </button>
                                            
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-info btn-sm" onclick="viewProduct(<?php echo $item['product_id']; ?>)">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                                
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" name="remove_from_wishlist" class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-trash"></i> Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Recommendations -->
            <?php if (!empty($wishlistItems)): ?>
                <div class="mt-5">
                    <h4 class="mb-4">You Might Also Like</h4>
                    <div class="row g-4">
                        <?php for ($i = 0; $i < 3; $i++): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card">
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                         style="height: 200px;">
                                        <i class="bi bi-image display-4 text-muted"></i>
                                    </div>
                                    <div class="card-body">
                                        <h6>Recommended Product <?php echo $i + 1; ?></h6>
                                        <p class="text-muted small">Based on your wishlist preferences</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h6 text-primary mb-0">UGX <?php echo number_format(rand(10000, 100000)); ?></span>
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewProduct(<?php echo rand(100, 999); ?>)">
                                                View
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/navigation.js"></script>
    <script>
        function addToCart(productId) {
            // In a real application, this would add to the cart via API
            console.log('Adding to cart:', productId);
            
            // Simulate adding to cart
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({ id: productId, quantity: 1 });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartBadge();
            
            alert('Product added to cart!');
        }
        
        function viewProduct(productId) {
            // In a real application, this would navigate to product page
            console.log('Viewing product:', productId);
            alert('Product details would be shown here.');
        }
        
        function shareWishlist() {
            const wishlistUrl = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: 'My Wishlist - Prime Home Hub',
                    text: 'Check out my wishlist from Prime Home Hub!',
                    url: wishlistUrl
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(wishlistUrl).then(() => {
                    alert('Wishlist link copied to clipboard!');
                });
            }
        }
        
        function exportWishlist() {
            const wishlistData = <?php echo json_encode($wishlistItems); ?>;
            
            // Create CSV content
            let csvContent = "Product Name,Price,Category,Added Date\n";
            wishlistData.forEach(item => {
                csvContent += `"${item.name}",${item.price},"${item.category_name || 'N/A'}","${new Date(item.created_at).toLocaleDateString()}"\n`;
            });
            
            // Download as CSV file
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'wishlist.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        function updateCartBadge() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const badge = document.getElementById('cart-badge');
            if (badge) {
                badge.textContent = totalItems;
            }
        }
        
        // Initialize cart badge on page load
        document.addEventListener('DOMContentLoaded', updateCartBadge);
    </script>
</body>
</html>
