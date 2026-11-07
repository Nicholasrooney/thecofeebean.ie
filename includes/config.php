<?php
/*
 * The Coffee Bean, 64 Mary Street, Dublin 1: site settings
 * Edit the values below. Anything left as '' (empty) is hidden on the site.
 */

$site = [
    'name'      => 'The Coffee Bean',
    'domain'    => 'thecoffeebean.ie',
    'instagram' => '',   // e.g. 'https://www.instagram.com/...' — hidden while empty

    // Contact details (they appear in the footer and on the home page)
    'address'   => '64 Mary Street, Dublin 1',
    'eircode'   => 'D01 KW22',
    'phone'     => '01 561 3011',
    'email'     => '',   // e.g. 'hello@thecoffeebean.ie'
    'maps_url'  => 'https://www.google.com/maps/search/?api=1&query=The+Coffee+Bean%2C+64+Mary+St%2C+Dublin+1%2C+D01+KW22',

    // The exact pin, as "latitude,longitude", from the café's Google Maps listing.
    // Directions use this instead of the address text.
    // Left empty for now, so directions use the address + Eircode (which is
    // exact). Paste the pin from Google Maps here if you want it to the metre.
    'coords'    => '',

    // Google rating shown on the home page. Update now and then from Google Maps.
    'rating'       => '4.7',
    'review_count' => 974,

    // Show prices on the menu? Off until the café's real price list is in
    // includes/menu-data.php. Menu prices, add-on prices, the home page and
    // the prices sent to Google all follow this one switch.
    'show_prices' => false,

    // Opening hours are NOT set here. They come from $booking['service_hours']
    // below, so the hours shown on the site, the hours Google is told, and the
    // times the booking form offers can never disagree with each other.
    'hours' => [],
];

// Booking form options
$booking = [
    /*
     * Opening hours, per day. [open, close] in 24-hour time, or null if the
     * café is closed that day. This is the single source of truth — it drives
     * the hours on the site, the hours Google shows, and the slots the booking
     * form offers.
     */
    'service_hours' => [
        // From the café's own menu board (Nov 2026).
        'mon' => ['07:45', '17:00'],
        'tue' => ['07:45', '17:00'],
        'wed' => ['07:45', '17:00'],
        'thu' => ['07:45', '17:00'],
        'fri' => ['07:45', '17:00'],
        'sat' => ['08:30', '17:00'],
        'sun' => ['09:00', '17:00'],
    ],

    // How often a slot comes round, in minutes.
    'slot_minutes' => 30,

    // The last booking is this many minutes before closing, so nobody books a
    // table for five minutes before the doors shut. The café closes at 17:00,
    // so the last slot is 16:00.
    'last_booking_before_close' => 60,

    /*
     * One-off dates the café will not take new bookings for, on top of the
     * normal closed days. Use it for a day that is already full, a private
     * function, or a closure.
     *
     * Bookings already taken on these dates are untouched, and staff can still
     * add one by hand in the diary — this only stops the public form.
     *
     * Give a date an empty message for the standard wording, or write your own:
     *     '2026-12-25' => 'We are closed for Christmas.',
     */
    'no_bookings_dates' => [],

    // Filled in below from service_hours. Do not edit by hand.
    'times' => [],
    'max_guests'        => 6,   // bigger groups are asked to ring the café
    'days_ahead'        => 60,  // how far in advance people can book

    // Inside, the window counter, or the covered terrace out on Mary Street.
    'seating'           => ['Inside', 'Window counter', 'Outside (covered terrace)', 'No preference'],

    // How long a table is held when the café is busy, which is usually at
    // weekends. Shown on the booking page and in the confirmation email.
    'table_minutes'     => 60,

    // Bookings are saved here until the backend is connected.
    // The data folder is blocked from public access by data/.htaccess
    'csv_file'          => __DIR__ . '/../data/bookings.csv',

    // Who is emailed when a TABLE BOOKING comes in. Each person gets their own
    // copy. Catering enquiries have their own list in $catering below.
    'notify_email'      => [
        'nicholas.rooney2010@gmail.com',
    ],

    // Who is emailed when staff CHANGE a booking — cancelled, no-show, or
    // edited.
    'change_notify_email' => [
        'nicholas.rooney2010@gmail.com',
    ],

    /*
     * How many bookings the café takes in one hour, whatever their size.
     * Slots come round every half hour, so 13:00 and 13:30 share the 13:00
     * hour and together use up its two bookings.
     *
     * The public form stops offering a time once its hour is full. Staff can
     * still override, and the override is recorded.
     */
    'max_bookings_per_hour' => 2,

    // Customer details are anonymised this many months after their last visit.
    'retention_months'    => 24,
];

