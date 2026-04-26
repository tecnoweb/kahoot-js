<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/seo.php';
require_once 'includes/auth.php';

setSecurityHeaders();
requireLogin();

$user = getCurrentUser();
$pdo  = getDB();

// Paginazione
$page    = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM scans WHERE user_id = ?');
$countStmt->execute([$user['id']]);
$total     = (int) $countStmt->fetchColumn();
$totalPages = (int) ceil($total / $perPage);

$stmt = $pdo->prepare('SELECT * FROM scans WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
$stmt->bindValue(1, $user['id'], PDO::PARAM_INT);
$stmt->bindValue(2, $perPage, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$scans = $stmt->fetchAll();
$csrf  = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <?php renderSEO([
        'title'       => 'Dashboard — Soffitta.ai',
        'description' => 'Le tue valutazioni e perizie AI.',
    ]); renderPerformanceHead(); ?>
    <style>body { font-family: 'Inter', sans-serif; } h1,h2 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen">

<nav class="bg-white border-b border-stone-200 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2">
            <span class="text-2xl">🏛️</span>
            <span class="text-xl font-bold" style="font-family:'Playfair Display',serif">Soffitta.ai</span>
        </a>
        <div class="flex items-center gap-4 text-sm">
            <span class="text-stone-500 hidden sm:inline">Ciao, <?= htmlspecialchars($user['name'] ?? $user['email']) ?></span>
            <form action="/api/auth.php" method="POST" class="inline">
                <input type="hidden" name="action" value="logout">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <button type="submit" class="text-stone-400 hover:text-red-500 transition text-sm">Esci</button>
            </form>
        </div>
    </div>
</nav>

<main class="max-w-5xl mx-auto px-4 py-10">
    <!-- Intestazione dashboard -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold">Le tue valutazioni</h1>
            <p class="text-stone-400 text-sm mt-1">
                <?= $total ?> oggett<?= $total === 1 ? 'o analizzato' : 'i analizzati' ?>
                · Piano <?= htmlspecialchars(ucfirst($user['plan'])) ?>
            </p>
        </div>
        <a href="/" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-3 rounded-xl transition text-sm self-start">
            + Nuova valutazione
        </a>
    </div>

    <?php if (!$scans): ?>
    <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center">
        <div class="text-5xl mb-4">📦</div>
        <h2 class="text-xl font-bold mb-2">Nessuna valutazione ancora</h2>
        <p class="text-stone-400 mb-6">Carica la foto di un oggetto per scoprire il suo valore.</p>
        <a href="/" class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-8 py-3 rounded-xl transition">
            Inizia ora
        </a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($scans as $scan): ?>
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden hover:shadow-md transition group">
            <?php if ($scan['image_path']): ?>
            <div class="overflow-hidden h-40 bg-stone-100">
                <img src="/<?= htmlspecialchars($scan['image_path']) ?>"
                     alt="<?= htmlspecialchars($scan['object_name'] ?? 'Oggetto') ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     loading="lazy">
            </div>
            <?php else: ?>
            <div class="h-40 bg-stone-100 flex items-center justify-center text-4xl">📦</div>
            <?php endif; ?>
            <div class="p-4">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h3 class="font-bold text-stone-800 text-sm leading-snug">
                        <?= htmlspecialchars($scan['object_name'] ?? 'Oggetto non identificato') ?>
                    </h3>
                    <?php if ($scan['paid']): ?>
                    <span class="shrink-0 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Perizia</span>
                    <?php else: ?>
                    <span class="shrink-0 text-xs bg-stone-100 text-stone-400 px-2 py-0.5 rounded-full">Gratuita</span>
                    <?php endif; ?>
                </div>
                <div class="text-amber-600 font-bold text-sm mb-2">
                    €<?= number_format($scan['estimated_min'], 0, ',', '.') ?>–<?= number_format($scan['estimated_max'], 0, ',', '.') ?>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-stone-300"><?= date('d/m/Y', strtotime($scan['created_at'])) ?></span>
                    <a href="/result.php?scan_id=<?= $scan['id'] ?>"
                       class="text-xs text-amber-600 hover:underline font-medium">Vedi →</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginazione -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-2 mt-8">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?p=<?= $i ?>"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
                  <?= $i === $page ? 'bg-amber-500 text-white' : 'bg-white border border-stone-200 text-stone-600 hover:border-amber-400' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</main>

<footer class="bg-white border-t border-stone-200 py-6 mt-12">
    <div class="max-w-5xl mx-auto px-4 text-center text-xs text-stone-300">
        © <?= date('Y') ?> Soffitta.ai
    </div>
</footer>
</body>
</html>
