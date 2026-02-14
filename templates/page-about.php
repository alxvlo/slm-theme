<?php
/**
 * Template Name: About
 */
if (!defined('ABSPATH')) exit;

get_header();

$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in ? slm_dashboard_url() : add_query_arg('mode', 'signup', slm_login_url());
$cta_label = $is_logged_in ? 'Go to Dashboard' : 'Create Account to Order';

$core_values = [
  [
    'title' => 'Client Partnership',
    'description' => 'We operate as partners, not vendors. Your success, brand growth, and listing performance are our priority.',
  ],
  [
    'title' => 'Excellence in Presentation',
    'description' => 'Details matter. Every property is captured and delivered with precision, intention, and high standards.',
  ],
  [
    'title' => 'Integrity and Transparency',
    'description' => 'Clear communication, honest expectations, and accountability guide every interaction.',
  ],
  [
    'title' => 'Service-First Approach',
    'description' => 'White-glove service is our standard. Responsive, accessible, and respectful of your time.',
  ],
  [
    'title' => 'Innovation and Adaptability',
    'description' => 'We evolve with the market and refine tools and creative strategies to keep clients competitive.',
  ],
  [
    'title' => 'Relationship-Driven',
    'description' => 'We value trust and long-term collaboration over transactions and build lasting partnerships.',
  ],
];

$outcomes = [
  'Winning more listing presentations',
  'Elevating brand perception in your market',
  'Attracting higher-value opportunities',
  'Saving time through streamlined workflows',
  'Growing authority and local visibility',
  'Showing up confidently in your marketing',
  'Building scalable systems for long-term growth',
];

$traditional = [
  'Per-shoot transactional services',
  'Rigid package structures',
  'Limited strategy support',
  'Production volume over partnership',
];

$showcase = [
  'Flexible, growth-based service structures',
  'Services aligned to business goals',
  'Strategy, education, and execution',
  'Long-term client success as the priority',
];

$page_content = '';
if (have_posts()) {
  while (have_posts()) {
    the_post();
    $page_content = trim((string) get_the_content());
  }
}
?>

<main>
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>About Showcase Listings Media</h1>
      <p class="page-hero__sub">Break the standard. Showcase the difference.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section page-section--secondary">
    <div class="container about-wrap">
      <div class="about-intro card">
        <h2>Where Listings Become Showcase-Worthy</h2>
        <p>We built Showcase Listings Media around a simple belief: real estate marketing should be strategic, flexible, and outcome-driven. We do not just deliver media files. We help agents build competitive advantage.</p>
        <p>Our model is designed to support how modern agents actually work with scalable services, predictable workflows, and partnership-level support that strengthens long-term growth.</p>
      </div>
    </div>
  </section>

  <section class="page-section">
    <div class="container about-wrap">
      <h2 class="center">Core Values</h2>
      <p class="center sub">These values shape how we serve, collaborate, and deliver results for every client and listing.</p>

      <div class="about-values-grid">
        <?php foreach ($core_values as $value): ?>
          <article class="about-valueCard">
            <h3><?php echo esc_html($value['title']); ?></h3>
            <p><?php echo esc_html($value['description']); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary">
    <div class="container about-wrap">
      <h2 class="center">What Clients Gain</h2>
      <p class="center sub">Our services are structured around business outcomes, not just deliverables.</p>

      <div class="about-outcomes-grid">
        <?php foreach ($outcomes as $item): ?>
          <article class="about-outcomeCard">
            <p><?php echo esc_html($item); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section">
    <div class="container about-wrap">
      <h2 class="center">Why Agents Switch</h2>
      <p class="center sub">Most agents do not switch media partners on a whim. They switch when they want better systems, better support, and better outcomes.</p>

      <div class="about-compare">
        <article class="about-compareCard">
          <h3>Traditional Media Providers</h3>
          <ul>
            <?php foreach ($traditional as $item): ?>
              <li><?php echo esc_html($item); ?></li>
            <?php endforeach; ?>
          </ul>
        </article>

        <article class="about-compareCard about-compareCard--accent">
          <h3>Showcase Listings Media</h3>
          <ul>
            <?php foreach ($showcase as $item): ?>
              <li><?php echo esc_html($item); ?></li>
            <?php endforeach; ?>
          </ul>
        </article>
      </div>
    </div>
  </section>

  <?php if ($page_content !== ''): ?>
    <section class="page-section page-section--secondary">
      <div class="container prose">
        <?php echo wp_kses_post(apply_filters('the_content', $page_content)); ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="page-section">
    <div class="container about-wrap">
      <div class="about-cta">
        <h2>Ready to Build Marketing Momentum?</h2>
        <p>Create your account and start ordering media designed to help you win listings, improve perception, and scale with confidence.</p>
        <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
