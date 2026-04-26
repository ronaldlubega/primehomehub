<?php
/**
 * Prime Home Hub - Database Connection & Configuration
 * Handles all database operations
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'prime_home_hub');

class Database {
    private $connection;
    private $last_error;
    private $last_query;

    public function __construct() {
        try {
            $this->connection = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASSWORD,
                DB_NAME,
            );

            if ($this->connection->connect_error) {
                throw new Exception("Database Connection Failed: " . $this->connection->connect_error);
            }

            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            error_log($this->last_error);
        }
    }

    /**
     * Execute a query and return results
     */
    public function query($sql, $params = []) {
        try {
            $this->last_query = $sql;
            
            if (empty($params)) {
                $result = $this->connection->query($sql);
                if (!$result) {
                    throw new Exception("Query Error: " . $this->connection->error);
                }
                return $result;
            }

            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare Error: " . $this->connection->error);
            }

            $this->bind_params($stmt, $params);
            $stmt->execute();
            
            return $stmt->get_result();
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            error_log($this->last_error);
            return false;
        }
    }

    /**
     * Execute a query without returning results (INSERT, UPDATE, DELETE)
     */
    public function execute($sql, $params = []) {
        try {
            $this->last_query = $sql;
            
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare Error: " . $this->connection->error);
            }

            if (!empty($params)) {
                $this->bind_params($stmt, $params);
            }

            $stmt->execute();
            
            return [
                'success' => true,
                'affected_rows' => $stmt->affected_rows,
                'insert_id' => $stmt->insert_id
            ];
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            error_log($this->last_error);
            return [
                'success' => false,
                'error' => $this->last_error
            ];
        }
    }

    /**
     * Bind parameters to prepared statement
     */
    private function bind_params(&$stmt, $params) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }

        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
    }

    /**
     * Fetch single row as associative array
     */
    public function fetch_one($sql, $params = []) {
        $result = $this->query($sql, $params);
        if ($result) {
            return $result->fetch_assoc();
        }
        return null;
    }

    /**
     * Fetch all rows as associative array
     */
    public function fetch_all($sql, $params = []) {
        $result = $this->query($sql, $params);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    /**
     * Get total count of rows
     */
    public function count($table, $where = '') {
        $sql = "SELECT COUNT(*) as count FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE $where";
        }
        $result = $this->fetch_one($sql);
        return $result['count'] ?? 0;
    }

    /**
     * Get last inserted ID
     */
    public function last_insert_id() {
        return $this->connection->insert_id;
    }

    /**
     * Get last error message
     */
    public function get_error() {
        return $this->last_error;
    }

    /**
     * Get last executed query
     */
    public function get_last_query() {
        return $this->last_query;
    }

    /**
     * Close database connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Begin transaction
     */
    public function begin_transaction() {
        return $this->connection->begin_transaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }

    /**
     * Escape string for safe query
     */
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
}

// Initialize database connection
$db = new Database();

// Verify connection
if (!$db) {
    die(json_encode(['error' => 'Database connection failed']));
}
?>
