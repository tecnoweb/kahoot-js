<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= h($pageDescription ?? 'Lido Torre Conca – Il tuo stabilimento balneare a Torre Conca. Prenota online lettini, ombrelloni e servizi.') ?>">
    <title><?= h(($pageTitle ?? 'Benvenuto') . ' – Lido Torre Conca') ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    ocean:  { DEFAULT:'#0c2340','light':'#1a3a60','dark':'#08182d' },
                    sand:   { DEFAULT:'#d4a847','light':'#f5e6c8','dark':'#a07c2a' },
                    wave:   { DEFAULT:'#38bdf8','light':'#7dd3fc','dark':'#0ea5e9' },
                    sea:    { DEFAULT:'#0e7490' },
                },
                fontFamily: {
                    display: ['"Playfair Display"','Georgia','serif'],
                    body:    ['"Inter"','system-ui','sans-serif'],
                },
                animation: {
                    'wave-slow': 'wave 8s ease-in-out infinite',
                    'float':     'float 3s ease-in-out infinite',
                },
                keyframes: {
                    wave: { '0%,100%':{ transform:'translateX(0)' }, '50%':{ transform:'translateX(-30px)' } },
                    float:{ '0%,100%':{ transform:'translateY(0)' }, '50%':{ transform:'translateY(-8px)' } },
                }
            }
        }
    };
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">

    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
</head>
<body class="font-body bg-white text-gray-800 antialiased">

<!-- WhatsApp floating btn -->
<a href="<?= waLink('Ciao! Vorrei informazioni sul Lido Torre Conca.') ?>"
   target="_blank" rel="noopener"
   class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-2xl transition-all duration-300 hover:scale-110 group"
   aria-label="Contattaci su WhatsApp">
    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
    <span class="absolute right-16 bg-gray-900 text-white text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity">WhatsApp</span>
</a>

<!-- NAVBAR -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-40 transition-all duration-300" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">

            <!-- Logo -->
            <a href="/index.php" class="flex items-center space-x-3 group">
                <div class="flex items-center justify-center w-12 h-12 bg-sand rounded-full shadow-lg group-hover:scale-105 transition-transform">
                    <svg viewBox="0 0 48 48" class="w-8 h-8" fill="none">
                        <circle cx="24" cy="24" r="22" fill="#0c2340"/>
                        <path d="M8 32 Q24 12 40 32" stroke="#d4a847" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        <path d="M6 36 Q24 18 42 36" stroke="#38bdf8" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                        <circle cx="24" cy="17" r="5" fill="#d4a847"/>
                        <line x1="24" y1="17" x2="24" y2="32" stroke="#d4a847" stroke-width="1.5"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white font-display font-bold text-lg leading-tight nav-logo-text">Lido Torre Conca</div>
                    <div class="text-sand text-xs tracking-widest uppercase nav-logo-sub">Stabilimento Balneare</div>
                </div>
            </a>

            <!-- Desktop menu -->
            <div class="hidden md:flex items-center space-x-1">
                <?php
                $current = basename($_SERVER['PHP_SELF']);
                $navItems = [
                    ['index.php',    'Home'],
                    ['prenota.php',  'Prenota'],
                    ['servizi.php',  'Servizi'],
                    ['contatti.php', 'Contatti'],
                ];
                foreach ($navItems as [$file, $label]):
                    $active = ($current === $file) ? 'nav-active' : '';
                ?>
                <a href="/<?= $file ?>"
                   class="nav-link <?= $active ?> px-4 py-2 rounded-full text-sm font-medium text-white hover:text-sand transition-all duration-200">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>

                <a href="/prenota.php"
                   class="ml-4 px-5 py-2.5 bg-sand hover:bg-sand-dark text-ocean font-semibold text-sm rounded-full shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105">
                    Prenota Ora
                </a>
            </div>

            <!-- Mobile burger -->
            <button id="burger" class="md:hidden p-2 text-white" aria-label="Menu">
                <svg id="burger-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="burger-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="md:hidden hidden pb-4">
            <?php foreach ($navItems as [$file, $label]): ?>
            <a href="/<?= $file ?>" class="block px-4 py-3 text-white hover:text-sand font-medium border-t border-white/10">
                <?= $label ?>
            </a>
            <?php endforeach; ?>
            <div class="px-4 pt-3">
                <a href="/prenota.php" class="block text-center px-5 py-3 bg-sand text-ocean font-semibold rounded-full">
                    Prenota Ora
                </a>
            </div>
        </div>
    </div>
</nav>
