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
        if (
            !is_array($identifier)
            || !isset($identifier['id'])
            || !array_key_exists('private', $identifier)
            || !array_key_exists('owner_id', $identifier)
        ) {
            return create_response(false, null, 'Invalid list object', 400);
        }

        $list = $identifier;
    } elseif ($identifier_type === 'item object') {
        if (!is_array($identifier) || !isset($identifier['id'], $identifier['to_do_list_id'])) {
            return create_response(false, null, 'Invalid item object', 400);
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
            return create_response(false, null, 'Item not found', 404);
        }

        $stmt = $pdo->prepare('SELECT * FROM to_do_lists WHERE id = ?');
        $stmt->execute([$item['to_do_list_id']]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($identifier_type === 'list id') {
        $stmt = $pdo->prepare('SELECT * FROM to_do_lists WHERE id = ?');
        $stmt->execute([$identifier]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        return create_response(false, null, 'Invalid identifier type', 400);
    }

    if (!$list) {
        return create_response(false, null, 'List not found', 404);
    }

    $payload = ['list' => $list];
    if ($item) $payload['item'] = $item;

    // Public list => allowed
    if (empty($list['private'])) {
        return create_response(true, $payload, null, 200);
    }

    // Private list => must be logged in
    $user = current_user($pdo, $config);
    if (!$user) {
        return create_response(false, null, 'Unauthorized', 401);
    }

    // Owner => allowed (owner_id might be null)
    if (!empty($list['owner_id']) && (int)$list['owner_id'] === (int)$user['id']) {
        return create_response(true, $payload, null, 200);
    }

    // Shared => allowed
    $stmt = $pdo->prepare(
        'SELECT 1 FROM shared_to_do_lists
         WHERE to_do_list_id = ? AND user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$list['id'], $user['id']]);

    if ($stmt->fetchColumn()) {
        return create_response(true, $payload, null, 200);
    }

    return create_response(false, null, 'Forbidden', 403);
}

function get_user_to_do_lists(PDO $pdo, array $config): array
{
    $user = current_user($pdo, $config);
    if (!$user) return create_response(true, ['lists' => []], null, 200);

    $stmt = $pdo->prepare(
        'SELECT DISTINCT l.*
         FROM to_do_lists l
         LEFT JOIN shared_to_do_lists s
           ON s.to_do_list_id = l.id AND s.user_id = ?
         WHERE l.owner_id = ? OR s.user_id IS NOT NULL'
    );
    $stmt->execute([$user['id'], $user['id']]);

    return create_response(true, [
        'lists' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ], null, 200);
}

function get_to_do_list_items(PDO $pdo, array $config, int $listId): array
{
    $access = check_access_and_return($pdo, $config, $listId, 'list id');
    if (!$access['success']) return $access;

    $stmt = $pdo->prepare('SELECT * FROM to_do_items WHERE to_do_list_id = ?');
    $stmt->execute([$listId]);

    return create_response(true, [
        'list' => $access['data']['list'],
        'items' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ], null, 200);
}

function get_to_do_list(PDO $pdo, array $config, int $listId): array
{
    $access = check_access_and_return($pdo, $config, $listId, 'list id');
    if (!$access['success']) return $access;

    return create_response(true, [
        'list' => $access['data']['list']
    ], null, 200);
}

function get_to_do_item(PDO $pdo, array $config, int $itemId): array
{
    $access = check_access_and_return($pdo, $config, $itemId, 'item id');
    if (!$access['success']) return $access;

    return create_response(true, [
        'list' => $access['data']['list'],
        'item' => $access['data']['item']
    ], null, 200);
}

function delete_to_do_item(PDO $pdo, array $config, int $itemId): array
{
    $access = check_access_and_return($pdo, $config, $itemId, 'item id');
    if (!$access['success']) return $access;

    $stmt = $pdo->prepare('DELETE FROM to_do_items WHERE id = ?');
    $ok = $stmt->execute([$itemId]);

    if (!$ok) return create_response(false, [], 'Delete failed', 500);
    return create_response(true, ['deleted' => true], null, 200);
}

function delete_to_do_list(PDO $pdo, array $config, int $listId): array
{
    $access = check_access_and_return($pdo, $config, $listId, 'list id');
    if (!$access['success']) return $access;

    $list = $access['data']['list'];

    $noOwner = empty($list['owner_id']);

    if ($noOwner) {
        if (!empty($list['private']))
            return create_response(false, [], 'Forbidden', 403);
    } else {
        $user = current_user($pdo, $config);

        if (!$user)
            return create_response(false, [], 'Unauthorized', 401);

        if ((int)$list['owner_id'] !== (int)$user['id'])
            return create_response(false, [], 'Forbidden', 403);
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

        return create_response(true, ['deleted' => true], null, 200);
    } catch (Throwable $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();

        return create_response(false, [], 'Delete failed', 500);
    }
}

function create_to_do_list(PDO $pdo, array $config, string $name, bool $private): array
{
    $user = current_user($pdo, $config);

    if ($private && !$user)
        return create_response(false, [], 'Guests cannot create private lists', 403);

    $stmt = $pdo->prepare(
        'INSERT INTO to_do_lists (name, owner_id, `private`) VALUES (?, ?, ?)'
    );

    if (!$stmt->execute([$name, $user ? $user['id'] : null, $private ? 1 : 0]))
        return create_response(false, [], 'Insert failed', 500);

    $listId = (int)$pdo->lastInsertId();

    // keep existing behavior, but mark as "created"
    $res = get_to_do_list($pdo, $config, $listId);
    $res['status'] = 201;
    return $res;
}

function create_to_do_item(PDO $pdo, array $config, int $listId, string $description): array
{
    $access = check_access_and_return($pdo, $config, $listId, 'list id');
    if (!$access['success']) return $access;

    $creator = current_user($pdo, $config);

    if ($creator) {
        $creator_id = $creator['id'];
    } else {
        $creator_id = null;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO to_do_items (to_do_list_id, creator, description, completed)
         VALUES (?, ?, ?, ?)'
    );

    if (!$stmt->execute([$listId, $creator_id, $description, 0]))
        return create_response(false, [], 'Insert failed', 500);

    $itemId = (int)$pdo->lastInsertId();

    $res = get_to_do_item($pdo, $config, $itemId);
    $res['status'] = 201;
    return $res;
}

function update_to_do_item(
    PDO $pdo,
    array $config,
    int $itemId,
    ?string $description,
    ?bool $completed
): array {
    $access = check_access_and_return($pdo, $config, $itemId, 'item id');
    if (!$access['success']) return $access;

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

    if (empty($fields))
        return get_to_do_item($pdo, $config, $itemId);

    $params[] = $itemId;

    $stmt = $pdo->prepare(
        'UPDATE to_do_items SET ' .
            implode(', ', $fields) .
            ' WHERE id = ?'
    );

    if (!$stmt->execute($params))
        return create_response(false, [], 'Update failed', 500);

    return get_to_do_item($pdo, $config, $itemId);
}

function share_to_do_list(
    PDO $pdo,
    array $config,
    int $listId,
    string $username
): array {
    $access = check_access_and_return($pdo, $config, $listId, 'list id');
    if (!$access['success']) return $access;

    $list = $access['data']['list'];

    if (empty($list['owner_id']))
        return create_response(false, [], 'Guest list cannot be shared', 409);

    $user = current_user($pdo, $config);

    if (!$user)
        return create_response(false, [], 'Unauthorized', 401);

    if ((int)$list['owner_id'] !== (int)$user['id'])
        return create_response(false, [], 'Forbidden', 403);

    $stmt = $pdo->prepare(
        'SELECT id FROM users WHERE username = ? LIMIT 1'
    );
    $stmt->execute([$username]);

    $shareUserId = $stmt->fetchColumn();

    if (!$shareUserId)
        return create_response(false, [], 'User not found', 404);

    $stmt = $pdo->prepare(
        'SELECT 1 FROM shared_to_do_lists
         WHERE to_do_list_id = ? AND user_id = ?
         LIMIT 1'
    );
    $stmt->execute([$listId, $shareUserId]);

    if ($stmt->fetchColumn())
        return create_response(true, ['already_shared' => true], null, 200);

    $stmt = $pdo->prepare(
        'INSERT INTO shared_to_do_lists (to_do_list_id, user_id)
         VALUES (?, ?)'
    );

    $ok = $stmt->execute([$listId, $shareUserId]);
    if (!$ok) return create_response(false, [], 'Insert failed', 500);

    return create_response(true, ['shared' => true], null, 201);
}

// NEXT: se og fjern delingar
