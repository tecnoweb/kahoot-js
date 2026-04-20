<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Mappa Spiaggia';

$data = sanitizeStr($_GET['data'] ?? date('Y-m-d'), 10);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    $data = date('Y-m-d');
}

// Carica stato mappa per la data selezionata
$rows = fetchAll(
    "SELECT p.id, p.fila, p.numero, p.numero_globale, p.stato AS stato_postazione,
            pr.id AS pren_id, pr.codice, pr.nome, pr.cognome, pr.tipo_prenotazione,
            pr.data_inizio, pr.data_fine, pr.prezzo_totale, pr.stato AS stato_prenotazione
     FROM postazioni p
     LEFT JOIN prenotazioni pr ON pr.postazione_id = p.id
         AND pr.stato != 'annullata'
         AND pr.data_inizio <= :data_fine
         AND pr.data_fine   >= :data_inizio
     ORDER BY p.fila, p.numero",
    [':data_inizio' => $data, ':data_fine' => $data]
);

// Organizza per fila
$mappa = [];
foreach ($rows as $r) {
    $mappa[$r['fila']][$r['numero']] = $r;
}
$file = ['A','B','C','D','E','F'];
$numeri = range(1, 10);
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
    <style>
        .postazione { transition: all 0.15s ease; cursor: pointer; }
        .postazione:hover { transform: scale(1.08); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .postazione.libera { background:#d1fae5; border-color:#10b981; color:#065f46; }
        .postazione.occupata { background:#fee2e2; border-color:#ef4444; color:#991b1b; }
        .postazione.in_attesa { background:#fef3c7; border-color:#f59e0b; color:#92400e; }
        .postazione.manutenzione { background:#e5e7eb; border-color:#9ca3af; color:#4b5563; }
        .slide-panel { transition: transform 0.25s ease, opacity 0.25s ease; }
        .slide-panel.hidden-panel { transform: translateX(100%); opacity:0; pointer-events:none; }
    </style>
</head>
<body class="font-body bg-gray-50">
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<main class="lg:ml-60 min-h-screen">
<div class="p-4 md:p-6 lg:p-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-ocean">Mappa Spiaggia</h1>
            <p class="text-gray-500 text-sm mt-0.5">Clicca una postazione per dettagli o nuova prenotazione</p>
        </div>
        <div class="flex items-center gap-3">
            <input type="date" id="dataSelector" value="<?= h($data) ?>"
                   class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30"
                   onchange="changeData(this.value)">
            <button onclick="refreshMappa()" id="btnRefresh"
                    class="flex items-center gap-1.5 border border-gray-200 text-gray-600 px-3 py-2 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                <?= icon('refresh', 'w-4 h-4') ?> Aggiorna
            </button>
        </div>
    </div>

    <!-- Legenda -->
    <div class="flex flex-wrap gap-3 mb-5 text-xs font-medium">
        <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded border-2 border-emerald-500 bg-green-100 inline-block"></span>Libera</span>
        <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded border-2 border-red-400 bg-red-100 inline-block"></span>Occupata</span>
        <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded border-2 border-amber-400 bg-amber-100 inline-block"></span>In attesa</span>
        <span class="flex items-center gap-1.5"><span class="w-4 h-4 rounded border-2 border-gray-400 bg-gray-200 inline-block"></span>Manutenzione</span>
    </div>

    <!-- Layout con pannello laterale -->
    <div class="relative flex gap-5">

        <!-- Griglia mappa -->
        <div class="flex-1 bg-white rounded-2xl p-5 shadow-sm border border-gray-100 overflow-x-auto">
            <!-- Indicatore mare -->
            <div class="flex items-center justify-center mb-3 py-2 bg-blue-50 rounded-xl text-blue-600 text-xs font-semibold tracking-wider uppercase gap-2">
                <?= icon('waves', 'w-4 h-4') ?> MARE
            </div>

            <!-- Numeri colonne -->
            <div class="grid gap-1.5 mb-1.5" style="grid-template-columns: 2rem repeat(10, minmax(0, 1fr));">
                <div></div>
                <?php for ($n = 1; $n <= 10; $n++): ?>
                <div class="text-center text-xs font-bold text-gray-400"><?= $n ?></div>
                <?php endfor; ?>
            </div>

            <!-- Righe -->
            <?php foreach ($file as $fila): ?>
            <div class="grid gap-1.5 mb-1.5" style="grid-template-columns: 2rem repeat(10, minmax(0, 1fr));" id="fila-<?= $fila ?>">
                <div class="flex items-center justify-center">
                    <span class="text-xs font-bold text-gray-500 w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center"><?= $fila ?></span>
                </div>
                <?php for ($n = 1; $n <= 10; $n++):
                    $c = $mappa[$fila][$n] ?? null;
                    if (!$c) continue;

                    if ($c['stato_postazione'] === 'manutenzione') {
                        $classe = 'manutenzione';
                        $tooltip = "Manutenzione";
                    } elseif ($c['pren_id']) {
                        $classe = ($c['stato_prenotazione'] === 'in_attesa') ? 'in_attesa' : 'occupata';
                        $tooltip = h($c['nome'].' '.$c['cognome']);
                    } else {
                        $classe = 'libera';
                        $tooltip = 'Libera';
                    }
                    $cellData = json_encode([
                        'id'               => (int)$c['id'],
                        'fila'             => $c['fila'],
                        'numero'           => (int)$c['numero'],
                        'numero_globale'   => (int)$c['numero_globale'],
                        'stato_postazione' => $c['stato_postazione'],
                        'pren_id'          => $c['pren_id'] ? (int)$c['pren_id'] : null,
                        'codice'           => $c['codice'] ?? null,
                        'nome'             => $c['nome'] ?? null,
                        'cognome'          => $c['cognome'] ?? null,
                        'tipo'             => $c['tipo_prenotazione'] ?? null,
                        'data_inizio'      => $c['data_inizio'] ?? null,
                        'data_fine'        => $c['data_fine'] ?? null,
                        'prezzo'           => $c['prezzo_totale'] ?? null,
                        'stato_pren'       => $c['stato_prenotazione'] ?? null,
                    ], JSON_UNESCAPED_UNICODE);
                ?>
                <button class="postazione <?= $classe ?> border-2 rounded-lg aspect-square flex flex-col items-center justify-center min-w-0 p-0.5"
                        onclick='handleClick(<?= $cellData ?>)'
                        title="<?= $tooltip ?>"
                        data-postazione-id="<?= (int)$c['id'] ?>">
                    <span class="text-[9px] font-bold leading-none"><?= $fila ?><?= $n ?></span>
                </button>
                <?php endfor; ?>
            </div>
            <?php endforeach; ?>

            <!-- Retro -->
            <div class="flex items-center justify-center mt-3 py-2 bg-sand/10 rounded-xl text-sand text-xs font-semibold tracking-wider uppercase gap-2">
                <?= icon('anchor', 'w-4 h-4') ?> ENTRATA
            </div>
        </div>

        <!-- Pannello laterale -->
        <div id="pannelloLaterale"
             class="slide-panel hidden-panel fixed right-0 top-0 h-full w-full sm:w-96 bg-white shadow-2xl z-30 overflow-y-auto sm:static sm:h-auto sm:rounded-2xl sm:border sm:border-gray-100 lg:w-80 flex-shrink-0">
            <div id="pannelloContenuto"></div>
        </div>
    </div>
</div>
</main>

<!-- Overlay mobile -->
<div id="overlayPannello" class="sm:hidden fixed inset-0 bg-black/40 z-20 hidden" onclick="chiudiPannello()"></div>

<script>
const dataCorrente = document.getElementById('dataSelector').value;

function changeData(d) {
    window.location.href = `/admin/mappa.php?data=${encodeURIComponent(d)}`;
}

function handleClick(cell) {
    if (cell.stato_postazione === 'manutenzione') {
        mostraPannelloManutenzione(cell);
    } else if (cell.pren_id) {
        mostraPannelloPrenotazione(cell);
    } else {
        mostraPannelloNuova(cell);
    }
}

function aprirePannello(html) {
    const p = document.getElementById('pannelloLaterale');
    const o = document.getElementById('overlayPannello');
    document.getElementById('pannelloContenuto').innerHTML = html;
    p.classList.remove('hidden-panel');
    o.classList.remove('hidden');
}

function chiudiPannello() {
    document.getElementById('pannelloLaterale').classList.add('hidden-panel');
    document.getElementById('overlayPannello').classList.add('hidden');
}

function mostraPannelloPrenotazione(cell) {
    const tipi = {
        giornata_intera:'Giornata intera', mezza_giornata:'Mezza giornata',
        settimanale:'Settimanale', mensile:'Mensile'
    };
    const statoClr = cell.stato_pren === 'in_attesa' ? 'text-amber-600 bg-amber-50' : 'text-green-600 bg-green-50';
    const statoLbl = cell.stato_pren === 'in_attesa' ? 'In attesa' : 'Confermata';
    aprirePannello(`
        <div class="p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-ocean text-lg">Postazione ${escHtml(cell.fila)}${cell.numero}</h3>
                <button onclick="chiudiPannello()" class="text-gray-400 hover:text-gray-600">${iconSvg('close')}</button>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Codice</span><span class="font-mono font-bold text-ocean">${escHtml(cell.codice)}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Cliente</span><span class="font-semibold">${escHtml(cell.nome)} ${escHtml(cell.cognome)}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Tipo</span><span>${tipi[cell.tipo]||cell.tipo}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Dal</span><span>${formatDate(cell.data_inizio)}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Al</span><span>${formatDate(cell.data_fine)}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Prezzo</span><span class="font-bold text-green-600">€${parseFloat(cell.prezzo).toFixed(2).replace('.',',')}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Stato</span><span class="px-2 py-0.5 rounded-full text-xs font-semibold ${statoClr}">${statoLbl}</span></div>
            </div>
            ${cell.stato_pren === 'in_attesa' ? `
            <button onclick="aggiornaStatoMappa(${cell.pren_id}, 'confermata')"
                    class="w-full mb-2 bg-green-600 text-white py-2.5 rounded-xl font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                ${iconSvg('check')} Conferma prenotazione
            </button>
            ` : ''}
            <button onclick="aggiornaStatoMappa(${cell.pren_id}, 'annullata')"
                    class="w-full mb-3 bg-red-100 text-red-700 py-2.5 rounded-xl font-semibold hover:bg-red-200 transition-colors flex items-center justify-center gap-2">
                ${iconSvg('x')} Annulla prenotazione
            </button>
            <hr class="my-3 border-gray-100">
            <button onclick="toggleManutenzione(${cell.id}, 'manutenzione')"
                    class="w-full border border-gray-200 text-gray-600 py-2 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                Metti in manutenzione dopo
            </button>
        </div>
    `);
}

function mostraPannelloNuova(cell) {
    const oggi = document.getElementById('dataSelector').value;
    aprirePannello(`
        <div class="p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-ocean text-lg">Postazione ${escHtml(cell.fila)}${cell.numero}</h3>
                <button onclick="chiudiPannello()" class="text-gray-400 hover:text-gray-600">${iconSvg('close')}</button>
            </div>
            <div class="inline-flex items-center bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">Libera</div>
            <form id="formRapida" onsubmit="submitRapida(event, ${cell.id})">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nome *</label>
                        <input type="text" name="nome" required placeholder="Mario"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Cognome *</label>
                        <input type="text" name="cognome" required placeholder="Rossi"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Telefono</label>
                        <input type="tel" name="telefono" placeholder="+39..."
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
                        <select name="tipo_prenotazione" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                            <option value="giornata_intera">Giornata intera</option>
                            <option value="mezza_giornata">Mezza giornata</option>
                            <option value="settimanale">Settimanale</option>
                            <option value="mensile">Mensile</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Dal</label>
                            <input type="date" name="data_inizio" value="${oggi}" required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Al</label>
                            <input type="date" name="data_fine" value="${oggi}" required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                        </div>
                    </div>
                    <div id="errRapida" class="hidden bg-red-50 text-red-600 text-xs px-3 py-2 rounded-lg border border-red-100"></div>
                    <button type="submit" class="w-full bg-ocean text-white py-2.5 rounded-xl font-semibold hover:bg-ocean-light transition-colors">
                        Prenota
                    </button>
                </div>
            </form>
            <hr class="my-4 border-gray-100">
            <button onclick="toggleManutenzione(${cell.id}, 'manutenzione')"
                    class="w-full border border-gray-200 text-gray-600 py-2 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                Metti in manutenzione
            </button>
        </div>
    `);
}

function mostraPannelloManutenzione(cell) {
    aprirePannello(`
        <div class="p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-ocean text-lg">Postazione ${escHtml(cell.fila)}${cell.numero}</h3>
                <button onclick="chiudiPannello()" class="text-gray-400 hover:text-gray-600">${iconSvg('close')}</button>
            </div>
            <div class="bg-gray-100 rounded-xl p-4 text-center mb-4">
                <div class="text-gray-500 text-sm font-medium mb-1">Stato</div>
                <div class="font-bold text-gray-700">In manutenzione</div>
            </div>
            <button onclick="toggleManutenzione(${cell.id}, 'attiva')"
                    class="w-full bg-emerald-600 text-white py-2.5 rounded-xl font-semibold hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2">
                ${iconSvg('check')} Riattiva postazione
            </button>
        </div>
    `);
}

async function submitRapida(e, postazioneId) {
    e.preventDefault();
    const f = e.target;
    const fd = new FormData(f);
    const errEl = document.getElementById('errRapida');
    errEl.classList.add('hidden');
    const payload = {
        postazione_id: postazioneId,
        tipo_prenotazione: fd.get('tipo_prenotazione'),
        data_inizio: fd.get('data_inizio'),
        data_fine: fd.get('data_fine'),
        nome: fd.get('nome'),
        cognome: fd.get('cognome'),
        email: 'cassa@lidotorreconca.it',
        telefono: fd.get('telefono') || '0000',
        adulti: 2, bambini: 0, extra_lettino: 0, navetta: 0, note: '',
    };
    const btn = f.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.textContent = 'Prenotazione…';
    try {
        const res = await fetch('/admin/api/pos_prenota.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.ok) {
            showToast(`Prenotato! Codice: ${data.codice}`, 'success');
            chiudiPannello();
            setTimeout(() => refreshMappa(), 500);
        } else {
            errEl.textContent = data.error || 'Errore';
            errEl.classList.remove('hidden');
        }
    } catch(err) {
        errEl.textContent = 'Errore di rete';
        errEl.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.textContent = 'Prenota';
}

async function aggiornaStatoMappa(prenId, stato) {
    if (!confirm(`Impostare stato "${stato}"?`)) return;
    const res = await fetch('/admin/api/aggiorna_stato.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id: prenId, stato}),
    });
    const data = await res.json();
    if (data.ok) {
        showToast('Aggiornato', 'success');
        chiudiPannello();
        setTimeout(() => refreshMappa(), 500);
    } else {
        showToast(data.error || 'Errore', 'error');
    }
}

async function toggleManutenzione(postazioneId, nuovoStato) {
    const lbl = nuovoStato === 'manutenzione' ? 'mettere in manutenzione' : 'riattivare';
    if (!confirm(`Vuoi ${lbl} questa postazione?`)) return;
    const res = await fetch('/admin/api/aggiorna_postazione.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id: postazioneId, stato: nuovoStato}),
    });
    const data = await res.json();
    if (data.ok) {
        showToast('Postazione aggiornata', 'success');
        chiudiPannello();
        setTimeout(() => refreshMappa(), 500);
    } else {
        showToast(data.error || 'Errore', 'error');
    }
}

async function refreshMappa() {
    const btn = document.getElementById('btnRefresh');
    btn.disabled = true;
    const d = document.getElementById('dataSelector').value;
    try {
        const res = await fetch(`/admin/api/mappa_status.php?data=${encodeURIComponent(d)}`);
        const data = await res.json();
        if (data.postazioni) {
            data.postazioni.forEach(cell => {
                const el = document.querySelector(`[data-postazione-id="${cell.id}"]`);
                if (!el) return;
                el.className = el.className.replace(/\b(libera|occupata|in_attesa|manutenzione)\b/g, cell.classe);
                el.onclick = () => handleClick(cell);
            });
        }
    } catch(e) {}
    btn.disabled = false;
}

function iconSvg(name) {
    const icons = {
        close: '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        check: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        x: '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    };
    return icons[name] || '';
}

function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
function formatDate(d) {
    if (!d) return '';
    const p = d.split('-');
    return `${p[2]}/${p[1]}/${p[0]}`;
}

// Auto-refresh ogni 30 secondi
setInterval(refreshMappa, 30000);
</script>
</body>
</html>
