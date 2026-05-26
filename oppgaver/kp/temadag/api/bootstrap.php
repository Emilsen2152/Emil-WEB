<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';

require_once __DIR__ . '/../../../../api/db.php';

$pdo = db($config);

function create_response(
    bool $success,
    ?array $data = null,
    ?string $error = null,
    int $status = 200
): array {
    return [
        'success' => $success,
        'data'    => $data,
        'error'   => $error,
        'status'  => $status,
    ];
}

