<?php
$_tFoot = function(string $k, string $fb): string {
    return function_exists('t') ? t($k) : $fb;
};
?>
<footer class="bg-white border-t border-stone-200 py-8 mt-12">
    <div class="max-w-5xl mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-stone-400 mb-4">
            <div class="flex items-center gap-2">
                <span>🏛️</span>
                <span style="font-family:'Playfair Display',serif" class="font-bold text-stone-700">Soffitta.ai</span>
                <span>— <?= $_tFoot('site_tagline', "Valutazione AI di oggetti d'antiquariato") ?></span>
            </div>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="/come-funziona" class="hover:text-amber-600 transition"><?= $_tFoot('nav_how', 'Come funziona') ?></a>
                <a href="/prezzi" class="hover:text-amber-600 transition"><?= $_tFoot('nav_prices', 'Prezzi') ?></a>
                <a href="/faq" class="hover:text-amber-600 transition"><?= $_tFoot('nav_faq', 'FAQ') ?></a>
                <a href="/categoria/quadri" class="hover:text-amber-600 transition">Quadri</a>
                <a href="/categoria/gioielli" class="hover:text-amber-600 transition">Gioielli</a>
                <a href="/register-company.php" class="hover:text-amber-600 transition"><?= $_tFoot('nav_company', "Sei un'azienda?") ?></a>
                <a href="/contatti" class="hover:text-amber-600 transition">Contatti</a>
            </div>
        </div>
        <!-- GDPR Links -->
        <div class="border-t border-stone-100 pt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-stone-300">
            <div class="flex flex-wrap gap-3">
                <a href="/privacy" class="hover:text-amber-600 transition"><?= $_tFoot('privacy_policy', 'Privacy Policy') ?></a>
                <span>·</span>
                <a href="/cookie-policy" class="hover:text-amber-600 transition"><?= $_tFoot('cookie_policy', 'Cookie Policy') ?></a>
                <span>·</span>
                <a href="/termini" class="hover:text-amber-600 transition"><?= $_tFoot('terms', 'Termini di servizio') ?></a>
                <span>·</span>
                <button onclick="if(typeof openCookieSettings==='function')openCookieSettings()"
                        class="hover:text-amber-600 transition cursor-pointer">
                    🍪 <?= $_tFoot('gdpr_customize', 'Gestisci cookie') ?>
                </button>
            </div>
            <div><?= str_replace('{year}', date('Y'), $_tFoot('footer_rights', '© ' . date('Y') . ' Soffitta.ai')) ?></div>
        </div>
    </div>
</footer>
