<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'prime_home_hub');
define('DB_USER', 'root');
define('DB_PASS', '');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database class
class Database {
    private $host = DB_HOST;
    private $dbname = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            // Don't show error in production, just log it
            error_log("Connection failed: " . $e->getMessage());
        }
    }
    
    public function fetch_all($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            return [];
        }
    }
    
    public function fetch_one($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function execute($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute($params);
            return ['success' => $result, 'insert_id' => $this->conn->lastInsertId()];
        } catch(PDOException $e) {
            error_log("Execute failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($input) {
    return htmlspecialchars(trim($input));
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
?>
