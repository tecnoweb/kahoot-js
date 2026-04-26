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
?>
<!DOCTYPE html>
<html <?= htmlLangAttrs() ?>>
<head>
    <meta charset="UTF-8">
    <?php
    renderSEO([
        'title'       => t('cookie_policy') . ' — Soffitta.ai',
        'description' => 'Cookie Policy di Soffitta.ai: tipologie di cookie utilizzati, finalità e come gestire le preferenze.',
        'url'         => BASE_URL . '/cookie-policy',
    ]);
    renderPerformanceHead();
    renderHreflang('/cookie-policy');
    ?>
    <style>body { font-family: 'Inter', sans-serif; } h1,h2,h3 { font-family: 'Playfair Display', serif; } <?php if (isRtl()) echo 'body{text-align:right}'; ?></style>
</head>
<body class="bg-stone-50 min-h-screen">

<?php include 'includes/_nav.php'; ?>

<main class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold mb-2"><?= t('cookie_policy') ?></h1>
    <p class="text-stone-400 text-sm mb-10">Ultimo aggiornamento: 26 aprile 2026</p>

    <div class="space-y-8 text-stone-600 leading-relaxed">

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">Cosa sono i cookie</h2>
            <p>I cookie sono piccoli file di testo che vengono salvati sul tuo dispositivo quando visiti un sito web. Ci permettono di riconoscere il tuo browser e ricordare le tue preferenze.</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">Tipologie di cookie utilizzati</h2>

            <div class="space-y-4">
                <!-- Necessari -->
                <div class="bg-white border border-stone-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-stone-800">🔒 Cookie necessari</h3>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Sempre attivi</span>
                    </div>
                    <p class="text-sm text-stone-500 mb-3">Essenziali per il funzionamento del sito. Non possono essere disabilitati.</p>
                    <table class="w-full text-xs text-stone-500 border-t border-stone-100 pt-3">
                        <thead><tr class="text-stone-400 uppercase"><th class="text-left py-1">Nome</th><th class="text-left py-1">Finalità</th><th class="text-left py-1">Durata</th></tr></thead>
                        <tbody class="divide-y divide-stone-50">
                            <tr><td class="py-1.5 font-mono">PHPSESSID</td><td>Sessione PHP (autenticazione)</td><td>Sessione</td></tr>
                            <tr><td class="py-1.5 font-mono">lang</td><td>Preferenza lingua</td><td>30 giorni</td></tr>
                            <tr><td class="py-1.5 font-mono">csrf_token</td><td>Protezione CSRF</td><td>Sessione</td></tr>
                            <tr><td class="py-1.5 font-mono">consent_given</td><td>Memorizza scelta cookie</td><td>1 anno</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Analitici -->
                <div class="bg-white border border-stone-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-stone-800">📊 Cookie analitici</h3>
                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Opt-in</span>
                    </div>
                    <p class="text-sm text-stone-500 mb-3">Ci aiutano a capire come gli utenti interagiscono con il sito, in forma aggregata e anonima.</p>
                    <table class="w-full text-xs text-stone-500 border-t border-stone-100 pt-3">
                        <thead><tr class="text-stone-400 uppercase"><th class="text-left py-1">Nome</th><th class="text-left py-1">Fornitore</th><th class="text-left py-1">Durata</th></tr></thead>
                        <tbody class="divide-y divide-stone-50">
                            <tr><td class="py-1.5 font-mono">_ga</td><td>Google Analytics (opzionale)</td><td>2 anni</td></tr>
                            <tr><td class="py-1.5 font-mono">_gid</td><td>Google Analytics (opzionale)</td><td>24 ore</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Marketing -->
                <div class="bg-white border border-stone-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-stone-800">📢 Cookie di marketing</h3>
                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Opt-in</span>
                    </div>
                    <p class="text-sm text-stone-500">Attualmente non utilizziamo cookie di profilazione o remarketing di terze parti.</p>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">Come gestire i cookie</h2>
            <p class="mb-3">Puoi gestire le tue preferenze in qualsiasi momento tramite il pulsante qui sotto, oppure dalle impostazioni del tuo browser.</p>
            <button onclick="openCookieSettings()"
                    class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-3 rounded-xl transition">
                🍪 Gestisci preferenze cookie
            </button>
            <div class="mt-4 bg-stone-50 rounded-xl p-4 text-sm text-stone-500">
                <p class="font-medium mb-2">Istruzioni per browser:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener" class="text-amber-600 hover:underline">Google Chrome</a></li>
                    <li><a href="https://support.mozilla.org/kb/cookies-information-websites-store-on-your-computer" target="_blank" rel="noopener" class="text-amber-600 hover:underline">Mozilla Firefox</a></li>
                    <li><a href="https://support.apple.com/guide/safari/manage-cookies-sfri11471/mac" target="_blank" rel="noopener" class="text-amber-600 hover:underline">Apple Safari</a></li>
                    <li><a href="https://support.microsoft.com/microsoft-edge/delete-cookies-in-microsoft-edge" target="_blank" rel="noopener" class="text-amber-600 hover:underline">Microsoft Edge</a></li>
                </ul>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-bold text-stone-800 mb-3">Contatti</h2>
            <p>Per domande sulla nostra Cookie Policy: <a href="mailto:privacy@soffitta.ai" class="text-amber-600 hover:underline">privacy@soffitta.ai</a></p>
        </section>
    </div>
</main>

<?php include 'includes/_footer.php'; ?>
<?php include 'includes/_gdpr.php'; ?>
</body>
</html>
