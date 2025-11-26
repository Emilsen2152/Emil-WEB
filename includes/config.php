<?php
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // Local
    $BASE_URL = '/';
} else {
    // Production — extract first folder name
    $parts = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
    $folder = $parts[0] ?? '';

    $BASE_URL = '/' . $folder . '/';
}

function url($path) {
    global $BASE_URL;
    return $BASE_URL . ltrim($path, '/');
}
