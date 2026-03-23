<?php

// api/index.php (Patch/Update)
declare(strict_types=1);

require_once __DIR__ . '/../../../../../api/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Les JSON frå body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// 2. Hent ut verdiane (kartlegg 'nr' frå JS til $startnr)
$startnr = $data['nr'] ?? null;
$navn = $data['navn'] ?? null;
$lag = $data['lag'] ?? null;

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

// 3. Oppdater databasen
try {
    $stmt = $pdo->prepare("UPDATE gr_deltakere SET `navn` = ?, `lag` = ? WHERE `start_nr` = ?");
    $stmt->execute([$navn, $lag, (int)$startnr]);
    
    // rowCount() fortel kor mange rader som faktisk blei endra.
    // Merk: Viss du lagrar nøyaktig same info som står der frå før, vil rowCount vere 0.
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Deltaker oppdatert."
        ]);
    } else {
        echo json_encode([
            "success" => true, // Me returnerer true her fordi forespørselen var vellykka, 
            "message" => "Ingen endringar gjort (eller deltaker ikkje funnen)."
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Databasefeil: Kunne ikke oppdatere deltaker. Feil: " . $e->getMessage()
    ]);
}