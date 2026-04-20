<?php
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// ── Solo GET ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonOut(['ok' => false, 'errore' => 'Metodo non consentito.'], 405);
}

// ── Parametri ────────────────────────────────────────────────
$data      = sanitizeStr($_GET['data']      ?? '', 10);
$dataFine  = sanitizeStr($_GET['data_fine'] ?? '', 10);
$tipo      = sanitizeStr($_GET['tipo']      ?? '', 30);

$tipiValidi = ['giornata_intera', 'mezza_giornata', 'settimanale', 'mensile'];

if ($data === '') {
    jsonOut(['ok' => false, 'errore' => 'Parametro "data" obbligatorio.'], 400);
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) || !strtotime($data)) {
    jsonOut(['ok' => false, 'errore' => 'Formato data non valido (YYYY-MM-DD).'], 400);
}
if ($tipo === '' || !in_array($tipo, $tipiValidi, true)) {
    jsonOut(['ok' => false, 'errore' => 'Parametro "tipo" non valido.'], 400);
}

// ── Anticipo di 1 ora ────────────────────────────────────────
$now   = new DateTime();
$oggi  = $now->format('Y-m-d');

if ($data === $oggi) {
    // Determina l'orario di riferimento per la tipologia
    $orarioRif = ($tipo === 'mezza_giornata') ? ORARIO_POMERIGGIO : ORARIO_APERTURA;
    [$h, $m]   = explode(':', $orarioRif);
    $apertura  = clone $now;
    $apertura->setTime((int)$h, (int)$m, 0);
    $limite    = (clone $apertura)->modify('-' . MINUTI_ANTICIPO . ' minutes');

    if ($now >= $limite) {
        jsonOut([
            'ok'     => false,
            'errore' => 'Non è più possibile verificare la disponibilità per oggi (anticipo minimo ' . MINUTI_ANTICIPO . ' minuti).',
        ], 400);
    }
}

if ($data < $oggi) {
    jsonOut(['ok' => false, 'errore' => 'Non è possibile verificare disponibilità per date passate.'], 400);
}

// ── Calcola data_fine se non fornita ─────────────────────────
if ($dataFine === '') {
    $dtInizio = new DateTime($data);
    if ($tipo === 'settimanale') {
        $dtInizio->modify('+6 days');
    } elseif ($tipo === 'mensile') {
        $dtInizio->modify('+29 days');
    } else {
        $dtInizio->modify('+0 days'); // stesso giorno
    }
    $dataFine = $dtInizio->format('Y-m-d');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFine) || !strtotime($dataFine)) {
    jsonOut(['ok' => false, 'errore' => 'Formato data_fine non valido (YYYY-MM-DD).'], 400);
}
if ($dataFine < $data) {
    jsonOut(['ok' => false, 'errore' => '"data_fine" non può essere precedente a "data".'], 400);
}

// ── Costruisci set di date nell'intervallo ────────────────────
$dates   = [];
$current = new DateTime($data);
$end     = new DateTime($dataFine);

while ($current <= $end) {
    $dates[] = $current->format('Y-m-d');
    $current->modify('+1 day');
}

// ── Chiamata mappaDisponibilita per ogni giorno ──────────────
// Inizializza mappa con il primo giorno
$mappe = [];
foreach ($dates as $giorno) {
    $righe = mappaDisponibilita($giorno, $tipo);
    foreach ($righe as $riga) {
        $id = (int)$riga['id'];
        if (!isset($mappe[$id])) {
            $mappe[$id] = [
                'id'             => $id,
                'fila'           => $riga['fila'],
                'numero'         => (int)$riga['numero'],
                'numero_globale' => (int)$riga['numero_globale'],
                'stato'          => $riga['stato'],
                'occupata'       => false,
            ];
        }
        // Anche un solo giorno occupato → postazione non disponibile
        if ($riga['disponibilita'] === 'occupata') {
            $mappe[$id]['occupata'] = true;
        }
    }
}

// ── Formato risposta finale ───────────────────────────────────
$risultato = [];
foreach ($mappe as $p) {
    $risultato[] = [
        'id'             => $p['id'],
        'fila'           => $p['fila'],
        'numero'         => $p['numero'],
        'numero_globale' => $p['numero_globale'],
        'disponibile'    => !$p['occupata'] && $p['stato'] === 'attiva',
        'stato'          => $p['stato'],
    ];
}

// Ordina per numero_globale
usort($risultato, fn($a, $b) => $a['numero_globale'] <=> $b['numero_globale']);

jsonOut([
    'ok'         => true,
    'data_inizio' => $data,
    'data_fine'   => $dataFine,
    'tipo'        => $tipo,
    'data'        => $risultato,
]);
