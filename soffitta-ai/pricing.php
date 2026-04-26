<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';
require_once 'includes/seo.php';

setSecurityHeaders();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php renderSEO([
        'title'       => 'Prezzi — Soffitta.ai',
        'description' => 'Valuta gli oggetti di casa gratis. Perizia completa a soli €2,99. Piano Pro per valutazioni illimitate.',
        'keywords'    => 'prezzi perizia online, costo valutazione antiquariato AI, piano pro soffitta',
        'url'         => BASE_URL . '/prezzi',
    ]); renderPerformanceHead(); ?>
    <style>body { font-family: 'Inter', sans-serif; } h1,h2 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include 'includes/_nav.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold text-center mb-4">Prezzi semplici e trasparenti</h1>
    <p class="text-stone-400 text-center mb-12 text-lg">Inizia gratis, paga solo quando ottieni valore</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">

        <!-- Piano Free -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <div class="text-sm font-medium text-stone-400 mb-1">Gratuito</div>
            <div class="text-3xl font-bold mb-1">€0</div>
            <div class="text-stone-400 text-sm mb-6">Per sempre</div>
            <ul class="space-y-3 text-sm text-stone-600 mb-6">
                <?php foreach ([
                    '1 valutazione AI gratuita',
                    'Nome e categoria oggetto',
                    'Epoca e stima di base',
                    'Indice di confidenza AI',
                ] as $f): ?>
                <li class="flex items-center gap-2"><span class="text-green-500">✓</span><?= $f ?></li>
                <?php endforeach; ?>
                <?php foreach ([
                    'Suggerimenti vendita',
                    'Report PDF',
                ] as $f): ?>
                <li class="flex items-center gap-2 text-stone-300"><span>✗</span><?= $f ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="/" class="block w-full text-center bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 rounded-xl transition">
                Inizia gratis
            </a>
        </div>

        <!-- Perizia singola -->
        <div class="bg-amber-500 rounded-2xl p-6 text-white relative">
            <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-stone-900 text-white text-xs font-bold px-3 py-1 rounded-full">
                PIÙ POPOLARE
            </div>
            <div class="text-sm font-medium text-amber-200 mb-1">Perizia singola</div>
            <div class="text-3xl font-bold mb-1">€2,99</div>
            <div class="text-amber-200 text-sm mb-6">Per oggetto</div>
            <ul class="space-y-3 text-sm mb-6">
                <?php foreach ([
                    'Tutto del piano gratuito',
                    'Report PDF scaricabile',
                    'Suggerimenti di vendita personalizzati',
                    'Analisi dettagliata condizioni',
                    'Curiosità storiche sull\'oggetto',
                    'Valido per assicurazioni',
                ] as $f): ?>
                <li class="flex items-center gap-2"><span class="text-amber-200">✓</span><?= $f ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="/" class="block w-full text-center bg-white text-amber-600 font-bold py-3 rounded-xl transition hover:bg-amber-50">
                Valuta e sblocca
            </a>
        </div>

        <!-- Piano Pro -->
        <div class="bg-white rounded-2xl border border-amber-200 p-6">
            <div class="text-sm font-medium text-amber-600 mb-1">Piano Pro</div>
            <div class="text-3xl font-bold mb-1">€19,99</div>
            <div class="text-stone-400 text-sm mb-6">Al mese</div>
            <ul class="space-y-3 text-sm text-stone-600 mb-6">
                <?php foreach ([
                    'Valutazioni illimitate',
                    'Report PDF per ogni perizia',
                    'Storico completo valutazioni',
                    'Priorità in coda AI',
                    'Supporto prioritario via email',
                    'API access (beta)',
                ] as $f): ?>
                <li class="flex items-center gap-2"><span class="text-green-500">✓</span><?= $f ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="/register.php" class="block w-full text-center bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl transition">
                Inizia Pro
            </a>
        </div>
    </div>

    <!-- Garanzia -->
    <div class="bg-stone-100 rounded-2xl p-6 text-center">
        <p class="text-2xl mb-2">🛡️</p>
        <h2 class="text-xl font-bold mb-2">Soddisfatti o rimborsati</h2>
        <p class="text-stone-500 text-sm max-w-xl mx-auto">
            Se l'AI non riesce ad analizzare il tuo oggetto con un indice di confidenza superiore al 40%,
            ti rimborsiamo immediatamente senza domande.
        </p>
    </div>
</main>

<?php include 'includes/_footer.php'; ?>
</body>
</html>
