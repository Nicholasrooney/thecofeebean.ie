<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/seo.php';
require_once __DIR__ . '/analytics.php';

$pageTitle       = $pageTitle       ?? $site['name'];
$pageDescription = $pageDescription ?? 'The Coffee Bean, 64 Mary Street, Dublin 1: breakfast, brunch, sandwiches and organic Fairtrade coffee, seven days a week.';
$activePage      = $activePage      ?? '';
$pageKeywords    = $pageKeywords    ?? '';
$pageSchema      = $pageSchema      ?? [];
$pagePreload     = $pagePreload     ?? [];

$nav = [
    'home'     => ['Home', 'index.php'],
    'home-video' => ['Home with Video', 'home-video.php'],
    'menu'     => ['Menu', 'menu.php'],
    'gallery'  => ['Gallery', 'gallery.php'],
    'catering' => ['Event Catering', 'event-catering.php'],
    'order'    => [$ordering['label'] ?? 'Order for collection', $ordering['url'] ?? ''],
    'booking'  => ['Book a table', 'booking.php'],
];

// Switched off in config, so they leave the menu too.
if (empty($site['show_video_home'])) {
    unset($nav['home-video']);
}
if (empty($catering['enabled'])) {
    unset($nav['catering']);
}
if (empty($booking['enabled'])) {
    unset($nav['booking']);
}
if (empty($ordering['enabled']) || empty($ordering['url'])) {
    unset($nav['order']);
}
?>
<!DOCTYPE html>
<html lang="en-IE">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <?php if (!empty($pageKeywords)): ?>
        <meta name="keywords" content="<?= e($pageKeywords) ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= e($pageRobots ?? 'index, follow') ?>">
    <link rel="canonical" href="<?= e($pageCanonical ?? seo_canonical()) ?>">

    <meta property="og:type" content="<?= $activePage === 'home' ? 'website' : 'article' ?>">
    <meta property="og:site_name" content="<?= e($site['name']) ?>">
    <meta property="og:locale" content="en_IE">
    <meta property="og:url" content="<?= e($pageCanonical ?? seo_canonical()) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:image" content="<?= e(seo_url($pageImage ?? 'images/storefront.jpg')) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#2b3236">
    <?php foreach ($pagePreload as $preload): ?>
        <link rel="preload" as="image" href="<?= e($preload) ?>" fetchpriority="high">
    <?php endforeach; ?>
    <?php render_analytics_head(); ?>
    <link rel="icon" type="image/svg+xml" href="/images/logo.svg">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=DM+Serif+Display:ital@0;1&family=Caveat:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <?php seo_render_schema($pageSchema ?? []); ?>
</head>
<body class="page-<?= e($activePage) ?>">
<a class="skip-link" href="#main">Skip to content</a>

<header class="fascia">
    <div class="fascia-inner">
        <a class="wordmark" href="index.php" aria-label="<?= e($site['name']) ?> home">
            <img class="wordmark-badge" src="/images/logo.svg" alt="" width="56" height="56">
            <span class="wordmark-main">The Coffee Bean</span>
            <span class="wordmark-sub">Mary St</span>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Open navigation">
            <span class="nav-toggle-bars" aria-hidden="true"></span>
        </button>

        <nav id="site-nav" class="site-nav" aria-label="Main">
            <ul>
                <?php foreach ($nav as $key => [$label, $href]): ?>
                    <li>
                        <a href="<?= e($href) ?>"
                           class="<?= $key === 'booking' ? 'nav-book' : ($key === 'order' ? 'nav-order' : '') ?>"
                           <?= $key === 'order' ? 'data-track="order_click"' : '' ?>
                           <?= $activePage === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>

<main id="main">
