<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/buyer.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, [], 'Metodo non consentito');
}

$action = $_POST['action'] ?? '';

if ($action === 'logout') {
    session_unset();
    session_destroy();
    jsonResponse(true, [], 'Disconnesso');
}

if (!in_array($action, ['login', 'register'], true)) {
    jsonResponse(false, [], 'Azione non valida');
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
    $result = loginCompany($email, $password);
    if ($result['success']) {
        jsonResponse(true, ['redirect' => '/company/dashboard.php']);
    }
    jsonResponse(false, [], $result['message']);
}

// Register
$categories = $_POST['categories'] ?? [];
if (is_string($categories)) {
    $categories = json_decode($categories, true) ?: [];
}

$data = [
    'email'         => $email,
    'password'      => $password,
    'company_name'  => $_POST['company_name'] ?? '',
    'contact_name'  => $_POST['contact_name'] ?? '',
    'phone'         => $_POST['phone'] ?? '',
    'city'          => $_POST['city'] ?? '',
    'province'      => $_POST['province'] ?? '',
    'website'       => $_POST['website'] ?? '',
    'vat_number'    => $_POST['vat_number'] ?? '',
    'company_type'  => $_POST['company_type'] ?? 'antiquario',
    'description'   => $_POST['description'] ?? '',
    'categories'    => $categories,
    'budget_min'    => $_POST['budget_min'] ?? 0,
    'budget_max'    => $_POST['budget_max'] ?? 0,
    'era_interest'  => $_POST['era_interest'] ?? '',
];

$result = registerCompany($data);
if ($result['success']) {
    jsonResponse(true, ['redirect' => '/company/dashboard.php']);
}
jsonResponse(false, [], $result['message']);
