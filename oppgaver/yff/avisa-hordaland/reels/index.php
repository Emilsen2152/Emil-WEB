<?php
// Inkluderer konfigurasjon (juster stien om nødvendig)
// include_once '../../../includes/config.php'; 

$reels = [
    [
        'type' => 'video',
        'title' => 'Gymnashaugen Rundt 2026',
        'video_url' => 'videos/artikkel1.mp4',
        'description' => 'I dag var det Gymnashaugen Rundt, se målgangen!',
        'article_url' => 'https://www.avisa-hordaland.no/70-lag-pamelde-her-brakar-det-laus-i-ettermiddag/s/5-132-1093583'
    ],
    [
        'type' => 'text',
        'title' => 'Ordet fritt: Friskule',
        'bg_image' => 'images/voss-friskule.jpg',
        'description' => 'Voss Friskule: Eit supplement - ikkje ein trussel',
        'article_url' => 'https://www.avisa-hordaland.no/voss-friskule-eit-supplement-ikkje-ein-trussel/o/5-132-1106390'
    ],
    [
        'type' => 'video',
        'title' => 'Renteheving',
        'video_url' => 'videos/renteheving.mp4',
        'description' => 'Renta blir heva idag, me har spurd lokale innbyggjarar kva dei synest om det.',
        'article_url' => 'https://www.avisa-hordaland.no/hever-renta-til-4-25-prosent/s/5-132-1107571'
    ]
];
?>
<!DOCTYPE html>
<html lang="nn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avisa Hordaland - Reels</title>

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

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?= htmlspecialchars($reel['article_url']) ?>" class="btn-read-more">
                            Les artikkel
                        </a>

                        <div class="d-flex gap-3 fs-3 text-white">
                            <i id="like-btn-<?= $key ?>"
                                class="bi bi-heart btn-interaction"
                                style="cursor: pointer;"
                                data-reel-id="<?= $key ?>"></i>

                            <i id="share-btn-<?= $key ?>"
                                class="bi bi-share btn-interaction"
                                style="cursor: pointer;"
                                data-reel-id="<?= $key ?>"></i>

                            <?php if ($reel['type'] === 'video'): ?>
                                <div class="d-flex gap-3 fs-3 text-white">
                                <i id="mute-btn-<?= $key ?>"
                                    class="bi bi-volume-mute btn-interaction mute-control"
                                    style="cursor: pointer;"
                                    data-reel-id="<?= $key ?>"></i>

                                <i id="like-btn-<?= $key ?>" ...></i>
                                <i id="share-btn-<?= $key ?>" ...></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </main>

    <?php include '../bottom_nav.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="buttons.js"></script>

</html>