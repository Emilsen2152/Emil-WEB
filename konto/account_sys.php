<?php

$API_BASE = 'https://emil.elevweb.no';

/**
 * Make a simple API request with auth token
 *
 * @param string $path   API path, e.g., '/user'
 * @param string $method HTTP method, default GET
 * @param array|null $body Optional body for POST/PATCH
 * @return array|null    Decoded JSON response, or null on error
 */
function apiRequest(string $path, string $method = 'GET', ?array $body = null): ?array
{
    global $API_BASE;

    $url = rtrim($API_BASE, '/') . '/' . ltrim($path, '/');

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

    // Include auth token if it exists
    if (isset($_COOKIE['emil_web_auth_token'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Cookie: emil_web_auth_token=' . $_COOKIE['emil_web_auth_token'],
            'Content-Type: application/json'
        ]);
    }

    // Include JSON body for POST/PATCH
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    // SSL options (can remove if not needed)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) return null;

    return json_decode($response, true);
}

/**
 * Fetch the logged-in user
 */
function getUser(): ?array
{
    $result = apiRequest('/user');
    return $result['user'] ?? null;
}

/* Example usage:
$user = getUser();
if (!$user) {
    echo "Not logged in or token invalid.";
} else {
    echo "Welcome " . htmlspecialchars($user['username']);
}
*/
