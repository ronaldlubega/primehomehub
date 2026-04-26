<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';

// Get database connection
$db = new Database();

// Get portfolio projects
$projects = $db->fetch_all("SELECT * FROM portfolio ORDER BY created_at DESC");
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
    <?php include '../includes/header.php'; ?>

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
                                    <img src="<?php echo $project['image_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project['title']); ?>">
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

    <!-- Project Modal -->
    <div class="modal fade" id="projectModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Project Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="projectDetails"></div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/navigation.js"></script>
    <script>
        function viewProject(projectId) {
            // In a real application, this would fetch project details from the server
            document.getElementById('projectDetails').innerHTML = `
                <div class="text-center">
                    <i class="bi bi-image display-1 text-muted"></i>
                    <h4 class="mt-3">Project Details</h4>
                    <p>Project ID: ${projectId}</p>
                    <p class="text-muted">Detailed project information would be displayed here.</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('projectModal'));
            modal.show();
        }
    </script>
</body>
</html>
