<?php
require_once __DIR__ . '/config.php';

function renderSEO(array $params = []): void {
    $defaults = [
        'title'       => "Soffitta.ai — Scopri il valore degli oggetti di casa con l'AI",
        'description' => "Fotografa oggetti antichi, quadri, gioielli e mobili trovati in soffitta. L'intelligenza artificiale li identifica e stima il valore in 30 secondi.",
        'keywords'    => 'valutazione oggetti antichi, stima antiquariato online, quanto vale il mio oggetto, perizia online, AI antiquariato Italia',
        'url'         => BASE_URL . $_SERVER['REQUEST_URI'],
        'image'       => BASE_URL . '/assets/og-image.jpg',
        'type'        => 'website',
    ];
    // Usa locale da i18n se disponibile
    if (function_exists('getCurrentLocale')) {
        $defaults['locale'] = getCurrentLocale();
    }
    $p     = array_merge($defaults, $params);
    $title = htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8');
    $desc  = htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8');
    $url   = htmlspecialchars($p['url'], ENT_QUOTES, 'UTF-8');
    $img   = htmlspecialchars($p['image'], ENT_QUOTES, 'UTF-8');
    $kw    = htmlspecialchars($p['keywords'], ENT_QUOTES, 'UTF-8');
    $locale = htmlspecialchars($p['locale'] ?? 'it_IT', ENT_QUOTES, 'UTF-8');
    echo <<<HTML
    <title>{$title}</title>
    <meta name="description" content="{$desc}">
    <meta name="keywords" content="{$kw}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{$url}">
    <meta name="author" content="Soffitta.ai">
    <meta name="language" content="it-IT">
    <meta property="og:type"        content="{$p['type']}">
    <meta property="og:url"         content="{$url}">
    <meta property="og:title"       content="{$title}">
    <meta property="og:description" content="{$desc}">
    <meta property="og:image"       content="{$img}">
    <meta property="og:locale"      content="{$locale}">
    <meta property="og:site_name"   content="Soffitta.ai">
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="{$title}">
    <meta name="twitter:description" content="{$desc}">
    <meta name="twitter:image"       content="{$img}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f59e0b">
    HTML;
}

function renderPerformanceHead(): void {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link rel="dns-prefetch" href="https://api.anthropic.com">';
    echo '<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">';
    echo '<script src="https://cdn.tailwindcss.com"></script>';
}

function renderSchemaWebsite(): void {
    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'WebSite',
        'name'            => 'Soffitta.ai',
        'url'             => BASE_URL,
        'description'     => 'Valutazione oggetti antichi con intelligenza artificiale',
        'inLanguage'      => 'it-IT',
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => BASE_URL . '/cerca?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

function renderSchemaProduct(array $scan): void {
    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $scan['object_name'],
        'description' => $scan['description'],
        'image'       => BASE_URL . '/' . $scan['image_path'],
        'offers'      => [
            '@type'         => 'AggregateOffer',
            'lowPrice'      => $scan['estimated_min'],
            'highPrice'     => $scan['estimated_max'],
            'priceCurrency' => 'EUR',
        ],
    ];
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}

function renderSchemaFAQ(array $faqs): void {
    $items  = array_map(fn($f) => [
        '@type'          => 'Question',
        'name'           => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], $faqs);
    $schema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items];
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
}
