<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';
require_once '../includes/buyer.php';
require_once '../includes/seo.php';

setSecurityHeaders();
requireAdmin();

$pdo  = getDB();
$csrf = generateCsrfToken();

// Verifica/disabilita aziende
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $adminAction = $_POST['admin_action'] ?? '';
    $companyId   = (int) ($_POST['company_id'] ?? 0);
    if ($companyId) {
        if ($adminAction === 'verify_company') {
            $pdo->prepare('UPDATE companies SET verified = 1 WHERE id = ?')->execute([$companyId]);
        } elseif ($adminAction === 'disable_company') {
            $pdo->prepare('UPDATE companies SET active = 0 WHERE id = ?')->execute([$companyId]);
        } elseif ($adminAction === 'enable_company') {
            $pdo->prepare('UPDATE companies SET active = 1 WHERE id = ?')->execute([$companyId]);
        }
    }
}

// Stats aggregate
$stats = [];
$statsQueries = [
    'total_users'     => 'SELECT COUNT(*) FROM users',
    'total_scans'     => 'SELECT COUNT(*) FROM scans',
    'paid_scans'      => 'SELECT COUNT(*) FROM scans WHERE paid = 1',
    'revenue'         => 'SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = \'paid\'',
    'today_scans'     => 'SELECT COUNT(*) FROM scans WHERE DATE(created_at) = CURDATE()',
    'today_revenue'   => "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid' AND DATE(created_at) = CURDATE()",
    'total_companies' => 'SELECT COUNT(*) FROM companies',
    'pending_verify'  => 'SELECT COUNT(*) FROM companies WHERE verified = 0 AND active = 1',
    'total_matches'   => 'SELECT COUNT(*) FROM buyer_matches',
    'offers_made'     => "SELECT COUNT(*) FROM buyer_matches WHERE status = 'offer_made'",
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

// Aziende in attesa di verifica
$pendingCompanies = $pdo->query(
    'SELECT * FROM companies WHERE verified = 0 AND active = 1 ORDER BY created_at DESC'
)->fetchAll();

// Tutte le aziende
$allCompanies = $pdo->query(
    'SELECT c.*,
            (SELECT COUNT(*) FROM buyer_matches WHERE company_id = c.id) AS match_count,
            (SELECT COUNT(*) FROM buyer_matches WHERE company_id = c.id AND status = \'offer_made\') AS offer_count
     FROM companies c ORDER BY c.created_at DESC LIMIT 50'
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
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-4">
        <?php
        $statCards = [
            ['Utenti',          $stats['total_users'],   'bg-white'],
            ['Scansioni',       $stats['total_scans'],   'bg-white'],
            ['Perizie pagate',  $stats['paid_scans'],    'bg-white'],
            ['Ricavi totali',   '€' . number_format($stats['revenue'], 2, ',', '.'), 'bg-amber-50 border-amber-200'],
            ['Ricavi oggi',     '€' . number_format($stats['today_revenue'], 2, ',', '.'), 'bg-green-50 border-green-200'],
        ];
        foreach ($statCards as [$label, $value, $bg]): ?>
        <div class="<?= $bg ?> border border-stone-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-stone-800"><?= $value ?></div>
            <div class="text-xs text-stone-400 mt-1"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <!-- Stats acquirenti -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <?php
        $buyerCards = [
            ['Aziende registrate', $stats['total_companies'],                                   'bg-white'],
            ['In attesa verifica', $stats['pending_verify'],  $stats['pending_verify'] > 0 ? 'bg-amber-50 border-amber-300' : 'bg-white'],
            ['Match generati',     $stats['total_matches'],   'bg-white'],
            ['Offerte inviate',    $stats['offers_made'],     'bg-white'],
        ];
        foreach ($buyerCards as [$label, $value, $bg]): ?>
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

    <!-- Aziende in attesa di verifica -->
    <?php if ($pendingCompanies): ?>
    <div class="mt-8 bg-amber-50 border border-amber-200 rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-amber-200 flex items-center gap-2">
            <h2 class="font-bold text-lg text-amber-800">Aziende in attesa di verifica</h2>
            <span class="bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                <?= count($pendingCompanies) ?>
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-amber-100/50 text-xs text-amber-700 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Azienda</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Città</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Registrata</th>
                        <th class="px-4 py-2 text-center">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-100">
                    <?php foreach ($pendingCompanies as $co): ?>
                    <tr class="hover:bg-amber-50/70">
                        <td class="px-4 py-3 font-medium text-stone-800">
                            <?= htmlspecialchars($co['company_name']) ?>
                            <?php if ($co['vat_number']): ?>
                            <span class="text-xs text-stone-400 block">P.IVA: <?= htmlspecialchars($co['vat_number']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-stone-500 capitalize text-xs">
                            <?= htmlspecialchars(str_replace('_', ' ', $co['company_type'])) ?>
                        </td>
                        <td class="px-4 py-3 text-stone-400 text-xs">
                            <?= htmlspecialchars($co['city'] ?? '—') ?>
                            <?= $co['province'] ? '(' . htmlspecialchars($co['province']) . ')' : '' ?>
                        </td>
                        <td class="px-4 py-3 text-stone-400 text-xs"><?= htmlspecialchars($co['email']) ?></td>
                        <td class="px-4 py-3 text-stone-300 text-xs">
                            <?= date('d/m/Y', strtotime($co['created_at'])) ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="company_id" value="<?= $co['id'] ?>">
                                <input type="hidden" name="admin_action" value="verify_company">
                                <button type="submit"
                                        class="bg-green-500 hover:bg-green-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition">
                                    ✓ Verifica
                                </button>
                            </form>
                            <form method="POST" class="inline ml-1">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="company_id" value="<?= $co['id'] ?>">
                                <input type="hidden" name="admin_action" value="disable_company">
                                <button type="submit"
                                        class="bg-stone-200 hover:bg-red-100 text-stone-600 hover:text-red-700 text-xs font-medium px-3 py-1.5 rounded-lg transition">
                                    ✕ Rifiuta
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tutte le aziende -->
    <div class="mt-6 bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-bold text-lg">Aziende registrate</h2>
            <span class="text-stone-400 text-sm"><?= count($allCompanies) ?> aziende</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs text-stone-400 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Azienda</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Città</th>
                        <th class="px-4 py-2 text-center">Match</th>
                        <th class="px-4 py-2 text-center">Offerte</th>
                        <th class="px-4 py-2 text-center">Stato</th>
                        <th class="px-4 py-2 text-center">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php foreach ($allCompanies as $co): ?>
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-stone-800"><?= htmlspecialchars($co['company_name']) ?></div>
                            <div class="text-xs text-stone-400"><?= htmlspecialchars($co['email']) ?></div>
                        </td>
                        <td class="px-4 py-3 text-stone-400 text-xs capitalize">
                            <?= htmlspecialchars(str_replace('_', ' ', $co['company_type'])) ?>
                        </td>
                        <td class="px-4 py-3 text-stone-400 text-xs">
                            <?= htmlspecialchars($co['city'] ?? '—') ?>
                        </td>
                        <td class="px-4 py-3 text-center font-medium"><?= $co['match_count'] ?></td>
                        <td class="px-4 py-3 text-center font-medium text-amber-600"><?= $co['offer_count'] ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if (!$co['active']): ?>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">Disabilitata</span>
                            <?php elseif ($co['verified']): ?>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Verificata</span>
                            <?php else: ?>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">In verifica</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if ($co['active'] && !$co['verified']): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="company_id" value="<?= $co['id'] ?>">
                                <input type="hidden" name="admin_action" value="verify_company">
                                <button type="submit" class="text-xs text-green-600 hover:underline">Verifica</button>
                            </form>
                            <?php endif; ?>
                            <?php if ($co['active']): ?>
                            <form method="POST" class="inline ml-2">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="company_id" value="<?= $co['id'] ?>">
                                <input type="hidden" name="admin_action" value="disable_company">
                                <button type="submit" class="text-xs text-red-400 hover:underline">Disabilita</button>
                            </form>
                            <?php else: ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="company_id" value="<?= $co['id'] ?>">
                                <input type="hidden" name="admin_action" value="enable_company">
                                <button type="submit" class="text-xs text-green-600 hover:underline">Riabilita</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$allCompanies): ?>
                    <tr><td colspan="7" class="px-4 py-8 text-center text-stone-300">Nessuna azienda registrata</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
