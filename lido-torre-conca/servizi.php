<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';
$pageTitle       = 'Servizi';
$pageDescription = 'Tutti i servizi del Lido Torre Conca: noleggio pedalò, canoe, moto d\'acqua, SUP, food & drinks, navetta e abbonamenti.';
$noleggi = fetchAll('SELECT * FROM noleggi ORDER BY id');
$prezziDB = [];
foreach (fetchAll('SELECT tipo, prezzo FROM prezzi') as $r) $prezziDB[$r['tipo']] = $r['prezzo'];
include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<div class="bg-ocean pt-28 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-block px-4 py-1.5 bg-sand/20 text-sand text-xs font-semibold tracking-widest uppercase rounded-full mb-4">I Nostri Servizi</span>
        <h1 class="font-display text-4xl md:text-5xl font-bold text-white mb-4">Tutto quello che desideri,<br>direttamente in postazione</h1>
        <p class="text-blue-200 text-lg max-w-xl mx-auto">Da lunedì a domenica 08:30–19:00. Staff sempre disponibile.</p>
    </div>
</div>

<!-- Wave divider -->
<div class="-mt-1 bg-white"><svg viewBox="0 0 1440 60" preserveAspectRatio="none" class="w-full h-12"><path d="M0,40 C360,80 1080,0 1440,40 L1440,60 L0,60 Z" fill="white"/><path d="M0,40 C360,80 1080,0 1440,40" fill="none" stroke="#0c2340" stroke-width="0"/></svg></div>

