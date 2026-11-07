<?php
/*
 * Tests for the booking picker: default day/time, suggestions, and the jump
 * to the next available day. Fixture hours and fake availability, so neither
 * the café's real timetable nor a database is involved.
 *
 *     php db/test-picker.php
 */
require_once __DIR__ . '/../includes/booking-picker.php';

$passed = $failed = 0;
function check(string $label, $actual, $expected): void
{
    global $passed, $failed;
    if ($actual === $expected) { echo "  PASS  $label\n"; $passed++; return; }
    echo "  FAIL  $label\n          expected: " . json_encode($expected) . "\n          actual:   " . json_encode($actual) . "\n";
    $failed++;
}

$GLOBALS['booking']['service_hours'] = [
    'mon' => ['08:00', '17:00'], 'tue' => ['08:00', '17:00'], 'wed' => ['08:00', '17:00'],
    'thu' => ['08:00', '17:00'], 'fri' => ['08:00', '17:00'], 'sat' => ['09:00', '17:00'], 'sun' => null,
];
$GLOBALS['booking']['slot_minutes'] = 15;
$GLOBALS['booking']['last_booking_before_close'] = 60;
$GLOBALS['booking']['no_bookings_dates'] = [];
$GLOBALS['booking']['usual_time'] = '11:00';
$GLOBALS['booking']['default_guests'] = 2;
$GLOBALS['booking']['same_day_lead_minutes'] = 30;
$GLOBALS['booking']['picker_window_minutes'] = 150;
$GLOBALS['booking']['picker_suggestions'] = 5;
$GLOBALS['booking']['days_ahead'] = 60;

$tz      = new DateTimeZone('Europe/Dublin');
$allFree = fn($d, $slots, $g) => array_fill_keys($slots, true);
// 2026-11-09 is a Monday.
$monMorning = new DateTimeImmutable('2026-11-09 09:00', $tz);
$monEvening = new DateTimeImmutable('2026-11-09 20:00', $tz);
$satEvening = new DateTimeImmutable('2026-11-14 18:30', $tz);

echo "Booking picker\n" . str_repeat('=', 64) . "\n";

$d = picker_defaults($monMorning, $allFree);
check('morning: today, 2 people, usual time', [$d['date'], $d['guests'], $d['time']], ['2026-11-09', 2, '11:00']);

$d = picker_defaults(new DateTimeImmutable('2026-11-09 13:05', $tz), $allFree);
check('after the usual time: today, next free slot', [$d['date'], $d['time']], ['2026-11-09', '13:45']);

$d = picker_defaults($monEvening, $allFree);
check('8pm: moves to tomorrow at the usual time', [$d['date'], $d['time']], ['2026-11-10', '11:00']);

$d = picker_defaults($satEvening, $allFree);
check('Saturday evening skips closed Sunday to Monday', $d['date'], '2026-11-16');

check('five closest times, in clock order',
    picker_suggest(['10:00', '10:15', '10:30', '10:45', '11:00', '11:15', '11:30', '11:45'], '11:00'),
    ['10:30', '10:45', '11:00', '11:15', '11:30']);
check('ties go to the earlier time',
    picker_suggest(['10:45', '11:15'], '11:00', 150, 1), ['10:45']);
check('nothing outside the window', picker_suggest(['08:00'], '16:00'), []);

$r = picker_search('2026-11-09', '11:00', 2, $monMorning, $allFree);
check('open day: suggestions, no message', [count($r['suggestions']), $r['message'], $r['next']], [5, null, null]);

$r = picker_search('2026-11-09', '12:00', 2, $monEvening, $allFree);
check('too late today: says so', $r['message'], 'We are not taking any more online bookings today.');
check('…and offers tomorrow', [$r['next']['date'], $r['next']['label']], ['2026-11-10', 'tomorrow, 10 Nov']);
check('…near the time asked for', $r['next']['suggestions'], ['11:30', '11:45', '12:00', '12:15', '12:30']);

$fullAtLunch = function ($d, $slots, $g) {
    $m = array_fill_keys($slots, true);
    if ($d === '2026-11-10') { foreach ($m as $t => $v) { $m[$t] = false; } }
    return $m;
};
$r = picker_search('2026-11-10', '12:00', 2, $monMorning, $fullAtLunch);
check('full day: availability message', $r['message'], 'At the moment, there is no online availability that day.');
check('full day: next day offered', $r['next']['date'], '2026-11-11');

$r = picker_search('2026-11-15', '11:00', 2, $monMorning, $allFree);
check('closed day: closed message', $r['message'], 'We are closed that day.');

check('labels', [picker_day_label('2026-11-09', $monMorning), picker_day_label('2026-11-10', $monMorning), picker_day_label('2026-11-12', $monMorning)],
    ['Today, 9 Nov', 'Tomorrow, 10 Nov', 'Thu 12 Nov']);
check('date list skips closed Sundays', isset(picker_date_options($monMorning)['2026-11-15']), false);

echo "\n" . str_repeat('=', 64) . "\n";
echo $failed === 0 ? "ALL $passed TESTS PASSED\n" : "$passed passed, $failed FAILED\n";
exit($failed === 0 ? 0 : 1);
