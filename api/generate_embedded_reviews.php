<?php
/*==========================================================
  GENERATE EMBEDDED REVIEWS DATA
  Creates a JavaScript snippet with reviews.json data
  for embedding in HTML files
==========================================================*/

header('Content-Type: application/javascript');

try {
    // Read reviews from JSON file
    $reviewsFile = __DIR__ . '/../reviews/reviews.json';
    
    if (!file_exists($reviewsFile)) {
        echo "// No reviews file found";
        exit();
    }
    
    $content = file_get_contents($reviewsFile);
    $data = json_decode($content, true);
    
    if (!is_array($data) || !isset($data['reviews'])) {
        echo "// Invalid reviews data";
        exit();
    }
    
    // Generate JavaScript variable
    echo "// Auto-generated from reviews.json - Last updated: " . date('Y-m-d H:i:s') . "\n";
    echo "const embeddedReviewsData = " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ";\n";
    
} catch (Exception $e) {
    echo "// Error: " . $e->getMessage();
}
?>
