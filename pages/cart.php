<?php
require_once 'navigation.php';

// Check if user is logged in (additional check since navigation.php handles this)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Handle cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_item'])) {
        $item_id = $_POST['remove_item'];
        if (isset($_SESSION['cart'][$item_id])) {
            unset($_SESSION['cart'][$item_id]);
        }
        header('Location: cart.php');
        exit();
    }
    
    if (isset($_POST['update_quantity'])) {
        $item_id = $_POST['update_quantity'];
        $quantity = (int)$_POST['quantity'];
        if ($quantity > 0 && isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]['quantity'] = $quantity;
        }
        header('Location: cart.php');
        exit();
    }
    
    if (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
        header('Location: cart.php');
        exit();
    }
}

require_once '../includes/database.php';
$db = new Database();

// Get cart item IDs
$item_ids = array_keys($_SESSION['cart'] ?? []);
$products = [];

if (!empty($item_ids)) {
    $ids_string = implode(',', array_fill(0, count($item_ids), '?'));
    $products = $db->fetch_all(
        "SELECT p.*, c.name as category_name 
         FROM products p 
         JOIN categories c ON p.category_id = c.id 
         WHERE p.id IN ($ids_string)",
        $item_ids
    );
}

// Get cart items
$cart_items = [];
$cart_total = 0;
$cart_count = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item_id => $cart_item) {
        // Find product details
        $product = null;
        foreach ($products as $p) {
            if ($p['id'] == $item_id) {
                $product = $p;
                break;
            }
        }
        
        if ($product) {
            $quantity = $cart_item['quantity'] ?? 1;
            $subtotal = $product['price'] * $quantity;
            
            $cart_items[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'image_url' => $product['image_url'] ?? '',
                'category' => $product['category_name'] ?? ''
            ];
            
            $cart_total += $subtotal;
            $cart_count += $quantity;
        }
    }
}

