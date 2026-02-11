<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Ugyldig forespørsel.'
    ]);
    exit;
}

$navn = $_POST['navn'] ?? '';
$epost = $_POST['epost'] ?? '';
$telefon = $_POST['telefon'] ?? '';

if (!$navn || !$epost || !$telefon) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Alle felt må fyllast ut.'
    ]);
    exit;
}

$adminEmail = "emil.v.soldal@gmail.com";
$subjectAdmin = "Ny påmelding - CRAZYGAMER";

$bodyAdmin = "
<!DOCTYPE html>
<html lang='no'>
<body style=\"background:#0d0d0d;font-family:'Segoe UI', Roboto, sans-serif;color:white;padding:20px;\">
    <table width='100%' cellpadding='0' cellspacing='0' style='padding:20px 0;'>
        <tr>
            <td align='center'>
                <table width='600' cellpadding='0' cellspacing='0' style='background:#1a1a1a;border-radius:15px;padding:30px;'>
                    <tr>
                        <td align='center'>
                            <h2 style='font-size:28px;text-shadow:0 0 8px rgba(111,0,255,0.7); color:white;'>
                                <span style='color:#6f00ff;'><i>CRAZY</i></span>GAMER - Ny påmelding
                            </h2>

                            <p style='font-size:16px;color:white;margin-top:10px;'>
                                Det har kome inn ei ny påmelding:
                            </p>
                            <table style='width:100%;border-collapse:collapse;margin-top:20px;'>
                                <tr style='background:#2a2a2a;'>
                                    <td style='padding:8px;border:1px solid #333;'>Namn</td>
                                    <td style='padding:8px;border:1px solid #333;'>{$navn}</td>
                                </tr>
                                <tr>
                                    <td style='padding:8px;border:1px solid #333;'>E-post</td>
                                    <td style='padding:8px;border:1px solid #333;'>{$epost}</td>
                                </tr>
                                <tr style='background:#2a2a2a;'>
                                    <td style='padding:8px;border:1px solid #333;'>Telefon</td>
                                    <td style='padding:8px;border:1px solid #333;'>{$telefon}</td>
                                </tr>
                            </table>
                            <a href='https://elevweb.no/emil/oppgaver/kp/crazygamer' style='display:inline-block;margin-top:25px;padding:12px 30px;background:#6f00ff;color:white;text-decoration:none;border-radius:10px;box-shadow:0 0 15px #6f00ff;font-weight:bold;'>Gå til nettsida</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html;charset=UTF-8\r\n";
$headers .= "From: CRAZYGAMER <no-reply-crazygamer@elevweb.no>\r\n";

mail($adminEmail, $subjectAdmin, $bodyAdmin, $headers . "Reply-To: {$epost}\r\n");

$subjectUser = "Takk for påmeldinga - CRAZYGAMER";

$bodyUser = "
<!DOCTYPE html>
<html lang='no'>
<body style=\"background:#0d0d0d;font-family:'Segoe UI', Roboto, sans-serif;color:white;padding:20px;\">
    <table width='100%' cellpadding='0' cellspacing='0' style='padding:20px 0;'>
        <tr>
            <td align='center'>
                <table width='600' cellpadding='0' cellspacing='0' style='background:#1a1a1a;border-radius:15px;padding:30px;'>
                    <tr>
                        <td align='center'>
                            <h2 style='font-size:28px;text-shadow:0 0 8px rgba(111,0,255,0.7); color:white;'>
                                <span style='color:#6f00ff;'><i>CRAZY</i></span>GAMER
                            </h2>
                            <p style='font-size:16px;color:white;margin-top:10px;'>
                                Hei {$navn}, takk for at du meldte deg på!
                            </p>
                            <p style='font-size:16px;color:white;'>
                                Me kontaktar deg så snart me har behandla søknaden din.
                            </p>
                            <a href='https://elevweb.no/emil/oppgaver/kp/crazygamer' style='display:inline-block;margin-top:25px;padding:12px 30px;background:#6f00ff;color:white;text-decoration:none;border-radius:10px;box-shadow:0 0 15px #6f00ff;font-weight:bold;'>Besøk nettsida</a>
                            <p style='margin-top:25px;font-size:13px;color:#bbb;'>
                                Helsing,<br>CRAZYGAMER-teamet
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
";

mail($epost, $subjectUser, $bodyUser, $headers);

echo json_encode([
    'status' => 'success',
    'message' => 'Takk for at du meldte interesse for å melda deg på, ' . htmlspecialchars($navn) . '! Me tek kontakt snart.'
]);
