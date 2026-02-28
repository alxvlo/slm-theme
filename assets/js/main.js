(function () {
  const nav = document.querySelector('[data-nav]');
  if (!nav) return;

  const panel = nav.querySelector('[data-nav-panel]');
  const toggle = nav.querySelector('[data-nav-toggle]');
  const menu = panel ? panel.querySelector('.nav__menu') : null;
  if (!panel || !toggle || !menu) return;

  const mobileQuery = window.matchMedia('(max-width: 980px)');
  const parents = Array.from(menu.querySelectorAll('li.menu-item-has-children'));

  function setMobileMenuState(isOpen, restoreFocus) {
    nav.classList.toggle('is-mobile-open', isOpen);
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    document.body.classList.toggle('has-mobile-menu', isOpen);
    if (!isOpen && restoreFocus) toggle.focus();
  }

  function closeAllSubmenus(exceptLi) {
    parents.forEach((li) => {
      if (exceptLi && li === exceptLi) return;
      li.classList.remove('is-open');
      const link = li.querySelector(':scope > a');
      if (link) link.setAttribute('aria-expanded', 'false');
    });
  }

  toggle.addEventListener('click', () => {
    const shouldOpen = !nav.classList.contains('is-mobile-open');
    setMobileMenuState(shouldOpen, false);
    if (!shouldOpen) closeAllSubmenus();
  });

  panel.addEventListener('click', (event) => {
    if (event.target !== panel) return;
    closeAllSubmenus();
    setMobileMenuState(false, false);
  });

  parents.forEach((li) => {
    const link = li.querySelector(':scope > a');
    if (!link) return;

    link.setAttribute('aria-haspopup', 'true');
    link.setAttribute('aria-expanded', 'false');

    link.addEventListener('click', (event) => {
      const href = (link.getAttribute('href') || '').trim();
      const isHashOnly = href === '#' || href === '';
      const isOpen = li.classList.contains('is-open');
      const isMobile = mobileQuery.matches;

      if (!isOpen && (isMobile || isHashOnly)) {
        event.preventDefault();
        closeAllSubmenus(li);
        li.classList.add('is-open');
        link.setAttribute('aria-expanded', 'true');
        return;
      }

      if (isHashOnly) {
        event.preventDefault();
        li.classList.toggle('is-open');
        link.setAttribute('aria-expanded', li.classList.contains('is-open') ? 'true' : 'false');
        return;
      }

      if (isMobile) setMobileMenuState(false, false);
    });
  });

  menu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      if (!mobileQuery.matches) return;
      const parentLi = link.closest('li');
      if (!parentLi) return;
      const hasChildren = parentLi.classList.contains('menu-item-has-children');
      if (hasChildren) return;
      setMobileMenuState(false, false);
      closeAllSubmenus();
    });
  });

  document.addEventListener('click', (event) => {
    if (!nav.contains(event.target)) {
      closeAllSubmenus();
      setMobileMenuState(false, false);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    closeAllSubmenus();
    setMobileMenuState(false, true);
  });

  const handleModeShift = (e) => {
    if (!e.matches) {
      closeAllSubmenus();
      setMobileMenuState(false, false);
    }
  };

  if (typeof mobileQuery.addEventListener === 'function') {
    mobileQuery.addEventListener('change', handleModeShift);
  } else if (typeof mobileQuery.addListener === 'function') {
    mobileQuery.addListener(handleModeShift);
  }
})();

