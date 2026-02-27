<?php
require_once __DIR__ . '/../api/bootstrap.php';
require_once __DIR__ . '/../../../../api/bootstrap.php';

$user = current_user($pdo, $config);

$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$segments = explode('/', trim($uriPath, '/'));

$to_do_listId = end($segments);

if (!ctype_digit($to_do_listId)) {
    http_response_code(400);
    exit('Invalid to-do list ID');
}

$to_do_listId = (int)$to_do_listId;

$to_do_list_items_result = get_to_do_list_items($pdo, $config, $to_do_listId);

if (!$to_do_list_items_result['success']) {
    http_response_code(404);
    echo "To-do list not found";
    exit;
}

$to_do_list = $to_do_list_items_result['data']['list'];

$to_do_list_items = $to_do_list_items_result['data']['items'];

$hasOwner  = !empty($to_do_list['owner_id']);
$isOwner   = ($user && $hasOwner && (int)$to_do_list['owner_id'] === (int)$user['id']);

// Deling: berre eigar av eigd liste (APIen din krev eigar + owner_id)
$canShare  = $isOwner;

// Sletting:
// - dersom lista har owner_id: berre eigar
// - dersom lista ikkje har owner_id: alle kan slette (men berre om public)
$canDelete = $hasOwner ? $isOwner : empty($to_do_list['private']);
?>
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
            <div class="d-flex justify-content-center gap-2 mt-3 flex-wrap">
                <?php if ($canShare): ?>
                    <button class="btn btn-outline-primary" type="button"
                        data-bs-toggle="modal" data-bs-target="#shareModal">
                        Del lista
                    </button>
                <?php endif; ?>

                <?php if ($canDelete): ?>
                    <button class="btn btn-outline-danger" type="button"
                        data-bs-toggle="modal" data-bs-target="#deleteListModal">
                        Slett lista
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6 offset-md-3">
                <form id="add-item-form">
                    <input type="hidden" name="to_do_list_id" value="<?= $to_do_listId ?>">
                    <div class="input-group mb-3">
                        <input type="text" name="description" class="form-control" placeholder="Ny oppgåve..." required>
                        <button class="btn btn-primary" type="submit">Legg til</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6 offset-md-3">
                <div id="items" class="list-group"></div>
                <div id="items-empty" class="text-muted mt-2 d-none">Ingen oppgåver endå.</div>
            </div>
        </div>
    </div>
    <?php if ($canShare): ?>
        <!-- Share Modal -->
        <div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Del sjekklista</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Lukk"></button>
                    </div>

                    <div class="modal-body">
                        <div id="share-alert" class="alert d-none" role="alert"></div>

                        <form id="share-form" class="d-grid gap-2">
                            <label class="form-label" for="share-username">Brukarnamn</label>
                            <input class="form-control" id="share-username" name="username" placeholder="t.d. ola_nordmann" required>
                            <button class="btn btn-primary" type="submit">Del</button>
                        </form>

                        <small class="text-muted d-block mt-2">
                            Berre eigar kan dela lister som er knytte til konto.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($canDelete): ?>
        <!-- Delete List Modal -->
        <div class="modal fade" id="deleteListModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Slett lista?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Lukk"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-0">
                            Er du sikker på at du vil sletta <strong><?= htmlspecialchars($to_do_list['name']) ?></strong>?
                            Dette slettar òg alle oppgåver i lista.
                        </p>
                        <div id="delete-list-alert" class="alert d-none mt-3" role="alert"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Avbryt</button>
                        <button type="button" class="btn btn-danger" id="confirm-delete-list">
                            Ja, slett
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php
    include '../../../../includes/footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="to_do_list.js"></script>
</body>

</html>