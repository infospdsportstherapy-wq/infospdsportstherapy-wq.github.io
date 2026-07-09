<?php
/*==========================================================
  ADMIN LOGOUT API
  Destroys admin session
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

// Destroy session
session_destroy();

// Return success response
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Logout successful'
]);
?>
