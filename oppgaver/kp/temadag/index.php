<?php
require_once __DIR__ . '/api/bootstrap.php';

// Hent responsen frå API-en
$activitiesResponse = get_activities($pdo, $config);

// Hent ut sjølve aktivitetslista frå datastrukturen
$activities = $activitiesResponse['data']['activities'] ?? [];

// Sjekk om det ligg ein hash i URL-en for endring av påmelding
$urlHash = $_GET['hash'] ?? null;
?>

<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temadag - Påmelding</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="main.css">
</head>

<body class="bg-light">
    <?php include 'nav.php'; ?>

    <main class="container py-5">
        <h1 class="mb-4" id="page-title">Temadag - Påmelding</h1>

        <h2 class="h4 mb-3">Tilgjengelege aktivitetar</h2>
        <?php if (empty($activities)): ?>
            <div class="alert alert-info">Det er ingen tilgjengelege aktivitetar.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
                <?php foreach ($activities as $activity): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title h5 text-primary"><?= htmlspecialchars($activity['name']) ?></h3>
                                <p class="card-text text-muted small">
                                    <?= nl2br(htmlspecialchars($activity['description'])) ?>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0 small text-secondary">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><strong>Start:</strong> <?= date('d.m.Y H:i', strtotime($activity['start_time'])) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span><strong>Slutt:</strong> <?= date('d.m.Y H:i', strtotime($activity['end_time'])) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span><strong>Arrangør:</strong> <?= htmlspecialchars($activity['organizer_name']) ?></span>
                                </div>
                                <div class="mt-2 pt-2 border-top text-dark fw-semibold">
                                    Maks plassar: <?= htmlspecialchars($activity['max_participants']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr class="my-5">

        <div class="card shadow-sm max-width-form mx-auto" style="max-width: 600px;">
            <div class="card-header bg-dark text-white" id="form-header">
                <h2 class="h5 mb-0">Fyll ut påmeldingsskjema</h2>
            </div>
            <div class="card-body p-4">
                <form id="registration-form" data-hash="<?= htmlspecialchars((string)$urlHash) ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Namn</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-post</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="activity1" class="form-label text-success fw-bold">Aktivitet 1 (Fyrsteprioritet)</label>
                        <select class="form-select" id="activity1" name="activity1" required>
                            <option value="" disabled selected>Velg din favorittaktivitet</option>
                            <?php foreach ($activities as $activity): ?>
                                <?php $actId = $activity['activity_id'] ?? $activity['id'] ?? ''; ?>
                                <option value="<?= htmlspecialchars((string)$actId) ?>"><?= htmlspecialchars($activity['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="activity2" class="form-label text-info fw-bold">Aktivitet 2 (Andreprioritet)</label>
                        <select class="form-select" id="activity2" name="activity2" required>
                            <option value="" disabled selected>Velg aktivitet nummer to</option>
                            <?php foreach ($activities as $activity): ?>
                                <?php $actId = $activity['activity_id'] ?? $activity['id'] ?? ''; ?>
                                <option value="<?= htmlspecialchars((string)$actId) ?>"><?= htmlspecialchars($activity['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="activity3" class="form-label text-warning fw-bold">Aktivitet 3 (Tredjeprioritet)</label>
                        <select class="form-select" id="activity3" name="activity3" required>
                            <option value="" disabled selected>Velg aktivitet nummer tre</option>
                            <?php foreach ($activities as $activity): ?>
                                <?php $actId = $activity['activity_id'] ?? $activity['id'] ?? ''; ?>
                                <option value="<?= htmlspecialchars((string)$actId) ?>"><?= htmlspecialchars($activity['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" id="submit-btn" class="btn btn-primary w-100 py-2">Meld på</button>

                    <div id="form-message" class="mt-3"></div>
                </form>
            </div>
        </div>
    </main>

    <script src="main.js"></script>
</body>
</html>