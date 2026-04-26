<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';
require_once 'includes/seo.php';

setSecurityHeaders();

if (isLoggedIn()) redirect('/dashboard.php');

$csrf = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php renderSEO([
        'title'       => 'Registrati — Soffitta.ai',
        'description' => 'Crea il tuo account gratuito su Soffitta.ai e inizia a valutare i tuoi oggetti con l\'AI.',
    ]); renderPerformanceHead(); ?>
    <style>body { font-family: 'Inter', sans-serif; } h1 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen flex flex-col">

<nav class="bg-white border-b border-stone-200">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center">
        <a href="/" class="flex items-center gap-2">
            <span class="text-2xl">🏛️</span>
            <span class="text-xl font-bold" style="font-family:'Playfair Display',serif">Soffitta.ai</span>
        </a>
    </div>
</nav>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm bg-white rounded-2xl border border-stone-200 shadow-sm p-8">
        <h1 class="text-2xl font-bold mb-2 text-center">Crea account</h1>
        <p class="text-stone-400 text-sm text-center mb-6">Prima valutazione gratuita</p>

        <div id="authError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3 mb-4"></div>

        <form id="registerForm" class="space-y-4">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1" for="name">Nome</label>
                <input type="text" id="name" name="name" required
                       class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1" for="email">Email</label>
                <input type="email" id="email" name="email" required
                       class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1" for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8"
                       class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                <p class="text-xs text-stone-300 mt-1">Minimo 8 caratteri</p>
            </div>
            <button type="submit"
                    class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl transition">
                Crea account gratuito
            </button>
        </form>

        <p class="text-center text-sm text-stone-400 mt-6">
            Hai già un account?
            <a href="/login.php" class="text-amber-600 hover:underline font-medium">Accedi</a>
        </p>
    </div>
</main>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type=submit]');
    const err = document.getElementById('authError');
    btn.disabled = true;
    btn.textContent = 'Registrazione in corso…';
    err.classList.add('hidden');

    const body = new FormData(this);
    try {
        const res  = await fetch('/api/auth.php', { method: 'POST', body });
        const data = await res.json();
        if (data.success) {
            window.location.href = data.redirect || '/dashboard.php';
        } else {
            err.textContent = data.message || 'Errore durante la registrazione.';
            err.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Crea account gratuito';
        }
    } catch {
        err.textContent = 'Errore di connessione. Riprova.';
        err.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Crea account gratuito';
    }
});
</script>
</body>
</html>
