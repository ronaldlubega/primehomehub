<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
require_once 'includes/footer.php';

// Get database connection
$db = new Database();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    
    // Simple validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = "All fields are required.";
    } else {
        // Store in database (you'll need to create a contacts table)
        $result = $db->execute(
            "INSERT INTO contact_submissions (name, email, message) VALUES (?, ?, ?)",
            [$name, $email, $message]
        );
        
        if ($result['success']) {
            $success = "Thank you for contacting us! We'll get back to you soon.";
        } else {
            $error = "Failed to send message. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">About Us</h1>
            <p class="lead">Learn more about Prime Home Hub</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="mb-4">Our Story</h2>
                    <p class="lead">Prime Home Hub is your premier destination for interior design expertise and premium furniture collections.</p>
                    <p>We believe that your home should be a reflection of your personality and style. Our team of experienced designers and curators work tirelessly to bring you the finest selection of furniture and decor items that transform spaces into homes.</p>
                    
                    <h3 class="mt-5 mb-3">Our Mission</h3>
                    <p>To provide exceptional interior design solutions and high-quality furniture that helps our clients create beautiful, functional spaces they love.</p>
                    
                    <h3 class="mt-5 mb-3">Our Values</h3>
                    <ul>
                        <li>Quality craftsmanship in every piece</li>
                        <li>Sustainable and ethical sourcing</li>
                        <li>Customer satisfaction above all</li>
                        <li>Innovative design solutions</li>
                    </ul>
                    
                    <hr class="my-5">
                    
                    <h3 class="mb-4">Contact Us</h3>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="contact" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/navigation.js"></script>
</body>
</html>
