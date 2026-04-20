<?php
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$data = sanitizeStr($_GET['data'] ?? date('Y-m-d'), 10);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    $data = date('Y-m-d');
}

$rows = fetchAll(
    "SELECT p.id, p.fila, p.numero, p.numero_globale, p.stato AS stato_postazione,
            pr.id AS pren_id, pr.codice, pr.nome, pr.cognome,
            pr.tipo_prenotazione, pr.data_inizio, pr.data_fine,
            pr.prezzo_totale, pr.stato AS stato_prenotazione
     FROM postazioni p
     LEFT JOIN prenotazioni pr ON pr.postazione_id = p.id
         AND pr.stato != 'annullata'
         AND pr.data_inizio <= :data_fine
         AND pr.data_fine   >= :data_inizio
     ORDER BY p.fila, p.numero",
    [':data_inizio' => $data, ':data_fine' => $data]
);

$postazioni = [];
foreach ($rows as $r) {
    if ($r['stato_postazione'] === 'manutenzione') {
        $classe = 'manutenzione';
        $disponibilita = 'manutenzione';
    } elseif ($r['pren_id']) {
        $classe = ($r['stato_prenotazione'] === 'in_attesa') ? 'in_attesa' : 'occupata';
        $disponibilita = $classe;
    } else {
        $classe = 'libera';
        $disponibilita = 'libera';
    }

    $postazioni[] = [
        'id'               => (int)$r['id'],
        'fila'             => $r['fila'],
        'numero'           => (int)$r['numero'],
        'numero_globale'   => (int)$r['numero_globale'],
        'stato_postazione' => $r['stato_postazione'],
        'disponibilita'    => $disponibilita,
        'classe'           => $classe,
        'pren_id'          => $r['pren_id'] ? (int)$r['pren_id'] : null,
        'codice'           => $r['codice'],
        'nome'             => $r['nome'],
        'cognome'          => $r['cognome'],
        'tipo'             => $r['tipo_prenotazione'],
        'data_inizio'      => $r['data_inizio'],
        'data_fine'        => $r['data_fine'],
        'prezzo'           => $r['prezzo_totale'] ? (float)$r['prezzo_totale'] : null,
        'stato_pren'       => $r['stato_prenotazione'],
    ];
}

// Statistiche riepilogo
$libere       = count(array_filter($postazioni, fn($p) => $p['classe'] === 'libera'));
$occupate     = count(array_filter($postazioni, fn($p) => $p['classe'] === 'occupata'));
$in_attesa    = count(array_filter($postazioni, fn($p) => $p['classe'] === 'in_attesa'));
$manutenzione = count(array_filter($postazioni, fn($p) => $p['classe'] === 'manutenzione'));

jsonOut([
    'ok'         => true,
    'data'       => $data,
    'postazioni' => $postazioni,
    'riepilogo'  => [
        'libere'       => $libere,
        'occupate'     => $occupate,
        'in_attesa'    => $in_attesa,
        'manutenzione' => $manutenzione,
        'totale'       => count($postazioni),
    ],
]);
