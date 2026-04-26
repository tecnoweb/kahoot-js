<?php
// Includi solo dopo session_start() e con $user disponibile o getCurrentUser() chiamabile
if (!isset($user)) {
    $user = function_exists('getCurrentUser') ? getCurrentUser() : null;
}
?>
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
                <a href="/register.php" class="bg-amber-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-amber-600 transition hidden sm:inline-block">
                    Registrati
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
