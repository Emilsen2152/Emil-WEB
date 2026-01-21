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

        <div class="container mt-5">
            <!-- Heading -->
            <div class="text-center mb-5">
                <h2 class="d-inline-block text-dark bg-warning rounded py-2 px-4 shadow-sm">
                    Viktig informasjon knytt til overgangen
                </h2>
            </div>

            <div class="row g-4">
                <!-- Warning 1 -->
                <div class="col-md-6">
                    <div class="card bg-warning text-dark shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Kvar er kontoen min?</h5>
                            <p class="card-text flex-grow-1">
                                Kontosystemet for EmilWEB vil mest sannsynlig ikkje bli implementert på denne sida.
                                Dette betyr at du ikkje kan logge inn, eller opprette ein konto på den nye sida.
                                Eg jobbar med å implementere dette, men det vil ta tid før det er klart.<br><br>
                                Du kan bruke den gamle EmilWEB sida for å logge inn på kontoen din.
                                Ingen av sidene på den nye EmilWEB sida krev innlogging, så du kan framleis bruke alle funksjonane utan ein konto.
                                Som før krever nokre av ressursane på den gamle sida innlogging. Om det blir eit nytt kontosystem vil dette skje på eit seinare tidspunkt langt fram i tid.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Warning 2 -->
                <div class="col-md-6">
                    <div class="card bg-warning text-dark shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-7">
                                    <h5 class="card-title">Advarsel til PingPanik ansatte</h5>
                                    <p class="card-text">
                                        Nettresursar for PingPanik UB er fortsatt på <a href="old/" class="text-dark text-decoration-underline">den gamle EmilWEB sida</a>.
                                        Det er usikkert om desse blir overførte til den nye sida. Nye resursar for PingPanik UB vil bli publisert her.<br><br>
                                        Grunnen for dette er at dei gamle resursane treng ei oppdatering før dei kan publiserast på den nye sida, noko som vil ta tid.
                                        Det er derfor usikkert om det i det heile tatt vil bli publisert her.
                                    </p>
                                </div>
                                <div class="col-md-5 text-center">
                                    <img src="assets/private/index/PP_Logo.png" class="img-fluid rounded" alt="PingPanik logoen.">
                                </div>
                            </div>
                        </div>
                    </div>
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