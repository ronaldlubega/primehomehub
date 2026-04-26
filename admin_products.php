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
$messageType = '';

// Handle Product Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product']) || isset($_POST['edit_product'])) {
        $name = $_POST['name'];
        $categoryId = $_POST['category_id'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $description = $_POST['description'];
        $status = $_POST['status'] ?? 'active';
        
        $imageUrl = $_POST['current_image'] ?? '';
        
        // Handle Image Upload
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = upload_image($_FILES['product_image'], 'uploads/products/');
            if ($uploadResult) {
                $imageUrl = $uploadResult;
            } else {
                $message = "Image upload failed.";
                $messageType = 'danger';
            }
        }

        if (!$message) {
            if (isset($_POST['add_product'])) {
                $sql = "INSERT INTO products (name, category_id, price, stock, description, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $params = [$name, $categoryId, $price, $stock, $description, $imageUrl, $status];
            } else {
                $id = $_POST['product_id'];
                $sql = "UPDATE products SET name = ?, category_id = ?, price = ?, stock = ?, description = ?, image_url = ?, status = ? WHERE id = ?";
                $params = [$name, $categoryId, $price, $stock, $description, $imageUrl, $status, $id];
            }
            
            $result = $db->execute($sql, $params);
            if ($result['success']) {
                $message = "Product successfully " . (isset($_POST['add_product']) ? "added" : "updated") . "!";
                $messageType = 'success';
            } else {
                $message = "Database error: " . $result['error'];
                $messageType = 'danger';
            }
        }
    } elseif (isset($_POST['delete_product'])) {
        $id = $_POST['product_id'];
        $sql = "UPDATE products SET status = 'deleted' WHERE id = ?"; // Soft delete
        $result = $db->execute($sql, [$id]);
        if ($result['success']) {
            $message = "Product moved to archive.";
            $messageType = 'success';
        }
    }
}

// Ensure relevant categories exist in the code and database
$defaultCategories = [
    ['Living Room', 'living-room', 'Couches, coffee tables, and decor.'],
    ['Bedroom', 'bedroom', 'Beds, dressers, and essentials.'],
    ['Dining Room', 'dining-room', 'Tables, chairs, and sideboards.'],
    ['Office', 'office', 'Desks, chairs, and productivity.'],
    ['Outdoor', 'outdoor', 'Patio furniture and accents.'],
    ['Storage', 'storage', 'Bookshelves and cabinets.'],
    ['Decor', 'decor', 'Vases, art, and final touches.'],
    ['Kitchen', 'kitchen', 'Dining and pantry storage.'],
    ['Lighting', 'lighting', 'Lamps and ambient light.']
];

foreach ($defaultCategories as $cat) {
    $existing = $db->fetch_all("SELECT id FROM categories WHERE slug = ?", [$cat[1]]);
    if (empty($existing)) {
        $db->execute("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)", $cat);
    }
}

// Fetch categories for the dropdown
$categories = $db->fetch_all("SELECT id, name FROM categories ORDER BY name ASC");

// Fetch all products (excluding deleted)
$products = $db->fetch_all(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     JOIN categories c ON p.category_id = c.id 
     WHERE p.status != 'deleted' 
     ORDER BY p.created_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Products - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Century Gothic', 'Segoe UI', sans-serif; background-color: #f1f4f9; }
        .glass-card { background: rgba(255, 255, 255, 0.95); border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; }
        .navbar-brand { font-family: 'Century Gothic', sans-serif; font-weight: 900; text-transform: uppercase; }
        .product-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .btn-premium { background: #000; color: #fff; border: none; font-weight: bold; border-radius: 10px; transition: all 0.3s; }
        .btn-premium:hover { background: #333; color: #fff; transform: translateY(-2px); }
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
    <?php include 'includes/admin_nav.php'; ?>

    <div class="container py-5">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show"><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold m-0"><i class="bi bi-plus-square-fill me-2"></i> Post New Items</h2>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-premium px-4" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-upload"></i> Post Item
                </button>
            </div>
        </div>

        <div class="card glass-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Preview</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">No products posted yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <?php if ($p['image_url']): ?>
                                                <img src="<?php echo $p['image_url']; ?>" class="product-thumb" alt="Product">
                                            <?php else: ?>
                                                <div class="product-thumb bg-light d-flex align-items-center justify-content-center"><i class="bi bi-image text-muted"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['category_name']); ?></span></td>
                                        <td class="fw-bold text-primary">UGX <?php echo number_format($p['price']); ?></td>
                                        <td><?php echo $p['stock']; ?> units</td>
                                        <td>
                                            <span class="badge bg-<?php echo $p['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($p['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $p['id']; ?>"><i class="bi bi-pencil"></i></button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Archive this product?');">
                                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                                <button type="submit" name="delete_product" class="btn btn-sm btn-outline-danger"><i class="bi bi-archive"></i></button>
                                            </form>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>
            <div class="modal fade" id="editModal<?php echo $p['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form method="POST" enctype="multipart/form-data" class="modal-content glass-card">
                        <div class="modal-header"><h5>Edit Product Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body row g-3">
                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                            <input type="hidden" name="current_image" value="<?php echo $p['image_url']; ?>">
                            <div class="col-md-8"><label class="form-label">Product Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($p['name']); ?>" required></div>
                            <div class="col-md-4"><label class="form-label">Category</label><select name="category_id" class="form-select"><?php foreach ($categories as $cat): ?><option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $p['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option><?php endforeach; ?></select></div>
                            <div class="col-md-4"><label class="form-label">Price (UGX)</label><input type="number" name="price" class="form-control" value="<?php echo $p['price']; ?>" required></div>
                            <div class="col-md-4"><label class="form-label">Stock</label><input type="number" name="stock" class="form-control" value="<?php echo $p['stock']; ?>" required></div>
                            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?php echo $p['status'] === 'active' ? 'selected' : ''; ?>>Active</option><option value="inactive" <?php echo $p['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option></select></div>
                            <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($p['description']); ?></textarea></div>
                            <div class="col-12"><label class="form-label">Update Image</label><input type="file" name="product_image" class="form-control" accept="image/*"></div>
                        </div>
                        <div class="modal-footer"><button type="submit" name="edit_product" class="btn btn-premium px-4">Update Product</button></div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Add Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" enctype="multipart/form-data" class="modal-content glass-card">
                <div class="modal-header"><h5>Post New Furniture / Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-8"><label class="form-label">Product Name</label><input type="text" name="name" class="form-control" placeholder="e.g., Luxury Leather Sofa" required></div>
                    <div class="col-md-4"><label class="form-label">Category</label><select name="category_id" class="form-select" required><option value="">Select Category...</option><?php foreach ($categories as $cat): ?><option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Price (UGX)</label><input type="number" name="price" class="form-control" placeholder="0.00" required></div>
                    <div class="col-md-6"><label class="form-label">Stock Quantity</label><input type="number" name="stock" class="form-control" placeholder="10" required></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" placeholder="Tell customers about this item..."></textarea></div>
                    <div class="col-12"><label class="form-label">Item Photo</label><input type="file" name="product_image" class="form-control" accept="image/*" required></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="add_product" class="btn btn-premium px-5 py-2">Post Product</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
