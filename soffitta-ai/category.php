<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';
require_once 'includes/seo.php';

setSecurityHeaders();

$slugParam = preg_replace('/[^a-z\-]/', '', strtolower($_GET['slug'] ?? ''));
$validCats = ['quadri', 'ceramiche', 'gioielli', 'mobili', 'argenteria', 'altro'];

if (!in_array($slugParam, $validCats, true)) {
    http_response_code(404);
    redirect('/');
}

$catLabels = [
    'quadri'    => ['🖼️', 'Quadri e dipinti', 'quadri e dipinti antichi'],
    'ceramiche' => ['🏺', 'Ceramiche e porcellane', 'ceramiche e porcellane antiche'],
    'gioielli'  => ['💍', 'Gioielli e orologi', 'gioielli vintage e orologi antichi'],
    'mobili'    => ['🪑', 'Mobili antichi', 'mobili e arredi antichi'],
    'argenteria'=> ['🥄', 'Argenteria', 'argenteria e oggetti in argento'],
    'altro'     => ['📦', 'Oggetti da collezione', 'oggetti da collezione e antiquariato vario'],
];

[$icon, $label, $descLabel] = $catLabels[$slugParam];

$faqs = [
    ['q' => "Come valuto i miei {$label} con l'AI?",
     'a' => "Carica una foto ad alta risoluzione su sfondo neutro. L'AI di Soffitta.ai analizza stile, materiali, marchi e condizioni, restituendo una stima di mercato in 30 secondi."],
    ['q' => "Quanto valgono i {$label} in media?",
     'a' => "Il valore varia enormemente in base a epoca, autore, condizioni e rarità. La nostra AI considera tutte queste variabili per fornire un range realistico basato su aste e mercati recenti."],
];

$pdo = getDB();
// Ultime perizie pubbliche di questa categoria
$stmt = $pdo->prepare(
    'SELECT id, object_name, image_path, estimated_min, estimated_max, era, created_at, public_slug
     FROM scans WHERE category = ? AND paid = 1 ORDER BY created_at DESC LIMIT 12'
);
$stmt->execute([$slugParam]);
$recentScans = $stmt->fetchAll();

// Totale perizie categoria
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM scans WHERE category = ? AND paid = 1');
$countStmt->execute([$slugParam]);
$total = (int) $countStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php
    renderSEO([
        'title'       => "Valuta i tuoi {$label} con l'AI — Soffitta.ai",
        'description' => "Stima online il valore di {$descLabel}. L'intelligenza artificiale analizza la foto e fornisce il valore di mercato in 30 secondi.",
        'keywords'    => "valutazione {$slugParam} antichi online, stima {$slugParam} AI, perizia {$slugParam} online",
        'url'         => BASE_URL . '/categoria/' . $slugParam,
    ]);
    renderPerformanceHead();
    renderSchemaFAQ($faqs);
    ?>
    <style>body { font-family: 'Inter', sans-serif; } h1,h2 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include 'includes/_nav.php'; ?>

<main class="max-w-5xl mx-auto px-4 py-12">
    <!-- Hero categoria -->
    <div class="text-center mb-10">
        <div class="text-6xl mb-4"><?= $icon ?></div>
        <h1 class="text-4xl font-bold mb-3">Valuta i tuoi <?= htmlspecialchars($label) ?> con l'AI</h1>
        <p class="text-stone-500 text-lg max-w-2xl mx-auto">
            Fotografa <?= htmlspecialchars($descLabel) ?> e scopri il valore di mercato in 30 secondi.
            <?= $total > 0 ? "Già <strong class='text-stone-700'>{$total} perizie</strong> effettuate." : '' ?>
        </p>
        <a href="/" class="mt-6 inline-block bg-amber-500 hover:bg-amber-600 text-white font-bold px-8 py-4 rounded-xl transition text-lg">
            Valuta ora — è gratis
        </a>
    </div>

    <!-- Perizie recenti -->
    <?php if ($recentScans): ?>
    <div class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Perizie recenti — <?= htmlspecialchars($label) ?></h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($recentScans as $scan): ?>
            <a href="/perizia/<?= htmlspecialchars($scan['public_slug'] ?? $scan['id']) ?>"
               class="bg-white rounded-xl border border-stone-200 overflow-hidden hover:shadow-md transition group">
                <?php if ($scan['image_path']): ?>
                <div class="h-32 overflow-hidden bg-stone-100">
                    <img src="/<?= htmlspecialchars($scan['image_path']) ?>"
                         alt="<?= htmlspecialchars($scan['object_name'] ?? $label) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">
                </div>
                <?php else: ?>
                <div class="h-32 bg-stone-100 flex items-center justify-center text-3xl"><?= $icon ?></div>
                <?php endif; ?>
                <div class="p-3">
                    <p class="text-xs font-medium text-stone-700 leading-snug mb-1 line-clamp-2">
                        <?= htmlspecialchars($scan['object_name'] ?? 'Oggetto') ?>
                    </p>
                    <p class="text-xs text-amber-600 font-bold">
                        €<?= number_format($scan['estimated_min'], 0, ',', '.') ?>–<?= number_format($scan['estimated_max'], 0, ',', '.') ?>
                    </p>
                    <?php if ($scan['era']): ?>
                    <p class="text-xs text-stone-300 mt-0.5"><?= htmlspecialchars($scan['era']) ?></p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- FAQ categoria -->
    <div class="max-w-3xl mx-auto mb-12">
        <h2 class="text-2xl font-bold mb-6">Domande sui <?= htmlspecialchars($label) ?></h2>
        <div class="space-y-4">
            <?php foreach ($faqs as $faq): ?>
            <details class="bg-white rounded-xl border border-stone-200 p-4 group">
                <summary class="font-semibold cursor-pointer list-none flex justify-between items-center">
                    <?= htmlspecialchars($faq['q']) ?>
                    <span class="text-amber-500 ml-4 group-open:rotate-180 transition-transform">▾</span>
                </summary>
                <p class="mt-3 text-stone-500 text-sm leading-relaxed"><?= htmlspecialchars($faq['a']) ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Altre categorie -->
    <div class="text-center">
        <h2 class="text-xl font-bold mb-4">Altre categorie</h2>
        <div class="flex flex-wrap gap-3 justify-center">
            <?php
            $allCats = ['quadri' => '🖼️', 'ceramiche' => '🏺', 'gioielli' => '💍', 'mobili' => '🪑', 'argenteria' => '🥄', 'altro' => '📦'];
            foreach ($allCats as $slug => $ico):
                if ($slug === $slugParam) continue; ?>
            <a href="/categoria/<?= $slug ?>"
               class="flex items-center gap-2 bg-white border border-stone-200 rounded-full px-4 py-2 text-sm font-medium hover:border-amber-400 hover:text-amber-700 transition capitalize">
                <?= $ico ?> <?= ucfirst($slug) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php include 'includes/_footer.php'; ?>
</body>
</html>
