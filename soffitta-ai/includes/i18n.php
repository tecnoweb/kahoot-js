<?php
require_once __DIR__ . '/config.php';

// Lingue supportate: codice ISO → [nome nativo, dir, locale hreflang]
const SUPPORTED_LANGUAGES = [
    'it' => ['name' => 'Italiano',          'dir' => 'ltr', 'flag' => '🇮🇹', 'locale' => 'it_IT'],
    'en' => ['name' => 'English',           'dir' => 'ltr', 'flag' => '🇬🇧', 'locale' => 'en_GB'],
    'fr' => ['name' => 'Français',          'dir' => 'ltr', 'flag' => '🇫🇷', 'locale' => 'fr_FR'],
    'de' => ['name' => 'Deutsch',           'dir' => 'ltr', 'flag' => '🇩🇪', 'locale' => 'de_DE'],
    'es' => ['name' => 'Español',           'dir' => 'ltr', 'flag' => '🇪🇸', 'locale' => 'es_ES'],
    'pt' => ['name' => 'Português',         'dir' => 'ltr', 'flag' => '🇧🇷', 'locale' => 'pt_BR'],
    'nl' => ['name' => 'Nederlands',        'dir' => 'ltr', 'flag' => '🇳🇱', 'locale' => 'nl_NL'],
    'pl' => ['name' => 'Polski',            'dir' => 'ltr', 'flag' => '🇵🇱', 'locale' => 'pl_PL'],
    'ru' => ['name' => 'Русский',           'dir' => 'ltr', 'flag' => '🇷🇺', 'locale' => 'ru_RU'],
    'zh' => ['name' => '中文',              'dir' => 'ltr', 'flag' => '🇨🇳', 'locale' => 'zh_CN'],
    'ja' => ['name' => '日本語',            'dir' => 'ltr', 'flag' => '🇯🇵', 'locale' => 'ja_JP'],
    'ar' => ['name' => 'العربية',           'dir' => 'rtl', 'flag' => '🇸🇦', 'locale' => 'ar_SA'],
];

// Determina la lingua corrente
function detectLanguage(): string {
    // 1. Parametro URL esplicito
    if (!empty($_GET['lang']) && isset(SUPPORTED_LANGUAGES[$_GET['lang']])) {
        $lang = $_GET['lang'];
        $_SESSION['lang'] = $lang;
        return $lang;
    }
    // 2. Sessione
    if (!empty($_SESSION['lang']) && isset(SUPPORTED_LANGUAGES[$_SESSION['lang']])) {
        return $_SESSION['lang'];
    }
    // 3. Cookie
    if (!empty($_COOKIE['lang']) && isset(SUPPORTED_LANGUAGES[$_COOKIE['lang']])) {
        $_SESSION['lang'] = $_COOKIE['lang'];
        return $_COOKIE['lang'];
    }
    // 4. Accept-Language header
    $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if ($acceptLang) {
        preg_match_all('/([a-zA-Z]{2})(?:-[a-zA-Z]{2})?(?:;q=[0-9.]+)?/', $acceptLang, $m);
        foreach ($m[1] as $code) {
            $code = strtolower($code);
            if (isset(SUPPORTED_LANGUAGES[$code])) {
                $_SESSION['lang'] = $code;
                return $code;
            }
        }
    }
    return 'it';
}

// Carica le traduzioni per la lingua corrente
function loadTranslations(string $lang): array {
    $file = __DIR__ . '/../lang/' . $lang . '.php';
    if (!file_exists($file)) {
        $file = __DIR__ . '/../lang/it.php';
    }
    return require $file;
}

// Traduzione globale
$GLOBALS['_lang']         = null;
$GLOBALS['_translations'] = [];

function initI18n(): string {
    if ($GLOBALS['_lang'] !== null) {
        return $GLOBALS['_lang'];
    }
    $lang = detectLanguage();
    $GLOBALS['_lang']         = $lang;
    $GLOBALS['_translations'] = loadTranslations($lang);
    // Imposta cookie per 30 giorni
    if (!isset($_COOKIE['lang']) || $_COOKIE['lang'] !== $lang) {
        setcookie('lang', $lang, time() + 86400 * 30, '/', '', true, false);
    }
    return $lang;
}

