<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';

// Postazione da QR: /ordina.php?p=42  oppure /ordina.php?fila=A&n=3
$postazioneId = null;
$postazione   = null;

if (!empty($_GET['p'])) {
    $postazione = fetchOne(
        'SELECT id, fila, numero, numero_globale FROM postazioni WHERE numero_globale = ? AND stato = ?',
        [(int)$_GET['p'], 'attiva']
    );
} elseif (!empty($_GET['fila']) && !empty($_GET['n'])) {
    $postazione = fetchOne(
        'SELECT id, fila, numero, numero_globale FROM postazioni WHERE fila = ? AND numero = ? AND stato = ?',
        [strtoupper(substr($_GET['fila'],0,1)), (int)$_GET['n'], 'attiva']
    );
}
if ($postazione) $postazioneId = $postazione['id'];

$pageTitle       = $postazione ? 'Menu · Postazione ' . $postazione['fila'] . $postazione['numero'] : 'Ordina';
$pageDescription = 'Ordina cibo e bevande direttamente dal tuo ombrellone al Lido Torre Conca.';
include __DIR__ . '/includes/header.php';
?>

<!-- Header ordinazione -->
<div class="bg-ocean pt-24 pb-8">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <?php if ($postazione): ?>
        <div class="inline-flex items-center gap-2 bg-sand/20 rounded-full px-4 py-1.5 mb-3">
            <?= icon('umbrella','w-4 h-4','#d4a847') ?>
            <span class="text-sand text-sm font-semibold">Postazione <?= h($postazione['fila']) ?><?= (int)$postazione['numero'] ?></span>
        </div>
        <?php endif; ?>
        <h1 class="font-display text-3xl font-bold text-white mb-2">Ordina al tuo Ombrellone</h1>
        <p class="text-blue-200 text-sm">Scegli dal menu — lo staff porterà tutto da te.</p>
    </div>
</div>

<div class="max-w-2xl mx-auto px-4 py-6">

    <!-- Postazione mancante -->
    <?php if (!$postazione): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 mb-6 flex gap-3">
        <?= icon('alert','w-5 h-5 flex-shrink-0','#d97706') ?>
        <div>
            <div class="font-semibold text-yellow-800">Nessuna postazione rilevata</div>
            <div class="text-yellow-700 text-sm mt-1">Scansiona il QR code della tua postazione oppure inserisci il numero manualmente.</div>
            <div class="mt-3 flex gap-2">
                <input type="number" id="post-num" class="form-input w-24 h-10 text-sm" placeholder="Es. 15" min="1" max="60">
                <button onclick="location.href='/ordina.php?p='+document.getElementById('post-num').value" class="btn-primary h-10 px-4 text-sm">Vai</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Loading menu -->
    <div id="menu-loading" class="text-center py-8 text-gray-400">
        <div class="animate-spin w-8 h-8 border-2 border-ocean border-t-transparent rounded-full mx-auto mb-2"></div>
        Carico il menu...
    </div>

    <!-- Menu categorie (populated by JS) -->
    <div id="menu-container" class="hidden space-y-6"></div>

    <!-- Carrello fisso in basso -->
    <div id="cart-bar" class="hidden fixed bottom-0 left-0 right-0 z-50 px-4 pb-4 safe-area-bottom">
        <button id="btn-cart" class="w-full bg-ocean text-white font-bold text-lg py-4 rounded-2xl shadow-2xl flex items-center justify-between px-5 active:scale-95 transition-transform">
            <div class="flex items-center gap-2">
                <?= icon('food','w-5 h-5','#d4a847') ?>
                <span>Vedi Ordine</span>
                <span id="cart-count" class="bg-sand text-ocean text-sm font-bold px-2 py-0.5 rounded-full ml-1">0</span>
            </div>
            <span id="cart-total" class="text-sand font-bold">€0.00</span>
        </button>
    </div>
</div>

