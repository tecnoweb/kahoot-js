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
    jsonOut(['ok' => false, 'error' => 'ID postazione non valido'], 400);
}

$statiValidi = ['attiva', 'manutenzione'];
if (!in_array($stato, $statiValidi)) {
    jsonOut(['ok' => false, 'error' => 'Stato non valido. Usa: attiva, manutenzione'], 400);
}

$postazione = fetchOne('SELECT id, fila, numero, stato FROM postazioni WHERE id = ?', [$id]);
if (!$postazione) {
    jsonOut(['ok' => false, 'error' => 'Postazione non trovata'], 404);
}

// Se si vuole mettere in manutenzione, verifica non ci siano prenotazioni attive
if ($stato === 'manutenzione') {
    $oggi = date('Y-m-d');
    $attive = fetchOne(
        "SELECT COUNT(*) AS cnt FROM prenotazioni
         WHERE postazione_id = ? AND stato != 'annullata'
           AND data_fine >= ?",
        [$id, $oggi]
    );
    if ((int)$attive['cnt'] > 0) {
        jsonOut([
            'ok'    => false,
            'error' => 'Impossibile mettere in manutenzione: ci sono ' . $attive['cnt'] . ' prenotazioni attive o future su questa postazione.',
        ], 409);
    }
}

query('UPDATE postazioni SET stato = ? WHERE id = ?', [$stato, $id]);

jsonOut([
    'ok'       => true,
    'id'       => $id,
    'fila'     => $postazione['fila'],
    'numero'   => (int)$postazione['numero'],
    'stato'    => $stato,
    'messaggio' => $stato === 'manutenzione'
        ? "Postazione {$postazione['fila']}{$postazione['numero']} messa in manutenzione"
        : "Postazione {$postazione['fila']}{$postazione['numero']} riattivata",
]);
