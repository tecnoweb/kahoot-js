<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';
require_once '../includes/seo.php';

setSecurityHeaders();
requireAdmin();

$pdo = getDB();

// Stats aggregate
$stats = [];
$statsQueries = [
    'total_users'  => 'SELECT COUNT(*) FROM users',
    'total_scans'  => 'SELECT COUNT(*) FROM scans',
    'paid_scans'   => 'SELECT COUNT(*) FROM scans WHERE paid = 1',
    'revenue'      => 'SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = \'paid\'',
    'today_scans'  => 'SELECT COUNT(*) FROM scans WHERE DATE(created_at) = CURDATE()',
    'today_revenue'=> "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid' AND DATE(created_at) = CURDATE()",
];
foreach ($statsQueries as $key => $query) {
    $stats[$key] = $pdo->query($query)->fetchColumn();
}

// Ultimi scansioni
$recentScans = $pdo->query(
    'SELECT s.*, u.email FROM scans s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC LIMIT 20'
)->fetchAll();

// Ultimi pagamenti
$recentPayments = $pdo->query(
    'SELECT p.*, s.object_name, u.email FROM payments p
     JOIN scans s ON p.scan_id = s.id LEFT JOIN users u ON p.user_id = u.id
     ORDER BY p.created_at DESC LIMIT 10'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <?php renderPerformanceHead(); ?>
    <title>Admin — Soffitta.ai</title>
    <style>body { font-family: 'Inter', sans-serif; } h1,h2 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-100 min-h-screen">

<nav class="bg-stone-900 text-white px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span>🏛️</span>
        <span class="font-bold">Soffitta.ai Admin</span>
    </div>
    <a href="/" class="text-stone-400 hover:text-white text-sm transition">← Sito</a>
</nav>

<main class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Dashboard Admin</h1>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
        <?php
        $statCards = [
            ['Utenti totali',   $stats['total_users'],   'bg-white'],
            ['Scansioni totali', $stats['total_scans'],  'bg-white'],
            ['Perizie pagate',  $stats['paid_scans'],    'bg-white'],
            ['Ricavi totali',   '€' . number_format($stats['revenue'], 2, ',', '.'), 'bg-amber-50 border-amber-200'],
            ['Scansioni oggi',  $stats['today_scans'],   'bg-white'],
            ['Ricavi oggi',     '€' . number_format($stats['today_revenue'], 2, ',', '.'), 'bg-green-50 border-green-200'],
        ];
        foreach ($statCards as [$label, $value, $bg]): ?>
        <div class="<?= $bg ?> border border-stone-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-stone-800"><?= $value ?></div>
            <div class="text-xs text-stone-400 mt-1"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ultime scansioni -->
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-100">
                <h2 class="font-bold text-lg">Ultime scansioni</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 text-xs text-stone-400 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Oggetto</th>
                            <th class="px-4 py-2 text-left">Utente</th>
                            <th class="px-4 py-2 text-right">Valore</th>
                            <th class="px-4 py-2 text-center">Pagato</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <?php foreach ($recentScans as $scan): ?>
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-2 max-w-[180px] truncate" title="<?= htmlspecialchars($scan['object_name'] ?? '') ?>">
                                <a href="/result.php?scan_id=<?= $scan['id'] ?>" class="text-amber-600 hover:underline">
                                    <?= htmlspecialchars(mb_substr($scan['object_name'] ?? 'N/A', 0, 25)) ?>
                                </a>
                            </td>
                            <td class="px-4 py-2 text-stone-400 text-xs"><?= htmlspecialchars($scan['email'] ?? 'Anonimo') ?></td>
                            <td class="px-4 py-2 text-right font-medium">
                                €<?= number_format($scan['estimated_min'], 0, ',', '.') ?>–<?= number_format($scan['estimated_max'], 0, ',', '.') ?>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <?= $scan['paid'] ? '<span class="text-green-500 font-bold">✓</span>' : '<span class="text-stone-300">–</span>' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ultimi pagamenti -->
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-100">
                <h2 class="font-bold text-lg">Ultimi pagamenti</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 text-xs text-stone-400 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Oggetto</th>
                            <th class="px-4 py-2 text-left">Utente</th>
                            <th class="px-4 py-2 text-right">Importo</th>
                            <th class="px-4 py-2 text-center">Stato</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <?php foreach ($recentPayments as $pay): ?>
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-2 text-xs text-stone-600 max-w-[160px] truncate">
                                <?= htmlspecialchars(mb_substr($pay['object_name'] ?? 'N/A', 0, 22)) ?>
                            </td>
                            <td class="px-4 py-2 text-xs text-stone-400"><?= htmlspecialchars($pay['email'] ?? 'Anonimo') ?></td>
                            <td class="px-4 py-2 text-right font-medium text-amber-600">
                                €<?= number_format($pay['amount'], 2, ',', '.') ?>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    <?= $pay['status'] === 'paid' ? 'bg-green-100 text-green-700' : ($pay['status'] === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') ?>">
                                    <?= htmlspecialchars(ucfirst($pay['status'])) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
</body>
</html>
