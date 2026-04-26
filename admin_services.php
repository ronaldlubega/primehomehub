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

// Handle Add/Edit/Delete Service
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $icon = $_POST['icon'];
        
        $sql = "INSERT INTO services (name, description, price, icon) VALUES (?, ?, ?, ?)";
        $result = $db->execute($sql, [$name, $description, $price, $icon]);
        if ($result['success']) {
            $message = "Service added successfully!";
            $messageType = 'success';
        } else {
            $message = "Error adding service: " . $result['error'];
            $messageType = 'danger';
        }
    } elseif (isset($_POST['edit_service'])) {
        $id = $_POST['service_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $icon = $_POST['icon'];
        
        $sql = "UPDATE services SET name = ?, description = ?, price = ?, icon = ? WHERE id = ?";
        $result = $db->execute($sql, [$name, $description, $price, $icon, $id]);
        if ($result['success']) {
            $message = "Service updated successfully!";
            $messageType = 'success';
        } else {
            $message = "Error updating service: " . $result['error'];
            $messageType = 'danger';
        }
    } elseif (isset($_POST['delete_service'])) {
        $id = $_POST['service_id'];
        $sql = "DELETE FROM services WHERE id = ?";
        $result = $db->execute($sql, [$id]);
        if ($result['success']) {
            $message = "Service deleted successfully!";
            $messageType = 'success';
        } else {
            $message = "Error deleting service: " . $result['error'];
            $messageType = 'danger';
        }
    }
}

// Fetch all services
$services = $db->fetch_all("SELECT * FROM services ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Century Gothic', 'Segoe UI', sans-serif; background-color: #f1f4f9; }
        .glass-card { background: rgba(255, 255, 255, 0.95); border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; }
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

        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold m-0"><i class="bi bi-tools me-2"></i> Service Management</h2>
                <p class="text-muted mb-0">Manage interior design and consultation services</p>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-premium px-4" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="bi bi-plus-lg"></i> New Service
                </button>
            </div>
        </div>

        <div class="card glass-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Service Name</th>
                                <th>Description</th>
                                <th>Price (UGX)</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($services)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No services found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($services as $s): ?>
                                    <tr>
                                        <td><i class="bi <?php echo $s['icon']; ?> fs-4"></i></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($s['name']); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars(substr($s['description'], 0, 100)); ?>...</td>
                                        <td class="fw-bold">UGX <?php echo number_format($s['price']); ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-premium px-3" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $s['id']; ?>"><i class="bi bi-pencil"></i></button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this service?');">
                                                <input type="hidden" name="service_id" value="<?php echo $s['id']; ?>">
                                                <button type="submit" name="delete_service" class="btn btn-sm btn-outline-danger ms-1"><i class="bi bi-trash"></i></button>
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
    <?php foreach ($services as $s): ?>
        <div class="modal fade" id="editModal<?php echo $s['id']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" class="modal-content glass-card">
                    <div class="modal-header border-0"><h5>Edit Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body row g-3">
                        <input type="hidden" name="service_id" value="<?php echo $s['id']; ?>">
                        <div class="col-12"><label class="form-label">Service Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($s['name']); ?>" required></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($s['description']); ?></textarea></div>
                        <div class="col-md-6"><label class="form-label">Price</label><input type="number" name="price" class="form-control" value="<?php echo $s['price']; ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Icon Class</label><input type="text" name="icon" class="form-control" value="<?php echo htmlspecialchars($s['icon']); ?>" placeholder="bi-palette"></div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" name="edit_service" class="btn btn-premium px-4">Update Service</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Add Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content glass-card">
                <div class="modal-header border-0"><h5>Add New Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-12"><label class="form-label">Service Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" placeholder="Describe the service..."></textarea></div>
                    <div class="col-md-6"><label class="form-label">Price (UGX)</label><input type="number" name="price" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Icon Class (Bootstrap)</label><input type="text" name="icon" class="form-control" placeholder="bi-palette"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="add_service" class="btn btn-premium px-4">Post Service</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
