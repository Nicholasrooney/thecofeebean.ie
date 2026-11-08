# The Coffee Bean — site notes

Built on the same engine as Brewbaker / Cilantro (PHP 8, Hostinger). Pages:
Home, Menu, Gallery, Book a table, Privacy, plus the `/staff/` booking diary.
Git repo: https://github.com/Nicholasrooney/thecofeebean.ie (branch `main`).

## Facts used (Google listing + the café's menu boards, 7 Nov 2026)

- 64 Mary Street, North City, Dublin 1, D01 KW22 · 01 561 3011
- Mon–Fri 07:45–17:00, Sat 08:30–17:00, Sun 09:00–17:00 (from the menu board;
  Google agrees on Sunday 9am)
- 4.7★ from 974 Google reviews (update `rating` / `review_count` now and then)
- Indoor seating, window counter, covered terrace out front
- Google's auto description ("coffeehouse chain… ice-blended") is for the
  US chain, not this café. Suggest an edit on the Google listing.

## Where to edit

- `includes/config.php` — name, address, hours, rating, booking rules, emails,
  the `$ordering` block (order for collection)
- `includes/menu-data.php` — the menu, typed from the newest board
- `gallery.php` — the `$photos` list
- `index.php` — home copy, featured dishes and the six review quotes

## Still to do

1. **Prices** — every price from the boards is in `menu-data.php` but hidden.
   Confirm with the café (salad prices came from an older board), then set
   `$site['show_prices'] = true`. One switch covers menu, home page and Google.
2. **Allergens** — the boards print allergen numbers, too small to read in the
   photos. Get the list and fill in `'allergens'`. Never guess.
3. **Click and collect** — built but switched OFF (`$ordering['enabled']`). At `order.php` ("Order" in the top bar).
   Pay on collection. Orders go to `data/orders.csv` and are emailed to
   `$ordering['notify_email']` (Nicholas for now — add the café's address),
   with a receipt to the customer. Options per dish (size, milk, eggs, extras)
   are in `order_options_for()` in `includes/order.php`; the server re-prices
   every order from the menu, so the browser can't change a price. Switch
   checkout off with `$ordering['accepting'] = false`. Online card payment
   (e.g. SumUp) can be added later at the "Place order" step.
4. **Domain / mail** — point thecoffeebean.ie at Hostinger, turn on SSL.
   `$mail['from']` must be on the live domain. Bookings email Nicholas until
   the café gives an address.
5. **Bookings** — switched OFF (`$booking['enabled']`). When on, works in "request" mode (emails). For the full diary,
   create the MySQL DB in hPanel and run `/setup.php` (docs/database-setup.md).
6. **Instagram / email / GA4** — all blank and hidden until filled in.
7. Optional: paste the exact map pin into `$site['coords']`.

## Speed

- Photos: each is a 1200px `name.webp` plus a 640px `name-sm.webp`; pages use
  `photo()` (config.php) so phones get the small one. Originals were 1–2 MB
  screenshots; the site versions are ~40–170 KB.
- Hero film: `video/hero.mp4`, 540×960, no sound, ~1.6 MB. The poster image
  paints first; the film is fetched only after the page has loaded, never on
  Save-Data / 2G / reduce-motion, and pauses off screen. Same for
  `video/kitchen.mp4`, which loads only when scrolled to.
- To add a photo: `python` + Pillow, or squoosh.app — export WebP at 1200px
  (quality ~78) and 640px, name them `x.webp` / `x-sm.webp`, add to gallery.php.

## GDPR / cookies (checked 8 Nov 2026 against DPC guidance)

- Nothing goes to Google before consent: fonts are self-hosted (fonts/), the map
  and Google Analytics load only after "Accept all" (or a tap on the map).
- "Reject non-essential" and "Accept all" look identical (DPC: equal prominence).
- The choice is remembered for 6 months, then the banner asks again.
- "Cookie settings" in the footer reopens it; rejecting deletes the _ga cookies.
- privacy.php only describes what is switched on (bookings/ordering/GA sections
  appear by themselves when those are enabled).

### Adding Google Analytics
1. Create a GA4 property + web stream for thecoffeebean.ie, copy the G-XXXX ID.
2. Paste it into `$analytics['ga4_id']` in includes/config.php. That is all:
   the banner and privacy notice update themselves.
3. In GA4 Admin: Data collection → **Google signals OFF**; Data retention →
   **2 months**; Account settings → accept the **Data Processing Terms**.
   No Google Ads link (the consent setup keeps ad storage denied).

### Adding Google Search Console
No cookies, nothing to consent to. Add a *Domain* property for
thecoffeebean.ie and verify with the DNS TXT record in Hostinger (hPanel →
Domains → DNS). Or the HTML-file method: drop google*.html in the site root
(.gitignore already lets it through). Then submit
https://thecoffeebean.ie/sitemap.xml.

## Deploy

Edit here → commit → push to `main` → Hostinger pulls it (hPanel → Advanced →
Git, branch `main`, install path empty for public_html). Credentials never go
through git: `includes/secrets/db.php` is uploaded by hand in File Manager.

## Tests

    php db/test.php && php db/test-seo.php && php db/test-setup.php && php db/test-picker.php && php db/test-order.php

Source photos, videos and screenshots are in `Extras/` (gitignored).
`event-catering.php` is still the Cilantro catering page underneath; it answers
404 while `$catering['enabled']` is false. Rewrite it before switching it on.
