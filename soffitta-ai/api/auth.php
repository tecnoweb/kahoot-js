<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Metodo non consentito');
}

$action = $_POST['action'] ?? '';

if (!in_array($action, ['login', 'register', 'logout'], true)) {
    jsonResponse(false, [], 'Azione non valida');
}

if ($action === 'logout') {
    logoutUser();
    jsonResponse(true, [], 'Disconnesso');
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    jsonResponse(false, [], 'Token di sicurezza non valido');
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    jsonResponse(false, [], 'Email e password sono obbligatorie');
}

if ($action === 'login') {
    $result = loginUser($email, $password);
    if ($result['success']) {
        jsonResponse(true, ['redirect' => '/dashboard.php']);
    }
    jsonResponse(false, [], $result['message']);
}

if ($action === 'register') {
    $name   = sanitize($_POST['name'] ?? '');
    $result = registerUser($email, $password, $name);
    if ($result['success']) {
        jsonResponse(true, ['redirect' => '/dashboard.php']);
    }
    jsonResponse(false, [], $result['message']);
}
