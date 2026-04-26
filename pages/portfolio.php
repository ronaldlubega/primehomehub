<?php
require_once 'navigation.php';

// Sample portfolio projects
$projects = [
    [
        'id' => 1,
        'title' => 'Modern Living Room Design',
        'description' => 'Contemporary living room with minimalist furniture and neutral color palette',
        'image_url' => '',
        'created_at' => '2024-01-15'
    ],
    [
        'id' => 2,
        'title' => 'Luxury Bedroom Makeover',
        'description' => 'Elegant bedroom design with premium furnishings and sophisticated lighting',
        'image_url' => '',
        'created_at' => '2024-02-20'
    ],
    [
        'id' => 3,
        'title' => 'Office Space Transformation',
        'description' => 'Professional office redesign with ergonomic furniture and modern aesthetics',
        'image_url' => '',
        'created_at' => '2024-03-10'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Our Portfolio</h1>
            <p class="lead">Explore our latest design projects</p>
        </div>
    </section>

    <!-- Portfolio Content -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php if (empty($projects)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-image display-1 text-muted"></i>
                            <h3 class="mt-3">No Projects Yet</h3>
                            <p class="text-muted">Our portfolio is being updated with amazing projects. Check back soon!</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <?php if ($project['image_url']): ?>
                                    <img src="../<?php echo $project['image_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project['title']); ?>">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="bi bi-image display-4 text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($project['description'], 0, 100)); ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($project['created_at'])); ?></small>
                                        <button class="btn btn-outline-primary btn-sm" onclick="viewProject(<?php echo $project['id']; ?>)">View Details</button>
                                    </div>
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
        function viewProject(projectId) {
            alert('Project details would be shown here. Project ID: ' + projectId);
        }
    </script>
</body>
</html>
