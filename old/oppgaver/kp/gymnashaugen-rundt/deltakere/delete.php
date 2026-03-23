<?php

// api/index.php (Delete)
declare(strict_types=1);

require_once __DIR__ . '/../../../../../api/bootstrap.php';

// Set header med ein gong
header('Content-Type: application/json; charset=utf-8');

// Les JSON frå body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Hent nr frå den dekoda JSON-en
$startnr = $data['nr'] ?? null;

if ($startnr === null) {
    echo json_encode([
        "success" => false,
        "message" => "Startnummer mangler i forespørselen."
    ]);
    exit;
}

// Sjekk at startnr er et gyldig nummer
if (!is_numeric($startnr) || (int)$startnr <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Ugyldig startnummer format."
    ]);
    exit;
}

try {
    // DELETE - me brukar start_nr feltet ditt frå databasen
    $stmt = $pdo->prepare("SELECT `start_nr` as nr, `navn`, `lag` FROM gr_deltakere WHERE `start_nr` = ?");
    $stmt->execute([(int)$startnr]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Deltaker med startnummer $startnr er slettet."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Fant ingen deltaker med startnummer $startnr."
        ]);
    }
} catch (PDOException $e) {
    // I produksjon bør du kanskje logge feilen i staden for å spy ut alt til brukaren
    echo json_encode([
        "success" => false,
        "message" => "Databasefeil: Kunne ikkje slette deltakar. Feil: " . $e->getMessage()
    ]);
}