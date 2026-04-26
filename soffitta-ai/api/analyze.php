<?php
// Endpoint alternativo: analizza un'immagine già caricata per scan_id
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/claude.php';
require_once '../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, [], 'Metodo non consentito');
}

$scanId = (int) ($_GET['scan_id'] ?? 0);
if (!$scanId) {
    jsonResponse(false, [], 'ID scansione non valido');
}

$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM scans WHERE id = ?');
$stmt->execute([$scanId]);
$scan = $stmt->fetch();

if (!$scan) {
    jsonResponse(false, [], 'Scansione non trovata');
}

// Solo il proprietario o utenti anonimi con accesso in sessione
$userId = isLoggedIn() ? (int) $_SESSION['user_id'] : null;
if ($scan['user_id'] && $scan['user_id'] !== $userId) {
    jsonResponse(false, [], 'Accesso non autorizzato');
}

jsonResponse(true, ['data' => $scan]);
