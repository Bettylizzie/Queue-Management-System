<?php
/**
 * Database Connection File
 * 
 * Best Practices:
 * 1. Uses environment variables for credentials
 * 2. Implements proper error handling
 * 3. Supports multiple environments (dev/staging/prod)
 * 4. Includes connection optimizations
 */

// Error reporting (only show errors in development)
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Set to '1' for development

// Load environment variables (using .env file if available)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = parse_ini_file(__DIR__ . '/.env');
    foreach ($dotenv as $key => $value) {
        putenv("$key=$value");
    }
}

// Database configuration with fallback values
$dbConfig = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'name' => getenv('DB_NAME') ?: 'queue_system',
    'port' => getenv('DB_PORT') ?: 3307,
    'socket' => getenv('DB_SOCKET') ?: null
];

// Connection settings
$connectionOptions = [
    MYSQLI_OPT_CONNECT_TIMEOUT => 5,          // 5 second connection timeout
    MYSQLI_OPT_READ_TIMEOUT => 10,            // 10 second read timeout
    MYSQLI_INIT_COMMAND => "SET time_zone = '+00:00'" // Set UTC timezone
];

try {
    // Create connection with error suppression
    $conn = @new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['name'],
        $dbConfig['port'],
        $dbConfig['socket']
    );

    // Check connection
    if ($conn->connect_error) {
        throw new RuntimeException(
            'Database connection failed: ' . 
            htmlspecialchars($conn->connect_error, ENT_QUOTES, 'UTF-8')
        );
    }

    // Set connection options
    foreach ($connectionOptions as $option => $value) {
        $conn->options($option, $value);
    }

    // Set charset to UTF-8
    if (!$conn->set_charset('utf8mb4')) { // utf8mb4 supports full Unicode
        throw new RuntimeException(
            'Error setting charset: ' . 
            htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8')
        );
    }

    // Configure connection attributes
    $conn->query("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

} catch (RuntimeException $e) {
    // Log error securely
    error_log($e->getMessage());
    
    // Return JSON error (for API calls)
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        die(json_encode([
            'error' => 'Database connection error',
            'message' => 'Service temporarily unavailable'
        ]));
    }
    
    die('Database connection error. Please try again later.');
}

// Connection successful
return $conn;
?>