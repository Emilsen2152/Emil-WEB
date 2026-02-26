<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../../api/bootstrap.php';

function create_response(bool $success, ?array $data = null, ?string $error = null): array
{
    return [
        'success' => $success,
        'data'    => $data,
        'error'   => $error,
    ];
}

/**
 * SQL Structure:
 * to_do_lists: id, name, owner_id (nullable), private (bool)
 * to_do_items: id, to_do_list_id, description, completed (bool)
 * shared_to_do_lists: id, to_do_list_id, user_id
 *
 * Rules:
 * - Public lists: accessible by anyone
 * - Private lists: accessible only by owner or users it's shared with
 * - Guests (not logged in) CANNOT create private lists
 * - Delete:
 *   - If list has owner_id: only owner can delete list/items
 *   - If list has NO owner_id (guest public list): anyone can delete (but only if public)
 */

function check_access_and_return(PDO $pdo, array $config, mixed $identifier, string $identifier_type): array
{
    $list = null;
    $item = null;

    if ($identifier_type === 'list object') {
        // Validate required fields used later
        if (
            !is_array($identifier)
            || !isset($identifier['id'])
            || !array_key_exists('private', $identifier)
            || !array_key_exists('owner_id', $identifier)
        ) {
            return create_response(false, null, 'Invalid list object');
        }

        $list = $identifier;
    } elseif ($identifier_type === 'item object') {
        if (!is_array($identifier) || !isset($identifier['id'], $identifier['to_do_list_id'])) {
            return create_response(false, null, 'Invalid item object');
        }

        $item = $identifier;

        $stmt = $pdo->prepare('SELECT * FROM to_do_lists WHERE id = ?');
        $stmt->execute([$item['to_do_list_id']]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($identifier_type === 'item id') {
        $stmt = $pdo->prepare('SELECT * FROM to_do_items WHERE id = ?');
        $stmt->execute([$identifier]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            return create_response(false, null, 'Invalid item id');
        }

        $stmt = $pdo->prepare('SELECT * FROM to_do_lists WHERE id = ?');
        $stmt->execute([$item['to_do_list_id']]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($identifier_type === 'list id') {
        $stmt = $pdo->prepare('SELECT * FROM to_do_lists WHERE id = ?');
        $stmt->execute([$identifier]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        return create_response(false, null, 'Invalid identifier type');
    }

    if (!$list) {
        return create_response(false, null, 'List not found');
    }

    $payload = ['list' => $list];
    if ($item) $payload['item'] = $item;

    // Public list => allowed
    if (empty($list['private'])) {
        return create_response(true, $payload);
    }

    // Private list => must be logged in
    $user = current_user($pdo, $config);
    if (!$user) {
        return create_response(false, null, 'Unauthorized');
    }

    // Owner => allowed (owner_id might be null)
    if (!empty($list['owner_id']) && (int)$list['owner_id'] === (int)$user['id']) {
        return create_response(true, $payload);
    }

    // Shared => allowed
    $stmt = $pdo->prepare(
        'SELECT 1 FROM shared_to_do_lists
         WHERE to_do_list_id = ? AND user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$list['id'], $user['id']]);

    if ($stmt->fetchColumn()) {
        return create_response(true, $payload);
    }

    return create_response(false, null, 'Forbidden');
}

function get_user_to_do_lists(PDO $pdo, array $config): array
{
    $user = current_user($pdo, $config);
    if (!$user) return [];

    $stmt = $pdo->prepare(
        'SELECT DISTINCT l.*
         FROM to_do_lists l
         LEFT JOIN shared_to_do_lists s
           ON s.to_do_list_id = l.id AND s.user_id = ?
         WHERE l.owner_id = ? OR s.user_id IS NOT NULL'
    );
    $stmt->execute([$user['id'], $user['id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_to_do_list_items(PDO $pdo, array $config, int $listId): ?array
{
    $access = check_access_and_return($pdo, $config, $listId, 'list id');
    if (!$access['success']) return null;

    $stmt = $pdo->prepare('SELECT * FROM to_do_items WHERE to_do_list_id = ?');
    $stmt->execute([$listId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_to_do_list(PDO $pdo, array $config, int $listId): ?array
{
    $access = check_access_and_return($pdo, $config, $listId, 'list id');
    if (!$access['success']) return null;

    return $access['data']['list'];
}

function get_to_do_item(PDO $pdo, array $config, int $itemId): ?array
{
    $access = check_access_and_return($pdo, $config, $itemId, 'item id');
    if (!$access['success']) return null;

    return $access['data']['item'];
}

function delete_to_do_item(PDO $pdo, array $config, int $itemId): bool
{
    $access = check_access_and_return($pdo, $config, $itemId, 'item id');
    if (!$access['success']) return false;

    $list = $access['data']['list'];

    // No-owner lists: allow everyone ONLY if public
    if (empty($list['owner_id'])) {
        if (!empty($list['private'])) return false;
        $stmt = $pdo->prepare('DELETE FROM to_do_items WHERE id = ?');
        return (bool)$stmt->execute([$itemId]);
    }

    // Owner-only otherwise
    $user = current_user($pdo, $config);
    if (!$user) return false;
    if ((int)$list['owner_id'] !== (int)$user['id']) return false;

    $stmt = $pdo->prepare('DELETE FROM to_do_items WHERE id = ?');
    return (bool)$stmt->execute([$itemId]);
}

function delete_to_do_list(PDO $pdo, array $config, int $listId): bool
{
    $access = check_access_and_return($pdo, $config, $listId, 'list id');
    if (!$access['success']) return false;

    $list = $access['data']['list'];

    // No-owner lists: allow everyone ONLY if public
    $noOwner = empty($list['owner_id']);
    if ($noOwner) {
        if (!empty($list['private'])) return false;
    } else {
        // Owner-only otherwise
        $user = current_user($pdo, $config);
        if (!$user) return false;
        if ((int)$list['owner_id'] !== (int)$user['id']) return false;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('DELETE FROM to_do_items WHERE to_do_list_id = ?');
        $stmt->execute([$listId]);

        $stmt = $pdo->prepare('DELETE FROM shared_to_do_lists WHERE to_do_list_id = ?');
        $stmt->execute([$listId]);

        $stmt = $pdo->prepare('DELETE FROM to_do_lists WHERE id = ?');
        $stmt->execute([$listId]);

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

function create_to_do_list(PDO $pdo, array $config, string $name, bool $private): ?array
{
    $user = current_user($pdo, $config);

    // Guests cannot create private lists
    if ($private && !$user) {
        return null;
    }

    $stmt = $pdo->prepare('INSERT INTO to_do_lists (name, owner_id, `private`) VALUES (?, ?, ?)');
    $stmt->execute([$name, $user ? $user['id'] : null, $private ? 1 : 0]);

    $listId = (int)$pdo->lastInsertId();
    return get_to_do_list($pdo, $config, $listId);
}

function create_to_do_item(PDO $pdo, array $config, int $listId, string $description): ?array
{
    $access = check_access_and_return($pdo, $config, $listId, 'list id');
    if (!$access['success']) return null;

    $stmt = $pdo->prepare('INSERT INTO to_do_items (to_do_list_id, description, completed) VALUES (?, ?, ?)');
    $stmt->execute([$listId, $description, 0]);

    $itemId = (int)$pdo->lastInsertId();
    return get_to_do_item($pdo, $config, $itemId);
}

function update_to_do_item(PDO $pdo, array $config, int $itemId, ?string $description, ?bool $completed): ?array
{
    $access = check_access_and_return($pdo, $config, $itemId, 'item id');
    if (!$access['success']) return null;

    $fields = [];
    $params = [];

    if ($description !== null) {
        $fields[] = 'description = ?';
        $params[] = $description;
    }

    if ($completed !== null) {
        $fields[] = 'completed = ?';
        $params[] = $completed ? 1 : 0;
    }

    if (empty($fields)) {
        return get_to_do_item($pdo, $config, $itemId);
    }

    $params[] = $itemId;

    $stmt = $pdo->prepare('UPDATE to_do_items SET ' . implode(', ', $fields) . ' WHERE id = ?');
    if (!$stmt->execute($params)) return null;

    return get_to_do_item($pdo, $config, $itemId);
}

function share_to_do_list(PDO $pdo, array $config, int $listId, string $username): bool
{
    $access = check_access_and_return($pdo, $config, $listId, 'list id');
    if (!$access['success']) return false;

    $list = $access['data']['list'];

    // Only owner can share, and list must have an owner
    if (empty($list['owner_id'])) return false;

    $user = current_user($pdo, $config);
    if (!$user) return false;
    if ((int)$list['owner_id'] !== (int)$user['id']) return false;

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $shareUserId = $stmt->fetchColumn();
    if (!$shareUserId) return false;

    $stmt = $pdo->prepare('SELECT 1 FROM shared_to_do_lists WHERE to_do_list_id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$listId, $shareUserId]);
    if ($stmt->fetchColumn()) return true;

    $stmt = $pdo->prepare('INSERT INTO shared_to_do_lists (to_do_list_id, user_id) VALUES (?, ?)');
    return (bool)$stmt->execute([$listId, $shareUserId]);
}
