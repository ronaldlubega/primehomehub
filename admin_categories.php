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

// Handle Add/Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    if (isset($_POST['add_category'])) {
        $sql = "INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)";
        $result = $db->execute($sql, [$name, $slug, $description]);
        if ($result['success']) {
            $message = "Category '$name' added successfully!";
            $messageType = 'success';
        } else {
            $message = "Error adding category: " . $result['error'];
            $messageType = 'danger';
        }
    } elseif (isset($_POST['edit_category'])) {
        $id = $_POST['category_id'];
        $sql = "UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?";
        $result = $db->execute($sql, [$name, $slug, $description, $id]);
        if ($result['success']) {
            $message = "Category '$name' updated successfully!";
            $messageType = 'success';
        } else {
            $message = "Error updating category: " . $result['error'];
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete_category'])) {
        $id = $_POST['category_id'];
        $sql = "DELETE FROM categories WHERE id = ?";
        $result = $db->execute($sql, [$id]);
        if ($result['success']) {
            $message = "Category deleted successfully!";
            $messageType = 'success';
        } else {
            $message = "Error: Cannot delete category that has products associated with it.";
            $messageType = 'danger';
        }
    }
}

// Fetch all categories
$categories = $db->fetch_all("SELECT * FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Century Gothic', 'Segoe UI', sans-serif; background-color: #f8f9fa; }
        .glass-card { 
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px; 
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
        }
        .navbar-brand { font-family: 'Century Gothic', sans-serif; font-weight: 900; text-transform: uppercase; }
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

        <div class="row mb-4">
            <div class="col-md-8"><h2>Category Management</h2></div>
            <div class="col-md-4 text-end">
                <button class="btn btn-premium" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-lg"></i> New Category
                </button>
            </div>
        </div>

            <div class="card glass-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td><code>/<?php echo htmlspecialchars($cat['slug']); ?></code></td>
                                <td class="small text-muted"><?php echo htmlspecialchars(substr($cat['description'], 0, 80)); ?>...</td>
                                <td class="text-end">
                                    <button class="btn btn-premium px-4" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $cat['id']; ?>"><i class="bi bi-pencil"></i></button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" name="delete_category" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    <?php foreach ($categories as $cat): ?>
        <div class="modal fade" id="editModal<?php echo $cat['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" class="modal-content glass-card">
                    <div class="modal-header border-0"><h5>Edit Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($cat['name']); ?>" required></div>
                        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($cat['description']); ?></textarea></div>
                    </div>
                    <div class="modal-footer border-0">
                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" name="edit_category" class="btn btn-premium px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

    <!-- Add Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content glass-card">
                <div class="modal-header"><h5>Add New Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" placeholder="e.g., Living Room" required></div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" placeholder="Describe this category..."></textarea></div>
                </div>
                <div class="modal-footer"><button type="submit" name="add_category" class="btn btn-premium">Create Category</button></div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
