<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_any_role_json(['admin', 'driver']);

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->bus_id) && !empty($data->bus_name)) {
    $bus_id = strtoupper(trim(htmlspecialchars(strip_tags($data->bus_id))));
    $bus_name = trim(htmlspecialchars(strip_tags($data->bus_name)));

    // Validate structure (e.g. KA-19-A-9988 or KL-14-CC-7777)
    if (!preg_match("/^[A-Z]{2}-\d{2}-[A-Z]{1,2}-\d{4}$/", $bus_id)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid Bus ID structure. Format must be like KA-19-A-9988."]);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO buses (bus_id, bus_name) VALUES (:bus_id, :bus_name)");
        $stmt->execute([
            ':bus_id' => $bus_id,
            ':bus_name' => $bus_name
        ]);

        http_response_code(201);
        echo json_encode(["success" => true, "message" => "Bus successfully registered."]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Integrity constraint (duplicate)
            http_response_code(409);
            echo json_encode(["error" => "This bus number already exists in the system."]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        }
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Bus Number and Bus Name are required."]);
}
?>
