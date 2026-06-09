<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['driver']);

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_GET['bus_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing bus_id parameter."]);
    exit;
}

$bus_id = strip_tags($_GET['bus_id']);

try {
    $stmt = $conn->prepare("SELECT * FROM lost_items WHERE bus_id = :bus_id AND status = 'Lost' ORDER BY created_at DESC");
    $stmt->bindParam(":bus_id", $bus_id);
    $stmt->execute();
    
    $items = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $items[] = $row;
    }
    
    echo json_encode(["items" => $items]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
