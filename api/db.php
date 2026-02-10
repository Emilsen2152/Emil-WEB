<?php
declare(strict_types=1);

function db(array $config): PDO
{
    $db = $config['db'];

    $host = (string)$db['host'];
    $port = (int)$db['port'];
    $name = (string)$db['name'];
    $charset = (string)($db['charset'] ?? 'utf8mb4');

    // Railway proxy can be picky; keep it simple and explicit.
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,

        // If the proxy is slow/unreachable, fail fast instead of hanging forever
        PDO::ATTR_TIMEOUT            => 5,

        // Prevent "gone away" from long idle sessions; also makes sessions predictable
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}, SESSION wait_timeout=60, SESSION interactive_timeout=60",

        // Recommended: avoid buffered queries if you sometimes fetch large sets
        // (optional, but can reduce memory and weirdness)
        // PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
    ];

    // One retry helps with transient proxy drops (common on Railway).
    // Keep it limited: you do NOT want infinite reconnect loops.
    try {
        return new PDO($dsn, (string)$db['user'], (string)$db['pass'], $options);
    } catch (PDOException $e) {
        // Retry only for typical disconnect-ish cases
        $msg = $e->getMessage();
        $code = (int)$e->getCode();

        $disconnectLikely =
            str_contains($msg, 'server has gone away') ||
            str_contains($msg, 'Lost connection') ||
            str_contains($msg, 'Connection refused') ||
            str_contains($msg, 'timed out') ||
            $code === 2006;

        if (!$disconnectLikely) {
            throw $e;
        }

        usleep(200_000); // 200ms
        return new PDO($dsn, (string)$db['user'], (string)$db['pass'], $options);
    }
}
