<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/claude.php';
require_once '../includes/helpers.php';
require_once '../includes/buyer.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Metodo non consentito');
}

// CSRF check
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    jsonResponse(false, [], 'Token di sicurezza non valido. Ricarica la pagina.');
}

if (empty($_FILES['image'])) {
    jsonResponse(false, [], 'Nessuna immagine ricevuta');
}

$file     = $_FILES['image'];
$allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$mimeType = mime_content_type($file['tmp_name']);

if (!in_array($mimeType, $allowed, true)) {
    jsonResponse(false, [], 'Formato non supportato. Usa JPG, PNG o WEBP.');
}

if ($file['size'] > UPLOAD_MAX_MB * 1024 * 1024) {
    jsonResponse(false, [], 'Immagine troppo grande. Max ' . UPLOAD_MAX_MB . 'MB.');
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(false, [], 'Errore nel caricamento del file.');
}

// Verifica che sia davvero un'immagine tramite GD
$imgInfo = @getimagesize($file['tmp_name']);
if (!$imgInfo) {
    jsonResponse(false, [], 'Il file non è un\'immagine valida.');
}

// Salvataggio file grezzo
$ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$rawName  = uniqid('scan_', true) . '.' . $ext;
$rawDest  = UPLOAD_DIR . $rawName;

if (!move_uploaded_file($file['tmp_name'], $rawDest)) {
    jsonResponse(false, [], 'Errore nel salvataggio immagine.');
}

// Analisi AI
$analysis = analyzeObjectWithClaude($rawDest);

if (isset($analysis['error'])) {
    @unlink($rawDest);
    jsonResponse(false, [], $analysis['error']);
}

// Salvataggio DB
$pdo    = getDB();
$userId = isLoggedIn() ? (int) $_SESSION['user_id'] : null;

// Genera slug SEO
$slug = slugify($analysis['object_name'] ?? 'oggetto') . '-' . uniqid();

// Converti immagine in WebP con nome SEO
$seoName     = slugify($analysis['category'] ?? 'oggetto') . '-soffitta-ai-' . uniqid();
$processedPath = processImage($rawDest, UPLOAD_DIR, $seoName);
if ($processedPath) {
    @unlink($rawDest);
    $imagePath = 'uploads/' . basename($processedPath);
} else {
    $imagePath = 'uploads/' . $rawName;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO scans
            (user_id, image_path, object_name, description, estimated_min, estimated_max,
             condition_notes, sell_suggestions, era, category, confidence_score, curiosity, public_slug)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        $imagePath,
        $analysis['object_name']    ?? 'Oggetto non identificato',
        $analysis['description']    ?? '',
        $analysis['estimated_min']  ?? 0,
        $analysis['estimated_max']  ?? 0,
        $analysis['condition_notes'] ?? '',
        json_encode($analysis['sell_suggestions'] ?? [], JSON_UNESCAPED_UNICODE),
        $analysis['era']            ?? '',
        $analysis['category']       ?? 'altro',
        $analysis['confidence_score'] ?? 0,
        $analysis['curiosity']      ?? '',
        $slug,
    ]);
} catch (PDOException $e) {
    error_log('DB insert scan error: ' . $e->getMessage());
    jsonResponse(false, [], 'Errore nel salvataggio analisi.');
}

$scanId = (int) $pdo->lastInsertId();

// Incrementa contatore scansioni utente
if ($userId) {
    $pdo->prepare('UPDATE users SET scans_used = scans_used + 1 WHERE id = ?')->execute([$userId]);
} else {
    $_SESSION['anon_scan_done'] = true;
}

// Abbina potenziali acquirenti in background (non blocca la risposta)
$matchCount = createBuyerMatches($scanId);

jsonResponse(true, [
    'scan_id'      => $scanId,
    'slug'         => $slug,
    'data'         => $analysis,
    'image'        => BASE_URL . '/' . $imagePath,
    'buyer_matches'=> $matchCount,
]);
