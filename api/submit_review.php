<?php
/*==========================================================
  SUBMIT REVIEW API
  Handles new review submissions from users
  Uses centralized config for database credentials
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

try {
    // Create PDO connection using config
    $pdo = getDbConnection();

    // Get and validate form data
    $fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $activity = isset($_POST['activity']) ? trim($_POST['activity']) : '';
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;
    $review = isset($_POST['review']) ? trim($_POST['review']) : '';

    // Validate required fields
    $errors = [];

    if (empty($fullName)) {
        $errors[] = 'Full name is required';
    } elseif (strlen($fullName) < 2 || strlen($fullName) > 100) {
        $errors[] = 'Full name must be between 2 and 100 characters';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($activity)) {
        $errors[] = 'Sport/Activity is required';
    } elseif (strlen($activity) < 2 || strlen($activity) > 100) {
        $errors[] = 'Activity must be between 2 and 100 characters';
    }

    if ($rating === null || $rating < 3 || $rating > 5) {
        $errors[] = 'Rating must be between 3 and 5 stars';
    }

    if (empty($review)) {
        $errors[] = 'Review text is required';
    } elseif (strlen($review) < 10) {
        $errors[] = 'Review must be at least 10 characters long';
    } elseif (strlen($review) > 5000) {
        $errors[] = 'Review must not exceed 5000 characters';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit();
    }

    // Check for duplicate review by same email within last 24 hours (spam prevention)
    $checkDuplicateQuery = "
        SELECT r.review_id
        FROM reviews r
        INNER JOIN users u ON r.user_id = u.user_id
        WHERE u.email = :email
        AND r.review_date > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        LIMIT 1
    ";

    $stmt = $pdo->prepare($checkDuplicateQuery);
    $stmt->execute([':email' => $email]);
    $duplicate = $stmt->fetch();

    if ($duplicate) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'You have already submitted a review in the last 24 hours. Please try again later.'
        ]);
        exit();
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        // Check if user already exists (by email)
        $userQuery = "SELECT user_id FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($userQuery);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        $userId = null;

        if ($user) {
            // User exists, use their ID
            $userId = $user['user_id'];
        } else {
            // Create new user
            $createUserQuery = "
                INSERT INTO users (full_name, email, activity, created_at)
                VALUES (:fullName, :email, :activity, NOW())
            ";

            $stmt = $pdo->prepare($createUserQuery);
            $stmt->execute([
                ':fullName' => $fullName,
                ':email' => $email,
                ':activity' => $activity
            ]);

            $userId = $pdo->lastInsertId();
        }

        // Insert review with pending status
        $insertReviewQuery = "
            INSERT INTO reviews (user_id, review, rating, status, review_date)
            VALUES (:userId, :review, :rating, 'pending', NOW())
        ";

        $stmt = $pdo->prepare($insertReviewQuery);
        $stmt->execute([
            ':userId' => $userId,
            ':review' => $review,
            ':rating' => $rating
        ]);

        $reviewId = $pdo->lastInsertId();

        // Commit transaction
        $pdo->commit();

        // Generate approval tokens and send admin notification email asynchronously
        $approveToken = generateEmailToken($reviewId, 'approve', 24);
        $rejectToken = generateEmailToken($reviewId, 'reject', 24);
        
        if ($approveToken && $rejectToken) {
            $siteUrl = getConfig('SITE_URL');
            $adminEmail = getConfig('ADMIN_EMAIL');
            
            $approveUrl = $siteUrl . "/api/email_action.php?token=" . urlencode($approveToken);
            $rejectUrl = $siteUrl . "/api/email_action.php?token=" . urlencode($rejectToken);
            
            // Render and send email
            $htmlBody = renderEmailTemplate('admin-notification', [
                'reviewerName' => $fullName,
                'reviewerEmail' => $email,
                'activity' => $activity,
                'rating' => $rating,
                'reviewText' => $review,
                'approveLink' => $approveUrl,
                'rejectLink' => $rejectUrl,
                'siteName' => 'SPD Sports Therapy'
            ]);
            
            sendEmail(
                $adminEmail,
                "New Review from " . $fullName . " - SPD Sports Therapy",
                $htmlBody
            );
            
            // Update review email_sent flag
            $updateQuery = "UPDATE reviews SET email_sent = TRUE, email_sent_at = NOW() WHERE review_id = :review_id";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute([':review_id' => $reviewId]);
        }

        // Return success response
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Review submitted successfully and is awaiting approval',
            'review_id' => $reviewId,
            'status' => 'pending'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
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