(function () {
  const slider = document.querySelector('[data-home-slider]');
  if (!slider) return;

  const slides = Array.from(slider.querySelectorAll('.home-heroSlider__slide'));
  const dotsWrap = document.querySelector('[data-home-slider-dots]');
  const dots = dotsWrap ? Array.from(dotsWrap.querySelectorAll('[data-slide-index]')) : [];
  if (!slides.length) return;

  let activeIndex = slides.findIndex((slide) => slide.classList.contains('is-active'));
  if (activeIndex < 0) activeIndex = 0;

  function show(index) {
    const next = (index + slides.length) % slides.length;
    activeIndex = next;

    slides.forEach((slide, i) => {
      const active = i === next;
      slide.classList.toggle('is-active', active);
      slide.setAttribute('aria-hidden', active ? 'false' : 'true');
    });

    dots.forEach((dot, i) => {
      const active = i === next;
      dot.classList.toggle('is-active', active);
      dot.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  let timer = null;
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  function start() {
    if (timer || slides.length < 2 || prefersReducedMotion.matches) return;
    timer = window.setInterval(() => show(activeIndex + 1), 5000);
  }

  function stop() {
    if (!timer) return;
    window.clearInterval(timer);
    timer = null;
  }

  dots.forEach((dot) => {
    dot.addEventListener('click', () => {
      const index = Number(dot.getAttribute('data-slide-index') || '0');
      show(index);
      stop();
      start();
    });
  });

  slider.addEventListener('mouseenter', stop);
  slider.addEventListener('mouseleave', start);
  slider.addEventListener('focusin', stop);
  slider.addEventListener('focusout', start);

  show(activeIndex);
  start();
})();

(function () {
  const sliders = Array.from(document.querySelectorAll('[data-portfolio-slider]'));
  if (!sliders.length) return;

  sliders.forEach((root) => {
    const slidesWrap = root.querySelector('.pSlider__slides');
    const slides = slidesWrap ? Array.from(slidesWrap.querySelectorAll('.pSlider__slide')) : [];
    const thumbs = Array.from(root.querySelectorAll('[data-ps-thumb][data-ps-index]'));
    const prev = root.querySelector('[data-ps-prev]');
    const next = root.querySelector('[data-ps-next]');
    if (!slides.length) return;

    let activeIndex = slides.findIndex((s) => s.classList.contains('is-active'));
    if (activeIndex < 0) activeIndex = 0;

    function show(index) {
      const n = slides.length;
      const nextIndex = ((index % n) + n) % n;
      activeIndex = nextIndex;

      slides.forEach((slide, i) => {
        const active = i === nextIndex;
        slide.classList.toggle('is-active', active);
        slide.setAttribute('aria-hidden', active ? 'false' : 'true');
      });

      thumbs.forEach((btn) => {
        const i = Number(btn.getAttribute('data-ps-index') || '0');
        const active = i === nextIndex;
        btn.classList.toggle('is-active', active);
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
    }

    if (prev) prev.addEventListener('click', () => show(activeIndex - 1));
    if (next) next.addEventListener('click', () => show(activeIndex + 1));

    thumbs.forEach((btn) => {
      btn.addEventListener('click', () => {
        const i = Number(btn.getAttribute('data-ps-index') || '0');
        show(i);
      });
    });

    root.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') {
        e.preventDefault();
        show(activeIndex - 1);
      }
      if (e.key === 'ArrowRight') {
        e.preventDefault();
        show(activeIndex + 1);
      }
    });

    show(activeIndex);
  });
})();

(function () {
  const revealTargets = Array.from(document.querySelectorAll([
    '.home-services__header',
    '.home-serviceCard',
    '.home-how__header',
    '.home-howCard',
    '.home-start__card',
    '.about-valueCard',
    '.about-outcomeCard',
    '.about-compareCard',
    '.contact-item',
    '.contact-card',
    '.post-card',
    '.pSlider',
    '.pkg-card',
    '.svc-tile',
    '.service-hero__copy',
    '.service-mediaCard',
    '.service-benefitCard',
    '.service-whyCard',
    '.service-finalCta',
    '.auth-card',
    '.portal-card',
    '.portal-action',
    '.portal-tableCard'
  ].join(', ')));

  if (!revealTargets.length) return;

  /* Group by closest section/parent so stagger resets per section */
  const sectionMap = new Map();
  revealTargets.forEach((element) => {
    element.classList.add('reveal');
    const section = element.closest('section, .portal-account, .portal-stats, .portal-actions') || element.parentElement;
    if (!sectionMap.has(section)) sectionMap.set(section, []);
    sectionMap.get(section).push(element);
  });
  sectionMap.forEach((elements) => {
    elements.forEach((el, i) => {
      const staggerStep = Math.min(i, 10);
      el.style.setProperty('--reveal-delay', `${staggerStep * 60}ms`);
    });
  });

  if (!('IntersectionObserver' in window)) {
    revealTargets.forEach((element) => element.classList.add('is-visible'));
    return;
  }

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      obs.unobserve(entry.target);
    });
  }, {
    threshold: 0.14,
    rootMargin: '0px 0px -9% 0px'
  });

  revealTargets.forEach((element) => observer.observe(element));
})();

