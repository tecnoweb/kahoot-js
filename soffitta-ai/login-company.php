<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/buyer.php';
require_once 'includes/seo.php';

setSecurityHeaders();

if (isCompanyLoggedIn()) redirect('/company/dashboard.php');

$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php renderSEO([
        'title'       => 'Accesso aziende — Soffitta.ai',
        'description' => 'Area riservata antiquari, case d\'aste e gallerie. Accedi per vedere gli oggetti che corrispondono ai tuoi interessi.',
    ]); renderPerformanceHead(); ?>
    <style>body { font-family: 'Inter', sans-serif; } h1 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen flex flex-col">

<nav class="bg-white border-b border-stone-200">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2">
            <span class="text-2xl">🏛️</span>
            <span class="text-xl font-bold" style="font-family:'Playfair Display',serif">Soffitta.ai</span>
        </a>
        <a href="/login.php" class="text-sm text-stone-400 hover:text-amber-600 transition">Sei un privato? Accedi qui</a>
    </div>
</nav>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm bg-white rounded-2xl border border-stone-200 shadow-sm p-8">
        <div class="text-center mb-6">
            <div class="text-4xl mb-2">🏢</div>
            <h1 class="text-2xl font-bold">Area Aziende</h1>
            <p class="text-stone-400 text-sm mt-1">Accedi con le credenziali della tua azienda</p>
        </div>

        <div id="authError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3 mb-4"></div>

        <form id="companyLoginForm" class="space-y-4">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email aziendale</label>
                <input type="email" name="email" required autofocus
                       class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
            </div>
            <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl transition">
                Accedi
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-stone-100 text-center text-sm text-stone-400 space-y-2">
            <p>Non hai ancora un account?</p>
            <a href="/register-company.php"
               class="inline-block bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium px-5 py-2 rounded-xl transition">
                Registra la tua azienda
            </a>
        </div>
    </div>
</main>

<script>
document.getElementById('companyLoginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type=submit]');
    const err = document.getElementById('authError');
    btn.disabled = true;
    btn.textContent = 'Accesso in corso…';
    err.classList.add('hidden');

    try {
        const res  = await fetch('/api/company-auth.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        if (data.success) {
            window.location.href = data.redirect || '/company/dashboard.php';
        } else {
            err.textContent = data.message || 'Errore di accesso.';
            err.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Accedi';
        }
    } catch {
        err.textContent = 'Errore di connessione. Riprova.';
        err.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Accedi';
    }
});
</script>
</body>
</html>
