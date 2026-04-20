<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';
$pageTitle = 'Benvenuto';
$pageDescription = 'Lido Torre Conca – Il tuo stabilimento balneare premium a Torre Conca. 60 postazioni, noleggio attrezzature, food & drinks. Prenota online!';
$prezzi = fetchAll('SELECT * FROM prezzi ORDER BY id');
$noleggi = fetchAll('SELECT * FROM noleggi');
include __DIR__ . '/includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════ -->
<section class="hero-section relative min-h-screen flex items-center overflow-hidden">
    <!-- Animated ocean background -->
    <div class="absolute inset-0 z-0 hero-bg"></div>

    <!-- Floating bubbles -->
    <div class="absolute inset-0 z-0 overflow-hidden">
        <?php for($i=0;$i<8;$i++): ?>
        <div class="bubble bubble-<?=$i?>"></div>
        <?php endfor; ?>
    </div>

    <!-- Wave SVG bottom -->
    <div class="absolute bottom-0 left-0 right-0 z-10">
        <svg viewBox="0 0 1440 140" preserveAspectRatio="none" class="w-full h-24 md:h-36">
            <path d="M0,80 C200,140 400,20 720,80 C1040,140 1240,20 1440,80 L1440,140 L0,140 Z" fill="white"/>
        </svg>
    </div>

    <!-- Content -->
    <div class="relative z-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-32 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-5 py-2 mb-8">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
            <span class="text-white text-sm font-medium">Aperto Oggi · 08:30 – 19:00</span>
        </div>

        <h1 class="font-display text-5xl sm:text-6xl md:text-7xl lg:text-8xl font-bold text-white mb-6 leading-tight">
            Il tuo paradiso<br>
            <span class="text-sand">sul mare</span>
        </h1>
        <p class="text-xl md:text-2xl text-blue-100 mb-10 max-w-2xl mx-auto font-light leading-relaxed">
            60 postazioni esclusive, servizi premium e il meglio della costa siciliana. Da lunedì a domenica.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-14">
            <a href="/prenota.php"
               class="w-full sm:w-auto px-8 py-4 bg-sand hover:bg-sand-dark text-ocean font-bold text-lg rounded-full shadow-2xl hover:shadow-sand/30 transition-all duration-300 hover:scale-105">
                🏖️ Prenota la tua Postazione
            </a>
            <a href="/servizi.php"
               class="w-full sm:w-auto px-8 py-4 bg-white/10 backdrop-blur-md border border-white/30 hover:bg-white/20 text-white font-semibold text-lg rounded-full transition-all duration-300 hover:scale-105">
                Scopri i Servizi
            </a>
        </div>

        <!-- Stats bar -->
        <div class="inline-flex flex-wrap justify-center gap-6 md:gap-10 bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl px-8 py-5">
            <div class="text-center">
                <div class="text-3xl font-bold text-sand font-display">60</div>
                <div class="text-white/80 text-xs mt-0.5">Postazioni</div>
            </div>
            <div class="w-px bg-white/20 hidden md:block"></div>
            <div class="text-center">
                <div class="text-3xl font-bold text-sand font-display">4</div>
                <div class="text-white/80 text-xs mt-0.5">Attività Nautiche</div>
            </div>
            <div class="w-px bg-white/20 hidden md:block"></div>
            <div class="text-center">
                <div class="text-3xl font-bold text-sand font-display">7</div>
                <div class="text-white/80 text-xs mt-0.5">Giorni su 7</div>
            </div>
            <div class="w-px bg-white/20 hidden md:block"></div>
            <div class="text-center">
                <div class="text-3xl font-bold text-sand font-display">⭐</div>
                <div class="text-white/80 text-xs mt-0.5">Premium</div>
            </div>
        </div>
    </div>

    <!-- Scroll cue -->
    <div class="absolute bottom-14 left-1/2 -translate-x-1/2 z-20 animate-bounce">
        <svg class="w-6 h-6 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     QUICK BOOKING BAR
