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

    // Include JSON body if provided
    if ($body !== null) {
        $json = json_encode($body);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $headers[] = 'Content-Type: application/json';
    }

    // Prevent caching (avoids 304 issues)
    $headers[] = 'Cache-Control: no-cache';
    $headers[] = 'Pragma: no-cache';

    // Automatically include token from browser cookie if available
    if (isset($_COOKIE['emil_web_auth_token'])) {
        curl_setopt($ch, CURLOPT_COOKIE, 'emil_web_auth_token=' . $_COOKIE['emil_web_auth_token']);
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

    // Treat 2xx and 304 as success
    if (($status < 200 || $status >= 300) && $status !== 304) {
        error_log("apiRequest: {$method} {$url} returned status {$status}");
        error_log("Response body: " . $bodyContent);
        return $decoded;
    }

    // Update token if server sends a new one
    setTokenFromHeaders($responseHeaders);

    return $decoded;
}

/** Helper to set emil_web_auth_token cookie from response headers */
function setTokenFromHeaders(string $responseHeaders): void
{
    if (preg_match('/Set-Cookie:\s*emil_web_auth_token=([^;]+)/i', $responseHeaders, $matches)) {
        $token = $matches[1];
        setcookie(
            'emil_web_auth_token',
            $token,
            [
                'expires' => time() + 60*60*24*7, // 7 days
                'path' => '/',
                'domain' => '.elevweb.no',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }
}

/** Login */
function login(string $username, string $password): ?array
{
    $body = ['username' => $username, 'password' => $password];
    return apiRequest('/login', 'POST', $body);
}

/** Register */
function register(string $username, string $password): ?array
{
    $body = ['username' => $username, 'password' => $password];
    return apiRequest('/users', 'POST', $body);
}

/** Get current user */
function getUser(): ?array
{
    $result = apiRequest('/user');
    return is_array($result) && isset($result['user']) ? $result['user'] : null;
}

/** Logout */
function logout(): bool
{
    $result = apiRequest('/logout', 'POST');

    // Clear token cookie
    setcookie(
        'emil_web_auth_token',
        '',
        [
            'expires' => time() - 3600, // delete cookie
            'path' => '/',
            'domain' => '.elevweb.no',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );

    return $result !== null;
}

/** Change password */
function changePassword(string $currentPassword, string $newPassword): ?array
{
    $body = ['currentPassword' => $currentPassword, 'newPassword' => $newPassword];
    return apiRequest('/user/password', 'PUT', $body);
}
