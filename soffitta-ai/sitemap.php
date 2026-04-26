<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';

$pdo   = getDB();
$scans = $pdo->query(
    "SELECT id, public_slug, created_at FROM scans WHERE paid = 1 ORDER BY created_at DESC LIMIT 1000"
)->fetchAll();

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

$staticPages = [
    ['/', '1.0', 'daily'],
    ['/come-funziona', '0.8', 'weekly'],
    ['/prezzi', '0.8', 'weekly'],
    ['/faq', '0.8', 'weekly'],
    ['/categoria/quadri', '0.7', 'daily'],
    ['/categoria/ceramiche', '0.7', 'daily'],
    ['/categoria/gioielli', '0.7', 'daily'],
    ['/categoria/mobili', '0.7', 'daily'],
    ['/categoria/argenteria', '0.7', 'daily'],
    ['/categoria/altro', '0.6', 'weekly'],
];

foreach ($staticPages as [$page, $priority, $freq]) {
    $loc = htmlspecialchars(BASE_URL . $page);
    echo "<url><loc>{$loc}</loc><changefreq>{$freq}</changefreq><priority>{$priority}</priority></url>";
}

foreach ($scans as $scan) {
    $slugId  = $scan['public_slug'] ?: $scan['id'];
    $loc     = htmlspecialchars(BASE_URL . '/perizia/' . $slugId);
    $lastmod = date('Y-m-d', strtotime($scan['created_at']));
    echo "<url><loc>{$loc}</loc><lastmod>{$lastmod}</lastmod><changefreq>monthly</changefreq><priority>0.6</priority></url>";
}

echo '</urlset>';
