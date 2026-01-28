<?php
require_once __DIR__ . '/../api/bootstrap.php';

$user = require_auth($pdo, $config);

// If we get here, user IS logged in
echo 'Hello ' . htmlspecialchars($user['username']);
