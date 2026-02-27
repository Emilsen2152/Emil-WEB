<?php

declare(strict_types=1);

/**
 * index.php — PHP + MySQL Account API (rewritten)
 * - Register, login, logout, get user, change password
 * - Admin: list users, delete user, patch permissions
 * - Uses: config.php, db.php, auth.php, ratelimit.php
 */

$config = require __DIR__ . '/config.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/ratelimit.php';

$pdo = db($config);

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
    $regex = '#^' . preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
    if (!preg_match($regex, $actualPath, $m)) return false;

    foreach ($m as $k => $v) {
        if (is_string($k)) $params[$k] = $v;
    }
    return true;
}

/**
 * Convenience: ensure a value is a non-empty string.
 */
function require_string(mixed $value): ?string
{
    if (!is_string($value)) return null;
    $value = trim($value);
    return $value === '' ? null : $value;
}

/* ============================================================
   Routes
   ============================================================ */

$m = method();

// Remove /emil and /api prefixes if present, for cleaner route handling

$p = path();

/* -----------------------------
   POST /users  (register)
   Body: { "username": "", "password": "" }
   ----------------------------- */
if ($m === 'POST' && $p === '/users') {
    $body = read_json_body();

    $username = require_string($body['username'] ?? null);
    $password = require_string($body['password'] ?? null);

    if (!$username || !$password) {
        json_response(400, ['message' => 'Brukarnamn og passord er påkrevd.']);
    }

    $username = strtolower($username);
    if (str_contains($username, ' ')) {
        json_response(400, ['message' => 'Brukarnamn kan ikkje innehalde mellomrom.']);
    }

    // (Optional) Basic username constraints
    if (!preg_match('/^[a-z0-9._-]{3,32}$/', $username)) {
        json_response(400, ['message' => 'Ugyldig brukarnamn. Bruk 3-32 teikn: a-z, 0-9, . _ -']);
    }

    // Check existing
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        json_response(409, ['message' => 'Brukarnamn eksisterer allereie.']);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $token = random_token();

    $ins = $pdo->prepare('
        INSERT INTO users (username, password_hash, token, permissions)
        VALUES (?, ?, ?, JSON_ARRAY())
    ');
    $ins->execute([$username, $hash, $token]);

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    set_auth_cookie($config, $token);

    json_response(201, [
        'message' => 'Brukar oppretta.',
        'user' => format_user($user, true),
    ]);
}

/* -----------------------------
   POST /login
   Body: { "username": "", "password": "" }
   Rate limit: 5/min per IP
   ----------------------------- */
if ($m === 'POST' && $p === '/login') {
    rate_limit(
        $pdo,
        'login',
        5,
        60,
        ['message' => 'For mange innloggingsforsøk frå denne IP-adressa. Prøv igjen om eit minutt.']
    );

    $body = read_json_body();

    $username = require_string($body['username'] ?? null);
    $password = require_string($body['password'] ?? null);

    if (!$username || !$password) {
        json_response(400, ['message' => 'Brukarnamn og passord er påkrevd.']);
    }

    $username = strtolower($username);

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_response(401, ['message' => 'Ugyldig brukarnamn eller passord.']);
    }

    $upd = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
    $upd->execute([$user['id']]);

    // Refetch for consistent output
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$user['id']]);
    $user = $stmt->fetch();

    set_auth_cookie($config, (string)$user['token']);

    json_response(200, [
        'message' => 'Innlogging vellukka.',
        'user' => format_user($user, true),
    ]);
}

/* -----------------------------
   POST /logout (requires auth)
   ----------------------------- */
if ($m === 'POST' && $p === '/logout') {
    require_auth($pdo, $config);
    clear_auth_cookie($config);
    json_response(200, ['message' => 'Logged out.']);
}

/* -----------------------------
   GET /user (requires auth)
   ----------------------------- */
if ($m === 'GET' && $p === '/user') {
    $user = require_auth($pdo, $config);

    json_response(200, [
        'message' => 'Brukarinfo henta.',
        'user' => format_user($user, false),
    ]);
}

/* -----------------------------
   PUT /user/password (requires auth)
   Body: { "currentPassword": "", "newPassword": "" }
   Rotates token + cookie
   ----------------------------- */
