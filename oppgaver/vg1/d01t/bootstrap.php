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

function create_to_do_list(PDO $pdo, array $config, string $name, bool $private): ?array
{
    $user = current_user($pdo, $config);
    if (!$user) return null;

    $stmt = $pdo->prepare('INSERT INTO to_do_lists (name, owner_id, private) VALUES (?, ?, ?)');
    $stmt->execute([$name, $user['id'], $private]);
    return ['id' => (int)$pdo->lastInsertId(), 'name' => $name, 'private' => $private];
}

function add_item_to_list(PDO $pdo, int $to_do_list_id, string $description): ?array
{
    $stmt = $pdo->prepare('INSERT INTO to_do_items (to_do_list_id, description) VALUES (?, ?)');
    $stmt->execute([$to_do_list_id, $description]);
    return ['id' => (int)$pdo->lastInsertId(), 'description' => $description];
}

function share_to_do_list(PDO $pdo, int $to_do_list_id, int $user_id): bool
{
    $stmt = $pdo->prepare('INSERT INTO shared_to_do_lists (to_do_list_id, user_id) VALUES (?, ?)');
    return $stmt->execute([$to_do_list_id, $user_id]);
}

function unshare_to_do_list(PDO $pdo, int $to_do_list_id, int $user_id): bool
{
    $stmt = $pdo->prepare('DELETE FROM shared_to_do_lists WHERE to_do_list_id = ? AND user_id = ?');
    return $stmt->execute([$to_do_list_id, $user_id]);
}

function delete_to_do_list(PDO $pdo, int $to_do_list_id): bool
{
    $stmt = $pdo->prepare('DELETE FROM to_do_lists WHERE id = ?');
    return $stmt->execute([$to_do_list_id]);
}

function delete_to_do_item(PDO $pdo, int $item_id): bool
{
    $stmt = $pdo->prepare('DELETE FROM to_do_items WHERE id = ?');
    return $stmt->execute([$item_id]);
}

function toggle_item_completion(PDO $pdo, int $item_id, bool $completed): bool
{
    $stmt = $pdo->prepare('UPDATE to_do_items SET completed = ? WHERE id = ?');
    return $stmt->execute([$completed, $item_id]);
}
