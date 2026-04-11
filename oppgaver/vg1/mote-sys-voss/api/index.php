<?php

// api/index.php
declare(strict_types=1);

require_once __DIR__ . '/../../../../api/bootstrap.php';
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

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
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

    // Strip your folder prefixes (adjust if you move folders)
    $uriPath = str_replace('/emil/', '/', $uriPath);
    $uriPath = str_replace('/oppgaver/', '/', $uriPath);
    $uriPath = str_replace('/vg1/', '/', $uriPath);
    $uriPath = str_replace('/mote-sys-voss/', '/', $uriPath);
    $uriPath = str_replace('/api/', '/', $uriPath);

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
    $params = []; // important: reset each call
    $regex = '#^' . preg_replace(
        '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
        '(?P<$1>[^/]+)',
        $pattern
    ) . '$#';

    if (!preg_match($regex, $actualPath, $m)) return false;

    foreach ($m as $k => $v) {
        if (is_string($k)) $params[$k] = $v;
    }
    return true;
}

/**
 * IMPORTANT CHANGE:
 * Use the status code returned by your to-do bootstrap.php functions.
 * They now return ['status' => <int>, ...]
 */
function respond(array $res): void
{
    json_response((int)($res['status'] ?? 200), $res);
}

/* ============================================================
   Routes
   ============================================================ */

$m = method();
$p = path();

if ($m === 'GET' && $p === '/data') {
    respond(get_all_rows($pdo));
    exit;
}

$params = [];
if ($m === 'GET' && match_route('/data/{name}', $p, $params)) {
    respond(get_row($pdo, $params['name']));
    exit;
}

$params = [];
if ($m === 'PATCH' && match_route('/data/{name}', $p, $params)) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input) || !isset($input['value'])) {
        json_response(400, create_response(false, null, 'Invalid input: missing "value" field', 400));
        exit;
    }
    respond(update_row($pdo, $params['name'], $input['value']));
    exit;
}

/* ============================================================
   Fallback
   ============================================================ */

json_response(404, create_response(false, null, 'Not found: ' . $m . ' ' . $p, 404));
