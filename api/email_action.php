<?php
/*==========================================================
  EMAIL ACTION API
  Handles approve/reject actions from email links
  Uses one-time tokens instead of session auth
  Uses centralized config for database credentials
==========================================================*/

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/email.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get parameters
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

// Validate token
if (empty($token)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request - missing token'
    ]);
    exit();
}

try {
    // Create PDO connection using config
    $pdo = getDbConnection();

    // Verify token exists, is valid, not expired, and not used
    $tokenQuery = "
        SELECT token_id, review_id, action, is_used, expires_at
        FROM email_tokens
        WHERE token = :token
        AND review_id = :review_id
        AND action = :action
        LIMIT 1
    ";

    $stmt = $pdo->prepare($tokenQuery);
    $stmt->execute([
        ':token' => $token,
        ':review_id' => $reviewId,
        ':action' => $action
    ]);
    $emailToken = $stmt->fetch();

    if (!$emailToken) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid token or action'
        ]);
        exit();
    }

    // Check if token is already used
    if ($emailToken['is_used']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Token has already been used'
        ]);
        exit();
    }

    // Check if token is expired
    if (strtotime($emailToken['expires_at']) < time()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Token has expired'
        ]);
        exit();
    }

    // Check if review exists
    $reviewQuery = "SELECT review_id, status FROM reviews WHERE review_id = :review_id";
    $stmt = $pdo->prepare($reviewQuery);
    $stmt->execute([':review_id' => $reviewId]);
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

    // Update review status
    $updateQuery = "
        UPDATE reviews
        SET status = :status,
            reviewed_at = NOW()
        WHERE review_id = :review_id
    ";

    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([
        ':status' => $newStatus,
        ':review_id' => $reviewId
    ]);

    // Mark token as used
    $tokenUsedQuery = "
        UPDATE email_tokens
        SET is_used = TRUE, used_at = NOW()
        WHERE token_id = :token_id
    ";

    $stmt = $pdo->prepare($tokenUsedQuery);
    $stmt->execute([':token_id' => $emailToken['token_id']]);

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
