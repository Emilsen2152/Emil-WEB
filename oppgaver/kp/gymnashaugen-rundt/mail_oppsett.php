<?php

function sendMail($from, $subject, $message) {
    $headers = "From: Gymnashaugen Rundt <no-reply@elevweb.no>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail('emil.v.soldal@gmail.com', $subject, $message, $headers);
}   