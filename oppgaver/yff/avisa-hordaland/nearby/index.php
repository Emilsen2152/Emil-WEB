<?php
$reels = [
    [
        'type' => 'video',
        'title' => 'Gymnashaugen Rundt 2026',
        'video_url' => '../videos/artikkel1.mp4',
        'description' => 'I dag var det Gymnashaugen Rundt, se målgangen!',
        'article_url' => 'https://www.avisa-hordaland.no/70-lag-pamelde-her-brakar-det-laus-i-ettermiddag/s/5-132-1093583',
        'map_url' => 'https://www.google.com/maps/place/Voss+gymnas/@60.6275412,6.4257883,342m/data=!3m1!1e3!4m6!3m5!1s0x463dda970a6e9889:0xab94dc0cc9b52a28!8m2!3d60.6269798!4d6.4264102!16s%2Fg%2F1z2crzz7m?hl=no&entry=ttu&g_ep=EgoyMDI2MDUwMi4wIKXMDSoASAFQAw%3D%3D'
    ],
];
?>
<!DOCTYPE html>
<html lang="nn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avisa Hordaland - Nær deg</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">

</head>

<body class="bg-black">

    <main id="reels-container">
        <?php foreach ($reels as $key => $reel): ?>
            <div class="reel-container" data-index="<?= $key ?>">

                <?php if ($reel['type'] === 'video'): ?>
                    <video class="reel-video" loop muted playsinline>
                        <source src="<?= $reel['video_url'] ?>" type="video/mp4">
                    </video>
                <?php else: ?>
                    <div class="reel-video text-bg-container" style="background-image: url('<?= $reel['bg_image'] ?? '' ?>');">
                        <div class="overlay-dark"></div>
                        <i class="bi bi-chat-left-text text-white opacity-25" style="font-size: 5rem; z-index: 1;"></i>
                    </div>
                <?php endif; ?>

                <div class="reel-content">
                    <h2 class="h4 fw-bold mb-1 text-white"><?= htmlspecialchars($reel['title']) ?></h2>
                    <p class="small mb-3 text-white"><?= htmlspecialchars($reel['description']) ?></p>

                    <div class="d-flex justify-content-between align-items-end">
                        <a href="<?= htmlspecialchars($reel['article_url']) ?>" class="btn-icon-link interaction-item" title="Les artikkel">
                            <i class="bi bi-newspaper"></i>
                            <span>Les</span>
                        </a>

                        <div class="d-flex gap-3 text-white">
                            <?php if ($reel['type'] === 'video'): ?>
                                <div class="interaction-item">
                                    <i id="mute-btn-<?= $key ?>"
                                        class="bi bi-volume-mute btn-interaction mute-control"
                                        style="cursor: pointer;"
                                        data-reel-id="<?= $key ?>"></i>
                                    <span>Lyd</span>
                                </div>
                            <?php endif; ?>

                            <div class="interaction-item">
                                <i id="like-btn-<?= $key ?>"
                                    class="bi bi-heart btn-interaction"
                                    style="cursor: pointer;"
                                    data-reel-id="<?= $key ?>"></i>
                                <span><?= rand(5, 150) ?></span>
                            </div>

                            <div class="interaction-item">
                                <i id="share-btn-<?= $key ?>"
                                    class="bi bi-share btn-interaction"
                                    style="cursor: pointer;"
                                    data-reel-id="<?= $key ?>"></i>
                                <span><?= rand(1, 40) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </main>

    <?php include '../bottom_nav.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="buttons.js"></script>
</body>

</html>