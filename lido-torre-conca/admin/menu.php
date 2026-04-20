<?php
require_once __DIR__ . '/includes/auth.php';
// Solo livello 1 e 2
if ($_SESSION['admin_livello'] > 2) {
    header('Location: /admin/index.php'); exit;
}
$pageTitle = 'Gestione Menu';

$categorie = fetchAll('SELECT * FROM menu_categorie ORDER BY ordine, nome');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> – Admin Lido Torre Conca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{ocean:{'DEFAULT':'#0c2340','light':'#1a3a60'},sand:{'DEFAULT':'#d4a847','light':'#f5e6c8'}},fontFamily:{body:['"Inter"','system-ui']}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="font-body bg-gray-50">
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-content lg:ml-64 min-h-screen">
    <div class="p-4 lg:p-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-ocean"><?= $pageTitle ?></h1>
                <p class="text-gray-400 text-sm mt-0.5">Gestisci categorie, piatti, bevande e prezzi</p>
            </div>
            <button onclick="Modal.open('modal-add-item')" class="btn-primary flex items-center gap-2 h-10 px-4 text-sm">
                <?= icon('plus','w-4 h-4') ?> Nuovo Articolo
            </button>
        </div>

        <!-- Categorie accordion -->
        <div class="space-y-4">
            <?php foreach ($categorie as $cat):
                $items = fetchAll(
                    'SELECT * FROM menu_items WHERE categoria_id = ? ORDER BY ordine, nome',
                    [$cat['id']]
                );
            ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 bg-ocean/5 rounded-lg flex items-center justify-center">
                            <?= icon('food','w-4 h-4','#0c2340') ?>
                        </span>
                        <div>
                            <div class="font-semibold text-ocean"><?= h($cat['nome']) ?></div>
                            <div class="text-gray-400 text-xs"><?= count($items) ?> articoli</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $cat['attiva'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $cat['attiva'] ? 'Attiva' : 'Nascosta' ?>
                        </span>
                        <button onclick="toggleCategoria(<?= $cat['id'] ?>, <?= $cat['attiva'] ? 0 : 1 ?>)"
                                class="text-xs text-ocean hover:text-sand underline">
                            <?= $cat['attiva'] ? 'Nascondi' : 'Mostra' ?>
                        </button>
                    </div>
                </div>
                <!-- Items table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-400 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-5 py-2 text-left">Articolo</th>
                                <th class="px-3 py-2 text-right">Prezzo</th>
                                <th class="px-3 py-2 text-center">Disponibile</th>
                                <th class="px-3 py-2 text-center">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if (empty($items)): ?>
                            <tr><td colspan="4" class="px-5 py-4 text-gray-400 text-center">Nessun articolo in questa categoria</td></tr>
                            <?php else: foreach ($items as $item): ?>
                            <tr class="hover:bg-gray-50" id="item-<?= $item['id'] ?>">
                                <td class="px-5 py-3">
                                    <div class="font-medium text-ocean"><?= h($item['nome']) ?></div>
                                    <?php if ($item['descrizione']): ?><div class="text-gray-400 text-xs"><?= h($item['descrizione']) ?></div><?php endif; ?>
                                    <?php if ($item['allergeni']): ?><div class="text-yellow-600 text-xs">Allergeni: <?= h($item['allergeni']) ?></div><?php endif; ?>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <span class="font-bold text-ocean">€<?= number_format($item['prezzo'], 2) ?></span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <button onclick="toggleItem(<?= $item['id'] ?>, <?= $item['disponibile'] ? 0 : 1 ?>)"
                                            class="w-10 h-5 rounded-full transition-colors relative <?= $item['disponibile'] ? 'bg-green-400' : 'bg-gray-300' ?>">
                                        <span class="absolute w-4 h-4 bg-white rounded-full top-0.5 transition-all <?= $item['disponibile'] ? 'right-0.5' : 'left-0.5' ?>"></span>
                                    </button>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="editItem(<?= h(json_encode($item)) ?>)"
                                                class="text-ocean hover:text-sand"><?= icon('edit','w-4 h-4') ?></button>
                                        <button onclick="deleteItem(<?= $item['id'] ?>, '<?= h(addslashes($item['nome'])) ?>')"
                                                class="text-red-400 hover:text-red-600"><?= icon('trash','w-4 h-4') ?></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal add/edit item -->
<div class="modal-overlay" id="modal-add-item">
    <div class="modal-box max-w-lg">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-display text-xl font-bold text-ocean" id="modal-title">Nuovo Articolo</h2>
            <button data-modal-close="modal-add-item" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                <?= icon('close','w-4 h-4','#64748b') ?>
            </button>
        </div>
        <form id="form-item">
            <input type="hidden" id="item-id" name="id" value="">
            <div class="space-y-4">
                <div class="form-group">
                    <label class="form-label">Categoria *</label>
                    <select name="categoria_id" id="item-cat" class="form-input" required>
                        <?php foreach ($categorie as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= h($c['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nome *</label>
                    <input type="text" name="nome" id="item-nome" class="form-input" required placeholder="Es. Spritz Aperol">
                </div>
                <div class="form-group">
                    <label class="form-label">Descrizione</label>
                    <input type="text" name="descrizione" id="item-desc" class="form-input" placeholder="Breve descrizione">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="form-group">
                        <label class="form-label">Prezzo € *</label>
                        <input type="number" step="0.50" min="0" name="prezzo" id="item-prezzo" class="form-input" required placeholder="7.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ordine visualizzazione</label>
                        <input type="number" min="0" name="ordine" id="item-ordine" class="form-input" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Allergeni</label>
                    <input type="text" name="allergeni" id="item-allergeni" class="form-input" placeholder="Es. Glutine, Lattosio">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" data-modal-close="modal-add-item" class="btn-outline flex-1 h-12">Annulla</button>
                    <button type="submit" class="btn-primary flex-1 h-12">Salva</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/main.js"></script>
<script>
async function toggleItem(id, val) {
    await fetch('/admin/api/menu_item.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'toggle', id, disponibile: val })
    });
    location.reload();
}

async function toggleCategoria(id, val) {
    await fetch('/admin/api/menu_item.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'toggle_cat', id, attiva: val })
    });
    location.reload();
}

function editItem(item) {
    document.getElementById('modal-title').textContent    = 'Modifica Articolo';
    document.getElementById('item-id').value              = item.id;
    document.getElementById('item-cat').value             = item.categoria_id;
    document.getElementById('item-nome').value            = item.nome;
    document.getElementById('item-desc').value            = item.descrizione || '';
    document.getElementById('item-prezzo').value          = item.prezzo;
    document.getElementById('item-ordine').value          = item.ordine;
    document.getElementById('item-allergeni').value       = item.allergeni || '';
    Modal.open('modal-add-item');
}

async function deleteItem(id, nome) {
    if (!confirm(`Eliminare "${nome}"?`)) return;
    const r = await fetch('/admin/api/menu_item.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'delete', id })
    });
    const d = await r.json();
    if (d.ok) { document.getElementById(`item-${id}`)?.remove(); Toast.success('Eliminato'); }
    else Toast.error(d.errore || 'Errore');
}

document.getElementById('form-item').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const data = Object.fromEntries(fd.entries());
    data.action = data.id ? 'update' : 'create';
    data.prezzo = parseFloat(data.prezzo);
    data.ordine = parseInt(data.ordine) || 0;
    if (!data.id) delete data.id;

    const r = await fetch('/admin/api/menu_item.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(data)
    });
    const res = await r.json();
    if (res.ok) { Toast.success('Salvato'); setTimeout(() => location.reload(), 800); }
    else Toast.error(res.errore || 'Errore');
});
</script>
</body>
</html>
