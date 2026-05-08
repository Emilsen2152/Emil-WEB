<?php
// bottom_nav.php
include_once __DIR__ . '/../../../includes/config.php';

$bottomNavItems = [
    ['label' => 'Klassisk', 'icon' => 'bi-newspaper', 'link' => url('oppgaver/yff/avisa-hordaland/')],
    ['label' => 'Reels', 'icon' => 'bi-camera-video', 'link' => url('oppgaver/yff/avisa-hordaland/reels/')]
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