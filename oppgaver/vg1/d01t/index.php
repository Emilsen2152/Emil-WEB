<?php
require_once __DIR__ . '/api/bootstrap.php';
require_once __DIR__ . '/../../../api/bootstrap.php';

$user = current_user($pdo, $config);

if ($user) {
    $user_to_do_lists_result = get_user_to_do_lists($pdo, $config);
    if ($user_to_do_lists_result['success']) {
        $user_to_do_lists = $user_to_do_lists_result['data']['lists'] ?? [];
        foreach ($user_to_do_lists as &$list) {
            $username_result = get_creator_username($pdo, $config, $list['owner_id']);
            $list['creator_username'] = $username_result ?? 'Ukjent';
        }
    } else {
        $user_to_do_lists = [];
    }
}
?>

<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>d01t - EmilWEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="d01t.css">
</head>

<body class="bg-dark">
    <?php
    include '../../../includes/navbar.php';
    ?>

    <div class="container-fluid bg-light py-2">
        <div class="container">
            <h1 class="mt-4 p-2 text-center text-primary">d01t</h1>
            <p class="lead text-center">d01t er ei enkel sjekkliste nettsida.</p>
        </div>

        <div class="container py-4">

            <h2 class="text-center text-secondary mb-4">
                Dine lister
            </h2>

            <div class="row g-3 justify-content-center">
                <?php if ($user): ?>
                    <?php if (!empty($user_to_do_lists)): ?>
                        <?php foreach ($user_to_do_lists as $list): ?>
                            <div class="col-md-6 col-lg-5">
                                <a href="/oppgaver/vg1/d01t/to_do_list/<?= htmlspecialchars($list['id']) ?>"
                                    class="text-decoration-none">
                                    <div class="card shadow-sm h-100 list-card">
                                        <div class="card-body">
                                            <h5 class="text-primary mb-2">
                                                <?= htmlspecialchars($list['name']) ?>
                                            </h5>
                                            <div class="text-muted small">
                                                Opprettet av <?= htmlspecialchars($list['creator_username']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">
                                Du har ingen lister enda. Lag en ny for å komme i gang!
                            </p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">
                            Logg inn for å se dine lister.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="container text-center mb-4">
            <button class="btn btn-primary btn-lg"
                data-bs-toggle="modal"
                data-bs-target="#createListModal">
                Opprett ny sjekkliste
            </button>
        </div>

        <!-- Create List Modal -->
        <div class="modal fade" id="createListModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Opprett ny sjekkliste</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div id="create-list-alert" class="alert d-none" role="alert"></div>

                        <form id="create-list-form" class="d-grid gap-3">

                            <div>
                                <label class="form-label">Namn på lista</label>
                                <input type="text"
                                    class="form-control"
                                    name="name"
                                    placeholder="t.d. Handleliste"
                                    required>
                            </div>

                            <?php if ($user): ?>
                                <!-- Innlogga kan velge -->
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        id="privateCheck"
                                        name="private">
                                    <label class="form-check-label" for="privateCheck">
                                        Privat liste
                                    </label>
                                    <div class="form-text">
                                        Privat liste kan berre visast av deg og dei du deler ho med.
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Gjester får berre info -->
                                <div class="alert alert-info small mb-0">
                                    Som gjest kan du berre opprette offentlege lister.
                                </div>
                            <?php endif; ?>

                            <button class="btn btn-primary" type="submit">
                                Opprett
                            </button>

                        </form>

                    </div>
                </div>
            </div>
        </div>

        <div class="container pt-4">
            <h2 class="text-center text-secondary">Funksjonar</h2>
            <div class="row justify-content-center mt-4">
                <div class="col-md-9 text-center">
                    <div class="row g-4 justify-content-center">
                        <div class="col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h4 class="card-title fw-bold">Alle kan oppretta sjekklister</h4>
                                    <p class="card-text">
                                        Alle kan oppretta sjekklister, og det er ingen krav til innlogging eller registrering. Det er berre å gå inn på nettsida og laga ei sjekkliste.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h4 class="card-title fw-bold">Private og offentlege sjekklister</h4>
                                    <p class="card-text">
                                        Folk med konto kan også laga private sjekklister som berre dei sjølv kan sjå og redigera. Dei som ikkje har konto kan berre laga offentlege sjekklister.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h4 class="card-title fw-bold">Enkel deling</h4>
                                    <p class="card-text">
                                        Offentlege sjekklister kan delast ved å kopiera og senda lenka til sjekklista eller med direkte deling til folk med konto. Private sjekklister kan kun delast med direkte deling til folk med konto.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-4">
            <h2 class="text-center text-secondary">Bruksområde</h2>
            <div class="row justify-content-center mt-4">
                <div class="col-md-9 text-center">
                    <div class="row g-4 justify-content-center">
                        <div class="col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h4 class="card-title fw-bold">Enkel oppgaveplanlegging</h4>
                                    <p class="card-text">
                                        d01t er perfekt for enkel oppgaveplanlegging, enten det er for daglige gjøremål, skolearbeid eller små prosjekter. Det gir en rask og intuitiv måte å holde styr på oppgaver og sjekklister.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h4 class="card-title fw-bold">Handleliste</h4>
                                    <p class="card-text">
                                        d01t er perfekt for å laga handlelister. Du kan enkelt leggja til og sletta varer, og det er enkelt å dele handlelister med andre.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h4 class="card-title fw-bold">Sikker og enkel</h4>
                                    <p class="card-text">
                                        d01t er ein sikker og enkel måte å laga sjekklister på. Du treng ikkje å registrera deg for å bruka d01t, men du kan også laga konto for å ha meir kontroll over dine sjekklister.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include '../../../includes/footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="main_page.js"></script>
</body>

</html>