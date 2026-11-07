<?php
$pageTitle       = 'Gallery | The Coffee Bean, Mary Street, Dublin 1';
$pageDescription = 'Photos from The Coffee Bean on Mary Street, Dublin 1: eggs Royale, buttermilk pancakes, croque monsieur, fresh pastries, latte art and our red-awning terrace.';
$pageKeywords    = 'The Coffee Bean Dublin photos, brunch Dublin 1, café Mary Street, breakfast Dublin city centre';
$activePage      = 'gallery';

require_once __DIR__ . '/includes/seo.php';
$pageSchema = [seo_breadcrumbs(['Gallery' => 'gallery.php'])];

require __DIR__ . '/includes/header.php';

// To add a photo: make a 1200px .webp and a 640px "-sm.webp" in /images
// (see docs/COFFEEBEAN-NOTES.md), then add a line here without the extension.
$photos = [
    ['eggs-royale',        'Eggs Royale with Irish smoked salmon and hollandaise'],
    ['storefront-terrace', 'The covered terrace under the red awning on Mary Street'],
    ['pancakes',           'Buttermilk pancakes with fresh fruit, cream and edible flowers'],
    ['cappuccino',         'Cappuccino with a chocolate dusting'],
    ['croque-monsieur',    'Croque monsieur with honey roasted ham and Monterey Jack'],
    ['coffee-bar',         'Behind the coffee bar'],
    ['pastries',           'Croissants, pain au chocolat and scones'],
    ['eggs-benedict',      'Eggs Benedict with roasted cherry tomatoes'],
    ['brunch-flatlay',     'Brunch for the table: eggs Royale, croque monsieur, porridge and French toast'],
    ['interior',           'Window counter and tables inside'],
    ['french-toast',       'Brioche French toast with streaky bacon'],
    ['veggie-breakfast',   'The Veggie breakfast'],
    ['almond-croissant',   'Almond croissant'],
    ['hanging-sign',       'The Coffee Bean sign over Mary Street'],
    ['avocado-bacon',      'Avocado toast with streaky bacon'],
    ['porridge-pastries',  'Fruity porridge with fresh pastries and berries'],
    ['team',               'The team behind the counter'],
    ['eggs-royale-close',  'Eggs Royale, up close'],
    ['croque-close',       'Croque monsieur, fresh from the grill'],
    ['storefront',         'The shopfront at 64 Mary Street'],
    ['porridge',           'Porridge with banana, strawberries and blueberries'],
];
?>

<section class="page-intro">
    <div class="container">
        <h1>Gallery</h1>
        <?php if (!empty($site['instagram'])): ?>
            <p>More on <a class="text-link" href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener">Instagram</a>.</p>
        <?php else: ?>
            <p>A look at the food, the coffee and the café on Mary Street.</p>
        <?php endif; ?>
    </div>
</section>

<div class="container">
    <ul class="gallery">
        <?php foreach ($photos as $i => [$file, $caption]): ?>
            <li>
                <button type="button" class="gallery-item" data-index="<?= $i ?>"
                        data-src="images/<?= e($file) ?>.webp" data-caption="<?= e($caption) ?>"
                        aria-label="Open photo: <?= e($caption) ?>">
                    <img src="images/<?= e($file) ?>-sm.webp" alt="<?= e($caption) ?>" loading="lazy" decoding="async">
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<dialog class="lightbox" aria-label="Photo viewer">
    <figure>
        <img src="" alt="">
        <figcaption></figcaption>
    </figure>
    <button type="button" class="lb-btn lb-prev" aria-label="Previous photo">&#8249;</button>
    <button type="button" class="lb-btn lb-next" aria-label="Next photo">&#8250;</button>
    <button type="button" class="lb-btn lb-close" aria-label="Close">&times;</button>
</dialog>

<?php require __DIR__ . '/includes/footer.php'; ?>
