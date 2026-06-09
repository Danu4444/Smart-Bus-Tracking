<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/session.php';
require_role_html(['driver'], 'driverLogin.html');

$sess = [
    'username' => (string) ($_SESSION['driver_username'] ?? ''),
    'assigned_bus' => (string) ($_SESSION['driver_assigned_bus'] ?? ''),
];

$html = file_get_contents(__DIR__ . '/partials/driver_body.html');
$inject  = '<script>window.__DRIVER_SESSION__=' . json_encode($sess, JSON_UNESCAPED_UNICODE) . ';</script>';
if (!preg_match('/<body[^>]*>/', $html)) {
    echo $html;
    exit;
}
echo preg_replace('/<body[^>]*>/', '$0' . $inject, $html, 1);
