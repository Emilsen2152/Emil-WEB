<?php

function sendMail($navn, $bedrift, $email, $subject, $melding) {
    $headers = "From: Gymnashaugen Rundt <no-reply@elevweb.no>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

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

    return mail('emil.v.soldal@gmail.com', $subject, $body, $headers);
}   