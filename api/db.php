<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$server_name = $_SERVER['SERVER_NAME'] ?? '';
$has_env_db = getenv('MYSQLHOST') || getenv('MYSQLDATABASE') || getenv('MYSQLUSER');
$is_local_host = in_array($server_name, ['localhost', '127.0.0.1', '::1'], true)
    || strpos($server_name, '192.168.') === 0;

if ($has_env_db) {
    $host     = getenv('MYSQLHOST')     ?: 'localhost';
    $db_name  = getenv('MYSQLDATABASE') ?: 'bus_tracker_db';
    $username = getenv('MYSQLUSER')     ?: 'root';
    $password = getenv('MYSQLPASSWORD') ?: '';
    $port     = getenv('MYSQLPORT')     ?: '3306';
} elseif (!$is_local_host) {
    // InfinityFree Production Credentials
    // IMPORTANT: You MUST update the host and password before uploading!
    $host     = 'sql200.infinityfree.com'; // Go to InfinityFree MySQL Databases to find your host
    $db_name  = 'if0_41836734_busdb';
    $username = 'if0_41836734';
    $password = 'XVGDyxiKmR5PpDJ';    // Put your InfinityFree account password here
    $port     = '3306';
} else {
    // Local XAMPP Credentials
    $host     = 'localhost';
    $db_name  = 'bus_tracker_db';
    $username = 'root';
    $password = '';
    $port     = '3307';
}

try {
    $conn = new PDO("mysql:host={$host};port={$port};dbname={$db_name}", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    http_response_code(500);
    echo json_encode(["error" => "Connection error: " . $exception->getMessage()]);
    exit;
}
?>
