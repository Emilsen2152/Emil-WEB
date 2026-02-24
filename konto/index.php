<?php
require_once __DIR__ . '/../api/bootstrap.php';

$user = current_user($pdo, $config);

if (!$user) {
    header('Location: ./login');
    exit;
}

$error_messages = [
    'not_admin' => 'Sida du prøver å nå er bare tilgjengelig for administratorer.',
];

$error = $_GET['error'] ?? null;
if ($error && isset($error_messages[$error])) {
    $error_message = $error_messages[$error];
} else {
    $error_message = null;
}

?>

<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Min konto - EmilWEB</title>
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
                        <h1 class="mb-4 text-center">Min konto</h1>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <p><strong>Brukarnamn:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>Brukar-ID:</strong> <?php echo htmlspecialchars($user['id']); ?></p>
                        <p><strong>Registrert:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
                        <p><strong>Siste innlogging:</strong> <?php echo htmlspecialchars($user['last_login'] ?? 'Aldri'); ?></p>
                        <button id="logout-btn" class="btn btn-danger btn-lg w-100 mt-2">
                            Logg ut
                        </button>

                        <?php
                        if (check_admin($pdo, $config)) {
                            echo '<a href="admin" class="btn btn-secondary btn-lg w-100 mt-3">Adminpanel</a>';
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php
    include '../includes/footer.php';
    ?>

    <script src="account_page.js" type="module"></script>
</body>

</html>