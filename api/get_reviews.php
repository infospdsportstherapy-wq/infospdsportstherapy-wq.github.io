<?php
/*==========================================================
  GET RANDOM REVIEWS API - JSON VERSION
  Fetches random reviews from the JSON file
  Used for the homepage reviews carousel
==========================================================*/

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

try {
    // Get the limit (default to all reviews if not specified)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 999;

    // Path to reviews JSON file
    $reviewsFile = __DIR__ . '/../reviews/reviews.json';

    // Check if file exists
    if (!file_exists($reviewsFile)) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [],
            'count' => 0
        ]);
        exit();
    }

    // Read and parse JSON file
    $content = file_get_contents($reviewsFile);
    $data = json_decode($content, true);
    
    if (!isset($data['reviews']) || !is_array($data['reviews'])) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [],
            'count' => 0
        ]);
        exit();
    }

    // Get only approved reviews (all reviews are auto-approved in JSON version)
    $reviews = $data['reviews'];
    
    // Shuffle and limit
    shuffle($reviews);
    $reviews = array_slice($reviews, 0, $limit);

    // Transform to match expected format for homepage carousel
    $formatted = array_map(function($review) {
        return [
            'full_name' => $review['fullName'],
            'activity' => $review['activity'],
            'review' => $review['review'],
            'rating' => $review['rating'],
            'review_date' => $review['submittedAt']
        ];
    }, $reviews);

    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $formatted,
        'count' => count($formatted)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error loading reviews',
        'message' => $e->getMessage()
    ]);
}
?>
