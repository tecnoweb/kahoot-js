<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';
$pageTitle       = 'Prenota la tua Postazione';
$pageDescription = 'Prenota online lettini e ombrellone al Lido Torre Conca. Mappa interattiva, disponibilità in tempo reale, giornata intera o mezza giornata.';

// Prefill from quick-book bar
$qDataInizio = sanitizeStr($_GET['data_inizio'] ?? date('Y-m-d'));
$qDataFine   = sanitizeStr($_GET['data_fine']   ?? $qDataInizio);
$qTipo       = in_array($_GET['tipo'] ?? '', ['giornata_intera','mezza_giornata','settimanale','mensile'])
               ? $_GET['tipo'] : 'giornata_intera';

$prezziDB = [];
foreach (fetchAll('SELECT tipo, prezzo FROM prezzi') as $r) {
    $prezziDB[$r['tipo']] = (float)$r['prezzo'];
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page hero -->
<div class="bg-ocean pt-28 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Step indicator -->
        <div class="step-indicator mb-8 max-w-sm mx-auto">
            <div class="step active" id="step-ind-1">
                <div class="step-circle">1</div>
                <div class="step-label">Date</div>
            </div>
            <div class="step-line" id="line-1"></div>
            <div class="step" id="step-ind-2">
                <div class="step-circle">2</div>
                <div class="step-label">Postazione</div>
            </div>
            <div class="step-line" id="line-2"></div>
            <div class="step" id="step-ind-3">
                <div class="step-circle">3</div>
                <div class="step-label">Conferma</div>
            </div>
        </div>
        <h1 class="font-display text-3xl md:text-4xl font-bold text-white text-center">Prenota la tua Postazione</h1>
        <p class="text-blue-200 text-center mt-2 text-sm">Fino a 1 ora prima dell'arrivo · Lunedì – Domenica 08:30–19:00</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- ── STEP 1: Selezione date ─────────────────────────── -->
    <div id="step-1" class="mb-8">
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
            <h2 class="font-display text-xl font-semibold text-ocean mb-5 flex items-center gap-2">
                <?= icon('calendar','w-5 h-5','#0c2340') ?> Seleziona date e tipo
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="form-group">
                    <label class="form-label">Data arrivo</label>
                    <input type="date" id="data_inizio" class="form-input" min="<?= date('Y-m-d') ?>" value="<?= h($qDataInizio) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Data partenza</label>
                    <input type="date" id="data_fine" class="form-input" min="<?= date('Y-m-d') ?>" value="<?= h($qDataFine) ?>">
                </div>
                <div class="form-group sm:col-span-2 lg:col-span-1">
                    <label class="form-label">Tipo soggiorno</label>
                    <select id="tipo_prenotazione" class="form-input">
                        <option value="giornata_intera" <?= $qTipo==='giornata_intera'?'selected':'' ?>>Giornata Intera (08:30–19)</option>
                        <option value="mezza_giornata"  <?= $qTipo==='mezza_giornata' ?'selected':'' ?>>Mezza Giornata (14–19)</option>
                        <option value="settimanale"     <?= $qTipo==='settimanale'    ?'selected':'' ?>>Abbonamento Settimanale</option>
                        <option value="mensile"         <?= $qTipo==='mensile'        ?'selected':'' ?>>Abbonamento Mensile</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button id="btn-cerca" class="btn-primary w-full h-12 gap-2">
                        <?= icon('search','w-4 h-4') ?> Cerca disponibilità
                    </button>
                </div>
            </div>
            <!-- Info tipo -->
            <div id="tipo-info" class="mt-4 p-3 bg-sand-light/50 rounded-xl text-sm text-ocean hidden">
                <span id="tipo-info-text"></span>
            </div>
        </div>
    </div>

    <!-- ── STEP 2: Mappa ──────────────────────────────────── -->
    <div id="step-2" class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Mappa spiaggia -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                    <!-- Sea header -->
                    <div class="bg-gradient-to-b from-ocean to-wave px-4 py-3 flex items-center justify-center gap-2">
                        <?= icon('waves','w-5 h-5','white') ?>
                        <span class="text-white font-semibold text-sm tracking-widest uppercase">Mare</span>
                        <?= icon('waves','w-5 h-5','white') ?>
                    </div>

                    <!-- Grid -->
                    <div class="p-4 bg-amber-50/30 overflow-x-auto">
                        <div id="beach-grid" class="min-w-[320px]">
                            <!-- Populated by JS -->
                            <div class="text-center py-8 text-gray-400">
                                <div class="animate-spin w-8 h-8 border-2 border-ocean border-t-transparent rounded-full mx-auto mb-2"></div>
                                Clicca "Cerca disponibilità" per caricare la mappa
                            </div>
                        </div>
                    </div>

                    <!-- Stabilimento footer -->
                    <div class="bg-sand-light/60 px-4 py-3 flex items-center justify-center gap-2">
                        <?= icon('umbrella','w-4 h-4','#0c2340') ?>
                        <span class="text-ocean/70 font-medium text-xs tracking-widest uppercase">Stabilimento</span>
                    </div>

                    <!-- Legend -->
                    <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap gap-4 text-xs text-gray-600">
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-green-500 inline-block"></span> Libera</span>
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-red-400 inline-block"></span> Occupata</span>
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded border-2 border-yellow-400 inline-block"></span> Selezionata</span>
                        <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded bg-gray-300 inline-block"></span> Manutenzione</span>
                    </div>
                </div>
            </div>

            <!-- Form prenotazione (sidebar/bottom-sheet) -->
            <div class="lg:col-span-1">
                <div id="form-panel" class="bg-white rounded-2xl shadow-md border border-gray-100 p-5 sticky top-24">
                    <div id="form-empty" class="text-center py-8 text-gray-400">
                        <?= icon('chair','w-10 h-10 mx-auto mb-2','#cbd5e1') ?>
                        <p class="text-sm">Seleziona una postazione<br>dalla mappa per continuare</p>
                    </div>

                    <div id="form-booking" class="hidden">
                        <!-- Postazione selezionata -->
                        <div class="flex items-center justify-between mb-4 p-3 bg-ocean/5 rounded-xl">
                            <div>
                                <div class="text-xs text-gray-500 uppercase tracking-wide">Postazione selezionata</div>
                                <div class="font-display text-2xl font-bold text-ocean" id="label-postazione">—</div>
                                <div class="text-xs text-gray-500" id="label-date">—</div>
                            </div>
                            <?= icon('umbrella','w-8 h-8','#d4a847') ?>
                        </div>

                        <form id="form-prenota" novalidate>
                            <input type="hidden" id="f-postazione-id" name="postazione_id">
                            <input type="hidden" id="f-tipo" name="tipo_prenotazione">
                            <input type="hidden" id="f-data-inizio" name="data_inizio">
                            <input type="hidden" id="f-data-fine" name="data_fine">
                            <input type="hidden" id="csrf-token" name="csrf">

                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="form-group">
                                        <label class="form-label">Nome *</label>
                                        <input type="text" name="nome" class="form-input" placeholder="Mario" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Cognome *</label>
                                        <input type="text" name="cognome" class="form-input" placeholder="Rossi" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-input" placeholder="mario@email.it" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Telefono *</label>
                                    <input type="tel" name="telefono" class="form-input" placeholder="+39 347 000 0000" required>
                                </div>

                                <!-- Ospiti -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="form-group">
                                        <label class="form-label">Adulti</label>
                                        <div data-stepper class="flex items-center border border-gray-200 rounded-xl overflow-hidden h-12">
                                            <button type="button" data-stepper-minus class="px-3 h-full text-ocean font-bold text-lg hover:bg-gray-50">−</button>
                                            <input type="number" name="adulti" data-stepper-value data-min="1" data-max="6" value="2" class="flex-1 text-center text-base font-semibold border-0 bg-transparent focus:outline-none">
                                            <button type="button" data-stepper-plus  class="px-3 h-full text-ocean font-bold text-lg hover:bg-gray-50">+</button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Bambini</label>
                                        <div data-stepper class="flex items-center border border-gray-200 rounded-xl overflow-hidden h-12">
                                            <button type="button" data-stepper-minus class="px-3 h-full text-ocean font-bold text-lg hover:bg-gray-50">−</button>
                                            <input type="number" name="bambini" data-stepper-value data-min="0" data-max="6" value="0" class="flex-1 text-center text-base font-semibold border-0 bg-transparent focus:outline-none">
                                            <button type="button" data-stepper-plus  class="px-3 h-full text-ocean font-bold text-lg hover:bg-gray-50">+</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Extra -->
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-sand cursor-pointer transition-colors">
                                        <input type="checkbox" name="extra_lettino" id="chk-lettino" class="w-5 h-5 accent-ocean rounded">
                                        <div class="flex-1">
                                            <div class="font-medium text-sm text-ocean">Lettino Extra</div>
                                            <div class="text-xs text-gray-400">+€<?= number_format($prezziDB['extra_lettino'] ?? 5, 2) ?>/gg</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 hover:border-sand cursor-pointer transition-colors">
                                        <input type="checkbox" name="navetta" id="chk-navetta" class="w-5 h-5 accent-ocean rounded">
                                        <div class="flex-1">
                                            <div class="font-medium text-sm text-ocean">Servizio Navetta</div>
                                            <div class="text-xs text-gray-400">+€<?= number_format($prezziDB['navetta'] ?? 3, 2) ?>/gg</div>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Note</label>
                                    <textarea name="note" class="form-input" rows="2" placeholder="Esigenze particolari..."></textarea>
                                </div>

                                <!-- Totale -->
                                <div class="bg-ocean rounded-xl p-4 text-white">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Base</span><span id="price-base">€—</span>
                                    </div>
                                    <div id="price-extra-lettino-row" class="flex justify-between text-sm mb-1 hidden">
                                        <span>Lettino extra</span><span id="price-extra-lettino">€—</span>
                                    </div>
                                    <div id="price-navetta-row" class="flex justify-between text-sm mb-1 hidden">
                                        <span>Navetta</span><span id="price-navetta">€—</span>
                                    </div>
                                    <div class="border-t border-white/20 mt-2 pt-2 flex justify-between font-bold text-lg">
                                        <span>Totale</span><span id="price-totale">€—</span>
                                    </div>
                                </div>

                                <button type="submit" id="btn-prenota" class="btn-primary w-full h-14 text-base">
                                    <?= icon('check','w-5 h-5') ?> Conferma Prenotazione
                                </button>

                                <p class="text-xs text-gray-400 text-center">Riceverai conferma via email. Puoi annullare fino alla data di inizio.</p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── STEP 3: Conferma ───────────────────────────────── -->
    <div id="step-3" class="hidden">
        <div class="max-w-lg mx-auto bg-white rounded-2xl shadow-md border border-gray-100 p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <?= icon('check','w-8 h-8','#22c55e') ?>
            </div>
            <h2 class="font-display text-2xl font-bold text-ocean mb-2">Prenotazione Confermata!</h2>
            <p class="text-gray-500 mb-6">Riceverai una conferma. Mostra questo codice all'arrivo.</p>

            <div class="bg-ocean/5 rounded-2xl p-6 mb-6">
                <div class="text-xs text-gray-400 uppercase tracking-widest mb-1">Codice Prenotazione</div>
                <div id="conf-codice" class="font-display text-4xl font-bold text-ocean tracking-wider">—</div>
                <div class="mt-3 text-sm text-gray-600">
                    <span class="font-semibold" id="conf-postazione">—</span>
                    &nbsp;·&nbsp; <span id="conf-date">—</span>
                    &nbsp;·&nbsp; <span id="conf-tipo">—</span>
                </div>
                <div class="text-2xl font-bold text-sand mt-2" id="conf-prezzo">—</div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a id="btn-wa-conf" href="#" target="_blank" rel="noopener"
                   class="btn-sand flex items-center justify-center gap-2">
                    <?= icon('whatsapp','w-5 h-5','#0c2340') ?> Invia su WhatsApp
                </a>
                <button onclick="location.reload()" class="btn-outline">
                    Nuova Prenotazione
                </button>
            </div>
        </div>
    </div>

    <!-- ── Verifica prenotazione ──────────────────────────── -->
    <div class="mt-12 max-w-lg mx-auto">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-display text-lg font-semibold text-ocean mb-4 flex items-center gap-2">
                <?= icon('search','w-5 h-5','#0c2340') ?> Hai già una prenotazione?
            </h3>
            <div class="flex gap-3">
                <input type="text" id="verifica-codice" class="form-input flex-1 uppercase" placeholder="Es. AB12CD34" maxlength="12">
                <button id="btn-verifica" class="btn-primary px-5 h-12 flex-shrink-0">Cerca</button>
            </div>
            <div id="verifica-result" class="mt-4 hidden"></div>
        </div>
    </div>
</div>

<script>
const PREZZI = <?= json_encode($prezziDB, JSON_UNESCAPED_UNICODE) ?>;
const WA_NUM = '<?= WHATSAPP_NUM ?>';
let selectedPostazione = null;
let currentGG = 1;

// ── Fetch CSRF token ─────────────────────────────────────────
fetch('/api/csrf.php').then(r => r.json()).then(d => {
    document.getElementById('csrf-token').value = d.token;
}).catch(() => {});

// ── Tipo info labels ─────────────────────────────────────────
const tipoLabel = {
    giornata_intera: { label:'Giornata Intera', orario:'08:30 – 19:00' },
    mezza_giornata:  { label:'Mezza Giornata',  orario:'14:00 – 19:00' },
    settimanale:     { label:'Abbonamento Settimanale', orario:'7 giorni consecutivi' },
    mensile:         { label:'Abbonamento Mensile',     orario:'30 giorni consecutivi' },
};

document.getElementById('tipo_prenotazione').addEventListener('change', updateTipoInfo);
document.getElementById('data_inizio').addEventListener('change', function() {
    const fine = document.getElementById('data_fine');
    if (fine.value < this.value) fine.value = this.value;
    fine.min = this.value;
    autoSetDataFine();
});
document.getElementById('tipo_prenotazione').addEventListener('change', autoSetDataFine);

function autoSetDataFine() {
    const tipo = document.getElementById('tipo_prenotazione').value;
    const ini  = document.getElementById('data_inizio').value;
    const fine = document.getElementById('data_fine');
    if (tipo === 'settimanale') {
        fine.value = DateHelper.addDays(ini, 6);
    } else if (tipo === 'mensile') {
        fine.value = DateHelper.addDays(ini, 29);
    } else if (fine.value < ini) {
        fine.value = ini;
    }
}

function updateTipoInfo() {
    const tipo = document.getElementById('tipo_prenotazione').value;
    const info = tipoLabel[tipo];
    if (info) {
        document.getElementById('tipo-info-text').textContent = info.label + ' · ' + info.orario;
        document.getElementById('tipo-info').classList.remove('hidden');
    }
}
updateTipoInfo();
autoSetDataFine();

// ── Cerca disponibilità ──────────────────────────────────────
document.getElementById('btn-cerca').addEventListener('click', async function() {
    const ini  = document.getElementById('data_inizio').value;
    const fine = document.getElementById('data_fine').value;
    const tipo = document.getElementById('tipo_prenotazione').value;

    if (!ini) { Toast.error('Inserisci la data di arrivo'); return; }

    this.disabled = true;
    this.innerHTML = '<span class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full inline-block"></span> Carico...';

    try {
        const res = await apiFetch(`/api/disponibilita.php?data=${ini}&data_fine=${fine}&tipo=${tipo}`);
        if (!res.ok) throw new Error(res.errore || 'Errore');
        renderMappa(res.data);
        showStep(2);
        selectedPostazione = null;
        document.getElementById('form-empty').classList.remove('hidden');
        document.getElementById('form-booking').classList.add('hidden');
    } catch(e) {
        Toast.error(e.message || 'Impossibile caricare la disponibilità');
    } finally {
        this.disabled = false;
        this.innerHTML = '<?= addslashes(icon('search','w-4 h-4')) ?> Cerca disponibilità';
    }
});

// ── Render mappa ─────────────────────────────────────────────
function renderMappa(postazioni) {
    const grid = document.getElementById('beach-grid');
    const file = ['A','B','C','D','E','F'];
    const byFila = {};
    file.forEach(f => byFila[f] = []);
    postazioni.forEach(p => byFila[p.fila]?.push(p));

    let html = '';
    file.forEach(fila => {
        html += `<div class="flex items-center mb-2 gap-1">
            <span class="text-ocean font-bold text-xs w-5 flex-shrink-0 text-center">${fila}</span>
            <div class="flex flex-1 gap-1">`;
        (byFila[fila] || []).forEach(p => {
            let cls = p.stato === 'manutenzione' ? 'manutenzione' : (p.disponibile ? 'libera' : 'occupata');
            let clickable = cls === 'libera' ? `onclick="selectPostazione(${p.id},'${p.fila}',${p.numero})"` : '';
            let title = `Fila ${p.fila} · Pos. ${p.numero}${!p.disponibile ? ' (occupata)' : ''}`;
            html += `<div class="postazione ${cls} flex-1" data-id="${p.id}" title="${title}" ${clickable}></div>`;
        });
        html += `</div></div>`;
    });

    // Column numbers
    html = `<div class="flex mb-1 ml-6 gap-1">${Array.from({length:10},(_,i)=>`<div class="flex-1 text-center text-[10px] text-gray-400 font-medium">${i+1}</div>`).join('')}</div>` + html;
    grid.innerHTML = html;
}

// ── Seleziona postazione ─────────────────────────────────────
function selectPostazione(id, fila, numero) {
    document.querySelectorAll('.postazione.selezionata').forEach(el => el.classList.remove('selezionata'));
    document.querySelector(`.postazione[data-id="${id}"]`)?.classList.add('selezionata');

    selectedPostazione = { id, fila, numero };
    const ini  = document.getElementById('data_inizio').value;
    const fine = document.getElementById('data_fine').value;
    const tipo = document.getElementById('tipo_prenotazione').value;

    document.getElementById('f-postazione-id').value = id;
    document.getElementById('f-tipo').value          = tipo;
    document.getElementById('f-data-inizio').value   = ini;
    document.getElementById('f-data-fine').value     = fine;
    document.getElementById('label-postazione').textContent = `Fila ${fila} · N.${numero}`;

    currentGG = DateHelper.diffDays(ini, fine);
    document.getElementById('label-date').textContent = `${DateHelper.format(ini)}` + (ini !== fine ? ` → ${DateHelper.format(fine)}` : '') + ` · ${currentGG} gg`;

    document.getElementById('form-empty').classList.add('hidden');
    document.getElementById('form-booking').classList.remove('hidden');

    updatePrezzo();
    // Scroll to form on mobile
    if (window.innerWidth < 1024) {
        document.getElementById('form-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ── Calcolo prezzo ────────────────────────────────────────────
function updatePrezzo() {
    const tipo    = document.getElementById('tipo_prenotazione').value;
    const lettino = document.getElementById('chk-lettino').checked;
    const navetta = document.getElementById('chk-navetta').checked;
    const gg      = currentGG;

    let base = 0;
    if (tipo === 'giornata_intera') base = (PREZZI.giornata_intera || 25) * gg;
    else if (tipo === 'mezza_giornata')  base = (PREZZI.mezza_giornata || 15) * gg;
    else if (tipo === 'settimanale')     base = PREZZI.settimanale || 140;
    else if (tipo === 'mensile')         base = PREZZI.mensile || 450;

    const extraLettino = lettino ? (PREZZI.extra_lettino || 5) * gg : 0;
    const extraNavetta = navetta ? (PREZZI.navetta || 3)       * gg : 0;
    const totale = base + extraLettino + extraNavetta;

    document.getElementById('price-base').textContent     = `€${base.toFixed(2)}`;
    document.getElementById('price-totale').textContent   = `€${totale.toFixed(2)}`;

    const elLR = document.getElementById('price-extra-lettino-row');
    const elNR = document.getElementById('price-navetta-row');
    elLR.classList.toggle('hidden', !lettino);
    elNR.classList.toggle('hidden', !navetta);
    if (lettino) document.getElementById('price-extra-lettino').textContent = `€${extraLettino.toFixed(2)}`;
    if (navetta) document.getElementById('price-navetta').textContent       = `€${extraNavetta.toFixed(2)}`;
}

document.getElementById('chk-lettino').addEventListener('change', updatePrezzo);
document.getElementById('chk-navetta').addEventListener('change', updatePrezzo);

// ── Submit prenotazione ───────────────────────────────────────
document.getElementById('form-prenota').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-prenota');
    btn.disabled = true;

    const fd   = new FormData(this);
    const data = Object.fromEntries(fd.entries());
    data.extra_lettino = document.getElementById('chk-lettino').checked ? 1 : 0;
    data.navetta       = document.getElementById('chk-navetta').checked ? 1 : 0;
    data.adulti        = parseInt(fd.get('adulti')) || 2;
    data.bambini       = parseInt(fd.get('bambini')) || 0;
    data.postazione_id = parseInt(data.postazione_id);

    try {
        const res = await apiFetch('/api/prenota.php', { method:'POST', body: JSON.stringify(data) });
        if (!res.ok) throw new Error(res.errore || 'Errore durante la prenotazione');

        // Step 3 – conferma
        const tipoLabelText = tipoLabel[data.tipo_prenotazione]?.label || data.tipo_prenotazione;
        document.getElementById('conf-codice').textContent    = res.codice;
        document.getElementById('conf-postazione').textContent = `Postazione ${res.postazione}`;
        document.getElementById('conf-date').textContent      = `${DateHelper.format(res.data_inizio)} → ${DateHelper.format(res.data_fine)}`;
        document.getElementById('conf-tipo').textContent      = tipoLabelText;
        document.getElementById('conf-prezzo').textContent    = `€${parseFloat(res.prezzo_totale).toFixed(2)}`;

        const waMsg = `Ho prenotato al Lido Torre Conca!\nPostazione: ${res.postazione}\nCodice: ${res.codice}\nDate: ${DateHelper.format(res.data_inizio)} → ${DateHelper.format(res.data_fine)}\nTipo: ${tipoLabelText}`;
        document.getElementById('btn-wa-conf').href = `https://wa.me/${WA_NUM}?text=${encodeURIComponent(waMsg)}`;

        showStep(3);
        Toast.success('Prenotazione confermata!');
    } catch(e) {
        Toast.error(e.message);
    } finally {
        btn.disabled = false;
    }
});

// ── Step navigation ───────────────────────────────────────────
function showStep(n) {
    [1,2,3].forEach(i => {
        document.getElementById(`step-${i}`).classList.toggle('hidden', i !== n);
        const ind = document.getElementById(`step-ind-${i}`);
        ind.classList.remove('active','done');
        if (i < n) ind.classList.add('done');
        else if (i === n) ind.classList.add('active');
        if (i < 3) document.getElementById(`line-${i}`)?.classList.toggle('done', i < n);
    });
}

// ── Verifica prenotazione ─────────────────────────────────────
document.getElementById('btn-verifica').addEventListener('click', async function() {
    const codice = document.getElementById('verifica-codice').value.trim().toUpperCase();
    if (!codice) return;
    const result = document.getElementById('verifica-result');
    result.innerHTML = '<div class="text-gray-400 text-sm">Ricerco...</div>';
    result.classList.remove('hidden');
    try {
        const res = await apiFetch(`/api/verifica.php?codice=${encodeURIComponent(codice)}`);
        if (!res.ok) {
            result.innerHTML = '<div class="text-red-500 text-sm p-3 bg-red-50 rounded-xl">Prenotazione non trovata.</div>';
            return;
        }
        const p = res.prenotazione;
        const statoColor = { confermata:'green', in_attesa:'yellow', annullata:'red' };
        const col = statoColor[p.stato] || 'gray';
        result.innerHTML = `
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-sm space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Codice</span><strong>${p.codice}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Cliente</span><strong>${p.nome} ${p.cognome}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Postazione</span><strong>Fila ${p.fila} · N.${p.numero}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Date</span><strong>${DateHelper.format(p.data_inizio)} → ${DateHelper.format(p.data_fine)}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Totale</span><strong class="text-ocean">€${parseFloat(p.prezzo_totale).toFixed(2)}</strong></div>
                <div class="flex justify-between items-center"><span class="text-gray-500">Stato</span>
                    <span class="px-2 py-0.5 rounded-full bg-${col}-100 text-${col}-700 text-xs font-semibold uppercase">${p.stato.replace('_',' ')}</span>
                </div>
                ${p.stato !== 'annullata' ? `<button onclick="annullaModal('${p.codice}',\`${p.nome} ${p.cognome}\`)" class="btn-outline w-full mt-2 h-10 text-sm text-red-600 border-red-200 hover:bg-red-600 hover:text-white">Annulla prenotazione</button>` : ''}
            </div>`;
    } catch(e) {
        result.innerHTML = '<div class="text-red-500 text-sm p-3 bg-red-50 rounded-xl">Errore di rete. Riprova.</div>';
    }
});

// ── Annulla prenotazione ──────────────────────────────────────
function annullaModal(codice, nome) {
    if (!confirm(`Sei sicuro di voler annullare la prenotazione di ${nome}?\nInserisci la tua email per confermare.`)) return;
    const email = prompt('Inserisci l\'email usata per la prenotazione:');
    if (!email) return;
    apiFetch('/api/annulla.php', { method:'POST', body: JSON.stringify({ codice, email }) })
        .then(r => {
            if (r.ok) { Toast.success('Prenotazione annullata.'); document.getElementById('verifica-result').innerHTML = ''; }
            else Toast.error(r.errore || 'Impossibile annullare');
        }).catch(() => Toast.error('Errore di rete'));
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
