<?php 
declare(strict_types=1);

require_once __DIR__ . '../../../../api/bootstrap.php';

// Takes in list object, item object, id of item or id of list and check is user has access to that list (owner or shared) and returns the list if access is granted, returns array including list and item if item object or id is given, otherwise just list. Returns false if access is denied or identifier is invalid.
function check_access_and_return(PDO $pdo, array $config, mixed $identifier, string $identifier_type): bool
{
    if ($identifier_type === 'list object') {
        $list = $identifier;
    } elseif ($identifier_type === 'item object') {
        $stmt = $pdo->prepare('SELECT * FROM to_do_lists WHERE id = ?');
        $stmt->execute([$identifier['to_do_list_id']]);
        $list = $stmt->fetch();
    } elseif ($identifier_type === 'item id') {
        $stmt = $pdo->prepare('SELECT * FROM to_do_items WHERE id = ?');
        $stmt->execute([$identifier]);
        $item = $stmt->fetch();
        if (!$item) return false;

        $stmt = $pdo->prepare('SELECT * FROM to_do_lists WHERE id = ?');
        $stmt->execute([$item['to_do_list_id']]);
        $list = $stmt->fetch();
    } elseif ($identifier_type === 'list id') {
        $stmt = $pdo->prepare('SELECT * FROM to_do_lists WHERE id = ?');
        $stmt->execute([$identifier]);
        $list = $stmt->fetch();
    } else {
        return false;
    }

    if (!$list) return false;

    if (!$list['private']) return $list;

    $user = current_user($pdo, $config);
    if (!$user) return false;    

    if ($list['owner_id'] === $user['id']) return $list;

    $stmt = $pdo->prepare('SELECT * FROM shared_to_do_lists WHERE to_do_list_id = ? AND user_id = ?');
    $stmt->execute([$list['id'], $user['id']]);
    if ($stmt->fetch()) return $list;

    return false;
}

