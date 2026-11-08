<?php
require_once __DIR__ . '/includes/config.php';
if (empty($booking['enabled'])) {
    header('Location: /', true, 302);
    exit;
}
require __DIR__ . '/includes/booking-handler.php';

$pageTitle       = 'Book a Table | The Coffee Bean, Mary Street, Dublin 1';
$pageDescription = 'Book a table for breakfast or brunch at The Coffee Bean, 64 Mary Street, Dublin 1. Choose your date, time and party size and we will confirm.';
$pageKeywords    = 'book a table Dublin 1, brunch booking Dublin city centre, breakfast Mary Street, The Coffee Bean';
$activePage      = 'booking';

require_once __DIR__ . '/includes/seo.php';
$pageSchema = [seo_breadcrumbs(['Book a table' => 'booking.php'])];
require __DIR__ . '/includes/header.php';

function field_error(array $errors, string $field): string {
    return isset($errors[$field])
        ? '<p class="field-error" id="' . e($field) . '-error">' . e($errors[$field]) . '</p>'
        : '';
}
function error_attrs(array $errors, string $field): string {
    return isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="' . e($field) . '-error"' : '';
}

require_once __DIR__ . '/includes/booking-picker.php';

// The picker's starting point: what they already chose (after a failed submit
// or a no-JavaScript pick), otherwise 2 people at the usual time, today — or
// the next open day once today is effectively over.
$now      = new DateTimeImmutable('now', new DateTimeZone('Europe/Dublin'));
$defaults = picker_defaults($now);
$pGuests  = (int) ($old['date'] !== '' ? $old['guests'] : $defaults['guests']);
$pDate    = $old['date'] !== '' ? $old['date'] : $defaults['date'];
$pTime    = $old['time'] !== '' ? $old['time'] : $defaults['time'];
$dateOpts = picker_date_options($now);
if (!isset($dateOpts[$pDate])) {
    $pDate = $defaults['date'];
}
$timeOpts = hours_slots_for_date($pDate) ?: [$pTime];
if (!in_array($pTime, $timeOpts, true)) {
    $pTime = picker_nearest_slot($timeOpts, $pTime);
}
$search      = picker_search($pDate, $pTime, $pGuests, $now);
$showDetails = $picked || ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['search']));

function picker_buttons(string $date, array $times): string
{
    $out = '';
    foreach ($times as $t) {
        $out .= '<button type="submit" class="slot-btn" name="pick" value="' . e($date . '|' . $t) . '"'
              . ' data-date="' . e($date) . '" data-time="' . e($t) . '" formnovalidate>' . e($t) . '</button>';
    }
    return $out;
}
?>

