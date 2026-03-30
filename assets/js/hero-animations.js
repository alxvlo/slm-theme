/**
 * Hero Animations (Refined)
 * Handles GSAP reveal, magnetic buttons, cinematic parallax, and performance optimization.
 */
(function () {
  // Ensure GSAP is loaded
  if (typeof gsap === 'undefined') return;

  const hero = document.querySelector('.home-heroSlider');
  if (!hero) return;

  const container = hero.querySelector('.container');
  const title = hero.querySelector('.js-hero-title');
  const sub = hero.querySelector('.js-hero-sub');
  const actions = hero.querySelector('.js-hero-actions');
  const tagline = hero.querySelector('.js-hero-tagline');
  const trustItems = hero.querySelectorAll('.js-hero-trust li');
  const slides = hero.querySelectorAll('.home-heroSlider__slide');

  /**
   * Utility: Split text into words wrapped in spans
   */
  function splitWords(el) {
    if (!el) return;
    const text = el.textContent.trim();
    const words = text.split(/\s+/);
    el.innerHTML = words
      .map(word => `<span class="word-wrap" style="display:inline-block; overflow:hidden;"><span class="word" style="display:inline-block;">${word}</span></span>`)
      .join(' ');
  }

  // Pre-split the headline
  if (title) splitWords(title);

  const tl = gsap.timeline({
    defaults: { ease: "expo.out", force3D: true }
  });

  // 1. Initial State Cleanup & Reveal
  tl.set([title, sub, actions, tagline, '.js-hero-trust'], { visibility: 'visible' });

  // 2. High-End Headline Reveal (Words slide up from invisible container)
  if (title) {
    tl.from(title.querySelectorAll('.word'), {
      yPercent: 100,
      opacity: 0,
      duration: 1.5,
      stagger: 0.05,
    });
  }

  // 3. Subheadline Fade-in
  if (sub) {
    tl.from(sub, {
      y: 20,
      opacity: 0,
      duration: 1.2,
    }, "-=1.1");
  }

  // 4. Buttons Pop-in
  if (actions) {
    tl.from(actions.querySelectorAll('.btn'), {
      scale: 0.9,
      opacity: 0,
      duration: 1,
      stagger: 0.12,
      ease: "back.out(1.7)"
    }, "-=0.8");
  }

  // 5. Trust Line Fade-in
  if (tagline) {
    tl.from(tagline, {
      y: 10,
      opacity: 0,
      duration: 0.9,
    }, "-=0.7");
  }

  // 6. Trust Badges Entrance
  if (trustItems.length) {
    tl.from(trustItems, {
      y: 15,
      opacity: 0,
      duration: 1,
      stagger: 0.08,
    }, "-=0.7");
  }

  /**
   * Performance-optimized Floating (yPercent & force3D)
   */
  trustItems.forEach((item, i) => {
    gsap.to(item, {
      yPercent: 15,
      duration: 2.5 + (i * 0.3),
      repeat: -1,
      yoyo: true,
      ease: "sine.inOut",
      force3D: true,
      delay: i * 0.15
    });
  });

  /**
   * Background Cinematic Effects (Ken Burns + Mouse-Follow Parallax)
   */
  let currentSlideScale = 1;
  
  function applyKenBurns(slide) {
    if (!slide) return;
    gsap.killTweensOf(slide, "scale");
    gsap.set(slide, { scale: 1 });
    
    gsap.to(slide, {
      scale: 1.1,
      duration: 12,
      ease: "none",
      repeat: -1,
      yoyo: true,
      force3D: true
    });
  }

  // Initial Ken Burns
  const initialSlide = hero.querySelector('.home-heroSlider__slide.is-active');
  if (initialSlide) applyKenBurns(initialSlide);

  // Parallax Logic
  hero.addEventListener('mousemove', (e) => {
    const { clientX, clientY } = e;
    const { width, height, left, top } = hero.getBoundingClientRect();
    const x = (clientX - left) / width - 0.5;
    const y = (clientY - top) / height - 0.5;

    // Subtle background shift (+/- 10px)
    slides.forEach(slide => {
      if (slide.classList.contains('is-active')) {
        gsap.to(slide, {
          x: x * 20,
          y: y * 20,
          duration: 1,
          ease: "power2.out"
        });
      }
    });

    /**
     * Interactive 'Magnetic' Buttons
     * Radius: 50px
     */
    if (actions) {
      actions.querySelectorAll('.btn').forEach(btn => {
        const rect = btn.getBoundingClientRect();
        const btnX = rect.left + rect.width / 2;
        const btnY = rect.top + rect.height / 2;
        
        const dist = Math.hypot(clientX - btnX, clientY - btnY);
        
        if (dist < 80) { // Slightly larger activation for smoother feel
          const pullX = (clientX - btnX) * 0.35;
          const pullY = (clientY - btnY) * 0.35;
          gsap.to(btn, {
            x: pullX,
            y: pullY,
            scale: 1.05,
            duration: 0.4,
            ease: "power2.out"
          });
        } else {
          gsap.to(btn, {
            x: 0,
            y: 0,
            scale: 1,
            duration: 0.6,
            ease: "elastic.out(1, 0.3)"
          });
        }
      });
    }
  });

  // Reset positions on mouse leave
  hero.addEventListener('mouseleave', () => {
    slides.forEach(slide => {
      gsap.to(slide, { x: 0, y: 0, duration: 1 });
    });
    if (actions) {
      actions.querySelectorAll('.btn').forEach(btn => {
        gsap.to(btn, { x: 0, y: 0, scale: 1, duration: 0.8 });
      });
    }
  });

  /**
   * MutationObserver to catch slide changes from main.js and restart Ken Burns
   */
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.attributeName === 'class') {
        const target = mutation.target;
        if (target.classList.contains('is-active')) {
          applyKenBurns(target);
        } else {
          gsap.killTweensOf(target);
          gsap.to(target, { scale: 1, x: 0, y: 0, duration: 0.8 });
        }
      }
    });
  });

  slides.forEach(slide => {
    observer.observe(slide, { attributes: true });
  });

})();