<!-- ── Postazioni ─────────────────────────────────────────── -->
<section class="py-16 bg-white" id="postazioni">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-sand font-semibold text-sm tracking-widest uppercase">Comfort Premium</span>
                <h2 class="font-display text-3xl md:text-4xl font-bold text-ocean mt-2 mb-5">60 Postazioni Esclusive</h2>
                <p class="text-gray-600 leading-relaxed mb-8">Ogni postazione è attrezzata con 2 lettini ergonomici reclinabili e 1 ombrellone ampio. Sei file dalla battigia allo stabilimento — scegli la posizione che preferisci direttamente sulla mappa.</p>
                <div class="grid grid-cols-2 gap-4">
                    <?php $feats = [['chair','2 Lettini Inclusi','Ergonomici, reclinabili'],['umbrella','1 Ombrellone','Ampio e di qualità'],['clock','Orari Flessibili','Intera o mezza giornata'],['calendar','7 Giorni su 7','Sempre aperti'],['users','60 Postazioni','6 file disponibili'],['shield','Spazio Riservato','Solo tuo per tutta la giornata']]; foreach($feats as [$ic,$t,$s]): ?>
                    <div class="flex gap-3 p-3 rounded-xl bg-sand-light/40 border border-sand/20">
                        <div class="w-8 h-8 bg-ocean rounded-lg flex items-center justify-center flex-shrink-0"><?= icon($ic,'w-4 h-4','#d4a847') ?></div>
                        <div><div class="font-semibold text-ocean text-sm"><?= $t ?></div><div class="text-gray-500 text-xs"><?= $s ?></div></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Orari card -->
            <div class="bg-ocean rounded-3xl p-8 text-white">
                <h3 class="font-display text-2xl font-bold mb-6 flex items-center gap-3"><?= icon('clock','w-6 h-6','#d4a847') ?> Orari & Tariffe</h3>
                <div class="space-y-4">
                    <?php $tariffe = [['giornata_intera','Giornata Intera','08:30 – 19:00'],['mezza_giornata','Mezza Giornata','14:00 – 19:00'],['settimanale','Abbonamento Settimanale','7 giorni consecutivi'],['mensile','Abbonamento Mensile','30 giorni consecutivi']]; foreach($tariffe as [$key,$nome,$orario]): ?>
                    <div class="flex items-center justify-between py-3 border-b border-white/10 last:border-0">
                        <div><div class="font-semibold"><?= $nome ?></div><div class="text-blue-200 text-sm"><?= $orario ?></div></div>
                        <div class="text-sand font-bold text-xl">€<?= number_format($prezziDB[$key] ?? 0, 0) ?></div>
                    </div>
                    <?php endforeach; ?>
                    <div class="flex items-center justify-between py-2 bg-white/5 rounded-xl px-3">
                        <div class="text-sm">Lettino Extra</div>
                        <div class="text-sand font-bold">+€<?= number_format($prezziDB['extra_lettino'] ?? 5, 0) ?>/gg</div>
                    </div>
                    <div class="flex items-center justify-between py-2 bg-white/5 rounded-xl px-3">
                        <div class="text-sm">Servizio Navetta</div>
                        <div class="text-sand font-bold">+€<?= number_format($prezziDB['navetta'] ?? 3, 0) ?>/gg</div>
                    </div>
                </div>
                <a href="/prenota.php" class="btn-sand w-full mt-6 flex items-center justify-center gap-2">
                    <?= icon('calendar','w-4 h-4','#0c2340') ?> Prenota Ora
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ── Noleggio Nautico ───────────────────────────────────── -->
<section class="py-16 bg-ocean text-white" id="noleggio">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-sand font-semibold text-sm tracking-widest uppercase">Sport & Avventura</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold mt-2">Noleggio Attrezzature Nautiche</h2>
            <p class="text-blue-200 mt-3 max-w-xl mx-auto">Esplora il mare in sicurezza con il nostro staff certificato. Prenotazione via WhatsApp o direttamente al banco.</p>
        </div>
        <?php
        $nIcons = ['pedalo'=>'boat','canoa'=>'paddle','moto_acqua'=>'water-bike','sup'=>'paddle'];
        $nDescs = ['pedalo'=>'Perfetto per le famiglie, rilassante e divertente per tutta la giornata.','canoa'=>'Esplora le calette nascoste, ideale per 1-2 persone, facile da usare.','moto_acqua'=>'Adrenalina pura sul mare. Solo per adulti. Obbligo patente nautica.','sup'=>'Stand Up Paddle: equilibrio e relax. Ideale per tutte le età.'];
        ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($noleggi as $n): $ic = $nIcons[$n['tipo']] ?? 'boat'; ?>
            <div class="rental-card text-center">
                <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <?= icon($ic,'w-8 h-8','#d4a847') ?>
                </div>
                <h3 class="font-display text-xl font-bold mb-1"><?= h($n['nome']) ?></h3>
                <div class="text-sand font-bold text-2xl mb-2">€<?= number_format($n['prezzo_ora'],0) ?><span class="text-base font-normal text-blue-200">/ora</span></div>
                <p class="text-blue-200 text-sm mb-3"><?= $nDescs[$n['tipo']] ?? '' ?></p>
                <div class="text-xs text-blue-300 mb-4"><?= (int)$n['quantita_disponibile'] ?> disponibili</div>
                <a href="<?= waLink('Vorrei noleggiare un/una ' . $n['nome'] . ' al Lido Torre Conca') ?>" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-5 py-2 bg-sand/20 hover:bg-sand text-white hover:text-ocean rounded-full text-sm font-semibold transition-all duration-200">
                    <?= icon('whatsapp','w-4 h-4','currentColor') ?> Prenota
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Food & Drinks ─────────────────────────────────────── -->
<section class="py-16 bg-white" id="food">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-sand font-semibold text-sm tracking-widest uppercase">Ristoro</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-ocean mt-2">Food & Drinks</h2>
            <p class="text-gray-500 mt-3 max-w-xl mx-auto">Cucina fresca, cocktail estivi e spuntini direttamente sotto il tuo ombrellone. Ordina dalla tua postazione via QR code.</p>
        </div>
        <?php
        $cats = [
            ['food','Cucina','Panini, piadine, insalate, piatti caldi e grigliate di pesce fresco','ocean'],
            ['food','Aperitivi','Spritz, Negroni, prosecco e sfiziosità per l\'aperitivo in riva al mare','sand'],
            ['food','Cocktail','Mojito, Piña Colada, Hugo e tutta la selezione estiva','ocean'],
            ['food','Dolci','Granite siciliane artigianali, gelati e dolci freschi','sand'],
            ['food','Bibite','Acqua, succhi, bibite, birra artigianale e analcolici','ocean'],
            ['food','Menù Bambini','Piatti semplici, piccoli sandwich, succhi e ghiaccioli','sand'],
        ];
        ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            <?php foreach($cats as [$ic,$nome,$desc,$col]): ?>
            <div class="service-card">
                <div class="service-icon"><?= icon($ic,'w-6 h-6','#d4a847') ?></div>
                <h3 class="service-title"><?= $nome ?></h3>
                <p class="service-text"><?= $desc ?></p>
                <a href="/ordina.php" class="service-link">Ordina ora &rarr;</a>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- QR info banner -->
        <div class="bg-ocean/5 border border-ocean/10 rounded-2xl p-6 flex flex-col md:flex-row items-center gap-5">
            <div class="w-14 h-14 bg-ocean rounded-2xl flex items-center justify-center flex-shrink-0"><?= icon('grid','w-7 h-7','#d4a847') ?></div>
            <div class="text-center md:text-left">
                <h4 class="font-semibold text-ocean text-lg">Ordina direttamente dalla tua postazione</h4>
                <p class="text-gray-500 text-sm mt-1">Ogni ombrellone ha un QR code dedicato. Scansionalo, scegli dal menu e il personale porta tutto da te. Nessuna app richiesta.</p>
            </div>
            <a href="/ordina.php" class="btn-primary flex-shrink-0">Apri il Menu</a>
        </div>
    </div>
