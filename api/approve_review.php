<?php
/*==========================================================
  APPROVE REVIEW API
  Handles admin approval/rejection of pending reviews
  Uses centralized config for database credentials
==========================================================*/

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/email.php';

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

// Check session authentication
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized - Please log in'
    ]);
    exit();
}

try {
    // Create PDO connection using config
    $pdo = getDbConnection();

    // Get and validate data
    $reviewId = isset($_POST['reviewId']) ? intval($_POST['reviewId']) : null;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    $adminNotes = isset($_POST['adminNotes']) ? trim($_POST['adminNotes']) : '';

    // Validate inputs
    if (!$reviewId || $reviewId <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid review ID'
        ]);
        exit();
    }

    if (!in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action must be "approve" or "reject"'
        ]);
        exit();
    }

    // Check if review exists
    $checkQuery = "SELECT review_id, status FROM reviews WHERE review_id = :reviewId";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute([':reviewId' => $reviewId]);
    $review = $stmt->fetch();

    if (!$review) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Review not found'
        ]);
        exit();
    }

    // Determine new status
    $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

    // Update review
    $updateQuery = "
        UPDATE reviews
        SET status = :status,
            admin_notes = :adminNotes,
            reviewed_at = NOW()
        WHERE review_id = :reviewId
    ";

    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([
        ':status' => $newStatus,
        ':adminNotes' => $adminNotes,
        ':reviewId' => $reviewId
    ]);

    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => "Review successfully {$newStatus}",
        'review_id' => $reviewId,
        'status' => $newStatus,
        'reviewed_at' => date('Y-m-d H:i:s')
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
