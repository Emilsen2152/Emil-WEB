<?php
$reels = [
    [
        'type' => 'video',
        'title' => 'Renteheving',
        'video_url' => '../videos/renteheving.mp4',
        'description' => 'Renta blir heva idag, me har spurd lokale innbyggjarar kva dei synest om det.',
        'article_url' => 'https://www.avisa-hordaland.no/hever-renta-til-4-25-prosent/s/5-132-1107571',
        'map_url' => 'https://maps.app.goo.gl/cuRhAioZWwP74mCE8'
    ],
    [
        'type' => 'video',
        'title' => 'Gymnashaugen Rundt 2026',
        'video_url' => '../videos/artikkel1.mp4',
        'description' => 'I dag var det Gymnashaugen Rundt, se målgangen!',
        'article_url' => 'https://www.avisa-hordaland.no/70-lag-pamelde-her-brakar-det-laus-i-ettermiddag/s/5-132-1093583',
        'map_url' => 'https://www.google.com/maps/place/Voss+gymnas/@60.6275412,6.4257883,342m/data=!3m1!1e3!4m6!3m5!1s0x463dda970a6e9889:0xab94dc0cc9b52a28!8m2!3d60.6269798!4d6.4264102!16s%2Fg%2F1z2crzz7m?hl=no&entry=ttu&g_ep=EgoyMDI2MDUwMi4wIKXMDSoASAFQAw%3D%3D'
    ],
    [
        'type' => 'video',
        'title' => 'Sauen på vangen',
        'video_url' => '../videos/sau.mp4',
        'description' => 'Det var ein sau på vangen i dag, me har spurd sauen kva den synest om det.',
        'article_url' => 'https://www.avisa-hordaland.no/tid-for-ein-ny-kurs/o/5-132-1103964',
        'map_url' => "https://www.google.com/maps/place/60%C2%B037'46.4%22N+6%C2%B025'28.2%22E/@60.6295507,6.4238513,102m/data=!3m2!1e3!4b1!4m4!3m3!8m2!3d60.62955!4d6.424495?hl=no&entry=ttu&g_ep=EgoyMDI2MDUxMy4wIKXMDSoASAFQAw%3D%3D"
    ],
    [
        'type' => 'text',
        'title' => 'Ordet fritt: Friskule',
        'bg_image' => '../images/voss-friskule.jpg',
        'description' => 'Voss Friskule: Eit supplement - ikkje ein trussel',
        'article_url' => 'https://www.avisa-hordaland.no/voss-friskule-eit-supplement-ikkje-ein-trussel/o/5-132-1106390',
    ],
    [
        'type' => 'video',
        'title' => 'Intervju med Håvard Aldal',
        'video_url' => '../videos/håvardaldal.mp4',
        'description' => 'Me har snakka med Håvard Aldal.',
        'article_url' => 'https://www.avisa-hordaland.no/',
        'map_url' => "https://www.google.com/maps/place/Ringheimsvegen+4G,+5704+Vossevangen/@60.630555,6.4186717,148m/data=!3m1!1e3!4m6!3m5!1s0x463ddabdc28d6117:0x4f472484976a0d8a!8m2!3d60.6307336!4d6.4191668!16s%2Fg%2F11c1xwb4qs?hl=no&entry=ttu&g_ep=EgoyMDI2MDUxMy4wIKXMDSoASAFQAw%3D%3D"
    ],
    [
        'type' => 'video',
        'title' => 'Circle K konkurs',
        'video_url' => '../videos/circlek.mp4',
        'description' => 'Circle K på Voss har gått konkurs.',
        'article_url' => 'https://www.avisa-hordaland.no/stasjonen-gjekk-konkurs-har-ein-million-i-gjeld/s/5-132-1062340',
        'map_url' => "https://www.google.com/maps/place/Circle+K+Automat+Voss/@60.6283818,6.4255638,166m/data=!3m1!1e3!4m6!3m5!1s0x463dda9653cab9cd:0xfae3cfe1a24f9f44!8m2!3d60.6285797!4d6.4246615!16s%2Fg%2F11cn9rc68f?hl=no&entry=ttu&g_ep=EgoyMDI2MDUxMy4wIKXMDSoASAFQAw%3D%3D"
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