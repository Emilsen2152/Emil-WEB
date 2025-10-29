<?php
// Fange opp alle feil og warnings midlertidig for å ikkje bryte JSON
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Det oppstod ein intern feil. Vennligst prøv igjen seinare."
    ]);
    exit;
});
register_shutdown_function(function () {
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

function sendResponse($success, $message)
{
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

// HTML-formatert e-post
$body = '
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        h2 { color: #00529B; }
        p { line-height: 1.5; }
        .info { background-color: #f7f9fc; padding: 10px 15px; border-left: 3px solid #00529B; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2>Ny påmelding: Gymnashaugen Rundt</h2>
    <div class="info">
        <p><strong>Navn:</strong> ' . $navn . '</p>
        <p><strong>Bedrift:</strong> ' . $bedrift . '</p>
        <p><strong>E-post:</strong> ' . $email . '</p>
    </div>
    <p><strong>Melding:</strong></p>
    <p>' . nl2br($melding) . '</p>
</body>
</html>
';

// Headers med HTML-støtte
$headers = "From: Gymnashaugen Rundt <no-reply@elevweb.no>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Send e-post
if (!mail($to, $subject, $body, $headers)) {
    sendResponse(false, "Det oppstod ein intern feil. Vennligst prøv igjen seinare.");
}

// Suksess
sendResponse(true, "Takk for påmeldinga, $navn! Me har motteke skjemaet ditt.");
