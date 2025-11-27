<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KP Oppgåver - EmilWEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>

<body class="bg-dark">
    <?php
    include '../../includes/navbar.php';
    ?>

    <div class="container-fluid bg-light py-2">
        <div class="container">
            <div class="mt-4 p-2 text-center">
                <h1>Konseptutvikling og Programmering - Oppgåver</h1>
                <p>
                    Her finn du oppgåver relatert til faget Konseptutvikling og Programmering.
                </p>
            </div>
        </div>

        <div class="container mt-4">
            <?php
            // Array med oppgåver
            $tasks = [
                [
                    "title" => "Gamle Oppgåver før og med 26.11.2025",
                    "url" => "../../old/oppgaver/kp/"
                ]
            ];
            ?>

            <!-- Centered, slightly bigger buttons -->
            <div class="d-flex flex-wrap justify-content-center gap-3 mt-1 mb-5">
                <?php foreach ($tasks as $task): ?>
                    <a href="<?= htmlspecialchars($task['url']) ?>" class="btn btn-primary btn-md">
                        <?= htmlspecialchars($task['title']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <?php
    include '../../includes/footer.php';
    ?>
</body>

</html>
