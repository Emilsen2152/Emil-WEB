<?php

// api/index.php (Post/Create)
declare(strict_types=1);

require_once __DIR__ . '/../../../../../api/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Les JSON frå body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// 2. Hent ut verdiane (me brukar 'nr' for å matche JS-koden din)
$startnr = $data['nr'] ?? null;
$navn = $data['navn'] ?? null;
$lag = $data['lag'] ?? null;

// Sjekk om startnummer er med
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
        "message" => "Ugyldig startnummer."
    ]);
    exit;
}

// 3. Sett inn i databasen
try {
    $stmt = $pdo->prepare("INSERT INTO gr_deltakere (`start_nr`, `navn`, `lag`) VALUES (?, ?, ?)");
    $stmt->execute([(int)$startnr, $navn, $lag]);
    
    echo json_encode([
        "success" => true,
        "message" => "Deltaker registrert."
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') { // Duplicate entry
        echo json_encode([
            "success" => false,
            "message" => "Startnummer $startnr er allerede i bruk."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Databasefeil: Kunne ikke registrere deltaker. Feil: " . $e->getMessage()
        ]);
    }
}