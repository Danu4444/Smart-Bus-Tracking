<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_role_json(['admin']);

try {
    $stmt = $conn->prepare("
        SELECT d.id, d.username, d.bus_id, d.is_active, d.created_at, at.bus_id AS active_bus_id
        FROM drivers d
        LEFT JOIN active_trips at ON at.driver_id = d.id
        ORDER BY d.id DESC
    ");
    $stmt->execute();
    
    $drivers = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $drivers[] = $row;
    }
    
    echo json_encode(["drivers" => $drivers]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
