<?php
require 'api/db.php';
try {
    $conn->exec('ALTER TABLE bus_location ADD COLUMN last_moving_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
    echo 'Column added successfully.';
} catch(PDOException $e) {
    echo 'Already exists or error: ' . $e->getMessage();
}
?>
