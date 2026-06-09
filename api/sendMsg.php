<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

app_session_start();
if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(["error" => "Authentication required"]);
    exit;
}
if (session_idle_expired()) {
    session_destroy_full();
    http_response_code(401);
    echo json_encode(["error" => "Session expired"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->trip_bus_id) && !empty($data->sender_type) && !empty($data->message) && !empty($data->sender_id)) {
    $trip_bus_id = htmlspecialchars(strip_tags($data->trip_bus_id));
    $sender_type = htmlspecialchars(strip_tags($data->sender_type));
    $sender_id = htmlspecialchars(strip_tags($data->sender_id));
    $message = htmlspecialchars(strip_tags($data->message));

    if ($sender_type === 'passenger') {
        if ($_SESSION['role'] !== 'passenger' || ($sender_id !== ($_SESSION['passenger_phone'] ?? ''))) {
            http_response_code(403);
            echo json_encode(["error" => "Forbidden"]);
            exit;
        }
    } elseif ($sender_type === 'driver') {
        if ($_SESSION['role'] !== 'driver' || ($sender_id !== ($_SESSION['driver_username'] ?? ''))) {
            http_response_code(403);
            echo json_encode(["error" => "Forbidden"]);
            exit;
        }
    } else {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden"]);
        exit;
    }

    session_touch_activity();

    $rateStmt = $conn->prepare("SELECT created_at FROM chats WHERE trip_bus_id = :bus AND sender_id = :id ORDER BY created_at DESC LIMIT 1");
    $rateStmt->execute([':bus' => $trip_bus_id, ':id' => $sender_id]);
    $lastMsg = $rateStmt->fetch(PDO::FETCH_ASSOC);
    if ($lastMsg && (time() - strtotime($lastMsg['created_at'])) < 2) {
        http_response_code(429);
        echo json_encode(["error" => "Please wait 2 seconds before sending next message."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO chats (trip_bus_id, sender_type, sender_id, message) VALUES (:bus, :type, :id, :msg)");
    if ($stmt->execute([
        ':bus' => $trip_bus_id,
        ':type' => $sender_type,
        ':id' => $sender_id,
        ':msg' => $message
    ])) {
        echo json_encode(["success" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to save msg"]);
    }
} else {
    http_response_code(400);
}
