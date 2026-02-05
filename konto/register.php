<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrer konto - EmilWEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="bg-dark">
    <?php
    include '../includes/navbar.php';
    ?>

    <div class="container-fluid bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">

                    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5">
                        <h1 class="mb-4 text-center">Registrer konto</h1>

                        <form id="register-form">
                            <div class="mb-3">
                                <label for="username" class="form-label">Brukarnamn</label>
                                <input type="text" class="form-control form-control-lg"
                                    id="username" name="username" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Passord</label>
                                <input type="password" class="form-control form-control-lg"
                                    id="password" name="password" required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">
                                Opprett konto
                            </button>
                        </form>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-dark">
                            Har du allereie konto?
                            <a href="./login" class="text-warning fw-semibold">
                                Logg inn her
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    include '../includes/footer.php';
    ?>

    <script src="register_sys.js" type="module"></script>
</body>

</html>