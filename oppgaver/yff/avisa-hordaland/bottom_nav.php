<?php
include_once '../../../includes/config.php';
// bottom-nav.php
$bottomNavItems = [
    [
        'label' => 'Klassisk visning',
        'icon'  => 'bi-newspaper',
        'link'  => url('oppgaver/yff/avisa-hordaland/')
    ],
    [
        'label' => 'Hordaland Reels',
        'icon'  => 'bi-camera-video',
        'link'  => url('oppgaver/yff/avisa-hordaland/reels/')
    ]
];
?>

<nav class="navbar fixed-bottom navbar-light bg-light border-top">
    <div class="container d-flex justify-content-around">
        <?php foreach ($bottomNavItems as $item): ?>
            <a href="<?= htmlspecialchars($item['link']) ?>" class="text-decoration-none text-dark text-center py-2 col">
                <i class="bi <?= htmlspecialchars($item['icon']) ?> d-block fs-4"></i>
                <span style="font-size: 0.75rem;"><?= htmlspecialchars($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>