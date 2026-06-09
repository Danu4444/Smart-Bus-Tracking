<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
header("Access-Control-Allow-Methods: POST");
api_handle_options_preflight();
require_role_json(['driver']);

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->bus_id) && !empty($data->driver_username) && !empty($data->issue_type)) {
    $bus_id = htmlspecialchars(strip_tags($data->bus_id));
    $driver_username = htmlspecialchars(strip_tags($data->driver_username));
    require_driver_username_matches($driver_username);
    $issue_type = htmlspecialchars(strip_tags($data->issue_type));
    $message = isset($data->message) ? htmlspecialchars(strip_tags($data->message)) : '';

    try {
        $query = "INSERT INTO bus_emergencies (bus_id, driver_username, issue_type, message) 
                  VALUES (:bus_id, :driver_username, :issue_type, :message)";
        $stmt = $conn->prepare($query);

        $stmt->execute([
            ':bus_id' => $bus_id,
            ':driver_username' => $driver_username,
            ':issue_type' => $issue_type,
            ':message' => $message
        ]);

        // Force the bus status to 'Breakdown'
        $upd = $conn->prepare("UPDATE bus_location SET status = 'Breakdown' WHERE bus_id = :bus_id");
        $upd->execute([':bus_id' => $bus_id]);

        http_response_code(201);
        echo json_encode(["success" => true, "message" => "Emergency reported successfully."]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields."]);
}
?>
