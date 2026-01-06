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
 * Simple cookie jar:
 * [
 *   'cookie_name' => [
 *      'value' => string,
 *      'domain' => ?string,
 *      'path' => string,
 *      'secure' => bool
 *   ]
 * ]
 */
$GLOBALS['COOKIE_JAR'] = [];

/* ============================================================
 * Core HTTP helper
 * ============================================================ */

function request_api(string $method, string $path, ?array $json = null): array
{
    $url = API_BASE . $path;

    $ch = \curl_init($url);
    if (!$ch) {
        throw new \RuntimeException('Failed to init cURL');
    }

    $headers = ['Accept: application/json'];

    if ($json !== null) {
        $payload = \json_encode($json, JSON_UNESCAPED_UNICODE);
        $headers[] = 'Content-Type: application/json';
        \curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
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
    \curl_close($ch);

    $headersRaw = \substr($raw, 0, $headerSize);
    $bodyRaw    = \substr($raw, $headerSize);

    parse_set_cookie($headersRaw);

    $data = $bodyRaw !== ''
        ? \json_decode($bodyRaw, true)
        : null;

    return [
        'status' => $status,
        'data'   => $data,
    ];
}

/* ============================================================
 * Cookie handling
 * ============================================================ */

function build_cookie_header(string $url): array
{
    if (empty($GLOBALS['COOKIE_JAR'])) {
        return [];
    }

    $u     = \parse_url($url);
    $host  = \strtolower($u['host'] ?? '');
    $path  = $u['path'] ?? '/';
    $https = ($u['scheme'] ?? '') === 'https';

    $pairs = [];

    foreach ($GLOBALS['COOKIE_JAR'] as $name => $c) {
        if (($c['secure'] ?? false) && !$https) {
            continue;
        }

        $cookieDomain = $c['domain'] ?? null;
        if ($cookieDomain) {
            $d = \ltrim(\strtolower((string) $cookieDomain), '.');
            if ($host !== $d && !\str_ends_with($host, '.' . $d)) {
                continue;
            }
        }

        $cookiePath = $c['path'] ?? '/';
        if (!\str_starts_with($path, (string) $cookiePath)) {
            continue;
        }

        $pairs[] = $name . '=' . ($c['value'] ?? '');
    }

    return $pairs ? ['Cookie: ' . \implode('; ', $pairs)] : [];
}

function parse_set_cookie(string $headers): void
{
    $lines = \preg_split("/\r\n|\n|\r/", $headers) ?: [];

    foreach ($lines as $line) {
        if (\stripos($line, 'Set-Cookie:') !== 0) {
            continue;
        }

        $parts = \array_map('trim', \explode(';', \substr($line, 11)));
        if (count($parts) === 0) {
            continue;
        }

        $nv = $parts[0];
        $eqPos = \strpos($nv, '=');
        if ($eqPos === false) {
            continue;
        }

        $name  = \trim(\substr($nv, 0, $eqPos));
        $value = \trim(\substr($nv, $eqPos + 1));

        if ($name === '') {
            continue;
        }

        $cookie = [
            'value'  => $value,
            'domain' => null,
            'path'   => '/',
            'secure' => false,
        ];

        foreach (\array_slice($parts, 1) as $attr) {
            if (\strcasecmp($attr, 'Secure') === 0) {
                $cookie['secure'] = true;
                continue;
            }
            if (\stripos($attr, 'Domain=') === 0) {
                $cookie['domain'] = \substr($attr, 7);
                continue;
            }
            if (\stripos($attr, 'Path=') === 0) {
                $cookie['path'] = \substr($attr, 5);
                continue;
            }
        }

        // If cleared cookie, remove it
        if ($cookie['value'] === '') {
            unset($GLOBALS['COOKIE_JAR'][$name]);
            continue;
        }

        $GLOBALS['COOKIE_JAR'][$name] = $cookie;
    }
}

/* ============================================================
 * Account endpoints
 * ============================================================ */

function register_user(string $username, string $password): array
{
    return request_api('POST', '/users', compact('username', 'password'));
}

function login_user(string $username, string $password): array
{
    return request_api('POST', '/login', compact('username', 'password'));
}

function logout_user(): array
{
    return request_api('POST', '/logout');
}

function get_current_user(): array
{
    return request_api('GET', '/user');
}

function change_password(string $currentPassword, string $newPassword): array
{
    return request_api('PUT', '/user/password', compact('currentPassword', 'newPassword'));
}

/* ============================================================
 * Admin endpoints
 * ============================================================ */

function admin_list_users(): array
{
    return request_api('GET', '/users');
}

function admin_delete_user(string $username): array
{
    return request_api('DELETE', '/users/' . \rawurlencode($username));
}

function admin_set_permissions_patch(string $username, array $permissions): array
{
    return request_api(
        'PATCH',
        '/users/' . \rawurlencode($username),
        ['permissions' => \array_values($permissions)]
    );
}

function admin_set_permissions_put(string $username, array $permissions): array
{
    return request_api(
        'PUT',
        '/users/permissions/' . \rawurlencode($username),
        ['permissions' => \array_values($permissions)]
    );
}
