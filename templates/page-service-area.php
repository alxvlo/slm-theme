<?php
/**
 * Template Name: Service Area
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$pid = get_the_ID();

$order_form_url = function_exists('slm_aryeo_public_order_form_url')
  ? slm_aryeo_public_order_form_url()
  : '';
$book_url    = $order_form_url !== '' ? $order_form_url : home_url('/contact/');
$contact_url = home_url('/contact/');

$counties = [
  [
    'name' => 'Duval County',
    'desc' => 'Jacksonville, Mandarin, and the surrounding metro.',
  ],
  [
    'name' => 'St. Johns County',
    'desc' => 'St. Augustine, Ponte Vedra, Nocatee, and the St. Johns corridor.',
  ],
  [
    'name' => 'Clay County',
    'desc' => 'Fleming Island, Orange Park, and the western suburbs.',
  ],
  [
    'name' => 'Nassau County',
    'desc' => 'Fernandina Beach, Amelia Island, and Yulee.',
  ],
  [
    'name' => 'Putnam County',
    'desc' => 'Palatka and the southern river communities.',
  ],
  [
    'name' => 'Baker County',
    'desc' => 'Macclenny, Glen St. Mary, and the western county line.',
  ],
];

$featured_cities = [
  'Jacksonville',
  'Jacksonville Beach',
  'Neptune Beach',
  'St. Augustine',
  'St. Augustine Beach',
  'Ponte Vedra',
  'Ponte Vedra Beach',
  'Nocatee',
  'Mandarin',
  'Fleming Island',
  'Orange Park',
  'Middleburg',
  'Green Cove Springs',
  'Amelia Island',
  'Fernandina Beach',
  'Yulee',
  'Callahan',
  'Baldwin',
];
?>

<main id="main-content">

  <!-- ============================================================
       Page Header — Dark Navy (svc-hero)
       ============================================================ -->
  <section class="svc-hero" aria-label="Service area overview">
    <div class="container">
      <div class="svc-hero__content">
        <p class="svc-hero__eyebrow">Jacksonville &amp; North Florida</p>
        <h1 class="svc-hero__title">Where We Shoot</h1>
        <p class="svc-hero__sub">Professional real estate photography, video, drone and branded content across Northeast Florida &mdash; from the coast to the river.</p>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Counties Served
       ============================================================ -->
  <section class="svc-section" id="counties-served" aria-labelledby="svc-area-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="svc-area-title">Counties We Serve</h2>
        <p>Showcase Listings Media serves agents and local businesses across six North Florida counties. Wherever your listing or storefront is, we bring the same elevated media that helps it stand out and sell.</p>
      </header>

      <div class="svc-grid svc-grid--5">
        <?php foreach ($counties as $county): ?>
          <article class="svc-card" aria-label="<?php echo esc_attr($county['name']); ?> coverage">
            <h3 class="svc-card__name"><?php echo esc_html($county['name']); ?></h3>
            <p class="svc-card__desc"><?php echo esc_html($county['desc']); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Featured Cities Strip
       ============================================================ -->
  <section class="svc-section svc-section--alt" aria-labelledby="svc-cities-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="svc-cities-title">Cities We Cover</h2>
      </header>
      <ul class="svc-area-chips" aria-label="Featured cities served">
        <?php foreach ($featured_cities as $city): ?>
          <li class="svc-area-chip"><?php echo esc_html($city); ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="svc-section__note">Just outside these areas? Reach out &mdash; we travel across North Florida for the right project.</p>
    </div>
  </section>

  <!-- ============================================================
       Final CTA
       ============================================================ -->
  <section class="svc-final-cta" aria-label="Book a service">
    <div class="container">
      <h2>Serving Your Area?</h2>
      <p>Let&rsquo;s put professional media to work for your listing or business &mdash; wherever you are in North Florida.</p>
      <div class="svc-final-cta__btns">
        <a class="btn svc-final-cta__primary" href="<?php echo esc_url($book_url); ?>">Book a Shoot</a>
        <a class="btn svc-final-cta__secondary" href="tel:+19042945809">Call (904) 294-5809</a>
        <a class="btn svc-final-cta__secondary" href="<?php echo esc_url($contact_url); ?>">Send a Message</a>
      </div>
    </div>
  </section>

</main>

<?php slm_edit_page_button($pid); ?>

<?php get_footer(); ?>
