<?php
require __DIR__ . '/includes/order-handler.php';

if (empty($ordering['enabled'])) {
    header('Location: /', true, 302);
    exit;
}

$pageTitle       = 'Order for Collection | The Coffee Bean, Mary Street, Dublin 1';
$pageDescription = 'Order breakfast, brunch, sandwiches and coffee from The Coffee Bean on Mary Street, Dublin 1. Pick a collection time and pay when you collect.';
$pageKeywords    = 'click and collect Dublin 1, order breakfast Mary Street, coffee to collect Dublin city centre, The Coffee Bean order';
$activePage      = 'order';
$pageScripts     = ['js/order.js'];

require_once __DIR__ . '/includes/seo.php';
$pageSchema = [seo_breadcrumbs(['Order for collection' => 'order.php'])];

$catalog = order_catalog();
$day     = order_collection_day($now);
$preview = !show_prices();

require __DIR__ . '/includes/header.php';

function order_err(array $errors, string $k): string
{
    return isset($errors[$k]) ? '<p class="field-error" id="' . e($k) . '-error">' . e($errors[$k]) . '</p>' : '';
}
?>

<?php if ($orderDone): ?>
<section class="order-done" data-clear-basket>
    <div class="container order-done-inner">
        <div class="order-done-tick" aria-hidden="true">✓</div>
        <h1>Thanks<?= !empty($orderDone['name']) ? ', ' . e(explode(' ', $orderDone['name'])[0]) : '' ?>! Your order is in.</h1>
        <?php if (!empty($orderDone['ref'])): ?>
            <dl class="order-done-facts">
                <div><dt>Order</dt><dd><?= e($orderDone['ref']) ?></dd></div>
                <div><dt>Collect</dt><dd><?= e($orderDone['when']) ?></dd></div>
                <div><dt>Pay when you collect</dt><dd><?= e($orderDone['total']) ?></dd></div>
            </dl>
            <p>We have emailed a receipt to <strong><?= e($orderDone['email']) ?></strong>. Come to the counter at
               <?= e($site['address']) ?> and give your name or order number.</p>
        <?php endif; ?>
        <p>Need to change something? Ring us on <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone'])) ?>"><?= e($site['phone']) ?></a>.</p>
        <p><a class="btn btn-accent" href="order.php">Start a new order</a> <a class="btn btn-light btn-outline-dark" href="index.php">Back to home</a></p>
    </div>
</section>
<?php else: ?>

<section class="order-top">
    <div class="container">
        <h1>Order for collection</h1>
        <p class="order-sub"><?= e($site['address']) ?> · Pay when you collect, card or cash</p>
        <?php if (!$day || empty($ordering['accepting'])): ?>
            <p class="order-status is-closed">Online ordering is closed right now. Ring us on <?= e($site['phone']) ?>.</p>
        <?php elseif ($day['label'] === 'Today, ' . $now->format('j M')): ?>
            <p class="order-status is-open"><span class="dot" aria-hidden="true"></span> Open for orders · collect from <?= e($day['times'][0]) ?> today</p>
        <?php else: ?>
            <p class="order-status is-ahead">We're closed now · order ahead for <?= e($day['label']) ?> from <?= e($day['times'][0]) ?></p>
        <?php endif; ?>
        <?php if ($preview): ?>
            <p class="order-preview"><strong>Preview:</strong> prices are still being confirmed with the café.</p>
        <?php endif; ?>
    </div>
</section>

<nav class="order-tabs" aria-label="Menu sections">
    <div class="container order-tabs-inner">
        <?php foreach ($catalog as $i => $section): ?>
            <a href="#sec-<?= e($section['id']) ?>"<?= $i === 0 ? ' class="is-active"' : '' ?>><?= e($section['title']) ?></a>
        <?php endforeach; ?>
    </div>
</nav>

