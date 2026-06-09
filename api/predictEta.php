<?php
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['passenger']);

header("Content-Type: application/json; charset=UTF-8");

$busLat = isset($_GET['lat']) ? floatval($_GET['lat']) : 0;
$busLng = isset($_GET['lng']) ? floatval($_GET['lng']) : 0;
$userLat = isset($_GET['userLat']) ? floatval($_GET['userLat']) : 0;
$userLng = isset($_GET['userLng']) ? floatval($_GET['userLng']) : 0;
$fallbackDist = isset($_GET['dist']) ? floatval($_GET['dist']) : 0;
$fallbackSpeed = isset($_GET['speed']) ? floatval($_GET['speed']) : 30;

$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

// ---------------------------------------------------------
// 1. Weather API Integration (Cached 5 mins)
// ---------------------------------------------------------
$weatherCondition = "Clear";
$weatherDelayMins = 0;
$icon = "sun";
$openWeatherKey = getenv('OPENWEATHERMAP_API_KEY');

if ($openWeatherKey && $busLat != 0) {
    // Round to 2 decimals for weather cache (~1km grid)
    $wLat = round($busLat, 2);
    $wLng = round($busLng, 2);
    $weatherCacheFile = $cacheDir . "/weather_{$wLat}_{$wLng}.json";
    $weatherData = null;

    if (file_exists($weatherCacheFile) && (time() - filemtime($weatherCacheFile)) < 300) {
        $weatherData = json_decode(file_get_contents($weatherCacheFile), true);
    } else {
        $url = "https://api.openweathermap.org/data/2.5/weather?lat={$busLat}&lon={$busLng}&appid={$openWeatherKey}&units=metric";
        $resp = @file_get_contents($url);
        if ($resp) {
            $weatherData = json_decode($resp, true);
            file_put_contents($weatherCacheFile, $resp);
        }
    }

    if ($weatherData && isset($weatherData['weather'][0]['main'])) {
        $weatherCondition = $weatherData['weather'][0]['main'];
        if (in_array($weatherCondition, ['Rain', 'Drizzle'], true)) {
            $weatherDelayMins = 5;
            $icon = "cloud-rain";
        } elseif ($weatherCondition === 'Thunderstorm') {
            $weatherDelayMins = 10;
            $icon = "cloud-lightning";
        } elseif ($weatherCondition === 'Fog' || $weatherCondition === 'Mist') {
            $weatherDelayMins = 3;
            $icon = "cloud";
        }
    }
}

// ---------------------------------------------------------
// 2. Mapbox Traffic & Directions (Cached 60s)
// ---------------------------------------------------------
$mapboxKey = getenv('MAPBOX_API_KEY');
$baseEtaMins = 0;
$trafficCondition = "Light";
$trafficColor = "#10b981"; // Green
$mapboxUsed = false;

if ($mapboxKey && $busLat != 0 && $userLat != 0) {
    // Round to 4 decimals (~11 meters) to allow slight bus movement without busting cache immediately
    $tBusLat = round($busLat, 4); $tBusLng = round($busLng, 4);
    $tUserLat = round($userLat, 4); $tUserLng = round($userLng, 4);
    $trafficCacheFile = $cacheDir . "/traffic_{$tBusLat}_{$tBusLng}_{$tUserLat}_{$tUserLng}.json";
    $trafficData = null;

    if (file_exists($trafficCacheFile) && (time() - filemtime($trafficCacheFile)) < 60) {
        $trafficData = json_decode(file_get_contents($trafficCacheFile), true);
    } else {
        $url = "https://api.mapbox.com/directions/v5/mapbox/driving-traffic/{$busLng},{$busLat};{$userLng},{$userLat}?access_token={$mapboxKey}";
        $resp = @file_get_contents($url);
        if ($resp) {
            $trafficData = json_decode($resp, true);
            file_put_contents($trafficCacheFile, $resp);
        }
    }

    if ($trafficData && isset($trafficData['routes'][0])) {
        $route = $trafficData['routes'][0];
        $durationSeconds = $route['duration']; // Duration with traffic
        $distanceMeters = $route['distance'];
        
        $baseEtaMins = $durationSeconds / 60;
        $mapboxUsed = true;
        
        // Calculate speed in km/h
        if ($durationSeconds > 0) {
            $speedKmh = ($distanceMeters / 1000) / ($durationSeconds / 3600);
            if ($speedKmh < 15) {
                $trafficCondition = "Heavy";
                $trafficColor = "#ef4444"; // Red
            } elseif ($speedKmh < 30) {
                $trafficCondition = "Moderate";
                $trafficColor = "#f59e0b"; // Yellow
            } else {
                $trafficCondition = "Light";
                $trafficColor = "#10b981"; // Green
            }
        }
    }
}

// ---------------------------------------------------------
// 3. Fallback & Final ETA Calculation
// ---------------------------------------------------------
if (!$mapboxUsed) {
    // Fallback logic
    if ($fallbackSpeed < 5) $fallbackSpeed = 5;
    $timeHours = $fallbackDist / $fallbackSpeed;
    $baseEtaMins = $timeHours * 60;
    
    // Simulate traffic based on fallback speed
    if ($fallbackSpeed < 15) {
        $trafficCondition = "Heavy";
        $trafficColor = "#ef4444";
    } elseif ($fallbackSpeed < 30) {
        $trafficCondition = "Moderate";
        $trafficColor = "#f59e0b";
    }
}

// Combine Mapbox ETA + Weather Delay
$finalEtaMins = round($baseEtaMins + $weatherDelayMins);

// Output
echo json_encode([
    "eta_mins" => max(1, $finalEtaMins),
    "weather" => $weatherCondition,
    "weather_delay_mins" => $weatherDelayMins,
    "icon" => $icon,
    "traffic" => $trafficCondition,
    "traffic_icon" => "activity",
    "traffic_color" => $trafficColor,
    "ai_message" => $mapboxUsed ? "Live Mapbox traffic applied." : "Fallback ETA used.",
    "mapbox_used" => $mapboxUsed
]);
