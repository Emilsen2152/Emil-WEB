<?php
// ========== BASIC AUTH ========== //
$username = 'arild';  // change this
$password = '1234'; // change this

if (
    !isset($_SERVER['PHP_AUTH_USER']) ||
    $_SERVER['PHP_AUTH_USER'] !== $username ||
    $_SERVER['PHP_AUTH_PW'] !== $password
) {
    header('WWW-Authenticate: Basic realm="Messages Viewer"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Access denied.';
    exit;
}

// ========== LOAD JSON DATA ========== //
$jsonFile = __DIR__ . '/message4805ujrfej0r98435s.json';

if (!file_exists($jsonFile)) {
    die("No messages file found.");
}

$messages = json_decode(file_get_contents($jsonFile), true);
if (!is_array($messages) || empty($messages)) {
    echo "<p>No messages to display.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Messages Viewer</title>
<style>
    body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
    .message { background: #fff; border-left: 4px solid #4CAF50; margin-bottom: 10px; padding: 10px; border-radius: 4px; }
    .from { font-weight: bold; color: #333; }
    .time { font-size: 0.8em; color: #666; }
</style>
</head>
<body>

<h2>Messages</h2>

<?php foreach (array_reverse($messages) as $msg): ?>
    <div class="message">
        <div class="from"><?= htmlspecialchars($msg['from'] ?? 'Unknown') ?></div>
        <div><?= nl2br(htmlspecialchars($msg['message'] ?? '')) ?></div>
        <?php if (!empty($msg['time'])): ?>
            <div class="time"><?= htmlspecialchars($msg['time']) ?></div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

</body>
</html>
