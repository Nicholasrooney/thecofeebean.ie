<?php
/*
 * "Home with Video": the same home page, with the kitchen film playing behind
 * the headline instead of the photo. It exists so the café can compare the
 * two. It is kept out of Google (noindex, canonical points at the real home
 * page) so it never competes with index.php in search.
 *
 * Remove it from the menu with $site['show_video_home'] = false in config.php.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/seo.php';

if (empty($site['show_video_home'])) {
    header('Location: /', true, 302);
    exit;
}

$heroStyle     = 'video';
$activePage    = 'home-video';
$pageTitle     = 'Home with Video | The Coffee Bean, Mary Street, Dublin 1';
$pageRobots    = 'noindex, follow';
$pageCanonical = seo_url('index.php');

require __DIR__ . '/index.php';
