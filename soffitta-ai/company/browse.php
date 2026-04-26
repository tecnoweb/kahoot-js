<?php
// Sfoglia tutti gli oggetti pubblici disponibili all'acquisto
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/buyer.php';
require_once '../includes/seo.php';

setSecurityHeaders();
requireCompanyLogin();

$company = getCurrentCompany();
$pdo     = getDB();
$csrf    = generateCsrfToken();

$catFilter = preg_replace('/[^a-z]/', '', strtolower($_GET['cat'] ?? ''));
$search    = sanitize($_GET['q'] ?? '');
$page      = max(1, (int) ($_GET['p'] ?? 1));
$perPage   = 16;
$offset    = ($page - 1) * $perPage;

$sql    = 'SELECT s.*, bm.id AS match_id, bm.status AS match_status
           FROM scans s
           LEFT JOIN buyer_matches bm ON bm.scan_id = s.id AND bm.company_id = ?
           WHERE 1=1';
$params = [$company['id']];

if ($catFilter) {
    $sql .= ' AND s.category = ?';
    $params[] = $catFilter;
}
if ($search) {
    $sql .= ' AND (s.object_name LIKE ? OR s.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

// Count
$countSql = str_replace('SELECT s.*, bm.id AS match_id, bm.status AS match_status', 'SELECT COUNT(*)', $sql);
$cStmt = $pdo->prepare($countSql);
$cStmt->execute($params);
$total = (int) $cStmt->fetchColumn();
$totalPages = (int) ceil($total / $perPage);

$sql .= ' ORDER BY s.created_at DESC LIMIT ? OFFSET ?';
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $i => $v) {
    $stmt->bindValue($i + 1, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$scans = $stmt->fetchAll();

$catIcons = ['quadri' => '🖼️', 'ceramiche' => '🏺', 'gioielli' => '💍',
              'mobili' => '🪑', 'argenteria' => '🥄', 'altro' => '📦'];
$validCats = ['quadri', 'ceramiche', 'gioielli', 'mobili', 'argenteria', 'altro'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <?php renderPerformanceHead(); ?>
    <title>Sfoglia oggetti — Soffitta.ai Acquirenti</title>
    <style>body { font-family: 'Inter', sans-serif; } h1 { font-family: 'Playfair Display', serif; }</style>
</head>
<body class="bg-stone-50 min-h-screen">

<nav class="bg-white border-b border-stone-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="/" class="flex items-center gap-2">
                <span class="text-xl">🏛️</span>
                <span class="font-bold" style="font-family:'Playfair Display',serif">Soffitta.ai</span>
            </a>
            <a href="/company/dashboard.php" class="text-sm text-stone-400 hover:text-amber-600 transition hidden sm:inline">
                ← I miei abbinamenti
            </a>
        </div>
        <form action="/api/company-auth.php" method="POST" class="inline">
            <input type="hidden" name="action" value="logout">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit" class="text-stone-400 hover:text-red-500 text-sm transition">Esci</button>
        </form>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h1 class="text-3xl font-bold">Sfoglia tutti gli oggetti</h1>
        <span class="text-stone-400 text-sm"><?= $total ?> oggetti disponibili</span>
    </div>

    <!-- Ricerca e filtri -->
    <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-6">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
               placeholder="Cerca per nome o descrizione…"
               class="flex-1 border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
        <select name="cat"
                class="border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 bg-white">
            <option value="">Tutte le categorie</option>
            <?php foreach ($validCats as $cat): ?>
            <option value="<?= $cat ?>" <?= $catFilter === $cat ? 'selected' : '' ?>>
                <?= $catIcons[$cat] ?> <?= ucfirst($cat) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-white font-medium px-6 py-2.5 rounded-xl transition">
            Cerca
        </button>
        <?php if ($search || $catFilter): ?>
        <a href="/company/browse.php"
           class="bg-stone-100 hover:bg-stone-200 text-stone-600 font-medium px-4 py-2.5 rounded-xl transition text-sm flex items-center">
            ✕ Reset
        </a>
        <?php endif; ?>
    </form>

    <?php if (!$scans): ?>
    <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center">
        <div class="text-5xl mb-4">🔍</div>
        <p class="text-stone-500">Nessun oggetto trovato per questa ricerca.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($scans as $s):
            $hasMatch   = !empty($s['match_id']);
            $matchStatus = $s['match_status'] ?? null;
        ?>
        <div class="bg-white rounded-xl border border-stone-200 overflow-hidden hover:shadow-md transition group">
            <div class="relative overflow-hidden h-36 bg-stone-100">
                <?php if ($s['image_path']): ?>
                <img src="/<?= htmlspecialchars($s['image_path']) ?>"
                     alt="<?= htmlspecialchars($s['object_name'] ?? '') ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     loading="lazy">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-4xl">
                    <?= $catIcons[$s['category'] ?? 'altro'] ?? '📦' ?>
                </div>
                <?php endif; ?>
                <?php if ($matchStatus === 'offer_made'): ?>
                <div class="absolute top-1.5 right-1.5 bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full">Offerta</div>
                <?php elseif ($matchStatus === 'interested'): ?>
                <div class="absolute top-1.5 right-1.5 bg-green-500 text-white text-xs px-2 py-0.5 rounded-full">✓</div>
                <?php endif; ?>
            </div>
            <div class="p-3">
                <p class="text-xs font-semibold text-stone-800 leading-snug mb-1 line-clamp-2">
                    <?= htmlspecialchars($s['object_name'] ?? 'Oggetto') ?>
                </p>
                <?php if ($s['era']): ?>
                <p class="text-xs text-stone-300 mb-1"><?= htmlspecialchars($s['era']) ?></p>
                <?php endif; ?>
                <p class="text-sm font-bold text-amber-600 mb-2">
                    €<?= number_format($s['estimated_min'], 0, ',', '.') ?>–<?= number_format($s['estimated_max'], 0, ',', '.') ?>
                </p>
                <?php if ($company['verified'] && !$hasMatch): ?>
                <button onclick="quickInterest(<?= $s['id'] ?>, this)"
                        class="w-full bg-stone-50 hover:bg-amber-50 border border-stone-200 hover:border-amber-400 text-stone-600 hover:text-amber-700 text-xs py-1.5 rounded-lg transition">
                    + Aggiungi interesse
                </button>
                <?php elseif ($hasMatch): ?>
                <a href="/company/dashboard.php"
                   class="block w-full text-center text-xs text-stone-400 hover:text-amber-600 py-1 transition">
                    Vedi in dashboard →
                </a>
                <?php else: ?>
                <p class="text-xs text-stone-300 text-center">Verifica in attesa</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginazione -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-2 mt-8 flex-wrap">
        <?php
        $qs = http_build_query(['q' => $search, 'cat' => $catFilter]);
        for ($i = 1; $i <= min($totalPages, 20); $i++): ?>
        <a href="?<?= $qs ?>&p=<?= $i ?>"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
                  <?= $i === $page ? 'bg-amber-500 text-white' : 'bg-white border border-stone-200 text-stone-600 hover:border-amber-400' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</main>

<script>
async function quickInterest(scanId, btn) {
    btn.disabled = true;
    btn.textContent = '…';
    try {
        const body = new FormData();
        body.append('action', 'quick_interest');
        body.append('scan_id', scanId);
        body.append('csrf_token', '<?= htmlspecialchars($csrf, ENT_QUOTES) ?>');
        const res  = await fetch('/api/buyer-match.php', { method: 'POST', body });
        const data = await res.json();
        if (data.success) {
            btn.textContent = '✓ Aggiunto';
            btn.className = 'w-full bg-green-50 border border-green-200 text-green-700 text-xs py-1.5 rounded-lg';
        } else {
            btn.disabled = false;
            btn.textContent = '+ Aggiungi interesse';
        }
    } catch {
        btn.disabled = false;
        btn.textContent = '+ Aggiungi interesse';
    }
}
</script>
</body>
</html>
