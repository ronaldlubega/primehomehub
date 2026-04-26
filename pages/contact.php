<?php
require_once 'navigation.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$success = '';
$error = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $db = new Database();
    
    // Validate form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $service = trim($_POST['service']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // 1. Save to Database
        $sql = "INSERT INTO contacts (name, email, phone, subject, service, message) VALUES (?, ?, ?, ?, ?, ?)";
        $res = $db->execute($sql, [$name, $email, $phone, $subject, $service, $message]);
        
        if ($res['success']) {
            // 2. Create Notification for Admin with "all data"
            $notifMessage = "From: $name\nEmail: $email\nPhone: $phone\nSubject: $subject\nService: $service\n\nMessage: $message";
            create_notification('contact', 'New Contact Inquiry', $notifMessage);
            
            $success = "Thank you for contacting us! We'll get back to you soon at {$email}.";
        } else {
            $error = "System error: Failed to save your message. Please try again later.";
        }
    }
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <!-- Header -->
    <section class="bg-light py-5">
        <div class="container">
            <h1 class="display-4 mb-2">Get in Touch</h1>
            <p class="lead">We'd love to hear from you. Contact us for design consultations or inquiries.</p>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="py-5">
        <div class="container">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <!-- Contact Info -->
                <div class="col-lg-4">
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="bi bi-telephone text-primary"></i> Phone</h5>
                        <p>256-708292123</p>
                    </div>
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="bi bi-envelope text-primary"></i> Email</h5>
                        <p><a href="mailto:primehomehub@gmail.com" class="text-decoration-none">primehomehub@gmail.com</a></p>
                    </div>
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="bi bi-geo-alt text-primary"></i> Address</h5>
                        <p>123 Design Street<br>New York, NY 10001</p>
                    </div>
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="bi bi-clock text-primary"></i> Hours</h5>
                        <p>Mon - Fri: 9:00 AM - 6:00 PM<br>
                           Sat: 10:00 AM - 4:00 PM<br>
                           Sun: Closed</p>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-lg-8">
                    <form method="POST" id="contact-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-12">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="col-12">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="col-12">
                                <label for="service" class="form-label">Service Interested In</label>
                                <select class="form-select" id="service" name="service" required>
                                    <option value="">Select a service...</option>
                                    <option value="residential">Residential Design</option>
                                    <option value="commercial">Commercial Design</option>
                                    <option value="consultation">Consultation</option>
                                    <option value="shopping">Shopping Assistance</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="subscribe" name="subscribe" checked>
                                    <label class="form-check-label" for="subscribe">
                                        Subscribe to our newsletter for design tips and updates
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="contact_submit" class="btn btn-primary btn-lg">Send Message</button>
                                <button type="reset" class="btn btn-outline-secondary btn-lg ms-2">Clear</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h3 class="text-center mb-4">Visit Our Showroom</h3>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.5223450167046!2d-74.00701232346051!3d40.71455927138922!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a2f1d8d8d8d%3A0x0!2sNew%20York!5e0!3m2!1sen!2sus!4v1234567890" width="100%" height="400" style="border:0; border-radius:10px;" allowfullscreen="" loading="lazy"></iframe>
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
                    <p><i class="bi bi-telephone"></i> 256-708292123<br>
                    <i class="bi bi-envelope"></i> primehomehub@gmail.com</p>
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
        // Form helper for smooth UX
        document.getElementById('contact-form').addEventListener('reset', function() {
            setTimeout(() => {
                document.getElementById('name').focus();
            }, 0);
        });
    </script>
</body>
</html>
