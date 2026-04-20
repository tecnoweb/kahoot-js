<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'POS Cassa';

$oggi = date('Y-m-d');

// Carica prezzi per il calcolo JS
$prezziDB = fetchAll('SELECT tipo, prezzo FROM prezzi');
$prezziMap = [];
foreach ($prezziDB as $p) {
    $prezziMap[$p['tipo']] = (float)$p['prezzo'];
}

// Carica mappa postazioni per oggi
$postazioniRaw = fetchAll(
    "SELECT p.id, p.fila, p.numero, p.numero_globale, p.stato AS stato_postazione,
            pr.id AS pren_id, pr.nome, pr.cognome, pr.tipo_prenotazione, pr.stato AS stato_pren
     FROM postazioni p
     LEFT JOIN prenotazioni pr ON pr.postazione_id = p.id
         AND pr.stato != 'annullata'
         AND pr.data_inizio <= :data AND pr.data_fine >= :data2
     ORDER BY p.fila, p.numero",
    [':data' => $oggi, ':data2' => $oggi]
);
$filaLetters = ['A','B','C','D','E','F'];
$postazioniByFila = [];
foreach ($postazioniRaw as $p) {
    $postazioniByFila[$p['fila']][$p['numero']] = $p;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>POS Cassa – Lido Torre Conca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { ocean:{'DEFAULT':'#0c2340','light':'#1a3a60','dark':'#08182d'}, sand:{'DEFAULT':'#d4a847','light':'#f5e6c8','dark':'#a07c2a'} }, fontFamily: { body:['"Inter"','system-ui','sans-serif'] } } } };</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { overflow: hidden; }
        @media (max-width: 767px) { body { overflow: auto; } }
        .pos-cell { cursor: pointer; transition: all 0.12s ease; user-select: none; -webkit-tap-highlight-color: transparent; }
        .pos-cell:active { transform: scale(0.92); }
        .pos-cell.libera   { background:#d1fae5; border-color:#10b981; }
        .pos-cell.occupata { background:#fee2e2; border-color:#ef4444; }
        .pos-cell.in_attesa { background:#fef3c7; border-color:#f59e0b; }
        .pos-cell.manutenzione { background:#e5e7eb; border-color:#9ca3af; cursor:not-allowed; }
        .pos-cell.selected { background:#0c2340 !important; border-color:#d4a847 !important; color:white !important; ring: 2px solid #d4a847; }
        .prezzo-display { font-variant-numeric: tabular-nums; font-feature-settings: "tnum"; }
        .toggle-on  { background:#0c2340; color:white; }
        .toggle-off { background:#f3f4f6; color:#374151; }
        input[type="date"]::-webkit-calendar-picker-indicator { cursor: pointer; }
    </style>
</head>
<body class="font-body bg-gray-100">

<!-- Toast container -->
<div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none"></div>

<!-- POS Header -->
<header class="bg-ocean-dark text-white px-4 py-2.5 flex items-center justify-between fixed top-0 left-0 right-0 z-50 shadow-md">
    <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 bg-sand rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-ocean" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 12a11.05 11.05 0 00-22 0zm-5 7a3 3 0 01-6 0v-7"/>
            </svg>
        </div>
        <span class="font-bold text-sm">POS Cassa</span>
        <span class="hidden sm:block text-white/40 text-xs">Torre Conca</span>
    </div>
    <div class="flex items-center gap-2">
        <div id="liveClock" class="text-white/70 text-xs font-mono hidden sm:block"></div>
        <a href="/admin/index.php" class="text-white/70 hover:text-white text-xs px-3 py-1.5 rounded-lg border border-white/20 hover:border-white/40 transition-colors">
            Admin
        </a>
        <a href="/admin/logout.php" class="text-white/50 hover:text-white text-xs px-2 py-1.5 rounded-lg hover:bg-white/10 transition-colors">
            Esci
        </a>
    </div>
</header>

<div class="pt-12 h-screen flex flex-col md:flex-row overflow-hidden md:overflow-hidden">

<!-- ── COLONNA SINISTRA: Mappa ───────────────────────────── -->
<div class="md:w-[58%] lg:w-[60%] flex flex-col bg-white border-r border-gray-200 overflow-hidden">

    <!-- Toolbar mappa -->
    <div class="px-3 py-2.5 border-b border-gray-100 flex items-center gap-2 flex-shrink-0 flex-wrap">
        <div class="relative flex-1 min-w-0">
            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <input type="text" id="cercaPosto" placeholder="Cerca posto (es. A3, 15…)"
                   class="w-full pl-8 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30"
                   oninput="filtraPosti(this.value)">
        </div>
        <select id="filtroStato" onchange="filtraPosti(document.getElementById('cercaPosto').value)"
                class="px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-ocean/30">
            <option value="">Tutti</option>
            <option value="libera">Solo liberi</option>
            <option value="occupata">Solo occupati</option>
        </select>
    </div>

    <!-- Mappa scrollabile -->
    <div class="flex-1 overflow-auto p-3">
        <!-- Indicatore mare -->
        <div class="text-center text-xs font-semibold text-blue-500 bg-blue-50 py-1.5 rounded-lg mb-2 tracking-wider">MARE</div>

        <!-- Intestazione colonne -->
        <div class="grid gap-1 mb-1" style="grid-template-columns: 1.4rem repeat(10, minmax(0,1fr))">
            <div></div>
            <?php for ($n = 1; $n <= 10; $n++): ?>
            <div class="text-center text-[9px] font-bold text-gray-400"><?= $n ?></div>
            <?php endfor; ?>
        </div>

        <!-- Righe -->
        <?php foreach ($filaLetters as $fila): ?>
        <div class="grid gap-1 mb-1" style="grid-template-columns: 1.4rem repeat(10, minmax(0,1fr))">
            <div class="flex items-center justify-center">
                <span class="text-[9px] font-bold text-gray-500 w-5 h-5 bg-gray-100 rounded-full flex items-center justify-center"><?= $fila ?></span>
            </div>
            <?php for ($n = 1; $n <= 10; $n++):
                $c = $postazioniByFila[$fila][$n] ?? null;
                if (!$c) continue;
                if ($c['stato_postazione'] === 'manutenzione') {
                    $classe = 'manutenzione';
                } elseif ($c['pren_id']) {
                    $classe = ($c['stato_pren'] === 'in_attesa') ? 'in_attesa' : 'occupata';
                } else {
                    $classe = 'libera';
                }
            ?>
            <button class="pos-cell <?= $classe ?> border-2 rounded aspect-square flex items-center justify-center text-[8px] font-bold leading-none"
                    data-id="<?= (int)$c['id'] ?>"
                    data-fila="<?= $fila ?>"
                    data-numero="<?= $n ?>"
                    data-classe="<?= $classe ?>"
                    <?= $classe !== 'manutenzione' && !$c['pren_id'] ? "onclick=\"selezionaPosto({$c['id']}, '{$fila}', {$n})\"" : '' ?>
                    title="<?= $fila ?><?= $n ?> – <?= ucfirst($classe) ?>">
                <?= $fila ?><?= $n ?>
            </button>
            <?php endfor; ?>
        </div>
        <?php endforeach; ?>

        <div class="text-center text-xs font-semibold text-sand bg-sand/10 py-1.5 rounded-lg mt-2 tracking-wider">ENTRATA</div>

        <!-- Legenda -->
        <div class="flex flex-wrap gap-2 mt-3 text-[10px] font-medium text-gray-500">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded border-2 border-emerald-500 bg-green-100 inline-block"></span>Libera</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded border-2 border-red-400 bg-red-100 inline-block"></span>Occupata</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded border-2 border-amber-400 bg-amber-100 inline-block"></span>In attesa</span>
        </div>
    </div>
</div>

<!-- ── COLONNA DESTRA: Scontrino ─────────────────────────── -->
<div class="md:w-[42%] lg:w-[40%] flex flex-col bg-gray-50 overflow-hidden" id="colScontrino">

    <!-- Stato: nessuna selezione -->
    <div id="statoVuoto" class="flex flex-col items-center justify-center flex-1 text-center p-8">
        <div class="w-16 h-16 bg-ocean/10 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-9 h-9 text-ocean/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 12a11.05 11.05 0 00-22 0zm-5 7a3 3 0 01-6 0v-7"/>
            </svg>
        </div>
        <p class="text-gray-400 font-medium">Seleziona una postazione libera dalla mappa</p>
        <p class="text-gray-300 text-sm mt-1">o usa la ricerca in alto</p>
    </div>

    <!-- Stato: form POS -->
    <div id="statoForm" class="hidden flex-col flex-1 overflow-hidden">

        <!-- Postazione selezionata -->
        <div class="flex items-center justify-between px-4 py-3 bg-ocean-dark text-white flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-sand rounded-xl flex items-center justify-center font-black text-ocean text-sm" id="posLabel">A1</div>
                <div>
                    <div class="text-xs text-white/60">Postazione selezionata</div>
                    <div class="font-bold text-sm" id="posLabelFull">Fila A – Posto 1</div>
                </div>
            </div>
            <button onclick="resetPOS()" class="text-white/50 hover:text-white p-1.5 rounded-lg hover:bg-white/10">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <!-- Corpo form -->
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4">

            <!-- Tipo prenotazione -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tipo prenotazione</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="setTipo('giornata_intera', this)"
                            class="tipo-btn bg-ocean text-white border-2 border-ocean py-3 rounded-xl font-bold text-sm transition-all">
                        Giornata<br><span class="text-xs font-normal opacity-80">08:30–19:00</span>
                    </button>
                    <button type="button" onclick="setTipo('mezza_giornata', this)"
                            class="tipo-btn bg-gray-100 text-gray-700 border-2 border-gray-200 py-3 rounded-xl font-bold text-sm transition-all hover:bg-gray-200">
                        Mezza<br><span class="text-xs font-normal opacity-80">14:00–19:00</span>
                    </button>
                    <button type="button" onclick="setTipo('settimanale', this)"
                            class="tipo-btn bg-gray-100 text-gray-700 border-2 border-gray-200 py-3 rounded-xl font-bold text-sm transition-all hover:bg-gray-200">
                        Settimanale<br><span class="text-xs font-normal opacity-80">7 giorni</span>
                    </button>
                    <button type="button" onclick="setTipo('mensile', this)"
                            class="tipo-btn bg-gray-100 text-gray-700 border-2 border-gray-200 py-3 rounded-xl font-bold text-sm transition-all hover:bg-gray-200">
                        Mensile<br><span class="text-xs font-normal opacity-80">30 giorni</span>
                    </button>
                </div>
            </div>

            <!-- Date -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Dal</label>
                    <input type="date" id="posDataInizio" value="<?= $oggi ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30 bg-white"
                           onchange="calcolaPrezzo()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Al</label>
                    <input type="date" id="posDataFine" value="<?= $oggi ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30 bg-white"
                           onchange="calcolaPrezzo()">
                </div>
            </div>

            <!-- Extra -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Extra</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" id="btnLettino" onclick="toggleExtra('lettino')"
                            class="toggle-off flex items-center justify-between px-4 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition-all">
                        <span>+Lettino</span>
                        <span class="text-xs opacity-60">+€<span id="prLettino"><?= number_format($prezziMap['extra_lettino'] ?? 5, 0) ?></span>/g</span>
                    </button>
                    <button type="button" id="btnNavetta" onclick="toggleExtra('navetta')"
                            class="toggle-off flex items-center justify-between px-4 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition-all">
                        <span>+Navetta</span>
                        <span class="text-xs opacity-60">+€<span id="prNavetta"><?= number_format($prezziMap['navetta'] ?? 3, 0) ?></span>/g</span>
                    </button>
                </div>
            </div>

            <!-- Persone -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Adulti</label>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="adj('adulti',-1)" class="w-8 h-8 bg-gray-200 rounded-lg font-bold text-gray-600 hover:bg-gray-300 flex-shrink-0">−</button>
                        <input type="number" id="posAdulti" value="2" min="1" max="10" class="flex-1 text-center border border-gray-200 rounded-lg py-1.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-ocean/30 bg-white">
                        <button type="button" onclick="adj('adulti',1)" class="w-8 h-8 bg-gray-200 rounded-lg font-bold text-gray-600 hover:bg-gray-300 flex-shrink-0">+</button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Bambini</label>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="adj('bambini',-1)" class="w-8 h-8 bg-gray-200 rounded-lg font-bold text-gray-600 hover:bg-gray-300 flex-shrink-0">−</button>
                        <input type="number" id="posBambini" value="0" min="0" max="10" class="flex-1 text-center border border-gray-200 rounded-lg py-1.5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-ocean/30 bg-white">
                        <button type="button" onclick="adj('bambini',1)" class="w-8 h-8 bg-gray-200 rounded-lg font-bold text-gray-600 hover:bg-gray-300 flex-shrink-0">+</button>
                    </div>
                </div>
            </div>

            <!-- Dati cliente -->
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Dati Cliente</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" id="posNome" placeholder="Nome *"
                           class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30 bg-white">
                    <input type="text" id="posCognome" placeholder="Cognome"
                           class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30 bg-white">
                </div>
                <input type="tel" id="posTelefono" placeholder="Telefono"
                       class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30 bg-white">
            </div>

        </div><!-- /overflow-y-auto -->

        <!-- Scontrino fisso in basso -->
        <div class="flex-shrink-0 bg-white border-t-2 border-gray-100 p-4 space-y-3">
            <!-- Riepilogo righe -->
            <div id="righePrezzo" class="space-y-1 text-sm text-gray-600"></div>

            <!-- Totale grande -->
            <div class="bg-ocean-dark rounded-2xl px-5 py-4 flex items-center justify-between">
                <span class="text-white/70 text-sm font-medium">TOTALE</span>
                <span class="prezzo-display text-white font-black text-4xl" id="totalePOS">€0</span>
            </div>

            <!-- Errore -->
            <div id="errPOS" class="hidden bg-red-50 text-red-600 text-xs px-3 py-2 rounded-lg border border-red-100"></div>

            <!-- Bottoni azione -->
            <div class="grid grid-cols-3 gap-2">
                <button onclick="confermaVendita(true)"
                        class="col-span-2 bg-sand hover:bg-sand-dark text-ocean font-black py-3.5 rounded-xl text-sm flex items-center justify-center gap-2 transition-colors active:scale-95">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    CONFERMA + STAMPA
                </button>
                <button onclick="confermaVendita(false)"
                        class="bg-ocean hover:bg-ocean-light text-white font-black py-3.5 rounded-xl text-sm flex items-center justify-center transition-colors active:scale-95">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </div>
            <button onclick="resetPOS()"
                    class="w-full border border-gray-200 text-gray-500 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">
                Annulla / Nuova vendita
            </button>
        </div>
    </div>

    <!-- Stato: conferma completata -->
    <div id="statoConferma" class="hidden flex-col items-center justify-center flex-1 p-8 text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-1">Vendita completata!</h2>
        <div class="text-3xl font-black text-ocean mb-4" id="confCodice">XXXXXXXX</div>
        <div class="bg-gray-50 rounded-2xl p-4 w-full max-w-xs text-sm space-y-1.5 mb-6">
            <div class="flex justify-between"><span class="text-gray-500">Postazione</span><span id="confPosto" class="font-bold"></span></div>
            <div class="flex justify-between"><span class="text-gray-500">Cliente</span><span id="confCliente" class="font-semibold"></span></div>
            <div class="flex justify-between"><span class="text-gray-500">Tipo</span><span id="confTipo" class="font-medium"></span></div>
            <div class="flex justify-between"><span class="text-gray-500">Date</span><span id="confDate" class="font-medium"></span></div>
            <div class="flex justify-between border-t border-gray-200 pt-2 mt-2"><span class="font-bold text-gray-700">Totale</span><span id="confTotale" class="font-black text-green-600"></span></div>
        </div>
        <div class="flex gap-3 w-full max-w-xs">
            <button onclick="resetPOS()"
                    class="flex-1 bg-ocean text-white py-3 rounded-xl font-bold hover:bg-ocean-light transition-colors">
                Nuova vendita
            </button>
            <button onclick="window.print()"
                    class="px-4 bg-gray-100 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            </button>
        </div>
    </div>

</div><!-- /col destra -->
</div><!-- /main flex -->

<script>
const PREZZI = <?= json_encode($prezziMap, JSON_UNESCAPED_UNICODE) ?>;

let stato = {
    postazioneId: null,
    fila: null,
    numero: null,
    tipo: 'giornata_intera',
    lettino: false,
    navetta: false,
};

// Clock live
function updateClock() {
    const el = document.getElementById('liveClock');
    if (!el) return;
    const d = new Date();
    el.textContent = d.toLocaleDateString('it-IT',{weekday:'short',day:'2-digit',month:'2-digit'}) + ' ' +
                     d.toLocaleTimeString('it-IT',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
updateClock(); setInterval(updateClock, 1000);

function selezionaPosto(id, fila, numero) {
    // Deseleziona precedente
    document.querySelectorAll('.pos-cell.selected').forEach(el => {
        const cl = el.dataset.classe;
        el.classList.remove('selected');
        el.classList.add(cl);
    });
    // Seleziona corrente
    const el = document.querySelector(`[data-id="${id}"]`);
    if (el) {
        el.classList.remove('libera','occupata','in_attesa');
        el.classList.add('selected');
    }
    stato.postazioneId = id;
    stato.fila = fila;
    stato.numero = numero;

    document.getElementById('posLabel').textContent = `${fila}${numero}`;
    document.getElementById('posLabelFull').textContent = `Fila ${fila} – Posto ${numero}`;
    document.getElementById('statoVuoto').classList.add('hidden');
    document.getElementById('statoConferma').classList.add('hidden');
    document.getElementById('statoForm').classList.remove('hidden');
    document.getElementById('statoForm').classList.add('flex');
    document.getElementById('posNome').focus();
    calcolaPrezzo();
}

function setTipo(tipo, btn) {
    stato.tipo = tipo;
    document.querySelectorAll('.tipo-btn').forEach(b => {
        b.className = b.className.replace('bg-ocean text-white border-ocean','bg-gray-100 text-gray-700 border-gray-200');
        b.classList.add('hover:bg-gray-200');
    });
    btn.className = btn.className.replace('bg-gray-100 text-gray-700 border-gray-200','bg-ocean text-white border-ocean');
    btn.classList.remove('hover:bg-gray-200');

    // Aggiusta date di default
    const oggi = new Date().toISOString().split('T')[0];
    let fine = oggi;
    if (tipo === 'settimanale') {
        const d = new Date(); d.setDate(d.getDate()+6);
        fine = d.toISOString().split('T')[0];
    } else if (tipo === 'mensile') {
        const d = new Date(); d.setDate(d.getDate()+29);
        fine = d.toISOString().split('T')[0];
    }
    document.getElementById('posDataFine').value = fine;
    calcolaPrezzo();
}

function toggleExtra(tipo) {
    if (tipo === 'lettino') {
        stato.lettino = !stato.lettino;
        const btn = document.getElementById('btnLettino');
        btn.className = stato.lettino
            ? btn.className.replace('toggle-off','toggle-on').replace('border-gray-200','border-ocean')
            : btn.className.replace('toggle-on','toggle-off').replace('border-ocean','border-gray-200');
    } else {
        stato.navetta = !stato.navetta;
        const btn = document.getElementById('btnNavetta');
        btn.className = stato.navetta
            ? btn.className.replace('toggle-off','toggle-on').replace('border-gray-200','border-ocean')
            : btn.className.replace('toggle-on','toggle-off').replace('border-ocean','border-gray-200');
    }
    calcolaPrezzo();
}

function adj(field, delta) {
    const el = document.getElementById(field === 'adulti' ? 'posAdulti' : 'posBambini');
    el.value = Math.max(field==='adulti'?1:0, Math.min(10, parseInt(el.value||0) + delta));
}

function giorniTra(d1, d2) {
    const a = new Date(d1), b = new Date(d2);
    if (isNaN(a) || isNaN(b)) return 1;
    return Math.max(1, Math.round((b - a) / 86400000) + 1);
}

function calcolaPrezzo() {
    const di = document.getElementById('posDataInizio').value;
    const df = document.getElementById('posDataFine').value;
    const gg = giorniTra(di, df);
    let base = 0;
    const tipo = stato.tipo;
    if (tipo === 'giornata_intera') base = (PREZZI['giornata_intera']||25) * gg;
    else if (tipo === 'mezza_giornata') base = (PREZZI['mezza_giornata']||15) * gg;
    else if (tipo === 'settimanale') base = PREZZI['settimanale']||140;
    else if (tipo === 'mensile') base = PREZZI['mensile']||450;

    let extra = 0;
    if (stato.lettino) extra += (PREZZI['extra_lettino']||5) * gg;
    if (stato.navetta) extra += (PREZZI['navetta']||3) * gg;
    const totale = Math.round((base + extra) * 100) / 100;

    const tipiLabel = {
        giornata_intera:'Giornata intera',mezza_giornata:'Mezza giornata',
        settimanale:'Settimanale',mensile:'Mensile'
    };

    let righe = `<div class="flex justify-between py-0.5"><span>${tipiLabel[tipo]||tipo} × ${gg}g</span><span class="font-medium">€${base.toFixed(2).replace('.',',')}</span></div>`;
    if (stato.lettino) righe += `<div class="flex justify-between py-0.5 text-blue-600"><span>Lettino extra × ${gg}g</span><span>+€${((PREZZI['extra_lettino']||5)*gg).toFixed(2).replace('.',',')}</span></div>`;
    if (stato.navetta) righe += `<div class="flex justify-between py-0.5 text-purple-600"><span>Navetta × ${gg}g</span><span>+€${((PREZZI['navetta']||3)*gg).toFixed(2).replace('.',',')}</span></div>`;

    document.getElementById('righePrezzo').innerHTML = righe;
    document.getElementById('totalePOS').textContent = `€${totale.toFixed(2).replace('.',',')}`;
    return totale;
}

async function confermaVendita(stampa) {
    const nome    = document.getElementById('posNome').value.trim();
    const cognome = document.getElementById('posCognome').value.trim();
    const telefono = document.getElementById('posTelefono').value.trim();
    const di = document.getElementById('posDataInizio').value;
    const df = document.getElementById('posDataFine').value;
    const errEl = document.getElementById('errPOS');

    errEl.classList.add('hidden');

    if (!stato.postazioneId) { errEl.textContent='Seleziona una postazione'; errEl.classList.remove('hidden'); return; }
    if (!nome) { errEl.textContent='Il nome è obbligatorio'; errEl.classList.remove('hidden'); return; }
    if (!di || !df) { errEl.textContent='Seleziona le date'; errEl.classList.remove('hidden'); return; }
    if (df < di) { errEl.textContent='La data fine deve essere >= data inizio'; errEl.classList.remove('hidden'); return; }

    const payload = {
        postazione_id: stato.postazioneId,
        tipo_prenotazione: stato.tipo,
        data_inizio: di,
        data_fine: df,
        nome: nome,
        cognome: cognome || nome,
        email: 'cassa@lidotorreconca.it',
        telefono: telefono || '0000',
        adulti: parseInt(document.getElementById('posAdulti').value)||2,
        bambini: parseInt(document.getElementById('posBambini').value)||0,
        extra_lettino: stato.lettino ? 1 : 0,
        navetta: stato.navetta ? 1 : 0,
        note: 'Vendita POS cassa',
    };

    // Disabilita bottoni
    document.querySelectorAll('#statoForm button[onclick*="confermaVendita"]').forEach(b => b.disabled = true);

    try {
        const res = await fetch('/admin/api/pos_prenota.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.ok) {
            mostraConferma(data, payload);
            if (stampa) setTimeout(() => window.print(), 400);
            // Aggiorna cella mappa
            const el = document.querySelector(`[data-id="${stato.postazioneId}"]`);
            if (el) {
                el.classList.remove('selected','libera');
                el.classList.add('occupata');
                el.dataset.classe = 'occupata';
                el.onclick = null;
            }
        } else {
            errEl.textContent = data.error || 'Errore nella creazione';
            errEl.classList.remove('hidden');
        }
    } catch(e) {
        errEl.textContent = 'Errore di rete';
        errEl.classList.remove('hidden');
    }
    document.querySelectorAll('#statoForm button[onclick*="confermaVendita"]').forEach(b => b.disabled = false);
}

function mostraConferma(data, payload) {
    const tipiLabel = {giornata_intera:'Giornata intera',mezza_giornata:'Mezza giornata',settimanale:'Settimanale',mensile:'Mensile'};
    document.getElementById('confCodice').textContent = data.codice;
    document.getElementById('confPosto').textContent = data.postazione;
    document.getElementById('confCliente').textContent = payload.nome + ' ' + (payload.cognome !== payload.nome ? payload.cognome : '');
    document.getElementById('confTipo').textContent = tipiLabel[payload.tipo_prenotazione]||payload.tipo_prenotazione;
    document.getElementById('confDate').textContent = formatDate(payload.data_inizio) + ' – ' + formatDate(payload.data_fine);
    document.getElementById('confTotale').textContent = '€' + parseFloat(data.prezzo_totale).toFixed(2).replace('.',',');

    document.getElementById('statoForm').classList.add('hidden');
    document.getElementById('statoForm').classList.remove('flex');
    document.getElementById('statoConferma').classList.remove('hidden');
    document.getElementById('statoConferma').classList.add('flex');
    showToast('Vendita completata! Codice: ' + data.codice, 'success');
}

function resetPOS() {
    // Deseleziona cella
    document.querySelectorAll('.pos-cell.selected').forEach(el => {
        const cl = el.dataset.classe || 'libera';
        el.classList.remove('selected');
        el.classList.add(cl);
    });
    stato = { postazioneId:null, fila:null, numero:null, tipo:'giornata_intera', lettino:false, navetta:false };
    // Reset form
    document.getElementById('posNome').value = '';
    document.getElementById('posCognome').value = '';
    document.getElementById('posTelefono').value = '';
    document.getElementById('posAdulti').value = '2';
    document.getElementById('posBambini').value = '0';
    document.getElementById('posDataInizio').value = new Date().toISOString().split('T')[0];
    document.getElementById('posDataFine').value = new Date().toISOString().split('T')[0];
    // Reset tipo buttons
    document.querySelectorAll('.tipo-btn').forEach((b,i) => {
        if (i===0) {
            b.className = b.className.replace('bg-gray-100 text-gray-700 border-gray-200','bg-ocean text-white border-ocean');
            b.classList.remove('hover:bg-gray-200');
        } else {
            b.className = b.className.replace('bg-ocean text-white border-ocean','bg-gray-100 text-gray-700 border-gray-200');
            b.classList.add('hover:bg-gray-200');
        }
    });
    // Reset extra
    stato.lettino = false; stato.navetta = false;
    ['btnLettino','btnNavetta'].forEach(id => {
        const b = document.getElementById(id);
        b.className = b.className.replace('toggle-on','toggle-off').replace('border-ocean','border-gray-200');
    });
    document.getElementById('statoVuoto').classList.remove('hidden');
    document.getElementById('statoForm').classList.add('hidden');
    document.getElementById('statoForm').classList.remove('flex');
    document.getElementById('statoConferma').classList.add('hidden');
    document.getElementById('statoConferma').classList.remove('flex');
}

function filtraPosti(q) {
    const filtroStato = document.getElementById('filtroStato').value;
    q = (q||'').toLowerCase().trim();
    document.querySelectorAll('.pos-cell').forEach(el => {
        const fila = el.dataset.fila || '';
        const num  = el.dataset.numero || '';
        const cl   = el.dataset.classe || '';
        const label = (fila + num).toLowerCase();
        const matchQ = !q || label.includes(q);
        const matchS = !filtroStato || cl === filtroStato;
        el.style.opacity = (matchQ && matchS) ? '1' : '0.15';
    });
}

function formatDate(d) {
    if (!d) return '';
    const p = d.split('-');
    return `${p[2]}/${p[1]}/${p[0]}`;
}

function showToast(msg, type='success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const colors = {success:'bg-green-600 text-white',error:'bg-red-600 text-white',info:'bg-ocean text-white'};
    toast.className = `pointer-events-auto flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-sm font-medium transition-all duration-300 ${colors[type]||colors.info}`;
    toast.textContent = msg;
    toast.style.opacity='0'; toast.style.transform='translateX(1rem)';
    container.appendChild(toast);
    requestAnimationFrame(() => { toast.style.opacity='1'; toast.style.transform='translateX(0)'; });
    setTimeout(() => { toast.style.opacity='0'; toast.style.transform='translateX(1rem)'; setTimeout(()=>toast.remove(),300); }, 3500);
}

// Init
calcolaPrezzo();
</script>
</body>
</html>