</section>

<!-- ── Navetta ───────────────────────────────────────────── -->
<section class="py-16 bg-sand-light/40" id="navetta">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            <div>
                <span class="text-sand font-semibold text-sm tracking-widest uppercase">Trasferimento</span>
                <h2 class="font-display text-3xl md:text-4xl font-bold text-ocean mt-2 mb-5">Servizio Navetta</h2>
                <p class="text-gray-600 leading-relaxed mb-6">Raggiungi il lido comodamente senza pensieri. Il nostro servizio navetta opera tutti i giorni con fermate nei punti principali della zona. Si prenota insieme alla postazione.</p>
                <ul class="space-y-3 mb-6">
                    <?php $navItems = ['Attivo 7 giorni su 7','Andata e ritorno inclusi','Fermate in punti centralizzati','Posti garantiti con prenotazione','+€'. number_format($prezziDB['navetta'] ?? 3,0). '/persona al giorno']; foreach($navItems as $ni): ?>
                    <li class="flex items-center gap-3 text-gray-700">
                        <span class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0"><?= icon('check','w-3 h-3','white') ?></span>
                        <?= h($ni) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="/prenota.php" class="btn-primary inline-flex items-center gap-2">
                    <?= icon('bus','w-4 h-4') ?> Aggiungi alla prenotazione
                </a>
            </div>
            <div class="bg-ocean rounded-3xl p-8 text-white">
                <h3 class="font-display text-xl font-bold mb-5"><?= icon('clock','w-5 h-5 inline mr-2','#d4a847') ?>Orari Navetta</h3>
                <?php $corse = [['Mattina — Andata','08:00 / 08:15 / 08:30'],['Pomeriggio — Andata','13:30 / 13:45 / 14:00'],['Sera — Ritorno','18:45 / 19:00 / 19:15']]; foreach($corse as [$label,$orari]): ?>
                <div class="py-3 border-b border-white/10 last:border-0">
                    <div class="text-blue-300 text-xs uppercase tracking-wide mb-1"><?= $label ?></div>
                    <div class="font-semibold"><?= $orari ?></div>
                </div>
                <?php endforeach; ?>
                <p class="text-blue-200 text-xs mt-4">Orari indicativi. Conferma al momento della prenotazione.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── Abbonamenti ────────────────────────────────────────── -->
