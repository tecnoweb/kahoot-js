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

$id    = isset($body['id']) ? (int)$body['id'] : 0;
$stato = sanitizeStr($body['stato'] ?? '', 20);

if ($id <= 0) {
    jsonOut(['ok' => false, 'error' => 'ID non valido'], 400);
}

$statiValidi = ['in_attesa', 'confermata', 'annullata'];
if (!in_array($stato, $statiValidi)) {
    jsonOut(['ok' => false, 'error' => 'Stato non valido. Usa: ' . implode(', ', $statiValidi)], 400);
}

// Verifica che la prenotazione esista
$pren = fetchOne('SELECT id, stato FROM prenotazioni WHERE id = ?', [$id]);
if (!$pren) {
    jsonOut(['ok' => false, 'error' => 'Prenotazione non trovata'], 404);
}

// Aggiorna
query('UPDATE prenotazioni SET stato = ? WHERE id = ?', [$stato, $id]);

jsonOut([
    'ok'    => true,
    'id'    => $id,
    'stato' => $stato,
]);