<div class="container booking-layout">
    <div class="booking-side">
        <h1>Book a table</h1>
        <p>Choose a time that suits and we'll hold a table for you. We'll contact you if anything needs to change.</p>

        <p class="booking-notice"><strong>Pick a time, add your number, done.</strong>
            We only take <?= (int) $booking['max_bookings_per_hour'] ?> bookings an hour, so walk-ins
            are always welcome too.</p>

        <p>When we are busy, tables are held for
            <?= (int) ($booking['table_minutes'] ?? 60) ?> minutes.</p>

        <p>More than <?= (int) $booking['max_guests'] ?> people?
            <?php if ($site['phone']): ?>
                Call us on <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone'])) ?>"><?= e($site['phone']) ?></a>.
            <?php elseif ($site['email']): ?>
                Email <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>.
            <?php elseif (!empty($site['instagram'])): ?>
                Message us on <a href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener">Instagram</a>.
            <?php endif; ?>
        </p>
        <?php if (!empty($site['hours'])): ?>
            <dl class="hours">
                <?php foreach ($site['hours'] as $days => $time): ?>
                    <div><dt><?= e($days) ?></dt><dd><?= e($time) ?></dd></div>
                <?php endforeach; ?>
            </dl>
        <?php endif; ?>

        <img class="booking-photo" src="images/brunch-flatlay-sm.webp" alt="Brunch for the table at The Coffee Bean: eggs Royale, croque monsieur, fruity porridge and French toast" width="640" height="640" loading="lazy" decoding="async">
    </div>

    <div class="booking-main">
        <?php if ($confirmed && isset($_GET['sent'])): ?>
            <?php
            // Reported to analytics as a completed booking. Party size, time
            // and weekday only: never the name, phone or email.
            $confirmedDay = strtok((string) $confirmed['date'], ' ');
            ?>
            <div class="confirmation" role="status"
                 data-track-view="booking_submitted"
                 data-guests="<?= (int) $confirmed['guests'] ?>"
                 data-time="<?= e($confirmed['time']) ?>"
                 data-day="<?= e($confirmedDay) ?>">
                <h2>Booking request sent</h2>
                <p>Thanks, <?= e($confirmed['name']) ?>. We've got your request for
                    <strong><?= (int) $confirmed['guests'] ?> <?= $confirmed['guests'] === 1 ? 'guest' : 'guests' ?></strong>
                    on <strong><?= e($confirmed['date']) ?></strong> at <strong><?= e($confirmed['time']) ?></strong>.</p>
                <p>We'll be in touch if anything needs to change.</p>
                <a class="btn btn-accent" href="menu.php">See the menu</a>
            </div>
        <?php else: ?>

            <?php if (isset($errors['form'])): ?>
                <div class="form-alert" role="alert"><?= e($errors['form']) ?></div>
            <?php elseif ($errors): ?>
                <div class="form-alert" role="alert">Some details need fixing. Check the highlighted fields below.</div>
            <?php endif; ?>

            <form class="booking-form picker<?= $showDetails ? ' has-details' : '' ?>" method="post" action="booking.php#picker" id="picker" novalidate>
                <input type="hidden" name="token" value="<?= e($_SESSION['booking_token']) ?>">
                <div class="hp" aria-hidden="true">
                    <label for="website">Leave this empty</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="picker-card">
                    <h2 class="picker-title">Make a booking</h2>

                    <label class="picker-field">
                        <span class="visually-hidden">Number of people</span>
                        <svg class="picker-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
                        <select id="guests" name="guests"<?= error_attrs($errors, 'guests') ?>>
                            <?php for ($i = 1; $i <= $booking['max_guests']; $i++): ?>
                                <option value="<?= $i ?>" <?= $i === $pGuests ? 'selected' : '' ?>><?= $i ?> <?= $i === 1 ? 'person' : 'people' ?></option>
                            <?php endfor; ?>
                        </select>
                    </label>

                    <div class="picker-row">
                        <label class="picker-field">
                            <span class="visually-hidden">Date</span>
                            <svg class="picker-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg>
                            <select id="date" name="date"<?= error_attrs($errors, 'date') ?>>
                                <?php foreach ($dateOpts as $d => $label): ?>
                                    <option value="<?= e($d) ?>" <?= $d === $pDate ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="picker-field">
                            <span class="visually-hidden">Time</span>
                            <svg class="picker-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
                            <select id="time" name="time"<?= error_attrs($errors, 'time') ?>>
                                <?php foreach ($timeOpts as $t): ?>
                                    <option value="<?= e($t) ?>" <?= $t === $pTime ? 'selected' : '' ?>><?= e($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <?= field_error($errors, 'date') ?>
                    <?= field_error($errors, 'time') ?>
                    <?= field_error($errors, 'guests') ?>

                    <noscript><button class="btn btn-light btn-block picker-search" type="submit" name="search" value="1" formnovalidate>Find a table</button></noscript>

                    <div class="picker-results" aria-live="polite">
                        <?php if ($search['message']): ?>
                            <p class="picker-message"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 7v6M12 16.5v.5" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg><span><?= e($search['message']) ?></span></p>
                        <?php endif; ?>
                        <?php if ($search['suggestions']): ?>
                            <p class="picker-label">Select a time</p>
                            <div class="slot-grid"><?= picker_buttons($pDate, $search['suggestions']) ?></div>
                        <?php elseif ($search['next']): ?>
                            <p class="picker-label">Next available is <strong><?= e($search['next']['label']) ?></strong></p>
                            <div class="slot-grid"><?= picker_buttons($search['next']['date'], $search['next']['suggestions']) ?></div>
                        <?php endif; ?>
                    </div>

                    <p class="picker-foot">Tables are held for <?= (int) ($booking['table_minutes'] ?? 60) ?> minutes when we are busy. More than <?= (int) $booking['max_guests'] ?>? <?php if ($site['phone']): ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone'])) ?>">Call us</a><?php else: ?>Get in touch<?php endif; ?>.</p>
                </div>

                <div class="picker-details" id="picker-details">
                    <div class="picker-summary">
                        <p><strong class="js-summary"><?= $pGuests ?> <?= $pGuests === 1 ? 'person' : 'people' ?> · <?= e(picker_day_label($pDate, $now)) ?> · <?= e($pTime) ?></strong></p>
                        <a class="text-link js-change" href="#picker">Change</a>
                    </div>

                    <div class="field">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required autocomplete="name" maxlength="80"
                               value="<?= e($old['name']) ?>"<?= error_attrs($errors, 'name') ?>>
                        <?= field_error($errors, 'name') ?>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="phone">Mobile number</label>
                            <input type="tel" id="phone" name="phone" required autocomplete="tel" inputmode="tel" maxlength="20"
                                   placeholder="08X XXX XXXX"
                                   value="<?= e($old['phone']) ?>"<?= error_attrs($errors, 'phone') ?>>
                            <?= field_error($errors, 'phone') ?>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required autocomplete="email" inputmode="email" maxlength="120"
                                   value="<?= e($old['email']) ?>"<?= error_attrs($errors, 'email') ?>>
                            <?= field_error($errors, 'email') ?>
                        </div>
                    </div>

                    <details class="picker-extras"<?= ($old['notes'] !== '' || isset($errors['notes'])) ? ' open' : '' ?>>
                        <summary>Add a seating preference or request <span class="optional">(optional)</span></summary>
                        <div class="field">
                            <label for="seating">Seating</label>
                            <select id="seating" name="seating">
                                <?php foreach ($booking['seating'] as $option): ?>
                                    <option value="<?= e($option) ?>" <?= $old['seating'] === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="notes">Anything we should know?</label>
                            <textarea id="notes" name="notes" rows="3" maxlength="500"
                                      placeholder="High chair, allergies, a birthday"<?= error_attrs($errors, 'notes') ?>><?= e($old['notes']) ?></textarea>
                            <?= field_error($errors, 'notes') ?>
                        </div>
                    </details>

                    <div class="field consent">
                        <label class="consent-label" for="marketing_consent">
                            <input type="checkbox" id="marketing_consent" name="marketing_consent" value="1"
                                   <?= $consent ? 'checked' : '' ?>>
                            <span>Email me now and then about offers at The Coffee Bean. Optional, and you can stop any time.</span>
                        </label>
                    </div>

                    <p class="privacy-note">
                        We use your details to manage this booking and nothing else.
                        See our <a href="privacy.php">privacy notice</a>.
                    </p>
                    <button class="btn btn-accent btn-block" type="submit">Confirm booking</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