(function () {
  const cards = Array.from(document.querySelectorAll('.portal-tableCard'));
  if (!cards.length) return;

  function normalizeStatus(text) {
    return String(text || '')
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '-');
  }

  function parseRowDate(text) {
    const value = String(text || '').trim();
    if (!value) return null;
    const parsed = Date.parse(value);
    if (Number.isNaN(parsed)) return null;
    return parsed;
  }

  cards.forEach((card, index) => {
    const table = card.querySelector('.table');
    const tbody = table ? table.querySelector('tbody') : null;
    if (!table || !tbody) return;
    if (card.querySelector('.table-controls')) return;

    const rows = Array.from(tbody.querySelectorAll('tr'));
    const dataRows = rows.filter((row) => !row.querySelector('td[colspan]'));
    const hasDataRows = dataRows.length > 0;

    const controls = document.createElement('div');
    controls.className = 'table-controls';

    const searchWrap = document.createElement('div');
    searchWrap.className = 'table-control';
    const searchId = `table-search-${index}`;
    searchWrap.innerHTML = `
      <label for="${searchId}">Search</label>
      <input id="${searchId}" type="search" placeholder="Search order, customer, service, address">
    `;

    const statusWrap = document.createElement('div');
    statusWrap.className = 'table-control';
    const statusId = `table-status-${index}`;
    statusWrap.innerHTML = `
      <label for="${statusId}">Status</label>
      <select id="${statusId}">
        <option value="all">All statuses</option>
        <option value="completed">Completed</option>
        <option value="in-progress">In Progress</option>
        <option value="scheduled">Scheduled</option>
        <option value="pending">Pending</option>
      </select>
    `;

    const fromDateWrap = document.createElement('div');
    fromDateWrap.className = 'table-control';
    const fromDateId = `table-from-${index}`;
    fromDateWrap.innerHTML = `
      <label for="${fromDateId}">From Date</label>
      <input id="${fromDateId}" type="date">
    `;

    const toDateWrap = document.createElement('div');
    toDateWrap.className = 'table-control';
    const toDateId = `table-to-${index}`;
    toDateWrap.innerHTML = `
      <label for="${toDateId}">To Date</label>
      <input id="${toDateId}" type="date">
    `;

    const resetWrap = document.createElement('div');
    resetWrap.className = 'table-control table-control--action';
    const resetId = `table-reset-${index}`;
    resetWrap.innerHTML = `
      <label for="${resetId}">Reset</label>
      <button id="${resetId}" type="button" class="table-reset btn btn--secondary">Reset Filters</button>
    `;

    controls.appendChild(searchWrap);
    controls.appendChild(statusWrap);
    controls.appendChild(fromDateWrap);
    controls.appendChild(toDateWrap);
    controls.appendChild(resetWrap);

    const anchor = card.querySelector('.table-scroll') || table;
    anchor.parentNode.insertBefore(controls, anchor);

    const searchInput = searchWrap.querySelector('input');
    const statusSelect = statusWrap.querySelector('select');
    const fromDateInput = fromDateWrap.querySelector('input');
    const toDateInput = toDateWrap.querySelector('input');
    const resetButton = resetWrap.querySelector('button');

    if (!hasDataRows) {
      controls.classList.add('is-disabled');
      [searchInput, statusSelect, fromDateInput, toDateInput, resetButton].forEach((el) => {
        el.disabled = true;
      });

      const note = document.createElement('p');
      note.className = 'table-controls__note';
      note.textContent = 'Filters will activate when orders are available.';
      controls.appendChild(note);
      return;
    }

    /* Accessible live region for filter result counts */
    const liveRegion = document.createElement('span');
    liveRegion.setAttribute('aria-live', 'polite');
    liveRegion.setAttribute('role', 'status');
    liveRegion.className = 'screen-reader-text';
    controls.appendChild(liveRegion);

    function applyFilters() {
      const searchTerm = (searchInput.value || '').trim().toLowerCase();
      const statusFilter = statusSelect.value || 'all';
      const fromValue = (fromDateInput.value || '').trim();
      const toValue = (toDateInput.value || '').trim();
      const fromTimestamp = fromValue ? Date.parse(`${fromValue}T00:00:00`) : null;
      const toTimestamp = toValue ? Date.parse(`${toValue}T23:59:59`) : null;
      let visibleCount = 0;

      dataRows.forEach((row) => {
        const rowText = row.textContent.toLowerCase();
        const statusText = row.querySelector('.status-pill') ? row.querySelector('.status-pill').textContent : '';
        const rowStatus = normalizeStatus(statusText);
        const dateCell = row.querySelector('td:last-child');
        const rowDate = parseRowDate(dateCell ? dateCell.textContent : '');

        const matchesSearch = searchTerm === '' || rowText.includes(searchTerm);
        const matchesStatus = statusFilter === 'all' || rowStatus === statusFilter;
        const matchesDate = (!fromTimestamp && !toTimestamp)
          || (
            rowDate !== null
            && (!fromTimestamp || rowDate >= fromTimestamp)
            && (!toTimestamp || rowDate <= toTimestamp)
          );
        const visible = matchesSearch && matchesStatus && matchesDate;

        row.hidden = !visible;
        if (visible) visibleCount++;
      });

      let noResultsRow = tbody.querySelector('.js-no-results');
      if (visibleCount === 0) {
        if (!noResultsRow) {
          noResultsRow = document.createElement('tr');
          noResultsRow.className = 'js-no-results';
          const td = document.createElement('td');
          td.colSpan = table.querySelectorAll('thead th').length || 1;
          td.textContent = 'No matching rows. Adjust search or status filter.';
          noResultsRow.appendChild(td);
          tbody.appendChild(noResultsRow);
        }
      } else if (noResultsRow) {
        noResultsRow.remove();
      }

      liveRegion.textContent = visibleCount === 0 ? 'No matching results.' : visibleCount + ' result' + (visibleCount === 1 ? '' : 's') + ' found.';
    }

    searchInput.addEventListener('input', applyFilters);
    statusSelect.addEventListener('change', applyFilters);
    fromDateInput.addEventListener('change', applyFilters);
    toDateInput.addEventListener('change', applyFilters);
    resetButton.addEventListener('click', () => {
      searchInput.value = '';
      statusSelect.value = 'all';
      fromDateInput.value = '';
      toDateInput.value = '';
      applyFilters();
    });
  });
})();

