<?php 
declare(strict_types=1);

require_once __DIR__ . '../../../../api/bootstrap.php';

function get_user_to_do_lists(PDO $pdo, array $config): array
{
    $user = current_user($pdo, $config);
    if (!$user) {
        return [];
    }

    $stmt = $pdo->prepare('SELECT * FROM to_do_lists WHERE owner_id = ?');
    $stmt->execute([$user['id']]);
    return $stmt->fetchAll();
}

function get_shared_to_do_lists(PDO $pdo, array $config): array
{
    $user = current_user($pdo, $config);
    if (!$user) return [];

    $stmt = $pdo->prepare('
        SELECT l.*
        FROM to_do_lists l
        INNER JOIN shared_to_do_lists s ON s.to_do_list_id = l.id
        WHERE s.user_id = ?
    ');
    $stmt->execute([$user['id']]);
    return $stmt->fetchAll();
}

function get_to_do_list_items(PDO $pdo, int $to_do_list_id): array
{
    $stmt = $pdo->prepare('SELECT * FROM to_do_items WHERE to_do_list_id = ?');
    $stmt->execute([$to_do_list_id]);
    return $stmt->fetchAll();
}

function create_to_do_list(PDO $pdo, array $config, string $name): ?array
{
    $user = current_user($pdo, $config);
    if (!$user) return null;

    $stmt = $pdo->prepare('INSERT INTO to_do_lists (name, owner_id) VALUES (?, ?)');
    $stmt->execute([$name, $user['id']]);
    return ['id' => (int)$pdo->lastInsertId(), 'name' => $name];
}
