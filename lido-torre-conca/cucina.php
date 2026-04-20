<?php
// ── Display Cucina — accesso pubblico interno (rete locale) ──
// Protetto da PIN semplice o da sessione admin
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';

// PIN di accesso cucina (configurabile)
define('CUCINA_PIN', '1234');

$autoCucina = $_SESSION['cucina_auth'] ?? false;
if (!$autoCucina && !empty($_SESSION['admin_id'])) $autoCucina = true;

if (!$autoCucina) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pin'])) {
        if ($_POST['pin'] === CUCINA_PIN) {
            $_SESSION['cucina_auth'] = true;
            $autoCucina = true;
        } else {
            $pinError = 'PIN errato';
        }
    }
    if (!$autoCucina):
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso Cucina – Lido Torre Conca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{ocean:{'DEFAULT':'#0c2340'},sand:{'DEFAULT':'#d4a847'}}}}}</script>
</head>
<body class="bg-ocean min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-8 w-full max-w-xs text-center shadow-2xl">
        <div class="w-16 h-16 bg-ocean/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-ocean" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
        </div>
        <h1 class="font-bold text-ocean text-xl mb-1">Display Cucina</h1>
        <p class="text-gray-400 text-sm mb-6">Lido Torre Conca</p>
        <?php if (!empty($pinError)): ?>
        <div class="bg-red-50 text-red-600 text-sm rounded-xl px-4 py-2 mb-4"><?= h($pinError) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="pin" class="w-full text-center text-2xl font-bold border-2 border-gray-200 rounded-xl h-14 mb-4 focus:outline-none focus:border-ocean" placeholder="PIN" autofocus maxlength="8">
            <button class="w-full h-12 bg-ocean text-white font-bold rounded-xl">Accedi</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
    endif;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cucina – Lido Torre Conca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{ocean:{'DEFAULT':'#0c2340','light':'#1a3a60'},sand:{'DEFAULT':'#d4a847'}},fontFamily:{body:['"Inter"','system-ui','sans-serif']}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <style>
        body { background: #f1f5f9; }
        .ticket { transition: all .3s; }
        .ticket.entrando { animation: slideIn .4s ease; }
        @keyframes slideIn { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:translateY(0); } }
    </style>
</head>
<body class="font-body">

<!-- Topbar -->
<div class="bg-ocean text-white px-4 py-3 flex items-center justify-between sticky top-0 z-40 shadow-lg">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-sand rounded-lg flex items-center justify-center">
            <?= icon('food','w-4 h-4','#0c2340') ?>
        </div>
        <div>
            <div class="font-bold text-sm">Cucina · Lido Torre Conca</div>
            <div class="text-blue-300 text-xs" id="clock-display">--:--</div>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-1.5 text-xs">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
            <span class="text-green-300">In diretta</span>
        </div>
        <a href="/admin/index.php" class="text-blue-300 hover:text-white text-xs">Admin</a>
    </div>
</div>

<!-- KPI bar -->
<div class="bg-white shadow-sm border-b border-gray-100 px-4 py-3">
    <div class="max-w-6xl mx-auto flex flex-wrap gap-4 text-center">
        <div class="flex-1 min-w-[80px]">
            <div id="kpi-nuovi" class="text-2xl font-bold text-red-500">0</div>
            <div class="text-xs text-gray-400 uppercase tracking-wide">Nuovi</div>
        </div>
        <div class="flex-1 min-w-[80px]">
            <div id="kpi-corso" class="text-2xl font-bold text-blue-500">0</div>
            <div class="text-xs text-gray-400 uppercase tracking-wide">In corso</div>
        </div>
        <div class="flex-1 min-w-[80px]">
            <div id="kpi-pronti" class="text-2xl font-bold text-green-500">0</div>
            <div class="text-xs text-gray-400 uppercase tracking-wide">Pronti</div>
        </div>
        <div class="flex-1 min-w-[80px]">
            <div id="kpi-consegnati" class="text-2xl font-bold text-gray-400">0</div>
            <div class="text-xs text-gray-400 uppercase tracking-wide">Consegnati oggi</div>
        </div>
    </div>
</div>

<!-- Board -->
<div class="max-w-6xl mx-auto px-4 py-5">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <!-- Nuovi ordini -->
        <div>
            <h2 class="font-bold text-red-600 text-sm uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span> Nuovi
            </h2>
            <div id="col-nuovi" class="space-y-3">
                <div class="text-gray-300 text-sm text-center py-8">Nessun ordine</div>
            </div>
        </div>

        <!-- In preparazione -->
        <div>
            <h2 class="font-bold text-blue-600 text-sm uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-blue-500 rounded-full"></span> In preparazione
            </h2>
            <div id="col-corso" class="space-y-3">
                <div class="text-gray-300 text-sm text-center py-8">Nessun ordine</div>
            </div>
        </div>

        <!-- Pronti -->
        <div>
            <h2 class="font-bold text-green-600 text-sm uppercase tracking-wider mb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span> Pronti
            </h2>
            <div id="col-pronti" class="space-y-3">
                <div class="text-gray-300 text-sm text-center py-8">Nessun ordine</div>
            </div>
        </div>
    </div>
