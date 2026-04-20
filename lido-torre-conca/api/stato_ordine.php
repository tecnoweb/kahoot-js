<?php
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// GET /api/stato_ordine.php?codice=XXXXX  → stato ordine per cliente
// POST { codice, stato, admin_key }        → aggiorna stato (da cucina/admin)

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $codice = strtoupper(sanitizeStr($_GET['codice'] ?? ''));
    if (!$codice) jsonOut(['ok' => false, 'errore' => 'Codice mancante'], 400);

    $ordine = fetchOne(
        'SELECT o.codice, o.stato, o.totale, o.created_at, o.updated_at,
                p.fila, p.numero
         FROM ordini o
         LEFT JOIN postazioni p ON p.id = o.postazione_id
         WHERE o.codice = ?',
        [$codice]
    );

    if (!$ordine) jsonOut(['ok' => false, 'errore' => 'Ordine non trovato'], 404);

    $items = fetchAll(
        'SELECT nome_item, prezzo_unitario, quantita, note FROM ordini_items WHERE ordine_id = (SELECT id FROM ordini WHERE codice = ?)',
        [$codice]
    );

    jsonOut(['ok' => true, 'ordine' => array_merge($ordine, ['items' => $items])]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aggiornamento stato — solo da admin autenticato via sessione
    require_once dirname(__DIR__) . '/includes/config.php';
    if (empty($_SESSION['admin_id'])) {
        jsonOut(['ok' => false, 'errore' => 'Non autorizzato'], 401);
    }

    $body  = json_decode(file_get_contents('php://input'), true) ?? [];
    $id    = (int)($body['id'] ?? 0);
    $stato = $body['stato'] ?? '';

    $statiValidi = ['nuovo', 'in_corso', 'pronto', 'consegnato', 'annullato'];
    if (!$id || !in_array($stato, $statiValidi)) {
        jsonOut(['ok' => false, 'errore' => 'Parametri non validi'], 422);
    }

    $ordine = fetchOne('SELECT id, stato FROM ordini WHERE id = ?', [$id]);
    if (!$ordine) jsonOut(['ok' => false, 'errore' => 'Ordine non trovato'], 404);

    query('UPDATE ordini SET stato = ? WHERE id = ?', [$stato, $id]);

    jsonOut(['ok' => true, 'stato' => $stato]);
}

jsonOut(['ok' => false, 'errore' => 'Metodo non consentito'], 405);
