<?php
/*==========================================================
  SUBMIT REVIEW API - JSON VERSION
  Handles review submissions and saves to JSON file
  No database, no emails required
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

try {
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

    // Path to reviews JSON file
    $reviewsFile = __DIR__ . '/../reviews/reviews.json';

    // Ensure reviews directory exists
    if (!is_dir(dirname($reviewsFile))) {
        mkdir(dirname($reviewsFile), 0755, true);
    }

    // Read existing reviews
    $reviewsData = ['reviews' => []];
    if (file_exists($reviewsFile)) {
        $content = file_get_contents($reviewsFile);
        $decoded = json_decode($content, true);
        if (is_array($decoded) && isset($decoded['reviews'])) {
            $reviewsData = $decoded;
        }
    }

    // Check for duplicate review within last 24 hours (spam prevention)
    $now = time();
    $oneDayAgo = $now - (24 * 60 * 60);
    
    foreach ($reviewsData['reviews'] as $existingReview) {
        if ($existingReview['email'] === $email && $existingReview['timestamp'] > $oneDayAgo) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => 'You have already submitted a review in the last 24 hours. Please try again later.'
            ]);
            exit();
        }
    }

    // Create new review
    $newReview = [
        'id' => count($reviewsData['reviews']) + 1,
        'fullName' => $fullName,
        'email' => $email,
        'activity' => $activity,
        'rating' => $rating,
        'review' => $review,
        'timestamp' => $now,
        'submittedAt' => date('Y-m-d H:i:s', $now),
        'status' => 'approved'
    ];

    // Add to reviews
    $reviewsData['reviews'][] = $newReview;

    // Save to file
    if (!file_put_contents($reviewsFile, json_encode($reviewsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX)) {
        throw new Exception('Failed to save review');
    }

    // Update the embedded reviews data in services.html for file:// protocol compatibility
    updateEmbeddedReviewsInHTML($reviewsData);

    // Return success response
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Review submitted successfully!',
        'review_id' => $newReview['id']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $e->getMessage()
    ]);
}

/**
 * Update the embedded reviews data in services.html
 * This ensures that the carousel displays new reviews even when accessed via file:// protocol
 */
function updateEmbeddedReviewsInHTML($reviewsData) {
    try {
        $servicesFile = __DIR__ . '/../services.html';
        if (!file_exists($servicesFile)) {
            return; // File doesn't exist, skip update
        }

        $htmlContent = file_get_contents($servicesFile);
        
        // Generate the new embedded data
        $embeddedData = "const embeddedReviewsData = " . json_encode($reviewsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ";";
        
        // Find and replace the embeddedReviewsData in the HTML
        $pattern = '/const embeddedReviewsData = \{[\s\S]*?\};/';
        $replacement = $embeddedData;
        
        $newHtmlContent = preg_replace($pattern, $replacement, $htmlContent, 1);
        
        if ($newHtmlContent !== $htmlContent) {
            file_put_contents($servicesFile, $newHtmlContent, LOCK_EX);
        }
    } catch (Exception $e) {
        // Silently fail - don't block review submission if HTML update fails
        error_log("Warning: Failed to update embedded reviews in HTML: " . $e->getMessage());
    }
}
?>
