<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=profile');
    exit();
}

// Get database connection
$db = new Database();
$userId = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address";
    } else {
        // Update user profile
        $result = $db->execute(
            "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?",
            [$name, $email, $phone, $address, $userId]
        );
        
        if ($result['success']) {
            $success = "Profile updated successfully!";
            // Update session
            $_SESSION['name'] = $name;
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        $error = "New passwords do not match";
    } elseif (strlen($newPassword) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Verify current password
        $user = $db->fetch_one("SELECT password FROM users WHERE id = ?", [$userId]);
        
        if (password_verify($currentPassword, $user['password'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $result = $db->execute(
                "UPDATE users SET password = ? WHERE id = ?",
                [$hashedPassword, $userId]
            );
            
            if ($result['success']) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to change password. Please try again.";
            }
        } else {
            $error = "Current password is incorrect";
        }
    }
}

// Get user data
$user = $db->fetch_one("SELECT * FROM users WHERE id = ?", [$userId]);

// Get user's order statistics
$orderStats = $db->fetch_one(
    "SELECT COUNT(*) as total_orders, SUM(total_amount) as total_spent FROM orders WHERE user_id = ? AND status != 'cancelled'",
    [$userId]
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">My Profile</h1>
            <p class="lead">Manage your account information</p>
        </div>
    </section>

    <!-- Profile Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <!-- Profile Card -->
                    <div class="card text-center mb-4">
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 100px; height: 100px; font-size: 2rem;">
                                    <i class="bi bi-person"></i>
                                </div>
                            </div>
                            <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                            <span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span>
                        </div>
                    </div>

                    <!-- Stats Card -->
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Account Statistics</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Orders:</span>
                                <strong><?php echo $orderStats['total_orders']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Spent:</span>
                                <strong>UGX <?php echo number_format($orderStats['total_spent'] ?? 0); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Member Since:</span>
                                <strong><?php echo date('M j, Y', strtotime($user['created_at'])); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <!-- Messages -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="address" name="address" 
                                               value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                                    </div>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Profile
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           minlength="6" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="6" required>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    <i class="bi bi-lock"></i> Change Password
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Account Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Account Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="orders.php" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-box"></i> View Order History
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="wishlist.php" class="btn btn-outline-danger w-100">
                                        <i class="bi bi-heart"></i> View Wishlist
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <button class="btn btn-outline-info w-100" onclick="exportData()">
                                        <i class="bi bi-download"></i> Export My Data
                                    </button>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <button class="btn btn-outline-warning w-100" onclick="deleteAccount()">
                                        <i class="bi bi-trash"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/navigation.js"></script>
    <script>
        function exportData() {
            // In a real application, this would fetch and export user data
            const userData = {
                name: '<?php echo htmlspecialchars($user["name"]); ?>',
                email: '<?php echo htmlspecialchars($user["email"]); ?>',
                orders: <?php echo $orderStats['total_orders']; ?>,
                totalSpent: <?php echo $orderStats['total_spent'] ?? 0; ?>,
                memberSince: '<?php echo date('M j, Y', strtotime($user['created_at'])); ?>'
            };
            
            const dataStr = JSON.stringify(userData, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = 'my-data.json';
            a.click();
            
            URL.revokeObjectURL(url);
        }
        
        function deleteAccount() {
            const confirmation = confirm('Are you sure you want to delete your account? This action cannot be undone and will remove all your data including orders and wishlist items.');
            
            if (confirmation) {
                const finalConfirmation = prompt('Type "DELETE" to confirm account deletion:');
                
                if (finalConfirmation === 'DELETE') {
                    // In a real application, this would send a delete request to the server
                    alert('Account deletion request submitted. You will receive an email confirmation shortly.');
                    // Redirect to logout
                    window.location.href = '../logout.php';
                } else {
                    alert('Account deletion cancelled.');
                }
            }
        }
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
