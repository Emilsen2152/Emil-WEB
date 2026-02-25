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
        INNER JOIN shared_to_do_lists s ON s.table_id = l.id
        WHERE s.user_id = ?
    ');
    $stmt->execute([$user['id']]);
    return $stmt->fetchAll();
}
