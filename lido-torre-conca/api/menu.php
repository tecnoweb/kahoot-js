<?php
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// GET /api/menu.php → tutte le categorie con items disponibili
// GET /api/menu.php?categoria_id=N → solo quella categoria

$categoriaId = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : null;

try {
    if ($categoriaId) {
        $cat = fetchOne('SELECT * FROM menu_categorie WHERE id = ? AND attiva = 1', [$categoriaId]);
        if (!$cat) jsonOut(['ok' => false, 'errore' => 'Categoria non trovata'], 404);
        $items = fetchAll(
            'SELECT id, nome, descrizione, prezzo, allergeni FROM menu_items WHERE categoria_id = ? AND disponibile = 1 ORDER BY ordine, nome',
            [$categoriaId]
        );
        jsonOut(['ok' => true, 'categoria' => $cat, 'items' => $items]);
    } else {
        $categorie = fetchAll('SELECT * FROM menu_categorie WHERE attiva = 1 ORDER BY ordine, nome');
        foreach ($categorie as &$cat) {
            $cat['items'] = fetchAll(
                'SELECT id, nome, descrizione, prezzo, allergeni FROM menu_items WHERE categoria_id = ? AND disponibile = 1 ORDER BY ordine, nome',
                [$cat['id']]
            );
        }
        unset($cat);
        jsonOut(['ok' => true, 'categorie' => $categorie]);
    }
} catch (Throwable $e) {
    jsonOut(['ok' => false, 'errore' => 'Errore server'], 500);
}
