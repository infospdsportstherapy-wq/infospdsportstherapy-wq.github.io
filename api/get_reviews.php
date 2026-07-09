<?php
/*==========================================================
  GET RANDOM REVIEWS API
  Fetches random reviews from the database
  Uses centralized config for database credentials
==========================================================*/

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

try {
    // Create PDO connection using config
    $pdo = getDbConnection();

    // Get the limit (default to all reviews if not specified)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 999;

    // Query to get random approved reviews with user information
    $query = "
        SELECT
            u.full_name,
            u.activity,
            r.review,
            r.rating,
            r.review_date
        FROM users u
        INNER JOIN reviews r ON u.user_id = r.user_id
        WHERE r.status = 'approved'
        ORDER BY RAND()
        LIMIT :limit
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll();

    // Return JSON response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $reviews,
        'count' => count($reviews)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'message' => $e->getMessage()
    ]);
}
?>
