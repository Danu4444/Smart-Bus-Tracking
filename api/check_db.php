<?php
require_once "db.php";
$stmt = $conn->query("SELECT * FROM active_trips");
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($result);
?>