<div class="container order-layout">
    <div class="order-menu">
        <noscript><p class="order-preview">Online ordering needs JavaScript. Please ring us on <?= e($site['phone']) ?> to order.</p></noscript>
        <?php foreach ($catalog as $section): ?>
            <section class="order-section" id="sec-<?= e($section['id']) ?>" aria-labelledby="h-<?= e($section['id']) ?>">
                <h2 id="h-<?= e($section['id']) ?>"><?= e($section['title']) ?></h2>
                <?php if ($section['until']): ?>
                    <p class="order-until">Collect by <?= e($section['until']) ?></p>
                <?php endif; ?>
                <ul class="order-items">
                    <?php foreach ($section['items'] as $item): ?>
                        <li>
                            <button type="button" class="order-item" data-id="<?= e($item['id']) ?>"
                                    aria-label="<?= e($item['name']) ?>, <?= e(order_money($item['price'])) ?>. Add to order">
                                <span class="order-item-text">
                                    <span class="order-item-name"><?= e($item['name']) ?></span>
                                    <?php if ($item['desc']): ?><span class="order-item-desc"><?= e($item['desc']) ?></span><?php endif; ?>
                                    <span class="order-item-price"><?= e(order_money($item['price'])) ?><?= $item['options'] ? '<span class="order-item-more"> · options</span>' : '' ?></span>
                                </span>
                                <?php if ($item['photo']): ?>
                                    <img class="order-item-img" src="images/<?= e($item['photo']) ?>-sm.webp" alt="" loading="lazy" decoding="async" width="96" height="96">
                                <?php endif; ?>
                                <span class="order-item-add" aria-hidden="true">+</span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>
    </div>

    <aside class="order-basket" id="basket" aria-label="Your order">
        <form class="basket-card" method="post" action="order.php#basket" id="checkout" novalidate>
            <div class="basket-head">
                <h2>Your order</h2>
                <button type="button" class="basket-close" aria-label="Close basket">&times;</button>
            </div>

            <?php if (!empty($orderErrors['form']) || !empty($orderErrors['basket'])): ?>
                <p class="form-error" role="alert"><?= e($orderErrors['form'] ?? $orderErrors['basket']) ?></p>
            <?php endif; ?>

            <p class="basket-empty">Your basket is empty. Tap a dish to add it.</p>
            <ul class="basket-lines" aria-live="polite"></ul>

            <div class="basket-body">
                <div class="basket-total"><span>Total</span><strong class="js-total">€0.00</strong></div>
                <p class="basket-pay">Pay when you collect · card or cash</p>

                <input type="hidden" name="token" value="<?= e($_SESSION['order_token']) ?>">
                <input type="hidden" name="basket" value="<?= e($orderOld['basket']) ?>">
                <input type="hidden" name="collect_date" value="<?= e($day['date'] ?? '') ?>">
                <div class="hp" aria-hidden="true">
                    <label for="website">Leave this empty</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <?php if ($day && !empty($ordering['accepting'])): ?>
                    <div class="field">
                        <label for="collect_time">Collection time · <?= e($day['label']) ?></label>
                        <select id="collect_time" name="collect_time"<?= isset($orderErrors['collect_time']) ? ' aria-invalid="true" aria-describedby="collect_time-error"' : '' ?>>
                            <?php foreach ($day['times'] as $i => $t): ?>
                                <option value="<?= e($t) ?>" <?= ($orderOld['collect_time'] === $t || ($orderOld['collect_time'] === '' && $i === 0)) ? 'selected' : '' ?>>
                                    <?= e($t) ?><?= $i === 0 && str_starts_with($day['label'], 'Today') ? ' (soonest)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= order_err($orderErrors, 'collect_time') ?>
                    </div>

                    <button type="button" class="btn btn-accent btn-block js-continue">Continue · <span class="js-total">€0.00</span></button>

                    <div class="checkout-details">
                        <div class="field">
                            <label for="o-name">Name for the order</label>
                            <input type="text" id="o-name" name="name" autocomplete="name" maxlength="80" required value="<?= e($orderOld['name']) ?>"<?= isset($orderErrors['name']) ? ' aria-invalid="true"' : '' ?>>
                            <?= order_err($orderErrors, 'name') ?>
                        </div>
                        <div class="field">
                            <label for="o-phone">Mobile number</label>
                            <input type="tel" id="o-phone" name="phone" autocomplete="tel" inputmode="tel" maxlength="20" required placeholder="08X XXX XXXX" value="<?= e($orderOld['phone']) ?>"<?= isset($orderErrors['phone']) ? ' aria-invalid="true"' : '' ?>>
                            <?= order_err($orderErrors, 'phone') ?>
                        </div>
                        <div class="field">
                            <label for="o-email">Email for your receipt</label>
                            <input type="email" id="o-email" name="email" autocomplete="email" inputmode="email" maxlength="120" required value="<?= e($orderOld['email']) ?>"<?= isset($orderErrors['email']) ? ' aria-invalid="true"' : '' ?>>
                            <?= order_err($orderErrors, 'email') ?>
                        </div>
                        <div class="field">
                            <label for="o-notes">Note for the kitchen <span class="optional">(optional)</span></label>
                            <textarea id="o-notes" name="notes" rows="2" maxlength="300" placeholder="Allergies, cutlery, anything else"><?= e($orderOld['notes']) ?></textarea>
                        </div>
                        <label class="consent-label" for="o-consent">
                            <input type="checkbox" id="o-consent" name="marketing_consent" value="1">
                            <span>Email me now and then about offers. Optional.</span>
                        </label>
                        <p class="privacy-note">We use your details for this order only. See our <a href="privacy.php">privacy notice</a>.</p>
                        <button class="btn btn-accent btn-block js-place" type="submit">Place order · <span class="js-total">€0.00</span></button>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </aside>
</div>

<button type="button" class="basket-bar" hidden>
    <span class="basket-bar-count js-count">0</span>
    <span>View order</span>
    <strong class="js-total">€0.00</strong>
</button>

<dialog class="item-dialog" aria-labelledby="item-dialog-title">
    <form method="dialog" class="item-dialog-inner"></form>
</dialog>

<script type="application/json" id="order-data"><?= json_encode([
    'catalog'    => $catalog,
    'times'      => $day['times'] ?? [],
    'hasErrors'  => (bool) $orderErrors,
    'showDetails'=> $_SERVER['REQUEST_METHOD'] === 'POST',
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
