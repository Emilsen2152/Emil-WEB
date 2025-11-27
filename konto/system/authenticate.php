<?php

$API_BASE = 'https://emil.elevweb.no';

/**
 * Universal API request function.
 *
 * @param string $apiBase Base URL like https://emil.elevweb.no
 * @param string $method  HTTP method: GET/POST/PATCH/DELETE
 * @param string $path    Endpoint path like '/user' or 'user'
 * @param array|null $body Optional JSON body
 * @param array $headers Additional headers if needed
 *
 * @return array|null Decoded JSON response OR null if error
 */
function apiRequest(string $apiBase, string $method, string $path, ?array $body = null, array $headers = []): ?array
{
    // Build full URL
    $url = rtrim($apiBase, '/') . '/' . ltrim($path, '/');

    $ch = curl_init();

    // Basic CURL settings
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),

        // Cookies (auth token)
        CURLOPT_COOKIEFILE => '',
        CURLOPT_COOKIEJAR  => '',

        // SSL loosened (can be tightened)
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ];

    // Add JSON body if present
    if ($body !== null) {
        $jsonBody = json_encode($body);
        $options[CURLOPT_POSTFIELDS] = $jsonBody;
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($jsonBody);
    }

    // Set headers if any
    if (!empty($headers)) {
        $options[CURLOPT_HTTPHEADER] = $headers;
    }

    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);

    curl_close($ch);

    // Transport error
    if ($err) {
        error_log("CURL ERROR ($method $url): $err");
        return null;
    }

    // Decode JSON
    $data = json_decode($response, true);

    // Invalid response or error HTTP code
    if ($status < 200 || $status >= 300) {
        return null;
    }

    return is_array($data) ? $data : null;
}

/**
 * Fetch the logged-in user using GET /user
 */
function getUser(string $apiBase): ?array
{
    $result = apiRequest($apiBase, 'GET', '/user');

    if (!$result || !isset($result['user'])) {
        return null;
    }

    return $result['user'];
}

/**
 * Example usage:
 *
 * $user = getUser($API_BASE);
 * 
 * if (!$user) {
 *     echo "Not logged in or token invalid.";
 * } else {
 *     echo "Welcome " . htmlspecialchars($user['username']);
 * }
 *
 *
 * Example POST:
 *
 * $created = apiRequest($API_BASE, 'POST', '/giftcards/create', [
 *     'value' => 200,
 *     'type' => 'steam'
 * ]);
 *
 * if ($created) print_r($created);
 */
