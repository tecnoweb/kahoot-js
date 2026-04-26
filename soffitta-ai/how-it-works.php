<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';
require_once 'includes/seo.php';

setSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php renderSEO([
        'title'       => 'Come funziona — Soffitta.ai',
        'description' => 'Scopri come stimare il valore degli oggetti vecchi con l\'intelligenza artificiale. Foto, analisi AI e stima in 30 secondi.',
        'keywords'    => 'come stimare valore oggetti vecchi, valutare antiquariato online, perizia AI online Italia',
        'url'         => BASE_URL . '/come-funziona',
    ]); renderPerformanceHead(); ?>
    <style>body { font-family: 'Inter', sans-serif; } h1,h2,h3 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include 'includes/_nav.php'; ?>

<main class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold text-center mb-4">Come funziona Soffitta.ai</h1>
    <p class="text-stone-400 text-center mb-12 text-lg">
        Dalla foto alla perizia in 30 secondi — ecco come stimiamo il valore dei tuoi oggetti
    </p>

    <div class="space-y-8">
        <?php
        $steps = [
            ['📸', 'Scatta o carica una foto', '1',
             'Fotografa l\'oggetto con buona luce, possibilmente su sfondo neutro. Il nostro sistema accetta JPG, PNG e WEBP fino a 10MB. Puoi fotografare quadri, ceramiche, gioielli, mobili, argenteria e molto altro.'],
            ['🤖', 'L\'AI analizza l\'immagine', '2',
             'Il nostro motore AI, basato su Claude di Anthropic, analizza ogni dettaglio: stile, materiali, tecniche di lavorazione, eventuali firme o marchi. Confronta l\'oggetto con milioni di transazioni d\'aste e mercati antiquari.'],
            ['📊', 'Ricevi la stima in 30 secondi', '3',
             'La valutazione include il nome dell\'oggetto, l\'epoca stimata, le condizioni, un range di valore di mercato in euro e un indice di confidenza che indica quanto l\'AI è certa dell\'analisi.'],
            ['💰', 'Sblocca la perizia completa', '4',
             'Per soli €2,99 ottieni il report PDF completo con stima dettagliata, suggerimenti personalizzati sui migliori canali di vendita e curiosità storiche sull\'oggetto. Utile per assicurazioni e successioni.'],
        ];
        foreach ($steps as [$icon, $title, $num, $desc]): ?>
        <div class="bg-white rounded-2xl border border-stone-200 p-6 flex gap-4 items-start">
            <div class="shrink-0 w-10 h-10 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center font-bold text-lg">
                <?= $num ?>
            </div>
            <div>
                <h2 class="text-xl font-bold mb-2"><?= $icon ?> <?= $title ?></h2>
                <p class="text-stone-500 leading-relaxed"><?= $desc ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-12 text-center">
        <a href="/" class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-bold px-8 py-4 rounded-xl transition text-lg">
            Prova gratis ora
        </a>
        <p class="text-stone-300 text-sm mt-3">Prima valutazione sempre gratuita</p>
    </div>
</main>

<?php include 'includes/_footer.php'; ?>
</body>
</html>
