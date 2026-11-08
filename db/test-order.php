<?php
/*
 * Tests for click and collect: catalogue, server-side pricing, collection
 * times and validation. Fixture hours; no database or email needed.
 *
 *     php db/test-order.php
 */
require_once __DIR__ . '/../includes/order.php';

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
$GLOBALS['ordering'] = array_merge($GLOBALS['ordering'], ['prep_minutes' => 20, 'slot_minutes' => 15, 'last_order_before_close' => 30, 'max_items' => 30]);

// A small fixture menu so prices in the tests never depend on the real board.
$GLOBALS['menu'] = [
    ['id' => 'breakfast', 'title' => 'Breakfast', 'note' => '', 'items' => [
        ['name' => 'Homemade buttermilk pancakes', 'price' => '13.45', 'desc' => '', 'allergens' => [], 'extra' => []],
        ['name' => 'Add to any dish', 'price' => '', 'desc' => '', 'allergens' => [], 'extra' => []],
    ]],
    ['id' => 'coffee', 'title' => 'Coffee', 'note' => '', 'items' => [
        ['name' => 'Latte', 'price' => '3.60', 'price_large' => '4.00', 'desc' => '', 'allergens' => [], 'extra' => []],
    ]],
];

$tz  = new DateTimeZone('Europe/Dublin');
$mon = new DateTimeImmutable('2026-11-09 10:07', $tz);
echo "Click and collect\n" . str_repeat('=', 64) . "\n";

$cat = order_catalog();
$idx = order_index($cat);
check('notes without a price are not orderable', isset($idx['breakfast/add-to-any-dish']), false);
check('ids are section/slug', array_keys($idx), ['breakfast/homemade-buttermilk-pancakes', 'coffee/latte']);
check('price in cent', $idx['coffee/latte']['price'], 360);
check('large size priced from the board', $idx['coffee/latte']['options'][0]['choices'][1]['price'], 40);

$p = order_price_line($idx['coffee/latte'], [0 => 1, 1 => 1, 2 => [0, 1]]);
check('large oat latte with shot and vanilla', $p['unit'], 360 + 40 + 50 + 50 + 50);
check('labels for the kitchen', $p['labels'], ['Large', 'Oat milk', '+ Extra shot', '+ Vanilla syrup']);
check('a made-up option is refused', is_string(order_price_line($idx['coffee/latte'], [0 => 9])), true);
$p = order_price_line($idx['breakfast/homemade-buttermilk-pancakes'], [1 => 1]);
check('gluten free pancakes +€2', $p['unit'], 1545);

$day = order_collection_day($mon);
check('first collection: now + 20 min, rounded up to the quarter', $day['times'][0], '10:30');
check('last collection 30 min before close', end($day['times']), '16:30');
check('label', $day['label'], 'Today, 9 Nov');
$late = order_collection_day(new DateTimeImmutable('2026-11-14 18:00', $tz));
check('Saturday evening: order ahead for Monday (Sunday closed)', [$late['date'], $late['times'][0]], ['2026-11-16', '08:00']);

$good = [
    'basket' => json_encode([['id' => 'coffee/latte', 'qty' => 2, 'choices' => [0 => 1], 'note' => 'extra hot', 'price' => 1]]),
    'collect_date' => '2026-11-09', 'collect_time' => '11:00',
    'name' => 'Aoife Byrne', 'phone' => '087 123 4567', 'email' => 'aoife@example.com', 'notes' => '',
];
[$o, $e] = order_validate($good, $mon, $idx);
check('valid order accepted', $e, []);
check('server total ignores any price sent', $o['total'], 2 * (360 + 40));
check('reference format', (bool) preg_match('/^CB-[0-9A-F]{5}$/', $o['ref']), true);

[$o, $e] = order_validate(['basket' => json_encode([['id' => 'coffee/caviar', 'qty' => 1]])] + $good, $mon, $idx);
check('unknown item refused', isset($e['basket']), true);
[$o, $e] = order_validate(['collect_time' => '10:15'] + $good, $mon, $idx);
check('a time sooner than prep allows is refused', isset($e['collect_time']), true);
[$o, $e] = order_validate(['basket' => json_encode([['id' => 'breakfast/homemade-buttermilk-pancakes', 'qty' => 1]]), 'collect_time' => '16:00'] + $good, $mon, $idx);
check('breakfast after 15:30 refused', $e['collect_time'] ?? '', 'Homemade buttermilk pancakes is served until 15:30. Please pick an earlier time.');
[$o, $e] = order_validate(['phone' => '12', 'email' => 'nope'] + $good, $mon, $idx);
check('phone and email checked', [isset($e['phone']), isset($e['email'])], [true, true]);
[$o, $e] = order_validate(['basket' => '[]'] + $good, $mon, $idx);
check('empty basket refused', isset($e['basket']), true);

echo "\n" . str_repeat('=', 64) . "\n";
echo $failed === 0 ? "ALL $passed TESTS PASSED\n" : "$passed passed, $failed FAILED\n";
exit($failed === 0 ? 0 : 1);
