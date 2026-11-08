<?php
/*
 * The home page. Two versions share this file:
 *   index.php       -> photo at the top (the live home page)
 *   home-video.php  -> the kitchen film playing behind the headline, for
 *                      showing the café the alternative. It sets $heroStyle
 *                      to 'video' and includes this file.
 */
$heroStyle       = $heroStyle ?? 'photo';
$pageTitle       = $pageTitle ?? 'The Coffee Bean | Breakfast, Brunch & Coffee on Mary Street, Dublin 1';
$pageDescription = 'The Coffee Bean, 64 Mary Street, Dublin 1. Breakfast and brunch seven days a week, signature sandwiches and wraps, fresh pastries and organic Fairtrade coffee. Gluten free, vegetarian and vegan friendly.';
$pageKeywords    = 'breakfast Dublin 1, brunch Mary Street Dublin, café Dublin city centre, coffee near Henry Street, vegan breakfast Dublin, The Coffee Bean Dublin';
$activePage      = $activePage ?? 'home';
$pagePreload     = $heroStyle === 'video' ? ['images/kitchen-poster-hq.webp'] : ['images/storefront-terrace.webp'];

// Every answer here is true of the café: it comes from the Google listing or
// the café's own menu board. A wrong answer in a rich result is worse than none.
require_once __DIR__ . '/includes/seo.php';
$pageSchema = [
    seo_faq([
        'Where is The Coffee Bean?' =>
            'The Coffee Bean is at 64 Mary Street, Dublin 1, D01 KW22, where Henry Street\'s shopping carries on towards Capel Street.',
        'What are the opening hours?' =>
            'Monday to Friday 7:45 to 17:00, Saturday 8:30 to 17:00 and Sunday 9:00 to 17:00.',
        'When is breakfast served?' =>
            'Breakfast and brunch run from opening until 15:30. Sandwiches and wraps are served until 16:00.',
        'Do I need to book?' =>
            'No, just walk in. For a bigger group, call us on 01 561 3011.',
        'Do you have vegan and gluten free options?' =>
            'Yes. The menu is gluten free, vegetarian and vegan friendly, with dishes like the Simply Vegan focaccia, the Veggie breakfast and gluten free pancakes. Ask staff about allergens.',
        'Can you sit outside?' =>
            'Yes. There is seating inside and on a covered terrace out front on Mary Street.',
    ]),
];
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/directions.php';

$featured = [
    ['img' => 'eggs-royale',     'name' => 'Eggs Royale',          'price' => '14.25', 'alt' => 'Eggs Royale: poached eggs on Irish smoked salmon with hollandaise, on a teal plate'],
    ['img' => 'pancakes',        'name' => 'Buttermilk pancakes',  'price' => '13.45', 'alt' => 'A stack of buttermilk pancakes with strawberries, blueberries, banana, cream and edible flowers'],
    ['img' => 'croque-monsieur', 'name' => 'Croque monsieur',      'price' => '9.25',  'alt' => 'A golden croque monsieur cut in half, with cherry tomatoes and basil'],
];

// Real Google reviews, quoted as written (trimmed for length).
$reviews = [
    ['name' => 'Damián S.',    'text' => 'The brunch at The Coffee Bean was absolutely wonderful! The coffee was excellent, the food tasted like heaven, and everything was prepared with so much care.'],
    ['name' => 'Derek M.',     'text' => 'Everything about The Coffee Bean is lovely. It\'s small but it has character and charm. The food is consistent and so is the attitude.'],
    ['name' => 'Nasthalie T.', 'text' => 'Best coffee art in Dublin 1! The brownies are absolutely delicious, and the staff are the friendliest ever.'],
    ['name' => 'Amelie S.',    'text' => 'Easily my favourite lunch we\'ve had in Dublin so far. Lovely little coffee shop vibe, with superb food, very reasonably priced too.'],
    ['name' => 'Marvin G.',    'text' => 'The food was outstanding and incredibly fresh. Every member of staff was welcoming, attentive, and genuinely friendly.'],
    ['name' => 'Alper K.',     'text' => 'In my 1 week stay in Dublin I came all of my seven days here to have a great breakfast. I can recommend the wraps and the sourdough sandwich very much!'],
];

