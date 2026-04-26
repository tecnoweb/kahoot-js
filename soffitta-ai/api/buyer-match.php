<?php
// API: gestione interesse/offerta di un'azienda su una scansione
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/buyer.php';

header('Content-Type: application/json; charset=utf-8');

if (!isCompanyLoggedIn()) {
    jsonResponse(false, [], 'Accesso richiesto');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Metodo non consentito');
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    jsonResponse(false, [], 'Token di sicurezza non valido');
}

$action    = $_POST['action'] ?? '';
$matchId   = (int) ($_POST['match_id'] ?? 0);

if (!$matchId) {
    jsonResponse(false, [], 'ID match non valido');
}

$pdo       = getDB();
$companyId = (int) $_SESSION['company_id'];

// Azione speciale: aggiunta manuale interesse da browse.php
if ($action === 'quick_interest') {
    $scanId = (int) ($_POST['scan_id'] ?? 0);
    if (!$scanId) jsonResponse(false, [], 'ID scansione non valido');

    $stmt = $pdo->prepare('SELECT id FROM scans WHERE id = ?');
    $stmt->execute([$scanId]);
    if (!$stmt->fetch()) jsonResponse(false, [], 'Scansione non trovata');

    // Calcola score e inserisce (o ignora se già esiste)
    require_once '../includes/buyer.php';
    $stmt = $pdo->prepare('SELECT * FROM scans WHERE id = ?');
    $stmt->execute([$scanId]);
    $scan = $stmt->fetch();
    $stmt2 = $pdo->prepare('SELECT * FROM companies WHERE id = ?');
    $stmt2->execute([$companyId]);
    $company = $stmt2->fetch();
    $score = calculateMatchScore($scan, $company);

    $ins = $pdo->prepare(
        'INSERT IGNORE INTO buyer_matches (scan_id, company_id, match_score, status)
         VALUES (?, ?, ?, \'interested\')'
    );
    $ins->execute([$scanId, $companyId, $score]);
    jsonResponse(true, [], 'Interesse registrato');
}

// Verifica che il match appartenga a questa azienda
$stmt = $pdo->prepare('SELECT * FROM buyer_matches WHERE id = ? AND company_id = ?');
$stmt->execute([$matchId, $companyId]);
$match = $stmt->fetch();

if (!$match) {
    jsonResponse(false, [], 'Match non trovato');
}

switch ($action) {
    case 'interested':
        $pdo->prepare(
            "UPDATE buyer_matches SET status = 'interested', responded_at = NOW() WHERE id = ?"
        )->execute([$matchId]);
        jsonResponse(true, [], 'Interesse registrato');

    case 'not_interested':
        $pdo->prepare(
            "UPDATE buyer_matches SET status = 'not_interested', responded_at = NOW() WHERE id = ?"
        )->execute([$matchId]);
        jsonResponse(true, [], 'Risposta registrata');

    case 'offer':
        $amount  = round((float) ($_POST['offer_amount'] ?? 0), 2);
        $message = sanitize($_POST['message'] ?? '');
        if ($amount <= 0) {
            jsonResponse(false, [], 'Importo offerta non valido');
        }
        if (mb_strlen($message) > 1000) {
            jsonResponse(false, [], 'Messaggio troppo lungo (max 1000 caratteri)');
        }
        $pdo->prepare(
            "UPDATE buyer_matches
             SET status = 'offer_made', offer_amount = ?, message = ?, responded_at = NOW()
             WHERE id = ?"
        )->execute([$amount, $message, $matchId]);
        jsonResponse(true, [], 'Offerta inviata al venditore');

    default:
        jsonResponse(false, [], 'Azione non valida');
}
