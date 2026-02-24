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
    <title>Administrator - EmilWEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="bg-dark">
    <?php
    include '../../includes/navbar.php';
    ?>

    <div class="container-fluid bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">

                    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5">
                        <h1 class="mb-4 text-center">Administratorpanel</h1>

                        <a href="brukere" class="btn btn-primary btn-lg w-100 mt-2">
                            Brukeradministrasjon
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include '../../includes/footer.php';
    ?>>
</body>
</html>