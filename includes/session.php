<?php
/**
 * Shared session bootstrap: idle timeout, role checks, secure cookie defaults.
 * Include from project root via: dirname(__DIR__) . '/includes/session.php' (from api/)
 * or __DIR__ . '/../includes/session.php' (from admin/, driver/, app/)
 */
declare(strict_types=1);

const SESSION_IDLE_SECONDS = 900; // 15 minutes

function app_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function session_touch_activity(): void
{
    $_SESSION['last_activity'] = time();
}

function session_idle_expired(): bool
{
    if (!isset($_SESSION['last_activity'])) {
        return true;
    }
    return (time() - (int) $_SESSION['last_activity']) > SESSION_IDLE_SECONDS;
}

function session_destroy_full(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * CORS for browser fetch() with credentials (same-site / cookies).
 */
function api_send_cors_headers(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin !== '') {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    } else {
        header('Access-Control-Allow-Origin: *');
    }
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

function api_handle_options_preflight(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * JSON API: require one of the given roles; responds 401/403 JSON on failure.
 */
function require_role_json(array $allowed_roles): void
{
    app_session_start();

    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
    if (session_idle_expired()) {
        session_destroy_full();
        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => 'Session expired']);
        exit;
    }
    if (!in_array($_SESSION['role'], $allowed_roles, true)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    session_touch_activity();
}

/**
 * JSON API: require any one role from the list.
 */
function require_any_role_json(array $allowed_roles): void
{
    require_role_json($allowed_roles);
}

/**
 * HTML pages: redirect to login if not authenticated or wrong role.
 * $loginRelativeUrl is relative to the current request (e.g. adminLogin.html).
 */
function require_role_html(array $allowed_roles, string $loginRelativeUrl): void
{
    app_session_start();

    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        header('Location: ' . $loginRelativeUrl);
        exit;
    }
    if (session_idle_expired()) {
        session_destroy_full();
        header('Location: ' . $loginRelativeUrl);
        exit;
    }
    if (!in_array($_SESSION['role'], $allowed_roles, true)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
    session_touch_activity();
}

/**
 * Passenger APIs: session must match phone sent in request body/query.
 */
function require_passenger_phone_matches(string $phone): void
{
    $sessionPhone = $_SESSION['passenger_phone'] ?? '';
    if ($sessionPhone === '' || $phone !== $sessionPhone) {
        http_response_code(403);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}

/**
 * Driver APIs: JSON body driver_username must match logged-in driver.
 */
function require_driver_username_matches(string $username): void
{
    $sessionUser = $_SESSION['driver_username'] ?? '';
    if ($sessionUser === '' || $username !== $sessionUser) {
        http_response_code(403);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
}
