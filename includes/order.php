<?php
/*
 * Click and collect: the catalogue, prices, options, collection times and
 * order validation.
 *
 * The menu itself lives in menu-data.php. This file turns it into something
 * orderable (ids, prices in cent, option groups) and is the ONLY place a total
 * is worked out for real. The page's JavaScript shows running totals for
 * convenience, but the server ignores any price the browser sends: it gets
 * item ids, choices and quantities, and prices them again from here.
 *
 * No database needed: orders are appended to data/orders.csv (blocked from the
 * web by data/.htaccess) and emailed to the café and the customer.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/hours.php';
require_once __DIR__ . '/menu-data.php';

/** "Eggs Benedict" -> "eggs-benedict" */
function order_slug(string $s): string
{
    $s = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $s) ?: $s);
    return trim(preg_replace('/[^a-z0-9]+/', '-', $s), '-');
}

function order_cents($euro): int
{
    return (int) round(((float) $euro) * 100);
}

function order_money(int $cents): string
{
    return '€' . number_format($cents / 100, 2);
}

/** Last collection time for each menu section (kitchen hours on the board). */
function order_section_until(): array
{
    return ['breakfast' => '15:30', 'sandwiches' => '16:00', 'salads' => '16:00'];
}

/** Photos for the order page, by item name. Only real photos of the dish. */
function order_photos(): array
{
    return [
        'The Veggie'                   => 'veggie-breakfast',
        'Eggs Benedict'                => 'eggs-benedict',
        'Eggs Royale'                  => 'eggs-royale-close',
        'Homemade buttermilk pancakes' => 'pancakes',
        'Brioche French toast'         => 'french-toast',
        'Croque monsieur'              => 'croque-close',
        'Avocado toast & egg'          => 'avocado-bacon',
        'Fruity porridge'              => 'porridge',
        'Cappuccino'                   => 'cappuccino',
    ];
}

/**
 * Option groups for one dish. A group is "one" (pick exactly one, the first
 * is the default) or "many" (any number). Prices are extras in cent.
 */
function order_options_for(string $sectionId, array $item): array
{
    $name   = $item['name'];
    $groups = [];
    $one    = fn(string $label, array $choices) => ['label' => $label, 'type' => 'one', 'choices' => $choices];
    $many   = fn(string $label, array $choices) => ['label' => $label, 'type' => 'many', 'choices' => $choices];
    $c      = fn(string $label, float $extra = 0) => ['label' => $label, 'price' => order_cents($extra)];

    $eggs = $one('Eggs', [$c('Fried'), $c('Scrambled')]);

    switch ($name) {
        case 'The Dubliner':
        case 'The Veggie':
            $groups[] = $eggs;
            $groups[] = $one('Drink included', [$c('Tea'), $c('Americano'), $c('Can')]);
            break;
        case 'Eggs Benedict':
            $groups[] = $one('Style', [$c('Honey roasted ham'), $c('Avocado')]);
            break;
        case '3 egg omelette':
            $groups[] = $one('Served with', [$c('Side salad'), $c('Toast')]);
            break;
        case 'Homemade buttermilk pancakes':
            $groups[] = $one('Topping', [$c('Streaky bacon'), $c('Seasonal fruit')]);
            $groups[] = $one('Batter', [$c('Regular'), $c('Gluten free', 2.00)]);
            break;
        case 'Brioche French toast':
            $groups[] = $one('Topping', [$c('Streaky bacon'), $c('Berries & walnut')]);
            break;
        case 'Breakfast bagel':
        case 'Veggie bagel':
            $groups[] = $eggs;
            break;
        case 'Avocado toast & egg':
            $groups[] = $one('Eggs', [$c('Poached'), $c('Fried'), $c('Scrambled')]);
            break;
        case 'Fruity porridge':
            $groups[] = $one('Made with', [$c('Dairy milk'), $c('Oat milk', 1), $c('Almond milk', 1), $c('Coconut milk', 1), $c('Soy milk', 1)]);
            break;
        case 'BLTT (white or brown)':
            $groups[] = $one('Bread', [$c('White'), $c('Brown')]);
            break;
    }

    // Sides for the cooked breakfasts.
    $noSides = ['Fruity porridge', 'Greek yoghurt & granola bowl'];
    if ($sectionId === 'breakfast' && !in_array($name, $noSides, true)) {
        $groups[] = $many('Add extras', [
            $c('Streaky bacon', 4), $c('Sausages', 4), $c('Eggs', 4), $c('Irish smoked salmon', 4.5),
            $c('Grilled halloumi', 3), $c('Avocado', 3),
            $c('Hash brown', 2), $c('Beans', 2), $c('Mushrooms', 2), $c('Cheese', 2),
        ]);
    }

    // Drinks: size, milk and extras.
    if (in_array($sectionId, ['coffee', 'iced'], true)) {
        if (!empty($item['price_large'])) {
            $groups[] = $one('Size', [$c('Regular'), ['label' => 'Large', 'price' => order_cents($item['price_large']) - order_cents($item['price'])]]);
        }
        $noMilk = ['Espresso', 'Americano', 'Iced Americano', 'Organic herbal tea'];
        if (!in_array($name, $noMilk, true)) {
            $groups[] = $one('Milk', [$c('Dairy milk'), $c('Oat milk', 0.5), $c('Almond milk', 0.5), $c('Coconut milk', 0.5), $c('Soy milk', 0.5)]);
        }
        if (!in_array($name, ['Organic herbal tea', 'Breakfast tea'], true)) {
            $groups[] = $many('Extras', [
                $c('Extra shot', 0.5), $c('Vanilla syrup', 0.5), $c('Caramel syrup', 0.5), $c('Hazelnut syrup', 0.5),
                $c('Pumpkin spice syrup', 0.5), $c('Gingerbread syrup', 0.5), $c('Cream', 0.5), $c('Marshmallows', 0.5),
            ]);
        }
    }

    return $groups;
}

