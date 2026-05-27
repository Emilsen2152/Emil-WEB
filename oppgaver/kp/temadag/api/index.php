<?php

// DB Structure (for reference):
/*
CREATE TABLE `activities` (
  `activity_id` int UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `organizer_id` int UNSIGNED NOT NULL,
  `max_participants` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `organizers` (
  `organizer_id` int UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `token` char(50) NOT NULL,
  `super_user` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `participants` (
  `participant_id` int UNSIGNED NOT NULL,
  `name` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `hash` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `registrations` (
  `registration_id` int UNSIGNED NOT NULL,
  `activity_id` int UNSIGNED NOT NULL,
  `participant_id` int UNSIGNED NOT NULL,
  `priority` varchar(1) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
*/

declare(strict_types=1);

require_once __DIR__ . '/../../../../api/bootstrap.php';
require_once __DIR__ . '/bootstrap.php';

/* ============================================================
   Basic headers (CORS + JSON)
   ============================================================ */

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowOrigin = $origin !== '' ? $origin : '*';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . $allowOrigin);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,OPTIONS');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/* ============================================================
   Utilities
   ============================================================ */

function method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

/**
 * Støttar både clean URLs og /index.php/... og strippar mapper
 */
function path(): string
{
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $uriPath = preg_replace('#/+#', '/', $uriPath);

    $uriPath = str_replace('/emil/', '/', $uriPath);
    $uriPath = str_replace('/oppgaver/', '/', $uriPath);
    $uriPath = str_replace('/kp/', '/', $uriPath);
    $uriPath = str_replace('/temadag/', '/', $uriPath);
    $uriPath = str_replace('/api/', '/', $uriPath);

    if (str_starts_with($uriPath, '/index.php')) {
        $uriPath = substr($uriPath, strlen('/index.php'));
        if ($uriPath === '') $uriPath = '/';
    }

    if ($uriPath === '' || $uriPath[0] !== '/') $uriPath = '/' . $uriPath;
    return $uriPath;
}

/**
 * Matchar ruter som /activities/{id} og fyller $params
 */
function match_route(string $pattern, string $actualPath, array &$params): bool
{
    $params = [];
    $regex = '#^' . preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';

    if (!preg_match($regex, $actualPath, $m)) return false;

    foreach ($m as $k => $v) {
        if (is_string($k)) $params[$k] = $v;
    }
    return true;
}

function respond(array $res): void
{
    json_response((int)($res['status'] ?? 200), $res);
}

/* ============================================================
   Routes
   ============================================================ */

$m = method();
$p = path();
$params = [];

/* --- 1. AUTENTISERING (LÆRARAR) --- */

// GET /organizer (Sjekk innlogga status)
if ($m === 'GET' && $p === '/organizer') {
    $organizer = current_temadag_organizer($pdo, $config);
    if (!$organizer) {
        respond(create_response(false, null, 'Unauthorized', 401));
        exit;
    }
    unset($organizer['token'], $organizer['password_hash']);
    respond(create_response(true, ['organizer' => $organizer]));
    exit;
}

// POST /login (Logg inn)
if ($m === 'POST' && $p === '/login') {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    if (!is_string($username) || !is_string($password)) {
        respond(create_response(false, null, 'Ugyldig input', 400));
        exit;
    }

    $stmt = $pdo->prepare('SELECT * FROM organizers WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $organizer = $stmt->fetch();

    if (!$organizer || !password_verify($password, $organizer['password_hash'])) {
        respond(create_response(false, null, 'Feil brukarnamn eller passord', 401));
        exit;
    }

    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare('UPDATE organizers SET token = ? WHERE organizer_id = ?');
    $stmt->execute([$token, $organizer['organizer_id']]);

    $cookieConfig = $config['cookie'];
    setcookie($cookieConfig['name'], $token, [
        'expires' => time() + $cookieConfig['maxAge'],
        'path' => '/',
        'domain' => $cookieConfig['domain'],
        'secure' => $cookieConfig['secure'],
        'httponly' => true,
        'samesite' => $cookieConfig['samesite']
    ]);

    respond(create_response(true, [
        'organizer' => [
            'organizer_id' => $organizer['organizer_id'],
            'name' => $organizer['name'],
            'username' => $organizer['username'],
            'super_user' => $organizer['super_user'] == 1
        ]
    ]));
    exit;
}

// POST /logout (Logg ut)
if ($m === 'POST' && $p === '/logout') {
    $organizer = current_temadag_organizer($pdo, $config);
    if ($organizer) {
        $stmt = $pdo->prepare('UPDATE organizers SET token = NULL WHERE organizer_id = ?');
        $stmt->execute([$organizer['organizer_id']]);
    }

    $cookieConfig = $config['cookie'];
    setcookie($cookieConfig['name'], '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => $cookieConfig['domain'],
        'secure' => $cookieConfig['secure'],
        'httponly' => true,
        'samesite' => $cookieConfig['samesite']
    ]);

    respond(create_response(true, null, 'Logga ut', 200));
    exit;
}


