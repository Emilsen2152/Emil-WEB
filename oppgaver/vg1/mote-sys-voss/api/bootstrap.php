<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../api/bootstrap.php';

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

function get_all_rows(PDO $pdo): array {
    $stmt = $pdo->prepare("SELECT * FROM hs_meeting");
    $stmt->execute();
    
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$data) {
        return create_response(false, null, 'No data found', 404);
    }

    return create_response(true, $data);
}

function get_row(PDO $pdo, string $name): array {
    $stmt = $pdo->prepare("SELECT * FROM hs_meeting WHERE data_name = :name");
    $stmt->execute(['name' => $name]);
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        return create_response(false, null, 'No data found for name: ' . $name, 404);
    }

    return create_response(true, $data);
}

function update_row(PDO $pdo, string $name, string $value): array {
    $stmt = $pdo->prepare("UPDATE hs_meeting SET data_value = :value WHERE data_name = :name");
    $stmt->execute(['name' => $name, 'value' => $value]);

    if ($stmt->rowCount() === 0) {
        return create_response(false, null, 'No row updated. Check if name exists: ' . $name, 404);
    }

    return create_response(true, null);
}