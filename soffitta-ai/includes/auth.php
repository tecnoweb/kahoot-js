<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        redirect('/login.php');
    }
}

function requireAdmin(): void {
    if (empty($_SESSION['is_admin'])) {
        http_response_code(403);
        exit('Accesso negato');
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function loginUser(string $email, string $password): array {
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT id, email, name, password, plan, scans_used FROM users WHERE email = ?');
    $stmt->execute([mb_strtolower(trim($email))]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Email o password errati'];
    }

    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_plan'] = $user['plan'];

    return ['success' => true];
}

function registerUser(string $email, string $password, string $name): array {
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'La password deve avere almeno 8 caratteri'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email non valida'];
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([mb_strtolower(trim($email))]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email già registrata'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (email, password, name) VALUES (?, ?, ?)');
    $stmt->execute([mb_strtolower(trim($email)), $hash, sanitize($name)]);

    $userId = (int) $pdo->lastInsertId();
    session_regenerate_id(true);
    $_SESSION['user_id']   = $userId;
    $_SESSION['user_name'] = sanitize($name);
    $_SESSION['user_plan'] = 'free';

    return ['success' => true];
}

function logoutUser(): void {
    session_unset();
    session_destroy();
}

function getCurrentUser(): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT id, email, name, plan, scans_used FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

// Controlla se l'utente può effettuare una nuova scansione gratuita
function canScanFree(?int $userId): bool {
    if ($userId === null) {
        // Utenti anonimi: controlla sessione
        return empty($_SESSION['anon_scan_done']);
    }
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT scans_used, plan FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) {
        return false;
    }
    return $user['plan'] === 'pro' || $user['scans_used'] < FREE_SCANS_LIMIT;
}
