<?php
/*
 * Tests for the SEO helpers — mainly the opening-hours parser, which turns the
 * free text in config.php into schema.org specifications. Wrong hours in a
 * Google result send people to a locked door, so this one matters.
 *
 *     php db/test-seo.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/seo.php';

// Fixture hours, so the suite tests the code and not the café's current
// timetable. Editing the real hours in config.php must never turn this red.
$GLOBALS['booking']['service_hours'] = [
    'mon' => ['07:00', '17:00'], 'tue' => ['07:00', '17:00'], 'wed' => ['07:00', '17:00'],
    'thu' => ['07:00', '17:00'], 'fri' => ['07:00', '17:00'], 'sat' => ['08:00', '17:00'],
    'sun' => null,
];
$GLOBALS['booking']['slot_minutes'] = 30;
$GLOBALS['booking']['last_booking_before_close'] = 60;
$GLOBALS['booking']['times'] = hours_all_slots($GLOBALS['booking']['service_hours']);
$GLOBALS['site']['address']  = '64 Mary Street, Dublin 1';

$passed = $failed = 0;

function check(string $label, $actual, $expected): void
{
    global $passed, $failed;
    if ($actual === $expected) {
        echo "  PASS  $label\n";
        $passed++;
    } else {
        echo "  FAIL  $label\n";
        echo "          expected: " . json_encode($expected) . "\n";
        echo "          actual:   " . json_encode($actual) . "\n";
        $failed++;
    }
}

echo "SEO helpers\n" . str_repeat('=', 64) . "\n\nOpening hours parsing\n---------------------\n";

$r = seo_opening_hours(['Mon to Fri' => '9:00 to 16:00']);
check('weekday range expands to five days', count($r[0]['dayOfWeek']), 5);
check('first day is Monday',   $r[0]['dayOfWeek'][0], 'Monday');
check('last day is Friday',    $r[0]['dayOfWeek'][4], 'Friday');
check('open time zero-padded', $r[0]['opens'],  '09:00');
check('close time kept',       $r[0]['closes'], '16:00');

$r = seo_opening_hours(['Saturday' => '09:00 - 17:00']);
check('single day',            $r[0]['dayOfWeek'], ['Saturday']);
check('dash separator works',  $r[0]['closes'], '17:00');

$r = seo_opening_hours(['Sat and Sun' => '10.00 to 18.00']);
check('two listed days',       $r[0]['dayOfWeek'], ['Saturday', 'Sunday']);
check('dots in time handled',  $r[0]['opens'], '10:00');

$r = seo_opening_hours(['Sat to Mon' => '10:00 to 16:00']);
check('range wrapping the week', $r[0]['dayOfWeek'], ['Saturday', 'Sunday', 'Monday']);

$r = seo_opening_hours(['Mon to Fri' => '9:00 to 16:00', 'Sunday' => '11:00 to 15:00']);
check('several rules kept separate', count($r), 2);

check('unparseable text is skipped, not guessed', seo_opening_hours(['Mon' => 'all day']), []);
check('no hours means no claim',                  seo_opening_hours([]), []);

echo "\nURLs and schema\n---------------\n";

check('home canonical has no index.php', seo_url('index.php'), 'https://thecoffeebean.ie/');
check('inner page url',                  seo_url('menu.php'),  'https://thecoffeebean.ie/menu.php');

$node = seo_restaurant_node();
check('typed as both Restaurant and café', $node['@type'], ['Restaurant', 'CafeOrCoffeeShop']);
check('locality comes from the address',  $node['address']['addressLocality'], 'Dublin 1');
check('street address carried through',   $node['address']['streetAddress'], '64 Mary Street');
check('opening hours published', count($node['openingHoursSpecification']), 7);

$byDay = [];
foreach ($node['openingHoursSpecification'] as $spec) {
    $byDay[basename($spec['dayOfWeek'])] = $spec['opens'] . '-' . $spec['closes'];
}
check('Sunday published as closed',  $byDay['Sunday'],    '00:00-00:00');
check('weekday hours',               $byDay['Wednesday'], '07:00-17:00');
check('Saturday opens later',        $byDay['Saturday'],  '08:00-17:00');

echo "
Opening hours as the single source
----------------------------------
";

check('Sunday is closed',          hours_is_open('2026-09-13'), false);
check('Monday is open',            hours_is_open('2026-09-14'), true);
check('no slots on a closed day',  hours_slots_for_date('2026-09-13'), []);

$mon = hours_slots_for_date('2026-09-14');
$sat = hours_slots_for_date('2026-09-19');
check('weekday opens at 07:00',    $mon[0], '07:00');
check('Saturday opens at 08:00',   $sat[0], '08:00');
check('last slot is 16:00 (an hour before a 17:00 close)', end($mon), '16:00');
check('Saturday has two fewer slots than a weekday', count($mon) - count($sat), 2);

check('next open day after Sunday is Monday', hours_next_open_date('2026-09-13'), '2026-09-14');

// Dates taken off the booking form
$savedBlocked = $GLOBALS['booking']['no_bookings_dates'];
$GLOBALS['booking']['no_bookings_dates'] = ['2026-10-05' => '', '2026-10-06' => 'Private function.'];

check('a blocked date gives the standard wording',
    hours_blocked_message('2026-10-05'), 'We are not taking any more bookings that day.');
check('a custom reason is used when given',
    hours_blocked_message('2026-10-06'), 'Private function.');
check('any other date is unaffected',   hours_blocked_message('2026-10-07'), null);
check('a blocked date takes no bookings', hours_takes_bookings('2026-10-05'), false);
check('but the cafe is still open that day', hours_is_open('2026-10-05'), true);
check('an ordinary open day still takes them', hours_takes_bookings('2026-10-08'), true);
check('a closed Sunday takes none either',  hours_takes_bookings('2026-10-04'), false);

// Landing on the 5th should be pointed past the 6th, not into it.
check('the next bookable day skips both blocked days',
    hours_next_open_date('2026-10-05'), '2026-10-07');

$GLOBALS['booking']['no_bookings_dates'] = ['2026-10-05'];
check('a plain list of dates works too',
    hours_blocked_message('2026-10-05'), 'We are not taking any more bookings that day.');

$GLOBALS['booking']['no_bookings_dates'] = $savedBlocked;

// Whatever is configured, check it is usable: real dates, each one actually
// refused. Asserting the dates themselves would mean editing this every time
// a weekend is taken off.
$badDates = $notRefused = [];
foreach ($savedBlocked as $key => $value) {
    $date = is_int($key) ? $value : $key;
    if (!DateTimeImmutable::createFromFormat('!Y-m-d', $date)) {
        $badDates[] = $date;
    }
    if (hours_blocked_message($date) === null) {
        $notRefused[] = $date;
    }
}
check('every blocked date is a real Y-m-d date', $badDates, []);
check('every blocked date is actually refused', $notRefused, []);

$display = hours_display();
check('closed day shown as Closed',  $display['Sunday'], 'Closed');
check('consecutive days grouped',    isset($display['Monday to Friday']), true);
check('Saturday listed on its own',  isset($display['Saturday']), true);

// The staff form offers every slot the cafe ever runs, so they can take a
// booking outside normal service if they want to.
check('staff slot list spans both schedules', in_array('07:00', $booking['times'], true) && in_array('16:00', $booking['times'], true), true);

require __DIR__ . '/../includes/menu-data.php';
$menuNode = seo_menu_node($menu);
$items = array_sum(array_map(fn($s) => count($s['hasMenuItem']), $menuNode['hasMenuSection']));
check('every menu section in schema', count($menuNode['hasMenuSection']), count($menu));
check('every dish in schema',         $items, array_sum(array_map(fn($s) => count($s['items']), $menu)));
$GLOBALS['site']['show_prices'] = false;
$hidden = seo_menu_node($menu);
check('no prices sent to Google while prices are off', isset($hidden['hasMenuSection'][0]['hasMenuItem'][0]['offers']), false);

$GLOBALS['site']['show_prices'] = true;
$shown = seo_menu_node($menu);
check('prices carried in euro when switched on', $shown['hasMenuSection'][0]['hasMenuItem'][0]['offers']['priceCurrency'], 'EUR');
$GLOBALS['site']['show_prices'] = false;

$crumbs = seo_breadcrumbs(['Menu' => 'menu.php']);
check('breadcrumb starts at home', $crumbs['itemListElement'][0]['name'], 'Home');
check('breadcrumb positions',      $crumbs['itemListElement'][1]['position'], 2);

echo "\n" . str_repeat('=', 64) . "\n";
echo $failed === 0 ? "ALL $passed TESTS PASSED\n" : "$passed passed, $failed FAILED\n";
exit($failed === 0 ? 0 : 1);
