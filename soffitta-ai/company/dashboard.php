<?php
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

// Statistiche azienda
$stats = [];
$statsQ = [
    'total'        => 'SELECT COUNT(*) FROM buyer_matches WHERE company_id = ?',
    'interested'   => "SELECT COUNT(*) FROM buyer_matches WHERE company_id = ? AND status = 'interested'",
    'offers'       => "SELECT COUNT(*) FROM buyer_matches WHERE company_id = ? AND status = 'offer_made'",
    'new_today'    => "SELECT COUNT(*) FROM buyer_matches bm JOIN scans s ON bm.scan_id = s.id WHERE bm.company_id = ? AND DATE(s.created_at) = CURDATE()",
];
foreach ($statsQ as $key => $q) {
    $stmt = $pdo->prepare($q);
    $stmt->execute([$company['id']]);
    $stats[$key] = (int) $stmt->fetchColumn();
}

// Paginazione
$page    = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;
$total   = countMatchedScansForCompany((int) $company['id']);
$totalPages = (int) ceil($total / $perPage);

// Filtro stato
$filterStatus = $_GET['status'] ?? 'all';
$validStatuses = ['all', 'pending', 'interested', 'not_interested', 'offer_made'];
if (!in_array($filterStatus, $validStatuses, true)) $filterStatus = 'all';

// Query oggetti abbinati con filtro opzionale
$sql = 'SELECT bm.id AS match_id, bm.match_score, bm.status, bm.offer_amount, bm.message,
               s.id AS scan_id, s.object_name, s.image_path, s.estimated_min,
               s.estimated_max, s.category, s.era, s.description, s.confidence_score,
               s.created_at, s.public_slug
        FROM buyer_matches bm
        JOIN scans s ON bm.scan_id = s.id
        WHERE bm.company_id = ?';
$params = [$company['id']];

