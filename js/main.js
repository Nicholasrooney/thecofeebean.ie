// Mobile navigation
(function () {
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('site-nav');
  if (!toggle || !nav) return;

  toggle.addEventListener('click', function () {
    var open = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', String(!open));
    toggle.setAttribute('aria-label', open ? 'Open navigation' : 'Close navigation');
    nav.classList.toggle('is-open', !open);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && nav.classList.contains('is-open')) {
      toggle.click();
      toggle.focus();
    }
  });
})();

// Gallery lightbox
(function () {
  var items = Array.prototype.slice.call(document.querySelectorAll('.gallery-item'));
  var box = document.querySelector('.lightbox');
  if (!items.length || !box || typeof box.showModal !== 'function') return;

  var img = box.querySelector('img');
  var caption = box.querySelector('figcaption');
  var current = 0;

  function show(index) {
    current = (index + items.length) % items.length;
    var item = items[current];
    img.src = item.dataset.src;
    img.alt = item.dataset.caption;
    caption.textContent = item.dataset.caption;
  }

  items.forEach(function (item, i) {
    item.addEventListener('click', function () {
      show(i);
      box.showModal();
    });
  });

  box.querySelector('.lb-prev').addEventListener('click', function () { show(current - 1); });
  box.querySelector('.lb-next').addEventListener('click', function () { show(current + 1); });
  box.querySelector('.lb-close').addEventListener('click', function () { box.close(); });

  box.addEventListener('click', function (e) {
    if (e.target === box) box.close();
  });

  box.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowLeft') show(current - 1);
    if (e.key === 'ArrowRight') show(current + 1);
  });

  box.addEventListener('close', function () {
    items[current].focus();
  });
})();

// Directions: open the maps app this device actually uses
(function () {
  var btn = document.querySelector('.btn-directions');
  if (!btn || !btn.dataset.apple) return;

  var ua = navigator.userAgent || '';
  var isApple = /iPad|iPhone|iPod|Macintosh/.test(ua) ||
                // iPadOS 13+ reports itself as a Mac, so check for touch too
                (/Mac/.test(ua) && navigator.maxTouchPoints > 1);

  // Everyone starts on the Google link, which works everywhere. Apple devices
  // get upgraded to Apple Maps so the button opens their default app.
  if (isApple) btn.href = btn.dataset.apple;
})();

// Cookie choice. Stored in localStorage rather than a cookie, so saying no to
// cookies doesn't ironically set one. Returns 'all', 'essential' or null.
function cookieChoice(set) {
  try {
    if (set) { localStorage.setItem('coffeebean-cookies', set); return set; }
    return localStorage.getItem('coffeebean-cookies');
  } catch (e) {
    // Private browsing, or storage blocked. Treat as "not decided" and never
    // assume consent.
    return null;
  }
}

// Map. Consent first: nothing is fetched from Google until the visitor has
// chosen "Accept all" in the cookie notice, or taps the placeholder to load
// the map just this once. Choosing "Essential only" later takes it back out.
(function () {
  var frame = document.querySelector('.map-frame');
  if (!frame) return;
  var placeholder = frame.querySelector('.map-load');

  function loadMap() {
    if (frame.querySelector('.map-embed')) return;
    if (placeholder) placeholder.hidden = true;
    var iframe = document.createElement('iframe');
    iframe.className = 'map-embed';
    iframe.src = frame.dataset.embed;
    iframe.title = 'Map showing ' + frame.dataset.place;
    iframe.loading = 'lazy';
    iframe.referrerPolicy = 'no-referrer-when-downgrade';
    iframe.allowFullscreen = true;
    frame.appendChild(iframe);
  }

  function removeMap() {
    var iframe = frame.querySelector('.map-embed');
    if (iframe) iframe.remove();
    if (placeholder) placeholder.hidden = false;
  }

  if (placeholder) placeholder.addEventListener('click', loadMap);
  if (cookieChoice() === 'all') loadMap();

  document.addEventListener('coffeebean:allow-map', loadMap);
  document.addEventListener('coffeebean:deny-map', removeMap);
})();