/* --- 2. SUPERBRUKAR-FUNKSJONALITET --- */

// POST /organizers (Superbrukar kan opprette nye lærarar/rektor-kontoar)
if ($m === 'POST' && $p === '/organizers') {
    $organizer = current_temadag_organizer($pdo, $config);
    if (!$organizer || empty($organizer['super_user'])) {
        respond(create_response(false, null, 'Kun superbrukar har tilgang til å opprette brukarar', 403));
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? null;
    $username = $input['username'] ?? null;
    $password = $input['password'] ?? null;
    $is_super = isset($input['super_user']) ? (int)$input['super_user'] : 0;

    if (!$name || !$username || !$password) {
        respond(create_response(false, null, 'Manglar namn, brukarnamn eller passord', 400));
        exit;
    }

    $stmt = $pdo->prepare('SELECT organizer_id FROM organizers WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        respond(create_response(false, null, 'Brukarnamnet er alt i bruk', 400));
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO organizers (name, username, password_hash, token, super_user) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$name, $username, $passwordHash, bin2hex(random_bytes(16)), $is_super]);

    respond(create_response(true, ['organizer_id' => $pdo->lastInsertId()], 'Brukar oppretta', 201));
    exit;
}


/* --- 3. ADMINISTRASJON AV AKTIVITETAR --- */

// GET /activities (Hent aktivitetar - Lærar ser sine eigne, Superbrukar ser alle, Offentleg ser alt)
if ($m === 'GET' && $p === '/activities') {
    respond(get_admin_activities($pdo, $config));
}

if ($m === 'GET' && $p === '/activities/all') {
    
    respond(get_activities($pdo, $config));
}

// POST /activities (Opprett aktivitet)
if ($m === 'POST' && $p === '/activities') {
    $organizer = current_temadag_organizer($pdo, $config);
    if (!$organizer) {
        respond(create_response(false, null, 'Unauthorized', 401));
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? null;
    $description = $input['description'] ?? '';
    $start_time = $input['start_time'] ?? null;
    $end_time = $input['end_time'] ?? null;
    $max_participants = $input['max_participants'] ?? null;

    if (!$name || !$start_time || !$end_time || $max_participants === null) {
        respond(create_response(false, null, 'Manglar obligatoriske felt', 400));
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO activities (name, description, start_time, end_time, organizer_id, max_participants) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $description, $start_time, $end_time, $organizer['organizer_id'], (int)$max_participants]);
    
    respond(create_response(true, ['activity_id' => $pdo->lastInsertId()], 'Aktivitet oppretta', 201));
    exit;
}

