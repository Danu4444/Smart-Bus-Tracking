<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['passenger']);

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';

try {
    // Only search vehicles that have updated their location in the last 2 hours 
    // to prevent fetching ancient dead trips.
    $query = "
        SELECT DISTINCT at.bus_id, b.bus_name, at.from_city, at.to_city, at.status, at.last_ping_at
        FROM active_trips at
        JOIN buses b ON at.bus_id = b.bus_id
        WHERE at.last_ping_at >= NOW() - INTERVAL 2 HOUR
    ";
    
    // Add fuzzy search matching 
    if (!empty($from)) {
        $query .= " AND at.from_city = :from_city";
    }
    if (!empty($to)) {
        $query .= " AND at.to_city = :to_city";
    }

    $stmt = $conn->prepare($query);

    if (!empty($from)) {
        $from_param = $from;
        $stmt->bindParam(":from_city", $from_param);
    }
    if (!empty($to)) {
        $to_param = $to;
        $stmt->bindParam(":to_city", $to_param);
    }

    $stmt->execute();
    
    $buses = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $buses[] = $row;
    }
    
    echo json_encode(["results" => $buses]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