/**
 * Everything orderable, by section. Items with no price (notes such as
 * "Add to any dish") are left out.
 *
 * @return array<int, array{id:string,title:string,until:?string,items:array}>
 */
function order_catalog(): array
{
    global $menu;
    $until  = order_section_until();
    $photos = order_photos();
    $out    = [];

    foreach ($menu as $section) {
        $items = [];
        foreach ($section['items'] as $item) {
            if (($item['price'] ?? '') === '') {
                continue;
            }
            $items[] = [
                'id'      => $section['id'] . '/' . order_slug($item['name']),
                'name'    => $item['name'],
                'desc'    => $item['desc'],
                'price'   => order_cents($item['price']),
                'photo'   => $photos[$item['name']] ?? null,
                'options' => order_options_for($section['id'], $item),
            ];
        }
        if ($items) {
            $out[] = [
                'id'    => $section['id'],
                'title' => $section['title'],
                'until' => $until[$section['id']] ?? null,
                'items' => $items,
            ];
        }
    }
    return $out;
}

/** Flat id => item (with its section's 'until') lookup. */
function order_index(?array $catalog = null): array
{
    $index = [];
    foreach ($catalog ?? order_catalog() as $section) {
        foreach ($section['items'] as $item) {
            $index[$item['id']] = $item + ['until' => $section['until'], 'section' => $section['title']];
        }
    }
    return $index;
}

/**
 * Collection times on offer: from now + prep time, every slot, up to the last
 * order before closing. If today is done, the next open day.
 *
 * @return array{date:string,label:string,times:string[]}|null
 */