// PUT /activities/{id} (Endre aktivitet)
if ($m === 'PUT' && match_route('/activities/{id}', $p, $params)) {
    $organizer = current_temadag_organizer($pdo, $config);
    if (!$organizer) {
        respond(create_response(false, null, 'Unauthorized', 401));
        exit;
    }

    $activityId = $params['id'];
    $stmt = $pdo->prepare('SELECT * FROM activities WHERE activity_id = ? LIMIT 1');
    $stmt->execute([$activityId]);
    $activity = $stmt->fetch();

    if (!$activity) {
        respond(create_response(false, null, 'Aktivitet ikkje funnen', 404));
        exit;
    }

    if (empty($organizer['super_user']) && $activity['organizer_id'] != $organizer['organizer_id']) {
        respond(create_response(false, null, 'Du har ikkje tilgang til å endre denne aktiviteten', 403));
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? $activity['name'];
    $description = $input['description'] ?? $activity['description'];
    $start_time = $input['start_time'] ?? $activity['start_time'];
    $end_time = $input['end_time'] ?? $activity['end_time'];
    $max_participants = $input['max_participants'] ?? $activity['max_participants'];

    $stmt = $pdo->prepare('UPDATE activities SET name = ?, description = ?, start_time = ?, end_time = ?, max_participants = ? WHERE activity_id = ?');
    $stmt->execute([$name, $description, $start_time, $end_time, (int)$max_participants, $activityId]);

    respond(create_response(true, null, 'Aktivitet oppdatert'));
    exit;
}

// DELETE /activities/{id} (Slett aktivitet)
if ($m === 'DELETE' && match_route('/activities/{id}', $p, $params)) {
    $organizer = current_temadag_organizer($pdo, $config);
    if (!$organizer) {
        respond(create_response(false, null, 'Unauthorized', 401));
        exit;
    }

    $activityId = $params['id'];
    $stmt = $pdo->prepare('SELECT * FROM activities WHERE activity_id = ? LIMIT 1');
    $stmt->execute([$activityId]);
    $activity = $stmt->fetch();

    if (!$activity) {
        respond(create_response(false, null, 'Aktivitet ikkje funnen', 404));
        exit;
    }

    if (empty($organizer['super_user']) && $activity['organizer_id'] != $organizer['organizer_id']) {
        respond(create_response(false, null, 'Forbidden', 403));
        exit;
    }

    $pdo->beginTransaction();
    try {
        // Fjern tilhøyrande registreringar først pga fremmednøkkel
        $stmt = $pdo->prepare('DELETE FROM registrations WHERE activity_id = ?');
        $stmt->execute([$activityId]);

        $stmt = $pdo->prepare('DELETE FROM activities WHERE activity_id = ?');
        $stmt->execute([$activityId]);

        $pdo->commit();
        respond(create_response(true, null, 'Aktivitet sletta'));
    } catch (Exception $e) {
        $pdo->rollBack();
        respond(create_response(false, null, 'Kunne ikkje slette aktivitet', 500));
    }
    exit;
}


/* --- 4. ELEVSIDE: PÅMELDING & GENERELL OPPDATERING VIA HASH --- */

// GET /registration-by-hash (Hent eksisterande påmelding for utfylling i skjema)
if ($m === 'GET' && $p === '/registration-by-hash') {
    $hash = $_GET['hash'] ?? '';
    if (empty($hash)) {
        respond(create_response(false, null, 'Manglar hash', 400));
        exit;
    }

    $stmt = $pdo->prepare('SELECT participant_id, name, email FROM participants WHERE hash = ? LIMIT 1');
    $stmt->execute([$hash]);
    $participant = $stmt->fetch();

    if (!$participant) {
        respond(create_response(false, null, 'Ugyldig eller utgått lenke', 404));
        exit;
    }

    // Hent dei 3 eksisterande aktivitetane sortert etter prioritet
    $stmt = $pdo->prepare('SELECT activity_id, priority FROM registrations WHERE participant_id = ? ORDER BY priority ASC');
    $stmt->execute([$participant['participant_id']]);
    $regs = $stmt->fetchAll();

    respond(create_response(true, [
        'participant' => $participant,
        'choices' => array_column($regs, 'activity_id')
    ]));
    exit;
}

// POST /register (Førstegongs påmelding: Namn, E-post og nøyaktig 3 val i prioritert rekkefølgje)
if ($m === 'POST' && $p === '/register') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? null;
    $email = $input['email'] ?? null;
    $choices = $input['choices'] ?? []; // Forventar array med 3 ID-ar: [Aktivitet_1, Aktivitet_2, Aktivitet_3]

    if (!$name || !$email || count($choices) !== 3) {
        respond(create_response(false, null, 'Du må fylle ut namn, e-post og velje nøyaktig 3 aktivitetar', 400));
        exit;
    }

    if (count(array_unique($choices)) !== 3) {
        respond(create_response(false, null, 'Du kan ikkje velje same aktivitet fleire gonger', 400));
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Sjekk om e-post finst frå før for å unngå dobbeltlagring av elevinformasjon
        $stmt = $pdo->prepare('SELECT participant_id, hash FROM participants WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $participant = $stmt->fetch();

        if ($participant) {
            // Sjekk om eleven allereie har aktive registreringar
            $stmt = $pdo->prepare('SELECT registration_id FROM registrations WHERE participant_id = ? LIMIT 1');
            $stmt->execute([$participant['participant_id']]);
            if ($stmt->fetch()) {
                respond(create_response(false, null, 'Denne e-posten er alt registrert. Bruk endrings-lenka du fekk.', 400));
                $pdo->rollBack();
                exit;
            }
            $participantId = (int)$participant['participant_id'];
            $hash = $participant['hash'];
        } else {
            // Generer tilfeldig hash for endrings-link
            $hash = bin2hex(random_bytes(16));
            $stmt = $pdo->prepare('INSERT INTO participants (name, email, hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hash]);
            $participantId = (int)$pdo->lastInsertId();
        }

        // Lagre dei 3 registreringane (Prioritet 1, 2 og 3 med NOW() for første-mann-til-mølla)
        $stmt = $pdo->prepare('INSERT INTO registrations (activity_id, participant_id, priority, timestamp) VALUES (?, ?, ?, NOW())');
        foreach ($choices as $index => $activityId) {
            $priority = (string)($index + 1); // '1', '2', '3'
            $stmt->execute([(int)$activityId, $participantId, $priority]);
        }

        $pdo->commit();
        respond(create_response(true, ['hash' => $hash], 'Påmelding registrert!', 201));
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        respond(create_response(false, null, 'Feil under lagring av påmelding', 500));
    }
    exit;
}

// PUT /registrations/update-registration (Generell oppdatering av heile påmeldinga via hash)
if ($m === 'PUT' && $p === '/registrations/update-registration') {
    $input = json_decode(file_get_contents('php://input'), true);
    $hash = $input['hash'] ?? null;
    $name = $input['name'] ?? null;
    $email = $input['email'] ?? null;
    $choices = $input['choices'] ?? []; // Ny array: [Aktivitet_1, Aktivitet_2, Aktivitet_3]

    if (!$hash || !$name || !$email || count($choices) !== 3) {
        respond(create_response(false, null, 'Ugyldig forespørsel. Manglar data.', 400));
        exit;
    }

    if (count(array_unique($choices)) !== 3) {
        respond(create_response(false, null, 'Du kan ikkje velje same aktivitet fleire gonger', 400));
        exit;
    }

    $stmt = $pdo->prepare('SELECT participant_id FROM participants WHERE hash = ? LIMIT 1');
    $stmt->execute([$hash]);
    $participant = $stmt->fetch();

    if (!$participant) {
        respond(create_response(false, null, 'Ugyldig eller utgått lenke', 404));
        exit;
    }

    $participantId = (int)$participant['participant_id'];

    try {
        $pdo->beginTransaction();

        // 1. Oppdater namn og e-post om eleven retta på dette i skjemaet sitt
        $stmt = $pdo->prepare('UPDATE participants SET name = ?, email = ? WHERE participant_id = ?');
        $stmt->execute([$name, $email, $participantId]);

        // 2. Slett dei gamle prioriteringane
        $stmt = $pdo->prepare('DELETE FROM registrations WHERE participant_id = ?');
        $stmt->execute([$participantId]);

        // 3. Set inn dei nye generelle vala med oppdatert tidsstempel
        $stmt = $pdo->prepare('INSERT INTO registrations (activity_id, participant_id, priority, timestamp) VALUES (?, ?, ?, NOW())');
        foreach ($choices as $index => $activityId) {
            $priority = (string)($index + 1);
            $stmt->execute([(int)$activityId, $participantId, $priority]);
        }

        $pdo->commit();
        respond(create_response(true, null, 'Påmeldinga di har vorte oppdatert!'));
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        respond(create_response(false, null, 'Kunne ikkje oppdatere registreringa', 500));
    }
    exit;
}


/* --- 5. VISNING AV DELTAKARLISTER FOR LÆRARAR --- */

// GET /activities/{id}/participants (Hent deltakarliste for ein aktivitet sortert etter tid/førstemann til mølla)
if ($m === 'GET' && match_route('/activities/{id}/participants', $p, $params)) {
    $organizer = current_temadag_organizer($pdo, $config);
    if (!$organizer) {
        respond(create_response(false, null, 'Unauthorized', 401));
        exit;
    }

    $activityId = $params['id'];

    $stmt = $pdo->prepare('SELECT organizer_id FROM activities WHERE activity_id = ? LIMIT 1');
    $stmt->execute([$activityId]);
    $activity = $stmt->fetch();

    if (!$activity) {
        respond(create_response(false, null, 'Aktivitet ikkje funnen', 404));
        exit;
    }

    if (empty($organizer['super_user']) && $activity['organizer_id'] != $organizer['organizer_id']) {
        respond(create_response(false, null, 'Forbidden', 403));
        exit;
    }

    // Hentar ut påmeldte elevar sortert etter registrerings-tidspunkt og kva prioritet dei gav aktiviteten
    $stmt = $pdo->prepare('
        SELECT p.name, p.email, r.priority, r.timestamp 
        FROM registrations r
        JOIN participants p ON r.participant_id = p.participant_id
        WHERE r.activity_id = ?
        ORDER BY r.timestamp ASC, r.priority ASC
    ');
    $stmt->execute([$activityId]);
    $list = $stmt->fetchAll();

    respond(create_response(true, ['participants' => $list]));
    exit;
}

/* ============================================================
   Fallback (404)
   ============================================================ */

json_response(404, create_response(false, null, 'Not found: ' . $m . ' ' . $p, 404));