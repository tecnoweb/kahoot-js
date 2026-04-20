<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Gestione Prezzi';

$prezzi = fetchAll('SELECT * FROM prezzi ORDER BY id');
$noleggi = fetchAll('SELECT * FROM noleggi ORDER BY id');
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

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ocean">Gestione Prezzi</h1>
        <p class="text-gray-500 text-sm mt-0.5">Modifica i prezzi del listino. Le modifiche sono immediate.</p>
    </div>

    <!-- Prezzi prenotazioni -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 bg-ocean/10 rounded-xl flex items-center justify-center">
                <?= icon('tag', 'w-4 h-4', '#0c2340') ?>
            </div>
            <div>
                <h2 class="font-semibold text-ocean">Listino Prenotazioni</h2>
                <p class="text-xs text-gray-400">Prezzi base per tipo di prenotazione e servizi extra</p>
            </div>
        </div>
        <div class="divide-y divide-gray-50">
            <?php foreach ($prezzi as $p): ?>
            <div class="flex items-center justify-between px-5 py-4 gap-4" id="row-prezzo-<?= (int)$p['id'] ?>">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-800"><?= h($p['nome']) ?></div>
                    <div class="text-xs text-gray-400 mt-0.5"><?= h($p['descrizione'] ?? '') ?></div>
                    <div class="text-xs font-mono text-gray-300 mt-0.5"><?= h($p['tipo']) ?></div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-sm">€</span>
                        <input type="number"
                               id="prezzo-<?= (int)$p['id'] ?>"
                               value="<?= number_format((float)$p['prezzo'], 2, '.', '') ?>"
                               step="0.50" min="0"
                               class="w-28 pl-7 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-ocean/30 text-right"
                               onkeydown="if(event.key==='Enter') salvaPrezzo(<?= (int)$p['id'] ?>)">
                    </div>
                    <button onclick="salvaPrezzo(<?= (int)$p['id'] ?>)"
                            class="flex items-center gap-1.5 bg-ocean hover:bg-ocean-light text-white px-4 py-2.5 rounded-xl text-sm font-medium transition-colors">
                        <?= icon('check', 'w-4 h-4') ?>
                        Salva
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Noleggi -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 bg-sand/20 rounded-xl flex items-center justify-center">
                <?= icon('boat', 'w-4 h-4', '#d4a847') ?>
            </div>
            <div>
                <h2 class="font-semibold text-ocean">Noleggi</h2>
                <p class="text-xs text-gray-400">Prezzi orari per i servizi di noleggio</p>
            </div>
        </div>
        <div class="divide-y divide-gray-50">
            <?php foreach ($noleggi as $n): ?>
            <div class="flex items-center justify-between px-5 py-4 gap-4" id="row-noleggio-<?= (int)$n['id'] ?>">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-800"><?= h($n['nome']) ?></div>
                    <div class="text-xs text-gray-400 mt-0.5">Disponibili: <?= (int)$n['quantita_disponibile'] ?> unità</div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-sm">€</span>
                        <input type="number"
                               id="noleggio-<?= (int)$n['id'] ?>"
                               value="<?= number_format((float)$n['prezzo_ora'], 2, '.', '') ?>"
                               step="0.50" min="0"
                               class="w-28 pl-7 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-ocean/30 text-right"
                               onkeydown="if(event.key==='Enter') salvaNoleggioPrezzo(<?= (int)$n['id'] ?>)">
                    </div>
                    <div class="text-xs text-gray-400">/ora</div>
                    <button onclick="salvaNoleggioPrezzo(<?= (int)$n['id'] ?>)"
                            class="flex items-center gap-1.5 bg-ocean hover:bg-ocean-light text-white px-4 py-2.5 rounded-xl text-sm font-medium transition-colors">
                        <?= icon('check', 'w-4 h-4') ?>
                        Salva
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Note informative -->
    <div class="mt-5 bg-blue-50 border border-blue-100 rounded-2xl p-4 flex gap-3">
        <?= icon('info', 'w-5 h-5 flex-shrink-0 mt-0.5', '#3b82f6') ?>
        <div class="text-sm text-blue-700">
            <strong>Nota:</strong> Le modifiche ai prezzi si applicano solo alle nuove prenotazioni. Le prenotazioni esistenti mantengono il prezzo al momento della creazione.
        </div>
    </div>

</div>
</main>

<script>
async function salvaPrezzo(id) {
    const input = document.getElementById(`prezzo-${id}`);
    const prezzo = parseFloat(input.value);
    if (isNaN(prezzo) || prezzo < 0) {
        showToast('Prezzo non valido', 'error');
        return;
    }
    const res = await fetch('/admin/api/aggiorna_prezzo.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id, prezzo, tipo: 'prezzo'}),
    });
    const data = await res.json();
    if (data.ok) {
        showToast('Prezzo aggiornato con successo', 'success');
        const row = document.getElementById(`row-prezzo-${id}`);
        row.classList.add('bg-green-50');
        setTimeout(() => row.classList.remove('bg-green-50'), 1500);
    } else {
        showToast(data.error || 'Errore', 'error');
    }
}

async function salvaNoleggioPrezzo(id) {
    const input = document.getElementById(`noleggio-${id}`);
    const prezzo = parseFloat(input.value);
    if (isNaN(prezzo) || prezzo < 0) {
        showToast('Prezzo non valido', 'error');
        return;
    }
    const res = await fetch('/admin/api/aggiorna_prezzo.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id, prezzo, tipo: 'noleggio'}),
    });
    const data = await res.json();
    if (data.ok) {
        showToast('Prezzo noleggio aggiornato', 'success');
        const row = document.getElementById(`row-noleggio-${id}`);
        row.classList.add('bg-green-50');
        setTimeout(() => row.classList.remove('bg-green-50'), 1500);
    } else {
        showToast(data.error || 'Errore', 'error');
    }
}
</script>
</body>
</html>
