<?php
require_once 'includes/database.php';
$db = new Database();

echo "--- NOTIFICATIONS AUDIT ---\n";
$res = $db->fetch_all("SELECT id, type, title, is_read FROM notifications ORDER BY created_at DESC");
echo "Count: " . count($res) . "\n";
print_r($res);

echo "\n--- PENDING ORDER COUNT ---\n";
$pending = $db->fetch_one("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'");
echo "Pending Orders: " . $pending['c'] . "\n";

echo "\n--- UNREAD NOTIF COUNT ---\n";
$unread = $db->fetch_one("SELECT COUNT(*) as c FROM notifications WHERE is_read = 0");
echo "Unread Notifs: " . $unread['c'] . "\n";
?>
