<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Impostazioni';

$successMsg = '';
$errorMsg = '';

// Cambio password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $attuale   = $_POST['password_attuale'] ?? '';
    $nuova     = $_POST['password_nuova'] ?? '';
    $conferma  = $_POST['password_conferma'] ?? '';

    if (!$attuale || !$nuova || !$conferma) {
        $errorMsg = 'Compila tutti i campi.';
    } elseif ($nuova !== $conferma) {
        $errorMsg = 'La nuova password e la conferma non coincidono.';
    } elseif (strlen($nuova) < 8) {
        $errorMsg = 'La password deve essere di almeno 8 caratteri.';
    } else {
        $user = fetchOne('SELECT password_hash FROM admin_users WHERE id = ?', [$_SESSION['admin_id']]);
        if (!$user || !password_verify($attuale, $user['password_hash'])) {
            $errorMsg = 'La password attuale non è corretta.';
        } else {
            $hash = password_hash($nuova, PASSWORD_BCRYPT);
            query('UPDATE admin_users SET password_hash = ? WHERE id = ?', [$hash, $_SESSION['admin_id']]);
            $successMsg = 'Password aggiornata con successo.';
        }
    }
}

// Statistiche DB
$statsDB = fetchOne("SELECT
    (SELECT COUNT(*) FROM prenotazioni) AS tot_prenot,
    (SELECT COUNT(*) FROM prenotazioni WHERE stato='confermata') AS tot_conf,
    (SELECT COUNT(*) FROM postazioni) AS tot_post,
    (SELECT COUNT(*) FROM admin_users) AS tot_admin
");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> – Admin Lido Torre Conca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { ocean:{'DEFAULT':'#0c2340','light':'#1a3a60','dark':'#08182d'}, sand:{'DEFAULT':'#d4a847','light':'#f5e6c8','dark':'#a07c2a'} }, fontFamily: { body:['"Inter"','system-ui','sans-serif'] } } } };</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-body bg-gray-50">
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<main class="lg:ml-60 min-h-screen">
<div class="p-4 md:p-6 lg:p-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ocean">Impostazioni</h1>
        <p class="text-gray-500 text-sm mt-0.5">Configurazione del pannello amministrativo</p>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">

        <!-- Profilo admin -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 bg-ocean/10 rounded-xl flex items-center justify-center">
                    <?= icon('user', 'w-4 h-4', '#0c2340') ?>
                </div>
                <h2 class="font-semibold text-ocean">Profilo Amministratore</h2>
            </div>
            <div class="p-5">
                <?php
                $adminInfo = fetchOne('SELECT username, nome, created_at FROM admin_users WHERE id = ?', [$_SESSION['admin_id']]);
                ?>
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-14 h-14 bg-ocean rounded-2xl flex items-center justify-center">
                        <?= icon('user', 'w-7 h-7', '#d4a847') ?>
                    </div>
                    <div>
                        <div class="font-bold text-ocean text-lg"><?= h($adminInfo['nome'] ?? 'Admin') ?></div>
                        <div class="text-gray-500 text-sm">@<?= h($adminInfo['username'] ?? '') ?></div>
                        <div class="text-gray-400 text-xs mt-0.5">Account creato il <?= date('d/m/Y', strtotime($adminInfo['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cambio password -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-50 rounded-xl flex items-center justify-center">
                    <?= icon('lock', 'w-4 h-4', '#d97706') ?>
                </div>
                <h2 class="font-semibold text-ocean">Cambia Password</h2>
            </div>
            <div class="p-5">
                <?php if ($successMsg): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm flex items-center gap-2">
                    <?= icon('check', 'w-4 h-4') ?> <?= h($successMsg) ?>
                </div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm flex items-center gap-2">
                    <?= icon('alert', 'w-4 h-4') ?> <?= h($errorMsg) ?>
                </div>
                <?php endif; ?>
                <form method="POST" action="/admin/impostazioni.php" class="space-y-4">
                    <input type="hidden" name="action" value="change_password">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password attuale *</label>
                        <input type="password" name="password_attuale" required
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nuova password *</label>
                        <input type="password" name="password_nuova" required minlength="8"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                        <p class="text-xs text-gray-400 mt-1">Minimo 8 caratteri</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Conferma nuova password *</label>
                        <input type="password" name="password_conferma" required minlength="8"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-ocean/30">
                    </div>
                    <button type="submit"
                            class="w-full bg-ocean hover:bg-ocean-light text-white py-2.5 rounded-xl font-semibold text-sm transition-colors">
                        Aggiorna Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Statistiche sistema -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 bg-green-50 rounded-xl flex items-center justify-center">
                    <?= icon('trending-up', 'w-4 h-4', '#16a34a') ?>
                </div>
                <h2 class="font-semibold text-ocean">Statistiche Sistema</h2>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="text-3xl font-black text-ocean"><?= (int)$statsDB['tot_prenot'] ?></div>
                        <div class="text-xs text-gray-500 mt-1">Prenotazioni totali</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-3xl font-black text-green-600"><?= (int)$statsDB['tot_conf'] ?></div>
                        <div class="text-xs text-gray-500 mt-1">Confermate</div>
                    </div>
                    <div class="bg-sand/10 rounded-xl p-4 text-center">
                        <div class="text-3xl font-black text-sand"><?= (int)$statsDB['tot_post'] ?></div>
                        <div class="text-xs text-gray-500 mt-1">Postazioni totali</div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-4 text-center">
                        <div class="text-3xl font-black text-blue-600"><?= (int)$statsDB['tot_admin'] ?></div>
                        <div class="text-xs text-gray-500 mt-1">Utenti admin</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info sistema -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <div class="w-8 h-8 bg-purple-50 rounded-xl flex items-center justify-center">
                    <?= icon('info', 'w-4 h-4', '#7c3aed') ?>
                </div>
                <h2 class="font-semibold text-ocean">Info Sistema</h2>
            </div>
            <div class="p-5 space-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-500">Versione PHP</span>
                    <span class="font-mono font-medium"><?= phpversion() ?></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-500">Data server</span>
                    <span class="font-medium"><?= date('d/m/Y H:i') ?></span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-50">
                    <span class="text-gray-500">Session ID</span>
                    <span class="font-mono text-xs text-gray-400"><?= substr(session_id(), 0, 12) ?>…</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-500">Utente loggato</span>
                    <span class="font-medium"><?= h($_SESSION['admin_nome'] ?? '') ?></span>
                </div>
            </div>
        </div>

    </div>

    <!-- Link utili -->
    <div class="mt-6 grid sm:grid-cols-3 gap-4">
        <a href="/admin/prenotazioni.php"
           class="flex items-center gap-3 bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:border-ocean/30 hover:shadow-md transition-all group">
            <?= icon('list', 'w-6 h-6 text-ocean group-hover:text-ocean-light', '#0c2340') ?>
            <div>
                <div class="font-semibold text-gray-800 text-sm">Prenotazioni</div>
                <div class="text-xs text-gray-400">Gestisci tutte le prenotazioni</div>
            </div>
        </a>
        <a href="/admin/mappa.php"
           class="flex items-center gap-3 bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:border-ocean/30 hover:shadow-md transition-all group">
            <?= icon('grid', 'w-6 h-6 text-ocean group-hover:text-ocean-light', '#0c2340') ?>
            <div>
                <div class="font-semibold text-gray-800 text-sm">Mappa Spiaggia</div>
                <div class="text-xs text-gray-400">Visualizza la griglia postazioni</div>
            </div>
        </a>
        <a href="/admin/pos.php"
           class="flex items-center gap-3 bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:border-ocean/30 hover:shadow-md transition-all group">
            <?= icon('credit-card', 'w-6 h-6 text-ocean group-hover:text-ocean-light', '#0c2340') ?>
            <div>
                <div class="font-semibold text-gray-800 text-sm">POS Cassa</div>
                <div class="text-xs text-gray-400">Apertura rapida punto vendita</div>
            </div>
        </a>
    </div>

</div>
</main>
</body>
</html>