// Videos. The hero film is the heaviest thing on the site, so the page never
// waits for it: the poster image paints first, and the film is only fetched
// after the page has loaded, and not at all for people on Save-Data, a 2G
// connection, or with "reduce motion" switched on (they keep the poster).
// Below-the-fold films load when they scroll into view, and every film
// pauses while it is off screen.
(function () {
  var videos = Array.prototype.slice.call(document.querySelectorAll('.js-lazy-video'));
  if (!videos.length) return;

  var conn = navigator.connection || {};
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce || conn.saveData || /(^|-)2g$/.test(conn.effectiveType || '')) return;

  function start(v) {
    if (!v.dataset.src) return;
    v.src = v.dataset.src;
    v.removeAttribute('data-src');
    var p = v.play();
    if (p && p.catch) p.catch(function () {});
    var toggle = v.parentNode.querySelector('.video-toggle');
    if (toggle) toggle.hidden = false;
  }

  // Pause / play button, so a moving image can always be stopped.
  videos.forEach(function (v) {
    var toggle = v.parentNode.querySelector('.video-toggle');
    if (!toggle) return;
    toggle.addEventListener('click', function () {
      var paused = toggle.getAttribute('aria-pressed') === 'true';
      toggle.setAttribute('aria-pressed', String(!paused));
      v.dataset.userPaused = paused ? '' : '1';
      if (paused) v.play(); else v.pause();
    });
  });

  if (!('IntersectionObserver' in window)) {
    window.addEventListener('load', function () { videos.forEach(start); });
    return;
  }

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      var v = entry.target;
      if (entry.isIntersecting) {
        if (v.dataset.src) {
          if (v.dataset.autoplay === 'eager' && document.readyState !== 'complete') {
            window.addEventListener('load', function () { start(v); }, { once: true });
          } else {
            start(v);
          }
        } else if (!v.dataset.userPaused) {
          var p = v.play();
          if (p && p.catch) p.catch(function () {});
        }
      } else if (!v.paused) {
        v.pause();
      }
    });
  }, { rootMargin: '200px 0px' });

  videos.forEach(function (v) { io.observe(v); });
})();

// Cookie banner. Shown until somebody chooses, and reopened by the footer's
// "Cookie settings" link so a choice can be changed or withdrawn later.
(function () {
  var banner = document.getElementById('cookie-banner');
  if (!banner) return;

  if (!cookieChoice()) banner.hidden = false;

  banner.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-cookie]');
    if (!btn) return;

    var accepted = btn.dataset.cookie === 'all';
    cookieChoice(btn.dataset.cookie);
    banner.hidden = true;

    if (window.coffeebeanAnalytics) {
      if (accepted) {
        window.coffeebeanAnalytics.enable();
        window.coffeebeanAnalytics.track('cookie_consent', { choice: 'accept_all' });
      } else {
        window.coffeebeanAnalytics.disable();
      }
    }

    document.dispatchEvent(new CustomEvent(accepted ? 'coffeebean:allow-map' : 'coffeebean:deny-map'));
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-cookie-settings]')) return;
    banner.hidden = false;
    var first = banner.querySelector('[data-cookie="all"]');
    if (first) first.focus();
  });
})();

