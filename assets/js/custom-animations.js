(function () {
  if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
    return;
  }

  gsap.registerPlugin(ScrollTrigger);

  document.addEventListener('DOMContentLoaded', () => {
    // Hero Slide Animation Overrides
    const heroElements = document.querySelectorAll('.page-hero__content > *');
    if (heroElements.length) {
      gsap.fromTo(heroElements,
        { y: 30, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: "power3.out" }
      );
    }

    // Fade up sections
    const sections = gsap.utils.toArray('.page-section');
    sections.forEach(sec => {
      gsap.fromTo(sec,
        { opacity: 0, y: 40 },
        {
          opacity: 1, y: 0, duration: 0.8, ease: "power2.out",
          scrollTrigger: {
            trigger: sec,
            start: "top 85%",
            toggleActions: "play none none none"
          }
        }
      );
    });

    // Staggered grid cards
    const grids = gsap.utils.toArray('.pkg-grid, .addon-grid, .home-how__grid, .home-who__grid, .home-why__grid, .portfolio-grid');
    grids.forEach(grid => {
      const cards = grid.querySelectorAll('.pkg-card, .addon-card, .home-howCard, .home-who__card, .home-why__card, .post-card');
      if (cards.length) {
         gsap.fromTo(cards,
           { opacity: 0, y: 30 },
           {
             opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: "power2.out",
             scrollTrigger: {
               trigger: grid,
               start: "top 85%",
               toggleActions: "play none none none"
             }
           }
         );
      }
    });
  });
})();
