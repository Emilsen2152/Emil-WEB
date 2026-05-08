<?php
// navbar.php
include_once '../../../includes/config.php';

$topNavItems = [
    'Tips oss'     => '#',
    'E-avis'       => '#',
    'Dødsannonsar' => '#',
    'Annonsering'  => '#',
    'Direktesport' => '#',
];

$baseUrl = url('');
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="<?= $baseUrl ?>">
            <img src="https://assets.acdn.no/local/v3/publications/www.avisa-hordaland.no/gfx/small-positive.svg" alt="Avisa Hordaland Logo" height="40">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php foreach ($topNavItems as $label => $link): ?>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="<?= htmlspecialchars($link) ?>">
                            <?= htmlspecialchars($label) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>