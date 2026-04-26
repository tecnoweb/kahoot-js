<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/seo.php';
require_once 'includes/auth.php';

setSecurityHeaders();

$scanId = (int) ($_GET['scan_id'] ?? 0);
if (!$scanId) redirect('/');

$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM scans WHERE id = ?');
$stmt->execute([$scanId]);
$scan = $stmt->fetch();

if (!$scan || $scan['paid']) {
    redirect('/perizia/' . $scanId);
}

$user  = getCurrentUser();
$csrf  = generateCsrfToken();
$price = number_format(PRICE_PREMIUM_SCAN, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php renderSEO([
        'title'       => 'Perizia completa — Soffitta.ai',
        'description' => 'Ottieni la perizia completa con report PDF e suggerimenti di vendita personalizzati.',
    ]); renderPerformanceHead(); ?>
    <style>body { font-family: 'Inter', sans-serif; } h1,h2 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen">

<nav class="bg-white border-b border-stone-200 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center gap-2">
        <a href="/" class="flex items-center gap-2">
            <span class="text-2xl">🏛️</span>
            <span class="text-xl font-bold" style="font-family:'Playfair Display',serif">Soffitta.ai</span>
        </a>
    </div>
</nav>

<main class="max-w-xl mx-auto px-4 py-12">
    <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-8">
        <h1 class="text-2xl font-bold mb-2">Perizia completa</h1>
        <p class="text-stone-400 text-sm mb-6">
            Sblocca il report dettagliato per:
            <strong class="text-stone-700"><?= htmlspecialchars($scan['object_name'] ?? 'oggetto') ?></strong>
        </p>

        <!-- Riepilogo oggetto -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm text-stone-500">Valore stimato</span>
                <span class="font-bold text-amber-600">
                    €<?= number_format($scan['estimated_min'], 0, ',', '.') ?>–<?= number_format($scan['estimated_max'], 0, ',', '.') ?>
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-stone-500">Categoria</span>
                <span class="text-sm font-medium capitalize"><?= htmlspecialchars($scan['category'] ?? '') ?></span>
            </div>
        </div>

        <!-- Cosa include -->
        <div class="mb-6">
            <div class="font-semibold mb-3 text-stone-700">La perizia completa include:</div>
            <ul class="space-y-2 text-sm text-stone-600">
                <?php foreach ([
                    'Report PDF scaricabile con timbro Soffitta.ai',
                    'Stima dettagliata con range di mercato aggiornato',
                    'Suggerimenti personalizzati dove vendere',
                    'Analisi dello stato di conservazione',
                    'Curiosità storiche sull\'oggetto',
                    'Valido ai fini assicurativi e per successioni',
                ] as $item): ?>
                <li class="flex items-start gap-2">
                    <span class="text-green-500 font-bold mt-0.5">✓</span>
                    <?= $item ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Prezzo e CTA -->
        <div class="border-t border-stone-100 pt-6">
            <div class="flex justify-between items-center mb-4">
                <span class="text-stone-500">Totale</span>
                <span class="text-2xl font-bold text-stone-800">€<?= $price ?></span>
            </div>

            <!-- Form pagamento Stripe (stub) -->
            <form action="/api/create-checkout.php" method="POST" id="checkoutForm">
                <input type="hidden" name="scan_id" value="<?= $scanId ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <button type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-4 rounded-xl transition text-lg flex items-center justify-center gap-2">
                    <span>🔒</span>
                    Paga €<?= $price ?> con carta
                </button>
            </form>

            <p class="text-center text-xs text-stone-300 mt-3">
                Pagamento sicuro via Stripe · SSL 256-bit
            </p>
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="/result.php?scan_id=<?= $scanId ?>"
           class="text-stone-400 hover:text-stone-600 text-sm transition">
            ← Torna alla valutazione gratuita
        </a>
    </div>
</main>

<footer class="bg-white border-t border-stone-200 py-6 mt-8">
    <div class="max-w-5xl mx-auto px-4 text-center text-xs text-stone-300">
        © <?= date('Y') ?> Soffitta.ai
    </div>
</footer>
<script src="/assets/app.js"></script>
</body>
</html>
