 <?php
/**
 * Prime Home Hub - Categories API
 * Handles category listing
 */

header('Content-Type: application/json');

require_once '../includes/database.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        list_categories();
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Action not found']);
        exit;
}

/**
 * Get all categories
 */
function list_categories() {
    global $db;

    $categories = $db->fetch_all(
        "SELECT id, name, description, image_url FROM categories ORDER BY name ASC"
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
}
?>
