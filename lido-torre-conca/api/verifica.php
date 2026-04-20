<?php
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── Solo GET ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonOut(['ok' => false, 'errore' => 'Metodo non consentito.'], 405);
}

// ── Parametro codice ─────────────────────────────────────────
$codice = strtoupper(sanitizeStr($_GET['codice'] ?? '', 20));

if ($codice === '') {
    jsonOut(['ok' => false, 'errore' => 'Parametro "codice" obbligatorio.'], 400);
}

// ── Cerca prenotazione ────────────────────────────────────────
$p = getPrenotazioneByCodice($codice);

if (!$p) {
    jsonOut(['ok' => false, 'errore' => 'Prenotazione non trovata.'], 404);
}

// ── Risposta ─────────────────────────────────────────────────
jsonOut([
    'ok'           => true,
    'prenotazione' => [
        'codice'           => $p['codice'],
        'nome'             => $p['nome'],
        'cognome'          => $p['cognome'],
        'fila'             => $p['fila'],
        'numero'           => (int)$p['numero'],
        'numero_globale'   => (int)$p['numero_globale'],
        'postazione'       => $p['fila'] . $p['numero'],
        'tipo_prenotazione' => $p['tipo_prenotazione'],
        'data_inizio'      => $p['data_inizio'],
        'data_fine'        => $p['data_fine'],
        'adulti'           => (int)$p['adulti'],
        'bambini'          => (int)$p['bambini'],
        'extra_lettino'    => (bool)$p['extra_lettino'],
        'navetta'          => (bool)$p['navetta'],
        'prezzo_totale'    => (float)$p['prezzo_totale'],
        'stato'            => $p['stato'],
        'created_at'       => $p['created_at'],
    ],
]);
