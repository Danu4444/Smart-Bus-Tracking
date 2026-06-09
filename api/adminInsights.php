<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['admin']);

header("Content-Type: application/json; charset=UTF-8");

try {
    $lostStmt = $conn->query("
        SELECT id, bus_id, passenger_name, passenger_phone, item_description, status, created_at
        FROM lost_items
        ORDER BY created_at DESC
        LIMIT 20
    ");
    $lost_items = $lostStmt->fetchAll(PDO::FETCH_ASSOC);

    $chatStmt = $conn->query("
        SELECT id, trip_bus_id, sender_type, sender_id, message, created_at
        FROM chats
        ORDER BY created_at DESC
        LIMIT 30
    ");
    $chats = $chatStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "lost_items" => $lost_items,
        "chats" => $chats
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
