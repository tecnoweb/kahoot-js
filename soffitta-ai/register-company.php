<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/buyer.php';
require_once 'includes/seo.php';

setSecurityHeaders();

if (isCompanyLoggedIn()) redirect('/company/dashboard.php');

$csrf = generateCsrfToken();
$categories = ['quadri', 'ceramiche', 'gioielli', 'mobili', 'argenteria', 'altro'];
$companyTypes = [
    'antiquario'    => 'Antiquario / Rigattiere',
    'casa_aste'     => 'Casa d\'aste',
    'gioielleria'   => 'Gioielleria / Orologeria',
    'galleria'      => 'Galleria d\'arte',
    'collezionista' => 'Collezionista privato',
    'altro'         => 'Altro',
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php renderSEO([
        'title'       => 'Registra la tua azienda — Soffitta.ai',
        'description' => 'Registra antiquario, casa d\'aste o galleria su Soffitta.ai per ricevere proposte di acquisto di oggetti che corrispondono ai tuoi interessi.',
        'keywords'    => 'registrazione antiquario online, comprare oggetti antichi online, trovare venditori antiquariato',
        'url'         => BASE_URL . '/register-company',
    ]); renderPerformanceHead(); ?>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1,h2 { font-family: 'Playfair Display', serif; }
        .check-label input:checked ~ .check-box { background: #f59e0b; border-color: #f59e0b; }
        .check-label input:checked ~ .check-box::after { content: '✓'; color: white; font-size: 12px; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">

<nav class="bg-white border-b border-stone-200 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2">
            <span class="text-2xl">🏛️</span>
            <span class="text-xl font-bold" style="font-family:'Playfair Display',serif">Soffitta.ai</span>
        </a>
        <div class="flex items-center gap-3 text-sm">
            <span class="text-stone-400 hidden sm:inline">Già registrato?</span>
            <a href="/login-company.php" class="text-amber-600 hover:underline font-medium">Accedi come azienda</a>
        </div>
    </div>
</nav>

<main class="max-w-2xl mx-auto px-4 py-10">
    <div class="text-center mb-8">
        <div class="text-4xl mb-3">🏢</div>
        <h1 class="text-3xl font-bold mb-2">Registra la tua azienda</h1>
        <p class="text-stone-400">Ricevi notifiche su oggetti da acquistare che corrispondono ai tuoi interessi</p>
    </div>

    <!-- Vantaggi -->
    <div class="grid grid-cols-3 gap-3 mb-8">
        <?php foreach ([
            ['🔔', 'Notifiche in tempo reale', 'Oggetti che corrispondono ai tuoi interessi'],
            ['🎯', 'Matching preciso', 'Filtra per categoria, valore ed epoca'],
            ['💬', 'Contatta il venditore', 'Fai un\'offerta direttamente dalla piattaforma'],
        ] as [$icon, $title, $desc]): ?>
        <div class="bg-white border border-stone-200 rounded-xl p-4 text-center">
            <div class="text-2xl mb-1"><?= $icon ?></div>
            <div class="text-xs font-semibold text-stone-700"><?= $title ?></div>
            <div class="text-xs text-stone-400 mt-0.5"><?= $desc ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-8">
        <div id="regError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3 mb-5"></div>

        <form id="companyRegForm" class="space-y-6" novalidate>
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <!-- Sezione 1: Dati azienda -->
            <div>
                <h2 class="text-lg font-bold mb-4 pb-2 border-b border-stone-100">1. Dati azienda</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-stone-700 mb-1">Ragione sociale *</label>
                        <input type="text" name="company_name" required
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100"
                               placeholder="Antiquario Bianchi S.r.l.">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Tipo azienda *</label>
                        <select name="company_type" required
                                class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 bg-white">
                            <?php foreach ($companyTypes as $val => $label): ?>
                            <option value="<?= $val ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Partita IVA</label>
                        <input type="text" name="vat_number" maxlength="20"
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                               placeholder="IT01234567890">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Nome referente</label>
                        <input type="text" name="contact_name"
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                               placeholder="Mario Rossi">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Telefono</label>
                        <input type="tel" name="phone"
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                               placeholder="+39 02 1234567">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Città *</label>
                        <input type="text" name="city" required
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                               placeholder="Milano">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Provincia</label>
                        <input type="text" name="province" maxlength="3"
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                               placeholder="MI">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-stone-700 mb-1">Sito web</label>
                        <input type="url" name="website"
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                               placeholder="https://www.tuoantiquario.it">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-stone-700 mb-1">Descrizione attività</label>
                        <textarea name="description" rows="3" maxlength="500"
                                  class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 resize-none"
                                  placeholder="Antiquario specializzato in mobili del XVIII e XIX secolo, operante a Milano dal 1985..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Sezione 2: Interessi acquisto -->
            <div>
                <h2 class="text-lg font-bold mb-4 pb-2 border-b border-stone-100">2. Interessi di acquisto</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-stone-700 mb-2">Categorie di interesse *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <?php
                        $catIcons = ['quadri' => '🖼️', 'ceramiche' => '🏺', 'gioielli' => '💍',
                                     'mobili' => '🪑', 'argenteria' => '🥄', 'altro' => '📦'];
                        foreach ($categories as $cat): ?>
                        <label class="flex items-center gap-2 bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 cursor-pointer hover:border-amber-400 hover:bg-amber-50 transition has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50">
                            <input type="checkbox" name="categories[]" value="<?= $cat ?>"
                                   class="accent-amber-500">
                            <span class="text-sm capitalize"><?= $catIcons[$cat] ?> <?= ucfirst($cat) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Budget minimo (€)</label>
                        <input type="number" name="budget_min" min="0" step="50" value="0"
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                               placeholder="0">
                        <p class="text-xs text-stone-300 mt-1">0 = nessun minimo</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Budget massimo (€)</label>
                        <input type="number" name="budget_max" min="0" step="50" value="0"
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                               placeholder="0">
                        <p class="text-xs text-stone-300 mt-1">0 = nessun limite</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Epoche di interesse</label>
                    <input type="text" name="era_interest"
                           class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                           placeholder="XIX secolo, Art Déco, anni '50, Rinascimento...">
                    <p class="text-xs text-stone-300 mt-1">Separa le epoche con una virgola</p>
                </div>
            </div>

            <!-- Sezione 3: Credenziali -->
            <div>
                <h2 class="text-lg font-bold mb-4 pb-2 border-b border-stone-100">3. Credenziali accesso</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-stone-700 mb-1">Email aziendale *</label>
                        <input type="email" name="email" required
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                               placeholder="info@tuazienda.it">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Password *</label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
                        <p class="text-xs text-stone-300 mt-1">Minimo 8 caratteri</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-stone-700 mb-1">Conferma password *</label>
                        <input type="password" id="confirmPwd" required minlength="8"
                               class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-stone-600">
                ⚠️ <strong>Nota:</strong> Il tuo account sarà attivato dopo una breve verifica da parte del nostro team
                (solitamente entro 24 ore). Riceverai una email di conferma.
            </div>

            <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-4 rounded-xl transition text-base flex items-center justify-center gap-2">
                <span>🏢</span>
                Registra la tua azienda
            </button>
        </form>
    </div>
</main>

<footer class="bg-white border-t border-stone-200 py-6 mt-8">
    <div class="max-w-5xl mx-auto px-4 text-center text-xs text-stone-300">
        © <?= date('Y') ?> Soffitta.ai
    </div>
</footer>

<script>
document.getElementById('companyRegForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type=submit]');
    const err = document.getElementById('regError');
    err.classList.add('hidden');

    // Verifica password
    const pwd  = this.querySelector('[name=password]').value;
    const conf = document.getElementById('confirmPwd').value;
    if (pwd !== conf) {
        err.textContent = 'Le password non coincidono.';
        err.classList.remove('hidden');
        return;
    }

    // Verifica almeno una categoria
    const cats = this.querySelectorAll('[name="categories[]"]:checked');
    if (cats.length === 0) {
        err.textContent = 'Seleziona almeno una categoria di interesse.';
        err.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Registrazione in corso…';

    const body = new FormData(this);
    try {
        const res  = await fetch('/api/company-auth.php', { method: 'POST', body });
        const data = await res.json();
        if (data.success) {
            window.location.href = data.redirect || '/company/dashboard.php';
        } else {
            err.textContent = data.message || 'Errore durante la registrazione.';
            err.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = '<span>🏢</span> Registra la tua azienda';
        }
    } catch {
        err.textContent = 'Errore di connessione. Riprova.';
        err.classList.remove('hidden');
        btn.disabled = false;
        btn.innerHTML = '<span>🏢</span> Registra la tua azienda';
    }
});
</script>
</body>
</html>
