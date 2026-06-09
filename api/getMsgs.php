<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_any_role_json(['passenger', 'driver', 'admin']);

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_GET['bus_id'])) {
    http_response_code(400);
    exit;
}

$bus_id = htmlspecialchars(strip_tags($_GET['bus_id']));

$stmt = $conn->prepare("SELECT * FROM chats WHERE trip_bus_id = :bus ORDER BY created_at ASC");
$stmt->execute([':bus' => $bus_id]);

$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["chats" => $chats]);
?>