// Funzione di traduzione — usa sprintf-style {0} o named {key}
function t(string $key, array $vars = []): string {
    $text = $GLOBALS['_translations'][$key] ?? $key;
    foreach ($vars as $k => $v) {
        $text = str_replace('{' . $k . '}', htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'), $text);
    }
    return $text;
}

// Versione senza escape — per testo già sicuro o HTML
function tRaw(string $key, array $vars = []): string {
    $text = $GLOBALS['_translations'][$key] ?? $key;
    foreach ($vars as $k => $v) {
        $text = str_replace('{' . $k . '}', (string)$v, $text);
    }
    return $text;
}

// Ritorna attributi HTML lang/dir da inserire nel <html>
function htmlLangAttrs(): string {
    $lang = $GLOBALS['_lang'] ?? 'it';
    $dir  = SUPPORTED_LANGUAGES[$lang]['dir'] ?? 'ltr';
    return 'lang="' . $lang . '" dir="' . $dir . '"';
}

// Genera hreflang links per il <head> SEO
function renderHreflang(string $currentPath = ''): void {
    if (!$currentPath) {
        $uri  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $uri  = preg_replace('/[?&]lang=[a-z]{2}/', '', $uri);
        $currentPath = $uri;
    }
    foreach (SUPPORTED_LANGUAGES as $code => $info) {
        $sep = str_contains($currentPath, '?') ? '&' : '?';
        $url = htmlspecialchars(BASE_URL . $currentPath . $sep . 'lang=' . $code);
        echo "<link rel=\"alternate\" hreflang=\"{$code}\" href=\"{$url}\">\n    ";
    }
    echo "<link rel=\"alternate\" hreflang=\"x-default\" href=\"" . htmlspecialchars(BASE_URL . $currentPath) . "\">\n    ";
}

// Switcher lingua per il navbar
function renderLangSwitcher(): void {
    $currentLang = $GLOBALS['_lang'] ?? 'it';
    $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $currentInfo = SUPPORTED_LANGUAGES[$currentLang];
    echo '<div class="relative group">';
    echo '<button class="flex items-center gap-1.5 text-sm text-stone-500 hover:text-amber-600 transition px-2 py-1 rounded-lg hover:bg-stone-50">';
    echo '<span>' . $currentInfo['flag'] . '</span>';
    echo '<span class="hidden sm:inline font-medium">' . htmlspecialchars($currentLang) . '</span>';
    echo '<span class="text-stone-300 text-xs">▾</span>';
    echo '</button>';
    echo '<div class="absolute right-0 top-full mt-1 bg-white border border-stone-200 rounded-xl shadow-lg py-1.5 min-w-[160px] hidden group-hover:block z-50">';
    foreach (SUPPORTED_LANGUAGES as $code => $info) {
        $url = htmlspecialchars($uri . '?lang=' . $code);
        $active = $code === $currentLang ? 'bg-amber-50 text-amber-700 font-medium' : 'text-stone-600 hover:bg-stone-50';
        echo "<a href=\"{$url}\" class=\"flex items-center gap-2 px-4 py-2 text-sm {$active} transition\">";
        echo "<span>{$info['flag']}</span><span>{$info['name']}</span>";
        echo '</a>';
    }
    echo '</div></div>';
}

// Ritorna il locale per og:locale
function getCurrentLocale(): string {
    $lang = $GLOBALS['_lang'] ?? 'it';
    return SUPPORTED_LANGUAGES[$lang]['locale'] ?? 'it_IT';
}

// Controlla se lingua RTL
function isRtl(): bool {
    $lang = $GLOBALS['_lang'] ?? 'it';
    return (SUPPORTED_LANGUAGES[$lang]['dir'] ?? 'ltr') === 'rtl';
}
