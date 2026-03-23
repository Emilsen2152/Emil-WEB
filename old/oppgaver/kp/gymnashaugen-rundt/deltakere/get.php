<?php

// api/index.php
declare(strict_types=1);

require_once __DIR__ . '/../../../../../api/bootstrap.php';

// Hent nr fra URL-parametre
$startnr = $_GET['nr'] ?? null;

header('Content-Type: application/json; charset=utf-8');
if ($startnr === null) {
    echo json_encode([
        "success" => false,
        "message" => "Startnummer mangler."
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

$stmt = $pdo->prepare("SELECT * FROM gr_deltakere WHERE start_nr = ?");
$stmt->execute([(int)$startnr]);
$deltaker = $stmt->fetch(PDO::FETCH_ASSOC);

if ($deltaker) {
    echo json_encode([
        "success" => true,
        "data" => $deltaker
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Deltaker ikke funnet."
    ]);
}
