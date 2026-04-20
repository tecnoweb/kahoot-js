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

$id     = isset($body['id']) ? (int)$body['id'] : 0;
$prezzo = isset($body['prezzo']) ? (float)$body['prezzo'] : -1;
$tipo   = sanitizeStr($body['tipo'] ?? 'prezzo', 20); // 'prezzo' o 'noleggio'

if ($id <= 0) {
    jsonOut(['ok' => false, 'error' => 'ID non valido'], 400);
}
if ($prezzo < 0) {
    jsonOut(['ok' => false, 'error' => 'Prezzo non valido (deve essere >= 0)'], 400);
}

if ($tipo === 'noleggio') {
    // Verifica esistenza noleggio
    $record = fetchOne('SELECT id FROM noleggi WHERE id = ?', [$id]);
    if (!$record) {
        jsonOut(['ok' => false, 'error' => 'Noleggio non trovato'], 404);
    }
    query('UPDATE noleggi SET prezzo_ora = ? WHERE id = ?', [round($prezzo, 2), $id]);
    jsonOut([
        'ok'     => true,
        'id'     => $id,
        'prezzo' => round($prezzo, 2),
        'tipo'   => 'noleggio',
    ]);
} else {
    // prezzi prenotazioni
    $record = fetchOne('SELECT id FROM prezzi WHERE id = ?', [$id]);
    if (!$record) {
        jsonOut(['ok' => false, 'error' => 'Voce di prezzo non trovata'], 404);
    }
    query('UPDATE prezzi SET prezzo = ? WHERE id = ?', [round($prezzo, 2), $id]);
    jsonOut([
        'ok'     => true,
        'id'     => $id,
        'prezzo' => round($prezzo, 2),
        'tipo'   => 'prezzo',
    ]);
}
