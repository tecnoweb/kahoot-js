<?php
// Stub Stripe checkout — da completare con Stripe PHP SDK
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/');
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    redirect('/');
}

$scanId = (int) ($_POST['scan_id'] ?? 0);
if (!$scanId) redirect('/');

$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM scans WHERE id = ?');
$stmt->execute([$scanId]);
$scan = $stmt->fetch();

if (!$scan || $scan['paid']) {
    redirect('/perizia/' . $scanId);
}

$userId = isLoggedIn() ? (int) $_SESSION['user_id'] : null;

try {
    // Registra il tentativo di pagamento
    $stmt = $pdo->prepare(
        'INSERT INTO payments (scan_id, user_id, amount, status) VALUES (?, ?, ?, \'pending\')'
    );
    $stmt->execute([$scanId, $userId, PRICE_PREMIUM_SCAN]);
    $paymentId = $pdo->lastInsertId();
} catch (PDOException $e) {
    error_log('Payment insert error: ' . $e->getMessage());
    redirect('/checkout.php?scan_id=' . $scanId . '&error=1');
}

/*
 * INTEGRAZIONE STRIPE — da completare:
 *
 * require_once '../vendor/autoload.php';
 * \Stripe\Stripe::setApiKey(STRIPE_SECRET);
 *
 * $session = \Stripe\Checkout\Session::create([
 *     'payment_method_types' => ['card'],
 *     'line_items' => [[
 *         'price_data' => [
 *             'currency'     => 'eur',
 *             'unit_amount'  => (int)(PRICE_PREMIUM_SCAN * 100),
 *             'product_data' => [
 *                 'name' => 'Perizia completa — ' . $scan['object_name'],
 *             ],
 *         ],
 *         'quantity' => 1,
 *     ]],
 *     'mode'       => 'payment',
 *     'success_url' => BASE_URL . '/api/payment-success.php?session_id={CHECKOUT_SESSION_ID}&scan_id=' . $scanId,
 *     'cancel_url'  => BASE_URL . '/checkout.php?scan_id=' . $scanId,
 *     'metadata'    => ['scan_id' => $scanId, 'payment_id' => $paymentId],
 * ]);
 *
 * redirect($session->url);
 */

// Stub temporaneo: simula pagamento riuscito per test
$pdo->prepare('UPDATE payments SET status = \'paid\' WHERE id = ?')->execute([$paymentId]);
$pdo->prepare('UPDATE scans SET paid = 1 WHERE id = ?')->execute([$scanId]);

redirect('/perizia/' . ($scan['public_slug'] ?? $scanId) . '?paid=1');
