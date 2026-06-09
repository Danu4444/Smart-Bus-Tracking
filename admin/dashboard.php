<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/session.php';
require_role_html(['admin'], 'adminLogin.html');

readfile(__DIR__ . '/partials/dashboard_body.html');