if ($m === 'PUT' && $p === '/user/password') {
    $user = require_auth($pdo, $config);
    $body = read_json_body();

    $currentPassword = require_string($body['currentPassword'] ?? null);
    $newPassword = require_string($body['newPassword'] ?? null);

    if (!$currentPassword || !$newPassword) {
        json_response(400, ['message' => 'Gammelt passord og nytt passord er påkrevd.']);
    }

    if (!password_verify($currentPassword, $user['password_hash'])) {
        json_response(401, ['message' => 'Ugyldig gammalt passord.']);
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $newToken = random_token();

    $upd = $pdo->prepare('UPDATE users SET password_hash = ?, token = ? WHERE id = ?');
    $upd->execute([$newHash, $newToken, $user['id']]);

    set_auth_cookie($config, $newToken);

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$user['id']]);
    $fresh = $stmt->fetch();

    json_response(200, [
        'message' => 'Passord endra.',
        'user' => format_user($fresh, true),
    ]);
}

/* -----------------------------
   GET /users/{id} (gives username)
   ----------------------------- */

$params = [];
if ($m === 'GET' && match_route('/users/{id}', $p, $params)) {
    $stmt = $pdo->prepare('SELECT username FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$params['id']]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(404, ['message' => 'Bruker ikkje funnen.']);
    }

    json_response(200, [
        'message' => 'Fant brukar.',
        'username' => $user['username'],
    ]);
}

/* ============================================================
   Admin endpoints
   ============================================================ */

/* -----------------------------
   GET /users (requires admin)
   ----------------------------- */
if ($m === 'GET' && $p === '/users') {
    $admin = require_auth($pdo, $config);
    require_admin($config, $admin);

    $stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
    $rows = $stmt->fetchAll();

    json_response(200, [
        'message' => 'Fant ' . count($rows) . ' brukere.',
        'users' => array_map(fn($u) => format_user($u, false), $rows),
    ]);
}

/* -----------------------------
   DELETE /users/{username} (requires admin)
   ----------------------------- */
$params = [];
if ($m === 'DELETE' && match_route('/users/{username}', $p, $params)) {
    $admin = require_auth($pdo, $config);
    require_admin($config, $admin);

    $username = strtolower($params['username']);

    if ($username === $config['admin_username']) {
        json_response(400, ['message' => 'Admin-brukeren kan ikkje slettast.']);
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $u = $stmt->fetch();

    if (!$u) {
        json_response(404, ['message' => 'Bruker ikkje funnen.']);
    }

    $del = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $del->execute([$u['id']]);

    json_response(200, [
        'message' => 'Bruker "' . $username . '" er sletta.',
        'deleted' => format_user($u, false),
    ]);
}

/* -----------------------------
   PATCH /users/{username} (requires admin)
   Body: { "permissions": ["admin","pingpanik"] }
   ----------------------------- */
$params = [];
if ($m === 'PATCH' && match_route('/users/{username}', $p, $params)) {
    $admin = require_auth($pdo, $config);
    require_admin($config, $admin);

    $body = read_json_body();
    $permissions = $body['permissions'] ?? null;

    if (!is_array($permissions)) {
        json_response(400, ['message' => 'Permissions må vere ei liste (array).']);
    }

    // Keep only non-empty strings
    $permissions = array_values(array_filter(
        $permissions,
        fn($p) => is_string($p) && trim($p) !== ''
    ));

    $username = strtolower($params['username']);

    if ($username === $config['admin_username'] && !in_array('admin', $permissions, true)) {
        json_response(403, ['message' => 'Kan ikkje fjerne admin frå admin.']);
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $u = $stmt->fetch();

    if (!$u) {
        json_response(404, ['message' => 'Bruker ikkje funnen.']);
    }

    $upd = $pdo->prepare('UPDATE users SET permissions = ? WHERE id = ?');
    $upd->execute([json_encode($permissions, JSON_UNESCAPED_UNICODE), $u['id']]);

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$u['id']]);
    $fresh = $stmt->fetch();

    json_response(200, [
        'message' => 'Oppdaterte rettigheter for "' . $username . '".',
        'user' => format_user($fresh, false),
    ]);
}

/* ============================================================
   Fallback
   ============================================================ */

json_response(404, ['message' => 'Not found: ' . $m . ' ' . $p]);
