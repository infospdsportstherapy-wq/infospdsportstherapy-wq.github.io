<?php
/*==========================================================
  SEND REVIEW NOTIFICATION API
  Sends email notification to admin when new review submitted
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

// Database configuration
$db_host = 'localhost';
$db_name = 'spd_sports_therapy';
$db_user = 'root';
$db_password = '';

// Email configuration
$adminEmail = 'info.spdsportstherapy@gmail.com';
$siteUrl = 'http://localhost'; // Update with your site URL in production

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

    // Generate secure one-time tokens for approve and reject actions
    $approveToken = bin2hex(random_bytes(32));
    $rejectToken = bin2hex(random_bytes(32));
    $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Store approve token
    $tokenQuery = "
        INSERT INTO email_tokens (review_id, token, action, expires_at)
        VALUES (:review_id, :token, :action, :expires_at)
    ";
    $stmt = $pdo->prepare($tokenQuery);
    $stmt->execute([
        ':review_id' => $reviewId,
        ':token' => $approveToken,
        ':action' => 'approve',
        ':expires_at' => $tokenExpiry
    ]);

    // Store reject token
    $stmt->execute([
        ':review_id' => $reviewId,
        ':token' => $rejectToken,
        ':action' => 'reject',
        ':expires_at' => $tokenExpiry
    ]);

    // Build action URLs
    $approveUrl = $siteUrl . "/api/email_action.php?token=" . $approveToken . "&action=approve&review_id=" . $reviewId;
    $rejectUrl = $siteUrl . "/api/email_action.php?token=" . $rejectToken . "&action=reject&review_id=" . $reviewId;

    // Build HTML email
    $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    
    $htmlBody = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #4488ff 0%, #2563eb 100%);
                color: white;
                padding: 20px;
                text-align: center;
            }
            .content {
                padding: 20px;
            }
            .review-details {
                background: #f5f5f5;
                padding: 15px;
                border-radius: 6px;
                margin: 15px 0;
            }
            .review-field {
                margin: 10px 0;
            }
            .review-field strong {
                color: #2563eb;
            }
            .review-text {
                background: white;
                padding: 12px;
                border-left: 4px solid #4488ff;
                margin: 15px 0;
                font-style: italic;
            }
            .action-buttons {
                display: flex;
                gap: 10px;
                margin: 20px 0;
            }
            .button {
                flex: 1;
                padding: 12px;
                text-align: center;
                border-radius: 6px;
                text-decoration: none;
                color: white;
                font-weight: bold;
                display: inline-block;
            }
            .button-approve {
                background: #27ae60;
            }
            .button-approve:hover {
                background: #229954;
            }
            .button-reject {
                background: #e74c3c;
            }
            .button-reject:hover {
                background: #c0392b;
            }
            .footer {
                background: #f5f5f5;
                padding: 15px;
                font-size: 12px;
                color: #666;
                text-align: center;
            }
            .rating {
                font-size: 18px;
                color: #ffc107;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Review Submitted</h2>
                <p>SPD Sports Therapy Review Management</p>
            </div>
            
            <div class='content'>
                <p>A new review has been submitted and is waiting for your approval.</p>
                
                <div class='review-details'>
                    <div class='review-field'>
                        <strong>Reviewer Name:</strong> " . htmlspecialchars($reviewerName) . "
                    </div>
                    <div class='review-field'>
                        <strong>Email:</strong> <a href='mailto:" . htmlspecialchars($reviewerEmail) . "'>" . htmlspecialchars($reviewerEmail) . "</a>
                    </div>
                    <div class='review-field'>
                        <strong>Activity:</strong> " . htmlspecialchars($activity) . "
                    </div>
                    <div class='review-field'>
                        <strong>Rating:</strong> <span class='rating'>" . $stars . "</span> (" . $rating . "/5)
                    </div>
                </div>
                
                <div class='review-field'>
                    <strong>Review:</strong>
                </div>
                <div class='review-text'>
                    " . nl2br(htmlspecialchars($reviewText)) . "
                </div>
                
                <p><strong>What would you like to do?</strong></p>
                <div class='action-buttons'>
                    <a href='" . htmlspecialchars($approveUrl) . "' class='button button-approve'>✓ Approve</a>
                    <a href='" . htmlspecialchars($rejectUrl) . "' class='button button-reject'>✕ Reject</a>
                </div>
                
                <p style='font-size: 12px; color: #666;'>
                    <strong>Note:</strong> These links expire in 24 hours. You can also manage reviews directly in your <a href='" . $siteUrl . "/admin/'>admin dashboard</a>.
                </p>
            </div>
            
            <div class='footer'>
                <p>SPD Sports Therapy • Review Management System</p>
                <p>© " . date('Y') . " All rights reserved</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Build plain text version for email clients that don't support HTML
    $textBody = "
New Review Submitted
SPD Sports Therapy Review Management

A new review has been submitted and is waiting for your approval.

---
Reviewer: " . $reviewerName . "
Email: " . $reviewerEmail . "
Activity: " . $activity . "
Rating: " . $rating . "/5

Review:
" . $reviewText . "

---

Approve: " . $approveUrl . "
Reject: " . $rejectUrl . "

These links expire in 24 hours.

SPD Sports Therapy • Review Management System
© " . date('Y') . " All rights reserved
    ";

    // Send email using PHP mail()
    $subject = "New Review from " . $reviewerName . " - SPD Sports Therapy";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: <noreply@spdsportstherapy.com>" . "\r\n";
    $headers .= "Reply-To: " . $reviewerEmail . "\r\n";

    $mailSent = mail($adminEmail, $subject, $htmlBody, $headers);

    if ($mailSent) {
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
