<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Dashboard';

$oggi = date('Y-m-d');
$lunedi = date('Y-m-d', strtotime('monday this week'));
$domenica = date('Y-m-d', strtotime('sunday this week'));

// KPI: prenotazioni oggi
$kpiOggi = fetchOne(
    "SELECT COUNT(*) AS tot FROM prenotazioni
     WHERE stato != 'annullata'
       AND data_inizio <= ? AND data_fine >= ?",
    [$oggi, $oggi]
);

// KPI: prenotazioni settimana
$kpiSettimana = fetchOne(
    "SELECT COUNT(*) AS tot FROM prenotazioni
     WHERE stato != 'annullata'
       AND data_inizio <= ? AND data_fine >= ?",
    [$domenica, $lunedi]
);

// KPI: incasso oggi (prenotazioni confermate create oggi)
$kpiIncasso = fetchOne(
    "SELECT COALESCE(SUM(prezzo_totale),0) AS tot FROM prenotazioni
     WHERE stato = 'confermata'
       AND DATE(created_at) = ?",
    [$oggi]
);

// KPI: postazioni libere oggi
$kpiLibere = fetchOne(
    "SELECT COUNT(*) AS tot FROM postazioni
     WHERE stato = 'attiva'
       AND id NOT IN (
           SELECT DISTINCT postazione_id FROM prenotazioni
           WHERE stato != 'annullata'
             AND data_inizio <= ? AND data_fine >= ?
       )",
    [$oggi, $oggi]
);

// Ultime 5 prenotazioni
$ultimePren = fetchAll(
    "SELECT pr.*, p.fila, p.numero FROM prenotazioni pr
     JOIN postazioni p ON p.id = pr.postazione_id
     ORDER BY pr.created_at DESC
     LIMIT 5"
);

// Grafico ultime 7 giorni
$grafico = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $r = fetchOne(
        "SELECT COUNT(*) AS cnt FROM prenotazioni
         WHERE stato != 'annullata' AND DATE(created_at) = ?",
        [$d]
    );
    $grafico[] = [
        'data'  => $d,
        'label' => date('D', strtotime($d)),
        'label_it' => ['Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mer','Thu'=>'Gio','Fri'=>'Ven','Sat'=>'Sab','Sun'=>'Dom'][date('D', strtotime($d))] ?? date('D', strtotime($d)),
        'cnt'   => (int)$r['cnt'],
    ];
}
$maxGrafico = max(array_column($grafico, 'cnt'), 1);

