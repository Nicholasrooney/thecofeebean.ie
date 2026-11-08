// Click and collect: menu -> item options -> basket -> collection time -> details.
// Prices shown here are for convenience only; the server prices every order
// again from the menu (includes/order.php) and ignores anything sent from here.
(function () {
  var KEY = 'coffeebean-basket';

  // Order placed: empty the basket and stop.
  if (document.querySelector('[data-clear-basket]')) {
    try { localStorage.removeItem(KEY); } catch (e) {}
    return;
  }

  var dataEl = document.getElementById('order-data');
  if (!dataEl) return;
  var data = JSON.parse(dataEl.textContent);

  var index = {};
  data.catalog.forEach(function (section) {
    section.items.forEach(function (item) {
      item.until = section.until;
      index[item.id] = item;
    });
  });

  var aside    = document.getElementById('basket');
  var form     = document.getElementById('checkout');
  var linesEl  = form.querySelector('.basket-lines');
  var emptyEl  = form.querySelector('.basket-empty');
  var hidden   = form.querySelector('input[name="basket"]');
  var timeSel  = form.querySelector('#collect_time');
  var bar      = document.querySelector('.basket-bar');
  var dialog   = document.querySelector('.item-dialog');

  // ---------- money and pricing ----------
  function money(c) { return '€' + (c / 100).toFixed(2); }

  function unitPrice(item, choices) {
    var p = item.price;
    item.options.forEach(function (g, gi) {
      var v = choices[gi];
      if (g.type === 'one') {
        var c = g.choices[v == null ? 0 : v];
        if (c) p += c.price;
      } else {
        (v || []).forEach(function (ci) { if (g.choices[ci]) p += g.choices[ci].price; });
      }
    });
    return p;
  }

  function labels(item, choices) {
    var out = [];
    item.options.forEach(function (g, gi) {
      var v = choices[gi];
      if (g.type === 'one') {
        var c = g.choices[v == null ? 0 : v];
        if (c) out.push(c.label);
      } else {
        (v || []).forEach(function (ci) { if (g.choices[ci]) out.push('+ ' + g.choices[ci].label); });
      }
    });
    return out;
  }

  // ---------- basket state ----------
  var basket = [];
  try { basket = JSON.parse(localStorage.getItem(KEY) || '[]') || []; } catch (e) { basket = []; }
  // Drop anything no longer on the menu.
  basket = basket.filter(function (l) { return index[l.id]; });

  function save() {
    try { localStorage.setItem(KEY, JSON.stringify(basket)); } catch (e) {}
  }

  function sameLine(a, b) {
    return a.id === b.id && a.note === b.note && JSON.stringify(a.choices) === JSON.stringify(b.choices);
  }

  function add(line) {
    for (var i = 0; i < basket.length; i++) {
      if (sameLine(basket[i], line)) { basket[i].qty = Math.min(20, basket[i].qty + line.qty); render(); return; }
    }
    basket.push(line);
    render();
  }

  // ---------- rendering ----------
  function render() {
    save();
    var total = 0, count = 0;
    linesEl.innerHTML = '';

    basket.forEach(function (l, i) {
      var item = index[l.id];
      var lineTotal = unitPrice(item, l.choices) * l.qty;
      total += lineTotal; count += l.qty;

      var li = document.createElement('li');
      li.className = 'basket-line';
      var info = document.createElement('div');
      info.className = 'basket-line-info';
      var name = document.createElement('p');
      name.className = 'basket-line-name';
      name.textContent = item.name;
      info.appendChild(name);
      var bits = labels(item, l.choices);
      if (l.note) bits.push('Note: ' + l.note);
      if (bits.length) {
        var opt = document.createElement('p');
        opt.className = 'basket-line-opts';
        opt.textContent = bits.join(', ');
        info.appendChild(opt);
      }
      var qty = document.createElement('div');
      qty.className = 'qty';
      qty.innerHTML =
        '<button type="button" class="qty-btn" data-act="dec" data-i="' + i + '" aria-label="One less ' + item.name.replace(/"/g, '') + '">−</button>' +
        '<span aria-live="polite">' + l.qty + '</span>' +
        '<button type="button" class="qty-btn" data-act="inc" data-i="' + i + '" aria-label="One more ' + item.name.replace(/"/g, '') + '">+</button>';
      info.appendChild(qty);

      var price = document.createElement('p');
      price.className = 'basket-line-price';
      price.textContent = money(lineTotal);

      li.appendChild(info);
      li.appendChild(price);
      linesEl.appendChild(li);
    });

    form.classList.toggle('is-empty', !basket.length);
    emptyEl.hidden = basket.length > 0;
    Array.prototype.forEach.call(document.querySelectorAll('.js-total'), function (n) { n.textContent = money(total); });
    Array.prototype.forEach.call(document.querySelectorAll('.js-count'), function (n) { n.textContent = count; });
    if (bar) bar.hidden = !basket.length;

    hidden.value = JSON.stringify(basket.map(function (l) {
      return { id: l.id, qty: l.qty, choices: l.choices, note: l.note };
    }));

    limitTimes();
  }

  // Breakfast is collected by 15:30 and so on: hide later times when the basket needs it.
  function limitTimes() {
    if (!timeSel) return;
    var until = null;
    basket.forEach(function (l) {
      var u = index[l.id].until;
      if (u && (until === null || u < until)) until = u;
    });
    var firstOk = null, lastOk = null;
    Array.prototype.forEach.call(timeSel.options, function (o) {
      var ok = !until || o.value <= until;
      o.disabled = !ok;
      if (ok) { if (!firstOk) firstOk = o; lastOk = o; }
    });
    if (timeSel.selectedOptions[0] && timeSel.selectedOptions[0].disabled && lastOk) lastOk.selected = true;
    var hint = form.querySelector('.time-hint');
    if (!hint) {
      hint = document.createElement('p');
      hint.className = 'field-hint time-hint';
      timeSel.parentNode.appendChild(hint);
    }
    hint.textContent = until && !firstOk ? 'Some items in your order are no longer being served today.'
                     : until ? 'Your order includes food served until ' + until + '.' : '';
  }

  linesEl.addEventListener('click', function (e) {
    var b = e.target.closest('.qty-btn');
    if (!b) return;
    var i = +b.dataset.i;
    if (b.dataset.act === 'inc') basket[i].qty = Math.min(20, basket[i].qty + 1);
    else if (--basket[i].qty <= 0) basket.splice(i, 1);
    render();
  });

  // ---------- item dialog ----------
  function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

  function openItem(id) {
    var item = index[id];
    if (!item || !dialog) return;
    var inner = dialog.querySelector('.item-dialog-inner');
    var html = '';
    if (item.photo) html += '<img class="item-dialog-img" src="images/' + item.photo + '.webp" alt="">';
    html += '<div class="item-dialog-body">';
    html += '<h2 id="item-dialog-title">' + esc(item.name) + '</h2>';
    if (item.desc) html += '<p class="item-dialog-desc">' + esc(item.desc) + '</p>';
    html += '<p class="item-dialog-base">' + money(item.price) + '</p>';

    item.options.forEach(function (g, gi) {
      html += '<fieldset class="opt-group"><legend>' + esc(g.label) +
              '<span class="opt-req">' + (g.type === 'one' ? 'Choose 1' : 'Optional') + '</span></legend>';
      g.choices.forEach(function (c, ci) {
        var type = g.type === 'one' ? 'radio' : 'checkbox';
        html += '<label class="opt"><input type="' + type + '" name="g' + gi + '" value="' + ci + '"' +
                (g.type === 'one' && ci === 0 ? ' checked' : '') + '>' +
                '<span class="opt-label">' + esc(c.label) + '</span>' +
                (c.price ? '<span class="opt-price">+' + money(c.price) + '</span>' : '') + '</label>';
      });
      html += '</fieldset>';
    });

    html += '<label class="opt-note"><span>Anything to add or leave out?</span>' +
            '<textarea name="note" rows="2" maxlength="140" placeholder="e.g. no onion, extra hot"></textarea></label>';
    html += '</div>';
    html += '<div class="item-dialog-foot">' +
              '<div class="qty qty-lg"><button type="button" class="qty-btn" data-q="-1" aria-label="One less">−</button>' +
              '<span class="js-dq">1</span><button type="button" class="qty-btn" data-q="1" aria-label="One more">+</button></div>' +
              '<button type="button" class="btn btn-accent js-add">Add · <span class="js-dprice"></span></button>' +
            '</div>' +
            '<button type="button" class="item-dialog-close" aria-label="Close">&times;</button>';
    inner.innerHTML = html;

    var qty = 1;
    function read() {
      var choices = {};
      item.options.forEach(function (g, gi) {
        var inputs = inner.querySelectorAll('input[name="g' + gi + '"]:checked');
        if (g.type === 'one') choices[gi] = inputs[0] ? +inputs[0].value : 0;
        else choices[gi] = Array.prototype.map.call(inputs, function (n) { return +n.value; });
      });
      return choices;
    }
    function update() {
      inner.querySelector('.js-dq').textContent = qty;
      inner.querySelector('.js-dprice').textContent = money(unitPrice(item, read()) * qty);
    }
    inner.onchange = update;
    inner.onclick = function (e) {
      var q = e.target.closest('[data-q]');
      if (q) { qty = Math.max(1, Math.min(20, qty + +q.dataset.q)); update(); }
      if (e.target.closest('.item-dialog-close')) dialog.close();
      if (e.target.closest('.js-add')) {
        add({ id: item.id, qty: qty, choices: read(), note: inner.querySelector('textarea').value.trim().slice(0, 140) });
        dialog.close();
        toast(item.name + ' added');
      }
    };
    update();
    if (typeof dialog.showModal === 'function') dialog.showModal(); else dialog.setAttribute('open', '');
  }

  if (dialog) dialog.addEventListener('click', function (e) { if (e.target === dialog) dialog.close(); });

  document.querySelector('.order-menu').addEventListener('click', function (e) {
    var b = e.target.closest('.order-item');
    if (b) openItem(b.dataset.id);
  });

  // ---------- small feedback ----------
  var toastEl;
  function toast(msg) {
    if (!toastEl) {
      toastEl = document.createElement('div');
      toastEl.className = 'order-toast';
      toastEl.setAttribute('role', 'status');
      document.body.appendChild(toastEl);
    }
    toastEl.textContent = msg;
    toastEl.classList.add('is-on');
    clearTimeout(toast.t);
    toast.t = setTimeout(function () { toastEl.classList.remove('is-on'); }, 1800);
    if (bar) { bar.classList.remove('bump'); void bar.offsetWidth; bar.classList.add('bump'); }
  }

  // ---------- basket on phones: a sheet ----------
  function openSheet() { aside.classList.add('is-open'); document.body.classList.add('sheet-open'); }
  function closeSheet() { aside.classList.remove('is-open'); document.body.classList.remove('sheet-open'); }
  if (bar) bar.addEventListener('click', openSheet);
  var closeBtn = form.querySelector('.basket-close');
  if (closeBtn) closeBtn.addEventListener('click', closeSheet);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSheet(); });

  // ---------- checkout ----------
  var cont = form.querySelector('.js-continue');
  if (cont) cont.addEventListener('click', function () {
    if (!basket.length) return;
    form.classList.add('show-details');
    var n = form.querySelector('#o-name');
    if (n) n.focus();
  });

  form.addEventListener('submit', function (e) {
    if (!basket.length) { e.preventDefault(); return; }
    render();
    var btn = form.querySelector('.js-place');
    if (btn) { btn.disabled = true; btn.firstChild.textContent = 'Placing order… '; }
  });

  // ---------- section tabs ----------
  var tabs = Array.prototype.slice.call(document.querySelectorAll('.order-tabs a'));
  if ('IntersectionObserver' in window && tabs.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        tabs.forEach(function (t) {
          var on = t.getAttribute('href') === '#' + en.target.id;
          t.classList.toggle('is-active', on);
          if (on && t.scrollIntoView) t.parentNode.scrollTo({ left: t.offsetLeft - 16, behavior: 'smooth' });
        });
      });
    }, { rootMargin: '-35% 0px -60% 0px' });
    document.querySelectorAll('.order-section').forEach(function (s) { io.observe(s); });
  }

  // Sections whose kitchen has closed for the collection day (e.g. breakfast
  // after 15:30) are shown but cannot be added.
  var firstTime = data.times[0];
  if (firstTime) {
    data.catalog.forEach(function (section) {
      if (!section.until || section.until >= firstTime) return;
      var el = document.getElementById('sec-' + section.id);
      if (!el) return;
      el.classList.add('is-unavailable');
      var note = el.querySelector('.order-until');
      if (note) note.textContent = 'Finished for today (collect by ' + section.until + ')';
      Array.prototype.forEach.call(el.querySelectorAll('.order-item'), function (b) { b.disabled = true; });
      basket = basket.filter(function (l) { return index[l.id].until !== section.until || section.until >= firstTime; });
    });
  }

  // ---------- start ----------
  render();
  if (data.showDetails && basket.length) {
    form.classList.add('show-details');
    if (data.hasErrors) openSheet();
  }
})();
