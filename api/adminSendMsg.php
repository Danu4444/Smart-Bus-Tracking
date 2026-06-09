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

require_role_json(['admin']);

$data = json_decode(file_get_contents("php://input"), true);

$bus_id  = isset($data['bus_id'])  ? trim($data['bus_id'])  : '';
$message = isset($data['message']) ? trim($data['message']) : '';

if (empty($bus_id) || empty($message)) {
    http_response_code(400);
    echo json_encode(["error" => "bus_id and message are required"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO chats (trip_bus_id, sender_type, sender_id, message)
        VALUES (:bus_id, 'driver', 'Admin', :message)
    ");
    $stmt->execute([':bus_id' => $bus_id, ':message' => $message]);
    echo json_encode(["success" => true, "message" => "Notification sent to driver"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB error: " . $e->getMessage()]);
}
