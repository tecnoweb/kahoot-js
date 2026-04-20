<?php
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['ok' => false, 'errore' => 'Metodo non consentito'], 405);
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) jsonOut(['ok' => false, 'errore' => 'Payload non valido'], 400);

// Campi obbligatori
$nomeCliente  = sanitizeStr($body['nome_cliente'] ?? '');
$telefono     = sanitizeStr($body['telefono']     ?? '', 25);
$note         = sanitizeStr($body['note']         ?? '', 500);
$tipoOrigine  = in_array($body['tipo_origine'] ?? '', ['spiaggia','ristorante','banco'])
                ? $body['tipo_origine'] : 'spiaggia';
$items        = $body['items'] ?? [];
$postazioneId = isset($body['postazione_id']) ? (int)$body['postazione_id'] : null;

if (empty($nomeCliente)) {
    jsonOut(['ok' => false, 'errore' => 'Nome cliente obbligatorio'], 422);
}
if (empty($items) || !is_array($items)) {
    jsonOut(['ok' => false, 'errore' => 'Nessun articolo nel carrello'], 422);
}

// Valida postazione se spiaggia
if ($tipoOrigine === 'spiaggia') {
    if (!$postazioneId) jsonOut(['ok' => false, 'errore' => 'Postazione obbligatoria per ordini spiaggia'], 422);
    $post = fetchOne('SELECT id FROM postazioni WHERE id = ? AND stato = ?', [$postazioneId, 'attiva']);
    if (!$post) jsonOut(['ok' => false, 'errore' => 'Postazione non valida'], 422);
}

// Verifica items e calcola totale
$validatedItems = [];
$totale = 0.0;

foreach ($items as $item) {
    $itemId = (int)($item['id'] ?? 0);
    $qty    = max(1, min(20, (int)($item['quantita'] ?? 1)));
    $note_item = sanitizeStr($item['note'] ?? '', 255);

    $menuItem = fetchOne(
        'SELECT id, nome, prezzo FROM menu_items WHERE id = ? AND disponibile = 1',
        [$itemId]
    );
    if (!$menuItem) jsonOut(['ok' => false, 'errore' => "Articolo ID {$itemId} non disponibile"], 422);

    $validatedItems[] = [
        'menu_item_id'    => $menuItem['id'],
        'nome_item'       => $menuItem['nome'],
        'prezzo_unitario' => (float)$menuItem['prezzo'],
        'quantita'        => $qty,
        'note'            => $note_item,
    ];
    $totale += (float)$menuItem['prezzo'] * $qty;
}

// Genera codice ordine
do {
    $codice = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    $exists = fetchOne('SELECT id FROM ordini WHERE codice = ?', [$codice]);
} while ($exists);

try {
    db()->beginTransaction();

    query(
        'INSERT INTO ordini (codice, postazione_id, tipo_origine, nome_cliente, telefono, note, totale, stato)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $codice,
            $postazioneId,
            $tipoOrigine,
            $nomeCliente,
            $telefono ?: null,
            $note     ?: null,
            round($totale, 2),
            'nuovo',
        ]
    );
    $ordineId = (int)lastId();

    foreach ($validatedItems as $vi) {
        query(
            'INSERT INTO ordini_items (ordine_id, menu_item_id, nome_item, prezzo_unitario, quantita, note)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $ordineId,
                $vi['menu_item_id'],
                $vi['nome_item'],
                $vi['prezzo_unitario'],
                $vi['quantita'],
                $vi['note'] ?: null,
            ]
        );
    }

    db()->commit();

    jsonOut([
        'ok'      => true,
        'codice'  => $codice,
        'ordine_id' => $ordineId,
        'totale'  => round($totale, 2),
        'messaggio' => 'Ordine ricevuto! Lo staff lo preparerà al più presto.',
    ]);
} catch (Throwable $e) {
    db()->rollBack();
    jsonOut(['ok' => false, 'errore' => 'Errore durante la registrazione ordine'], 500);
}
