<?php
/*==========================================================
  GET ALL REVIEWS API
  Fetches all reviews for admin dashboard (requires session auth)
  Uses centralized config for database credentials
==========================================================*/

require_once __DIR__ . '/../config/config.php';

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

try {
    // Create PDO connection using config
    $pdo = getDbConnection();

    // Get optional filter parameter
    $status = isset($_GET['status']) ? trim($_GET['status']) : null;
    $orderBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'review_date DESC';

    // Build query
    $query = "
        SELECT
            r.review_id,
            u.full_name,
            u.email,
            u.activity,
            r.review,
            r.rating,
            r.status,
            r.admin_notes,
            r.review_date,
            r.reviewed_at
        FROM reviews r
        INNER JOIN users u ON r.user_id = u.user_id
    ";

    // Add status filter if specified
    if ($status) {
        $allowedStatuses = ['pending', 'approved', 'rejected'];
        if (in_array($status, $allowedStatuses)) {
            $query .= " WHERE r.status = '" . $pdo->quote($status) . "'";
        }
    }

    // Add sorting
    $allowedSorts = ['review_date DESC', 'review_date ASC', 'rating DESC', 'status'];
    $orderBy = in_array($orderBy, $allowedSorts) ? $orderBy : 'review_date DESC';
    $query .= " ORDER BY r." . $orderBy;

    // Limit results (prevent large data transfers)
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000;
    if ($limit > 1000) $limit = 1000;
    $query .= " LIMIT " . $limit;

    $stmt = $pdo->prepare($query);
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
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
}
?>
