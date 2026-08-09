<?php
/**
 * Template Name: About
 */
if (!defined('ABSPATH'))
  exit;

// SEO — register before get_header() fires wp_head
add_filter('pre_get_document_title', function () {
  return 'About Us | Showcase Listings Media | Jacksonville, FL';
}, 99);
add_action('wp_head', function () {
  echo '<meta name="description" content="Learn about Showcase Listings Media — Jacksonville\'s trusted real estate photography and video company serving agents and businesses across North Florida.">' . "\n";
}, 1);

get_header();

$cta_url      = slm_book_url();
$contact_url  = home_url('/contact/');

$pid    = get_the_ID();
$config = function_exists('slm_get_editable_fields_for_template')
  ? slm_get_editable_fields_for_template('templates/page-about.php')
  : [];

$meta_get = function ($key) use ($pid, $config) {
  $v = get_post_meta($pid, $key, true);
  if ($v === '') {
    if ($config && isset($config[$key]))
      return $config[$key]['default'];
  }
  return $v;
};

// Owner
$owner_name      = $meta_get('slm_about_owner_name');
$owner_role      = $meta_get('slm_about_owner_role');
$owner_bio       = $meta_get('slm_about_owner_bio');
$owner_photo_id  = absint((string) $meta_get('slm_about_owner_photo_id'));
$owner_photo_url = $owner_photo_id > 0 ? wp_get_attachment_image_url($owner_photo_id, 'large') : '';

?>

