<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';
$pageTitle       = 'Contatti';
$pageDescription = 'Contatta il Lido Torre Conca. Siamo a Torre Conca, Palermo. Scrivici su WhatsApp, email o usa il nostro form.';
include __DIR__ . '/includes/header.php';
?>

<!-- Hero compatto -->
<div class="bg-ocean pt-28 pb-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-block px-4 py-1.5 bg-sand/20 text-sand text-xs font-semibold tracking-widest uppercase rounded-full mb-4">Contattaci</span>
        <h1 class="font-display text-4xl md:text-5xl font-bold text-white mb-3">Siamo qui per te</h1>
        <p class="text-blue-200 text-lg">Rispondiamo su WhatsApp entro pochi minuti durante gli orari di apertura.</p>
    </div>
</div>
<div class="-mt-1 bg-gray-50"><svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="w-full h-10"><path d="M0,20 C360,40 1080,0 1440,20 L1440,40 L0,40 Z" fill="#f9fafb"/></svg></div>

<section class="py-12 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

            <!-- Info contatto -->
            <div class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-display text-xl font-bold text-ocean mb-5">Dove trovarci</h2>
                    <?php $contacts = [['map-pin','Indirizzo','Torre Conca, 90044 Cinisi (PA)<br>Sicilia, Italia'],['phone','Telefono','<a href="tel:+393471234567" class="hover:text-sand transition-colors">+39 347 123 4567</a>'],['mail','Email','<a href="mailto:info@lidotorreconca.it" class="hover:text-sand transition-colors">info@lidotorreconca.it</a>'],['globe','Sito Web','<a href="https://lidotorreconca.it" class="hover:text-sand transition-colors">lidotorreconca.it</a>'],['clock','Orari','Lunedì – Domenica<br>08:30 – 19:00']]; foreach($contacts as [$ic,$label,$val]): ?>
                    <div class="flex gap-4 py-3 border-b border-gray-100 last:border-0">
                        <div class="w-9 h-9 bg-ocean/5 rounded-xl flex items-center justify-center flex-shrink-0">
                            <?= icon($ic,'w-4 h-4','#0c2340') ?>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wide font-medium"><?= $label ?></div>
                            <div class="text-gray-700 mt-0.5"><?= $val ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- WhatsApp CTA -->
                <a href="<?= waLink('Ciao! Vorrei informazioni sul Lido Torre Conca.') ?>" target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-3 w-full py-4 bg-green-500 hover:bg-green-600 text-white font-bold text-lg rounded-2xl shadow-lg transition-all duration-200 hover:scale-105">
                    <?= icon('whatsapp','w-6 h-6','white') ?> Scrivici su WhatsApp
                </a>

                <!-- Orari apertura -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-ocean mb-3 flex items-center gap-2">
                        <?= icon('clock','w-4 h-4','#0c2340') ?> Quando siamo aperti
                    </h3>
                    <div class="space-y-2 text-sm">
                        <?php $giorni = ['Lunedì','Martedì','Mercoledì','Giovedì','Venerdì','Sabato','Domenica']; foreach($giorni as $g): $isWE = in_array($g,['Sabato','Domenica']); ?>
                        <div class="flex justify-between items-center py-1.5 border-b border-gray-50 last:border-0">
                            <span class="text-gray-600 <?= $isWE ? 'font-semibold' : '' ?>"><?= $g ?></span>
                            <span class="flex items-center gap-1.5 text-green-600 font-medium">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> 08:30 – 19:00
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Form contatto + Mappa -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Form -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="font-display text-xl font-bold text-ocean mb-5 flex items-center gap-2">
                        <?= icon('mail','w-5 h-5','#0c2340') ?> Inviaci un messaggio
                    </h2>
                    <form id="form-contatto" novalidate>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="form-label">Nome *</label>
                                    <input type="text" id="c-nome" class="form-input" placeholder="Mario Rossi" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email *</label>
                                    <input type="email" id="c-email" class="form-input" placeholder="mario@email.it" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Oggetto</label>
                                <select id="c-oggetto" class="form-input">
                                    <option value="Prenotazione">Informazioni prenotazione</option>
                                    <option value="Noleggio">Noleggio attrezzature</option>
                                    <option value="Navetta">Servizio navetta</option>
                                    <option value="Food">Food & drinks</option>
                                    <option value="Abbonamento">Abbonamenti</option>
                                    <option value="Altro">Altro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Messaggio *</label>
                                <textarea id="c-messaggio" class="form-input" rows="5" placeholder="Scrivi qui il tuo messaggio..." required></textarea>
                            </div>
                            <p class="text-xs text-gray-400 flex items-start gap-2">
                                <?= icon('info','w-4 h-4 flex-shrink-0','#94a3b8') ?>
                                Il messaggio verrà aperto in WhatsApp. Non è richiesta nessuna registrazione.
                            </p>
                            <button type="submit" class="btn-primary w-full h-14 text-base gap-2">
                                <?= icon('whatsapp','w-5 h-5','currentColor') ?> Invia via WhatsApp
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Mappa placeholder -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="h-64 bg-gradient-to-br from-ocean via-wave/60 to-sand-light relative flex items-center justify-center">
                        <div class="absolute inset-0 opacity-20" style="background-image:repeating-linear-gradient(0deg,rgba(255,255,255,.15) 0px,rgba(255,255,255,.15) 1px,transparent 1px,transparent 40px),repeating-linear-gradient(90deg,rgba(255,255,255,.15) 0px,rgba(255,255,255,.15) 1px,transparent 1px,transparent 40px)"></div>
                        <div class="text-center text-white z-10">
                            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                                <?= icon('map-pin','w-6 h-6','white') ?>
                            </div>
                            <div class="font-display text-lg font-bold">Lido Torre Conca</div>
                            <div class="text-white/80 text-sm mt-1">Torre Conca · Palermo, Sicilia</div>
                            <a href="https://maps.google.com/?q=Torre+Conca+Palermo+Sicilia" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 mt-3 px-4 py-1.5 bg-white/20 hover:bg-white/30 text-white text-sm font-medium rounded-full transition-colors">
                                <?= icon('globe','w-3.5 h-3.5','white') ?> Apri in Maps
                            </a>
                        </div>
                    </div>
                    <div class="px-5 py-4 flex items-center gap-3">
                        <?= icon('map-pin','w-4 h-4','#0c2340') ?>
                        <span class="text-gray-700 text-sm">Torre Conca, 90044 Cinisi (PA) — Sicilia, Italia</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-16 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="font-display text-3xl font-bold text-ocean">Hai altre domande?</h2>
        </div>
        <div class="bg-gray-50 rounded-2xl divide-y divide-gray-200 border border-gray-100 overflow-hidden">
            <?php $faqs = [['Come posso prenotare?','Usa la nostra mappa interattiva su questa pagina o scrivi su WhatsApp. La prenotazione è possibile fino a 1 ora prima dell\'arrivo.'],['Posso cancellare la prenotazione?','Sì, puoi annullare inserendo il codice prenotazione nella pagina prenota, oppure contattandoci direttamente su WhatsApp.'],['Accettate pagamenti online?','Al momento il pagamento avviene in loco alla cassa. La prenotazione online è gratuita e senza carta di credito.'],['Come funziona il QR code per ordinare cibo?','Ogni postazione ha un QR code dedicato. Scansionalo con il tuo smartphone per aprire il menu e ordinare: il personale porterà tutto direttamente da te.'],['Posso portare il mio cibo?','È permesso portare piccoli snack. Le bevande alcoliche esterne non sono consentite per normativa.']]; foreach($faqs as [$q,$a]): ?>
            <div class="accordion-item">
                <button class="accordion-trigger px-6 text-left">
                    <span class="font-medium text-ocean"><?= h($q) ?></span>
                    <span class="accordion-icon ml-4 flex-shrink-0"><?= icon('chevron-down','w-5 h-5','#0c2340') ?></span>
                </button>
                <div class="accordion-body px-6 text-gray-600"><?= h($a) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-8">
            <p class="text-gray-500 mb-4">Non hai trovato risposta? Scrivici direttamente.</p>
            <a href="<?= waLink('Ciao! Ho una domanda sul Lido Torre Conca.') ?>" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-7 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-full transition-all">
                <?= icon('whatsapp','w-4 h-4','white') ?> Scrivici su WhatsApp
            </a>
        </div>
    </div>
</section>

<script>
document.getElementById('form-contatto').addEventListener('submit', function(e) {
    e.preventDefault();
    const nome     = document.getElementById('c-nome').value.trim();
    const email    = document.getElementById('c-email').value.trim();
    const oggetto  = document.getElementById('c-oggetto').value;
    const messaggio = document.getElementById('c-messaggio').value.trim();

    if (!nome || !email || !messaggio) {
        Toast.error('Compila tutti i campi obbligatori.');
        return;
    }

    const testo = `Ciao! Sono ${nome} (${email}).\n\nOggetto: ${oggetto}\n\n${messaggio}`;
    const url   = `https://wa.me/<?= WHATSAPP_NUM ?>?text=${encodeURIComponent(testo)}`;
    window.open(url, '_blank', 'noopener');
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