══════════════════════════════════════════════════════ -->
<section class="bg-white shadow-xl relative z-30 -mt-1">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6">
        <div class="quick-book-card rounded-2xl p-5 md:p-6 shadow-2xl border border-sand/20">
            <h2 class="text-center font-display text-xl font-semibold text-ocean mb-5">Prenota in 30 secondi</h2>
            <form action="/prenota.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div class="form-group">
                    <label class="form-label">Data arrivo</label>
                    <input type="date" name="data_inizio" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>"
                           class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Data partenza</label>
                    <input type="date" name="data_fine" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>"
                           class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-input">
                        <option value="giornata_intera">Giornata Intera</option>
                        <option value="mezza_giornata">Mezza Giornata</option>
                        <option value="settimanale">Abbonamento Settimanale</option>
                        <option value="mensile">Abbonamento Mensile</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full h-12 text-base">
                        Cerca Disponibilità
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     SERVIZI PRINCIPALI
══════════════════════════════════════════════════════ -->
<section class="py-20 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="text-sand font-semibold text-sm tracking-widest uppercase">Cosa offriamo</span>
            <h2 class="font-display text-4xl md:text-5xl font-bold text-ocean mt-2">Tutto quello che desideri</h2>
            <div class="w-16 h-1 bg-sand mx-auto mt-4 rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            <!-- Postazioni -->
            <div class="service-card group">
                <div class="service-icon">🏖️</div>
                <h3 class="service-title">60 Postazioni Premium</h3>
                <p class="service-text">Ogni postazione include 2 lettini ergonomici e 1 ombrellone di qualità. 6 file dalla battigia al retro, per scegliere la tua posizione ideale.</p>
                <a href="/prenota.php" class="service-link group-hover:text-sand">Scegli la tua →</a>
            </div>

            <!-- Noleggio -->
            <div class="service-card group">
                <div class="service-icon">🚤</div>
                <h3 class="service-title">Noleggio Attrezzature</h3>
                <p class="service-text">Pedalò, canoe, moto d'acqua e SUP disponibili a noleggio. Esplora il mare in totale sicurezza con il nostro staff qualificato.</p>
                <a href="/servizi.php" class="service-link group-hover:text-sand">Scopri i prezzi →</a>
            </div>

            <!-- Food & Drinks -->
            <div class="service-card group">
                <div class="service-icon">🍹</div>
                <h3 class="service-title">Food & Drinks</h3>
                <p class="service-text">Cucina fresca, cocktail, granite e bibite fresche direttamente in postazione. Il servizio al tavolo è incluso per tutti gli ospiti.</p>
                <a href="/servizi.php#food" class="service-link group-hover:text-sand">Il menù →</a>
            </div>

            <!-- Navetta -->
            <div class="service-card group">
                <div class="service-icon">🚌</div>
                <h3 class="service-title">Servizio Navetta</h3>
                <p class="service-text">Non hai l'auto? Nessun problema. Il nostro servizio navetta ti porta e riporta comodamente. Prenotabile con la tua postazione.</p>
                <a href="/prenota.php" class="service-link group-hover:text-sand">Aggiungi alla prenotazione →</a>
            </div>

            <!-- Abbonamenti -->
            <div class="service-card group">
                <div class="service-icon">🗓️</div>
                <h3 class="service-title">Abbonamenti</h3>
                <p class="service-text">Scegli l'abbonamento settimanale o mensile per la massima comodità. Risparmia e prenota la tua postazione fissa per tutta la stagione.</p>
                <a href="/prenota.php?tipo=settimanale" class="service-link group-hover:text-sand">Abbonati →</a>
            </div>

            <!-- WhatsApp -->
            <div class="service-card group border-green-100 bg-green-50/40">
                <div class="service-icon">💬</div>
                <h3 class="service-title">Assistenza WhatsApp</h3>
                <p class="service-text">Comunicazioni rapide via WhatsApp per qualsiasi esigenza: modifiche, richieste speciali, informazioni su disponibilità e servizi.</p>
                <a href="<?= waLink('Ciao! Sono interessato ai servizi del Lido Torre Conca.') ?>" target="_blank" rel="noopener" class="service-link group-hover:text-green-600">Scrivici ora →</a>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     PREZZI
