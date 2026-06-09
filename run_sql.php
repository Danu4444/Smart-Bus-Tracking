<?php
require_once "api/db.php";

$sql = file_get_contents(__DIR__ . "/schema.sql");

try {
    if ($sql === false) {
        throw new Exception("Failed to read schema.sql");
    }
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if ($statement === '') continue;
        try {
            $conn->exec($statement);
        } catch (PDOException $stmtErr) {
            $msg = $stmtErr->getMessage();
            $ignorable = strpos($msg, 'Duplicate key name') !== false ||
                        strpos($msg, 'already exists') !== false ||
                        strpos($msg, 'Duplicate column name') !== false;
            if (!$ignorable) {
                throw $stmtErr;
            }
        }
    }

    // Safety migrations for older databases
    try { $conn->exec("ALTER TABLE drivers ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1"); } catch (PDOException $e) {}
    try { $conn->exec("ALTER TABLE drivers MODIFY COLUMN bus_id VARCHAR(50) NOT NULL DEFAULT ''"); } catch (PDOException $e) {}
    try { $conn->exec("ALTER TABLE bus_location ADD UNIQUE KEY uniq_bus_location_bus (bus_id)"); } catch (PDOException $e) {}
    try {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS active_trips (
                id INT AUTO_INCREMENT PRIMARY KEY,
                driver_id INT NOT NULL,
                bus_id VARCHAR(50) NOT NULL,
                from_city VARCHAR(100) NOT NULL,
                to_city VARCHAR(100) NOT NULL,
                crowd_level VARCHAR(20) DEFAULT 'Medium',
                status VARCHAR(30) DEFAULT 'Running',
                latitude FLOAT NOT NULL,
                longitude FLOAT NOT NULL,
                last_moving_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_ping_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_active_driver (driver_id),
                UNIQUE KEY uniq_active_bus (bus_id)
            )
        ");
    } catch (PDOException $e) {}
    try {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS trip_history_summary (
                id INT AUTO_INCREMENT PRIMARY KEY,
                driver_id INT NOT NULL,
                bus_id VARCHAR(50) NOT NULL,
                from_city VARCHAR(100) NOT NULL,
                to_city VARCHAR(100) NOT NULL,
                start_lat FLOAT NULL,
                start_lng FLOAT NULL,
                end_lat FLOAT NULL,
                end_lng FLOAT NULL,
                ended_reason VARCHAR(30) DEFAULT 'manual',
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                ended_at TIMESTAMP NULL
            )
        ");
    } catch (PDOException $e) {}

    echo "Database schema applied successfully!\n";
} catch(PDOException $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
