<?php
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

// Accesso: admin autenticato OPPURE PIN cucina (sessione cucina_auth)
$isAdmin  = !empty($_SESSION['admin_id']);
$isCucina = !empty($_SESSION['cucina_auth']);

if (!$isAdmin && !$isCucina) {
    jsonOut(['ok' => false, 'errore' => 'Non autorizzato'], 401);
}

// Ordini di oggi non annullati, con items e postazione
$ordini = fetchAll(
    "SELECT o.id, o.codice, o.tipo_origine, o.nome_cliente, o.telefono, o.note,
            o.totale, o.stato, o.created_at,
            p.fila, p.numero
     FROM ordini o
     LEFT JOIN postazioni p ON p.id = o.postazione_id
     WHERE DATE(o.created_at) = CURDATE()
       AND o.stato != 'annullato'
     ORDER BY
       FIELD(o.stato,'nuovo','in_corso','pronto','consegnato'),
       o.created_at ASC"
);

foreach ($ordini as &$o) {
    $o['items'] = fetchAll(
        'SELECT nome_item, quantita, prezzo_unitario, note FROM ordini_items WHERE ordine_id = ?',
        [$o['id']]
    );
}
unset($o);

jsonOut(['ok' => true, 'ordini' => $ordini]);
