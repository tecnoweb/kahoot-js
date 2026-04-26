<?php
require_once __DIR__ . '/config.php';

function sanitize(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Genera slug SEO-friendly da stringa italiana
function slugify(string $text): string {
    $map = [
        'à' => 'a','á' => 'a','â' => 'a','ä' => 'a',
        'è' => 'e','é' => 'e','ê' => 'e','ë' => 'e',
        'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i',
        'ò' => 'o','ó' => 'o','ô' => 'o','ö' => 'o',
        'ù' => 'u','ú' => 'u','û' => 'u','ü' => 'u',
        'ñ' => 'n','ç' => 'c',
    ];
    $text = strtr(mb_strtolower($text, 'UTF-8'), $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function jsonResponse(bool $success, array $data = [], string $message = ''): never {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

function setSecurityHeaders(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com https://js.stripe.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tailwindcss.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self'; frame-src https://js.stripe.com;");
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
