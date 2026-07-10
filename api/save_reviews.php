<?php
/*==========================================================
  SAVE REVIEWS API
  Handles saving reviews.json updates from admin panel
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
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['reviews']) || !is_array($input['reviews'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid reviews data']);
        exit();
    }

    // Path to reviews JSON file
    $reviewsFile = __DIR__ . '/../reviews/reviews.json';

    // Save reviews
    $reviewsData = ['reviews' => $input['reviews']];
    
    if (!file_put_contents($reviewsFile, json_encode($reviewsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX)) {
        throw new Exception('Failed to save reviews');
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Reviews saved successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error saving reviews',
        'error' => $e->getMessage()
    ]);
}
?>