<!-- Bottom sheet carrello -->
<div class="bottom-sheet-overlay" id="cart-overlay">
    <div class="bottom-sheet" id="cart-sheet">
        <div class="bottom-sheet-handle"></div>
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-display text-xl font-bold text-ocean">Il tuo Ordine</h2>
            <button onclick="Modal.close('cart-overlay')" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                <?= icon('close','w-4 h-4','#64748b') ?>
            </button>
        </div>

        <!-- Lista carrello -->
        <div id="cart-items" class="space-y-3 mb-5 max-h-64 overflow-y-auto"></div>

        <!-- Totale -->
        <div class="border-t border-gray-100 pt-4 mb-5">
            <div class="flex justify-between font-bold text-ocean text-lg">
                <span>Totale</span><span id="cart-total-sheet">€0.00</span>
            </div>
        </div>

        <!-- Form dati cliente -->
        <div class="space-y-3 mb-5">
            <div class="form-group">
                <label class="form-label">Nome *</label>
                <input type="text" id="ord-nome" class="form-input" placeholder="Mario Rossi" required>
            </div>
            <div class="form-group">
                <label class="form-label">Telefono (per notifica pronto)</label>
                <input type="tel" id="ord-tel" class="form-input" placeholder="+39 347 000 0000">
            </div>
            <div class="form-group">
                <label class="form-label">Note generali</label>
                <textarea id="ord-note" class="form-input" rows="2" placeholder="Allergie, preferenze..."></textarea>
            </div>
        </div>
        <button id="btn-invia-ordine" class="btn-primary w-full h-14 text-base">
            <?= icon('check','w-5 h-5') ?> Invia Ordine
        </button>
    </div>
</div>

<!-- Conferma ordine -->
<div class="modal-overlay" id="conf-overlay">
    <div class="modal-box text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <?= icon('check','w-8 h-8','#22c55e') ?>
        </div>
        <h2 class="font-display text-2xl font-bold text-ocean mb-2">Ordine Inviato!</h2>
        <p class="text-gray-500 mb-4">Lo staff sta preparando il tuo ordine. Ti avviseremo quando è pronto.</p>
        <div class="bg-ocean/5 rounded-2xl p-5 mb-5">
            <div class="text-xs text-gray-400 uppercase tracking-widest mb-1">Codice Ordine</div>
            <div id="conf-ordine-codice" class="font-display text-4xl font-bold text-ocean tracking-widest">—</div>
            <div id="conf-ordine-totale" class="text-sand font-bold text-2xl mt-2">—</div>
        </div>
        <p class="text-xs text-gray-400 mb-5">Conserva il codice ordine. Puoi tracciarlo in tempo reale.</p>
        <button onclick="location.reload()" class="btn-primary w-full h-12">Nuovo Ordine</button>
    </div>
</div>

<script>
const POSTAZIONE_ID = <?= $postazioneId ? $postazioneId : 'null' ?>;
let cart = {}; // { itemId: { nome, prezzo, qty } }

// ── Carica menu ───────────────────────────────────────────────
async function loadMenu() {
    try {
        const data = await apiFetch('/api/menu.php');
        if (!data.ok) throw new Error();
        renderMenu(data.categorie);
        document.getElementById('menu-loading').classList.add('hidden');
        document.getElementById('menu-container').classList.remove('hidden');
    } catch {
        document.getElementById('menu-loading').innerHTML = '<p class="text-red-400">Impossibile caricare il menu. Riprova.</p>';
    }
}

function renderMenu(categorie) {
    const container = document.getElementById('menu-container');
    container.innerHTML = categorie.filter(c => c.items?.length).map(cat => `
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-ocean/5 px-5 py-3 border-b border-gray-100">
                <h2 class="font-display text-lg font-bold text-ocean">${cat.nome}</h2>
                ${cat.descrizione ? `<p class="text-gray-400 text-xs">${cat.descrizione}</p>` : ''}
            </div>
            <div class="divide-y divide-gray-50">
                ${cat.items.map(item => renderItem(item)).join('')}
            </div>
        </div>
    `).join('');
}

