<?php
include_once 'config.php';

$navItems = [
    'Eigenprosjekt' => [],
    'Oppgåver' => [
        'Konseptutvikling og Programmering' => url('oppgaver/kp/')
    ],
    'Til gamle EmilWEB' => url('old/'),
    'Til elevweb.no' => 'https://elevweb.no'
];

$baseUrl = url('');

echo <<<HTML
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="$baseUrl">
            <span class="text-primary">Emil</span>WEB
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
HTML;

foreach ($navItems as $label => $link) {
    if (is_array($link)) {

        $dropdownId = htmlspecialchars($label);

        echo <<<HTML
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown$dropdownId"
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {$dropdownId}
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown$dropdownId">
HTML;

        foreach ($link as $subLabel => $subLink) {
            $safeLabel = htmlspecialchars($subLabel);
            $safeLink = htmlspecialchars($subLink);

            echo <<<HTML
                        <li><a class="dropdown-item" href="$safeLink">$safeLabel</a></li>
HTML;
        }

        echo <<<HTML
                    </ul>
                </li>
HTML;

    } else {
        $safeLabel = htmlspecialchars($label);
        $safeLink = htmlspecialchars($link);

        echo <<<HTML
                <li class="nav-item">
                    <a class="nav-link" href="$safeLink">$safeLabel</a>
                </li>
HTML;
    }
}

echo <<<HTML
            </ul>
        </div>
    </div>
</nav>
HTML;
