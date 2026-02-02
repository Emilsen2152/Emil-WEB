<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ratelimit.php';

$pdo = db($config);

function current_user(PDO $pdo, array $config): ?array
{
    $cookieName = $config['cookie']['name'];
    $token = $_COOKIE[$cookieName] ?? null;

    if (!$token || !is_string($token) || strlen($token) !== 64) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE token = ? LIMIT 1');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    return $user ?: null;
}
