<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();

header("Content-Type: application/json; charset=UTF-8");

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// Get posted JSON data
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->bus_id) &&
    isset($data->latitude) &&
    isset($data->longitude) &&
    !empty($data->from_city) &&
    !empty($data->to_city) &&
    !empty($data->driver_username)
) {
    require_role_json(['driver']);

    $bus_id = htmlspecialchars(strip_tags($data->bus_id));
    $from_city = htmlspecialchars(strip_tags($data->from_city));
    $to_city = htmlspecialchars(strip_tags($data->to_city));
    $crowd_level = isset($data->crowd_level) ? htmlspecialchars(strip_tags($data->crowd_level)) : 'Medium';
    $latitude = floatval($data->latitude);
    $longitude = floatval($data->longitude);

    $status = isset($data->status) ? htmlspecialchars(strip_tags($data->status)) : 'Running';
    $driver_username = htmlspecialchars(strip_tags($data->driver_username));
    require_driver_username_matches($driver_username);
    $allowedStatuses = ['Running', 'On Break', 'Breakdown'];
    if (!in_array($status, $allowedStatuses, true)) {
        $status = 'Running';
    }

    // Driver must exist and be active
    $driverStmt = $conn->prepare("SELECT id, is_active FROM drivers WHERE username = :u LIMIT 1");
    $driverStmt->execute([':u' => $driver_username]);
    $driver = $driverStmt->fetch(PDO::FETCH_ASSOC);
    if (!$driver) {
        http_response_code(401);
        echo json_encode(["error" => "Driver session invalid."]);
        exit;
    }
    if (intval($driver['is_active']) !== 1) {
        http_response_code(403);
        echo json_encode(["error" => "Driver disabled by admin."]);
        exit;
    }
    $driver_id = intval($driver['id']);

    // Ensure one active driver per bus and one active trip per driver
    $activeTripStmt = $conn->prepare("SELECT id, bus_id, latitude, longitude FROM active_trips WHERE driver_id = :driver_id LIMIT 1");
    $activeTripStmt->execute([':driver_id' => $driver_id]);
    $driverTrip = $activeTripStmt->fetch(PDO::FETCH_ASSOC);

    if ($driverTrip && $driverTrip['bus_id'] !== $bus_id) {
        http_response_code(409);
        echo json_encode(["error" => "Driver already has an active trip on another bus. End it first."]);
        exit;
    }

    $busTripStmt = $conn->prepare("SELECT driver_id FROM active_trips WHERE bus_id = :bus_id LIMIT 1");
    $busTripStmt->execute([':bus_id' => $bus_id]);
    $busTrip = $busTripStmt->fetch(PDO::FETCH_ASSOC);
    if ($busTrip && intval($busTrip['driver_id']) !== $driver_id) {
        http_response_code(409);
        echo json_encode(["error" => "This bus already has an active driver trip."]);
        exit;
    }

    // Check movement threshold: ignore <10m
    $query = "SELECT latitude, longitude, last_moving_timestamp FROM bus_location WHERE bus_id = :bus_id LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute([':bus_id' => $bus_id]);
    
    $is_moving = true; // Assume moving by default
    if ($stmt->rowCount() > 0) {
        $lastRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $earthRadius = 6371000;
        $lat1 = deg2rad(floatval($lastRow['latitude']));
        $lon1 = deg2rad(floatval($lastRow['longitude']));
        $lat2 = deg2rad($latitude);
        $lon2 = deg2rad($longitude);
        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;
        $a = sin($dLat / 2) * sin($dLat / 2) + cos($lat1) * cos($lat2) * sin($dLon / 2) * sin($dLon / 2);
        $distanceMeters = 2 * $earthRadius * atan2(sqrt($a), sqrt(1 - $a));
        if ($distanceMeters < 10) $is_moving = false;
    }

    if ($is_moving) {
        // GPS moved - update everything including last_moving_timestamp
        $sql = "INSERT INTO bus_location (bus_id, from_city, to_city, crowd_level, status, latitude, longitude, last_moving_timestamp, updated_at) 
                VALUES (:bus_id, :from, :to, :crowd, :status, :lat, :lng, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE 
                from_city = VALUES(from_city), to_city = VALUES(to_city), crowd_level = VALUES(crowd_level), 
                status = VALUES(status), latitude = VALUES(latitude), longitude = VALUES(longitude), 
                last_moving_timestamp = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP";
    } else {
        // GPS hasn't moved - DO NOT update last_moving_timestamp so the admin can calculate stall time
        $sql = "INSERT INTO bus_location (bus_id, from_city, to_city, crowd_level, status, latitude, longitude, updated_at) 
                VALUES (:bus_id, :from, :to, :crowd, :status, :lat, :lng, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE 
                from_city = VALUES(from_city), to_city = VALUES(to_city), crowd_level = VALUES(crowd_level), 
                status = VALUES(status), latitude = VALUES(latitude), longitude = VALUES(longitude),
                updated_at = CURRENT_TIMESTAMP";
    }

    $insertStmt = $conn->prepare($sql);
    $insertStmt->execute([
        ':bus_id' => $bus_id,
        ':from' => $from_city,
        ':to' => $to_city,
        ':crowd' => $crowd_level,
        ':status' => $status,
        ':lat' => $latitude,
        ':lng' => $longitude
    ]);

    // Upsert active trip state
    if ($driverTrip) {
        if ($is_moving) {
            $tripSql = "UPDATE active_trips SET from_city = :from_city, to_city = :to_city, crowd_level = :crowd, status = :status, latitude = :lat, longitude = :lng, last_moving_at = CURRENT_TIMESTAMP, last_ping_at = CURRENT_TIMESTAMP WHERE driver_id = :driver_id";
        } else {
            $tripSql = "UPDATE active_trips SET from_city = :from_city, to_city = :to_city, crowd_level = :crowd, status = :status, latitude = :lat, longitude = :lng, last_ping_at = CURRENT_TIMESTAMP WHERE driver_id = :driver_id";
        }
        $tripStmt = $conn->prepare($tripSql);
        $tripStmt->execute([
            ':from_city' => $from_city,
            ':to_city' => $to_city,
            ':crowd' => $crowd_level,
            ':status' => $status,
            ':lat' => $latitude,
            ':lng' => $longitude,
            ':driver_id' => $driver_id
        ]);
    } else {
        $tripStmt = $conn->prepare("INSERT INTO active_trips (driver_id, bus_id, from_city, to_city, crowd_level, status, latitude, longitude) VALUES (:driver_id, :bus_id, :from_city, :to_city, :crowd, :status, :lat, :lng)");
        $tripStmt->execute([
            ':driver_id' => $driver_id,
            ':bus_id' => $bus_id,
            ':from_city' => $from_city,
            ':to_city' => $to_city,
            ':crowd' => $crowd_level,
            ':status' => $status,
            ':lat' => $latitude,
            ':lng' => $longitude
        ]);
    }

    http_response_code(201);
    echo json_encode(["message" => "Location & status updated."]);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Incomplete data. Required: bus_id, from_city, to_city, latitude, longitude, driver_username."]);
}
?>
