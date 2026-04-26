<?php
// Data storage functions
function saveData($filename, $data) {
    file_put_contents('data/' . $filename, json_encode($data, JSON_PRETTY_PRINT));
}

function loadData($filename) {
    $file = 'data/' . $filename;
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return [];
}

// Initialize data directory if it doesn't exist
if (!is_dir('data')) {
    mkdir('data', 0777, true);
}

// Handle contact form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $contact = [
        'id' => time(),
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'subject' => $_POST['subject'],
        'service' => $_POST['service'],
        'message' => $_POST['message'],
        'subscribe' => isset($_POST['subscribe']),
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => 'new'
    ];
    
    $contacts = loadData('contacts.json');
    array_unshift($contacts, $contact);
    saveData('contacts.json', $contacts);
    
    $success = "Thank you for contacting us! We'll get back to you soon.";
}

// Handle new user registration alerts
function alertNewUser($userData) {
    $alerts = loadData('alerts.json');
    $alert = [
        'id' => time(),
        'type' => 'new_user',
        'title' => 'New Customer Registration',
        'message' => "New customer registered: {$userData['name']} ({$userData['email']})",
        'data' => $userData,
        'timestamp' => date('Y-m-d H:i:s'),
        'read' => false
    ];
    array_unshift($alerts, $alert);
    saveData('alerts.json', $alerts);
}

// Handle new order alerts
function alertNewOrder($orderData) {
    $alerts = loadData('alerts.json');
    $alert = [
        'id' => time(),
        'type' => 'new_order',
        'title' => 'New Order Placed',
        'message' => "Order #{$orderData['id']} placed by {$orderData['customer_name']}",
        'data' => $orderData,
        'timestamp' => date('Y-m-d H:i:s'),
        'read' => false
    ];
    array_unshift($alerts, $alert);
    saveData('alerts.json', $alerts);
}

// Handle contact form alerts
function alertNewContact($contactData) {
    $alerts = loadData('alerts.json');
    $alert = [
        'id' => time(),
        'type' => 'new_contact',
        'title' => 'New Contact Inquiry',
        'message' => "Contact form submission from {$contactData['name']}",
        'data' => $contactData,
        'timestamp' => date('Y-m-d H:i:s'),
        'read' => false
    ];
    array_unshift($alerts, $alert);
    saveData('alerts.json', $alerts);
}
?>
