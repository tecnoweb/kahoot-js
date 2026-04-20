<?php
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_id'])) jsonOut(['ok' => false, 'errore' => 'Non autorizzato'], 401);
if ($_SESSION['admin_livello'] > 2) jsonOut(['ok' => false, 'errore' => 'Permesso negato'], 403);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonOut(['ok' => false, 'errore' => 'Metodo non consentito'], 405);

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

switch ($action) {
    case 'create':
        $catId   = (int)($body['categoria_id'] ?? 0);
        $nome    = sanitizeStr($body['nome'] ?? '');
        $desc    = sanitizeStr($body['descrizione'] ?? '');
        $prezzo  = round((float)($body['prezzo'] ?? 0), 2);
        $ordine  = (int)($body['ordine'] ?? 0);
        $allergeni = sanitizeStr($body['allergeni'] ?? '');
        if (!$catId || !$nome || $prezzo <= 0) jsonOut(['ok' => false, 'errore' => 'Dati mancanti'], 422);
        query(
            'INSERT INTO menu_items (categoria_id, nome, descrizione, prezzo, ordine, allergeni) VALUES (?,?,?,?,?,?)',
            [$catId, $nome, $desc ?: null, $prezzo, $ordine, $allergeni ?: null]
        );
        jsonOut(['ok' => true, 'id' => (int)lastId()]);

    case 'update':
        $id      = (int)($body['id'] ?? 0);
        $nome    = sanitizeStr($body['nome'] ?? '');
        $desc    = sanitizeStr($body['descrizione'] ?? '');
        $prezzo  = round((float)($body['prezzo'] ?? 0), 2);
        $ordine  = (int)($body['ordine'] ?? 0);
        $catId   = (int)($body['categoria_id'] ?? 0);
        $allergeni = sanitizeStr($body['allergeni'] ?? '');
        if (!$id || !$nome || $prezzo <= 0) jsonOut(['ok' => false, 'errore' => 'Dati mancanti'], 422);
        query(
            'UPDATE menu_items SET categoria_id=?, nome=?, descrizione=?, prezzo=?, ordine=?, allergeni=? WHERE id=?',
            [$catId, $nome, $desc ?: null, $prezzo, $ordine, $allergeni ?: null, $id]
        );
        jsonOut(['ok' => true]);

    case 'delete':
        $id = (int)($body['id'] ?? 0);
        if (!$id) jsonOut(['ok' => false, 'errore' => 'ID mancante'], 422);
        query('DELETE FROM menu_items WHERE id = ?', [$id]);
        jsonOut(['ok' => true]);

    case 'toggle':
        $id   = (int)($body['id'] ?? 0);
        $disp = (int)(bool)($body['disponibile'] ?? 0);
        if (!$id) jsonOut(['ok' => false, 'errore' => 'ID mancante'], 422);
        query('UPDATE menu_items SET disponibile = ? WHERE id = ?', [$disp, $id]);
        jsonOut(['ok' => true]);

    case 'toggle_cat':
        $id     = (int)($body['id'] ?? 0);
        $attiva = (int)(bool)($body['attiva'] ?? 0);
        if (!$id) jsonOut(['ok' => false, 'errore' => 'ID mancante'], 422);
        query('UPDATE menu_categorie SET attiva = ? WHERE id = ?', [$attiva, $id]);
        jsonOut(['ok' => true]);

    default:
        jsonOut(['ok' => false, 'errore' => 'Azione non valida'], 400);
}
