<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['driver']);

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$driver_username = isset($data['driver_username']) ? trim($data['driver_username']) : '';
$reason = isset($data['reason']) ? trim($data['reason']) : 'manual';

if ($driver_username === '') {
    http_response_code(400);
    echo json_encode(["error" => "driver_username is required"]);
    exit;
}
require_driver_username_matches($driver_username);

try {
    $driverStmt = $conn->prepare("SELECT id FROM drivers WHERE username = :u LIMIT 1");
    $driverStmt->execute([':u' => $driver_username]);
    $driver = $driverStmt->fetch(PDO::FETCH_ASSOC);
    if (!$driver) {
        http_response_code(404);
        echo json_encode(["error" => "Driver not found"]);
        exit;
    }

    $tripStmt = $conn->prepare("SELECT * FROM active_trips WHERE driver_id = :driver_id LIMIT 1");
    $tripStmt->execute([':driver_id' => intval($driver['id'])]);
    $trip = $tripStmt->fetch(PDO::FETCH_ASSOC);
    if (!$trip) {
        echo json_encode(["success" => true, "message" => "No active trip to end"]);
        exit;
    }

    $ins = $conn->prepare("
        INSERT INTO trip_history_summary
        (driver_id, bus_id, from_city, to_city, start_lat, start_lng, end_lat, end_lng, ended_reason, started_at, ended_at)
        VALUES
        (:driver_id, :bus_id, :from_city, :to_city, :start_lat, :start_lng, :end_lat, :end_lng, :ended_reason, :started_at, NOW())
    ");
    $ins->execute([
        ':driver_id' => $trip['driver_id'],
        ':bus_id' => $trip['bus_id'],
        ':from_city' => $trip['from_city'],
        ':to_city' => $trip['to_city'],
        ':start_lat' => $trip['latitude'],
        ':start_lng' => $trip['longitude'],
        ':end_lat' => $trip['latitude'],
        ':end_lng' => $trip['longitude'],
        ':ended_reason' => $reason,
        ':started_at' => $trip['started_at']
    ]);

    $del = $conn->prepare("DELETE FROM active_trips WHERE id = :id");
    $del->execute([':id' => $trip['id']]);

    echo json_encode(["success" => true, "message" => "Trip ended"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
