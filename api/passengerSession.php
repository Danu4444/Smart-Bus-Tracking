<?php
require_once dirname(__DIR__) . '/includes/session.php';
require_once "db.php";

header("Content-Type: application/json; charset=UTF-8");
api_send_cors_headers();

app_session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'passenger') {
    echo json_encode(["authenticated" => false]);
    exit;
}

if (session_idle_expired()) {
    session_destroy_full();
    echo json_encode(["authenticated" => false]);
    exit;
}

session_touch_activity();

echo json_encode([
    "authenticated" => true,
    "phone" => $_SESSION['passenger_phone'] ?? ''
]);
