<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_any_role_json(['admin', 'driver']);

header("Content-Type: application/json; charset=UTF-8");

try {
    $stmt = $conn->prepare("SELECT bus_id, bus_name FROM buses ORDER BY bus_id ASC");
    $stmt->execute();
    
    $buses = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $buses[] = $row;
    }
    
    echo json_encode(["buses" => $buses]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