/*
 * Database. Create it in hPanel > Databases > MySQL Databases, then run
 * db/schema.sql through phpMyAdmin. See docs/database-setup.md.
 *
 * Credentials belong in includes/secrets/db.php, which is gitignored — see the
 * bottom of this file.
 */
$db = [
    'driver'  => 'mysql',
    'host'    => 'localhost',
    'name'    => '',
    'user'    => '',
    'pass'    => '',
    'charset' => 'utf8mb4',
    'path'    => '',   // only used when driver is 'sqlite' (the test suite)
];

/*
 * Staff area at /staff/.
 *
 * Generate the hash by visiting /staff/hash.php, then paste it here. Never put
 * the plain password in this file.
 */
$staff = [
    'password_hash'   => '',
    'session_hours'   => 12,
    'max_attempts'    => 5,
    'lockout_minutes' => 15,
];

/*
 * Outgoing email.
 *
 * Fill in 'from' with a mailbox on this domain — mail claiming to come from a
 * gmail.com address will be treated as forged and binned.
 *
 * SMTP is strongly preferred over bare mail(): create the mailbox in hPanel >
 * Emails, then put its details here. Leave smtp.host empty to use mail().
 * Either way, set SPF and DKIM for the domain or confirmations go to spam.
 */
$mail = [
    /*
     * 'from' MUST be on this domain. A message claiming to come from a
     * gmail.com address but sent through Hostinger fails SPF and gets binned
     * or spam-filed — so the café's own address sends it, and replies are
     * pointed at Nicholas's inbox instead.
     *
     * This address does not need a mailbox to send from. Create one at
     * hPanel > Emails when you want to receive at it too, then fill in the
     * smtp block below.
     */
    'from'      => 'bookings@thecoffeebean.ie',   // must be on the live domain
    'from_name' => 'The Coffee Bean',
    'reply_to'  => 'nicholas.rooney2010@gmail.com',
    'log_file'  => __DIR__ . '/../data/mail.log',

    'smtp' => [
        // Hostinger's outgoing mail server. Takes effect once 'user' below is
        // filled in with the mailbox address and the password is set in
        // includes/secrets/db.php.
        'host'     => 'smtp.hostinger.com',
        'port'     => 587,
        'security' => 'tls',        // 'tls' (STARTTLS), 'ssl', or '' for none
        'user'     => '',           // the full email address
        'pass'     => '',           // put this in includes/secrets/db.php
        'timeout'  => 10,
    ],
];

/*
 * Google Calendar sync (one way: website -> Google).
 *
 * Setup is documented in docs/google-calendar-setup.md. In short: create a
 * service account, put its JSON key at the path below, then share the calendar
 * with the service account's email address giving it "Make changes to events".
 *
 * Leave 'enabled' false until the key file is in place. Sync failures never
 * block a booking either way — they are written to the log file instead.
 */
$calendar = [
    'enabled'          => false,
    'calendar_id'      => '',   // from Google Calendar > Settings > Integrate calendar
    'key_file'         => __DIR__ . '/secrets/google-service-account.json',
    'token_cache'      => __DIR__ . '/../data/.google-token.json',
    'log_file'         => __DIR__ . '/../data/calendar-sync.log',
    'timezone'         => 'Europe/Dublin',
    'duration_minutes' => 60,   // how long a table is held in the calendar

    // Token for calendar-test.php. Set to a long random string to run the test
    // page, then blank it again when you are finished.
    'test_token'       => '',
];

/*
 * Google Analytics 4. Not a secret — it appears in every page's source.
 * Loaded only after a visitor accepts cookies; see includes/analytics.php.
 * Leave empty to switch analytics off entirely.
 */
$analytics = [
    'ga4_id' => '',   // add The Coffee Bean's own GA4 ID when it has one
];

/*
 * Event catering (event-catering.php).
 *
 * Package prices are NOT typed here. Each package names the menu dishes it is
 * built from, and its "from" price per person is read live from
 * includes/menu-data.php: the cheapest dish in each group, added together.
 * Change a price on the menu and the catering price follows, so the two can
 * never disagree.
 *
 * If a named dish is ever removed from the menu, that package shows
 * "price on request" instead of a wrong number.
 */
