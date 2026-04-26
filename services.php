<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';

// Get database connection
$db = new Database();

// Get services
$services = $db->fetch_all("SELECT * FROM services ORDER BY id");
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
    <?php include '../includes/header.php'; ?>

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
                        <div class="col-md-6 col-lg-4">
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
            
            <!-- Consultation Section -->
            <div class="row mt-5">
                <div class="col-lg-8 mx-auto">
                    <div class="card bg-light">
                        <div class="card-body text-center p-5">
                            <h3 class="mb-4">Free Consultation</h3>
                            <p class="mb-4">Get a free 30-minute consultation with our design experts</p>
                            <button class="btn btn-primary btn-lg" onclick="bookConsultation()">Book Free Consultation</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="serviceForm">
                        <div class="mb-3">
                            <label for="serviceName" class="form-label">Service</label>
                            <input type="text" class="form-control" id="serviceName" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="clientName" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="clientName" required>
                        </div>
                        <div class="mb-3">
                            <label for="clientEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="clientEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="clientPhone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="clientPhone">
                        </div>
                        <div class="mb-3">
                            <label for="projectDetails" class="form-label">Project Details</label>
                            <textarea class="form-control" id="projectDetails" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitServiceBooking()">Submit Booking</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/navigation.js"></script>
    <script>
        function bookService(serviceId) {
            // In a real application, this would fetch service details
            document.getElementById('serviceName').value = `Service ID: ${serviceId}`;
            
            const modal = new bootstrap.Modal(document.getElementById('serviceModal'));
            modal.show();
        }
        
        function bookConsultation() {
            document.getElementById('serviceName').value = 'Free Consultation';
            
            const modal = new bootstrap.Modal(document.getElementById('serviceModal'));
            modal.show();
        }
        
        function submitServiceBooking() {
            // Validate form
            const name = document.getElementById('clientName').value;
            const email = document.getElementById('clientEmail').value;
            
            if (!name || !email) {
                alert('Please fill in all required fields');
                return;
            }
            
            // In a real application, this would submit to the server
            alert('Service booking submitted successfully! We will contact you soon.');
            
            // Close modal and reset form
            bootstrap.Modal.getInstance(document.getElementById('serviceModal')).hide();
            document.getElementById('serviceForm').reset();
        }
    </script>
</body>
</html>
