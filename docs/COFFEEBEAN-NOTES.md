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
3. **Order for collection** — `$ordering` in config. Link-out now (set `url`
   and `enabled`), or build a basket later on top of `menu-data.php`.
4. **Domain / mail** — point thecoffeebean.ie at Hostinger, turn on SSL.
   `$mail['from']` must be on the live domain. Bookings email Nicholas until
   the café gives an address.
5. **Bookings** — works now in "request" mode (emails). For the full diary,
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

## GDPR / cookies

Same consent banner as the other sites. Analytics is opt-in. The Google map no
longer loads until the visitor accepts or taps "Show the map" (the older sites
load it first and remove it on "Essential only").

## Deploy

Edit here → commit → push to `main` → Hostinger pulls it (hPanel → Advanced →
Git, branch `main`, install path empty for public_html). Credentials never go
through git: `includes/secrets/db.php` is uploaded by hand in File Manager.

## Tests

    php db/test.php && php db/test-seo.php && php db/test-setup.php && php db/test-picker.php

Source photos, videos and screenshots are in `Extras/` (gitignored).
`event-catering.php` is still the Cilantro catering page underneath; it answers
404 while `$catering['enabled']` is false. Rewrite it before switching it on.
