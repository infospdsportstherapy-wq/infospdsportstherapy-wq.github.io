<?php
/*==========================================================
  APPROVE REVIEW API
  Handles admin approval/rejection of pending reviews
==========================================================*/

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

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

// Basic authentication - in production, use proper session/token auth
$adminPassword = 'admin123'; // CHANGE THIS to a strong password in production
$providedPassword = isset($_POST['adminPassword']) ? $_POST['adminPassword'] : '';

if ($providedPassword !== $adminPassword) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Database configuration
$db_host = 'localhost';
$db_name = 'spd_sports_therapy'; // Update with your database name
$db_user = 'root'; // Update with your database user
$db_password = ''; // Update with your database password

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
