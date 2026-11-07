<?php
/*
 * The quick "Make a booking" picker (OpenTable style).
 *
 * People choose party size, date and a rough time; we answer with a handful of
 * real, free times close to what they asked for. If that day has nothing near
 * their time (or it is too late in the day), we jump ahead and offer the next
 * day that does.
 *
 * Everything here is pure logic over the same sources the rest of the booking
 * code uses (hours.php for opening hours, capacity.php for how full an hour
 * is), so the picker can never offer a time the form would then refuse.
 * slots.php serves it as JSON; booking.php uses it for the first paint.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/hours.php';

/**
 * Free times for a date, with anything already past (or about to be) removed
 * when the date is today.
 *
 * @param callable|null $availability fn(string $date, array $slots, int $guests): array<string,bool>
 *                                    Injected so tests run without a database.
 * @return string[] free times, in order
 */
function picker_free_times(string $date, int $guests, DateTimeImmutable $now, ?callable $availability = null): array
{
    global $booking;

    $slots = hours_slots_for_date($date);
    if (!$slots || hours_blocked_message($date) !== null) {
        return [];
    }

    $availability = $availability ?? 'picker_capacity_availability';
    $map = $availability($date, $slots, $guests);

    // Same day: nothing in the next lead_minutes, so nobody books a table for
    // five minutes from now and arrives to find it was never seen.
    if ($date === $now->format('Y-m-d')) {
        $lead   = (int) ($booking['same_day_lead_minutes'] ?? 30);
        $cutoff = $now->modify("+$lead minutes")->format('H:i');
        foreach ($map as $t => $free) {
            if ($t < $cutoff) {
                $map[$t] = false;
            }
        }
    }

    return array_keys(array_filter($map));
}

/** Live capacity, or everything free while there is no database yet. */
function picker_capacity_availability(string $date, array $slots, int $guests): array
{
    global $booking;
    try {
        require_once __DIR__ . '/db.php';
        require_once __DIR__ . '/bookings.php';
        require_once __DIR__ . '/capacity.php';
        return capacity_slot_availability($date, $slots, (int) ($booking['max_bookings_per_hour'] ?? 0), max(1, $guests));
    } catch (Throwable $e) {
        return array_fill_keys($slots, true);
    }
}

/**
 * Up to $max free times closest to the time asked for, within $window minutes
 * either side, returned in clock order.
 */
function picker_suggest(array $free, string $wanted, ?int $window = null, ?int $max = null): array
{
    global $booking;
    $window = $window ?? (int) ($booking['picker_window_minutes'] ?? 150);
    $max    = $max    ?? (int) ($booking['picker_suggestions'] ?? 5);

    $toMin  = fn(string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
    $target = $toMin($wanted);

    $near = array_values(array_filter($free, fn($t) => abs($toMin($t) - $target) <= $window));
    usort($near, function ($a, $b) use ($toMin, $target) {
        $da = abs($toMin($a) - $target);
        $db = abs($toMin($b) - $target);
        return $da === $db ? strcmp($a, $b) : $da <=> $db;   // tie: earlier first
    });

    $picked = array_slice($near, 0, $max);
    sort($picked);
    return $picked;
}

/**
 * The full answer for one search: suggestions on the chosen date, or — when
 * there are none — the next date that has some.
 *
 * @return array{date:string, wanted:string, suggestions:string[], message:?string,
 *               next:?array{date:string,label:string,suggestions:string[]}}
 */
function picker_search(string $date, string $wanted, int $guests, DateTimeImmutable $now, ?callable $availability = null): array
{
    global $booking;

    $free        = picker_free_times($date, $guests, $now, $availability);
    $suggestions = picker_suggest($free, $wanted);

    $result = [
        'date'        => $date,
        'wanted'      => $wanted,
        'suggestions' => $suggestions,
        'message'     => null,
        'next'        => null,
    ];

    if ($suggestions) {
        return $result;
    }

    $hours = rtrim(rtrim(number_format(((int) ($booking['picker_window_minutes'] ?? 150)) / 60, 1), '0'), '.');
    if (!hours_slots_for_date($date)) {
        $result['message'] = hours_blocked_message($date) ?? 'We are closed that day.';
    } elseif (!$free) {
        $result['message'] = $date === $now->format('Y-m-d')
            ? 'We are not taking any more online bookings today.'
            : 'At the moment, there is no online availability that day.';
    } else {
        $result['message'] = "At the moment, there's no online availability within $hours hours of $wanted.";
    }

    // Look ahead for the next day with room near the same time (or, failing
    // that, any room at all).
    $tz   = new DateTimeZone('Europe/Dublin');
    $day  = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $tz);
    $last = (new DateTimeImmutable('today', $tz))->modify('+' . (int) $booking['days_ahead'] . ' days');
    for ($i = 1; $i <= 14; $i++) {
        $d = $day->modify("+$i days");
        if ($d > $last) {
            break;
        }
        $ds    = $d->format('Y-m-d');
        $dFree = picker_free_times($ds, $guests, $now, $availability);
        if (!$dFree) {
            continue;
        }
        $near = picker_suggest($dFree, $wanted);
        $result['next'] = [
            'date'        => $ds,
            'label'       => picker_day_label($ds, $now, true),
            'slots'       => hours_slots_for_date($ds),
            'suggestions' => $near ?: picker_suggest($dFree, $wanted, PHP_INT_MAX),
        ];
        break;
    }

    return $result;
}