function renderItem(item) {
    return `
    <div class="flex items-center gap-3 px-5 py-4" id="item-row-${item.id}">
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-ocean text-sm">${item.nome}</div>
            ${item.descrizione ? `<div class="text-gray-400 text-xs mt-0.5 line-clamp-2">${item.descrizione}</div>` : ''}
            ${item.allergeni ? `<div class="text-yellow-600 text-xs mt-0.5">Allergeni: ${item.allergeni}</div>` : ''}
        </div>
        <div class="text-right flex-shrink-0">
            <div class="text-ocean font-bold mb-1.5">€${parseFloat(item.prezzo).toFixed(2)}</div>
            <div class="flex items-center gap-1" id="ctrl-${item.id}">
                <button onclick="changeQty(${item.id},'${item.nome.replace(/'/g,"\\'")}',${item.prezzo},-1)"
                    class="w-7 h-7 rounded-full bg-gray-100 hover:bg-red-100 flex items-center justify-center font-bold text-gray-600 transition-colors">−</button>
                <span id="qty-${item.id}" class="w-6 text-center font-bold text-ocean text-sm">0</span>
                <button onclick="changeQty(${item.id},'${item.nome.replace(/'/g,"\\'")}',${item.prezzo},1)"
                    class="w-7 h-7 rounded-full bg-ocean/10 hover:bg-ocean text-ocean hover:text-white flex items-center justify-center font-bold transition-colors">+</button>
            </div>
        </div>
    </div>`;
}

function changeQty(id, nome, prezzo, delta) {
    if (!cart[id]) cart[id] = { nome, prezzo: parseFloat(prezzo), qty: 0 };
    cart[id].qty = Math.max(0, cart[id].qty + delta);
    if (cart[id].qty === 0) delete cart[id];
    document.getElementById(`qty-${id}`).textContent = cart[id]?.qty || 0;
    updateCartBar();
}

function updateCartBar() {
    const items = Object.entries(cart);
    const count = items.reduce((s,[,v]) => s + v.qty, 0);
    const total = items.reduce((s,[,v]) => s + v.prezzo * v.qty, 0);

    document.getElementById('cart-count').textContent = count;
    document.getElementById('cart-total').textContent = `€${total.toFixed(2)}`;
    document.getElementById('cart-bar').classList.toggle('hidden', count === 0);
}

function renderCartSheet() {
    const items = Object.entries(cart);
    const total = items.reduce((s,[,v]) => s + v.prezzo * v.qty, 0);

    document.getElementById('cart-items').innerHTML = items.length
        ? items.map(([id, v]) => `
            <div class="flex items-center justify-between py-2 border-b border-gray-50">
                <div>
                    <div class="font-medium text-ocean text-sm">${v.nome}</div>
                    <div class="text-gray-400 text-xs">€${v.prezzo.toFixed(2)} × ${v.qty}</div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-ocean">€${(v.prezzo * v.qty).toFixed(2)}</span>
                    <button onclick="changeQty(${id},'${v.nome.replace(/'/g,"\\'")}',${v.prezzo},-1);renderCartSheet()"
                        class="w-6 h-6 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-sm font-bold">−</button>
                </div>
            </div>`) .join('')
        : '<p class="text-gray-400 text-sm text-center py-4">Carrello vuoto</p>';

    document.getElementById('cart-total-sheet').textContent = `€${total.toFixed(2)}`;
}

document.getElementById('btn-cart').addEventListener('click', () => {
    renderCartSheet();
    Modal.open('cart-overlay');
});

// ── Invia ordine ──────────────────────────────────────────────
document.getElementById('btn-invia-ordine').addEventListener('click', async function() {
    const nome = document.getElementById('ord-nome').value.trim();
    if (!nome) { Toast.error('Inserisci il tuo nome'); return; }

    const items = Object.entries(cart).map(([id, v]) => ({
        id: parseInt(id), quantita: v.qty, note: ''
    }));
    if (!items.length) { Toast.error('Il carrello è vuoto'); return; }

    this.disabled = true;
    try {
        const res = await apiFetch('/api/ordine.php', {
            method: 'POST',
            body: JSON.stringify({
                nome_cliente:  nome,
                telefono:      document.getElementById('ord-tel').value.trim(),
                note:          document.getElementById('ord-note').value.trim(),
                tipo_origine:  POSTAZIONE_ID ? 'spiaggia' : 'banco',
                postazione_id: POSTAZIONE_ID,
                items,
            })
        });
        if (!res.ok) throw new Error(res.errore || 'Errore');

        Modal.close('cart-overlay');
        document.getElementById('conf-ordine-codice').textContent = res.codice;
        document.getElementById('conf-ordine-totale').textContent = `€${parseFloat(res.totale).toFixed(2)}`;
        Modal.open('conf-overlay');
        cart = {};
        updateCartBar();
    } catch(e) {
        Toast.error(e.message);
    } finally {
        this.disabled = false;
    }
});

loadMenu();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
