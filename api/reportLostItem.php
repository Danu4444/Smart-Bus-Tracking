<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_role_json(['passenger']);

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->bus_id) &&
    !empty($data->passenger_name) &&
    !empty($data->passenger_phone) &&
    !empty($data->item_description)
) {
    require_passenger_phone_matches(strip_tags($data->passenger_phone));
    try {
        $stmt = $conn->prepare("INSERT INTO lost_items (bus_id, passenger_name, passenger_phone, item_description) VALUES (:bus_id, :p_name, :p_phone, :desc)");
        
        $stmt->bindParam(":bus_id", strip_tags($data->bus_id));
        $stmt->bindParam(":p_name", strip_tags($data->passenger_name));
        $stmt->bindParam(":p_phone", strip_tags($data->passenger_phone));
        $stmt->bindParam(":desc", strip_tags($data->item_description));
        
        if($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["success" => true, "message" => "Item reported successfully. The driver has been notified."]);
        } else {
            http_response_code(503);
            echo json_encode(["error" => "Unable to file report."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Incomplete data. Provide bus_id, name, phone, and desc."]);
}
?>
