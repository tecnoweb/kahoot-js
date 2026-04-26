<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';
require_once 'includes/seo.php';

setSecurityHeaders();
$csrf  = generateCsrfToken();
$user  = getCurrentUser();
$faqs  = [
    ['q' => 'Come funziona la valutazione AI?', 'a' => 'Carichi una foto dell\'oggetto e la nostra AI, addestrata su milioni di perizie antiquarie, lo identifica e stima il valore di mercato in 30 secondi.'],
    ['q' => 'Quanto costa una perizia?', 'a' => 'La prima valutazione di base è gratuita. La perizia dettagliata con relazione completa e suggerimenti di vendita costa soli €2,99.'],
    ['q' => 'L\'AI è accurata?', 'a' => 'Il sistema raggiunge un\'accuratezza media del 85% su oggetti ben illuminati. Forniamo sempre un range di valore e un indice di confidenza per trasparenza.'],
    ['q' => 'Quali oggetti posso valutare?', 'a' => 'Quadri, ceramiche, gioielli, mobili, argenteria, orologi, libri antichi, porcellane, bronzi e molto altro.'],
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php renderSEO(); renderPerformanceHead(); renderSchemaWebsite(); renderSchemaFAQ($faqs); ?>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Playfair Display', serif; }
        .dropzone-active { border-color: #f59e0b !important; background-color: #fffbeb; }
    </style>
</head>
<body class="bg-stone-50 text-stone-800 min-h-screen">

<!-- Navbar -->
<nav class="bg-white border-b border-stone-200 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2">
            <span class="text-2xl">🏛️</span>
            <span class="text-xl font-bold" style="font-family:'Playfair Display',serif">Soffitta.ai</span>
        </a>
        <div class="flex items-center gap-4 text-sm">
            <a href="/come-funziona" class="text-stone-500 hover:text-amber-600 transition hidden sm:inline">Come funziona</a>
            <a href="/prezzi" class="text-stone-500 hover:text-amber-600 transition hidden sm:inline">Prezzi</a>
            <a href="/faq" class="text-stone-500 hover:text-amber-600 transition hidden sm:inline">FAQ</a>
            <?php if ($user): ?>
                <a href="/dashboard.php" class="bg-amber-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-amber-600 transition">
                    Dashboard
                </a>
            <?php else: ?>
                <a href="/login.php" class="text-stone-600 hover:text-amber-600 transition">Accedi</a>
                <a href="/register.php" class="bg-amber-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-amber-600 transition">
                    Registrati
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="max-w-3xl mx-auto px-4 pt-16 pb-8 text-center">
    <h1 class="text-4xl md:text-5xl font-bold text-stone-900 leading-tight mb-4">
        Scopri il valore degli<br>oggetti di casa con l'AI
    </h1>
    <p class="text-lg text-stone-500 mb-8">
        Fotografa quadri, gioielli, ceramiche o mobili trovati in soffitta.<br>
        In 30 secondi l'AI li identifica e stima il valore di mercato.
    </p>

    <!-- Upload area -->
    <div id="uploadSection" class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
        <div id="dropzone"
             class="border-2 border-dashed border-stone-300 rounded-xl p-10 cursor-pointer transition-colors hover:border-amber-400 hover:bg-amber-50"
             role="button" aria-label="Carica immagine oggetto">
            <div id="dropzoneContent">
                <div class="text-5xl mb-4">📷</div>
                <p class="text-stone-600 font-medium">Trascina qui la foto dell'oggetto</p>
                <p class="text-stone-400 text-sm mt-1">oppure clicca per scegliere un file</p>
                <p class="text-stone-300 text-xs mt-3">JPG, PNG, WEBP · max 10MB</p>
            </div>
            <img id="preview" src="" alt="Anteprima oggetto" class="hidden max-h-64 mx-auto rounded-xl mt-4 object-contain">
        </div>
        <input type="file" id="fileInput" accept="image/*" class="hidden">
        <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrf) ?>">

        <!-- Loader -->
        <div id="loader" class="hidden mt-6 text-center">
            <div class="inline-block w-10 h-10 border-4 border-amber-300 border-t-amber-600 rounded-full animate-spin mb-3"></div>
            <p class="text-stone-500 text-sm">L'AI sta analizzando il tuo oggetto…</p>
        </div>

        <!-- Risultato -->
        <div id="result" class="hidden mt-4"></div>
    </div>
</section>

<!-- Come funziona — pillole -->
<section class="max-w-4xl mx-auto px-4 py-12">
    <h2 class="text-2xl font-bold text-center mb-8">Come funziona</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ([
            ['📸', 'Fotografa', 'Scatta o carica la foto dell\'oggetto con il tuo smartphone'],
            ['🤖', 'L\'AI analizza', 'In 30 secondi identifica l\'oggetto, l\'epoca e le condizioni'],
            ['💰', 'Scopri il valore', 'Ricevi la stima di mercato e i migliori canali di vendita'],
        ] as [$icon, $title, $desc]): ?>
        <div class="bg-white rounded-xl p-6 border border-stone-200 text-center">
            <div class="text-4xl mb-3"><?= $icon ?></div>
            <h3 class="font-bold text-lg mb-2"><?= $title ?></h3>
            <p class="text-stone-500 text-sm"><?= $desc ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Categorie -->
<section class="max-w-4xl mx-auto px-4 py-6 pb-12">
    <h2 class="text-2xl font-bold text-center mb-6">Categorie valutate</h2>
    <div class="flex flex-wrap gap-3 justify-center">
        <?php
        $cats = [
            'quadri' => '🖼️', 'ceramiche' => '🏺', 'gioielli' => '💍',
            'mobili' => '🪑', 'argenteria' => '🥄', 'altro' => '📦',
        ];
        foreach ($cats as $slug => $icon): ?>
        <a href="/categoria/<?= $slug ?>"
           class="flex items-center gap-2 bg-white border border-stone-200 rounded-full px-4 py-2 text-sm font-medium hover:border-amber-400 hover:text-amber-700 transition capitalize">
            <?= $icon ?> <?= ucfirst($slug) ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- FAQ -->
<section class="max-w-3xl mx-auto px-4 py-12 border-t border-stone-200">
    <h2 class="text-2xl font-bold text-center mb-8">Domande frequenti</h2>
    <div class="space-y-4">
        <?php foreach ($faqs as $faq): ?>
        <details class="bg-white rounded-xl border border-stone-200 p-4 group">
            <summary class="font-semibold cursor-pointer list-none flex justify-between items-center">
                <?= htmlspecialchars($faq['q']) ?>
                <span class="text-amber-500 group-open:rotate-180 transition-transform">▾</span>
            </summary>
            <p class="mt-3 text-stone-500 text-sm leading-relaxed"><?= htmlspecialchars($faq['a']) ?></p>
        </details>
        <?php endforeach; ?>
    </div>
</section>

<!-- Footer -->
<footer class="bg-white border-t border-stone-200 py-8 mt-8">
    <div class="max-w-5xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-stone-400">
        <div class="flex items-center gap-2">
            <span>🏛️</span>
            <span style="font-family:'Playfair Display',serif" class="font-bold text-stone-700">Soffitta.ai</span>
            <span>— Valutazione AI di oggetti d'antiquariato</span>
        </div>
        <div class="flex gap-4">
            <a href="/come-funziona" class="hover:text-amber-600 transition">Come funziona</a>
            <a href="/prezzi" class="hover:text-amber-600 transition">Prezzi</a>
            <a href="/faq" class="hover:text-amber-600 transition">FAQ</a>
            <a href="/contatti" class="hover:text-amber-600 transition">Contatti</a>
        </div>
    </div>
</footer>

<script src="/assets/app.js"></script>
</body>
</html>