<main id="main-content">

  <?php slm_edit_page_button($pid); ?>

  <!-- ============================================================
       Section 1 — Hero (dark navy)
       ============================================================ -->
  <section class="about-hero" aria-label="About Showcase Listings Media">
    <div class="container">
      <div class="about-hero__content js-reveal">
        <h1 class="about-hero__title">Real Estate &amp; Brand Media in North Florida That Helps You Stand Out</h1>
        <p class="about-hero__sub">At Showcase Listings Media, we do more than capture photos — we create content that helps real estate agents and businesses across Jacksonville and North Florida stand out, attract attention, and grow.</p>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Section 2 — Our Story (light grey-blue)
       ============================================================ -->
  <section class="about-story" aria-labelledby="about-story-title">
    <div class="container">
      <div class="about-story__inner js-reveal">
        <h2 id="about-story-title" class="about-story__title">Built Different. On Purpose.</h2>
        <p class="about-story__body">Founded on the belief that every listing and every brand is different, our approach is intentional. We don't rely on cookie-cutter templates or rushed edits. Every shoot is designed to highlight what makes your property or business unique.</p>
        <p class="about-story__body">After stepping away from a franchise model, this company was built to give clients more —</p>
        <ul class="about-story__accent-list" aria-label="Our commitments">
          <li>More creativity</li>
          <li>More strategy</li>
          <li>More care in every project</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Section 3 — What We Believe (dark navy)
       ============================================================ -->
  <section class="about-belief" aria-labelledby="about-belief-title">
    <div class="container">
      <h2 id="about-belief-title" class="about-belief__title js-reveal">In today's market, it's not enough to simply 'have photos.'</h2>
      <p class="about-belief__sub js-reveal">You need content that:</p>

      <div class="about-belief__cards">
        <article class="about-belief__card js-reveal">
          <div class="about-belief__card-icon" aria-hidden="true">
            <!-- Icon: scroll-stop / lightning -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
            </svg>
          </div>
          <p>Stops people from scrolling</p>
        </article>

        <article class="about-belief__card js-reveal">
          <div class="about-belief__card-icon" aria-hidden="true">
            <!-- Icon: eye / impression -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </div>
          <p>Creates a strong first impression</p>
        </article>

        <article class="about-belief__card js-reveal">
          <div class="about-belief__card-icon" aria-hidden="true">
            <!-- Icon: trending up / compete -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
              <polyline points="17 6 23 6 23 12"/>
            </svg>
          </div>
          <p>Positions you above your competition</p>
        </article>
      </div>

      <p class="about-belief__closing js-reveal">Whether you're a real estate agent in Jacksonville, a growing brand in North Florida, or a business looking to elevate your presence, the goal is the same:</p>
      <a class="about-belief__cta js-reveal" href="<?php echo esc_url($cta_url); ?>">Make Your Content Work for You</a>
    </div>
  </section>

  <!-- ============================================================
       Section 4 — Meet the Founder (light grey-blue)
       ============================================================ -->
  <section class="about-founder" aria-labelledby="about-founder-name">
    <div class="container">
      <div class="about-founder__grid">

        <div class="about-founder__media js-reveal js-reveal-left">
          <img
            src="/wp-content/themes/slm-theme/assets/img/brittney.JPG"
            alt="Brittney Tribble, founder of Showcase Listings Media Jacksonville FL"
            loading="lazy"
            decoding="async">
        </div>

        <div class="about-founder__content js-reveal js-reveal-right">
          <span class="about-founder__eyebrow">Meet the Founder</span>
          <h2 id="about-founder-name" class="about-founder__name">Brittney Tribble</h2>
          <p class="about-founder__role"><?php echo esc_html(trim((string) $owner_role) !== '' ? $owner_role : 'Founder, Showcase Listings Media'); ?></p>
          <?php if (trim((string) $owner_bio) !== ''): ?>
            <p class="about-founder__bio"><?php echo esc_html($owner_bio); ?></p>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </section>

  <!-- ============================================================
       Section 5 — Our Approach (dark navy)
       ============================================================ -->
  <section class="about-approach" aria-labelledby="about-approach-title">
    <div class="container">
      <h2 id="about-approach-title" class="about-approach__title js-reveal">Our Approach</h2>
      <div class="about-approach__cards">
        <article class="about-approach__card js-reveal">
          <span class="about-approach__card-num" aria-hidden="true">01</span>
          <p>Every project is tailored — no one-size-fits-all</p>
        </article>
        <article class="about-approach__card js-reveal">
          <span class="about-approach__card-num" aria-hidden="true">02</span>
          <p>Fast, reliable turnaround times</p>
        </article>
        <article class="about-approach__card js-reveal">
          <span class="about-approach__card-num" aria-hidden="true">03</span>
          <p>Clear communication from start to finish</p>
        </article>
        <article class="about-approach__card js-reveal">
          <span class="about-approach__card-num" aria-hidden="true">04</span>
          <p>A focus on both aesthetic and performance</p>
        </article>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Section 6 — Our Partners (light grey-blue)
       ============================================================ -->
  <section class="about-partners" aria-labelledby="about-partners-title">
    <div class="container">

      <header class="about-partners__header js-reveal">
        <h2 id="about-partners-title">Our Partners</h2>
        <p>We collaborate with industry leaders to maximize our clients' benefits.</p>
      </header>

      <div class="about-partners__grid">

        <article class="about-partner-card about-partner-card--org js-reveal" aria-label="Partner: Modern Florida Home Staging">
          <span class="about-partner-card__badge">Partner</span>
          <div class="about-partner-card__logo">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/modern-florida-home-staging-logo.jpeg'); ?>"
                 alt="Modern Florida Home Staging logo" loading="lazy">
          </div>
          <p class="about-partner-card__desc">Modern Florida Home Staging LLC is our trusted staging partner. Their certified staging expertise transforms spaces to captivate buyers and maximize property value on every project we shoot together.</p>
          <a href="https://www.modernfloridahomestaging.com/" class="about-partner-card__link" target="_blank" rel="noopener noreferrer">Visit Website &rarr;</a>
        </article>

        <article class="about-partner-card about-partner-card--org js-reveal" aria-label="Participant: realMLS Photographer Network">
          <span class="about-partner-card__badge">Participant</span>
          <div class="about-partner-card__logo">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/realmls-photographer-network-badge.png'); ?>"
                 alt="realMLS Photographer Network participant badge" loading="lazy">
          </div>
          <p class="about-partner-card__desc">Through this access, we can upload and manage photographs, captions, videos, virtual tours, and floor plans on behalf of the listing brokerage or agent within realMLS &mdash; saving agents so much time!</p>
          <a href="https://www.realmls.com/" class="about-partner-card__link" target="_blank" rel="noopener noreferrer">Visit Website &rarr;</a>
        </article>

      </div>

      <div class="about-partners__cta js-reveal">
        <h3>Become a Partner</h3>
        <p>Interested in partnering with us to maximize your clients' benefits and content by working together?</p>
        <a href="<?php echo esc_url($contact_url); ?>" class="about-partners__cta-btn">Become a Partner Now</a>
      </div>

    </div>
  </section>

  <!-- ============================================================
       Section 7 — Final CTA (dark navy, matches Services page)
       ============================================================ -->
  <section class="svc-final-cta" aria-label="Book a service with Showcase Listings Media">
    <div class="container">
      <h2>Ready to Elevate Your Listings or Your Brand?</h2>
      <p>If you're ready to stand out with high-quality real estate photography and video in Jacksonville, FL — we'd love to work with you.</p>
      <div class="svc-final-cta__btns">
        <a class="btn svc-final-cta__primary" href="<?php echo esc_url($cta_url); ?>">Book Your Next Shoot</a>
        <a class="btn svc-final-cta__secondary" href="tel:+19042945809">Call (904) 294-5809</a>
        <a class="btn svc-final-cta__secondary" href="<?php echo esc_url($contact_url); ?>">Send a Message</a>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
