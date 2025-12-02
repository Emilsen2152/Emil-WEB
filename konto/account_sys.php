<?php

$API_BASE = 'https://emil.elevweb.no';

function apiRequest(string $path, string $method = 'GET', ?array $body = null, ?string &$responseHeaders = null): ?array
{
    global $API_BASE;

    $url = rtrim($API_BASE, '/') . '/' . ltrim($path, '/');
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true); // include headers in output

    $headers = ['Accept: application/json'];

    if ($body !== null) {
        $json = json_encode($body);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $headers[] = 'Content-Type: application/json';
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false) {
        curl_close($ch);
        error_log('apiRequest cURL error: ' . $error);
        return null;
    }

    // Separate headers and body
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $responseHeaders = substr($response, 0, $headerSize);
    $bodyContent = substr($response, $headerSize);

    curl_close($ch);

    $decoded = json_decode($bodyContent, true);

    if ($status < 200 || $status >= 300) {
        error_log("apiRequest: {$method} {$url} returned status {$status}");
        error_log("Response body: " . $bodyContent);
        return $decoded; // still return decoded body for errors
    }

    return $decoded;
}

/** example getUser using the cookie-based approach */
function getUser(): ?array
{
    $result = apiRequest('/user');
    return is_array($result) && isset($result['user']) ? $result['user'] : null;
}

function login(string $username, string $password): ?array
{
    $body = [
        'username' => $username,
        'password' => $password
    ];

    $responseHeaders = '';
    $result = apiRequest('/login', 'POST', $body, $responseHeaders);

    if (!$result) return null;

    // Parse Set-Cookie from response headers
    if (preg_match('/Set-Cookie:\s*emil_web_auth_token=([^;]+)/i', $responseHeaders, $matches)) {
        $token = $matches[1];

        // Set cookie for user’s browser
        setcookie(
            'emil_web_auth_token',
            $token,
            [
                'expires' => time() + 60*60*24*7, // 7 days
                'path' => '/',
                'domain' => '.elevweb.no',       // shared parent domain
                'secure' => isset($_SERVER['HTTPS']), // only over HTTPS
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    return $result;
}

function register(string $username, string $password): ?array
{
    $body = [
        'username' => $username,
        'password' => $password,
    ];

    $responseHeaders = '';
    $result = apiRequest('/users', 'POST', $body, $responseHeaders);

    if (!$result) return null;

    // Parse Set-Cookie from response headers
    if (preg_match('/Set-Cookie:\s*emil_web_auth_token=([^;]+)/i', $responseHeaders, $matches)) {
        $token = $matches[1];

        // Set cookie for user's browser
        setcookie(
            'emil_web_auth_token',
            $token,
            [
                'expires' => time() + 60*60*24*7, // 7 days
                'path' => '/',
                'domain' => '.elevweb.no',        // shared parent domain
                'secure' => isset($_SERVER['HTTPS']), // only over HTTPS
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    return $result;
}

function logout(): bool
{
    // Call the API to invalidate the token server-side
    $result = apiRequest('/logout', 'POST');

    // Clear the token cookie in the browser
    setcookie(
        'emil_web_auth_token',
        '',
        [
            'expires' => time() - 3600,  // set in the past to delete
            'path' => '/',
            'domain' => '.elevweb.no',   // match the domain used when setting it
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );

    return $result !== null;
}


function changePassword(string $currentPassword, string $newPassword): ?array
{
    $body = ['currentPassword' => $currentPassword, 'newPassword' => $newPassword];
    $result = apiRequest('/user/password', 'PUT', $body);
    return is_array($result) ? $result : null;
}