function order_collection_day(DateTimeImmutable $now): ?array
{
    global $ordering;
    $tz      = new DateTimeZone('Europe/Dublin');
    $step    = (int) ($ordering['slot_minutes'] ?? 15);
    $prep    = (int) ($ordering['prep_minutes'] ?? 20);
    $lastCut = (int) ($ordering['last_order_before_close'] ?? 30);

    for ($i = 0; $i <= 7; $i++) {
        $day  = (new DateTimeImmutable($now->format('Y-m-d'), $tz))->modify("+$i days");
        $date = $day->format('Y-m-d');
        $win  = hours_for_date($date);
        if (!$win) {
            continue;
        }
        $open  = new DateTimeImmutable("$date {$win[0]}", $tz);
        $close = (new DateTimeImmutable("$date {$win[1]}", $tz))->modify("-$lastCut minutes");
        $from  = $i === 0 ? max($open, $now->modify("+$prep minutes")) : $open;

        // Round up to the slot grid (e.g. 13:07 -> 13:15).
        $mins = (int) $from->format('H') * 60 + (int) $from->format('i');
        $mins = (int) (ceil($mins / $step) * $step);
        $t    = new DateTimeImmutable($date . ' ' . sprintf('%02d:%02d', intdiv($mins, 60), $mins % 60), $tz);

        $times = [];
        for (; $t <= $close; $t = $t->modify("+$step minutes")) {
            $times[] = $t->format('H:i');
        }
        if ($times) {
            $label = $i === 0 ? 'Today' : ($i === 1 ? 'Tomorrow' : $day->format('l'));
            return ['date' => $date, 'label' => $label . ', ' . $day->format('j M'), 'times' => $times];
        }
    }
    return null;
}

/**
 * Prices one basket line from its item id and chosen option indexes.
 *
 * $choices is group index => choice index (one) or list of choice indexes (many).
 *
 * @return array{unit:int, labels:string[]}|string  a string is an error message
 */
function order_price_line(array $item, array $choices)
{
    $unit   = $item['price'];
    $labels = [];
    foreach ($item['options'] as $g => $group) {
        $picked = $choices[$g] ?? ($group['type'] === 'one' ? 0 : []);
        if ($group['type'] === 'one') {
            if (!is_int($picked) && !ctype_digit((string) $picked)) {
                return 'Please choose ' . strtolower($group['label']) . ' for ' . $item['name'] . '.';
            }
            $choice = $group['choices'][(int) $picked] ?? null;
            if (!$choice) {
                return 'Please choose ' . strtolower($group['label']) . ' for ' . $item['name'] . '.';
            }
            $unit    += $choice['price'];
            $labels[] = $choice['label'];
        } else {
            foreach (array_unique(array_map('intval', (array) $picked)) as $ci) {
                $choice = $group['choices'][$ci] ?? null;
                if (!$choice) {
                    return 'One of the extras for ' . $item['name'] . ' is no longer available.';
                }
                $unit    += $choice['price'];
                $labels[] = '+ ' . $choice['label'];
            }
        }
    }
    return ['unit' => $unit, 'labels' => $labels];
}

/**
 * Turns the posted basket (JSON) and details into a clean order, or errors.
 *
 * @return array{0: ?array, 1: array<string,string>}  [order, errors]
 */
