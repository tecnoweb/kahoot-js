<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/seo.php';
require_once 'includes/auth.php';
require_once 'includes/buyer.php';

setSecurityHeaders();

// Supporta /perizia/123 (via .htaccess) e ?scan_id=123
$scanId = (int) ($_GET['scan_id'] ?? 0);
if (!$scanId) {
    http_response_code(404);
    redirect('/');
}

$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM scans WHERE id = ?');
$stmt->execute([$scanId]);
$scan = $stmt->fetch();

if (!$scan) {
    http_response_code(404);
    redirect('/');
}

$isPaid = (bool) $scan['paid'];
$user   = getCurrentUser();

// Solo il proprietario può vedere perizie non pagate altrui
$ownerId = $scan['user_id'];
$viewerId = $user['id'] ?? null;
if (!$isPaid && $ownerId && $ownerId !== $viewerId) {
    redirect('/checkout.php?scan_id=' . $scanId);
}

$sellSuggestions = json_decode($scan['sell_suggestions'] ?? '[]', true) ?: [];
$slug = $scan['public_slug'] ?? $scanId;

$seoTitle = htmlspecialchars($scan['object_name']) . ' — Valore stimato €' .
    number_format($scan['estimated_min'], 0, ',', '.') . '–€' .
    number_format($scan['estimated_max'], 0, ',', '.') . ' | Soffitta.ai';

$seoImage = $scan['image_path'] ? BASE_URL . '/' . $scan['image_path'] : BASE_URL . '/assets/og-image.jpg';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php
    renderSEO([
        'title'       => $seoTitle,
        'description' => mb_substr(strip_tags($scan['description']), 0, 160),
        'url'         => BASE_URL . '/perizia/' . $slug,
        'image'       => $seoImage,
        'type'        => 'product',
    ]);
    renderPerformanceHead();
    if ($isPaid) renderSchemaProduct($scan);
    ?>
    <style>body { font-family: 'Inter', sans-serif; } h1,h2,h3 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen">

<nav class="bg-white border-b border-stone-200 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2">
            <span class="text-2xl">🏛️</span>
            <span class="text-xl font-bold" style="font-family:'Playfair Display',serif">Soffitta.ai</span>
        </a>
        <?php if ($user): ?>
            <a href="/dashboard.php" class="text-sm text-amber-600 hover:underline">Dashboard</a>
        <?php else: ?>
            <a href="/" class="text-sm text-amber-600 hover:underline">Valuta il tuo oggetto</a>
        <?php endif; ?>
    </div>
</nav>

