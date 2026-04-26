<?php
echo "<h2>Registration Debug Test</h2>";

// Test 1: Check if POST data is coming
echo "<h3>1. POST Data Check:</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "✅ POST request received<br>";
    echo "POST data: <pre>" . print_r($_POST, true) . "</pre>";
} else {
    echo "❌ No POST request<br>";
    echo "Current method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
}

// Test 2: Check required fields
echo "<h3>2. Required Fields Check:</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['role', 'name', 'password'];
    $missing = [];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing[] = $field;
        }
    }
    
    if (empty($missing)) {
        echo "✅ All required fields present<br>";
    } else {
        echo "❌ Missing fields: " . implode(', ', $missing) . "<br>";
    }
}

// Test 3: Check data_handler functions
echo "<h3>3. Data Handler Functions:</h3>";
try {
    require_once 'data_handler.php';
    
    // Test loadData
    $users = loadData('users.json');
    echo "✅ loadData() works - Found " . count($users) . " users<br>";
    
    // Test saveData
    $testData = ['test' => 'value', 'time' => time()];
    saveData('test_save.json', $testData);
    echo "✅ saveData() works<br>";
    
    // Clean up
    if (file_exists('data/test_save.json')) {
        unlink('data/test_save.json');
        echo "✅ Test file cleaned up<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Data handler error: " . $e->getMessage() . "<br>";
}

// Test 4: Check file permissions
echo "<h3>4. File Permissions:</h3>";
if (is_dir('data')) {
    echo "✅ Data directory exists<br>";
    echo "Writable: " . (is_writable('data') ? 'Yes' : 'No') . "<br>";
} else {
    echo "❌ Data directory missing<br>";
}

if (file_exists('data/users.json')) {
    echo "✅ users.json exists<br>";
    echo "Writable: " . (is_writable('data/users.json') ? 'Yes' : 'No') . "<br>";
} else {
    echo "❌ users.json missing<br>";
}

// Test 5: Try a simple registration
echo "<h3>5. Simple Registration Test:</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_register'])) {
    try {
        $userData = [
            'id' => time(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'role' => 'client',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $users = loadData('users.json');
        array_unshift($users, $userData);
        saveData('users.json', $users);
        
        echo "✅ Simple registration successful<br>";
        echo "User ID: " . $userData['id'] . "<br>";
        echo "Total users now: " . count($users) . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Simple registration failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo '<form method="post">
        <input type="hidden" name="test_register" value="1">
        <button type="submit">Test Simple Registration</button>
    </form>';
}

echo "<hr>";
echo "<p><a href='register.php'>Back to Registration</a></p>";
echo "<p><a href='index.php'>Back to Home</a></p>";
?>
