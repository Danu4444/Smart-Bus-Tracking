<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/session.php';

app_session_start();
session_destroy_full();

header('Location: index.html');
exit;
