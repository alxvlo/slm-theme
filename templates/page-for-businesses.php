<?php
/**
 * Template Name: For Businesses
 *
 * Landing page for local/business clients (non-agent). Copy is verbatim from
 * Brittney's "FOR BUSINESSES — Landing Page Copy" doc. SEO title/description are
 * set as post meta in functions.php (slm_meta_title / slm_meta_description) and
 * emitted by the generic system in inc/seo.php — no inline filter needed here.
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$is_logged_in = is_user_logged_in();

// Role-aware primary CTA — matches the resolution used sitewide (home/cta.php,
// how-it-works.php, slm_primary_cta_for_user). Do NOT redesign booking here (step 5).
$book_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : slm_consult_cta_url();

$contact_url = home_url('/contact/');

// Service destinations for the "What We Do" grid and the Ladder. These use the
// same helpers as the rest of the site, so they resolve to real permalinks where
// the pages are published and fall back to a path otherwise.
$packages_url    = slm_service_page_url('social-media-packages');
$assistance_url  = slm_service_page_url('social-media-assistance');
$memberships_url = slm_memberships_url();
$mentorship_url  = slm_page_url_by_template('templates/page-social-mentorship-program.php', '/social-mentorship-program/');

$management_url = slm_social_media_management_url();
?>

<main id="main-content">

  <!-- ============================================================
       Hero — Dark Navy (svc-hero pattern + actions + trust line)
       ============================================================ -->
  <section class="svc-hero" aria-label="Content for local businesses">
    <div class="container">
      <div class="svc-hero__content">
        <p class="svc-hero__eyebrow">Jacksonville &amp; North Florida</p>
        <h1 class="svc-hero__title">Content That Makes Your Business Impossible to Ignore</h1>
        <p class="svc-hero__sub">Professional photo, video, and social media built for local businesses — created by the same team trusted by North Florida's top real estate agents.</p>
        <p class="biz-hero__actions">
          <a class="btn btn--outlineLight" href="#what-we-do">See Business Services</a>
          <a class="btn btn--accent" href="<?php echo esc_url($book_url); ?>">Book a Free Consult</a>
        </p>
        <p class="biz-hero__trust">Trusted by contractors, service providers, studios, and local brands across North Florida</p>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Problem — Dark Navy (home-problem pattern)
       ============================================================ -->
  <section class="home-problem" aria-labelledby="biz-problem-title">
    <div class="container">
      <div class="home-problem__content">
        <h2 id="biz-problem-title">Your work is great. Your online presence should prove it.</h2>
        <p class="home-problem__lead">Most local businesses fall into the same trap:</p>
        <ul class="home-problem__list">
          <li>Posting randomly, whenever there's time</li>
          <li>Phone photos that don't match the quality of the work</li>
          <li>No plan, no consistency, no results</li>
        </ul>
        <p class="home-problem__close">Meanwhile, the competitor down the street is showing up every week with polished content — and winning the clients you should be getting.</p>
      </div>
    </div>
  </section>

  <!-- ============================================================
       What We Do — 5-card service grid (reuses svc-cards-grid)
       ============================================================ -->
  <section class="svc-section" id="what-we-do" aria-labelledby="biz-whatwedo-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="biz-whatwedo-title">One team. Every piece of your content.</h2>
      </header>

      <div class="svc-cards-grid">

        <!-- Brand Photo & Video -->
        <div class="svc-card">
          <span class="svc-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="1.8"/></svg>
          </span>
          <h3 class="svc-card__title">Brand Photo &amp; Video</h3>
          <p class="svc-card__body">Professional photography and video of your team, your space, and your work in action. From facility tours and Matterport 3D walkthroughs to team headshots and job-site footage — content that makes a strong first impression before a client ever calls.</p>
          <a class="svc-card__btn" href="<?php echo esc_url($contact_url); ?>" aria-label="Learn more about Brand Photo &amp; Video">Learn More</a>
        </div>

        <!-- Drone Content -->
        <div class="svc-card">
          <span class="svc-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="2" stroke="currentColor" stroke-width="1.8"/><path d="M5 5.5 8.5 9M19 5.5 15.5 9M5 18.5 8.5 15M19 18.5 15.5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="4" cy="5" r="2" stroke="currentColor" stroke-width="1.8"/><circle cx="20" cy="5" r="2" stroke="currentColor" stroke-width="1.8"/><circle cx="4" cy="19" r="2" stroke="currentColor" stroke-width="1.8"/><circle cx="20" cy="19" r="2" stroke="currentColor" stroke-width="1.8"/></svg>
          </span>
          <h3 class="svc-card__title">Drone Content</h3>
          <p class="svc-card__body">FAA-certified aerial photo and video that shows off your projects, property, and service area from a perspective your competitors can't match.</p>
          <a class="svc-card__btn" href="<?php echo esc_url($contact_url); ?>" aria-label="Learn more about Drone Content">Learn More</a>
        </div>

        <!-- Social Media Content Packages -->
        <div class="svc-card">
          <span class="svc-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><rect x="5" y="2" width="14" height="20" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M10 9.5 15 12l-5 2.5V9.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
          </span>
          <h3 class="svc-card__title">Social Media Content Packages</h3>
          <p class="svc-card__body">One shoot, a month of content. Edited reels, talking-head videos, and branded posts designed for Instagram and Facebook — built around your business, not a template.</p>
          <a class="svc-card__btn" href="<?php echo esc_url($packages_url); ?>" aria-label="Learn more about Social Media Content Packages">Learn More</a>
        </div>

        <!-- Social Media Management -->
        <div class="svc-card">
          <span class="svc-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3 9h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="m9 14 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </span>
          <h3 class="svc-card__title">Social Media Management</h3>
          <p class="svc-card__body">We create it, post it, and manage it. Content, captions, hashtags, scheduling, DM lead capture, and monthly reporting — you run your business, we run your presence.</p>
          <a class="svc-card__btn" href="<?php echo esc_url($management_url); ?>" aria-label="Learn more about Social Media Management">Learn More</a>
        </div>

        <!-- Social Media Mentorship -->
        <div class="svc-card">
          <span class="svc-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none"><path d="M12 3 2 8l10 5 10-5-10-5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M6 10.5V15c0 1.4 2.7 3 6 3s6-1.6 6-3v-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 8v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          </span>
          <h3 class="svc-card__title">Social Media Mentorship</h3>
          <p class="svc-card__body">Want to own it yourself? Our 2–3 month hands-on program teaches you the system: strategy, content capture, posting rhythm, and the habits to keep it going.</p>
          <a class="svc-card__btn" href="<?php echo esc_url($mentorship_url); ?>" aria-label="Learn more about Social Media Mentorship">Learn More</a>
        </div>

      </div>
    </div>
  </section>

  <!-- ============================================================
       The Ladder — numbered list (net-new .biz-ladder component,
       reuses the How-It-Works numeral-badge treatment)
       ============================================================ -->
  <section class="svc-section svc-section--alt" aria-labelledby="biz-ladder-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="biz-ladder-title">Start where you are. Grow from there.</h2>
      </header>

      <ol class="biz-ladder">
        <li class="biz-ladder__item">
          <span class="biz-ladder__num">1</span>
          <span class="biz-ladder__text"><strong>Need content?</strong> <span class="biz-ladder__arrow" aria-hidden="true">→</span> <a href="<?php echo esc_url($packages_url); ?>">Social Media Packages</a></span>
        </li>
        <li class="biz-ladder__item">
          <span class="biz-ladder__num">2</span>
          <span class="biz-ladder__text"><strong>Have clips, need editing?</strong> <span class="biz-ladder__arrow" aria-hidden="true">→</span> <a href="<?php echo esc_url($assistance_url); ?>">Social Media Assistance</a></span>
        </li>
        <li class="biz-ladder__item">
          <span class="biz-ladder__num">3</span>
          <span class="biz-ladder__text"><strong>Want consistent monthly output?</strong> <span class="biz-ladder__arrow" aria-hidden="true">→</span> <a href="<?php echo esc_url($memberships_url); ?>">Content Memberships</a></span>
        </li>
        <li class="biz-ladder__item">
          <span class="biz-ladder__num">4</span>
          <span class="biz-ladder__text"><strong>Want it completely off your plate?</strong> <span class="biz-ladder__arrow" aria-hidden="true">→</span> <a href="<?php echo esc_url($management_url); ?>">Social Media Management</a></span>
        </li>
        <li class="biz-ladder__item">
          <span class="biz-ladder__num">5</span>
          <span class="biz-ladder__text"><strong>Want to learn to do it yourself?</strong> <span class="biz-ladder__arrow" aria-hidden="true">→</span> <a href="<?php echo esc_url($mentorship_url); ?>">Mentorship Program</a></span>
        </li>
      </ol>
    </div>
  </section>

  <!-- ============================================================
       How It Works — 4 steps (home-how / home-howCard pattern)
       ============================================================ -->
  <section class="home-how" aria-labelledby="biz-how-title">
    <div class="container">
      <header class="home-how__header">
        <h2 id="biz-how-title">How It Works</h2>
      </header>

      <div class="home-how__grid">
        <article class="home-howCard">
          <span class="home-howCard__step">1</span>
          <h3>Free consult</h3>
          <p>Tell us about your business and goals. We'll recommend the right starting point (no pressure, no obligation).</p>
        </article>

        <article class="home-howCard">
          <span class="home-howCard__step">2</span>
          <h3>We capture your content</h3>
          <p>On-site shoot built around your brand, your people, and your work.</p>
        </article>

        <article class="home-howCard">
          <span class="home-howCard__step">3</span>
          <h3>Delivered in 24–48 hours</h3>
          <p>Posting-ready content in your inbox — or posted for you if we're managing your accounts.</p>
        </article>

        <article class="home-howCard">
          <span class="home-howCard__step">4</span>
          <h3>You stay visible. You grow.</h3>
          <p>Consistent, professional presence that works even when you're busy working.</p>
        </article>
      </div>
    </div>
  </section>

  <!-- ============================================================
       PORTFOLIO — designed "coming soon" empty state.
       No real business-client assets exist in this install yet, so this
       renders as an intentional placeholder rather than a hidden section.

       SWAP-IN POINT: when real media lands (studio Matterport, roofing
       drone, Turf Science reels), delete the .biz-portfolio-soon block
       below and replace it with the standard gallery pattern:

         <div class="service-gallery">
           <div class="service-mediaCard"><img src="URL" alt="" loading="lazy" decoding="async"></div>
         </div>

       The <section>, header, and CTA below can stay as-is.
       ============================================================ -->
  <section class="svc-section" aria-labelledby="biz-portfolio-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="biz-portfolio-title">Recent Business Work</h2>
        <p>Fresh business-client projects are in production — check back soon, or be one of the first.</p>
      </header>

      <div class="biz-portfolio-soon" aria-hidden="true">
        <div class="biz-portfolio-soon__tile">
          <span class="biz-portfolio-soon__badge">In production</span>
          <span class="biz-portfolio-soon__label">Studio Sessions</span>
        </div>
        <div class="biz-portfolio-soon__tile">
          <span class="biz-portfolio-soon__badge">In production</span>
          <span class="biz-portfolio-soon__label">Drone &amp; Facility</span>
        </div>
        <div class="biz-portfolio-soon__tile">
          <span class="biz-portfolio-soon__badge">In production</span>
          <span class="biz-portfolio-soon__label">Brand Reels</span>
        </div>
      </div>

      <p class="biz-portfolio-soon__cta">
        Want your work featured here?
        <a href="<?php echo esc_url($contact_url); ?>">Let's talk.</a>
      </p>
    </div>
  </section>

  <!-- ============================================================
       FAQ — plain semantic Q&A (net-new .biz-faq, no accordion JS)
       ============================================================ -->
  <section class="svc-section svc-section--alt" aria-labelledby="biz-faq-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="biz-faq-title">Frequently Asked Questions</h2>
      </header>

      <div class="biz-faq">
        <div class="biz-faq__item">
          <h3 class="biz-faq__q">Do you only work with real estate agents?</h3>
          <p class="biz-faq__a">No — real estate media is our specialty, but we bring that same standard to local businesses: contractors, service providers, studios, salons, restaurants, and more.</p>
        </div>
        <div class="biz-faq__item">
          <h3 class="biz-faq__q">Do I need to be on camera?</h3>
          <p class="biz-faq__a">Not if you don't want to be. Voiceover reels, drone content, and work-in-action footage all perform without a talking head.</p>
        </div>
        <div class="biz-faq__item">
          <h3 class="biz-faq__q">What if I already have social media accounts?</h3>
          <p class="biz-faq__a">Perfect. We optimize what you have — profile, bio, highlights, branding — before we start creating.</p>
        </div>
        <div class="biz-faq__item">
          <h3 class="biz-faq__q">How fast will I see results?</h3>
          <p class="biz-faq__a">Consistency compounds. Most businesses see engagement improve within the first month; lead flow builds as your content library and audience grow.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Final CTA — dark band (svc-final-cta pattern, home/cta.php)
       ============================================================ -->
  <section class="svc-final-cta" aria-label="Book business content">
    <div class="container">
      <h2>Ready to look as good online as you are in person?</h2>
      <div class="svc-final-cta__btns">
        <a class="btn svc-final-cta__primary" href="<?php echo esc_url($book_url); ?>">Book a Free Consult</a>
        <a class="btn svc-final-cta__secondary" href="tel:+19042945809">Call (904) 294-5809</a>
        <a class="btn svc-final-cta__secondary" href="<?php echo esc_url($contact_url); ?>">Send a Message</a>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
