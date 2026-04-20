<?php
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['ok' => false, 'error' => 'Metodo non consentito'], 405);
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    jsonOut(['ok' => false, 'error' => 'Payload JSON non valido'], 400);
}

// ── Leggi e valida campi ─────────────────────────────────────
$postazioneIdRaw  = $body['postazione_id'] ?? null;
$tipoPrenotazione = sanitizeStr($body['tipo_prenotazione'] ?? '', 30);
$dataInizio       = sanitizeStr($body['data_inizio'] ?? '', 10);
$dataFine         = sanitizeStr($body['data_fine'] ?? '', 10);
$nome             = sanitizeStr($body['nome'] ?? '', 100);
$cognome          = sanitizeStr($body['cognome'] ?? $body['nome'] ?? '', 100);
$email            = sanitizeStr($body['email'] ?? 'cassa@lidotorreconca.it', 255);
$telefono         = sanitizeStr($body['telefono'] ?? '0000', 25);
$adulti           = max(1, min(10, (int)($body['adulti'] ?? 2)));
$bambini          = max(0, min(10, (int)($body['bambini'] ?? 0)));
$extraLettino     = !empty($body['extra_lettino']) ? 1 : 0;
$navetta          = !empty($body['navetta']) ? 1 : 0;
$note             = sanitizeStr($body['note'] ?? '', 1000);
$daAdmin          = !empty($body['da_admin']);

// Validazioni base
if (!$nome) {
    jsonOut(['ok' => false, 'error' => 'Nome obbligatorio'], 400);
}

$tipiValidi = ['giornata_intera', 'mezza_giornata', 'settimanale', 'mensile'];
if (!in_array($tipoPrenotazione, $tipiValidi)) {
    jsonOut(['ok' => false, 'error' => 'Tipo prenotazione non valido'], 400);
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataInizio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFine)) {
    jsonOut(['ok' => false, 'error' => 'Date non valide (formato YYYY-MM-DD)'], 400);
}

if ($dataFine < $dataInizio) {
    jsonOut(['ok' => false, 'error' => 'La data fine deve essere >= data inizio'], 400);
}

// Risolvi postazione_id
// Il client può passare l'ID diretto (da mappa.php) o il numero globale (da prenotazioni.php)
$postazioneId = (int)$postazioneIdRaw;
$postazione = null;

if ($postazioneId > 0) {
    // Prova prima come ID diretto
    $postazione = fetchOne(
        "SELECT id, fila, numero, numero_globale, stato FROM postazioni WHERE id = ?",
        [$postazioneId]
    );
    // Se non trovato, prova come numero_globale
    if (!$postazione) {
        $postazione = fetchOne(
            "SELECT id, fila, numero, numero_globale, stato FROM postazioni WHERE numero_globale = ?",
            [$postazioneId]
        );
    }
}

if (!$postazione) {
    jsonOut(['ok' => false, 'error' => 'Postazione non trovata'], 404);
}

if ($postazione['stato'] === 'manutenzione') {
    jsonOut(['ok' => false, 'error' => 'Postazione in manutenzione, non prenotabile'], 409);
}

// Verifica disponibilità
if (!postazioneDisponibile($postazione['id'], $dataInizio, $dataFine)) {
    jsonOut(['ok' => false, 'error' => 'Postazione non disponibile per il periodo selezionato'], 409);
}

// Calcola prezzo
$giorni       = giorniTra($dataInizio, $dataFine);
$prezzoTotale = calcolaPrezzo($tipoPrenotazione, $giorni, (bool)$extraLettino, (bool)$navetta);

// Genera codice
$codice = generaCodice();

// Email default se vuota o non valida
if (!$email || !validEmail($email)) {
    $email = 'cassa@lidotorreconca.it';
}

// Inserisci – il POS crea sempre prenotazioni confermate
query(
    "INSERT INTO prenotazioni
        (codice, postazione_id, tipo_prenotazione, data_inizio, data_fine,
         nome, cognome, email, telefono, adulti, bambini,
         extra_lettino, navetta, note, prezzo_totale, stato)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'confermata')",
    [
        $codice,
        $postazione['id'],
        $tipoPrenotazione,
        $dataInizio,
        $dataFine,
        $nome,
        $cognome,
        $email,
        $telefono,
        $adulti,
        $bambini,
        $extraLettino,
        $navetta,
        $note,
        $prezzoTotale,
    ]
);

$prenId = (int)lastId();

jsonOut([
    'ok'           => true,
    'id'           => $prenId,
    'codice'       => $codice,
    'postazione'   => $postazione['fila'] . $postazione['numero'],
    'fila'         => $postazione['fila'],
    'numero'       => (int)$postazione['numero'],
    'tipo'         => $tipoPrenotazione,
    'data_inizio'  => $dataInizio,
    'data_fine'    => $dataFine,
    'giorni'       => $giorni,
    'prezzo_totale'=> $prezzoTotale,
    'stato'        => 'confermata',
]);
