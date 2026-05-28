<?php
require_once __DIR__ . '/../api/bootstrap.php';

// Viss arrangøren allereie er logga inn, send dei rett til admin.php
$organizer = current_temadag_organizer($pdo, $config);

if ($organizer) {
    header('Location: ./');
    exit;
}
?>

<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temadag - Logg inn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="bg-light d-flex align-items-center min-vh-100">

    <main class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                
                <div class="text-center mb-4">
                    <h1 class="h3 mb-1 fw-bold text-dark">Temadag Arrangør</h1>
                    <p class="text-muted">Logg inn for å administrere aktivitetar</p>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        
                        <div id="login-feedback" class="d-none mb-3"></div>

                        <form id="login-form">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Brukarnamn</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username" 
                                       placeholder="Ditt brukarnamn" 
                                       required 
                                       autofocus>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Passord</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Ditt passord" 
                                       required>
                            </div>

                            <button type="submit" id="login-submit-btn" class="btn btn-primary w-100 py-2 fw-semibold">
                                Logg inn
                            </button>
                            
                        </form>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="../index" class="text-decoration-none small text-secondary">&larr; Gå til påmelding for elevar</a>
                </div>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="login.js"></script>
</body>
</html>