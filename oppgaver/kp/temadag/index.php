<?php

require_once __DIR__ . '/api/bootstrap.php';

$activitiesResponse = get_activities($pdo, $config);

/*
Planlegging frontend
Eg planlegg å ha to sider. Ei for påmelding og ei for administrasjon av aktivitetar. På sida for påmelding vil det vera eit skjema der du kan velga 3 aktivitetar i prioritert rekkefølge, dette gjer eg sidan brukaren prioriterer basert på dei tre dei har mest lyst til å vera på.
Generelt vil skjemaet ha denne strukturen:
Namn
E-post
Aktivitet 1
Aktivitet 2
Aktivitet 3.
Administrasjonssida vil ha eit skjema for å opprette/endre aktivitetar.
Skjemaet vil ha denne strukturen:
Namn på aktiviteten
Beskrivelse
Start-tid
Slutt-tid
Maks antal deltakarar.
Under skjemaet vil det vera ei liste med aktivitetar den brukaren har oppretta. Der kan dei trykke på ein knapp for å laste den inn i skjemaet over for å endre den.
Om den pålogga brukaren er superbrukar vil den og få opp eit skjema for å opprette brukarar:
Namn
Brukarnamn
Passord
Om brukaren er ein superbrukar
*/
?>

<!DOCTYPE html>
<html lang="no">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temadag</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="temadag.css">
</head>

<body class="bg-light">
</body>