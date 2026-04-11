<?php
require_once __DIR__ . '/../../../api/bootstrap.php';
require_once __DIR__ . '/api/bootstrap.php';

$adminUser = check_admin($pdo, $config);

if (!$adminUser) {
    header('Location: ../../../konto/?error=not_admin');
    exit;
}
?>

<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Møtesystem Voss</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="bg-dark text-white">
    <?php
    include '../../../includes/navbar.php';
    ?>

    <div class="container-fluid bg-light py-5 text-dark">
        <div class="container text-center mb-5">
            <h1 class="display-4">Møtesystem Voss</h1>
            <p class="lead">Velkommen til Møtesystemet originalt laga for Heradsstyremøtet 16.04.2026</p>
        </div>

        <div class="container">
            <h2 class="mb-4 text-center">Her er alle data-feltene du kan endra på.</h2>

            <div id="data-fields" class="row gy-4 justify-content-center">

                <?php
                foreach (get_all_rows($pdo)['data'] as $row): ?>

                    <div class="col-md-6 col-lg-5">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($row['data_name']) ?></h5>
                                <p class="card-text">Nåværende verdi: <br><strong class="fs-5"><?= htmlspecialchars($row['data_value']) ?></strong></p>

                                <form class="update-form">
                                    <div class="mb-3 text-start"> <label for="value-<?= htmlspecialchars($row['data_name']) ?>" class="form-label small">Ny verdi</label>
                                        <input type="text" class="form-control" id="value-<?= htmlspecialchars($row['data_name']) ?>" name="value">
                                    </div>
                                    <input type="hidden" name="name" value="<?= htmlspecialchars($row['data_name']) ?>">
                                    <button type="submit" class="btn btn-primary px-4">Oppdater</button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>

            </div>
        </div>
    </div>

    <?php
    include '../../../includes/footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="editor.js" type="module"></script>
</body>

</html>