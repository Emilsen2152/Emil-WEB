<?php

$API_BASE = 'https://emil.elevweb.no';

function apiRequest(string $path, string $method = 'GET', ?array $body = null, ?string $token = null): ?array
{
    global $API_BASE;

    $url = rtrim($API_BASE, '/') . '/' . ltrim($path, '/');
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // prepare headers
    $headers = ['Accept: application/json'];

    // include JSON body for POST/PATCH
    if ($body !== null) {
        $json = json_encode($body);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $headers[] = 'Content-Type: application/json';
    }

    // Prefer token passed to function, otherwise use cookie from client
    if ($token === null && isset($_COOKIE['emil_web_auth_token'])) {
        $token = $_COOKIE['emil_web_auth_token'];
    }

    // If API expects the token as a cookie named emil_web_auth_token:
    if ($token !== null) {
        // Use CURLOPT_COOKIE — more reliable than manual header
        curl_setopt($ch, CURLOPT_COOKIE, 'emil_web_auth_token=' . $token);
    }

    // If the API instead expects Authorization: Bearer <token>, uncomment:
    /*
    if ($token !== null) {
        $headers[] = 'Authorization: Bearer ' . $token;
        // optionally remove the cookie line above
    }
    */

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // SSL: do NOT disable in production. Left commented here in case you're debugging locally.
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        // cURL-level error
        error_log('apiRequest cURL error: ' . $error);
        return null;
    }

    // optional: try to decode even for non-2xx so you can see API error message
    $decoded = json_decode($response, true);

    if ($status < 200 || $status >= 300) {
        // helpful debug info
        error_log("apiRequest: {$method} {$url} returned status {$status}");
        error_log("Response body: " . $response);
        return null;
    }

    return $decoded;
}

/** example getUser using the cookie-based approach */
function getUser(): ?array
{
    $result = apiRequest('/user');
    return is_array($result) && isset($result['user']) ? $result['user'] : null;
}
