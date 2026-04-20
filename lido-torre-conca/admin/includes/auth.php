<?php
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
require_once dirname(dirname(__DIR__)) . '/includes/icons.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

// Esponi livello corrente in modo sicuro a tutti i file admin
if (!defined('ADMIN_LIVELLO')) {
    define('ADMIN_LIVELLO', (int)($_SESSION['admin_livello'] ?? 2));
}

/**
 * Verifica che l'admin abbia almeno il livello richiesto.
 * Livello 1 = sviluppatore (massimo), livello 3 = cassiere (minimo).
 */
function requireLivello(int $livelloRichiesto): void {
    if (ADMIN_LIVELLO > $livelloRichiesto) {
        http_response_code(403);
        echo '<div style="font-family:sans-serif;padding:2rem;color:#c00">Accesso negato: permesso insufficiente per questa sezione.</div>';
        exit;
    }
}