</div>

<!-- Notifica audio hidden -->
<audio id="notif-audio" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAA..." type="audio/wav">
</audio>

<script>
let lastKnownIds = new Set();

// Clock
function updateClock() {
    const now = new Date();
    document.getElementById('clock-display').textContent =
        now.toLocaleTimeString('it-IT', { hour:'2-digit', minute:'2-digit' });
}
setInterval(updateClock, 1000);
updateClock();

// Carica ordini
async function loadOrdini() {
    try {
        const res = await fetch('/admin/api/ordini_cucina.php');
        const data = await res.json();
        if (!data.ok) return;
        renderBoard(data.ordini);
    } catch {}
}

function aggiornaStato(id, stato) {
    fetch('/api/stato_ordine.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, stato })
    }).then(() => loadOrdini());
}

function renderTicket(o) {
    const minuti = Math.floor((Date.now() - new Date(o.created_at).getTime()) / 60000);
    const urgente = minuti > 15;
    const postLabel = o.fila ? `Fila ${o.fila}·${o.numero}` : (o.tipo_origine === 'ristorante' ? 'Ristorante' : 'Banco');

    const azioni = {
        nuovo:     `<button onclick="aggiornaStato(${o.id},'in_corso')"    class="btn-corsia bg-blue-500 text-white">Inizia</button>`,
        in_corso:  `<button onclick="aggiornaStato(${o.id},'pronto')"      class="btn-corsia bg-green-500 text-white">Pronto</button>`,
        pronto:    `<button onclick="aggiornaStato(${o.id},'consegnato')"  class="btn-corsia bg-gray-500 text-white">Consegnato</button>`,
    };

    return `
    <div class="order-ticket ${o.stato} ticket" data-id="${o.id}">
        <div class="flex items-start justify-between mb-2">
            <div>
                <div class="font-bold text-ocean text-lg tracking-widest">#${o.codice}</div>
                <div class="text-gray-500 text-xs">${o.nome_cliente} · ${postLabel}</div>
            </div>
            <div class="text-right">
                <div class="text-xs ${urgente ? 'text-red-500 font-bold' : 'text-gray-400'}">${minuti}m fa</div>
                <div class="font-bold text-ocean">€${parseFloat(o.totale).toFixed(2)}</div>
            </div>
        </div>
        <ul class="space-y-1 mb-3 text-sm">
            ${(o.items||[]).map(it => `<li class="flex justify-between"><span>${it.nome_item}${it.note ? ` <em class="text-xs text-gray-400">${it.note}</em>` : ''}</span><span class="font-medium">×${it.quantita}</span></li>`).join('')}
        </ul>
        ${o.note ? `<div class="text-xs text-yellow-700 bg-yellow-50 rounded-lg px-2 py-1 mb-2">${o.note}</div>` : ''}
        <div class="flex gap-2">
            ${azioni[o.stato] || ''}
            <button onclick="aggiornaStato(${o.id},'annullato')" class="btn-corsia bg-red-100 text-red-600 text-xs">Annulla</button>
        </div>
    </div>`;
}

const btnCorsia = `<style>.btn-corsia{padding:.35rem .8rem;border-radius:.6rem;font-size:.8rem;font-weight:700;transition:all .2s;cursor:pointer;}.btn-corsia:hover{opacity:.85;transform:scale(.97)}</style>`;
document.head.insertAdjacentHTML('beforeend', btnCorsia);

function renderBoard(ordini) {
    const cols = { nuovo: [], in_corso: [], pronto: [] };
    let consegnati = 0;
    ordini.forEach(o => {
        if (cols[o.stato] !== undefined) cols[o.stato].push(o);
        if (o.stato === 'consegnato') consegnati++;
    });

    // Suona se ci sono nuovi ordini non noti
    const nuoviIds = new Set(cols.nuovo.map(o => o.id));
    const hasNew = [...nuoviIds].some(id => !lastKnownIds.has(id));
    if (hasNew && lastKnownIds.size > 0) {
        document.getElementById('notif-audio').play().catch(() => {});
    }
    lastKnownIds = new Set(ordini.map(o => o.id));

    ['nuovo','in_corso','pronto'].forEach(stato => {
        const col = document.getElementById(`col-${stato === 'in_corso' ? 'corso' : stato}`);
        col.innerHTML = cols[stato].length
            ? cols[stato].map(renderTicket).join('')
            : '<div class="text-gray-300 text-sm text-center py-8">Nessun ordine</div>';
    });

    document.getElementById('kpi-nuovi').textContent    = cols.nuovo.length;
    document.getElementById('kpi-corso').textContent    = cols.in_corso.length;
    document.getElementById('kpi-pronti').textContent   = cols.pronto.length;
    document.getElementById('kpi-consegnati').textContent = consegnati;
}

loadOrdini();
setInterval(loadOrdini, 8000); // refresh ogni 8 secondi
</script>
</body>
</html>
