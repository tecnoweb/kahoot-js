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
$csrf    = generateCsrfToken();
$pdo     = getDB();

$success = '';
$error   = '';

// Salvataggio profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token di sicurezza non valido.';
    } else {
        $validTypes = ['antiquario','casa_aste','gioielleria','galleria','collezionista','altro'];
        $type = in_array($_POST['company_type'] ?? '', $validTypes, true) ? $_POST['company_type'] : 'altro';

        $rawCats   = $_POST['categories'] ?? [];
        $cleanCats = array_values(array_intersect((array)$rawCats,
            ['quadri', 'ceramiche', 'gioielli', 'mobili', 'argenteria', 'altro']));

        try {
            $stmt = $pdo->prepare("
                UPDATE companies SET
                    company_name   = ?, contact_name = ?, phone = ?,
                    city           = ?, province = ?, website = ?,
                    company_type   = ?, description = ?,
                    categories_interest = ?,
                    budget_min     = ?, budget_max = ?,
                    era_interest   = ?
                WHERE id = ?
            ");
            $stmt->execute([
                sanitize($_POST['company_name'] ?? ''),
                sanitize($_POST['contact_name'] ?? ''),
                sanitize($_POST['phone'] ?? ''),
                sanitize($_POST['city'] ?? ''),
                sanitize($_POST['province'] ?? ''),
                sanitize($_POST['website'] ?? ''),
                $type,
                sanitize($_POST['description'] ?? ''),
                json_encode($cleanCats),
                max(0, (int) ($_POST['budget_min'] ?? 0)),
                max(0, (int) ($_POST['budget_max'] ?? 0)),
                sanitize($_POST['era_interest'] ?? ''),
                $company['id'],
            ]);
            $success = 'Profilo aggiornato con successo.';
            $company = getCurrentCompany(); // Ricarica
        } catch (PDOException $e) {
            error_log('Company profile update: ' . $e->getMessage());
            $error = 'Errore durante il salvataggio.';
        }
    }
}

$currentCats = json_decode($company['categories_interest'] ?? '[]', true) ?: [];
$categories  = ['quadri', 'ceramiche', 'gioielli', 'mobili', 'argenteria', 'altro'];
$catIcons    = ['quadri' => '🖼️', 'ceramiche' => '🏺', 'gioielli' => '💍',
                 'mobili' => '🪑', 'argenteria' => '🥄', 'altro' => '📦'];
$companyTypes = [
    'antiquario'    => 'Antiquario / Rigattiere',
    'casa_aste'     => 'Casa d\'aste',
    'gioielleria'   => 'Gioielleria / Orologeria',
    'galleria'      => 'Galleria d\'arte',
    'collezionista' => 'Collezionista privato',
    'altro'         => 'Altro',
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <?php renderPerformanceHead(); ?>
    <title>Profilo azienda — Soffitta.ai</title>
    <style>body { font-family: 'Inter', sans-serif; } h1,h2 { font-family: 'Playfair Display', serif; }</style>
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
                ← Dashboard
            </a>
        </div>
        <form action="/api/company-auth.php" method="POST" class="inline">
            <input type="hidden" name="action" value="logout">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit" class="text-stone-400 hover:text-red-500 text-sm transition">Esci</button>
        </form>
    </div>
</nav>

<main class="max-w-2xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-2">Profilo azienda</h1>
    <p class="text-stone-400 text-sm mb-8">
        Aggiorna le tue preferenze per ricevere abbinamenti più precisi
    </p>

    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 mb-6 flex items-center gap-2">
        <span>✓</span> <?= htmlspecialchars($success) ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="space-y-8">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="save_profile" value="1">

        <!-- Dati azienda -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="text-lg font-bold mb-5">Dati azienda</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-stone-700 mb-1">Ragione sociale *</label>
                    <input type="text" name="company_name" required
                           value="<?= htmlspecialchars($company['company_name']) ?>"
                           class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Tipo azienda</label>
                    <select name="company_type"
                            class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 bg-white">
                        <?php foreach ($companyTypes as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $company['company_type'] === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Nome referente</label>
                    <input type="text" name="contact_name"
                           value="<?= htmlspecialchars($company['contact_name'] ?? '') ?>"
                           class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Città</label>
                    <input type="text" name="city"
                           value="<?= htmlspecialchars($company['city'] ?? '') ?>"
                           class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Provincia</label>
                    <input type="text" name="province" maxlength="3"
                           value="<?= htmlspecialchars($company['province'] ?? '') ?>"
                           class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Telefono</label>
                    <input type="tel" name="phone"
                           value="<?= htmlspecialchars($company['phone'] ?? '') ?>"
                           class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Sito web</label>
                    <input type="url" name="website"
                           value="<?= htmlspecialchars($company['website'] ?? '') ?>"
                           class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-stone-700 mb-1">Descrizione attività</label>
                    <textarea name="description" rows="3" maxlength="500"
                              class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 resize-none"
                              ><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Interessi acquisto -->
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="text-lg font-bold mb-5">Interessi di acquisto</h2>

            <div class="mb-5">
                <label class="block text-sm font-medium text-stone-700 mb-2">Categorie *</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <?php foreach ($categories as $cat): ?>
                    <label class="flex items-center gap-2 bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 cursor-pointer hover:border-amber-400 hover:bg-amber-50 transition has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50">
                        <input type="checkbox" name="categories[]" value="<?= $cat ?>"
                               class="accent-amber-500"
                               <?= in_array($cat, $currentCats, true) ? 'checked' : '' ?>>
                        <span class="text-sm capitalize"><?= $catIcons[$cat] ?> <?= ucfirst($cat) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Budget minimo (€)</label>
                    <input type="number" name="budget_min" min="0" step="50"
                           value="<?= (int)$company['budget_min'] ?>"
                           class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
                    <p class="text-xs text-stone-300 mt-1">0 = nessun minimo</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Budget massimo (€)</label>
                    <input type="number" name="budget_max" min="0" step="50"
                           value="<?= (int)$company['budget_max'] ?>"
                           class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400">
                    <p class="text-xs text-stone-300 mt-1">0 = nessun limite</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Epoche di interesse</label>
                <input type="text" name="era_interest"
                       value="<?= htmlspecialchars($company['era_interest'] ?? '') ?>"
                       class="w-full border border-stone-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400"
                       placeholder="XIX secolo, Art Déco, anni '50…">
                <p class="text-xs text-stone-300 mt-1">Separa le epoche con virgola</p>
            </div>
        </div>

        <button type="submit"
                class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-4 rounded-xl transition">
            Salva modifiche
        </button>
    </form>

    <p class="text-center text-xs text-stone-300 mt-6">
        Email account: <?= htmlspecialchars($company['email']) ?> ·
        <a href="/company/dashboard.php" class="hover:text-amber-600 transition">Torna alla dashboard</a>
    </p>
</main>
</body>
</html>
