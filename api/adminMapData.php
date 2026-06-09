<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['admin']);

header("Content-Type: application/json; charset=UTF-8");

// Fetch active bus tracking from active_trips for stronger trip state
$stmtBuses = $conn->query("
    SELECT at.*, b.bus_name, d.username AS driver_username
    FROM active_trips at
    INNER JOIN drivers d ON d.id = at.driver_id AND d.is_active = 1
    LEFT JOIN buses b ON b.bus_id = at.bus_id
    WHERE at.latitude IS NOT NULL
      AND at.longitude IS NOT NULL
");
$buses = $stmtBuses->fetchAll(PDO::FETCH_ASSOC);

// Calculate Stall Logic inside PHP so UI is cleaner
$current_time = time();
foreach ($buses as &$bus) {
    $pingAgo = $current_time - strtotime($bus['last_ping_at']);
    $moveAgo = $current_time - strtotime($bus['last_moving_at']);
    $bus['last_ping_secs'] = $pingAgo;
    $bus['stalled_mins'] = round($moveAgo / 60);

    if ($pingAgo >= 30 && $pingAgo <= 60) {
        $bus['status'] = 'Interrupted';
    } elseif ($moveAgo > 300) {
        if ($bus['status'] === 'Running') $bus['status'] = 'Stalled';
        if ($bus['status'] === 'On Break') $bus['status'] = 'On Break';
        if ($bus['status'] === 'Breakdown') $bus['status'] = 'Alert';
    }
}

// Fetch active Emergencies
$stmtEmergencies = $conn->query("SELECT * FROM bus_emergencies WHERE status = 'Active'");
$emergencies = $stmtEmergencies->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "buses" => $buses,
    "emergencies" => $emergencies,
    "metrics" => [
        "active_trips" => count($buses),
        "stalled" => count(array_filter($buses, function ($b) { return in_array($b['status'], ['Stalled', 'Interrupted'], true); })),
        "sos_alerts" => count($emergencies)
    ]
]);
?>
