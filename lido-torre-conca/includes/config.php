<?php
// ============================================================
// Lido Torre Conca – Configurazione
// ============================================================

define('DB_HOST',     getenv('DB_HOST')     ?: 'localhost');
define('DB_NAME',     getenv('DB_NAME')     ?: 'lido_torre_conca');
define('DB_USER',     getenv('DB_USER')     ?: 'root');
define('DB_PASS',     getenv('DB_PASS')     ?: '');
define('DB_CHARSET',  'utf8mb4');

define('SITE_NAME',   'Lido Torre Conca');
define('SITE_URL',    'https://lidotorreconca.it');
define('SITE_EMAIL',  'info@lidotorreconca.it');
define('WHATSAPP_NUM','393471234567'); // senza + e spazi

define('ORARIO_APERTURA', '08:30');
define('ORARIO_CHIUSURA',  '19:00');
define('ORARIO_POMERIGGIO','14:00');

// Prenotazione minima 1 ora prima
define('MINUTI_ANTICIPO', 60);

define('TIMEZONE', 'Europe/Rome');
date_default_timezone_set(TIMEZONE);

// Sessione sicura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}
