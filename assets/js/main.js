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
  function start() {
    if (timer || slides.length < 2) return;
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
  const revealTargets = Array.from(document.querySelectorAll([
    '.page-hero__content',
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
    '.pkg-card',
    '.svc-tile',
    '.auth-card',
    '.portal-card',
    '.portal-action',
    '.portal-tableCard'
  ].join(', ')));

  if (!revealTargets.length) return;

  revealTargets.forEach((element, index) => {
    element.classList.add('reveal');
    element.style.setProperty('--reveal-delay', `${(index % 7) * 60}ms`);
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