if ($filterStatus !== 'all') {
    $sql     .= ' AND bm.status = ?';
    $params[] = $filterStatus;
}
$sql .= ' ORDER BY bm.match_score DESC, s.created_at DESC LIMIT ? OFFSET ?';
$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $i => $v) {
    $stmt->bindValue($i + 1, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$matches = $stmt->fetchAll();

$csrf = generateCsrfToken();

$catIcons = ['quadri' => '🖼️', 'ceramiche' => '🏺', 'gioielli' => '💍',
              'mobili' => '🪑', 'argenteria' => '🥄', 'altro' => '📦'];
$statusLabels = [
    'pending'         => ['text' => 'Nuovo',      'class' => 'bg-blue-100 text-blue-700'],
    'interested'      => ['text' => 'Interessato', 'class' => 'bg-green-100 text-green-700'],
    'not_interested'  => ['text' => 'Scartato',    'class' => 'bg-stone-100 text-stone-400'],
    'offer_made'      => ['text' => 'Offerta',     'class' => 'bg-amber-100 text-amber-700'],
    'sold'            => ['text' => 'Venduto',     'class' => 'bg-purple-100 text-purple-700'],
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <?php renderPerformanceHead(); ?>
    <title>Dashboard Acquirenti — <?= htmlspecialchars($company['company_name']) ?> — Soffitta.ai</title>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1,h2,h3 { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-stone-50 min-h-screen">

<!-- Navbar azienda -->
<nav class="bg-white border-b border-stone-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="/" class="flex items-center gap-2">
                <span class="text-xl">🏛️</span>
                <span class="font-bold text-base" style="font-family:'Playfair Display',serif">Soffitta.ai</span>
            </a>
            <span class="text-stone-200 hidden sm:inline">|</span>
            <span class="text-sm text-stone-500 hidden sm:inline">
                🏢 <?= htmlspecialchars($company['company_name']) ?>
                <?php if (!$company['verified']): ?>
                <span class="ml-1 text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">In verifica</span>
                <?php endif; ?>
            </span>
        </div>
        <div class="flex items-center gap-3 text-sm">
            <a href="/company/profile.php" class="text-stone-500 hover:text-amber-600 transition hidden sm:inline">Profilo</a>
            <a href="/company/browse.php" class="text-stone-500 hover:text-amber-600 transition hidden sm:inline">Sfoglia tutto</a>
            <form action="/api/company-auth.php" method="POST" class="inline">
                <input type="hidden" name="action" value="logout">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <button type="submit" class="text-stone-400 hover:text-red-500 transition">Esci</button>
            </form>
        </div>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-4 py-8">

    <!-- Avviso verifica pending -->
    <?php if (!$company['verified']): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start gap-3">
        <span class="text-2xl">⏳</span>
        <div>
            <p class="font-semibold text-amber-800">Account in attesa di verifica</p>
            <p class="text-amber-700 text-sm mt-0.5">
                Il tuo account è in fase di revisione da parte del nostro team. Riceverai una email di conferma entro 24 ore.
                Puoi già vedere gli oggetti abbinati, ma potrai contattare i venditori solo dopo la verifica.
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Header dashboard -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">Oggetti per te</h1>
            <p class="text-stone-400 text-sm mt-1">
                <?= $total ?> oggett<?= $total === 1 ? 'o abbinato' : 'i abbinati' ?> ai tuoi interessi
            </p>
        </div>
        <a href="/company/profile.php"
           class="bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium px-5 py-2.5 rounded-xl transition text-sm self-start">
            Modifica interessi
        </a>
    </div>

    <!-- Statistiche -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <?php
        $statCards = [
            ['Abbinamenti totali', $stats['total'],      'text-stone-700'],
            ['Nuovi oggi',         $stats['new_today'],  'text-blue-600'],
            ['Interessato',        $stats['interested'], 'text-green-600'],
            ['Offerte inviate',    $stats['offers'],     'text-amber-600'],
        ];
        foreach ($statCards as [$label, $value, $color]): ?>
        <div class="bg-white border border-stone-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold <?= $color ?>"><?= $value ?></div>
            <div class="text-xs text-stone-400 mt-1"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filtri stato -->
    <div class="flex gap-2 flex-wrap mb-6">
        <?php
        $filterOptions = [
            'all'            => 'Tutti',
            'pending'        => 'Nuovi',
            'interested'     => 'Interessato',
            'offer_made'     => 'Con offerta',
            'not_interested' => 'Scartati',
        ];
        foreach ($filterOptions as $val => $label): ?>
        <a href="?status=<?= $val ?>"
           class="px-4 py-2 rounded-full text-sm font-medium transition
                  <?= $filterStatus === $val ? 'bg-amber-500 text-white' : 'bg-white border border-stone-200 text-stone-600 hover:border-amber-400' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Griglia oggetti abbinati -->
    <?php if (!$matches): ?>
    <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center">
        <div class="text-5xl mb-4">🔍</div>
        <h2 class="text-xl font-bold mb-2">Nessun oggetto abbinato</h2>
        <p class="text-stone-400 mb-4">
            Aggiorna le tue preferenze di acquisto per ricevere abbinamenti più precisi.
        </p>
        <a href="/company/profile.php" class="inline-block bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-3 rounded-xl transition">
            Aggiorna preferenze
        </a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($matches as $m): ?>
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden hover:shadow-md transition group"
             data-match-id="<?= $m['match_id'] ?>">

            <!-- Immagine -->
            <div class="relative overflow-hidden h-44 bg-stone-100">
                <?php if ($m['image_path']): ?>
                <img src="/<?= htmlspecialchars($m['image_path']) ?>"
                     alt="<?= htmlspecialchars($m['object_name'] ?? 'Oggetto') ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     loading="lazy">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-5xl">
                    <?= $catIcons[$m['category']] ?? '📦' ?>
                </div>
                <?php endif; ?>

                <!-- Badge match score -->
                <div class="absolute top-2 right-2 bg-white/90 backdrop-blur-sm rounded-full px-2.5 py-1 text-xs font-bold
                             <?= $m['match_score'] >= 70 ? 'text-green-700' : ($m['match_score'] >= 40 ? 'text-amber-700' : 'text-stone-600') ?>">
                    <?= $m['match_score'] ?>% match
                </div>

                <!-- Badge stato -->
                <?php $sl = $statusLabels[$m['status']] ?? $statusLabels['pending']; ?>
                <div class="absolute top-2 left-2">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full <?= $sl['class'] ?>">
                        <?= $sl['text'] ?>
                    </span>
                </div>
            </div>

            <div class="p-4">
                <h3 class="font-bold text-stone-800 text-sm leading-snug mb-1">
                    <?= htmlspecialchars($m['object_name'] ?? 'Oggetto non identificato') ?>
                </h3>
                <div class="flex items-center gap-2 mb-2">
                    <?php if ($m['era']): ?>
                    <span class="text-xs text-stone-400"><?= htmlspecialchars($m['era']) ?></span>
                    <span class="text-stone-200">·</span>
                    <?php endif; ?>
                    <span class="text-xs capitalize text-stone-400"><?= htmlspecialchars($m['category'] ?? '') ?></span>
                </div>
                <div class="text-amber-600 font-bold text-base mb-3">
                    €<?= number_format($m['estimated_min'], 0, ',', '.') ?>–<?= number_format($m['estimated_max'], 0, ',', '.') ?>
                </div>

                <?php if ($m['status'] === 'offer_made' && $m['offer_amount']): ?>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-2 mb-3 text-xs">
                    <span class="text-amber-700 font-medium">Tua offerta: €<?= number_format($m['offer_amount'], 2, ',', '.') ?></span>
                    <?php if ($m['message']): ?>
                    <p class="text-stone-400 mt-0.5 line-clamp-2"><?= htmlspecialchars($m['message']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Azioni -->
                <?php if ($company['verified'] && $m['status'] === 'pending'): ?>
                <div class="flex gap-2">
                    <button onclick="setInterest(<?= $m['match_id'] ?>, 'interested')"
                            class="flex-1 bg-green-50 hover:bg-green-100 text-green-700 text-xs font-medium py-2 rounded-lg transition border border-green-200">
                        Interessato
                    </button>
                    <button onclick="openOfferModal(<?= $m['match_id'] ?>, '<?= htmlspecialchars($m['object_name'] ?? '', ENT_QUOTES) ?>')"
                            class="flex-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium py-2 rounded-lg transition">
                        Fai offerta
                    </button>
                    <button onclick="setInterest(<?= $m['match_id'] ?>, 'not_interested')"
                            class="bg-stone-50 hover:bg-stone-100 text-stone-400 text-xs p-2 rounded-lg transition border border-stone-200"
                            title="Non interessato">✕</button>
                </div>
                <?php elseif ($company['verified'] && $m['status'] === 'interested'): ?>
                <button onclick="openOfferModal(<?= $m['match_id'] ?>, '<?= htmlspecialchars($m['object_name'] ?? '', ENT_QUOTES) ?>')"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium py-2 rounded-lg transition">
                    Invia offerta
                </button>
                <?php elseif (!$company['verified']): ?>
                <p class="text-xs text-stone-300 text-center py-1">Disponibile dopo verifica</p>
                <?php else: ?>
                <a href="/perizia/<?= htmlspecialchars($m['public_slug'] ?? $m['scan_id']) ?>"
                   class="block w-full text-center text-amber-600 hover:underline text-xs py-1">
                    Vedi dettaglio perizia →
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginazione -->
    <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-2 mt-8">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?status=<?= $filterStatus ?>&p=<?= $i ?>"
           class="px-4 py-2 rounded-lg text-sm font-medium transition
                  <?= $i === $page ? 'bg-amber-500 text-white' : 'bg-white border border-stone-200 text-stone-600 hover:border-amber-400' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</main>

<!-- Modal offerta -->
<div id="offerModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
        <h3 class="text-xl font-bold mb-1">Invia un'offerta</h3>
        <p class="text-stone-400 text-sm mb-5" id="offerObjectName"></p>

        <div id="offerError" class="hidden bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3 mb-4"></div>

        <form id="offerForm" class="space-y-4">
            <input type="hidden" name="action" value="offer">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="match_id" id="offerMatchId">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Importo offerta (€) *</label>
                <input type="number" name="offer_amount" min="1" step="1" required
                       class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                       placeholder="Es. 250">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Messaggio al venditore</label>
                <textarea name="message" rows="3" maxlength="1000"
                          class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 resize-none"
                          placeholder="Presentati e spiega il tuo interesse per l'oggetto…"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeOfferModal()"
                        class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-medium py-3 rounded-xl transition">
                    Annulla
                </button>
                <button type="submit"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl transition">
                    Invia offerta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const csrf = '<?= htmlspecialchars($csrf, ENT_QUOTES) ?>';

async function setInterest(matchId, status) {
    const card = document.querySelector(`[data-match-id="${matchId}"]`);
    try {
        const body = new FormData();
        body.append('action', status);
        body.append('match_id', matchId);
        body.append('csrf_token', csrf);
        const res  = await fetch('/api/buyer-match.php', { method: 'POST', body });
        const data = await res.json();
        if (data.success && card) {
            card.style.opacity = '0.5';
            setTimeout(() => card.remove(), 300);
        }
    } catch(e) {
        alert('Errore di connessione.');
    }
}

function openOfferModal(matchId, objectName) {
    document.getElementById('offerMatchId').value = matchId;
    document.getElementById('offerObjectName').textContent = objectName;
    document.getElementById('offerModal').classList.remove('hidden');
    document.getElementById('offerError').classList.add('hidden');
    document.querySelector('[name=offer_amount]').value = '';
    document.querySelector('[name=message]').value = '';
}

function closeOfferModal() {
    document.getElementById('offerModal').classList.add('hidden');
}

document.getElementById('offerModal').addEventListener('click', function(e) {
    if (e.target === this) closeOfferModal();
});

document.getElementById('offerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type=submit]');
    const err = document.getElementById('offerError');
    btn.disabled = true;
    btn.textContent = 'Invio…';
    err.classList.add('hidden');

    try {
        const res  = await fetch('/api/buyer-match.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        if (data.success) {
            closeOfferModal();
            const matchId = this.querySelector('[name=match_id]').value;
            const card = document.querySelector(`[data-match-id="${matchId}"]`);
            if (card) {
                card.style.opacity = '0.6';
                setTimeout(() => card.remove(), 400);
            }
        } else {
            err.textContent = data.message || 'Errore.';
            err.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Invia offerta';
        }
    } catch {
        err.textContent = 'Errore di connessione.';
        err.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Invia offerta';
    }
});
</script>
</body>
</html>
