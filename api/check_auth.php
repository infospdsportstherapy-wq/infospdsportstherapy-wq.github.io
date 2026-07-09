<?php
/*==========================================================
  CHECK AUTH API
  Verifies if admin session is active and valid
==========================================================*/

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
        'authenticated' => false
    ]);
    exit();
}

// Check if admin session exists
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_username'])) {
    // Session is valid
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'authenticated' => true,
        'admin' => [
            'admin_id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'email' => $_SESSION['admin_email']
        ]
    ]);
} else {
    // No valid session
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'authenticated' => false,
        'message' => 'Not authenticated'
    ]);
}
?>
