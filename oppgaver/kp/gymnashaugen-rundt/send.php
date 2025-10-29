<?php
// Fange opp alle feil og warnings midlertidig for å ikkje bryte JSON
set_error_handler(function($errno, $errstr, $errfile, $errline){
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Det oppstod ein intern feil. Vennligst prøv igjen seinare."
    ]);
    exit;
});
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL) {
        header('Content-Type: application/json');
        echo json_encode([
            "success" => false,
            "message" => "Det oppstod ein intern feil. Vennligst prøv igjen seinare."
        ]);
        exit;
    }
});

header('Content-Type: application/json');

$to = "emil.v.soldal@gmail.com";
$subject = "Påmelding: Gymnashaugen Rundt";

function sendResponse($success, $message) {
    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Ugyldig forespørsel.");
}

// Hent og sanitér data
$navn = htmlspecialchars(strip_tags($_POST['navn'] ?? ''));
$bedrift = htmlspecialchars(strip_tags($_POST['bedrift'] ?? ''));
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$melding = htmlspecialchars(strip_tags($_POST['melding'] ?? ''));

// Enkel validering
if (empty($navn) || empty($bedrift) || empty($email) || empty($melding)) {
    sendResponse(false, "Vennligst fyll ut alle felt.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "Ugyldig e-postadresse.");
}

// Sett opp e-postinnhald
$body = "Ny påmelding fra Gymnashaugen Rundt:\n\n";
$body .= "Navn: $navn\n";
$body .= "Bedrift: $bedrift\n";
$body .= "E-post: $email\n\n";
$body .= "Melding:\n$melding\n";

// $headers = "From: $email\r\n";
$headers = "From: <no-reply@elevweb.no>\r\n";
$headers .= "Reply-To: $email\r\n";

if (!mail($to, $subject, $body, $headers)) {
    sendResponse(false, "Det oppstod ein intern feil. Vennligst prøv igjen seinare.");
}

// Suksess
sendResponse(true, "Takk for påmeldinga, $navn! Me har motteke skjemaet ditt.");

// NB: Ingen closing PHP tag