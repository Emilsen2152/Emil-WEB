<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Cunt Hunt - EmilWEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

<body class="bg-dark text-light">

    <?php include '../../../includes/navbar.php'; ?>

    <div class="container-fluid py-3 py-md-5" style="max-width: 1000px;">
        <div class="card bg-black border-secondary shadow-lg">
            <div class="card-body">

                <div class="row g-1 g-md-2 mb-2 text-center">
                    <div class="col-3">
                        <div class="stat-box border border-secondary rounded bg-black py-2">
                            <small class="stat-label d-block text-secondary text-uppercase fw-bold">Skutt</small>
                            <span id="skutt" class="stat-value fw-bold text-primary">0 / 10</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-box border border-secondary rounded bg-black py-2">
                            <small class="stat-label d-block text-secondary text-uppercase fw-bold">Tid</small>
                            <span id="tid" class="stat-value fw-bold text-primary">0.00</span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-box border border-secondary rounded bg-black py-2" id="rekord-btn" style="cursor:pointer">
                            <small class="stat-label d-block text-secondary text-uppercase fw-bold">Rekord</small>
                            <span id="rekord" class="stat-value fw-bold text-success">0.00</span>
                        </div>
                    </div>
                    <div class="col-3 d-grid">
                        <button id="restartGame" class="btn btn-primary fw-bold btn-sm btn-md-lg">Start</button>
                    </div>
                </div>

                <div class="game-wrapper border border-secondary shadow">
                    <canvas id="game"></canvas>
                </div>

            </div>
        </div>

    </div>

    <?php include '../../../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>

</html>