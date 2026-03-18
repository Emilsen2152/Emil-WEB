<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Cunt Hunt - EmilWEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-light">

    <?php include '../../../includes/navbar.php'; ?>

    <div class="container py-5" style="max-width: 950px;">

        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="fw-bold display-5 mb-2">The Cunt Hunt</h1>
            <p class="text-secondary mb-0">Skyt ballongene på minst mogleg tid.</p>
        </div>

        <!-- Main Card -->
        <div class="card bg-black border-secondary shadow-lg">
            <div class="card-body p-4 p-md-5">

                <!-- Top Bar -->
                <div class="row align-items-center g-3 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold text-light">Antall skutt:</span>
                            <span id="skutt" class="badge bg-primary fs-6 px-3 py-2 rounded-pill">0 / 10</span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold text-light">Tid brukt:</span>
                            <span id="tid" class="badge bg-primary fs-6 px-3 py-2 rounded-pill">0 s</span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex justify-content-md-end align-items-center gap-2">
                            <span class="fw-semibold text-light">Rekord:</span>

                            <button id="rekord"
                                class="btn btn-primary btn-sm rounded-pill px-3 py-2 fw-semibold">
                                0 s
                            </button>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex justify-content-md-end">
                            <button id="restartGame" class="btn btn-primary px-4">
                                Restart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Canvas Wrapper -->
                <div class="bg-dark border border-secondary rounded-4 p-3 p-md-4">
                    <div class="ratio ratio-16x9">
                        <canvas id="game" class="w-100 h-100 bg-light rounded-3"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php include '../../../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>

</html>