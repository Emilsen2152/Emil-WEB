<?php
require_once __DIR__ . '/../../api/bootstrap.php';

$user = current_user($pdo, $config);

if (!$user) {
    header('Location: ../../../konto/login');
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

<body class="bg-dark">
    <?php
    include '../../../includes/navbar.php';
    ?>

    <div class="container-fluid bg-light py-2">
        <div class="container">
            <h1 class="display-4">Møtesystem Voss</h1>
            <p class="lead">Velkommen til Møtesystemet originalt laga for Heradsstyremøtet 16.04.2026</p>
        </div>

        
    </div>

    <?php
    include '../../../includes/footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
