<?php

declare(strict_types=1);

function json_response(int $status, array $payload): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function set_auth_cookie(array $config, string $token): void
{
    $c = $config['cookie'];
    setcookie($c['name'], $token, [
        'expires'  => time() + (int)$c['maxAge'],
        'path'     => '/',
        'domain'   => $c['domain'],
        'secure'   => (bool)$c['secure'],
        'httponly' => true,
        'samesite' => (string)$c['samesite'],
    ]);
}

function clear_auth_cookie(array $config): void
{
    $c = $config['cookie'];
    setcookie($c['name'], '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'domain'   => $c['domain'],
        'secure'   => (bool)$c['secure'],
        'httponly' => true,
        'samesite' => (string)$c['samesite'],
    ]);
}

function random_token(): string
{
    return bin2hex(random_bytes(32)); // 64 hex chars
}

function format_user(array $row, bool $includeToken = false): array
{
    $permissions = [];
    if (isset($row['permissions'])) {
        $decoded = json_decode((string)$row['permissions'], true);
        if (is_array($decoded)) $permissions = $decoded;
    }

    $out = [
        'username'   => $row['username'],
        'permissions' => $permissions,
        'lastLogin'  => $row['last_login'] ?? null,
        'createdAt'  => $row['created_at'],
    ];

    if ($includeToken) $out['token'] = $row['token'];
    return $out;
}

function require_auth(PDO $pdo, array $config): array
{
    $cookieName = $config['cookie']['name'];
    $token = $_COOKIE[$cookieName] ?? null;

    if (!$token || !is_string($token) || strlen($token) !== 64) {
        json_response(401, ['error' => 'Missing token.']);
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE token = ? LIMIT 1');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(403, ['error' => 'Invalid or expired token.']);
    }

    return $user;
}

function require_admin(array $config, array $user): void
{
    $perms = json_decode((string)$user['permissions'], true);
    $perms = is_array($perms) ? $perms : [];

    $isAdmin = ($user['username'] === $config['admin_username']) || in_array('admin', $perms, true);

    if (!$isAdmin) {
        json_response(403, ['message' => 'Ingen tilgang. Admin-rettigheter kreves.']);
    }
}
