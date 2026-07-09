<?php
/*==========================================================
  SEND REVIEW NOTIFICATION API
  Sends email notification to admin when new review submitted
  Uses centralized config and PHPMailer for SMTP
==========================================================*/

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/email.php';

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

// Get data
$reviewId = isset($_POST['reviewId']) ? intval($_POST['reviewId']) : null;
$reviewerName = isset($_POST['reviewerName']) ? trim($_POST['reviewerName']) : '';
$reviewerEmail = isset($_POST['reviewerEmail']) ? trim($_POST['reviewerEmail']) : '';
$activity = isset($_POST['activity']) ? trim($_POST['activity']) : '';
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$reviewText = isset($_POST['reviewText']) ? trim($_POST['reviewText']) : '';

// Validate inputs
if (!$reviewId || $reviewId <= 0 || empty($reviewerName) || empty($reviewText)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required review data'
    ]);
    exit();
}

// Get config values
$adminEmail = getConfig('ADMIN_EMAIL');
$siteUrl = getConfig('SITE_URL');

try {
    // Get database connection using config
    $pdo = getDbConnection();

    // Generate email tokens for approve and reject actions
    $approveToken = generateEmailToken($reviewId, 'approve', 24);
    $rejectToken = generateEmailToken($reviewId, 'reject', 24);

    if (!$approveToken || !$rejectToken) {
        throw new Exception('Failed to generate email tokens');
    }

    // Build action URLs
    $approveUrl = $siteUrl . "/api/email_action.php?token=" . urlencode($approveToken);
    $rejectUrl = $siteUrl . "/api/email_action.php?token=" . urlencode($rejectToken);

    // Render email template
    $htmlBody = renderEmailTemplate('admin-notification', [
        'reviewerName' => $reviewerName,
        'reviewerEmail' => $reviewerEmail,
        'activity' => $activity,
        'rating' => $rating,
        'reviewText' => $reviewText,
        'approveLink' => $approveUrl,
        'rejectLink' => $rejectUrl,
        'siteName' => 'SPD Sports Therapy'
    ]);

    // Send email using PHPMailer or fallback
    $result = sendEmail(
        $adminEmail,
        "New Review from " . $reviewerName . " - SPD Sports Therapy",
        $htmlBody
    );

    if ($result['success']) {
        // Update review email_sent flag
        $updateQuery = "
            UPDATE reviews
            SET email_sent = TRUE, email_sent_at = NOW()
            WHERE review_id = :review_id
        ";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([':review_id' => $reviewId]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Review notification email sent successfully',
            'review_id' => $reviewId
        ]);
    } else {
        // Log failure but don't fail the review submission
        error_log('Email send failed: ' . $result['error']);
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Review received, but email notification failed',
            'review_id' => $reviewId,
            'email_warning' => 'Admin notification email could not be sent'
        ]);
    }

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
