<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Prenotazioni';

// Filtri
$filtroData   = sanitizeStr($_GET['data']   ?? '', 10);
$filtroStato  = sanitizeStr($_GET['stato']  ?? '', 20);
$filtroTipo   = sanitizeStr($_GET['tipo']   ?? '', 30);
$filtroRicerca = sanitizeStr($_GET['q']     ?? '', 100);
$pagina       = max(1, (int)($_GET['p'] ?? 1));
$perPagina    = 20;
$offset       = ($pagina - 1) * $perPagina;

$statiValidi = ['', 'in_attesa', 'confermata', 'annullata'];
$tipiValidi  = ['', 'giornata_intera', 'mezza_giornata', 'settimanale', 'mensile'];
if (!in_array($filtroStato, $statiValidi)) $filtroStato = '';
if (!in_array($filtroTipo,  $tipiValidi))  $filtroTipo  = '';

// Build WHERE
$where  = [];
$params = [];
if ($filtroData) {
    $where[]  = "pr.data_inizio <= ? AND pr.data_fine >= ?";
    $params[] = $filtroData;
    $params[] = $filtroData;
}
if ($filtroStato) {
    $where[]  = "pr.stato = ?";
    $params[] = $filtroStato;
}
if ($filtroTipo) {
    $where[]  = "pr.tipo_prenotazione = ?";
    $params[] = $filtroTipo;
}
if ($filtroRicerca) {
    $like = '%' . $filtroRicerca . '%';
    $where[]  = "(pr.nome LIKE ? OR pr.cognome LIKE ? OR pr.codice LIKE ? OR pr.email LIKE ? OR pr.telefono LIKE ?)";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$totaleRighe = fetchOne(
    "SELECT COUNT(*) AS cnt FROM prenotazioni pr JOIN postazioni p ON p.id = pr.postazione_id $whereSql",
    $params
)['cnt'];

$totalePagine = max(1, (int)ceil($totaleRighe / $perPagina));
$pagina = min($pagina, $totalePagine);
$offset = ($pagina - 1) * $perPagina;

$prenotazioni = fetchAll(
    "SELECT pr.*, p.fila, p.numero, p.numero_globale
     FROM prenotazioni pr
     JOIN postazioni p ON p.id = pr.postazione_id
     $whereSql
     ORDER BY pr.created_at DESC
     LIMIT $perPagina OFFSET $offset",
    $params
);

$statoLabel = [
    'in_attesa'  => ['label' => 'In attesa',  'cls' => 'bg-amber-100 text-amber-700'],
    'confermata' => ['label' => 'Confermata', 'cls' => 'bg-green-100 text-green-700'],
    'annullata'  => ['label' => 'Annullata',  'cls' => 'bg-red-100 text-red-700'],
];
$tipoLabel = [
    'giornata_intera' => 'Giornata intera',
    'mezza_giornata'  => 'Mezza giornata',
    'settimanale'     => 'Settimanale',
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

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-ocean">Prenotazioni</h1>
            <p class="text-gray-500 text-sm mt-0.5"><?= (int)$totaleRighe ?> risultati trovati</p>
        </div>
        <button onclick="openNewModal()"
                class="flex items-center gap-2 bg-ocean text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-ocean-light transition-colors shadow-sm">
            <?= icon('plus', 'w-4 h-4') ?>
            Nuova Prenotazione
        </button>
    </div>

    <!-- Filtri -->
    <form method="GET" action="/admin/prenotazioni.php" class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-5">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
            <div class="col-span-2 md:col-span-1 lg:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cerca</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <?= icon('search', 'w-4 h-4') ?>
                    </span>
                    <input type="text" name="q" value="<?= h($filtroRicerca) ?>"
                           placeholder="Nome, cognome, codice, email…"
                           class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Data</label>
                <input type="date" name="data" value="<?= h($filtroData) ?>"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Stato</label>
                <select name="stato" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                    <option value="">Tutti</option>
                    <?php foreach (['in_attesa'=>'In attesa','confermata'=>'Confermata','annullata'=>'Annullata'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $filtroStato===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
                <select name="tipo" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                    <option value="">Tutti</option>
                    <?php foreach ($tipoLabel as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $filtroTipo===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end gap-2 col-span-2 md:col-span-4 lg:col-span-5">
                <button type="submit" class="flex items-center gap-1.5 bg-ocean text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-ocean-light transition-colors">
                    <?= icon('filter', 'w-4 h-4') ?> Filtra
                </button>
                <a href="/admin/prenotazioni.php" class="flex items-center gap-1.5 border border-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                    <?= icon('refresh', 'w-4 h-4') ?> Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Tabella -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="mainTable">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <th class="px-5 py-3 font-medium">Codice</th>
                        <th class="px-5 py-3 font-medium">Post.</th>
                        <th class="px-5 py-3 font-medium">Cliente</th>
                        <th class="px-5 py-3 font-medium hidden lg:table-cell">Tipo</th>
                        <th class="px-5 py-3 font-medium hidden md:table-cell">Date</th>
                        <th class="px-5 py-3 font-medium hidden xl:table-cell">Extra</th>
                        <th class="px-5 py-3 font-medium">Prezzo</th>
                        <th class="px-5 py-3 font-medium">Stato</th>
                        <th class="px-5 py-3 font-medium text-right">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($prenotazioni as $p):
                        $st = $statoLabel[$p['stato']] ?? ['label'=>$p['stato'],'cls'=>'bg-gray-100 text-gray-600'];
                    ?>
                    <tr class="hover:bg-gray-50/50 transition-colors" data-id="<?= (int)$p['id'] ?>">
                        <td class="px-5 py-3.5">
                            <span class="font-mono font-bold text-ocean text-xs"><?= h($p['codice']) ?></span>
                        </td>
                        <td class="px-5 py-3.5 font-bold text-ocean"><?= h($p['fila']) ?><?= (int)$p['numero'] ?></td>
                        <td class="px-5 py-3.5">
                            <div class="font-medium text-gray-800"><?= h($p['nome']) ?> <?= h($p['cognome']) ?></div>
                            <div class="text-xs text-gray-400"><?= h($p['telefono']) ?></div>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 hidden lg:table-cell"><?= h($tipoLabel[$p['tipo_prenotazione']] ?? '') ?></td>
                        <td class="px-5 py-3.5 text-gray-500 text-xs hidden md:table-cell">
                            <?= h(date('d/m/Y', strtotime($p['data_inizio']))) ?><br>
                            <?= h(date('d/m/Y', strtotime($p['data_fine']))) ?>
                        </td>
                        <td class="px-5 py-3.5 text-gray-500 text-xs hidden xl:table-cell">
                            <?php if ($p['extra_lettino']): ?><span class="inline-block bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded text-xs mr-1">+Lettino</span><?php endif; ?>
                            <?php if ($p['navetta']): ?><span class="inline-block bg-purple-50 text-purple-600 px-1.5 py-0.5 rounded text-xs">+Navetta</span><?php endif; ?>
                        </td>
                        <td class="px-5 py-3.5 font-bold text-gray-800">€<?= number_format((float)$p['prezzo_totale'], 2, ',', '.') ?></td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= $st['cls'] ?> stato-badge">
                                <?= h($st['label']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1.5">
                                <button onclick='openDetail(<?= json_encode($p, JSON_UNESCAPED_UNICODE) ?>)'
                                        class="p-1.5 rounded-lg text-ocean hover:bg-ocean/10 transition-colors" title="Dettaglio">
                                    <?= icon('info', 'w-4 h-4') ?>
                                </button>
                                <?php if ($p['stato'] === 'in_attesa'): ?>
                                <button onclick="aggiornaStato(<?= (int)$p['id'] ?>, 'confermata', this)"
                                        class="p-1.5 rounded-lg text-green-600 hover:bg-green-50 transition-colors" title="Conferma">
                                    <?= icon('check', 'w-4 h-4') ?>
                                </button>
                                <button onclick="aggiornaStato(<?= (int)$p['id'] ?>, 'annullata', this)"
                                        class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Annulla">
                                    <?= icon('x', 'w-4 h-4') ?>
                                </button>
                                <?php elseif ($p['stato'] === 'confermata'): ?>
                                <button onclick="aggiornaStato(<?= (int)$p['id'] ?>, 'annullata', this)"
                                        class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors" title="Annulla">
                                    <?= icon('trash', 'w-4 h-4') ?>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($prenotazioni)): ?>
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center gap-2">
                                <?= icon('search', 'w-10 h-10 text-gray-300') ?>
                                <span>Nessuna prenotazione trovata con i filtri attuali.</span>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginazione -->
        <?php if ($totalePagine > 1): ?>
        <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100">
            <span class="text-sm text-gray-500">
                Pagina <?= $pagina ?> di <?= $totalePagine ?> (<?= (int)$totaleRighe ?> risultati)
            </span>
            <div class="flex items-center gap-1">
                <?php
                $baseUrl = '/admin/prenotazioni.php?' . http_build_query(array_filter([
                    'data'  => $filtroData,
                    'stato' => $filtroStato,
                    'tipo'  => $filtroTipo,
                    'q'     => $filtroRicerca,
                ]));
                for ($i = max(1,$pagina-2); $i <= min($totalePagine,$pagina+2); $i++):
                ?>
                <a href="<?= h($baseUrl . '&p=' . $i) ?>"
                   class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors <?= $i===$pagina ? 'bg-ocean text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</main>

<!-- Modal Dettaglio -->
<div id="modalDettaglio" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeDetail()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white flex items-center justify-between px-6 py-4 border-b border-gray-100 rounded-t-2xl">
            <h3 class="font-bold text-ocean text-lg">Dettaglio Prenotazione</h3>
            <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                <?= icon('close', 'w-5 h-5') ?>
            </button>
        </div>
        <div id="modalDettaglioBody" class="px-6 py-5 space-y-4"></div>
    </div>
</div>

<!-- Modal Nuova Prenotazione -->
<div id="modalNuova" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeNewModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[95vh] overflow-y-auto">
        <div class="sticky top-0 bg-white flex items-center justify-between px-6 py-4 border-b border-gray-100 rounded-t-2xl">
            <h3 class="font-bold text-ocean text-lg">Nuova Prenotazione</h3>
            <button onclick="closeNewModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                <?= icon('close', 'w-5 h-5') ?>
            </button>
        </div>
        <form id="formNuova" onsubmit="submitNuova(event)" class="px-6 py-5 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="nome" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cognome *</label>
                    <input type="text" name="cognome" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono *</label>
                    <input type="tel" name="telefono" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Postazione (n. globale) *</label>
                    <input type="number" name="postazione_id_globale" min="1" max="60" required placeholder="1-60"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                    <p class="text-xs text-gray-400 mt-0.5">Numero globale 1-60 (A1=1, F10=60)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="tipo_prenotazione" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                        <option value="giornata_intera">Giornata intera</option>
                        <option value="mezza_giornata">Mezza giornata</option>
                        <option value="settimanale">Settimanale</option>
                        <option value="mensile">Mensile</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data inizio *</label>
                    <input type="date" name="data_inizio" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data fine *</label>
                    <input type="date" name="data_fine" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adulti</label>
                    <input type="number" name="adulti" value="2" min="1" max="10" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bambini</label>
                    <input type="number" name="bambini" value="0" min="0" max="10" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                </div>
            </div>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="extra_lettino" value="1" class="rounded">
                    <span class="text-sm text-gray-700">Lettino extra</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="navetta" value="1" class="rounded">
                    <span class="text-sm text-gray-700">Navetta</span>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                <textarea name="note" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30 resize-none"></textarea>
            </div>
            <div id="formNuovaError" class="hidden bg-red-50 text-red-700 text-sm px-3 py-2 rounded-lg border border-red-200"></div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-ocean text-white py-2.5 rounded-xl font-semibold hover:bg-ocean-light transition-colors">
                    Crea Prenotazione
                </button>
                <button type="button" onclick="closeNewModal()" class="px-6 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition-colors">
                    Annulla
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const statoMeta = {
    in_attesa:  { label: 'In attesa',  cls: 'bg-amber-100 text-amber-700' },
    confermata: { label: 'Confermata', cls: 'bg-green-100 text-green-700' },
    annullata:  { label: 'Annullata',  cls: 'bg-red-100 text-red-700' },
};
const tipoMeta = {
    giornata_intera: 'Giornata intera',
    mezza_giornata:  'Mezza giornata',
    settimanale:     'Settimanale',
    mensile:         'Mensile',
};

function openDetail(p) {
    document.getElementById('modalDettaglio').classList.replace('hidden','flex');
    const l = statoMeta[p.stato] || {label:p.stato, cls:'bg-gray-100 text-gray-600'};
    document.getElementById('modalDettaglioBody').innerHTML = `
        <div class="flex justify-between items-start">
            <div>
                <span class="font-mono font-bold text-xl text-ocean">${p.codice}</span>
                <span class="ml-2 inline-flex px-2 py-0.5 rounded-full text-xs font-semibold ${l.cls}">${l.label}</span>
            </div>
            <span class="text-2xl font-bold text-green-600">€${parseFloat(p.prezzo_totale).toFixed(2).replace('.',',')}</span>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400 font-medium mb-1">Postazione</div>
                <div class="font-bold text-ocean text-lg">${p.fila}${p.numero}</div>
                <div class="text-xs text-gray-500">N. globale: ${p.numero_globale}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400 font-medium mb-1">Tipo</div>
                <div class="font-semibold text-gray-800">${tipoMeta[p.tipo_prenotazione]||p.tipo_prenotazione}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400 font-medium mb-1">Date</div>
                <div class="font-semibold text-gray-800">${formatDate(p.data_inizio)}</div>
                <div class="text-xs text-gray-500">→ ${formatDate(p.data_fine)}</div>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <div class="text-xs text-gray-400 font-medium mb-1">Persone</div>
                <div class="font-semibold text-gray-800">${p.adulti} adulti, ${p.bambini} bambini</div>
            </div>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 text-sm space-y-2">
            <div class="text-xs text-gray-400 font-medium mb-2">Dati Cliente</div>
            <div class="flex justify-between"><span class="text-gray-500">Nome</span><span class="font-medium">${escHtml(p.nome)} ${escHtml(p.cognome)}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium">${escHtml(p.email)}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Telefono</span><span class="font-medium">${escHtml(p.telefono)}</span></div>
        </div>
        ${(p.extra_lettino == 1 || p.navetta == 1) ? `<div class="flex gap-2">
            ${p.extra_lettino == 1 ? '<span class="bg-blue-50 text-blue-600 px-2 py-1 rounded-lg text-xs font-medium">+Lettino extra</span>' : ''}
            ${p.navetta == 1 ? '<span class="bg-purple-50 text-purple-600 px-2 py-1 rounded-lg text-xs font-medium">+Servizio navetta</span>' : ''}
        </div>` : ''}
        ${p.note ? `<div class="bg-amber-50 rounded-xl p-3 text-sm"><div class="text-xs text-gray-400 mb-1">Note</div><div class="text-gray-700">${escHtml(p.note)}</div></div>` : ''}
        <div class="text-xs text-gray-400 text-right">Creata il ${formatDateTime(p.created_at)}</div>
    `;
}

function closeDetail() {
    document.getElementById('modalDettaglio').classList.replace('flex','hidden');
}

function openNewModal() {
    const f = document.getElementById('formNuova');
    f.reset();
    f.querySelector('[name=data_inizio]').value = new Date().toISOString().split('T')[0];
    f.querySelector('[name=data_fine]').value = new Date().toISOString().split('T')[0];
    document.getElementById('formNuovaError').classList.add('hidden');
    document.getElementById('modalNuova').classList.replace('hidden','flex');
}

function closeNewModal() {
    document.getElementById('modalNuova').classList.replace('flex','hidden');
}

async function submitNuova(e) {
    e.preventDefault();
    const f = e.target;
    const errEl = document.getElementById('formNuovaError');
    errEl.classList.add('hidden');
    const fd = new FormData(f);
    const data = Object.fromEntries(fd.entries());
    // Risolvi postazione_id dal numero globale
    const postazione_id_globale = parseInt(data.postazione_id_globale);
    if (!postazione_id_globale || postazione_id_globale < 1 || postazione_id_globale > 60) {
        errEl.textContent = 'Numero postazione non valido (1-60)';
        errEl.classList.remove('hidden');
        return;
    }
    const payload = {
        postazione_id: postazione_id_globale, // backend cercherà per numero_globale
        tipo_prenotazione: data.tipo_prenotazione,
        data_inizio: data.data_inizio,
        data_fine: data.data_fine,
        nome: data.nome,
        cognome: data.cognome,
        email: data.email,
        telefono: data.telefono,
        adulti: parseInt(data.adulti)||2,
        bambini: parseInt(data.bambini)||0,
        extra_lettino: data.extra_lettino ? 1 : 0,
        navetta: data.navetta ? 1 : 0,
        note: data.note || '',
        da_admin: true,
    };
    const btn = f.querySelector('[type=submit]');
    btn.disabled = true;
    btn.textContent = 'Creazione in corso…';
    try {
        const res = await fetch('/admin/api/pos_prenota.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const resp = await res.json();
        if (resp.ok) {
            closeNewModal();
            showToast(`Prenotazione ${resp.codice} creata con successo!`, 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            errEl.textContent = resp.error || 'Errore nella creazione';
            errEl.classList.remove('hidden');
        }
    } catch(err) {
        errEl.textContent = 'Errore di rete';
        errEl.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.textContent = 'Crea Prenotazione';
}

async function aggiornaStato(id, stato, btn) {
    if (!confirm(`Confermi di impostare stato "${stato}"?`)) return;
    btn.disabled = true;
    try {
        const res = await fetch('/admin/api/aggiorna_stato.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({id, stato}),
        });
        const data = await res.json();
        if (data.ok) {
            showToast('Stato aggiornato', 'success');
            const row = btn.closest('tr');
            const l = statoMeta[stato] || {label:stato, cls:'bg-gray-100 text-gray-600'};
            row.querySelector('.stato-badge').textContent = l.label;
            row.querySelector('.stato-badge').className = `inline-flex px-2 py-0.5 rounded-full text-xs font-semibold ${l.cls} stato-badge`;
            // rimuovi pulsanti azioni
            const btns = row.querySelectorAll('button[onclick*="aggiornaStato"]');
            btns.forEach(b => b.remove());
        } else {
            showToast(data.error || 'Errore', 'error');
            btn.disabled = false;
        }
    } catch(e) {
        showToast('Errore di rete', 'error');
        btn.disabled = false;
    }
}

function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatDate(d) {
    if (!d) return '';
    const p = d.split('-');
    return `${p[2]}/${p[1]}/${p[0]}`;
}
function formatDateTime(dt) {
    if (!dt) return '';
    const d = new Date(dt);
    return `${d.getDate().toString().padStart(2,'0')}/${(d.getMonth()+1).toString().padStart(2,'0')}/${d.getFullYear()} ${d.getHours().toString().padStart(2,'0')}:${d.getMinutes().toString().padStart(2,'0')}`;
}
</script>
</body>
</html>
