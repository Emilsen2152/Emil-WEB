<?php
// navbar.php
include_once __DIR__ . '/../../../includes/config.php';

$topNavItems = [
    'Tips oss'     => 'https://www.avisa-hordaland.no/vis/info/tips-oss',
    'E-avis'       => 'https://eavis.avisa-hordaland.no',
    'Dødsannonsar' => 'https://www.avisa-hordaland.no/vis/dodsannonser/',
    'Annonsering'  => 'https://www.avisa-hordaland.no/meiningar',
    'Direktesport' => 'https://www.direktesport.no/',
];

$baseUrl = url('/oppgaver/yff/avisa-hordaland/');
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