<main class="max-w-3xl mx-auto px-4 py-10">
    <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">
        <?php if ($scan['image_path']): ?>
        <div class="relative">
            <img src="/<?= htmlspecialchars($scan['image_path']) ?>"
                 alt="<?= htmlspecialchars($scan['object_name'] ?? 'Oggetto valutato da Soffitta.ai') ?>"
                 class="w-full max-h-80 object-contain bg-stone-100 p-4"
                 loading="lazy">
            <?php if (!$isPaid): ?>
            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/60 to-transparent flex items-end p-4">
                <span class="text-white text-sm font-medium bg-amber-500 px-3 py-1 rounded-full">
                    Valutazione gratuita
                </span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="p-6">
            <div class="flex flex-wrap items-start gap-2 mb-4">
                <h1 class="text-2xl font-bold text-stone-900 mr-2">
                    <?= htmlspecialchars($scan['object_name'] ?? 'Oggetto non identificato') ?>
                </h1>
                <?php if ($scan['category']): ?>
                <span class="text-sm bg-amber-100 text-amber-700 px-3 py-1 rounded-full capitalize">
                    <?= htmlspecialchars($scan['category']) ?>
                </span>
                <?php endif; ?>
                <?php if ($scan['era']): ?>
                <span class="text-sm bg-stone-100 text-stone-600 px-3 py-1 rounded-full">
                    <?= htmlspecialchars($scan['era']) ?>
                </span>
                <?php endif; ?>
            </div>

            <p class="text-stone-600 leading-relaxed mb-6">
                <?= htmlspecialchars($scan['description'] ?? '') ?>
            </p>

            <!-- Valore -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
                    <div class="text-xs text-stone-400 mb-1">Valore stimato</div>
                    <div class="text-2xl font-bold text-amber-600">
                        €<?= number_format($scan['estimated_min'], 0, ',', '.') ?>–<?= number_format($scan['estimated_max'], 0, ',', '.') ?>
                    </div>
                </div>
                <div class="bg-stone-50 border border-stone-200 rounded-xl p-4 text-center">
                    <div class="text-xs text-stone-400 mb-1">Confidenza AI</div>
                    <div class="text-2xl font-bold <?= $scan['confidence_score'] >= 70 ? 'text-green-600' : ($scan['confidence_score'] >= 40 ? 'text-amber-600' : 'text-red-500') ?>">
                        <?= (int) $scan['confidence_score'] ?>%
                    </div>
                </div>
            </div>

            <?php if ($scan['condition_notes']): ?>
            <div class="bg-stone-50 rounded-xl p-4 mb-6 border border-stone-100">
                <div class="text-sm font-semibold text-stone-600 mb-1">Condizioni</div>
                <p class="text-stone-500 text-sm"><?= htmlspecialchars($scan['condition_notes']) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($isPaid && !empty($sellSuggestions)): ?>
            <div class="mb-6">
                <div class="font-semibold text-stone-700 mb-3">Dove venderlo:</div>
                <ul class="space-y-2">
                    <?php foreach ($sellSuggestions as $s): ?>
                    <li class="flex items-start gap-2 text-stone-600 text-sm">
                        <span class="text-amber-500 mt-0.5">→</span>
                        <?= htmlspecialchars($s) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($scan['curiosity']): ?>
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 mb-6 text-sm italic text-stone-500">
                💡 <?= htmlspecialchars($scan['curiosity']) ?>
            </div>
            <?php endif; ?>

            <div class="text-xs text-stone-300 border-t border-stone-100 pt-4">
                Perizia effettuata il <?= date('d/m/Y', strtotime($scan['created_at'])) ?>
                · Soffitta.ai AI Engine
            </div>
        </div>

        <?php if (!$isPaid): ?>
        <!-- CTA perizia completa -->
        <div class="bg-amber-50 border-t border-amber-200 p-6 text-center">
            <p class="text-stone-700 font-semibold mb-1">Vuoi la perizia completa?</p>
            <p class="text-stone-400 text-sm mb-4">
                Ottieni il report PDF completo con consigli di vendita personalizzati e stima dettagliata.
            </p>
            <a href="/checkout.php?scan_id=<?= $scanId ?>"
               class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-bold px-8 py-3 rounded-xl transition">
                Perizia completa — €2,99
            </a>
        </div>
        <?php endif; ?>

        <?php
        // Acquirenti interessati — visibili al proprietario della scansione
        $isOwner = ($ownerId && $ownerId === $viewerId) || ($ownerId === null);
        $buyerMatches = getBuyerMatches($scanId, 5);
        if ($isOwner && !empty($buyerMatches)):
        ?>
        <div class="border-t border-stone-100 p-6">
            <h2 class="text-lg font-bold mb-1">
                Potenziali acquirenti
                <span class="ml-2 text-sm font-normal text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                    <?= count($buyerMatches) ?> abbinati
                </span>
            </h2>
            <p class="text-stone-400 text-sm mb-4">
                Queste aziende potrebbero essere interessate al tuo oggetto
            </p>
            <div class="space-y-3">
                <?php
                $typeLabels = [
                    'antiquario'    => 'Antiquario',
                    'casa_aste'     => 'Casa d\'aste',
                    'gioielleria'   => 'Gioielleria',
                    'galleria'      => 'Galleria d\'arte',
                    'collezionista' => 'Collezionista',
                    'altro'         => 'Acquirente',
                ];
                foreach ($buyerMatches as $bm):
                    $hasOffer = $bm['status'] === 'offer_made' && $bm['offer_amount'] > 0;
                ?>
                <div class="flex items-start gap-4 bg-stone-50 border border-stone-100 rounded-xl p-4
                             <?= $hasOffer ? 'border-amber-200 bg-amber-50' : '' ?>">
                    <div class="shrink-0 w-10 h-10 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center font-bold text-sm">
                        <?= mb_strtoupper(mb_substr($bm['company_name'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <?php if ($isPaid): ?>
                            <span class="font-semibold text-stone-800 text-sm">
                                <?= htmlspecialchars($bm['company_name']) ?>
                            </span>
                            <?php else: ?>
                            <!-- Nome oscurato per valutazioni non pagate -->
                            <span class="font-semibold text-stone-800 text-sm">
                                <?= $typeLabels[$bm['company_type']] ?? 'Acquirente' ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($bm['city']): ?>
                            <span class="text-xs text-stone-400">
                                📍 <?= htmlspecialchars($bm['city']) ?>
                                <?= $bm['province'] ? '(' . htmlspecialchars($bm['province']) . ')' : '' ?>
                            </span>
                            <?php endif; ?>
                            <span class="text-xs text-stone-300">Match: <?= $bm['match_score'] ?>%</span>
                        </div>

                        <?php if ($hasOffer): ?>
                        <div class="mt-1.5 bg-amber-100 border border-amber-200 rounded-lg px-3 py-2">
                            <span class="text-amber-800 font-bold text-sm">
                                Offerta: €<?= number_format($bm['offer_amount'], 2, ',', '.') ?>
                            </span>
                            <?php if ($bm['message']): ?>
                            <p class="text-stone-600 text-xs mt-1 italic">
                                "<?= htmlspecialchars(mb_substr($bm['message'], 0, 120)) ?>"
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php elseif ($bm['status'] === 'interested'): ?>
                        <p class="text-green-600 text-xs mt-1 font-medium">Interessato</p>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isPaid): ?>
                    <!-- Sblocca contatti con perizia pagata -->
                    <a href="/checkout.php?scan_id=<?= $scanId ?>"
                       class="shrink-0 text-xs bg-amber-500 hover:bg-amber-600 text-white font-medium px-3 py-1.5 rounded-lg transition">
                        Sblocca
                    </a>
                    <?php elseif ($bm['website']): ?>
                    <a href="<?= htmlspecialchars($bm['website']) ?>" target="_blank" rel="noopener"
                       class="shrink-0 text-xs bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium px-3 py-1.5 rounded-lg transition">
                        Sito →
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (!$isPaid): ?>
            <p class="text-center text-xs text-stone-300 mt-4">
                I nomi completi delle aziende e i contatti sono visibili con la perizia completa
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="mt-6 text-center">
        <a href="/"
           class="inline-block bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium px-6 py-3 rounded-xl transition">
            Valuta un altro oggetto
        </a>
    </div>
</main>

<footer class="bg-white border-t border-stone-200 py-6 mt-12">
    <div class="max-w-5xl mx-auto px-4 text-center text-xs text-stone-300">
        © <?= date('Y') ?> Soffitta.ai — Valutazione AI di oggetti d'antiquariato
    </div>
</footer>
</body>
</html>
