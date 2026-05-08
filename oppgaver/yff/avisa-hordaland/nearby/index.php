<?php
// Protopype data
$artiklar = [
    [
        'title' => 'Gymnashaugen Rundt 2026',
        'reel_url' => '../reels/?reelId=2',
        'description' => 'I dag var det Gymnashaugen Rundt, se målgangen!',
        'article_url' => 'https://www.avisa-hordaland.no/70-lag-pamelde-her-brakar-det-laus-i-ettermiddag/s/5-132-1093583',
        'map_url' => 'https://www.google.com/maps/place/Voss+gymnas/@60.6275412,6.4257883,342m/data=!3m1!1e3!4m6!3m5!1s0x463dda970a6e9889:0xab94dc0cc9b52a28!8m2!3d60.6269798!4d6.4264102!16s%2Fg%2F1z2crzz7m?hl=no&entry=ttu&g_ep=EgoyMDI2MDUwMi4wIKXMDSoASAFQAw%3D%3D'
    ],
];
?>
<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avisa Hordaland - Nær meg</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body class="bg-white">
    <?php
    include '../navbar.php';
    ?>

    <main>
        <!-- Viss innholdet nært personen, hent frå arrayen med reel også. Bruk foreach-løkke -->
        <div class="container mt-4">
            <h1 class="mb-3">Nær meg</h1>
            <p>
                Se artiklar og videoar som er relevante for der du er. Dette kan vera lokale nyhende, arrangement eller andre ting som skjer i nærleiken av deg.
            </p>
            <?php foreach ($artiklar as $artikkel): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($artikkel['title']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($artikkel['description']) ?></p>
                        <a href="<?= htmlspecialchars($artikkel['article_url']) ?>" class="btn btn-primary">Les artikkel</a>
                        <a href="<?= htmlspecialchars($artikkel['map_url']) ?>" class="btn btn-secondary" target="_blank">Se på kart</a>
                        <a href="<?= htmlspecialchars($artikkel['reel_url']) ?>" class="btn btn-info">Se reel</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php
    include '../bottom_nav.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>