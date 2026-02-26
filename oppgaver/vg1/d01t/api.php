<?php

require_once __DIR__ . '../../../../api/bootstrap.php';
require_once __DIR__ . '/bootstrap.php';

/* ============================================================
   Basic headers (CORS + JSON)
   ============================================================ */

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowOrigin = $origin !== '' ? $origin : '*';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . $allowOrigin);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,OPTIONS');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/* ============================================================
   Utilities
   ============================================================ */

function method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

/**
 * Supports both:
 *  - /login (recommended with rewrite)
 *  - /index.php/login (no rewrite)
 */
function path(): string
{
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $uriPath = preg_replace('#/+#', '/', $uriPath);

    // API lives under /api -> strip that prefix
    //if ($uriPath === '/api') return '/';
    $uriPath = str_replace('/emil/', '/', $uriPath);
    $uriPath = str_replace('/oppgaver/', '/', $uriPath);
    $uriPath = str_replace('/vg1/', '/', $uriPath);
    $uriPath = str_replace('/d01t/', '/', $uriPath);


    // Support "/index.php/..." style too
    if (str_starts_with($uriPath, '/index.php')) {
        $uriPath = substr($uriPath, strlen('/index.php'));
        if ($uriPath === '') $uriPath = '/';
    }

    if ($uriPath === '' || $uriPath[0] !== '/') $uriPath = '/' . $uriPath;
    return $uriPath;
}

/**
 * Match route patterns like "/users/{username}".
 * Fills $params with named groups.
 */
function match_route(string $pattern, string $actualPath, array &$params): bool
{
    $regex = '#^' . preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
    if (!preg_match($regex, $actualPath, $m)) return false;

    foreach ($m as $k => $v) {
        if (is_string($k)) $params[$k] = $v;
    }
    return true;
}

/* ============================================================
   Routes
   ============================================================ */

$m = method();

// Remove /emil and /api prefixes if present, for cleaner route handling

$p = path();

