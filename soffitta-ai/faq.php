<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';
require_once 'includes/seo.php';

setSecurityHeaders();

$faqs = [
    ['q' => 'Come funziona la valutazione AI?',
     'a' => 'Carichi una foto dell\'oggetto e la nostra AI, basata su Claude di Anthropic, lo analizza in 30 secondi identificando materiali, stile, epoca e condizioni. La stima si basa su milioni di transazioni di aste e mercati antiquari italiani ed europei.'],
    ['q' => 'Quanto costa una perizia?',
     'a' => 'La prima valutazione di base è sempre gratuita e include il nome dell\'oggetto, l\'epoca, le condizioni e il range di valore. La perizia completa con report PDF scaricabile, suggerimenti di vendita personalizzati e dettagli storici costa €2,99.'],
    ['q' => 'L\'AI è accurata?',
     'a' => 'Il sistema raggiunge un\'accuratezza media dell\'85% su oggetti ben fotografati in buona luce. Per ogni analisi forniamo un "indice di confidenza" (0–100%) che indica la certezza della stima. Con confidenza sotto il 30% l\'oggetto potrebbe richiedere una perizia fisica da un esperto.'],
    ['q' => 'Quali oggetti posso valutare?',
     'a' => 'Puoi valutare quadri e stampe, ceramiche e porcellane, gioielli e orologi, mobili antichi, argenteria, bronzi, sculture, libri antichi, francobolli, monete e oggetti da collezione in generale.'],
    ['q' => 'La perizia è legalmente valida?',
     'a' => 'Il report di Soffitta.ai è un\'analisi di mercato basata su AI, non una perizia giuridicamente vincolante. Per scopi legali, assicurativi formali o vendite di valore molto elevato, ti consigliamo di affiancare la nostra stima a quella di un perito certificato.'],
    ['q' => 'Come devo fotografare l\'oggetto?',
     'a' => 'Per risultati migliori: usa buona luce naturale o artificiale diffusa, sfondo neutro (bianco o grigio), fotografia frontale e poi dettagli (firme, marchi, retro del dipinto, eventuali difetti). Più dettagli fornisci, più precisa sarà la stima.'],
    ['q' => 'Le mie immagini sono al sicuro?',
     'a' => 'Sì. Le immagini sono caricate su server sicuri italiani, non vengono cedute a terzi e le perizie non pagate non vengono pubblicate. Le perizie pagate possono essere indicizzate dai motori di ricerca per aiutare altri utenti a trovare oggetti simili.'],
    ['q' => 'Come ricevo la perizia completa dopo il pagamento?',
     'a' => 'Immediatamente dopo il pagamento via Stripe, puoi scaricare il PDF dal tuo account. Ricevi anche un\'email con il link diretto al documento.'],
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php
    renderSEO([
        'title'       => 'FAQ — Domande frequenti — Soffitta.ai',
        'description' => 'Risposte alle domande più frequenti su come funziona la valutazione AI di oggetti antichi con Soffitta.ai.',
        'keywords'    => 'FAQ valutazione oggetti antichi AI, domande perizia antiquariato online',
        'url'         => BASE_URL . '/faq',
    ]);
    renderPerformanceHead();
    renderSchemaFAQ($faqs);
    ?>
    <style>body { font-family: 'Inter', sans-serif; } h1 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include 'includes/_nav.php'; ?>

<main class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold text-center mb-4">Domande frequenti</h1>
    <p class="text-stone-400 text-center mb-12">Tutto quello che devi sapere su Soffitta.ai</p>

    <div class="space-y-4">
        <?php foreach ($faqs as $i => $faq): ?>
        <details class="bg-white rounded-xl border border-stone-200 overflow-hidden group" <?= $i === 0 ? 'open' : '' ?>>
            <summary class="font-semibold cursor-pointer list-none flex justify-between items-center px-6 py-4 hover:bg-stone-50 transition">
                <span><?= htmlspecialchars($faq['q']) ?></span>
                <span class="text-amber-500 shrink-0 ml-4 group-open:rotate-180 transition-transform duration-200">▾</span>
            </summary>
            <div class="px-6 pb-5 text-stone-500 leading-relaxed text-sm border-t border-stone-100 pt-4">
                <?= htmlspecialchars($faq['a']) ?>
            </div>
        </details>
        <?php endforeach; ?>
    </div>

    <div class="mt-12 bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
        <p class="font-bold text-stone-800 text-lg mb-2">Non hai trovato risposta?</p>
        <p class="text-stone-500 text-sm mb-4">Scrivici, risponderemo entro 24 ore.</p>
        <a href="/contatti" class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-3 rounded-xl transition">
            Contattaci
        </a>
    </div>
</main>

<?php include 'includes/_footer.php'; ?>
</body>
</html>
