<?php
/*==========================================================
  ADMIN LOGIN API
  Handles admin authentication with session management
==========================================================*/

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

// Get form data
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validate input
if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Username and password required'
    ]);
    exit();
}

// Database configuration
$db_host = 'localhost';
$db_name = 'spd_sports_therapy';
$db_user = 'root';
$db_password = '';

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Query admin user
    $query = "
        SELECT admin_id, username, email, password_hash, is_active
        FROM admin_users
        WHERE username = :username
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();

    // Check if admin exists and is active
    if (!$admin || !$admin['is_active']) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
        exit();
    }

    // Verify password
    if (!password_verify($password, $admin['password_hash'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
        exit();
    }

    // Password correct - create session
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_login_time'] = time();

    // Update last login timestamp
    $updateQuery = "UPDATE admin_users SET last_login = NOW() WHERE admin_id = :admin_id";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute([':admin_id' => $admin['admin_id']]);

    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'admin' => [
            'admin_id' => $admin['admin_id'],
            'username' => $admin['username'],
            'email' => $admin['email']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}
?>
