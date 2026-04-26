<?php
// Basic PHP test - no JSON, just plain text
echo "PHP is working!";
echo "Time: " . date('Y-m-d H:i:s');
echo "Method: " . $_SERVER['REQUEST_METHOD'];
echo "Action: " . ($_GET['action'] ?? 'none');
?>
