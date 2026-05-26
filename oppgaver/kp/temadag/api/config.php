<?php

declare(strict_types=1);

if ($_SERVER['HTTP_HOST'] === 'localhost') {
    return [
        'db' => [
            'host' => '46.101.240.37',
            'name' => 'temadag',
            'user' => 'web',
            'pass' => '(_W-m0ladLi]01gF',
            'charset' => 'utf8mb4',
            'port' => 3306
        ],

        // Cookie settings
        'cookie' => [
            'name' => 'emil_web_auth_token',
            'domain' => null,          // null for localhost. Set '.elevweb.no' when you move to prod.
            'secure' => false,         // true on HTTPS
            'samesite' => 'Lax',       // 'None' if you need cross-site + HTTPS
            'maxAge' => 60 * 60 * 24 * 7,
        ]
    ];
};

return [
    'db' => [
        'host' => '46.101.240.37',
        'name' => 'temadag',
        'user' => 'web',
        'pass' => '(_W-m0ladLi]01gF',
        'charset' => 'utf8mb4',
        'port' => 3306
    ],

    // Cookie settings
    'cookie' => [
        'name' => 'emil_web_auth_token',
        'domain' => '.elevweb.no',          // null for localhost. Set '.elevweb.no' when you move to prod.
        'secure' => true,         // true on HTTPS
        'samesite' => 'Lax',       // 'None' if you need cross-site + HTTPS
        'maxAge' => 60 * 60 * 24 * 7,
    ]
];