$strip = [
    ['veggie-breakfast', 'The Veggie: scrambled egg, avocado, mushrooms, beans, hash brown, tomatoes and toast'],
    ['french-toast',     'Brioche French toast with streaky bacon and icing sugar'],
    ['avocado-bacon',    'Smashed avocado on sourdough with streaky bacon and pea shoots'],
    ['porridge',         'Fruity porridge with banana, strawberries and blueberries'],
];

$hasOrdering = !empty($ordering['enabled']) && !empty($ordering['url']);
?>

<?php if ($heroStyle === 'video'): ?>
<section class="hero hero-video-bg">
    <div class="hero-media">
        <video class="hero-video js-lazy-video" muted playsinline loop preload="none"
               poster="images/kitchen-poster-hq.webp" data-src="video/kitchen-hq.mp4" data-autoplay="eager"
               width="576" height="1024" aria-label="Short film: avocado toast with poached eggs being made in the kitchen at The Coffee Bean"></video>
        <button type="button" class="video-toggle" aria-pressed="false" hidden>
            <span class="video-toggle-pause">Pause video</span>
            <span class="video-toggle-play">Play video</span>
        </button>
    </div>
<?php else: ?>
<section class="hero hero-photo-split">
    <div class="hero-photo">
        <?= photo('storefront-terrace', 'The Coffee Bean on Mary Street: a red awning over a busy covered terrace', '(min-width: 900px) 50vw, 100vw', 'width="961" height="1043" fetchpriority="high" decoding="async"') ?>
    </div>
<?php endif; ?>
    <div class="hero-text">
        <p class="hero-eyebrow">64 Mary Street · Dublin 1</p>
        <h1>Breakfast, brunch &amp; proper coffee<span class="hero-h1-aside">seven days a week</span></h1>
        <p class="hero-lede">A small, cosy independent café with a big menu. Free range eggs, organic bread and 100% organic Fairtrade coffee, from 7:45 every weekday morning.</p>
        <div class="hero-actions">
            <a class="btn btn-accent" href="menu.php">See the menu</a>
            <?php if ($hasOrdering): ?>
                <a class="btn btn-outline-light" href="<?= e($ordering['url']) ?>" data-track="order_click"><?= e($ordering['button']) ?></a>
            <?php elseif (!empty($booking['enabled'])): ?>
                <a class="btn btn-outline-light" href="booking.php">Book a table</a>
            <?php else: ?>
                <a class="btn btn-outline-light" href="#map">Find us</a>
            <?php endif; ?>
        </div>
        <?php if (!empty($site['rating'])): ?>
            <a class="rating-chip" href="<?= e($site['maps_url']) ?>" target="_blank" rel="noopener">
                <span class="rating-stars" aria-hidden="true">★★★★★</span>
                <span><strong><?= e($site['rating']) ?></strong> from <?= number_format((int) $site['review_count']) ?> Google reviews</span>
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="section featured">
    <div class="container">
        <div class="section-head">
            <h2>From the kitchen</h2>
            <a class="text-link" href="menu.php">Full menu</a>
        </div>
        <ul class="dish-row">
            <?php foreach ($featured as $dish): ?>
                <li class="dish">
                    <a href="menu.php">
                        <?= photo($dish['img'], $dish['alt']) ?>
                        <span class="dish-name"><?= e($dish['name']) ?></span>
                        <?php if (show_prices() && $dish['price'] !== ''): ?>
                            <span class="dish-price">€<?= e($dish['price']) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section inside">
    <div class="container inside-grid">
        <?= photo('coffee-bar', 'Behind the coffee bar at The Coffee Bean, with hanging plants and pendant lights', '(min-width: 900px) 45vw, 100vw', 'class="inside-photo" width="937" height="1033" loading="lazy" decoding="async"') ?>
        <div class="inside-text">
            <p class="script-kicker">small room, big menu</p>
            <h2>Your table under the red awning</h2>
            <p>We use fresh ingredients from local suppliers, seasonal vegetables, fresh herbs, free range eggs and organic bread in everything we make. The coffee is 100% organic Fairtrade Arabica, and there are oat, almond, coconut and soy milks for anyone who wants them.</p>
            <p>Sit inside, take a stool at the window, or grab a seat on the covered terrace out on Mary Street and watch the city go by. Gluten free, vegetarian or vegan? Just ask.</p>
            <a class="btn btn-accent" href="menu.php#breakfast">See breakfast &amp; brunch</a>
        </div>
    </div>
</section>

