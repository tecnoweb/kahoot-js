<?php
require_once dirname(__DIR__) . '/includes/functions.php';

// Se già loggato, redirect
if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeStr($_POST['username'] ?? '', 100);
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $user = fetchOne(
            'SELECT id, username, password_hash, nome, livello, attivo FROM admin_users WHERE username = ?',
            [$username]
        );
        if ($user && password_verify($password, $user['password_hash']) && ($user['attivo'] ?? 1)) {
            session_regenerate_id(true);
            $_SESSION['admin_id']      = $user['id'];
            $_SESSION['admin_nome']    = $user['nome'];
            $_SESSION['admin_livello'] = (int)($user['livello'] ?? 2);
            // Cassiere (livello 3) va direttamente al POS
            header('Location: ' . ($_SESSION['admin_livello'] === 3 ? '/admin/pos.php' : '/admin/index.php'));
            exit;
        }
    }
    $error = 'Credenziali non valide. Riprova.';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso Admin – Lido Torre Conca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ocean: { DEFAULT: '#0c2340', light: '#1a3a60', dark: '#08182d' },
                        sand:  { DEFAULT: '#d4a847', light: '#f5e6c8', dark: '#a07c2a' }
                    },
                    fontFamily: { body: ['"Inter"', 'system-ui', 'sans-serif'] }
                }
            }
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-body min-h-screen bg-gradient-to-br from-ocean-dark via-ocean to-ocean-light flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <!-- Logo / Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-sand rounded-2xl mb-4 shadow-lg">
                <svg class="w-9 h-9 text-ocean" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 12a11.05 11.05 0 00-22 0zm-5 7a3 3 0 01-6 0v-7"/>
                </svg>
            </div>
            <h1 class="text-white text-2xl font-bold tracking-tight">Lido Torre Conca</h1>
            <p class="text-white/60 text-sm mt-1">Pannello Amministrativo</p>
        </div>

        <!-- Card Login -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-ocean text-lg font-semibold mb-6">Accedi</h2>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-5 flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?= h($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/admin/login.php" novalidate>
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?= h($_POST['username'] ?? '') ?>"
                        required
                        autocomplete="username"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-ocean focus:border-transparent transition"
                        placeholder="admin"
                    >
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-ocean focus:border-transparent transition pr-10"
                            placeholder="••••••••"
                        >
                        <button type="button" onclick="togglePwd()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg id="eye-icon" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full bg-ocean hover:bg-ocean-light text-white font-semibold py-2.5 px-4 rounded-lg transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-ocean focus:ring-offset-2"
                >
                    Accedi
                </button>
            </form>
        </div>

        <p class="text-center text-white/40 text-xs mt-6">Lido Torre Conca &copy; <?= date('Y') ?></p>
    </div>

    <script>
        function togglePwd() {
            const inp = document.getElementById('password');
            const ico = document.getElementById('eye-icon');
            if (inp.type === 'password') {
                inp.type = 'text';
                ico.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
            } else {
                inp.type = 'password';
                ico.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            }
        }
        document.getElementById('username').focus();
    </script>
</body>
</html>
