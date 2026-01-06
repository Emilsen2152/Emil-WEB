<?php

declare(strict_types=1);

namespace Elevweb\Api;

/**
 * Emil.elevweb.no API client (procedural, namespaced)
 *
 * Website: https://elevweb.no
 * API:     https://emil.elevweb.no
 */

const API_BASE = 'https://emil.elevweb.no';

/**
 * Simple cookie jar (in-memory)
 */
$GLOBALS['COOKIE_JAR'] = [];

/* ============================================================
 * Core HTTP
 * ============================================================ */

function api_request(string $method, string $path, ?array $json = null): array
{
    $url = API_BASE . $path;

    $ch = \curl_init($url);
    if (!$ch) {
        throw new \RuntimeException('Failed to init cURL');
    }

    $headers = ['Accept: application/json'];

    if ($json !== null) {
        $headers[] = 'Content-Type: application/json';
        \curl_setopt($ch, CURLOPT_POSTFIELDS, \json_encode($json, JSON_UNESCAPED_UNICODE));
    }

    \curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => \strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => \array_merge(
            $headers,
            build_cookie_header($url)
        ),
    ]);

    $raw = \curl_exec($ch);
    if ($raw === false) {
        throw new \RuntimeException(\curl_error($ch));
    }

    $status     = (int) \curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) \curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    unset($ch);

    $headersRaw = \substr($raw, 0, $headerSize);
    $bodyRaw    = \substr($raw, $headerSize);

    parse_set_cookie($headersRaw);

    return [
        'status' => $status,
        'data'   => $bodyRaw !== '' ? \json_decode($bodyRaw, true) : null,
    ];
}

/* ============================================================
 * Cookie handling
 * ============================================================ */

function build_cookie_header(string $url): array
{
    if (!$GLOBALS['COOKIE_JAR']) return [];

    $u     = \parse_url($url);
    $host  = \strtolower($u['host'] ?? '');
    $path  = $u['path'] ?? '/';
    $https = ($u['scheme'] ?? '') === 'https';

    $pairs = [];

    foreach ($GLOBALS['COOKIE_JAR'] as $name => $c) {
        if (($c['secure'] ?? false) && !$https) continue;

        if (!empty($c['domain'])) {
            $d = \ltrim(\strtolower($c['domain']), '.');
            if ($host !== $d && !\str_ends_with($host, '.' . $d)) continue;
        }

        if (!\str_starts_with($path, $c['path'] ?? '/')) continue;

        $pairs[] = $name . '=' . $c['value'];
    }

    return $pairs ? ['Cookie: ' . \implode('; ', $pairs)] : [];
}

function parse_set_cookie(string $headers): void
{
    foreach (\preg_split("/\r\n|\n|\r/", $headers) as $line) {
        if (\stripos($line, 'Set-Cookie:') !== 0) continue;

        $parts = \array_map('trim', \explode(';', \substr($line, 11)));
        [$name, $value] = \explode('=', $parts[0], 2);

        $cookie = [
            'value'  => $value,
            'domain' => null,
            'path'   => '/',
            'secure' => false,
        ];

        foreach (\array_slice($parts, 1) as $attr) {
            if (\strcasecmp($attr, 'Secure') === 0) $cookie['secure'] = true;
            if (\stripos($attr, 'Domain=') === 0)   $cookie['domain'] = \substr($attr, 7);
            if (\stripos($attr, 'Path=') === 0)     $cookie['path']   = \substr($attr, 5);
        }

        $GLOBALS['COOKIE_JAR'][$name] = $cookie['value'] === ''
            ? null
            : $cookie;

        if ($cookie['value'] === '') {
            unset($GLOBALS['COOKIE_JAR'][$name]);
        }
    }
}

/* ============================================================
 * Account endpoints
 * ============================================================ */

function register_user(string $username, string $password): array
{
    return api_request('POST', '/users', compact('username', 'password'));
}

function login_user(string $username, string $password): array
{
    return api_request('POST', '/login', compact('username', 'password'));
}

function logout_user(): array
{
    return api_request('POST', '/logout');
}

function get_authenticated_user(): array
{
    return api_request('GET', '/user');
}

function change_password(string $currentPassword, string $newPassword): array
{
    return api_request('PUT', '/user/password', compact('currentPassword', 'newPassword'));
}

/* ============================================================
 * Admin endpoints
 * ============================================================ */

function admin_list_users(): array
{
    return api_request('GET', '/users');
}

function admin_delete_user(string $username): array
{
    return api_request('DELETE', '/users/' . \rawurlencode($username));
}

function admin_set_permissions_patch(string $username, array $permissions): array
{
    return api_request(
        'PATCH',
        '/users/' . \rawurlencode($username),
        ['permissions' => \array_values($permissions)]
    );
}

function admin_set_permissions_put(string $username, array $permissions): array
{
    return api_request(
        'PUT',
        '/users/permissions/' . \rawurlencode($username),
        ['permissions' => \array_values($permissions)]
    );
}