<section class="section reviews" aria-labelledby="reviews-title">
    <div class="container">
        <div class="section-head">
            <h2 id="reviews-title">What people say</h2>
            <?php if (!empty($site['rating'])): ?>
                <a class="text-link" href="<?= e($site['maps_url']) ?>" target="_blank" rel="noopener"><?= e($site['rating']) ?> ★ from <?= number_format((int) $site['review_count']) ?> reviews on Google</a>
            <?php endif; ?>
        </div>
        <ul class="review-grid">
            <?php foreach ($reviews as $r): ?>
                <li class="review">
                    <span class="review-stars" aria-label="5 out of 5 stars">★★★★★</span>
                    <blockquote><p><?= e($r['text']) ?></p></blockquote>
                    <p class="review-name"><?= e($r['name']) ?> · Google review</p>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="reviews-cta">
            <a class="btn btn-light" href="<?= e($site['maps_url']) ?>" target="_blank" rel="noopener">Read more on Google</a>
        </p>
    </div>
</section>

<section class="section kitchen">
    <div class="container kitchen-grid">
        <div class="kitchen-media">
            <?php if ($heroStyle === 'video'): ?>
                <video class="kitchen-video js-lazy-video" muted playsinline loop preload="none"
                       poster="images/hero-poster.webp" data-src="video/hero.mp4"
                       width="540" height="960" aria-label="Short film: coffee being made and lemon meringue tarts at The Coffee Bean"></video>
            <?php else: ?>
                <video class="kitchen-video js-lazy-video" muted playsinline loop preload="none"
                       poster="images/kitchen-poster.webp" data-src="video/kitchen.mp4"
                       width="540" height="960" aria-label="Short film: avocado toast with poached eggs being made in the kitchen"></video>
            <?php endif; ?>
        </div>
        <div class="kitchen-text">
            <p class="script-kicker">made to order</p>
            <h2>Every plate made fresh behind the counter</h2>
            <p>Smashed avocado and house pesto on sourdough. Eggs poached to order. Pancakes made from our own buttermilk batter. Pick your bread, pick your sides, and we will make it the way you like it.</p>
            <?= photo('team', 'The Coffee Bean team smiling behind the counter, with fresh croissants and pastries in front', '(min-width: 900px) 40vw, 100vw', 'class="team-photo" width="1158" height="1048" loading="lazy" decoding="async"') ?>
        </div>
    </div>
</section>

<section class="section breakfast">
    <div class="container">
        <div class="section-head">
            <h2>Breakfast till 15:30</h2>
            <a class="text-link" href="gallery.php">More photos</a>
        </div>
        <p class="section-lede">The Dubliner, eggs Benedict, French toast, granola bowls and fruity porridge, then signature sandwiches, wraps and salads at lunch.</p>
        <ul class="photo-strip">
            <?php foreach ($strip as [$file, $alt]): ?>
                <li><a href="gallery.php"><?= photo($file, $alt, '(min-width: 800px) 25vw, 50vw') ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php if ($hasOrdering): ?>
<section class="section cta-band">
    <div class="container cta-inner">
        <h2><?= e($ordering['note']) ?></h2>
        <a class="btn btn-light" href="<?= e($ordering['url']) ?>" data-track="order_click"><?= e($ordering['button']) ?></a>
    </div>
</section>
<?php endif; ?>

<section class="section visit">
    <div class="container visit-grid">
        <div>
            <h2>Visit us</h2>
            <?php render_directions_actions($site); ?>
            <?php if (!empty($site['phone'])): ?>
                <p class="visit-phone">Call <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone'])) ?>"><?= e($site['phone']) ?></a></p>
            <?php endif; ?>
            <?php if (!empty($site['hours'])): ?>
                <dl class="hours hours-large">
                    <?php foreach ($site['hours'] as $days => $time): ?>
                        <div><dt><?= e($days) ?></dt><dd><?= e($time) ?></dd></div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>
            <p>On Mary Street, where Henry Street's shops carry on towards Capel Street, a short walk from the Jervis Luas stop.</p>
        </div>
        <a class="visit-photo" href="gallery.php">
            <?= photo('interior', 'Inside The Coffee Bean: wooden tables, a window counter with stools and hanging plants', '(min-width: 900px) 45vw, 100vw') ?>
            <span>View the gallery</span>
        </a>
    </div>
</section>

<?php render_map_section($site); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