(function () {
  const banner = document.querySelector('[data-cookie-banner]');
  if (!banner) return;

  const storageKey = 'slm_cookie_consent';
  const readStoredValue = () => {
    try {
      return window.localStorage.getItem(storageKey);
    } catch (error) {
      return null;
    }
  };

  const writeStoredValue = (value) => {
    try {
      window.localStorage.setItem(storageKey, value);
    } catch (error) {
      // Ignore storage errors (private mode, restricted browsers).
    }
  };

  const stored = readStoredValue();
  if (stored === 'accepted' || stored === 'dismissed') {
    return;
  }

  const acceptBtn = banner.querySelector('[data-cookie-accept]');
  const dismissBtn = banner.querySelector('[data-cookie-dismiss]');

  const hideBanner = (value) => {
    writeStoredValue(value);
    banner.hidden = true;
  };

  if (acceptBtn) {
    acceptBtn.addEventListener('click', () => hideBanner('accepted'));
  }

  if (dismissBtn) {
    dismissBtn.addEventListener('click', () => hideBanner('dismissed'));
  }

  banner.hidden = false;
  banner.setAttribute('role', 'alertdialog');
  banner.setAttribute('aria-label', 'Cookie consent');
  if (acceptBtn) acceptBtn.focus();
})();

/* ── Dirty-form guard: warn before navigating away with unsaved changes ── */
(function () {
  var dirty = false;

  function shouldTrackForm(form) {
    if (!form) return false;
    if ((form.method || '').toLowerCase() === 'get') return false;
    if (form.closest('.table-controls')) return false;
    if (form.id === 'slm-profile-form') return true;
    if (form.classList.contains('contact-form')) return true;

    if (form.classList.contains('auth-form')) {
      if (form.querySelector('input[name="slm_action"][value="register"]')) return true;
      return false;
    }

    return form.hasAttribute('data-dirty-guard');
  }

  function markDirty() { dirty = true; }
  function clearDirty() { dirty = false; }

  document.addEventListener('input', function (e) {
    var form = e.target.closest('form');
    if (shouldTrackForm(form)) markDirty();
  });
  document.addEventListener('change', function (e) {
    var form = e.target.closest('form');
    if (shouldTrackForm(form)) markDirty();
  });

  document.querySelectorAll('form').forEach(function (form) {
    if (shouldTrackForm(form)) {
      form.addEventListener('submit', clearDirty);
    }
  });

  window.addEventListener('beforeunload', function (e) {
    if (!dirty) return;
    e.preventDefault();
    e.returnValue = '';
  });
})();

/* ── Portal profile AJAX save ── */
(function () {
  var form = document.getElementById('slm-profile-form');
  if (!form) return;

  var btn = form.querySelector('[type="submit"]');
  var feedback = document.getElementById('slm-profile-feedback');

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (btn) btn.disabled = true;
    if (feedback) { feedback.textContent = 'Saving…'; feedback.className = 'portal-feedback'; feedback.hidden = false; }

    var data = new FormData(form);
    data.append('action', 'slm_save_profile');

    fetch(typeof slmAjax !== 'undefined' ? slmAjax.url : '/wp-admin/admin-ajax.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: data
    })
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (feedback) {
        feedback.textContent = res.success ? (res.data && res.data.message ? res.data.message : 'Profile saved.') : (res.data && res.data.message ? res.data.message : 'Save failed.');
        feedback.className = 'portal-feedback ' + (res.success ? 'portal-feedback--ok' : 'portal-feedback--err');
        feedback.hidden = false;
      }
    })
    .catch(function () {
      if (feedback) { feedback.textContent = 'Network error. Please try again.'; feedback.className = 'portal-feedback portal-feedback--err'; feedback.hidden = false; }
    })
    .finally(function () {
      if (btn) btn.disabled = false;
    });
  });
})();
