<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Register</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .form-group { margin: 10px 0; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Test Registration Form</h2>
    
    <form action="test_register.php" method="post">
        <div class="form-group">
            <label for="role">Register as:</label>
            <select name="role" id="role" required>
                <option value="">-- Select Role --</option>
                <option value="client">Client</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email">
        </div>
        
        <div class="form-group">
            <label for="contact">Contact:</label>
            <input type="text" name="contact">
        </div>
        
        <div class="form-group" id="location-group" style="display:none;">
            <label for="location">Location:</label>
            <input type="text" name="location">
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <button type="submit">Register</button>
    </form>
    
    <script>
        document.getElementById('role').addEventListener('change', function() {
            const locationGroup = document.getElementById('location-group');
            if (this.value === 'client') {
                locationGroup.style.display = 'block';
            } else {
                locationGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>
