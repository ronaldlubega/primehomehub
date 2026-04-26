<?php
require_once 'navigation.php';
require_once '../includes/database.php';
$db = new Database();

// Fetch live services from MySQL
$services = $db->fetch_all("SELECT * FROM services ORDER BY created_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Our Services</h1>
            <p class="lead">Professional interior design solutions</p>
        </div>
    </section>

    <!-- Services Content -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php if (empty($services)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-tools display-1 text-muted"></i>
                            <h3 class="mt-3">Services Coming Soon</h3>
                            <p class="text-muted">We're preparing our service offerings. Check back soon!</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <?php if ($service['icon']): ?>
                                            <i class="<?php echo $service['icon']; ?> display-4 text-primary"></i>
                                        <?php else: ?>
                                            <i class="bi bi-palette display-4 text-primary"></i>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
                                    <?php if ($service['price']): ?>
                                        <p class="text-primary fw-bold">UGX <?php echo number_format($service['price']); ?></p>
                                    <?php endif; ?>
                                    <button class="btn btn-primary" onclick="bookService(<?php echo $service['id']; ?>)">Book Service</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
    <script src="../js/navigation.js"></script>
    <script>
        function bookService(serviceId) {
            alert('Service booking functionality would be implemented here. Service ID: ' + serviceId);
        }
    </script>
</body>
</html>
