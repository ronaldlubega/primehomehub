<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role     = $_POST['role'];
    $name     = $_POST['name'];
    $contact  = $_POST['contact'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email    = $_POST['email'] ?? '';
    $location = $_POST['location'] ?? '';
    $gender   = $_POST['gender'] ?? '';

    $userData = [
        'id'          => time(),
        'role'        => $role,
        'name'        => $name,
        'contact'     => $contact,
        'email'       => $email,
        'location'    => $location,
        'gender'      => $gender,
        'password'    => $password,
        'created_at'  => date('Y-m-d H:i:s'),
        'status'      => 'active',
        'login_count' => 0,
        'preferences' => ['currency' => 'UGX', 'newsletter' => false, 'notifications' => true],
        'address'     => ['city' => $location ?: 'Kampala', 'country' => 'Uganda', 'postal_code' => '256']
    ];

    $usersFile = 'data/users.json';
    $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
    $users[] = $userData;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

    // Notify Admin of new registration
    require_once 'includes/functions.php';
    create_notification('user', 'New User Registered', "A new user '$name' ($contact) has joined the platform.");

    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['name'] = $userData['name'];
    $_SESSION['role'] = $userData['role'];

    header($role === 'admin' ? 'Location: admin.php' : 'Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Prime Home Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Century Gothic', 'Segoe UI', sans-serif;
            background: var(--primary-gradient) fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden; /* Prevents scroll */
        }

        .register-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 25px 40px;
            width: 95%;
            max-width: 950px; /* Wider for landscape feel */
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-title {
            font-weight: 800;
            color: white;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .form-label {
            color: white;
            font-weight: 600;
            font-size: 0.75rem;
            margin-bottom: 4px;
        }

        .input-group {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
        }

        .form-control, .form-select {
            background: transparent !important;
            border: none;
            color: white !important;
            font-size: 0.85rem;
            padding: 8px;
        }

        .form-control::placeholder { color: rgba(255,255,255,0.7); }
        .input-group-text { background: transparent; border: none; color: white; }

        .form-select option {
            color: #000000 !important;
            background-color: #ffffff !important;
        }

        .btn-register {
            background: white;
            color: #764ba2;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 700;
            width: 100%;
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-register:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        .back-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.8rem;
            position: absolute;
            top: 20px;
            left: 30px;
        }

        .login-text {
            text-align: center;
            margin-top: 15px;
            color: rgba(255,255,255,0.8);
            font-size: 0.85rem;
            margin-bottom: 0;
        }
    </style>
</head>
<body>

    <div class="register-card">
        <a href="index.php" class="back-link"><i class="bi bi-arrow-left"></i> Back</a>
        
        <h1 class="brand-title">Create Account</h1>
        
        <form method="post">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Register as:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                        <select name="role" class="form-select" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="client">Client</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Full Name:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="name" class="form-control" required placeholder="Your Name">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Number:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                        <input type="text" name="contact" class="form-control" required placeholder="Phone Number">
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-4">
                    <label class="form-label">Email Address:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="mayiko@gmail.com">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Location:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                        <input type="text" name="location" id="locationInput" class="form-control" placeholder="City, Country">
                        <span class="input-group-text p-0">
                            <button type="button" id="locateBtn" class="btn btn-link text-white shadow-none border-0" title="Get my location">
                                <i class="bi bi-compass"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-gender-ambiguous"></i></span>
                        <select name="gender" class="form-select" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Password:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••••••">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn-register">
                        <i class="bi bi-person-plus me-2"></i>REGISTER NOW
                    </button>
                </div>
            </div>
        </form>

        <p class="login-text">
            Already have an account? <a href="login.php" class="text-white fw-bold">Sign In here</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const locateBtn = document.getElementById('locateBtn');
            if (locateBtn) {
                locateBtn.addEventListener('click', async function() {
                    const btn = this;
                    const icon = btn.querySelector('i');
                    const input = document.getElementById('locationInput');
                    
                    // Update UI to show loading
                    icon.className = 'bi bi-arrow-repeat spin';
                    btn.disabled = true;

                    const setLocation = async (lat, lon) => {
                        try {
                            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                            if (!response.ok) throw new Error('OSM Reverse failed');
                            const data = await response.json();
                            const addr = data.address || {};
                            const city = addr.city || addr.town || addr.village || addr.suburb || '';
                            const country = addr.country || '';
                            input.value = (city && country) ? `${city}, ${country}` : (data.display_name.split(',').slice(0,2).join(',').trim());
                        } catch (e) {
                            console.error('Reverse geocode error:', e);
                            input.value = `${lat.toFixed(4)}, ${lon.toFixed(4)}`;
                        }
                    };

                    const handleIPFallback = async () => {
                        try {
                            const res = await fetch('https://ipapi.co/json/');
                            if (!res.ok) throw new Error('IP-api failed');
                            const data = await res.json();
                            if (data.city && data.country_name) {
                                input.value = `${data.city}, ${data.country_name}`;
                            } else if (data.latitude && data.longitude) {
                                await setLocation(data.latitude, data.longitude);
                            }
                        } catch (e) {
                            console.error('IP Fallback failed:', e);
                            alert("Geolocation failed. Please enter your location manually.");
                        } finally {
                            icon.className = 'bi bi-compass';
                            btn.disabled = false;
                        }
                    };

                    if (!navigator.geolocation) {
                        await handleIPFallback();
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            await setLocation(position.coords.latitude, position.coords.longitude);
                            icon.className = 'bi bi-compass';
                            btn.disabled = false;
                        },
                        async (error) => {
                            console.warn('Browser geolocation failed:', error.message);
                            // Only show alert if it's a permission issue, otherwise try IP fallback
                            if (error.code === 1) {
                                alert("Permission denied. Please enable location access in your browser or enter manually.");
                                icon.className = 'bi bi-compass';
                                btn.disabled = false;
                            } else {
                                await handleIPFallback();
                            }
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                });
            }
        });
    </script>
    <style>
        .spin { animation: spin 1s linear infinite; display: inline-block; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        #locateBtn:disabled { opacity: 0.6; cursor: not-allowed; }
    </style>
</body>
</html>