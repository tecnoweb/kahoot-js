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

// ── Estrai e sanitizza ────────────────────────────────────────
$codice = strtoupper(sanitizeStr($body['codice'] ?? '', 20));
$email  = sanitizeStr($body['email']  ?? '', 255);

if ($codice === '') {
    jsonOut(['ok' => false, 'errore' => 'Il codice prenotazione è obbligatorio.'], 400);
}
if (!validEmail($email)) {
    jsonOut(['ok' => false, 'errore' => 'Indirizzo email non valido.'], 400);
}

// ── Cerca prenotazione ────────────────────────────────────────
$prenotazione = getPrenotazioneByCodice($codice);

// Risposta generica per non rivelare l'esistenza del codice
if (!$prenotazione) {
    jsonOut(['ok' => false, 'errore' => 'Prenotazione non trovata o dati non corrispondenti.'], 404);
}

// ── Verifica corrispondenza email ─────────────────────────────
// Confronto case-insensitive
if (strtolower($prenotazione['email']) !== strtolower($email)) {
    jsonOut(['ok' => false, 'errore' => 'Prenotazione non trovata o dati non corrispondenti.'], 404);
}

// ── Verifica stato attuale ────────────────────────────────────
if ($prenotazione['stato'] === 'annullata') {
    jsonOut(['ok' => false, 'errore' => 'La prenotazione risulta già annullata.'], 409);
}

// ── Verifica che data_inizio sia ancora futura ────────────────
$oggi       = (new DateTime())->format('Y-m-d');
$dataInizio = $prenotazione['data_inizio'];

if ($dataInizio <= $oggi) {
    jsonOut([
        'ok'     => false,
        'errore' => 'Non è possibile annullare una prenotazione già iniziata o passata.',
    ], 409);
}

// ── Aggiorna stato a 'annullata' ──────────────────────────────
query(
    "UPDATE prenotazioni SET stato = 'annullata' WHERE codice = ?",
    [$codice]
);

jsonOut([
    'ok'      => true,
    'codice'  => $codice,
    'message' => 'Prenotazione annullata con successo.',
]);
