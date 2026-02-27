<?php

// api/index.php
declare(strict_types=1);

require_once __DIR__ . '/../../../../../api/bootstrap.php';
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
    $uriPath = str_replace('/d01t/', '/', $uriPath);
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

// DO NOT ADD USER AUTHENTICATION RELATED ROUTES HERE, THEY ARE IN A SEPERATE API AREA

if ($m === 'GET' && $p === '/to-do-lists') {
    respond(get_user_to_do_lists($pdo, $config));
}

$params = [];
if ($m === 'GET' && match_route('/to-do-lists/{listId}/items', $p, $params)) {
    $listId = (int)($params['listId'] ?? 0);
    if ($listId <= 0) {
        respond(create_response(false, null, 'Invalid list id', 400));
    }
    respond(get_to_do_list_items($pdo, $config, $listId));
}

$params = [];
if ($m === 'GET' && match_route('/to-do-lists/{listId}', $p, $params)) {
    $listId = (int)($params['listId'] ?? 0);
    if ($listId <= 0) {
        respond(create_response(false, null, 'Invalid list id', 400));
    }
    respond(get_to_do_list($pdo, $config, $listId));
}

$params = [];
if ($m === 'GET' && match_route('/to-do-items/{itemId}', $p, $params)) {
    $itemId = (int)($params['itemId'] ?? 0);
    if ($itemId <= 0) {
        respond(create_response(false, null, 'Invalid item id', 400));
    }
    respond(get_to_do_item($pdo, $config, $itemId));
}

$params = [];
if ($m === 'DELETE' && match_route('/to-do-items/{itemId}', $p, $params)) {
    $itemId = (int)($params['itemId'] ?? 0);
    if ($itemId <= 0) {
        respond(create_response(false, null, 'Invalid item id', 400));
    }
    respond(delete_to_do_item($pdo, $config, $itemId));
}

$params = [];
if ($m === 'DELETE' && match_route('/to-do-lists/{listId}', $p, $params)) {
    $listId = (int)($params['listId'] ?? 0);
    if ($listId <= 0) {
        respond(create_response(false, null, 'Invalid list id', 400));
    }
    respond(delete_to_do_list($pdo, $config, $listId));
}

$params = [];
if ($m === 'CREATE' && match_route('/to-do-lists', $p, $params)) {
    $body = read_json_body();

    $name = trim((string)($body['name'] ?? ''));
    if ($name === '') {
        respond(create_response(false, null, 'Name is required', 400));
    }

    $private = (bool)($body['private'] ?? false);
    respond(create_to_do_list($pdo, $config, $name, $private));
}

$params = [];
if ($m === 'CREATE' && match_route('/to-do-lists/{listId}/items', $p, $params)) {
    $listId = (int)($params['listId'] ?? 0);
    if ($listId <= 0) {
        respond(create_response(false, null, 'Invalid list id', 400));
    }

    $body = read_json_body();

    $description = trim((string)($body['description'] ?? ''));
    if ($description === '') {
        respond(create_response(false, null, 'Description is required', 400));
    }

    respond(create_to_do_item($pdo, $config, $listId, $description));
}


/* ============================================================
   Fallback
   ============================================================ */

json_response(404, create_response(false, null, 'Not found: ' . $m . ' ' . $p, 404));
