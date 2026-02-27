<?php
require_once __DIR__ . '/../api/bootstrap.php';
require_once __DIR__ . '/../../../../api/bootstrap.php';

$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (preg_match('#/to_do_list/(\d+)$#', $uriPath, $matches)) {
    $to_do_listId = (int)$matches[1];
} else {
    http_response_code(400);
    echo "Invalid to-do list URL";
    exit;
}

$to_do_list_items_result = get_to_do_list_items($pdo, $config, $to_do_listId);

if (!$to_do_list_items_result['success']) {
    http_response_code(404);
    echo "To-do list not found";
    exit;
}

$to_do_list = $to_do_list_items_result['data']['list'];

$to_do_list_items = $to_do_list_items_result['data']['items'];
?>

<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($to_do_list['name']) ?> - d01t</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="bg-dark">
    <?php
    include '../../../../includes/navbar.php';
    ?>

    <div class="container-fluid bg-light py-2">
        <div class="container">
            <h1 class="mt-4 p-2 text-center text-primary"><?= htmlspecialchars($to_do_list['name']) ?></h1>
            <p class="lead text-center">Dette er <?= $to_do_list['private'] ? ' den private' : '' ?> sjekklista "<?= htmlspecialchars($to_do_list['name']) ?>".</p>
        </div>
    </div>
    <?php
    include '../../../../includes/footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
