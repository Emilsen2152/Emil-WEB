<?php

declare(strict_types=1);

function client_ip(): string
{
    // local dev: REMOTE_ADDR is fine
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return is_string($ip) ? $ip : '0.0.0.0';
}

function rate_limit(PDO $pdo, string $action, int $maxHits, int $windowSeconds, array $errorPayload): void
{
    $ip = client_ip();

    $now = new DateTimeImmutable('now');
    $windowStart = $now->sub(new DateInterval('PT' . $windowSeconds . 'S'));

    // Fetch existing row
    $stmt = $pdo->prepare('SELECT * FROM rate_limits WHERE ip = ? AND action = ? LIMIT 1');
    $stmt->execute([$ip, $action]);
    $row = $stmt->fetch();

    if (!$row) {
        $ins = $pdo->prepare('INSERT INTO rate_limits (ip, action, window_start, hits) VALUES (?, ?, ?, 1)');
        $ins->execute([$ip, $action, $now->format('Y-m-d H:i:s')]);
        return;
    }

    $rowWindowStart = new DateTimeImmutable($row['window_start']);

    if ($rowWindowStart < $windowStart) {
        // Reset window
        $upd = $pdo->prepare('UPDATE rate_limits SET window_start = ?, hits = 1 WHERE id = ?');
        $upd->execute([$now->format('Y-m-d H:i:s'), $row['id']]);
        return;
    }

    $hits = (int)$row['hits'];
    if ($hits >= $maxHits) {
        json_response(429, $errorPayload);
    }

    $upd = $pdo->prepare('UPDATE rate_limits SET hits = hits + 1 WHERE id = ?');
    $upd->execute([$row['id']]);
}