function order_validate(array $post, DateTimeImmutable $now, ?array $index = null): array
{
    global $ordering;
    $index  = $index ?? order_index();
    $errors = [];

    $basket = json_decode((string) ($post['basket'] ?? ''), true);
    if (!is_array($basket) || !$basket) {
        return [null, ['basket' => 'Your basket is empty.']];
    }

    $lines = [];
    $total = 0;
    $count = 0;
    $latest = null;   // the earliest "served until" across the basket
    foreach (array_slice($basket, 0, 50) as $row) {
        $item = $index[(string) ($row['id'] ?? '')] ?? null;
        if (!$item) {
            $errors['basket'] = 'Something in your basket is no longer on the menu. Please remove it and try again.';
            continue;
        }
        $qty = max(1, min(20, (int) ($row['qty'] ?? 1)));
        $priced = order_price_line($item, is_array($row['choices'] ?? null) ? $row['choices'] : []);
        if (is_string($priced)) {
            $errors['basket'] = $priced;
            continue;
        }
        $note = trim(mb_substr(preg_replace('/\s+/', ' ', (string) ($row['note'] ?? '')), 0, 140));
        $lines[] = [
            'name'   => $item['name'],
            'qty'    => $qty,
            'unit'   => $priced['unit'],
            'labels' => $priced['labels'],
            'note'   => $note,
            'total'  => $priced['unit'] * $qty,
        ];
        $total += $priced['unit'] * $qty;
        $count += $qty;
        if ($item['until'] && ($latest === null || $item['until'] < $latest[0])) {
            $latest = [$item['until'], $item['name']];
        }
    }
    if ($count > (int) ($ordering['max_items'] ?? 30)) {
        $errors['basket'] = 'That is a big order! Please ring us for more than ' . (int) $ordering['max_items'] . ' items.';
    }

    // Collection time: must be one we offer right now.
    $day  = order_collection_day($now);
    $time = (string) ($post['collect_time'] ?? '');
    if (!$day) {
        $errors['collect_time'] = 'We are not taking orders at the moment.';
    } elseif (($post['collect_date'] ?? '') !== $day['date'] || !in_array($time, $day['times'], true)) {
        $errors['collect_time'] = 'Please pick one of the collection times shown.';
    } elseif ($latest && $time > $latest[0]) {
        $errors['collect_time'] = $latest[1] . ' is served until ' . $latest[0] . '. Please pick an earlier time.';
    }

    $name  = trim((string) ($post['name'] ?? ''));
    $phone = trim((string) ($post['phone'] ?? ''));
    $email = trim((string) ($post['email'] ?? ''));
    $notes = trim(mb_substr((string) ($post['notes'] ?? ''), 0, 300));

    if ($name === '' || mb_strlen($name) > 80) {
        $errors['name'] = 'Please tell us your name for the order.';
    }
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) < 7 || strlen($digits) > 15) {
        $errors['phone'] = 'Enter a phone number we can call if there is a problem with the order.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
        $errors['email'] = 'Enter an email address for your receipt, like name@example.com.';
    }

    if ($errors) {
        return [null, $errors];
    }

    return [[
        'ref'          => 'CB-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)),
        'created'      => $now->format('Y-m-d H:i'),
        'collect_date' => $day['date'],
        'collect_time' => $time,
        'collect_label'=> $day['label'] . ' at ' . $time,
        'name'         => $name,
        'phone'        => $phone,
        'email'        => $email,
        'notes'        => $notes,
        'lines'        => $lines,
        'total'        => $total,
        'marketing'    => !empty($post['marketing_consent']),
    ], []];
}

/** The order as plain text, for emails and the CSV. */
function order_lines_text(array $order): string
{
    $out = [];
    foreach ($order['lines'] as $l) {
        $out[] = $l['qty'] . ' x ' . $l['name'] . '  ' . order_money($l['total']);
        foreach ($l['labels'] as $label) {
            $out[] = '      ' . $label;
        }
        if ($l['note'] !== '') {
            $out[] = '      Note: ' . $l['note'];
        }
    }
    return implode("\n", $out);
}

/** Appends the order to data/orders.csv. Returns false if it could not. */
function order_save_csv(array $order): bool
{
    global $ordering;
    $file = $ordering['csv_file'] ?? (__DIR__ . '/../data/orders.csv');
    $new  = !is_file($file);
    $fh   = @fopen($file, 'a');
    if (!$fh) {
        return false;
    }
    flock($fh, LOCK_EX);
    if ($new) {
        fputcsv($fh, ['ref', 'placed', 'collect_date', 'collect_time', 'name', 'phone', 'email', 'items', 'total', 'notes', 'marketing']);
    }
    $items = str_replace("\n", ' | ', preg_replace('/ {2,}/', ' ', order_lines_text($order)));
    fputcsv($fh, [
        $order['ref'], $order['created'], $order['collect_date'], $order['collect_time'],
        $order['name'], $order['phone'], $order['email'], $items,
        number_format($order['total'] / 100, 2, '.', ''), $order['notes'], $order['marketing'] ? 'yes' : 'no',
    ]);
    flock($fh, LOCK_UN);
    fclose($fh);
    return true;
}
