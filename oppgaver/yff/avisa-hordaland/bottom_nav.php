<?php
// bottom_nav.php
include_once __DIR__ . '/../../../includes/config.php';

$bottomNavItems = [
    ['label' => 'Info', 'icon' => 'bi-info-circle', 'link' => url('oppgaver/yff/avisa-hordaland/')],
    ['label' => 'Klassisk', 'icon' => 'bi-newspaper', 'link' => url('oppgaver/yff/avisa-hordaland/classic/')],
    ['label' => 'Reels', 'icon' => 'bi-camera-video', 'link' => url('oppgaver/yff/avisa-hordaland/reels/')],
    ['label' => 'Nær meg', 'icon' => 'bi-geo-alt', 'link' => url('oppgaver/yff/avisa-hordaland/nearby/')],
];
?>

<nav class="navbar fixed-bottom navbar-dark bg-dark border-top p-0">
    <div class="container d-flex justify-content-around" style="max-width: 500px; height: 70px;">
        <?php foreach ($bottomNavItems as $item): ?>
            <a href="<?= htmlspecialchars($item['link']) ?>" class="text-decoration-none text-light text-center py-2 col">
                <i class="bi <?= htmlspecialchars($item['icon']) ?> d-block fs-4"></i>
                <span style="font-size: 0.7rem;"><?= htmlspecialchars($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>