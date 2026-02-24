<?php
require_once __DIR__ . '/../../api/bootstrap.php';

$admin_user = check_admin($pdo, $config);

if (!$admin_user) {
    header('Location: ../?error=not_admin');
    exit;
}
?>
<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brukeradministrasjon - EmilWEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="bg-dark">
    <?php include '../../includes/navbar.php'; ?>

    <div class="container-fluid bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <h1 class="mb-4 text-center">Brukeradministrasjon</h1>

                    <a href="./" class="btn btn-primary btn-lg w-100 mt-2">
                        Tilbake til adminpanel
                    </a>

                    <div id="alert-area" class="mt-4"></div>

                    <div class="table-responsive mt-3">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">ID</th>
                                    <th>Brukernavn</th>
                                    <th style="width: 160px;">Registrert</th>
                                    <th style="width: 160px;">Siste innlogging</th>
                                    <th style="width: 260px;">Tilganger</th>
                                    <th style="width: 220px;">Handlinger</th>
                                </tr>
                            </thead>
                            <tbody id="user-table-body">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Laster brukere…
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-muted small mt-2 mb-0">
                        Tips: skriv permissions som kommaseparert liste, t.d. <code>admin,pingpanik</code>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>

    <script src="user_admin.js" type="module"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>