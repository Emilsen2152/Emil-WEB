<?php
declare(strict_types=1);

require_once __DIR__ . '/../api/bootstrap.php';

// Hent responsen frå api-en
$organizer_activities = get_admin_activities($pdo, $config);

// Korrigerer sjekken sidan get_admin_activities returnerer ein koda array via create_response()
$organizer = $organizer_activities['data']['organizer'] ?? null;

if (!$organizer) {
    // Ingen pålogga organisatør, vis organizer login
    header('Location: login');
    exit;
}

// Pålogga organisatør, vis administrasjonssida
$activities = $organizer_activities['data']['activities'] ?? [];
?>
<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temadag - Administrasjon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../main.css">
</head>

<body class="bg-light">
    <?php include '../nav.php'; ?>

    <main class="container py-5">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4">
            <div>
                <h1 class="h2 mb-1">Temadag - Administrasjon</h1>
                <p class="text-muted mb-0">Velkommen, <strong><?= htmlspecialchars($organizer['name']) ?></strong>! Her kan du administrere aktivitetane dine.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <button type="button" id="logout-btn" class="btn btn-outline-danger d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                        <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                    </svg>
                    Logg ut
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                
                <div class="card mb-5 shadow-sm">
                    <div class="card-header bg-primary text-dark">
                        <h2 class="h5 mb-0" id="activity-form-title">Opprett ny aktivitet</h2>
                    </div>
                    <div class="card-body">
                        <form id="activity-form">
                            <input type="hidden" id="activity-id" value="">

                            <div class="mb-3">
                                <label for="activity-name" class="form-label">Namn på aktiviteten</label>
                                <input type="text" class="form-control" id="activity-name" required>
                            </div>
                            <div class="mb-3">
                                <label for="activity-description" class="form-label">Beskrivelse</label>
                                <textarea class="form-control" id="activity-description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="activity-start" class="form-label">Start-tid</label>
                                <input type="datetime-local" class="form-control" id="activity-start" required>
                            </div>
                            <div class="mb-3">
                                <label for="activity-end" class="form-label">Slutt-tid</label>
                                <input type="datetime-local" class="form-control" id="activity-end" required>
                            </div>
                            <div class="mb-3">
                                <label for="activity-max-participants" class="form-label">Maks antal deltakarar</label>
                                <input type="number" class="form-control" id="activity-max-participants" min="1" required>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" id="activity-submit-btn" class="btn btn-success">Lagre aktivitet</button>
                                <button type="button" id="activity-cancel-btn" class="btn btn-secondary d-none">Avbryt endring</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (!empty($organizer['super_user'])): ?>
                    <div class="card mb-5 shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h2 class="h5 mb-0">Opprett ny brukar</h2>
                        </div>
                        <div class="card-body">
                            <form id="user-form">
                                <div class="mb-3">
                                    <label for="user-name" class="form-label">Namn</label>
                                    <input type="text" class="form-control" id="user-name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="user-username" class="form-label">Brukarnamn</label>
                                    <input type="text" class="form-control" id="user-username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="user-password" class="form-label">Passord</label>
                                    <input type="password" class="form-control" id="user-password" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="user-superuser" value="1">
                                    <label class="form-check-label" for="user-superuser">Superbrukar</label>
                                </div>
                                <button type="submit" class="btn btn-danger">Opprett brukar</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h2 class="h5 mb-0">Aktivitetar oppretta av deg</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($activities)): ?>
                            <p class="text-muted mb-0">Du har ikkje oppretta nokon aktivitetar enno.</p>
                        <?php else: ?>
                            <div class="list-group" id="activities-list">
                                <?php foreach ($activities as $activity): ?>
                                    <?php $act_id = $activity['activity_id'] ?? $activity['id'] ?? ''; ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-start p-3 mb-2 rounded border">
                                        <div class="me-auto">
                                            <h3 class="h6 mb-1 fw-bold"><?= htmlspecialchars($activity['name']) ?></h3>
                                            <p class="text-muted small mb-2"><?= nl2br(htmlspecialchars($activity['description'])) ?></p>
                                            <small class="text-secondary d-block mb-1">
                                                <strong>Tid:</strong> <?= date('d.m.Y H:i', strtotime($activity['start_time'])) ?> - <?= date('H:i', strtotime($activity['end_time'])) ?>
                                            </small>
                                            <small class="text-secondary d-block">
                                                <strong>Maks plassar:</strong> <?= htmlspecialchars((string)$activity['max_participants']) ?>
                                            </small>
                                        </div>
                                        
                                        <div class="d-flex flex-column gap-2 ms-3 align-self-center">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary edit-activity-trigger"
                                                    data-id="<?= htmlspecialchars((string)$act_id) ?>"
                                                    data-name="<?= htmlspecialchars($activity['name']) ?>"
                                                    data-description="<?= htmlspecialchars($activity['description']) ?>"
                                                    data-start="<?= date('Y-m-d\TH:i', strtotime($activity['start_time'])) ?>"
                                                    data-end="<?= date('Y-m-d\TH:i', strtotime($activity['end_time'])) ?>"
                                                    data-max="<?= htmlspecialchars((string)$activity['max_participants']) ?>">
                                                Endre
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info view-participants-trigger"
                                                    data-id="<?= htmlspecialchars((string)$act_id) ?>"
                                                    data-name="<?= htmlspecialchars($activity['name']) ?>">
                                                Sjå påmeldte
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="participantsModal" tabindex="-1" aria-labelledby="participantsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title fw-bold" id="participantsModalLabel">Påmeldte elevar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Lukk"></button>
                </div>
                <div class="modal-body">
                    <h6 id="modal-activity-name" class="text-muted mb-3"></h6>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Namn</th>
                                    <th scope="col">E-post</th>
                                    <th scope="col">Prioritet</th>
                                    <th scope="col">Påmeldt tidspunkt</th>
                                </tr>
                            </thead>
                            <tbody id="participants-table-body">
                            </tbody>
                        </table>
                    </div>
                    <div id="no-participants-msg" class="text-center py-3 d-none">
                        <p class="text-muted mb-0">Ingen elevar har meldt seg på denne aktiviteten enno.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Lukk</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin.js"></script>
</body>

</html>