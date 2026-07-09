<?php
/*==========================================================
  RESEND REVIEW EMAIL API
  Allows admin to manually resend email for approved reviews
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

// Check session authentication
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized - Please log in'
    ]);
    exit();
}

// Get review ID
$reviewId = isset($_POST['reviewId']) ? intval($_POST['reviewId']) : 0;

if ($reviewId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid review ID'
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
$siteUrl = 'http://localhost';

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

    // Get review details
    $query = "
        SELECT
            r.review_id,
            u.full_name,
            u.email,
            u.activity,
            r.review,
            r.rating,
            r.status
        FROM reviews r
        INNER JOIN users u ON r.user_id = u.user_id
        WHERE r.review_id = :review_id
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
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

    // Generate new tokens for approve/reject
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
    $stars = str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']);
    
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
                <h2>Review Resent</h2>
                <p>SPD Sports Therapy Review Management</p>
            </div>
            
            <div class='content'>
                <p>Here is the review that was previously submitted:</p>
                
                <div class='review-details'>
                    <div class='review-field'>
                        <strong>Reviewer Name:</strong> " . htmlspecialchars($review['full_name']) . "
                    </div>
                    <div class='review-field'>
                        <strong>Email:</strong> <a href='mailto:" . htmlspecialchars($review['email']) . "'>" . htmlspecialchars($review['email']) . "</a>
                    </div>
                    <div class='review-field'>
                        <strong>Activity:</strong> " . htmlspecialchars($review['activity']) . "
                    </div>
                    <div class='review-field'>
                        <strong>Rating:</strong> <span class='rating'>" . $stars . "</span> (" . $review['rating'] . "/5)
                    </div>
                </div>
                
                <div class='review-field'>
                    <strong>Review:</strong>
                </div>
                <div class='review-text'>
                    " . nl2br(htmlspecialchars($review['review'])) . "
                </div>
                
                <p><strong>What would you like to do?</strong></p>
                <div class='action-buttons'>
                    <a href='" . htmlspecialchars($approveUrl) . "' class='button button-approve'>✓ Approve</a>
                    <a href='" . htmlspecialchars($rejectUrl) . "' class='button button-reject'>✕ Reject</a>
                </div>
                
                <p style='font-size: 12px; color: #666;'>
                    <strong>Note:</strong> These links expire in 24 hours.
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

    // Build plain text version
    $textBody = "
Review Resent
SPD Sports Therapy Review Management

Reviewer: " . $review['full_name'] . "
Email: " . $review['email'] . "
Activity: " . $review['activity'] . "
Rating: " . $review['rating'] . "/5

Review:
" . $review['review'] . "

---

Approve: " . $approveUrl . "
Reject: " . $rejectUrl . "

These links expire in 24 hours.

SPD Sports Therapy • Review Management System
© " . date('Y') . " All rights reserved
    ";

    // Send email
    $subject = "Review from " . $review['full_name'] . " - SPD Sports Therapy (Resent)";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: <noreply@spdsportstherapy.com>" . "\r\n";
    $headers .= "Reply-To: " . $review['email'] . "\r\n";

    $mailSent = mail($adminEmail, $subject, $htmlBody, $headers);

    if ($mailSent) {
        // Update email sent timestamp
        $updateQuery = "
            UPDATE reviews
            SET email_sent_at = NOW()
            WHERE review_id = :review_id
        ";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute([':review_id' => $reviewId]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Email resent successfully',
            'review_id' => $reviewId
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send email'
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
