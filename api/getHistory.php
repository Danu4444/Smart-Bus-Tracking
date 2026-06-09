<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['passenger']);

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_GET['phone'])) {
    http_response_code(400);
    echo json_encode(["error" => "Phone number required."]);
    exit;
}

$phone = htmlspecialchars(strip_tags($_GET['phone']));
require_passenger_phone_matches($phone);

$stmt = $conn->prepare("SELECT bus_id, from_city, to_city, travel_date FROM passenger_history WHERE phone = :phone ORDER BY travel_date DESC LIMIT 10");
$stmt->execute([':phone' => $phone]);

$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["history" => $history]);
?>
