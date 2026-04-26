<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/helpers.php';
require_once 'includes/auth.php';
require_once 'includes/seo.php';
require_once 'includes/i18n.php';

$lang = initI18n();
setSecurityHeaders();
$user = getCurrentUser();
$updatedDate = '26 aprile 2026';
?>
<!DOCTYPE html>
<html <?= htmlLangAttrs() ?>>
<head>
    <meta charset="UTF-8">
    <?php
    renderSEO([
        'title'       => t('privacy_title') . ' — Soffitta.ai',
        'description' => 'Privacy Policy di Soffitta.ai: come raccogliamo, utilizziamo e proteggiamo i tuoi dati personali nel rispetto del GDPR.',
        'url'         => BASE_URL . '/privacy',
    ]);
    renderPerformanceHead();
    renderHreflang('/privacy');
    ?>
    <style>body { font-family: 'Inter', sans-serif; } h1,h2,h3 { font-family: 'Playfair Display', serif; } <?php if (isRtl()) echo 'body{text-align:right}'; ?></style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include 'includes/_nav.php'; ?>

<main class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold mb-2"><?= t('privacy_title') ?></h1>
    <p class="text-stone-400 text-sm mb-10"><?= t('privacy_updated', ['date' => $updatedDate]) ?></p>

    <div class="prose prose-stone max-w-none space-y-8 text-stone-600 leading-relaxed">

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">1. Titolare del Trattamento</h2>
            <p>Soffitta.ai (di seguito "noi" o "la piattaforma") è il titolare del trattamento dei dati personali raccolti tramite il sito <strong><?= htmlspecialchars(BASE_URL) ?></strong>.</p>
            <p class="mt-2">Per qualsiasi richiesta relativa alla privacy: <a href="mailto:privacy@soffitta.ai" class="text-amber-600 hover:underline">privacy@soffitta.ai</a></p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">2. Dati Raccolti</h2>
            <p>Raccogliamo le seguenti categorie di dati:</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li><strong>Dati di registrazione:</strong> nome, indirizzo email, password (cifrata)</li>
                <li><strong>Immagini caricate:</strong> foto degli oggetti da valutare, elaborate localmente</li>
                <li><strong>Dati di utilizzo:</strong> scansioni effettuate, pagamenti, preferenze</li>
                <li><strong>Dati tecnici:</strong> indirizzo IP, browser, cookie di sessione</li>
                <li><strong>Dati aziendali</strong> (per utenti business): ragione sociale, P.IVA, preferenze acquisto</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">3. Finalità del Trattamento</h2>
            <p>Trattiamo i tuoi dati per:</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li>Fornire il servizio di valutazione AI degli oggetti</li>
                <li>Gestire account e autenticazione sicura</li>
                <li>Elaborare pagamenti tramite Stripe</li>
                <li>Abbinare oggetti con potenziali acquirenti (buyer matching)</li>
                <li>Migliorare la qualità del servizio (analytics anonimi)</li>
                <li>Adempiere a obblighi legali e fiscali</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">4. Base Giuridica (GDPR Art. 6)</h2>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li><strong>Contratto</strong> (art. 6.1.b): esecuzione del servizio di valutazione</li>
                <li><strong>Consenso</strong> (art. 6.1.a): cookie analitici e di marketing</li>
                <li><strong>Legittimo interesse</strong> (art. 6.1.f): sicurezza, antifrode, miglioramento del servizio</li>
                <li><strong>Obbligo legale</strong> (art. 6.1.c): conservazione fatture e documenti fiscali</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">5. Conservazione dei Dati</h2>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li>Dati account: finché l'account è attivo + 2 anni dopo cancellazione</li>
                <li>Immagini caricate: 12 mesi dalla scansione (poi anonimizzate o eliminate)</li>
                <li>Dati di pagamento: 10 anni (obbligo fiscale italiano)</li>
                <li>Log tecnici: 90 giorni</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">6. Condivisione dei Dati</h2>
            <p>Non vendiamo i tuoi dati. Li condividiamo solo con:</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li><strong>Anthropic Claude API:</strong> le immagini vengono trasmesse per l'analisi AI (base64 sicura via HTTPS)</li>
                <li><strong>Stripe:</strong> elaborazione pagamenti (PCI-DSS compliant)</li>
                <li><strong>Provider hosting:</strong> server Plesk in Europa</li>
                <li><strong>Aziende acquirenti:</strong> solo i dati dell'oggetto (mai dati personali dell'utente) nel sistema di buyer matching</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">7. I Tuoi Diritti (GDPR Artt. 15-22)</h2>
            <p>Hai diritto di:</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li><strong>Accesso</strong> (art. 15): ottenere copia dei tuoi dati</li>
                <li><strong>Rettifica</strong> (art. 16): correggere dati inesatti</li>
                <li><strong>Cancellazione</strong> (art. 17): "diritto all'oblio"</li>
                <li><strong>Limitazione</strong> (art. 18): limitare il trattamento</li>
                <li><strong>Portabilità</strong> (art. 20): ricevere i dati in formato machine-readable</li>
                <li><strong>Opposizione</strong> (art. 21): opporti al trattamento</li>
                <li><strong>Revoca consenso</strong>: in qualsiasi momento per i trattamenti basati su consenso</li>
            </ul>
            <p class="mt-3">Per esercitare i tuoi diritti: <a href="mailto:privacy@soffitta.ai" class="text-amber-600 hover:underline">privacy@soffitta.ai</a></p>
            <p class="mt-2">Hai il diritto di presentare reclamo all'<a href="https://www.garanteprivacy.it" target="_blank" rel="noopener" class="text-amber-600 hover:underline">Autorità Garante per la Protezione dei Dati Personali</a>.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">8. Sicurezza</h2>
            <p>Implementiamo misure tecniche e organizzative adeguate: cifratura HTTPS/TLS, password bcrypt, prepared statements SQL, header di sicurezza HTTP, accesso limitato ai dati.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">9. Cookie</h2>
            <p>Per informazioni dettagliate sull'uso dei cookie consulta la nostra <a href="/cookie-policy" class="text-amber-600 hover:underline">Cookie Policy</a>.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">10. Trasferimenti Internazionali</h2>
            <p>Le immagini vengono elaborate dall'API di Anthropic (USA). Il trasferimento avviene con garanzie adeguate ai sensi del GDPR (Standard Contractual Clauses). I dati non vengono conservati permanentemente da Anthropic per scopi di addestramento.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">11. Modifiche</h2>
            <p>Ci riserviamo il diritto di aggiornare questa policy. In caso di modifiche sostanziali, ti avviseremo via email o con un banner prominente sul sito.</p>
        </section>
    </div>

    <div class="mt-10 bg-amber-50 border border-amber-200 rounded-xl p-5 text-sm text-stone-600">
        <p class="font-semibold mb-1">Richiesta cancellazione dati</p>
        <p>Per richiedere la cancellazione del tuo account e di tutti i dati associati, scrivi a
            <a href="mailto:privacy@soffitta.ai" class="text-amber-600 hover:underline font-medium">privacy@soffitta.ai</a>
            con oggetto "Cancellazione dati GDPR". Risponderemo entro 30 giorni.
        </p>
    </div>
</main>

<?php include 'includes/_footer.php'; ?>
<?php include 'includes/_gdpr.php'; ?>
</body>
</html>
