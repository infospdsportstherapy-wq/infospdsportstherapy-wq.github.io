<?php
/**
 * =============================================================================
 * SPD Sports Therapy - Configuration Manager
 * =============================================================================
 * 
 * Centralized configuration loader that reads from .env file
 * Provides getConfig() helper to access settings throughout the application
 * 
 * Usage: $db_host = getConfig('DB_HOST');
 * =============================================================================
 */

// Start output buffering to prevent header issues
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Define the environment file paths
$envFile = __DIR__ . '/../.env';
$envExampleFile = __DIR__ . '/../.env.example';

// Configuration storage
$config = [];

/**
 * Load environment variables from .env file
 * Falls back to .env.example if .env doesn't exist
 */
function loadConfig() {
    global $config, $envFile, $envExampleFile;
    
    if (empty($config)) {
        // Check if .env exists, use .env.example as fallback
        $fileToLoad = file_exists($envFile) ? $envFile : $envExampleFile;
        
        if (!file_exists($fileToLoad)) {
            throw new Exception('Configuration file not found: ' . $fileToLoad);
        }
        
        // Read and parse .env file
        $lines = file($fileToLoad, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                    (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                    $value = substr($value, 1, -1);
                }
                
                $config[$key] = $value;
                
                // Also set as environment variable for system access
                putenv($key . '=' . $value);
            }
        }
    }
    
    return $config;
}

/**
 * Get a configuration value
 * 
 * @param string $key Configuration key (e.g., 'DB_HOST')
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value or default
 */
function getConfig($key, $default = null) {
    global $config;
    
    if (empty($config)) {
        loadConfig();
    }
    
    return isset($config[$key]) ? $config[$key] : $default;
}

/**
 * Check if a configuration key exists
 * 
 * @param string $key Configuration key
 * @return bool True if key exists and not empty
 */
function hasConfig($key) {
    global $config;
    
    if (empty($config)) {
        loadConfig();
    }
    
    return isset($config[$key]) && !empty($config[$key]);
}

/**
 * Get all configuration values
 * 
 * @return array All configuration key-value pairs
 */
function getAllConfig() {
    global $config;
    
    if (empty($config)) {
        loadConfig();
    }
    
    return $config;
}

/**
 * Get database configuration array for PDO connection
 * 
 * @return array Configuration for PDO (host, name, user, password, port)
 */
function getDbConfig() {
    return [
        'host' => getConfig('DB_HOST', 'localhost'),
        'name' => getConfig('DB_NAME', 'spd_sports_therapy'),
        'user' => getConfig('DB_USER', 'root'),
        'password' => getConfig('DB_PASSWORD', ''),
        'port' => getConfig('DB_PORT', 3306),
        'charset' => getConfig('DB_CHARSET', 'utf8mb4')
    ];
}

/**
 * Create a PDO database connection
 * 
 * @return PDO Database connection object
 * @throws PDOException on connection failure
 */
function getDbConnection() {
    $dbConfig = getDbConfig();
    
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $dbConfig['host'],
        $dbConfig['port'],
        $dbConfig['name'],
        $dbConfig['charset']
    );
    
    return new PDO(
        $dsn,
        $dbConfig['user'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
}

/**
 * Get email configuration array for mail service
 * 
 * @return array Configuration for email (host, port, user, password, from_email, from_name)
 */
function getEmailConfig() {
    return [
        'host' => getConfig('MAIL_HOST', 'smtp.gmail.com'),
        'port' => getConfig('MAIL_PORT', 587),
        'username' => getConfig('MAIL_USERNAME', ''),
        'password' => getConfig('MAIL_PASSWORD', ''),
        'from_email' => getConfig('MAIL_FROM_EMAIL', getConfig('MAIL_USERNAME')),
        'from_name' => getConfig('MAIL_FROM_NAME', 'SPD Sports Therapy'),
        'debug' => getConfig('MAIL_DEBUG', 0)
    ];
}

/**
 * Error response helper
 * 
 * @param string $message Error message
 * @param int $code HTTP status code
 * @param array $errors Additional error details
 * @return void (outputs JSON and exits)
 */
function errorResponse($message, $code = 500, $errors = []) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ]);
    exit();
}

/**
 * Success response helper
 * 
 * @param mixed $data Response data
 * @param string $message Success message
 * @param int $code HTTP status code
 * @return void (outputs JSON and exits)
 */
function successResponse($data = null, $message = 'Success', $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Initialize configuration on load
try {
    loadConfig();
} catch (Exception $e) {
    // Configuration will be loaded on first use
    // This allows graceful fallback to .env.example
}

?>
