<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['passenger']);

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->phone) && !empty($data->bus_id) && !empty($data->from_city) && !empty($data->to_city)) {
    $phone = htmlspecialchars(strip_tags($data->phone));
    require_passenger_phone_matches($phone);
    $bus_id = htmlspecialchars(strip_tags($data->bus_id));
    $from_city = htmlspecialchars(strip_tags($data->from_city));
    $to_city = htmlspecialchars(strip_tags($data->to_city));
    
    $stmt = $conn->prepare("INSERT INTO passenger_history (phone, bus_id, from_city, to_city) VALUES (:phone, :bus_id, :from, :to)");
    
    if ($stmt->execute([
        ':phone' => $phone,
        ':bus_id' => $bus_id,
        ':from' => $from_city,
        ':to' => $to_city
    ])) {
        echo json_encode(["success" => true]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Server error"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
}
?>
