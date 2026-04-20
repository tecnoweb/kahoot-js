<?php
require_once __DIR__ . '/db.php';

// ── Codice prenotazione univoco ──────────────────────────────
function generaCodice(): string {
    do {
        $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $exists = fetchOne('SELECT id FROM prenotazioni WHERE codice = ?', [$code]);
    } while ($exists);
    return $code;
}

// ── Disponibilità ────────────────────────────────────────────
function postazioneDisponibile(int $postazioneId, string $dataInizio, string $dataFine): bool {
    $row = fetchOne(
        "SELECT COUNT(*) AS cnt FROM prenotazioni
         WHERE postazione_id = ?
           AND stato != 'annullata'
           AND data_inizio <= ? AND data_fine >= ?",
        [$postazioneId, $dataFine, $dataInizio]
    );
    return (int)$row['cnt'] === 0;
}

// ── Mappa disponibilità per data ─────────────────────────────
function mappaDisponibilita(string $data, string $tipoPrenotazione): array {
    $dataFine = ($tipoPrenotazione === 'mezza_giornata') ? $data : $data;

    $sql = "SELECT p.id, p.fila, p.numero, p.numero_globale, p.stato,
                   CASE WHEN pr.id IS NOT NULL THEN 'occupata' ELSE 'libera' END AS disponibilita,
                   pr.tipo_prenotazione AS tipo_occupazione
            FROM postazioni p
            LEFT JOIN prenotazioni pr ON pr.postazione_id = p.id
                AND pr.stato != 'annullata'
                AND pr.data_inizio <= :data_fine
                AND pr.data_fine   >= :data_inizio
                AND (
                    pr.tipo_prenotazione = 'giornata_intera'
                    OR pr.tipo_prenotazione = :tipo
                    OR pr.tipo_prenotazione IN ('settimanale','mensile')
                )
            ORDER BY p.fila, p.numero";

    return fetchAll($sql, [
        ':data_inizio' => $data,
        ':data_fine'   => $data,
        ':tipo'        => $tipoPrenotazione,
    ]);
}

// ── Calcolo prezzo ────────────────────────────────────────────
function calcolaPrezzo(
    string $tipo,
    int $gg,
    bool $extraLettino,
    bool $navetta
): float {
    $prezzi = [];
    foreach (fetchAll('SELECT tipo, prezzo FROM prezzi') as $r) {
        $prezzi[$r['tipo']] = (float)$r['prezzo'];
    }

    $base = match ($tipo) {
        'giornata_intera' => ($prezzi['giornata_intera'] ?? 25) * $gg,
        'mezza_giornata'  => ($prezzi['mezza_giornata']  ?? 15) * $gg,
        'settimanale'     => $prezzi['settimanale'] ?? 140,
        'mensile'         => $prezzi['mensile']     ?? 450,
        default           => 0,
    };

    if ($extraLettino) $base += ($prezzi['extra_lettino'] ?? 5) * $gg;
    if ($navetta)      $base += ($prezzi['navetta'] ?? 3)       * $gg;

    return round($base, 2);
}

// ── Giorni tra due date ───────────────────────────────────────
function giorniTra(string $inizio, string $fine): int {
    $d1 = new DateTime($inizio);
    $d2 = new DateTime($fine);
    return (int)$d1->diff($d2)->days + 1;
}

// ── Sanitize ─────────────────────────────────────────────────
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitizeStr(mixed $v, int $max = 255): string {
    return substr(trim((string)$v), 0, $max);
}

// ── JSON response ─────────────────────────────────────────────
function jsonOut(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── CSRF ─────────────────────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

// ── Validazione email / telefono ──────────────────────────────
function validEmail(string $e): bool {
    return (bool)filter_var($e, FILTER_VALIDATE_EMAIL);
}

function validTel(string $t): bool {
    return (bool)preg_match('/^\+?[\d\s\-]{7,20}$/', $t);
}

// ── WhatsApp link ─────────────────────────────────────────────
function waLink(string $msg = ''): string {
    $num = WHATSAPP_NUM;
    $txt = $msg ? '&text=' . rawurlencode($msg) : '';
    return "https://wa.me/{$num}{$txt}";
}

// ── Prenotazione per codice ───────────────────────────────────
function getPrenotazioneByCodice(string $codice): ?array {
    return fetchOne(
        "SELECT pr.*, p.fila, p.numero, p.numero_globale
         FROM prenotazioni pr
         JOIN postazioni p ON p.id = pr.postazione_id
         WHERE pr.codice = ?",
        [strtoupper($codice)]
    );
}