// Update cart count in session
$_SESSION['cart_count'] = $cart_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Cart Content -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($cart_items)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cart-x display-1 text-muted mb-3"></i>
                    <h3 class="text-muted">Your cart is empty</h3>
                    <p class="text-muted">Add some items to your cart to get started!</p>
                    <a href="shop.php" class="btn btn-primary mt-3">
                        <i class="bi bi-arrow-left"></i> To Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-cart3"></i> Cart Items (<?php echo $cart_count; ?>)
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl shadow-sm mb-4" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: white; border: 1px solid #e5e7eb; border-radius: 0.75rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                      
                                      <div class="flex items-center space-x-4" style="display: flex; align-items: center; gap: 1rem;">
                                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200" style="width: 5rem; height: 5rem; background: #f3f4f6; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb;">
                                          <?php if ($item['image_url']): ?>
                                              <img src="../<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                   class="w-full h-full object-cover rounded-lg" style="width: 100%; height: 100%; object-fit: cover; border-radius: 0.5rem;">
                                          <?php else: ?>
                                              <i class="bi bi-image text-gray-400" style="font-size: 2rem; color: #9ca3af;"></i>
                                          <?php endif; ?>
                                        </div>
                                        <div>
                                          <h3 class="text-lg font-bold text-gray-900" style="font-size: 1.125rem; font-weight: 700; color: #111827;"><?php echo htmlspecialchars($item['name']); ?></h3>
                                          <p class="text-sm text-gray-500" style="font-size: 0.875rem; color: #6b7280;">Price: UGX <?php echo number_format($item['price']); ?> / unit</p>
                                        </div>
                                      </div>

                                      <div class="flex flex-col items-center" style="display: flex; flex-direction: column; align-items: center;">
                                        <div class="flex items-center bg-gray-50 border border-gray-200 rounded-full px-2 py-1" style="display: flex; align-items: center; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 9999px; padding: 0.25rem 0.5rem;">
                                          <form method="post" id="quantity-form-<?php echo $item['id']; ?>" style="display: flex; align-items: center;">
                                            <input type="hidden" name="update_quantity" value="<?php echo $item['id']; ?>">
                                            <button type="button" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 text-gray-400 text-xl" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)" style="width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: transparent; border: none; cursor: pointer; font-size: 1.25rem; color: #9ca3af;" type="button">−</button>
                                            <input type="text" id="quantity-<?php echo $item['id']; ?>" name="quantity" value="<?php echo $item['quantity']; ?>" class="w-10 text-center bg-transparent border-none focus:outline-none font-semibold text-gray-700" style="width: 2.5rem; text-align: center; background: transparent; border: none; outline: none; font-weight: 600; color: #374151;" onchange="submitQuantityForm(<?php echo $item['id']; ?>)">
                                            <button type="button" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 text-gray-400 text-xl" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)" style="width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: transparent; border: none; cursor: pointer; font-size: 1.25rem; color: #9ca3af;" type="button">+</button>
                                          </form>
                                        </div>
                                        <span class="text-xs text-gray-500 mt-1 font-medium" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem; font-weight: 500;">UGX <?php echo number_format($item['price']); ?> each</span>
                                      </div>

                                      <div class="flex items-center space-x-6" style="display: flex; align-items: center; gap: 1.5rem;">
                                        <div class="flex space-x-3" style="display: flex; gap: 0.75rem;">
                                          <form method="post" class="d-inline">
                                            <input type="hidden" name="remove_item" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="text-red-300 hover:text-red-600" style="color: #fca5a5; background: transparent; border: none; cursor: pointer; padding: 0.5rem;" title="Remove Item">
                                              <i class="bi bi-trash" style="font-size: 1.5rem;"></i>
                                            </button>
                                          </form>
                                        </div>
                                        <div class="text-right">
                                          <p class="text-sm font-bold text-blue-600" style="font-size: 0.875rem; font-weight: 700; color: #2563eb; text-align: right;">UGX</p>
                                          <p class="text-2xl font-black text-blue-600 leading-tight" style="font-size: 1.5rem; font-weight: 900; color: #2563eb; line-height: 1.25; text-align: right;"><?php echo number_format($item['subtotal']); ?></p>
                                        </div>
                                      </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex gap-2 justify-content-between align-items-center">
                                            <a href="shop.php" class="btn btn-outline-primary btn-sm flex-fill">
                                                <i class="bi bi-arrow-left"></i> To Shopping
                                            </a>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="clear_cart" value="1">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to clear your cart?')" style="min-width: 120px;">
                                                    <i class="bi bi-trash"></i> Clear Cart
                                                </button>
                                            </form>
                                            <button class="btn btn-success btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                                <i class="bi bi-credit-card"></i> To Checkout
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-calculator"></i> Order Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>UGX <?php echo number_format($cart_total); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Delivery:</span>
                                    <span>UGX 50,000</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax:</span>
                                    <span>UGX <?php echo number_format($cart_total * 0.18); ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total:</strong>
                                    <strong class="text-primary">UGX <?php echo number_format($cart_total + 50000 + ($cart_total * 0.18)); ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-info-circle"></i> Delivery Info
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small mb-1"><strong>Estimated Delivery:</strong></p>
                                <p class="small text-muted mb-3">3-5 business days</p>
                                <p class="small mb-1"><strong>Delivery Areas:</strong></p>
                                <p class="small text-muted">Kampala, Entebbe, Mukono, and surrounding areas</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Checkout Modal -->
    <?php if (!empty($cart_items)): ?>
    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 text-white py-2 px-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h6 class="modal-title fw-bold text-uppercase mb-0" style="font-size: 0.85rem; letter-spacing: 1.5px;">
                        <i class="bi bi-shield-check me-2"></i> Secure Checkout
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size: 0.75rem;"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="post" action="checkout.php" id="checkout-form">
                        <div class="row g-4">
                            <!-- Left Column: Personal Info -->
                            <div class="col-md-6 border-end pe-md-4">
                                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-person-circle me-2"></i> Personal Details</h6>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Full Name</label>
                                    <input type="text" class="form-control" name="customer_name" 
                                           value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" required placeholder="Enter your full name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Email Address</label>
                                    <input type="email" class="form-control" name="customer_email" 
                                           value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required placeholder="example@mail.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small text-muted">Phone Number</label>
                                    <input type="tel" class="form-control" name="customer_phone" required placeholder="+256 ...">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small text-muted">Payment Method</label>
                                    <select class="form-select" name="payment_method" required>
                                        <option value="">Choose payment...</option>
                                        <option value="cash_on_delivery">Cash on Delivery</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Right Column: Delivery & Summary -->
                            <div class="col-md-6 ps-md-4">
                                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-truck me-2"></i> Delivery & Summary</h6>
                                <div class="mb-4">
                                    <label class="form-label small text-muted">Delivery Address</label>
                                    <textarea class="form-control" name="delivery_address" rows="3" required placeholder="Street name, house number, apartment..."></textarea>
                                </div>
                                
                                <div class="bg-light p-3 rounded-3 mb-0">
                                    <div class="d-flex justify-content-between mb-1 small text-muted">
                                        <span>Order Total:</span>
                                        <span>UGX <?php echo number_format($cart_total + 50000 + ($cart_total * 0.18)); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-0">
                                        <span class="fw-bold">Payable Now:</span>
                                        <span class="fw-bold text-success">UGX <?php echo number_format($cart_total + 50000 + ($cart_total * 0.18)); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="items" value="<?php echo htmlspecialchars(json_encode($cart_items)); ?>">
                        <input type="hidden" name="total" value="<?php echo $cart_total + 50000 + ($cart_total * 0.18); ?>">
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-link text-muted text-decoration-none fw-bold small me-auto" data-bs-dismiss="modal">Cancel Order</button>
                    <button type="submit" form="checkout-form" class="btn btn-lg px-5 fw-bold text-white shadow-sm" 
                            style="background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%); border: none; border-radius: 12px;">
                        <i class="bi bi-lock-fill me-2"></i> Confirm & Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .cart-item-image {
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .cart-item-image:hover {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0,123,255,0.15);
        }
        .quantity-control .btn {
            border-radius: 0;
        }
        .quantity-control .btn:first-child {
            border-radius: 0.25rem 0 0 0.25rem;
        }
        .quantity-control .btn:last-child:not(:nth-child(3)) {
            border-radius: 0 0.25rem 0.25rem 0;
        }
        .item-total {
            font-size: 1.1rem;
        }
        .cart-item-row {
            transition: background-color 0.2s ease;
        }
        .cart-item-row:hover {
            background-color: #f8f9fa;
        }
    </style>
    <script>
        function updateQuantity(itemId, change) {
            const input = document.getElementById('quantity-' + itemId);
            const currentValue = parseInt(input.value);
            const newValue = currentValue + change;
            
            // Validate quantity range
            if (newValue >= 1 && newValue <= 10) {
                input.value = newValue;
                submitQuantityForm(itemId);
            }
        }
        
        function submitQuantityForm(itemId) {
            const form = document.getElementById('quantity-form-' + itemId);
            if (form) {
                form.submit();
            }
        }
    </script>
</body>
</html>
