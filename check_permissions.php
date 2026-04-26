<?php
echo "<h2>File Permission Check</h2>";

// Check data directory
echo "<h3>Data Directory:</h3>";
if (is_dir('data')) {
    echo "✅ Data directory exists<br>";
    if (is_writable('data')) {
        echo "✅ Data directory is writable<br>";
    } else {
        echo "❌ Data directory is NOT writable<br>";
    }
} else {
    echo "❌ Data directory does NOT exist<br>";
}

// Check users.json file
echo "<h3>users.json File:</h3>";
if (file_exists('data/users.json')) {
    echo "✅ users.json exists<br>";
    if (is_writable('data/users.json')) {
        echo "✅ users.json is writable<br>";
    } else {
        echo "❌ users.json is NOT writable<br>";
    }
    
    // Try to read it
    $users = json_decode(file_get_contents('data/users.json'), true);
    echo "Current users count: " . count($users) . "<br>";
} else {
    echo "❌ users.json does NOT exist<br>";
}

// Test loadData and saveData functions
echo "<h3>Function Test:</h3>";
require_once 'data_handler.php';

try {
    $testData = ['test' => 'value', 'timestamp' => date('Y-m-d H:i:s')];
    saveData('test.json', $testData);
    echo "✅ saveData() works<br>";
    
    $loadedData = loadData('test.json');
    echo "✅ loadData() works<br>";
    echo "Test data: " . print_r($loadedData, true);
    
    // Clean up test file
    unlink('data/test.json');
    echo "✅ Test file cleaned up<br>";
    
} catch (Exception $e) {
    echo "❌ Function test failed: " . $e->getMessage() . "<br>";
}

echo "<p><a href='register.php'>Back to Registration</a></p>";
?>
