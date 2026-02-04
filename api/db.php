<?php

declare(strict_types=1);

function db(array $config): PDO
{
    $db = $config['db'];

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s;connect_timeout=5',
        $db['host'],
        (int)$db['port'],
        $db['name'],
        $db['charset'] ?? 'utf8mb4'
    );


    return new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ]);
}
