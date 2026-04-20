<?php
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── Solo POST ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['ok' => false, 'errore' => 'Metodo non consentito.'], 405);
}

// ── Leggi body JSON ───────────────────────────────────────────
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!is_array($body)) {
    jsonOut(['ok' => false, 'errore' => 'Body JSON non valido.'], 400);
}

// ── Verifica CSRF ─────────────────────────────────────────────
$csrf = sanitizeStr($body['csrf'] ?? '', 128);
if (!verifyCsrf($csrf)) {
    jsonOut(['ok' => false, 'errore' => 'Token CSRF non valido o scaduto.'], 403);
}

// ── Estrai e sanitizza campi ──────────────────────────────────
$postazioneId    = (int)($body['postazione_id']    ?? 0);
$tipo            = sanitizeStr($body['tipo_prenotazione'] ?? '', 30);
$dataInizio      = sanitizeStr($body['data_inizio']      ?? '', 10);
$dataFine        = sanitizeStr($body['data_fine']        ?? '', 10);
$nome            = sanitizeStr($body['nome']             ?? '', 100);
$cognome         = sanitizeStr($body['cognome']          ?? '', 100);
$email           = sanitizeStr($body['email']            ?? '', 255);
$telefono        = sanitizeStr($body['telefono']         ?? '', 20);
$adulti          = (int)($body['adulti']                 ?? 1);
$bambini         = (int)($body['bambini']                ?? 0);
$extraLettino    = !empty($body['extra_lettino']);
$navetta         = !empty($body['navetta']);
$note            = sanitizeStr($body['note']             ?? '', 1000);

// ── Validazione campi obbligatori ─────────────────────────────
$tipiValidi = ['giornata_intera', 'mezza_giornata', 'settimanale', 'mensile'];

if ($postazioneId <= 0) {
    jsonOut(['ok' => false, 'errore' => 'Postazione non valida.'], 400);
}
if (!in_array($tipo, $tipiValidi, true)) {
    jsonOut(['ok' => false, 'errore' => 'Tipo prenotazione non valido.'], 400);
}
if ($nome === '') {
    jsonOut(['ok' => false, 'errore' => 'Il nome è obbligatorio.'], 400);
}
if ($cognome === '') {
    jsonOut(['ok' => false, 'errore' => 'Il cognome è obbligatorio.'], 400);
}
if (!validEmail($email)) {
    jsonOut(['ok' => false, 'errore' => 'Indirizzo email non valido.'], 400);
}
if ($telefono !== '' && !validTel($telefono)) {
    jsonOut(['ok' => false, 'errore' => 'Numero di telefono non valido.'], 400);
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataInizio) || !strtotime($dataInizio)) {
    jsonOut(['ok' => false, 'errore' => 'Formato data_inizio non valido (YYYY-MM-DD).'], 400);
}
if ($adulti < 1 || $adulti > 10) {
    jsonOut(['ok' => false, 'errore' => 'Numero adulti non valido (1-10).'], 400);
}
if ($bambini < 0 || $bambini > 10) {
    jsonOut(['ok' => false, 'errore' => 'Numero bambini non valido (0-10).'], 400);
}

// ── Calcola/valida data_fine ──────────────────────────────────
$dtInizio = new DateTime($dataInizio);

if ($dataFine === '') {
    $dtFine = clone $dtInizio;
    if ($tipo === 'settimanale') {
        $dtFine->modify('+6 days');
    } elseif ($tipo === 'mensile') {
        $dtFine->modify('+29 days');
    }
    $dataFine = $dtFine->format('Y-m-d');
} else {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFine) || !strtotime($dataFine)) {
        jsonOut(['ok' => false, 'errore' => 'Formato data_fine non valido (YYYY-MM-DD).'], 400);
    }
    $dtFine = new DateTime($dataFine);
}

if ($dtFine < $dtInizio) {
    jsonOut(['ok' => false, 'errore' => '"data_fine" non può essere precedente a "data_inizio".'], 400);
}

// ── Controllo anticipo 60 minuti ─────────────────────────────
$now   = new DateTime();
$oggi  = $now->format('Y-m-d');

if ($dataInizio < $oggi) {
    jsonOut(['ok' => false, 'errore' => 'Non è possibile prenotare per date passate.'], 400);
}

if ($dataInizio === $oggi) {
    // Orario di riferimento per tipologia
    $orarioRif = ($tipo === 'mezza_giornata') ? ORARIO_POMERIGGIO : ORARIO_APERTURA;
    [$h, $m]   = explode(':', $orarioRif);
    $apertura  = clone $now;
    $apertura->setTime((int)$h, (int)$m, 0);
    $limite    = (clone $apertura)->modify('-' . MINUTI_ANTICIPO . ' minutes');

    if ($now >= $limite) {
        jsonOut([
            'ok'     => false,
            'errore' => 'Prenotazione non consentita: è necessario prenotare almeno ' . MINUTI_ANTICIPO . ' minuti prima dell\'apertura.',
        ], 400);
    }
}

// ── Verifica esistenza postazione e stato ────────────────────
$postazione = fetchOne(
    'SELECT id, fila, numero, numero_globale, stato FROM postazioni WHERE id = ?',
    [$postazioneId]
);
if (!$postazione) {
    jsonOut(['ok' => false, 'errore' => 'Postazione non trovata.'], 404);
}
if ($postazione['stato'] !== 'attiva') {
    jsonOut(['ok' => false, 'errore' => 'La postazione selezionata non è disponibile (in manutenzione).'], 409);
}

// ── Verifica disponibilità su tutto il range di date ─────────
$current = clone $dtInizio;
while ($current <= $dtFine) {
    $giorno = $current->format('Y-m-d');
    if (!postazioneDisponibile($postazioneId, $giorno, $giorno)) {
        jsonOut([
            'ok'     => false,
            'errore' => 'La postazione non è disponibile nel periodo selezionato.',
        ], 409);
    }
    $current->modify('+1 day');
}

// Doppia verifica con l'intero range (evita race condition)
if (!postazioneDisponibile($postazioneId, $dataInizio, $dataFine)) {
    jsonOut([
        'ok'     => false,
        'errore' => 'La postazione non è disponibile nel periodo selezionato.',
    ], 409);
}

// ── Calcola prezzo ────────────────────────────────────────────
$gg    = giorniTra($dataInizio, $dataFine);
$prezzo = calcolaPrezzo($tipo, $gg, $extraLettino, $navetta);

// ── Genera codice univoco ─────────────────────────────────────
$codice = generaCodice();

// ── Inserisci prenotazione ────────────────────────────────────
query(
    "INSERT INTO prenotazioni
        (codice, postazione_id, tipo_prenotazione, data_inizio, data_fine,
         nome, cognome, email, telefono, adulti, bambini,
         extra_lettino, navetta, note, prezzo_totale, stato, created_at)
     VALUES
        (?, ?, ?, ?, ?,
         ?, ?, ?, ?, ?, ?,
         ?, ?, ?, ?, 'in_attesa', NOW())",
    [
        $codice,
        $postazioneId,
        $tipo,
        $dataInizio,
        $dataFine,
        $nome,
        $cognome,
        $email,
        $telefono,
        $adulti,
        $bambini,
        (int)$extraLettino,
        (int)$navetta,
        $note,
        $prezzo,
    ]
);

jsonOut([
    'ok'           => true,
    'codice'       => $codice,
    'prezzo_totale' => $prezzo,
    'postazione'   => $postazione['fila'] . $postazione['numero'],
    'data_inizio'  => $dataInizio,
    'data_fine'    => $dataFine,
]);