// Booking picker (OpenTable style). Party size, date and a rough time in;
// a few real free times out as buttons. Picking one opens the short details
// step (name, phone, email). The server does the thinking (slots.php), so the
// buttons always match what the booking form will accept.
(function () {
  var form = document.querySelector('.picker');
  if (!form) return;

  var guests  = form.querySelector('#guests');
  var date    = form.querySelector('#date');
  var time    = form.querySelector('#time');
  var results = form.querySelector('.picker-results');
  var summary = form.querySelector('.js-summary');
  var details = form.querySelector('#picker-details');
  if (!guests || !date || !time || !results) return;

  form.classList.add('js');
  var inFlight = null;
  var nextSlots = {};   // date -> every slot that day, for jumping to "next available"

  function el(tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text != null) n.textContent = text;
    return n;
  }

  function setTimeOptions(slots, wanted) {
    if (!slots || !slots.length) return;
    var keep = wanted || time.value;
    while (time.options.length) time.remove(0);
    var toMin = function (t) { return +t.slice(0, 2) * 60 + +t.slice(3, 5); };
    var best = slots[0];
    slots.forEach(function (t) {
      var o = el('option', null, t); o.value = t; time.appendChild(o);
      if (Math.abs(toMin(t) - toMin(keep)) < Math.abs(toMin(best) - toMin(keep))) best = t;
    });
    time.value = slots.indexOf(keep) > -1 ? keep : best;
  }

  function buttons(d, times) {
    var grid = el('div', 'slot-grid');
    times.forEach(function (t) {
      var b = el('button', 'slot-btn', t);
      b.type = 'button'; b.dataset.date = d; b.dataset.time = t;
      grid.appendChild(b);
    });
    return grid;
  }

  function render(search) {
    results.innerHTML = '';
    if (search.message) {
      var m = el('p', 'picker-message');
      m.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 7v6M12 16.5v.5" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>';
      m.appendChild(el('span', null, search.message));
      results.appendChild(m);
    }
    if (search.suggestions && search.suggestions.length) {
      results.appendChild(el('p', 'picker-label', 'Select a time'));
      results.appendChild(buttons(search.date, search.suggestions));
    } else if (search.next) {
      nextSlots[search.next.date] = search.next.slots;
      var l = el('p', 'picker-label', 'Next available is ');
      l.appendChild(el('strong', null, search.next.label));
      results.appendChild(l);
      results.appendChild(buttons(search.next.date, search.next.suggestions));
    }
  }

  function refresh(dateChanged) {
    var url = 'slots.php?date=' + encodeURIComponent(date.value) +
              '&guests=' + encodeURIComponent(guests.value) +
              '&time=' + encodeURIComponent(time.value);
    if (inFlight) inFlight.abort();
    inFlight = new AbortController();
    results.classList.add('is-loading');
    fetch(url, { signal: inFlight.signal })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (data) {
        results.classList.remove('is-loading');
        if (!data) return;
        if (dateChanged && data.slots) {
          setTimeOptions(Object.keys(data.slots), time.value);
          if (data.search && data.search.wanted !== time.value) { refresh(false); return; }
        }
        render(data.search);
      })
      .catch(function () { results.classList.remove('is-loading'); });
  }

  function updateSummary() {
    if (!summary) return;
    var g = guests.options[guests.selectedIndex].text;
    var d = date.options[date.selectedIndex].text;
    summary.textContent = g + ' · ' + d + ' · ' + time.value;
  }

  guests.addEventListener('change', function () { refresh(false); });
  time.addEventListener('change', function () { refresh(false); });
  date.addEventListener('change', function () { refresh(true); });

  results.addEventListener('click', function (e) {
    var b = e.target.closest('.slot-btn');
    if (!b) return;
    e.preventDefault();
    if (b.dataset.date !== date.value) {
      date.value = b.dataset.date;
      if (nextSlots[b.dataset.date]) setTimeOptions(nextSlots[b.dataset.date], b.dataset.time);
    }
    if (Array.prototype.some.call(time.options, function (o) { return o.value === b.dataset.time; })) {
      time.value = b.dataset.time;
    }
    Array.prototype.forEach.call(results.querySelectorAll('.slot-btn'), function (x) {
      x.classList.toggle('is-picked', x === b);
      x.setAttribute('aria-pressed', String(x === b));
    });
    updateSummary();
    form.classList.add('has-details');
    if (details) {
      details.scrollIntoView({ behavior: 'smooth', block: 'start' });
      var first = details.querySelector('#name');
      if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 350);
    }
    if (window.coffeebeanAnalytics) window.coffeebeanAnalytics.track('booking_time_picked', { time: b.dataset.time, guests: guests.value });
  });

  var change = form.querySelector('.js-change');
  if (change) change.addEventListener('click', function (e) {
    e.preventDefault();
    form.classList.remove('has-details');
    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
})();

// Analytics: what people actually do on the site.
//
// Every event has its own name so it shows up on its own line in Google
// Analytics with no setup: click_menu, click_directions, click_book_table and
// so on. A "location" says where on the page it was tapped (nav, hero, footer,
// or the section it sits in), so you can tell a nav tap from a hero-button tap.
//
// Links are recognised by where they go, not by hand-tagging each one, so a
// new "Book a table" button anywhere on the site is counted automatically.
// Nothing personal is ever sent.
(function () {
  function track(name, params) {
    if (window.coffeebeanAnalytics) window.coffeebeanAnalytics.track(name, params);
  }

  function whereOnPage(el) {
    if (el.closest('.fascia, .site-nav')) return 'nav';
    if (el.closest('.site-footer')) return 'footer';
    if (el.closest('.hero')) return 'hero';
    if (el.closest('.cookie')) return 'cookie_banner';
    var section = el.closest('section[id], section[class]');
    if (section) return section.id || section.className.split(' ').pop();
    return 'page';
  }

  // Which event a link counts as, from its destination.
  function eventFor(a) {
    var explicit = a.getAttribute('data-track');
    if (explicit) return { name: explicit, label: a.getAttribute('data-track-label') || '' };

    var href = a.getAttribute('href') || '';
    if (/^tel:/.test(href))                return { name: 'click_phone' };
    if (/^mailto:/.test(href))             return { name: 'click_email' };
    if (/instagram\.com/.test(href))       return { name: 'click_instagram' };
    if (/maps\.apple\.com/.test(href))     return { name: 'click_directions', app: 'apple_maps' };
    if (/waze\.com/.test(href))            return { name: 'click_directions', app: 'waze' };
    if (/google\.[a-z.]+\/maps/.test(href)) return { name: 'click_directions', app: 'google_maps' };
    if (/event-catering/.test(href))       return { name: 'click_catering' };
    if (/booking\.php/.test(href))         return { name: 'click_book_table' };
    if (/menu\.php/.test(href))            return { name: 'click_menu' };
    if (/gallery\.php/.test(href))         return { name: 'click_gallery' };
    if (/privacy\.php/.test(href))         return { name: 'click_privacy' };
    if (/^#/.test(href) && a.closest('.menu-jump')) return { name: 'menu_section', label: href.slice(1) };
    return null;
  }

  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href]');
    if (!a) return;
    var hit = eventFor(a);
    if (!hit) return;

    var params = { location: whereOnPage(a) };
    if (hit.app) params.app = hit.app;
    if (hit.label) params.label = hit.label;
    if (a.classList.contains('btn-directions')) params.label = 'get_directions_button';
    track(hit.name, params);
  }, true);

  // Gallery: which photos people open.
  document.addEventListener('click', function (e) {
    var item = e.target.closest('.gallery-item');
    if (item) track('gallery_open', { label: item.dataset.caption || '' });
  }, true);

  // Booking funnel: started, hit a problem, or went through.
  var form = document.querySelector('.booking-form');
  if (form) {
    var started = false;
    form.addEventListener('focusin', function () {
      if (started) return;
      started = true;
      track('booking_form_start', { location: 'booking_page' });
    });

    // The server re-renders the form with errors; report which fields (never
    // what was typed) so the fields that trip people up are visible.
    var errors = Array.prototype.map.call(
      document.querySelectorAll('.field-error[id]'),
      function (el) { return el.id.replace(/-error$/, ''); }
    );
    if (errors.length) track('booking_form_error', { fields: errors.join(',') });
  }

  var done = document.querySelector('[data-track-view="booking_submitted"]');
  if (done) {
    track('booking_submitted', {
      guests: parseInt(done.dataset.guests, 10) || 0,
      time: done.dataset.time || '',
      day_of_week: done.dataset.day || ''
    });
  }
})();

