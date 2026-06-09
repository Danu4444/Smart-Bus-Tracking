<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_any_role_json(['passenger', 'driver', 'admin']);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

try {
    $stmt = $conn->prepare("SELECT city_name FROM cities ORDER BY city_name ASC");
    $stmt->execute();
    
    $cities = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cities[] = $row['city_name'];
    }
    
    echo json_encode(["cities" => $cities]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