$statoLabel = [
    'in_attesa'  => ['label' => 'In attesa',  'cls' => 'bg-amber-100 text-amber-700'],
    'confermata' => ['label' => 'Confermata', 'cls' => 'bg-green-100 text-green-700'],
    'annullata'  => ['label' => 'Annullata',  'cls' => 'bg-red-100 text-red-700'],
];
$tipoLabel = [
    'giornata_intera' => 'Giornata',
    'mezza_giornata'  => 'Mezza',
    'settimanale'     => 'Settim.',
    'mensile'         => 'Mensile',
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> – Admin Lido Torre Conca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { ocean:{'DEFAULT':'#0c2340','light':'#1a3a60','dark':'#08182d'}, sand:{'DEFAULT':'#d4a847','light':'#f5e6c8','dark':'#a07c2a'} }, fontFamily: { body:['"Inter"','system-ui','sans-serif'] } } } };</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-body bg-gray-50">
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<main class="lg:ml-60 min-h-screen">
    <div class="p-4 md:p-6 lg:p-8">

        <!-- Page header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-ocean">Dashboard</h1>
                <p class="text-gray-500 text-sm mt-0.5"><?= date('d F Y') ?></p>
            </div>
            <a href="/admin/prenotazioni.php"
               class="hidden md:flex items-center gap-2 bg-ocean text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-ocean-light transition-colors">
                <?= icon('plus', 'w-4 h-4') ?>
                Nuova prenotazione
            </a>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Prenot. Oggi</span>
                    <div class="w-9 h-9 bg-ocean/10 rounded-xl flex items-center justify-center">
                        <?= icon('calendar', 'w-5 h-5', '#0c2340') ?>
                    </div>
                </div>
                <div class="text-3xl font-bold text-ocean"><?= (int)$kpiOggi['tot'] ?></div>
                <div class="text-xs text-gray-400 mt-1">postazioni occupate oggi</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Questa Sett.</span>
                    <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center">
                        <?= icon('trending-up', 'w-5 h-5', '#3b82f6') ?>
                    </div>
                </div>
                <div class="text-3xl font-bold text-ocean"><?= (int)$kpiSettimana['tot'] ?></div>
                <div class="text-xs text-gray-400 mt-1"><?= date('d/m') ?> – <?= date('d/m', strtotime($domenica)) ?></div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Incasso Oggi</span>
                    <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center">
                        <?= icon('credit-card', 'w-5 h-5', '#16a34a') ?>
                    </div>
                </div>
                <div class="text-3xl font-bold text-green-600">€<?= number_format((float)$kpiIncasso['tot'], 0, ',', '.') ?></div>
                <div class="text-xs text-gray-400 mt-1">prenotazioni confermate</div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Postaz. Libere</span>
                    <div class="w-9 h-9 bg-sand/20 rounded-xl flex items-center justify-center">
                        <?= icon('umbrella', 'w-5 h-5', '#d4a847') ?>
                    </div>
                </div>
                <div class="text-3xl font-bold text-sand"><?= (int)$kpiLibere['tot'] ?></div>
                <div class="text-xs text-gray-400 mt-1">disponibili oggi su 60</div>
            </div>
        </div>

        <div class="grid lg:grid-cols-5 gap-6 mb-6">
            <!-- Grafico ultime 7 giorni -->
            <div class="lg:col-span-3 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <h2 class="font-semibold text-ocean mb-4 flex items-center gap-2">
                    <?= icon('trending-up', 'w-5 h-5') ?>
                    Prenotazioni ultimi 7 giorni
                </h2>
                <div class="flex items-end gap-2 h-32">
                    <?php foreach ($grafico as $g):
                        $pct = $maxGrafico > 0 ? round($g['cnt'] / $maxGrafico * 100) : 0;
                        $isOggi = $g['data'] === $oggi;
                    ?>
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-xs font-semibold text-gray-600"><?= $g['cnt'] > 0 ? $g['cnt'] : '' ?></span>
                        <div class="w-full rounded-t-md transition-all <?= $isOggi ? 'bg-sand' : 'bg-ocean/20' ?>"
                             style="height: <?= max($pct, 4) ?>%"
                             title="<?= h($g['data']) ?>: <?= $g['cnt'] ?> prenotazioni"></div>
                        <span class="text-[10px] text-gray-400 font-medium"><?= h($g['label_it']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3 flex items-center gap-4 text-xs text-gray-400">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-sand inline-block"></span> Oggi</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-ocean/20 inline-block"></span> Giorni precedenti</span>
                </div>
            </div>

            <!-- Quick stats -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
                <h2 class="font-semibold text-ocean mb-4 flex items-center gap-2">
                    <?= icon('info', 'w-5 h-5') ?>
                    Riepilogo rapido
                </h2>
                <?php
                $statsQuery = fetchOne(
                    "SELECT
                        COUNT(*) AS totale,
                        SUM(stato='confermata') AS confermate,
                        SUM(stato='in_attesa') AS attesa,
                        SUM(stato='annullata') AS annullate,
                        COALESCE(SUM(CASE WHEN stato='confermata' THEN prezzo_totale END),0) AS incasso_totale
                     FROM prenotazioni"
                );
                ?>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-600">Totale prenotazioni</span>
                        <span class="font-bold text-ocean"><?= (int)$statsQuery['totale'] ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-600">Confermate</span>
                        <span class="font-bold text-green-600"><?= (int)$statsQuery['confermate'] ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-600">In attesa</span>
                        <span class="font-bold text-amber-600"><?= (int)$statsQuery['attesa'] ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-600">Annullate</span>
                        <span class="font-bold text-red-500"><?= (int)$statsQuery['annullate'] ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm font-medium text-gray-700">Incasso totale</span>
                        <span class="font-bold text-green-600">€<?= number_format((float)$statsQuery['incasso_totale'], 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ultime prenotazioni -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-ocean flex items-center gap-2">
                    <?= icon('list', 'w-5 h-5') ?>
                    Ultime prenotazioni
                </h2>
                <a href="/admin/prenotazioni.php" class="text-sm text-ocean hover:text-ocean-light font-medium flex items-center gap-1">
                    Vedi tutte <?= icon('arrow-right', 'w-4 h-4') ?>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3 font-medium">Codice</th>
                            <th class="px-5 py-3 font-medium">Postazione</th>
                            <th class="px-5 py-3 font-medium">Cliente</th>
                            <th class="px-5 py-3 font-medium hidden md:table-cell">Tipo</th>
                            <th class="px-5 py-3 font-medium hidden md:table-cell">Date</th>
                            <th class="px-5 py-3 font-medium">Prezzo</th>
                            <th class="px-5 py-3 font-medium">Stato</th>
                            <th class="px-5 py-3 font-medium text-right">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="prenTable">
                        <?php foreach ($ultimePren as $p):
                            $st = $statoLabel[$p['stato']] ?? ['label'=>$p['stato'],'cls'=>'bg-gray-100 text-gray-600'];
                        ?>
                        <tr class="hover:bg-gray-50/50 transition-colors" data-id="<?= (int)$p['id'] ?>">
                            <td class="px-5 py-3.5">
                                <span class="font-mono font-semibold text-ocean text-xs"><?= h($p['codice']) ?></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-semibold"><?= h($p['fila']) ?><?= (int)$p['numero'] ?></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-medium text-gray-800"><?= h($p['nome']) ?> <?= h($p['cognome']) ?></div>
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell text-gray-500">
                                <?= h($tipoLabel[$p['tipo_prenotazione']] ?? $p['tipo_prenotazione']) ?>
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell text-gray-500 text-xs">
                                <?= h(date('d/m', strtotime($p['data_inizio']))) ?> – <?= h(date('d/m', strtotime($p['data_fine']))) ?>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-gray-800">
                                €<?= number_format((float)$p['prezzo_totale'], 2, ',', '.') ?>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $st['cls'] ?> stato-badge">
                                    <?= h($st['label']) ?>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5 azioni-wrapper">
                                    <?php if ($p['stato'] === 'in_attesa'): ?>
                                    <button onclick="aggiornaStato(<?= (int)$p['id'] ?>, 'confermata', this)"
                                            class="inline-flex items-center gap-1 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium px-2.5 py-1.5 rounded-lg transition-colors">
                                        <?= icon('check', 'w-3.5 h-3.5') ?> Conferma
                                    </button>
                                    <button onclick="aggiornaStato(<?= (int)$p['id'] ?>, 'annullata', this)"
                                            class="inline-flex items-center gap-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium px-2.5 py-1.5 rounded-lg transition-colors">
                                        <?= icon('x', 'w-3.5 h-3.5') ?> Annulla
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ultimePren)): ?>
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-gray-400">
                                Nessuna prenotazione ancora.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /p-8 -->
</main>

<script>
async function aggiornaStato(id, stato, btn) {
    if (!confirm(`Vuoi impostare la prenotazione come "${stato}"?`)) return;
    btn.disabled = true;
    try {
        const res = await fetch('/admin/api/aggiorna_stato.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, stato })
        });
        const data = await res.json();
        if (data.ok) {
            showToast('Stato aggiornato con successo', 'success');
            const row = btn.closest('tr');
            const badge = row.querySelector('.stato-badge');
            const azioniWrapper = row.querySelector('.azioni-wrapper');
            const labels = {
                confermata: { label: 'Confermata', cls: 'bg-green-100 text-green-700' },
                annullata:  { label: 'Annullata',  cls: 'bg-red-100 text-red-700' },
                in_attesa:  { label: 'In attesa',  cls: 'bg-amber-100 text-amber-700' },
            };
            const l = labels[stato];
            badge.textContent = l.label;
            badge.className = `inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${l.cls} stato-badge`;
            azioniWrapper.innerHTML = '';
        } else {
            showToast(data.error || 'Errore aggiornamento', 'error');
            btn.disabled = false;
        }
    } catch(e) {
        showToast('Errore di rete', 'error');
        btn.disabled = false;
    }
}
</script>
</body>
</html>
