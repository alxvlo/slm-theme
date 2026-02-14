(function () {
  const menu = document.querySelector('.nav__menu');
  if (!menu) return;

  const parents = Array.from(menu.querySelectorAll('li.menu-item-has-children'));
  if (!parents.length) return;

  function closeAll(exceptLi) {
    parents.forEach((li) => {
      if (exceptLi && li === exceptLi) return;
      li.classList.remove('is-open');
      const a = li.querySelector(':scope > a');
      if (a) a.setAttribute('aria-expanded', 'false');
    });
  }

  parents.forEach((li) => {
    const a = li.querySelector(':scope > a');
    if (!a) return;

    a.setAttribute('aria-haspopup', 'true');
    a.setAttribute('aria-expanded', 'false');

    a.addEventListener('click', (e) => {
      const href = (a.getAttribute('href') || '').trim();
      const isHashOnly = href === '#' || href === '';

      if (!li.classList.contains('is-open')) {
        e.preventDefault();
        closeAll(li);
        li.classList.add('is-open');
        a.setAttribute('aria-expanded', 'true');
        return;
      }

      if (isHashOnly) {
        e.preventDefault();
        li.classList.toggle('is-open');
        a.setAttribute('aria-expanded', li.classList.contains('is-open') ? 'true' : 'false');
      }
    });
  });

  document.addEventListener('click', (e) => {
    if (!menu.contains(e.target)) closeAll();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAll();
  });
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
