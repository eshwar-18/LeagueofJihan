<?php
/**
 * CPS510 A9 - Database Configuration File
 * 
 * This file establishes the Oracle database connection using OCI functions.
 * It provides reusable functions for executing queries and managing database operations.
 */

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Error: .env file not found. Please copy .env.example to .env and configure your credentials.");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Set as environment variable and define constant
        putenv("$key=$value");
        if (!defined($key)) {
            define($key, $value);
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/.env');

// Oracle Database Connection Configuration
define('DB_CONNECTION_STRING', '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=' . DB_HOST . ')(Port=' . DB_PORT . '))(CONNECT_DATA=(SID=' . DB_SID . ')))');

/**
 * Establishes a connection to the Oracle database
 * 
 * @return resource|false Connection resource on success, false on failure
 */
function getDBConnection() {
    $conn = @oci_connect(DB_USERNAME, DB_PASSWORD, DB_CONNECTION_STRING);
    
    if (!$conn) {
        $error = oci_error();
        die("Database Connection Error: " . htmlspecialchars($error['message']));
    }
    
    return $conn;
}

/**
 * Executes a SQL query and returns the statement handle
 * 
 * @param resource $conn Database connection
 * @param string $sql SQL query to execute
 * @return resource Statement handle
 */
function executeQuery($conn, $sql) {
    $statement = oci_parse($conn, $sql);
    
    if (!$statement) {
        $error = oci_error($conn);
        die("Query Parse Error: " . htmlspecialchars($error['message']));
    }
    
    $result = oci_execute($statement);
    
    if (!$result) {
        $error = oci_error($statement);
        die("Query Execution Error: " . htmlspecialchars($error['message']));
    }
    
    return $statement;
}

/**
 * Executes a DML query (INSERT, UPDATE, DELETE) with commit
 * 
 * @param resource $conn Database connection
 * @param string $sql SQL query to execute
 * @return bool True on success, false on failure
 */
function executeDML($conn, $sql) {
    $statement = oci_parse($conn, $sql);
    
    if (!$statement) {
        $error = oci_error($conn);
        return false;
    }
    
    $result = oci_execute($statement, OCI_NO_AUTO_COMMIT);
    
    if (!$result) {
        $error = oci_error($statement);
        oci_rollback($conn);
        return false;
    }
    
    oci_commit($conn);
    oci_free_statement($statement);
    return true;
}

/**
 * Closes the database connection
 * 
 * @param resource $conn Database connection to close
 */
function closeConnection($conn) {
    if ($conn) {
        oci_close($conn);
    }
}

/**
 * Sanitizes user input to prevent SQL injection
 * 
 * @param string $input User input
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Validates if a string is numeric
 * 
 * @param string $input Input to validate
 * @return bool True if numeric, false otherwise
 */
function isNumeric($input) {
    return is_numeric($input) && $input > 0;
}

/**
 * Validates email format
 * 
 * @param string $email Email to validate
 * @return bool True if valid email, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
?>
