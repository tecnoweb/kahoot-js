<?php
if (!isset($user)) {
    $user = function_exists('getCurrentUser') ? getCurrentUser() : null;
}
// i18n potrebbe non essere inizializzata in alcune pagine
$_tNav = function(string $k, string $fb): string {
    return function_exists('t') ? t($k) : $fb;
};
?>
<nav class="bg-white border-b border-stone-200 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2 shrink-0">
            <span class="text-2xl">🏛️</span>
            <span class="text-xl font-bold" style="font-family:'Playfair Display',serif">Soffitta.ai</span>
        </a>
        <div class="flex items-center gap-3 text-sm">
            <a href="/come-funziona" class="text-stone-500 hover:text-amber-600 transition hidden md:inline">
                <?= $_tNav('nav_how', 'Come funziona') ?>
            </a>
            <a href="/prezzi" class="text-stone-500 hover:text-amber-600 transition hidden md:inline">
                <?= $_tNav('nav_prices', 'Prezzi') ?>
            </a>
            <a href="/faq" class="text-stone-500 hover:text-amber-600 transition hidden md:inline">
                <?= $_tNav('nav_faq', 'FAQ') ?>
            </a>

            <!-- Language switcher -->
            <?php if (function_exists('renderLangSwitcher')) renderLangSwitcher(); ?>

            <?php if ($user): ?>
                <a href="/dashboard.php"
                   class="bg-amber-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-amber-600 transition">
                    <?= $_tNav('nav_dashboard', 'Dashboard') ?>
                </a>
            <?php else: ?>
                <a href="/login.php" class="text-stone-600 hover:text-amber-600 transition hidden sm:inline">
                    <?= $_tNav('nav_login', 'Accedi') ?>
                </a>
                <a href="/register.php"
                   class="bg-amber-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-amber-600 transition hidden sm:inline-block">
                    <?= $_tNav('nav_register', 'Registrati') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
