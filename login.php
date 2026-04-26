<?php
session_start();

// Load data handler functions
if (file_exists('data_handler.php')) {
    require_once 'data_handler.php';
} else {
    // Define fallback functions if data_handler.php doesn't exist
    function loadData($filename) {
        $file = 'data/' . $filename;
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return [];
    }
    
    function saveData($filename, $data) {
        file_put_contents('data/' . $filename, json_encode($data, JSON_PRETTY_PRINT));
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Load users from database
    $users = loadData('users.json');
    $userFound = false;
    $redirectPage = 'index.php'; // Default redirect for new users
    
    // Check if user exists and verify password
    foreach ($users as $user) {
        if (($user['email'] === $username || $user['name'] === $username) && password_verify($password, $user['password'])) {
            $userFound = true;
            
            // Update login info
            $user['last_login'] = date('Y-m-d H:i:s');
            $user['login_count'] = ($user['login_count'] ?? 0) + 1;
            $user['updated_at'] = date('Y-m-d H:i:s');
            
            // Create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                $redirectPage = 'admin.php';
            } else {
                // For clients, redirect to index.php (they can navigate from there)
                $redirectPage = 'index.php';
            }
            
            // Update user in JSON file
            $updatedUsers = array_map(function($u) use ($user) {
                if ($u['id'] === $user['id']) {
                    return $user;
                }
                return $u;
            }, $users);
            saveData('users.json', $updatedUsers);
            
            header('Location: ' . $redirectPage);
            exit();
        }
    }
    
    // If no user found, create new client user (for demo purposes)
    if (!$userFound) {
        $userData = [
            'id' => time() + rand(1000, 9999),
            'name' => $username,
            'email' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'client',
            'phone' => '',
            'location' => 'Kampala, Uganda',
            'gender' => '',
            'avatar' => null,
            'preferences' => [
                'currency' => 'UGX',
                'newsletter' => false,
                'notifications' => true
            ],
            'address' => [
                'street' => '',
                'city' => 'Kampala',
                'country' => 'Uganda',
                'postal_code' => '256'
            ],
            'last_login' => date('Y-m-d H:i:s'),
            'login_count' => 1,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Save new user
        array_unshift($users, $userData);
        saveData('users.json', $users);
        
        // Alert admin about new user
        if (function_exists('alertNewUser')) {
            alertNewUser($userData);
        }
        
        // Create session
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['name'] = $userData['name'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['role'] = $userData['role'];
        
        header('Location: index.php');
        exit();
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Century Gothic', 'Segoe UI', sans-serif;
            background: var(--primary-gradient) fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* Decorative circles for background */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: 0;
            filter: blur(50px);
        }
        .circle-1 { width: 400px; height: 400px; top: -100px; left: -100px; }
        .circle-2 { width: 300px; height: 300px; bottom: -50px; right: -50px; }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin: 20px;
            animation: slideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-title {
            font-family: 'Century Gothic', 'Segoe UI', sans-serif;
            font-weight: 800;
            color: white;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 25px;
            font-size: 1.8rem;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        .form-label {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 0.85rem;
        }

        .input-group {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: white;
            padding-left: 15px;
            font-size: 0.9rem;
        }

        .form-control {
            background: transparent;
            border: none;
            color: white;
            padding: 10px 12px;
            font-size: 0.9rem;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-control:focus {
            background: transparent;
            color: white;
            box-shadow: none;
        }

        .btn-login {
            background: white;
            color: #764ba2;
            border: none;
            border-radius: 10px;
            padding: 12px;
            width: 100%;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
            background: #f8f9fa;
            color: #667eea;
        }

        .back-link {
            display: inline-block;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: white;
            transform: translateX(-5px);
        }

        .register-text {
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .register-text a {
            color: white;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="login-card">
        <a href="index.php" class="back-link">
            <i class="bi bi-arrow-left me-2"></i>Back to Home
        </a>
        
        <h1 class="brand-title">Login</h1>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'auth_required'): ?>
            <div class="alert alert-info border-0 shadow-sm mb-4" style="background: rgba(255,255,255,0.2); color: white; border-radius: 10px;">
                <i class="bi bi-info-circle-fill me-2"></i> To proceed to cart, first log in
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="post">
            <div class="mb-3">
                <label class="form-label">Username or Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <p class="register-text">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
