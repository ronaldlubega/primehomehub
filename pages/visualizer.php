<?php
require_once 'navigation.php';

// Check if user is logged in (optional for visualizer)
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Visualizer - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Room Visualizer</h1>
            <p class="lead">Design your dream room with our interactive visualizer</p>
        </div>
    </section>

    <!-- Visualizer Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>Room Canvas</h4>
                        </div>
                        <div class="card-body">
                            <div class="bg-light rounded p-5 text-center" style="min-height: 400px;">
                                <i class="bi bi-house display-1 text-muted"></i>
                                <h4 class="mt-3">Interactive Room Visualizer</h4>
                                <p class="text-muted">Drag and drop furniture to design your room</p>
                                <div class="mt-4">
                                    <button class="btn btn-primary me-2" onclick="startDesign()">Start Designing</button>
                                    <button class="btn btn-outline-secondary" onclick="loadTemplate()">Load Template</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Tools & Options</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6>Room Type</h6>
                                <select class="form-select">
                                    <option>Living Room</option>
                                    <option>Bedroom</option>
                                    <option>Kitchen</option>
                                    <option>Office</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <h6>Room Size</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="number" class="form-control" placeholder="Width (ft)" value="12">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control" placeholder="Length (ft)" value="15">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h6>Quick Actions</h6>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary btn-sm">Save Design</button>
                                    <button class="btn btn-outline-success btn-sm">Share Design</button>
                                    <button class="btn btn-outline-info btn-sm">Get Quote</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
        function startDesign() {
            alert('Room visualizer would start here with interactive design tools');
        }
        
        function loadTemplate() {
            alert('Template selection would appear here');
        }
    </script>
</body>
</html>
