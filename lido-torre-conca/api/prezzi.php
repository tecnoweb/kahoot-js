<?php
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── Solo GET ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonOut(['ok' => false, 'errore' => 'Metodo non consentito.'], 405);
}

// ── Parametri ────────────────────────────────────────────────
$tipo         = sanitizeStr($_GET['tipo']         ?? '', 30);
$gg           = (int)($_GET['gg']                 ?? 1);
$extraLettino = !empty($_GET['extra_lettino']) && $_GET['extra_lettino'] !== '0';
$navetta      = !empty($_GET['navetta'])      && $_GET['navetta']      !== '0';

$tipiValidi = ['giornata_intera', 'mezza_giornata', 'settimanale', 'mensile'];

// ── Leggi tabella prezzi dal DB ───────────────────────────────
$righe  = fetchAll('SELECT tipo, nome, prezzo, descrizione FROM prezzi ORDER BY id');
$prezzi = [];
foreach ($righe as $r) {
    $prezzi[$r['tipo']] = [
        'nome'        => $r['nome'],
        'prezzo'      => (float)$r['prezzo'],
        'descrizione' => $r['descrizione'],
    ];
}

// ── Se non viene richiesto un calcolo specifico, restituisce lista completa ──
if ($tipo === '') {
    jsonOut([
        'ok'     => true,
        'prezzi' => $prezzi,
    ]);
}

// ── Validazione tipo ─────────────────────────────────────────
if (!in_array($tipo, $tipiValidi, true)) {
    jsonOut(['ok' => false, 'errore' => 'Parametro "tipo" non valido.'], 400);
}

// ── Validazione gg ────────────────────────────────────────────
if ($gg < 1) {
    jsonOut(['ok' => false, 'errore' => 'Il parametro "gg" deve essere almeno 1.'], 400);
}

// Per settimanale e mensile, gg è fisso
if ($tipo === 'settimanale') {
    $gg = 7;
} elseif ($tipo === 'mensile') {
    $gg = 30;
}

// ── Calcola componenti del prezzo ─────────────────────────────
// Recupera le singole voci per comporre il dettaglio
$prezziMap = [];
foreach (fetchAll('SELECT tipo, prezzo FROM prezzi') as $r) {
    $prezziMap[$r['tipo']] = (float)$r['prezzo'];
}

$prezzoBase = match ($tipo) {
    'giornata_intera' => ($prezziMap['giornata_intera'] ?? 25) * $gg,
    'mezza_giornata'  => ($prezziMap['mezza_giornata']  ?? 15) * $gg,
    'settimanale'     => $prezziMap['settimanale'] ?? 140,
    'mensile'         => $prezziMap['mensile']     ?? 450,
    default           => 0,
};

$prezzoExtraLettino = $extraLettino
    ? round(($prezziMap['extra_lettino'] ?? 5) * $gg, 2)
    : 0.0;

$prezzoNavetta = $navetta
    ? round(($prezziMap['navetta'] ?? 3) * $gg, 2)
    : 0.0;

// Usa calcolaPrezzo() per il totale (fonte di verità)
$totale = calcolaPrezzo($tipo, $gg, $extraLettino, $navetta);

jsonOut([
    'ok'      => true,
    'tipo'    => $tipo,
    'gg'      => $gg,
    'prezzo'  => $totale,
    'dettaglio' => [
        'base'         => round($prezzoBase, 2),
        'extra_lettino' => $prezzoExtraLettino,
        'navetta'      => $prezzoNavetta,
    ],
]);
