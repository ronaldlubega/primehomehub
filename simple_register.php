<!DOCTYPE html>
<html>
<head>
    <title>Simple Registration Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .form-group { margin: 10px 0; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 300px; padding: 8px; margin-bottom: 10px; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .debug { background: #f0f0f0; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Simple Registration Test</h2>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<div class='debug'>";
        echo "<h3>Form Submission Received:</h3>";
        echo "<pre>" . print_r($_POST, true) . "</pre>";
        echo "</div>";
        
        // Try to process
        try {
            require_once 'data_handler.php';
            
            $userData = [
                'id' => time(),
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'role' => $_POST['role'],
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $users = loadData('users.json');
            array_unshift($users, $userData);
            saveData('users.json', $users);
            
            echo "<div class='debug' style='background: #d4edda;'>";
            echo "<h3>✅ SUCCESS: User Saved!</h3>";
            echo "<pre>" . print_r($userData, true) . "</pre>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='debug' style='background: #f8d7da;'>";
            echo "<h3>❌ ERROR: " . $e->getMessage() . "</h3>";
            echo "</div>";
        }
    }
    ?>
    
    <form method="post">
        <div class="form-group">
            <label>Name:</label>
            <input type="text" name="name" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label>Role:</label>
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="client">Client</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        
        <button type="submit">Register</button>
    </form>
    
    <p><a href="debug_registration.php">Run Full Debug</a></p>
    <p><a href="register.php">Back to Main Registration</a></p>
</body>
</html>
