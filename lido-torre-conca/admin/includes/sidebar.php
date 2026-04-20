<?php
// Determina la pagina corrente per evidenziare il link attivo
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$navItems = [
    ['file' => 'index',        'label' => 'Dashboard',     'icon' => 'trending-up'],
    ['file' => 'mappa',        'label' => 'Mappa Spiaggia','icon' => 'grid'],
    ['file' => 'prenotazioni', 'label' => 'Prenotazioni',  'icon' => 'list'],
    ['file' => 'pos',          'label' => 'POS Cassa',     'icon' => 'credit-card'],
    ['file' => 'prezzi',       'label' => 'Prezzi',        'icon' => 'tag'],
    ['file' => 'impostazioni', 'label' => 'Impostazioni',  'icon' => 'settings'],
];
?>

<!-- ── SIDEBAR DESKTOP ───────────────────────────────────────── -->
<aside id="sidebar" class="hidden lg:flex flex-col fixed left-0 top-0 h-full w-60 bg-ocean-dark shadow-xl z-40">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
        <div class="flex-shrink-0 w-9 h-9 bg-sand rounded-xl flex items-center justify-center">
            <?= icon('umbrella', 'w-5 h-5', '#0c2340') ?>
        </div>
        <div>
            <div class="text-white font-bold text-sm leading-tight">Torre Conca</div>
            <div class="text-white/40 text-xs">Admin Panel</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <?php foreach ($navItems as $item):
            $isActive = $currentPage === $item['file'];
            $cls = $isActive
                ? 'bg-sand/20 text-sand border-r-2 border-sand'
                : 'text-white/70 hover:bg-white/5 hover:text-white';
        ?>
        <a href="/admin/<?= $item['file'] ?>.php"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors duration-150 <?= $cls ?>">
            <?= icon($item['icon'], 'w-5 h-5 flex-shrink-0') ?>
            <span class="text-sm font-medium"><?= $item['label'] ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- User / Logout -->
    <div class="px-3 py-4 border-t border-white/10">
        <div class="flex items-center gap-3 px-4 py-2 mb-1">
            <div class="w-8 h-8 bg-ocean-light rounded-full flex items-center justify-center flex-shrink-0">
                <?= icon('user', 'w-4 h-4', '#d4a847') ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-white text-sm font-medium truncate"><?= h($_SESSION['admin_nome'] ?? 'Admin') ?></div>
                <div class="text-white/40 text-xs">Amministratore</div>
            </div>
        </div>
        <a href="/admin/logout.php"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-white/60 hover:bg-red-500/20 hover:text-red-400 transition-colors duration-150 w-full">
            <?= icon('logout', 'w-4 h-4 flex-shrink-0') ?>
            <span class="text-sm font-medium">Esci</span>
        </a>
    </div>
</aside>

<!-- ── CONTENT OFFSET DESKTOP ───────────────────────────────── -->
<div class="hidden lg:block w-60 flex-shrink-0"></div>

<!-- ── TOP BAR MOBILE ────────────────────────────────────────── -->
<header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-ocean-dark shadow-md flex items-center justify-between px-4 py-3">
    <div class="flex items-center gap-2">
        <div class="w-7 h-7 bg-sand rounded-lg flex items-center justify-center">
            <?= icon('umbrella', 'w-4 h-4', '#0c2340') ?>
        </div>
        <span class="text-white font-bold text-sm">Torre Conca</span>
    </div>
    <span class="text-white/60 text-sm"><?= h($_SESSION['admin_nome'] ?? '') ?></span>
</header>

<!-- ── SPACER MOBILE TOP ──────────────────────────────────────── -->
<div class="lg:hidden h-14"></div>

<!-- ── BOTTOM NAV MOBILE ──────────────────────────────────────── -->
<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-ocean-dark border-t border-white/10 flex items-center safe-area-pb">
    <?php foreach ($navItems as $item):
        $isActive = $currentPage === $item['file'];
        $cls = $isActive ? 'text-sand' : 'text-white/50';
    ?>
    <a href="/admin/<?= $item['file'] ?>.php"
       class="flex-1 flex flex-col items-center py-2 px-1 gap-0.5 transition-colors <?= $cls ?>">
        <?= icon($item['icon'], 'w-5 h-5') ?>
        <span class="text-[10px] font-medium leading-tight text-center"><?= $item['label'] ?></span>
    </a>
    <?php endforeach; ?>
    <a href="/admin/logout.php"
       class="flex-shrink-0 flex flex-col items-center py-2 px-3 gap-0.5 text-white/40 hover:text-red-400 transition-colors">
        <?= icon('logout', 'w-5 h-5') ?>
        <span class="text-[10px] font-medium">Esci</span>
    </a>
</nav>

<!-- ── SPACER MOBILE BOTTOM ──────────────────────────────────── -->
<div class="lg:hidden h-16"></div>

<!-- ── TOAST CONTAINER ───────────────────────────────────────── -->
<div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

<script>
function showToast(msg, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-600 text-white',
        error:   'bg-red-600 text-white',
        info:    'bg-ocean text-white',
        warning: 'bg-amber-500 text-white',
    };
    toast.className = `pointer-events-auto flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-sm font-medium transition-all duration-300 ${colors[type] || colors.info}`;
    toast.innerHTML = msg;
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(1rem)';
    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    });
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(1rem)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}
</script>