<section class="py-16 bg-white" id="abbonamenti">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-sand font-semibold text-sm tracking-widest uppercase">Risparmia</span>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-ocean mt-2">Abbonamenti Stagionali</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-3xl mx-auto">
            <?php $abs = [['settimanale','Abbonamento Settimanale','7 giorni consecutivi, giornata intera',['Postazione fissa per 7 gg','Accesso 08:30–19:00','Priorità in mappa','Sconto noleggi 10%']],['mensile','Abbonamento Mensile','30 giorni, la scelta dei veri habitué',['Postazione fissa per 30 gg','Accesso prioritario','Sconto noleggi 20%','Food priority service','Locker in spiaggia incluso']]]; foreach($abs as $i=>[$key,$nome,$desc,$benefits]): $hl = $key==='mensile'; ?>
            <div class="price-card <?= $hl ? 'price-card-featured' : '' ?>">
                <?php if($hl): ?><div class="price-badge">Convenienza Massima</div><?php endif; ?>
                <?= icon('calendar','w-8 h-8 mb-3', $hl ? '#d4a847' : '#0c2340') ?>
                <h3 class="font-display text-xl font-bold <?= $hl ? 'text-white' : 'text-ocean' ?> mb-1"><?= $nome ?></h3>
                <p class="text-sm <?= $hl ? 'text-blue-200' : 'text-gray-500' ?> mb-5"><?= $desc ?></p>
                <div class="price-amount <?= $hl ? 'text-sand' : '' ?>">€<?= number_format($prezziDB[$key] ?? 0, 0) ?></div>
                <p class="text-xs <?= $hl ? 'text-blue-200' : 'text-gray-400' ?> mt-1 mb-5">per postazione</p>
                <ul class="space-y-2 mb-6 text-left">
                    <?php foreach($benefits as $b): ?>
                    <li class="flex items-center gap-2 text-sm <?= $hl ? 'text-white' : 'text-gray-600' ?>">
                        <span class="w-4 h-4 <?= $hl ? 'text-sand' : 'text-green-500' ?>"><?= icon('check','w-4 h-4', $hl ? '#d4a847' : '#22c55e') ?></span>
                        <?= h($b) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="/prenota.php?tipo=<?= $key ?>" class="block text-center px-4 py-3 rounded-full font-semibold text-sm transition-all <?= $hl ? 'bg-sand hover:bg-sand-dark text-ocean' : 'bg-ocean hover:bg-ocean-light text-white' ?>">
                    Sottoscrivi
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── FAQ ───────────────────────────────────────────────── -->
<section class="py-16 bg-sand-light/30">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="font-display text-3xl font-bold text-ocean">Domande Frequenti</h2>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100 overflow-hidden">
            <?php $faqs = [['Posso prenotare il giorno stesso?','Sì, puoi prenotare fino a 1 ora prima dell\'orario di arrivo desiderato. Per la mezza giornata (14:00) puoi prenotare fino alle 13:00.'],['Cosa include ogni postazione?','Ogni postazione include 2 lettini ergonomici reclinabili e 1 ombrellone ampio. È possibile aggiungere un lettino extra con un supplemento giornaliero.'],['Posso modificare la mia prenotazione?','Per modifiche contattaci via WhatsApp almeno 1 ora prima dell\'arrivo. Il personale ti assisterà.'],['Gli abbonamenti sono nominali?','Sì, gli abbonamenti sono intestati a una persona e non cedibili. Puoi però usufruirne con i tuoi ospiti.'],['C\'è un parcheggio?','Sì, è disponibile un\'area parcheggio nelle vicinanze. In alternativa usa il nostro servizio navetta.'],['Si accettano animali domestici?','Sì, gli animali di piccola taglia sono benvenuti purché al guinzaglio nelle aree comuni.']]; foreach($faqs as [$q,$a]): ?>
            <div class="accordion-item">
                <button class="accordion-trigger px-6">
                    <span><?= h($q) ?></span>
                    <span class="accordion-icon ml-4"><?= icon('chevron-down','w-5 h-5','#0c2340') ?></span>
                </button>
                <div class="accordion-body px-6"><?= h($a) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-20 cta-gradient text-white text-center">
    <div class="max-w-2xl mx-auto px-4">
        <h2 class="font-display text-3xl md:text-4xl font-bold mb-4">Pronto a goderti il mare?</h2>
        <p class="text-white/80 mb-8">Prenota ora la tua postazione o contattaci per qualsiasi informazione.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/prenota.php" class="btn-sand flex items-center justify-center gap-2">
                <?= icon('umbrella','w-4 h-4','#0c2340') ?> Prenota Online
            </a>
            <a href="<?= waLink('Ciao! Vorrei informazioni sui servizi del Lido Torre Conca.') ?>" target="_blank" rel="noopener"
               class="flex items-center justify-center gap-2 px-7 py-3 bg-white/10 border border-white/30 hover:bg-white/20 text-white font-semibold rounded-full transition-all">
                <?= icon('whatsapp','w-4 h-4','currentColor') ?> WhatsApp
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
