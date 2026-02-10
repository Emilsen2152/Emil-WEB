<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Javascript - EmilWEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="bg-dark">
    <?php
    include '../../../includes/navbar.php';
    ?>

    <div class="container-fluid bg-light py-2">
        <div class="container">
            <div class="mt-4 p-2 text-center">
                <h1>Javascript - Oppgåver</h1>
                <p>
                    Her finn du oppgåver relatert til Javascript.
                </p>
            </div>
        </div>

        <div class="container mt-4">
            <?php
            // Array med oppgåver
            $tasks = [
                [
                    "title" => "JavaScript Øving",
                    "url" => "./js-oving",
                ],
                [
                    "title" => "Side 52 - Prøv sjølv",
                    "url" => "./side-52/",
                ],
                [
                    "title" => "Side 60 - Prøv sjølv",
                    "url" => "./side-60/",
                ],
                [
                    "title" => "Side 64 - Prøv sjølv",
                    "url" => "./side-64/",
                ],
                [
                    "title" => "Side 69 - Prøv sjølv",
                    "url" => "./side-69/",
                ],
                [
                    "title" => "Klasselotteri",
                    "url" => "./klasselotteri/",
                ],
                [
                    "title" => "Utrekning Sirkel",
                    "url" => "./utrekning-sirkel/",
                ],
                [
                    "title" => "Eigen kalkulator",
                    "url" => "./eigen-kalkulator/",
                ],
                [
                    "title" => "Side 77",
                    "url" => "./side-77/",
                ],
                [
                    "title" => "Side 78",
                    "url" => "./side-78/",
                ],
                [
                    "title" => "Side 79",
                    "url" => "./side-79/",
                ],
                [
                    "title" => "Side 80",
                    "url" => "./side-80/",
                ],
                [
                    "title" => "Side 82",
                    "url" => "./side-82/",
                ],
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
    include '../../../includes/footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
