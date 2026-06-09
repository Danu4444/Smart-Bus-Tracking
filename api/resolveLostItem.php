<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['driver']);

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->item_id)) {
    try {
        $stmt = $conn->prepare("UPDATE lost_items SET status = 'Found' WHERE id = :id");
        $stmt->bindParam(":id", strip_tags($data->item_id));
        
        if($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["success" => true, "message" => "Item marked as Found."]);
        } else {
            http_response_code(503);
            echo json_encode(["error" => "Unable to resolve item."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Missing item_id."]);
}
?>