$catering = [
    // The catering page is off. Set to true (and fill in packages, built from
    // dish names in includes/menu-data.php) to put it on the site.
    'enabled' => false,

    'notify_email' => [
        'nicholas.rooney2010@gmail.com',
    ],

    'min_guests' => 10,
    'max_guests' => 100,
    'occasions'  => [],
    'packages'   => [],
    'videos'     => [],
];

/*
 * Order for collection.
 *
 * Off for now. Two ways to switch it on, neither needs a template change:
 *
 *  1. Link out: set 'url' to the café's ordering page (Flipdish, Deliveroo
 *     Pickup, SumUp online store, etc.) and 'enabled' to true. An "Order for
 *     collection" button then appears in the header, the home page and the menu.
 *
 *  2. Build it in: later, a basket on menu.php can reuse includes/menu-data.php
 *     (every dish already has an id-able name and a price) and post to an
 *     order handler shaped like includes/booking-handler.php. Leave 'url' as
 *     'order.php' when that exists.
 */
$ordering = [
    'enabled' => false,
    'label'   => 'Order for collection',
    'url'     => '',
    'note'    => 'Order ahead and collect from 64 Mary Street.',
];

/*
 * Everything below is derived from service_hours. One source of truth: change
 * the hours above and the site, the schema and the booking form all follow.
 */
require_once __DIR__ . '/hours.php';

$site['hours']     = hours_display($booking['service_hours']);
$booking['times']  = hours_all_slots($booking['service_hours']);

/*
 * Credentials live outside this file so they never reach Git.
 *
 * Preferred location is one level above public_html, where the web server
 * cannot serve it at all. includes/secrets/db.php is the fallback; it is
 * gitignored and blocked by .htaccess.
 *
 * The file just overrides what it needs, for example:
 *
 *     <?php
 *     $db['name'] = 'u123_coffeebean';
 *     $db['user'] = 'u123_coffeebean';
 *     $db['pass'] = '...';
 *     $staff['password_hash'] = '$2y$...';
 */
foreach ([__DIR__ . '/../../private/secrets.php', __DIR__ . '/secrets/db.php'] as $secretsFile) {
    if (is_readable($secretsFile)) {
        require $secretsFile;
        break;
    }
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * A stylesheet or script URL with a version stamp on it.
 *
 * .htaccess tells browsers to keep CSS and JS for a week, which is right for
 * speed and wrong the moment we change one: returning visitors keep the old
 * file until it expires. Stamping the modification time onto the URL makes an
 * edited file a different URL, so it is fetched immediately and an unchanged
 * one still comes from cache.
 *
 * Returns a ROOT-relative URL. It must be root-relative: a page at /staff/
 * asking for 'staff/staff.css' would look for /staff/staff/staff.css.
 *
 * @param string $path  from the web root, e.g. 'css/style.css'
 */
function asset(string $path): string
{
    $path  = ltrim($path, '/');
    $file  = __DIR__ . '/../' . $path;
    $stamp = is_file($file) ? filemtime($file) : time();
    return '/' . $path . '?v=' . $stamp;
}

/**
 * A responsive <img> for one of the site photos in /images. Each photo has a
 * 1200px "name.webp" and a 640px "name-sm.webp"; the browser picks the
 * smaller one on phones and in grids, which is most of the page weight saved.
 */
function photo(string $name, string $alt, string $sizes = '(min-width: 800px) 33vw, 100vw', string $extra = 'loading="lazy" decoding="async"'): string
{
    $n = e($name);
    return '<img src="images/' . $n . '.webp" srcset="images/' . $n . '-sm.webp 640w, images/' . $n . '.webp 1200w"'
         . ' sizes="' . e($sizes) . '" alt="' . e($alt) . '" ' . $extra . '>';
}

/**
 * How a stored seating value should read.
 */
function booking_seating_label(?string $seating): string
{
    $seating = trim((string) $seating);
    return $seating === '' ? 'No preference' : $seating;
}

/**
 * Whether prices are shown anywhere on the site. See $site['show_prices'].
 */
function show_prices(): bool
{
    global $site;
    return !empty($site['show_prices']);
}

/**
 * Takes the euro amounts out of a line of menu text, for when prices are off:
 *
 *     "Add bacon +€3.50 or salmon +€5.00"  ->  "Add bacon or salmon"
 */
function strip_prices(string $text): string
{
    $text = preg_replace('/\s*\+?\s*€\s*\d+(?:[.,]\d{1,2})?/u', '', $text);
    return trim(preg_replace('/\s{2,}/', ' ', $text));
}
