<?php
/*
Planlegging frontend
Eg planlegg å ha to sider. Ei for påmelding og ei for administrasjon av aktivitetar. På sida for påmelding vil det vera eit skjema der du kan velga 3 aktivitetar i prioritert rekkefølge, dette gjer eg sidan brukaren prioriterer basert på dei tre dei har mest lyst til å vera på.
Generelt vil skjemaet ha denne strukturen:
Namn
E-post
Aktivitet 1
Aktivitet 2
Aktivitet 3.
Administrasjonssida vil ha eit skjema for å opprette/endre aktivitetar.
Skjemaet vil ha denne strukturen:
Namn på aktiviteten
Beskrivelse
Start-tid
Slutt-tid
Maks antal deltakarar.
Under skjemaet vil det vera ei liste med aktivitetar den brukaren har oppretta. Der kan dei trykke på ein knapp for å laste den inn i skjemaet over for å endre den.
Om den pålogga brukaren er superbrukar vil den og få opp eit skjema for å opprette brukarar:
Namn
Brukarnamn
Passord
Om brukaren er ein superbrukar
*/

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
    <link rel="stylesheet" href="temadag.css">
</head>

<body class="bg-light">
    <?php
    include 'nav.php';
    ?>

    <main class="container py-5">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4">
            <div>
                <h1 class="h2 mb-1">Temadag - Administrasjon</h1>
                <p class="text-muted mb-0">Velkommen, <strong><?= htmlspecialchars($organizer['name']) ?></strong>! Her kan du administrere aktivitetene dine.</p>
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
                    <div class="card-header bg-primary text-white">
                        <h2 class="h5 mb-0" id="activity-form-title">Opprett ny aktivitet</h2>
                    </div>
                    <div class="card-body">
                        <form id="activity-form" method="POST" action="process_activity.php">
                            <input type="hidden" id="activity-id" name="activity_id" value="">

                            <div class="mb-3">
                                <label for="activity-name" class="form-label">Namn på aktiviteten</label>
                                <input type="text" class="form-control" id="activity-name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="activity-description" class="form-label">Beskrivelse</label>
                                <textarea class="form-control" id="activity-description" name="description" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="activity-start" class="form-label">Start-tid</label>
                                <input type="datetime-local" class="form-control" id="activity-start" name="start_time" required>
                            </div>
                            <div class="mb-3">
                                <label for="activity-end" class="form-label">Slutt-tid</label>
                                <input type="datetime-local" class="form-control" id="activity-end" name="end_time" required>
                            </div>
                            <div class="mb-3">
                                <label for="activity-max-participants" class="form-label">Maks antal deltakarar</label>
                                <input type="number" class="form-control" id="activity-max-participants" name="max_participants" min="1" required>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" id="activity-submit-btn" class="btn btn-success">Lagre aktivitet</button>
                                <button type="button" id="activity-cancel-btn" class="btn btn-secondary d-none">Avbryt endring</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (!empty($organizer['superuser']) || !empty($organizer['super_user'])): ?>
                    <div class="card mb-5 shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h2 class="h5 mb-0">Opprett ny brukar</h2>
                        </div>
                        <div class="card-body">
                            <form id="user-form" method="POST" action="process_user.php">
                                <div class="mb-3">
                                    <label for="user-name" class="form-label">Namn</label>
                                    <input type="text" class="form-control" id="user-name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="user-username" class="form-label">Brukarnamn</label>
                                    <input type="text" class="form-control" id="user-username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="user-password" class="form-label">Passord</label>
                                    <input type="password" class="form-control" id="user-password" name="password" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="user-superuser" name="superuser" value="1">
                                    <label class="form-check-label" for="user-superuser">Om brukaren er ein superbrukar</label>
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
                                    <?php 
                                        $act_id = $activity['activity_id'] ?? $activity['id'] ?? ''; 
                                    ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-start p-3 mb-2 rounded border">
                                        <div class="me-auto">
                                            <h3 class="h6 mb-1 fw-bold"><?= htmlspecialchars($activity['name']) ?></h3>
                                            <p class="text-muted small mb-2"><?= nl2br(htmlspecialchars($activity['description'])) ?></p>
                                            <small class="text-secondary d-block">
                                                <strong>Tid:</strong> <?= date('d.m.Y H:i', strtotime($activity['start_time'])) ?> - <?= date('H:i', strtotime($activity['end_time'])) ?>
                                            </small>
                                            <small class="text-secondary">
                                                <strong>Maks plassar:</strong> <?= htmlspecialchars($activity['max_participants']) ?>
                                            </small>
                                        </div>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary align-self-center ms-3 edit-activity-trigger"
                                                data-id="<?= htmlspecialchars((string)$act_id) ?>"
                                                data-name="<?= htmlspecialchars($activity['name']) ?>"
                                                data-description="<?= htmlspecialchars($activity['description']) ?>"
                                                data-start="<?= date('Y-m-d\TH:i', strtotime($activity['start_time'])) ?>"
                                                data-end="<?= date('Y-m-d\TH:i', strtotime($activity['end_time'])) ?>"
                                                data-max="<?= htmlspecialchars($activity['max_participants']) ?>">
                                            Endre
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>