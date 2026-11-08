<?php
require_once __DIR__ . '/includes/config.php';
if (empty($booking['enabled'])) {
    header('Location: /', true, 302);
    exit;
}
/*
 * Which time slots still have room on a given date.
 *
 * Called by the booking form whenever the date or party size changes, so the
 * times update without a page reload. Returns only availability — no customer
 * data, nothing worth harvesting.
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/hours.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/bookings.php';
require_once __DIR__ . '/includes/capacity.php';
require_once __DIR__ . '/includes/booking-picker.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$tz     = new DateTimeZone('Europe/Dublin');
$date   = (string) ($_GET['date'] ?? '');
$guests = max(1, min((int) ($_GET['guests'] ?? 1), (int) $booking['max_guests']));

$parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $tz);
if (!$parsed || $parsed->format('Y-m-d') !== $date) {
    http_response_code(400);
    echo json_encode(['error' => 'bad date']);
    exit;
}

// Stay inside the window the form itself allows.
$today   = new DateTimeImmutable('today', $tz);
$lastDay = $today->modify('+' . (int) $booking['days_ahead'] . ' days');
if ($parsed < $today || $parsed > $lastDay) {
    http_response_code(400);
    echo json_encode(['error' => 'date out of range']);
    exit;
}

$now    = new DateTimeImmutable('now', $tz);
$wanted = (string) ($_GET['time'] ?? '');
if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $wanted)) {
    $wanted = (string) ($booking['usual_time'] ?? '11:00');
}

// Every slot that exists that day, and which of them are still free. The
// picker uses the suggestions; the time dropdown uses the full list.
$daySlots = hours_slots_for_date($date);
$free     = picker_free_times($date, $guests, $now);

echo json_encode([
    'date'   => $date,
    'guests' => $guests,
    'closed' => !$daySlots || hours_blocked_message($date) !== null,
    'slots'  => array_combine($daySlots, array_map(fn($t) => in_array($t, $free, true), $daySlots)) ?: new stdClass(),
    'search' => picker_search($date, $wanted, $guests, $now),
]);