══════════════════════════════════════════════════════ -->
<section class="py-20 bg-sand-light/40 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="text-sand font-semibold text-sm tracking-widest uppercase">Tariffe</span>
            <h2 class="font-display text-4xl md:text-5xl font-bold text-ocean mt-2">Prezzi Trasparenti</h2>
            <div class="w-16 h-1 bg-sand mx-auto mt-4 rounded-full"></div>
            <p class="text-gray-500 mt-4 max-w-xl mx-auto">Tutti i prezzi includono 2 lettini + 1 ombrellone per postazione. Nessun costo nascosto.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            $tipiPrezzi = ['giornata_intera','mezza_giornata','settimanale','mensile'];
            $priceIcons = ['giornata_intera'=>'☀️','mezza_giornata'=>'🌤️','settimanale'=>'🗓️','mensile'=>'🏆'];
            $priceHighlight = ['mensile' => true, 'settimanale' => false, 'giornata_intera' => false, 'mezza_giornata' => false];
            foreach ($prezzi as $p):
                if (!in_array($p['tipo'], $tipiPrezzi)) continue;
                $hl = $priceHighlight[$p['tipo']] ?? false;
            ?>
            <div class="price-card <?= $hl ? 'price-card-featured' : '' ?>">
                <?php if ($hl): ?><div class="price-badge">Più Scelto</div><?php endif; ?>
                <div class="text-4xl mb-3"><?= $priceIcons[$p['tipo']] ?? '🏖️' ?></div>
                <h3 class="font-display font-semibold text-lg <?= $hl ? 'text-white' : 'text-ocean' ?> mb-1"><?= h($p['nome']) ?></h3>
                <p class="<?= $hl ? 'text-blue-200' : 'text-gray-500' ?> text-sm mb-5"><?= h($p['descrizione'] ?? '') ?></p>
                <div class="price-amount <?= $hl ? 'text-sand' : 'text-ocean' ?>">
                    €<?= number_format($p['prezzo'], 0) ?>
                </div>
                <p class="<?= $hl ? 'text-blue-200' : 'text-gray-400' ?> text-xs mt-1 mb-6">per postazione</p>
                <a href="/prenota.php?tipo=<?= $p['tipo'] ?>"
                   class="block text-center px-4 py-2.5 rounded-full font-semibold text-sm transition-all duration-200 <?= $hl ? 'bg-sand hover:bg-sand-dark text-ocean' : 'bg-ocean hover:bg-ocean-light text-white' ?>">
                    Prenota
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Extra options -->
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <?php foreach ($prezzi as $p):
                if (in_array($p['tipo'], $tipiPrezzi)) continue; ?>
            <div class="flex items-center space-x-2 bg-white rounded-full px-5 py-2.5 shadow-sm border border-sand/20">
                <span class="text-sand font-bold">+€<?= number_format($p['prezzo'], 0) ?></span>
                <span class="text-gray-600 text-sm"><?= h($p['nome']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     BEACH MAP PREVIEW
══════════════════════════════════════════════════════ -->
<section class="py-20 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-sand font-semibold text-sm tracking-widest uppercase">Mappa Interattiva</span>
                <h2 class="font-display text-4xl md:text-5xl font-bold text-ocean mt-2 mb-5">Scegli la tua<br>postazione</h2>
                <p class="text-gray-600 text-lg leading-relaxed mb-8">
                    La nostra mappa interattiva ti mostra in tempo reale quali postazioni sono disponibili. Scegli la fila più vicina al mare o quella più riparata — la scelta è tua!
                </p>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-center space-x-3">
                        <span class="w-4 h-4 rounded bg-green-400 inline-block flex-shrink-0"></span>
                        <span class="text-gray-600">Postazione libera — clicca per prenotare</span>
                    </li>
                    <li class="flex items-center space-x-3">
                        <span class="w-4 h-4 rounded bg-red-400 inline-block flex-shrink-0"></span>
                        <span class="text-gray-600">Postazione occupata per la data selezionata</span>
                    </li>
                    <li class="flex items-center space-x-3">
                        <span class="w-4 h-4 rounded bg-gray-300 inline-block flex-shrink-0"></span>
                        <span class="text-gray-600">In manutenzione</span>
                    </li>
                </ul>
                <a href="/prenota.php" class="btn-primary inline-flex items-center space-x-2">
                    <span>Apri la Mappa</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>

            <!-- Mini map preview -->
            <div class="beach-map-preview rounded-3xl overflow-hidden shadow-2xl">
                <div class="sea-top p-3 text-center">
                    <span class="text-white/80 text-xs font-medium tracking-widest uppercase">🌊 Mare</span>
                </div>
                <div class="bg-sand-light/60 p-4">
                    <?php
                    $file = ['A','B','C','D','E','F'];
                    foreach ($file as $fi):
                    ?>
                    <div class="flex items-center mb-2">
                        <span class="text-ocean font-bold text-xs w-5 flex-shrink-0"><?= $fi ?></span>
                        <div class="flex flex-1 gap-1">
                            <?php for($n=1;$n<=10;$n++):
                                $r = rand(0,3);
                                $cls = $r===0 ? 'bg-green-400 hover:bg-green-500 cursor-pointer' : ($r===1 ? 'bg-red-400' : ($r===2 ? 'bg-green-400 cursor-pointer hover:bg-green-500' : 'bg-red-400'));
                                if ($fi === 'A' && $n <= 6) $cls = 'bg-green-400 hover:bg-green-500 cursor-pointer';
                            ?>
                            <a href="/prenota.php" class="flex-1 h-5 rounded <?= $cls ?> transition-colors" title="Postazione <?= $fi ?><?= $n ?>"></a>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="bg-ocean/10 p-3 text-center">
                    <span class="text-ocean/60 text-xs font-medium tracking-widest uppercase">Stabilimento</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     NOLEGGIO
══════════════════════════════════════════════════════ -->
<section class="py-20 bg-ocean text-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="text-sand font-semibold text-sm tracking-widest uppercase">Sport & Avventura</span>
            <h2 class="font-display text-4xl md:text-5xl font-bold text-white mt-2">Noleggio Attrezzature</h2>
            <div class="w-16 h-1 bg-sand mx-auto mt-4 rounded-full"></div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php foreach ($noleggi as $n): ?>
            <div class="rental-card text-center">
                <div class="text-5xl mb-4"><?= h($n['icona']) ?></div>
                <h3 class="font-semibold text-lg mb-1"><?= h($n['nome']) ?></h3>
                <p class="text-blue-300 text-sm mb-3">€<?= number_format($n['prezzo_ora'], 0) ?>/ora</p>
                <p class="text-blue-200/60 text-xs"><?= $n['quantita_disponibile'] ?> disponibili</p>
                <a href="<?= waLink('Vorrei noleggiare: ' . $n['nome']) ?>" target="_blank" rel="noopener"
                   class="mt-4 inline-block px-4 py-2 bg-sand/20 hover:bg-sand text-white hover:text-ocean text-sm font-medium rounded-full transition-all duration-200">
                    Noleggia
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     CTA FINALE
══════════════════════════════════════════════════════ -->
<section class="py-24 cta-gradient text-white text-center overflow-hidden relative">
    <div class="absolute inset-0 opacity-10">
        <div class="w-96 h-96 bg-white rounded-full absolute -top-20 -left-20"></div>
        <div class="w-64 h-64 bg-white rounded-full absolute -bottom-10 -right-10"></div>
    </div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6">
        <h2 class="font-display text-4xl md:text-5xl font-bold mb-6">Pronto per una<br>giornata da sogno?</h2>
        <p class="text-xl text-white/80 mb-10 leading-relaxed">
            Prenota subito la tua postazione. Puoi farlo fino a 1 ora prima dell'arrivo, anche da mobile.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/prenota.php" class="px-8 py-4 bg-sand hover:bg-sand-dark text-ocean font-bold text-lg rounded-full shadow-2xl transition-all duration-300 hover:scale-105">
                🏖️ Prenota Online
            </a>
            <a href="<?= waLink('Ciao! Vorrei prenotare una postazione al Lido Torre Conca.') ?>" target="_blank" rel="noopener"
               class="px-8 py-4 bg-white/10 backdrop-blur-md border border-white/30 hover:bg-white/20 text-white font-semibold text-lg rounded-full transition-all duration-300 hover:scale-105">
                💬 Scrivi su WhatsApp
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
