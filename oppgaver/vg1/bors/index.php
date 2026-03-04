<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D01T - EmilWEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css" />
</head>

<body class="bg-dark">
    <?php
    include '../../../includes/navbar.php';
    ?>

    <div class="container-fluid bg-light">
        <div class="container py-4">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-3">
                <h1 class="m-0">Oslo børs</h1>
                <button id="newDay" class="btn btn-primary btn-lg">Ny dag</button>
            </div>

            <div class="row g-3">
                <!-- Status -->
                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Status</h5>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Dag</span>
                                <span id="day" class="fw-semibold">---</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Dato</span>
                                <span id="date" class="fw-semibold">---</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Børs</span>
                                <span id="open" class="badge text-bg-secondary">---</span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Saldo</span>
                                <span id="balance" class="fs-5 fw-bold">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Leaderboard -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="card-title m-0">Leaderboard</h5>
                                <span class="text-muted small">Total = kontanter + aksjeverdi</span>
                            </div>

                            <div class="table-responsive leaderboard-scroll">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 44px;">#</th>
                                            <th>Namn</th>
                                            <th class="text-end">Kontanter</th>
                                            <th class="text-end">Aksjar</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="leaderboardBody">
                                        <tr>
                                            <td colspan="5" class="text-muted">---</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Aksjar -->
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="card-title m-0">Aksjar</h5>
                                <span class="text-muted small">Kjøp/selg når børsen er open</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Namn</th>
                                            <th class="text-end">Pris</th>
                                            <th class="text-end">Dine</th>
                                            <th class="text-end">Tilgj.</th>
                                            <th class="text-end">Handling</th>
                                        </tr>
                                    </thead>
                                    <tbody id="stockBody">
                                        <tr>
                                            <td colspan="5" class="text-muted">---</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        Tips: Prisane endrar seg berre på kvardagar, men prisane endrar seg på mandagen basert på helga. Du kan planlegge kjøp og salg i helga og det vil skje på mandag.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include '../../../includes/footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="js/app.js"></script>
</body>

</html>