<?php
/*==========================================================
  GET RANDOM REVIEWS API
  Fetches random reviews from the database
==========================================================*/

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

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

    // Get the limit (default 6 reviews)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;

    // Query to get random reviews with user information
    $query = "
        SELECT
            u.full_name,
            u.activity,
            r.review,
            r.rating,
            r.review_date
        FROM users u
        INNER JOIN reviews r ON u.user_id = r.user_id
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
