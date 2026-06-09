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

if (!isset($_GET['bus_id']) || empty($_GET['bus_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing bus_id parameter."]);
    exit;
}

$bus_id = htmlspecialchars(strip_tags($_GET['bus_id']));

// Fetch the last 20 locations for the specified bus, ordered oldest to newest (to draw route properly)
$query = "SELECT latitude, longitude, updated_at, crowd_level FROM (
            SELECT latitude, longitude, updated_at, crowd_level 
            FROM bus_location 
            WHERE bus_id = :bus_id 
            ORDER BY updated_at DESC 
            LIMIT 20
          ) as recent_locations 
          ORDER BY updated_at ASC";

$stmt = $conn->prepare($query);
$stmt->bindParam(":bus_id", $bus_id);
$stmt->execute();

$locations = [];
$latest_crowd = 'Medium'; // fallback
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $latest_crowd = $row['crowd_level']; // Since it's ordered ASC, the very last iteration has newest crowd level
    $locations[] = [
        "latitude" => floatval($row['latitude']),
        "longitude" => floatval($row['longitude']),
        "updated_at" => $row['updated_at']
    ];
}

$tripStmt = $conn->prepare("SELECT status, last_ping_at FROM active_trips WHERE bus_id = :bus_id LIMIT 1");
$tripStmt->execute([':bus_id' => $bus_id]);
$trip = $tripStmt->fetch(PDO::FETCH_ASSOC);
$status = $trip ? $trip['status'] : 'Idle';
$last_ping_secs = $trip ? max(0, (time() - strtotime($trip['last_ping_at']))) : null;

echo json_encode([
    "bus_id" => $bus_id,
    "locations" => $locations,
    "crowd_level" => $latest_crowd,
    "status" => $status,
    "last_ping_secs" => $last_ping_secs
]);
?>
