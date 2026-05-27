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

function current_temadag_organizer(PDO $pdo, array $config): ?array
{
    $cookieName = $config['cookie']['name'];
    $token = $_COOKIE[$cookieName] ?? null;

    if (!$token || !is_string($token) || strlen($token) !== 64) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT * FROM organizers WHERE token = ? LIMIT 1');
    $stmt->execute([$token]);
    $organizer = $stmt->fetch();

    return $organizer ?: null;
}

function check_super_user(PDO $pdo, array $config): bool
{
    $organizer = current_temadag_organizer($pdo, $config);
    if (!$organizer) return false;

    return $organizer['superuser'] == 1;
}

function get_admin_activities(PDO $pdo, array $config): array
{
    $organizer = current_temadag_organizer($pdo, $config);

    if (!$organizer) {
        // Offentleg visning for påmeldingsskjemaet til elevane
        $stmt = $pdo->query('SELECT activity_id, name, description, start_time, end_time, max_participants FROM activities');
        $activities = $stmt->fetchAll();
    } else {
        // Administrasjonssida for lærarar
        if (!empty($organizer['super_user'])) {
            $stmt = $pdo->query('SELECT * FROM activities');
        } else {
            $stmt = $pdo->prepare('SELECT * FROM activities WHERE organizer_id = ?');
            $stmt->execute([$organizer['organizer_id']]);
        }
        $activities = $stmt->fetchAll();
    }

    return create_response(true, ['activities' => $activities]);
}

function get_activities(PDO $pdo, array $config): array
{
    $stmt = $pdo->query('SELECT activity_id, name, description, start_time, end_time, max_participants FROM activities');
    $activities = $stmt->fetchAll();

    return create_response(true, ['activities' => $activities]);
}
