<?php
// Soffitta.ai — Configurazione globale

define('DB_HOST', 'localhost');
define('DB_NAME', 'soffitta_ai');
define('DB_USER', 'root');
define('DB_PASS', '');

define('CLAUDE_API_KEY', 'sk-ant-XXXXXXXX');
define('CLAUDE_MODEL', 'claude-opus-4-5');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_MAX_MB', 10);
define('BASE_URL', 'https://soffitta.ai');

define('STRIPE_SECRET', 'sk_test_XXXXXXXX');
define('STRIPE_PUBLIC', 'pk_test_XXXXXXXX');

define('FREE_SCANS_LIMIT', 1);
define('PRICE_PREMIUM_SCAN', 2.99);

// Categorie oggetti supportate
define('CATEGORIES', ['quadri', 'ceramiche', 'gioielli', 'mobili', 'argenteria', 'altro']);