/**
 * What the picker shows before anybody touches it: 2 people, the usual time,
 * today — unless today is effectively over, in which case the next open day.
 *
 * @return array{guests:int, date:string, time:string}
 */
function picker_defaults(DateTimeImmutable $now, ?callable $availability = null): array
{
    global $booking;

    $guests = (int) ($booking['default_guests'] ?? 2);
    $usual  = (string) ($booking['usual_time'] ?? '11:00');
    $tz     = new DateTimeZone('Europe/Dublin');
    $day    = new DateTimeImmutable($now->format('Y-m-d'), $tz);

    for ($i = 0; $i <= 14; $i++) {
        $d    = $day->modify("+$i days")->format('Y-m-d');
        $free = picker_free_times($d, $guests, $now, $availability);
        if (!$free) {
            continue;
        }
        // Today with the usual time gone: start from the next free time instead.
        $time = $usual;
        if ($i === 0 && $usual < $free[0]) {
            $time = $free[0];
        }
        $slots = hours_slots_for_date($d);
        if (!in_array($time, $slots, true)) {
            $time = picker_nearest_slot($slots, $time);
        }
        return ['guests' => $guests, 'date' => $d, 'time' => $time];
    }

    return ['guests' => $guests, 'date' => $day->format('Y-m-d'), 'time' => $usual];
}

/** The slot in $slots closest to $time. */
function picker_nearest_slot(array $slots, string $time): string
{
    if (!$slots) {
        return $time;
    }
    $toMin = fn(string $t) => (int) substr($t, 0, 2) * 60 + (int) substr($t, 3, 2);
    usort($slots, fn($a, $b) => abs($toMin($a) - $toMin($time)) <=> abs($toMin($b) - $toMin($time)));
    return $slots[0];
}

/** "Today, 7 Nov", "Tomorrow, 8 Nov", "Mon 10 Nov". $lower gives "tomorrow, 8 Nov". */
function picker_day_label(string $date, DateTimeImmutable $now, bool $lower = false): string
{
    $tz   = new DateTimeZone('Europe/Dublin');
    $d    = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $tz);
    $diff = (int) (new DateTimeImmutable($now->format('Y-m-d'), $tz))->diff($d)->format('%r%a');

    if ($diff === 0) {
        $label = 'Today, ' . $d->format('j M');
    } elseif ($diff === 1) {
        $label = 'Tomorrow, ' . $d->format('j M');
    } else {
        return $d->format('D j M');
    }
    return $lower ? lcfirst($label) : $label;
}

/**
 * Dates for the date dropdown: every open day in the booking window.
 *
 * @return array<string,string> Y-m-d => label
 */
function picker_date_options(DateTimeImmutable $now): array
{
    global $booking;
    $tz   = new DateTimeZone('Europe/Dublin');
    $day  = new DateTimeImmutable($now->format('Y-m-d'), $tz);
    $out  = [];
    for ($i = 0; $i <= (int) $booking['days_ahead']; $i++) {
        $d = $day->modify("+$i days")->format('Y-m-d');
        if (hours_is_open($d)) {
            $out[$d] = picker_day_label($d, $now);
        }
    }
    return $out;
}
