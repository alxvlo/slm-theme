(function () {
  if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
    return;
  }

  gsap.registerPlugin(ScrollTrigger);

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (prefersReducedMotion) {
    return;
  }

  // Set up common fade in scroll animation
  const fadeUpElements = document.querySelectorAll('.home-serviceCard, .home-howCard, .pkg-card, .tCard, .about-valueCard, .addon-card');

  fadeUpElements.forEach((el, index) => {
    gsap.fromTo(el,
      { opacity: 0, y: 30 },
      {
        opacity: 1,
        y: 0,
        duration: 0.8,
        ease: 'power2.out',
        scrollTrigger: {
          trigger: el,
          start: 'top 85%',
          toggleActions: 'play none none reverse',
        }
      }
    );
  });

  // Hero Parallax effect
  const heroSlide = document.querySelector('.home-heroSlider__slide.is-active');
  if (heroSlide) {
    gsap.to(heroSlide, {
      yPercent: 15,
      ease: 'none',
      scrollTrigger: {
        trigger: '.home-heroSlider',
        start: 'top top',
        end: 'bottom top',
        scrub: true
      }
    });
  }

  const innerHero = document.querySelector('.page-hero');
  if (innerHero) {
    gsap.to(innerHero, {
      yPercent: 15,
      ease: 'none',
      scrollTrigger: {
        trigger: '.page-hero',
        start: 'top top',
        end: 'bottom top',
        scrub: true
      }
    });
  }

  // Section Headers Reveal
  const sectionHeaders = document.querySelectorAll('.home-services__header, .home-how__header, .home-testimonials__header, .page-section h2');
  sectionHeaders.forEach((header) => {
    gsap.fromTo(header,
      { opacity: 0, y: 20 },
      {
        opacity: 1,
        y: 0,
        duration: 0.8,
        ease: 'power2.out',
        scrollTrigger: {
          trigger: header,
          start: 'top 90%',
          toggleActions: 'play none none reverse',
        }
      }
    );
  });

})();
