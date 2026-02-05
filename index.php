<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EmilWEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="bg-dark">
    <?php
    include 'includes/navbar.php';
    ?>

    <div class="container-fluid bg-light py-5">
        <div class="container">
            <div class="mt-4 p-1 p-md-5 bg-primary shadow-sm text-white rounded row">
                <div class="col-md-6">
                    <h1>Velkommen til nye EmilWEB!</h1>
                    <p>
                        Dette er den nye versjonen av elevweb sida til Emil Velken Soldal.
                        Her finn du informasjon om mine eigne prosjekt, oppgåver eg har gjort, og lenker til anna innhald.
                        Om du ønsker å se på den gamle sida finner du den i navbaren ovanfor.
                    </p>
                </div>
                <div class="col-md-6">
                    <img src="assets/private/index/hero_image.JPG" class="rounded img-fluid mt-3" alt="Eit bilde som viser ein mur på Voss stasjon, det er kvit himmer og snø på muren. Det er og måse på muren.">
                </div>
            </div>
        </div>
    </div>

    <?php
    include 'includes/footer.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>