// Catering quote card: keeps the guest count beside the slider in step.
// Works as a plain form without any of this.
(function () {
  var card = document.querySelector('[data-price-card]');
  if (!card) return;

  var range   = card.querySelector('.pc-range');
  var guestEl = card.querySelector('[data-pc-guests]');
  if (!range) return;

  function update() {
    var guests = parseInt(range.value, 10) || 0;
    if (guestEl) guestEl.textContent = guests;
    range.setAttribute('aria-valuetext', guests + ' guests');
  }

  card.addEventListener('change', update);
  range.addEventListener('input', update);

  update();
})();

// Catering full form: only ask for a delivery address when it's needed.
(function () {
  var form = document.querySelector('.catering-form');
  if (!form) return;
  var box = form.querySelector('[data-delivery-address]');
  if (!box) return;

  form.addEventListener('change', function (e) {
    if (e.target.name !== 'fulfilment') return;
    box.hidden = e.target.value !== 'delivery';
    if (!box.hidden) {
      var ta = box.querySelector('textarea');
      if (ta) ta.focus();
    }
  });
})();

// Catering analytics: which occasions and packages people price, how many
// guests they slide to, and whether they go on to ask. No contact details.
(function () {
  function track(name, params) {
    if (window.coffeebeanAnalytics) window.coffeebeanAnalytics.track(name, params);
  }

  var card = document.querySelector('[data-price-card]');
  if (card) {
    card.addEventListener('change', function (e) {
      var t = e.target;
      if (t.name === 'occasion') track('catering_occasion_select', { label: t.value });
      // 'change' fires once when the slider is let go, not on every step.
      if (t.name === 'guests')   track('catering_guests_set', { guests: parseInt(t.value, 10) || 0 });
      // Only that a location was given — never what it says, which could be an address.
      if (t.name === 'location' && t.value.trim()) track('catering_location_entered', {});
    });

    var contact = card.querySelector('#pc-contact');
    if (contact) {
      contact.addEventListener('focus', function once() {
        track('catering_price_card_start', {});
        contact.removeEventListener('focus', once);
      });
    }
  }

  var full = document.querySelector('.catering-form');
  if (full) {
    var started = false;
    full.addEventListener('focusin', function () {
      if (started) return;
      started = true;
      track('catering_form_start', {});
    });
  }

  if (card || full) {
    var errors = Array.prototype.map.call(
      document.querySelectorAll('.field-error[id]'),
      function (el) { return el.id.replace(/-error$/, ''); }
    );
    if (errors.length) track('catering_form_error', { fields: errors.join(',') });
  }

  var done = document.querySelector('[data-track-view="catering_enquiry_submitted"]');
  if (done) {
    track('catering_enquiry_submitted', {
      form: done.dataset.source || '',
      guests: parseInt(done.dataset.guests, 10) || 0,
      package: done.dataset.package || '',
      occasion: done.dataset.occasion || ''
    });
  }
})();
