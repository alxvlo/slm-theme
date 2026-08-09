<?php
if (!defined('ABSPATH')) exit;

$theme_uri = get_template_directory_uri();

// Replace each image path below with your real hero photos when ready.
$slides = [
  $theme_uri . '/assets/img/Homepage1.jpeg',
  $theme_uri . '/assets/img/Homepage2.jpg',
  $theme_uri . '/assets/img/Homepage3.jpg',
  $theme_uri . '/assets/img/Homepage4.jpg',
  $theme_uri . '/assets/img/Homepage5.jpg',
  $theme_uri . '/assets/img/Homepage6.jpg',
];

$slides = [];
foreach ($slide_fields as $field => $fallback) {
  $slides[] = slm_acf_img_url($field, (int) $pid, $fallback);
}
// Remove empty entries
$slides = array_filter($slides);

$order_url     = slm_book_url();
$portfolio_url = slm_page_url_by_template('templates/page-portfolio.php', '/our-portfolio/');
?>

<section class="home-heroSlider" aria-label="Featured Property Media">
  <div class="home-heroSlider__slides" data-home-slider>
    <?php foreach ($slides as $i => $img): ?>
      <div
        class="home-heroSlider__slide <?php echo $i === 0 ? 'is-active' : ''; ?>"
        <?php if ($i === 0): ?>
          data-bg-image="<?php echo esc_url($img); ?>"
        <?php else: ?>
          data-lazy-bg="<?php echo esc_url($img); ?>"
          data-lazy="true"
        <?php endif; ?>
        aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>"
      ></div>
    <?php endforeach; ?>
    <div class="home-heroSlider__overlay" aria-hidden="true"></div>
  </div>

  <!-- Content -->
  <div class="home-hero__inner">
    <div class="container home-hero__content">

      <div class="home-hero__badge js-hero-badge">
        <span class="home-hero__badge-dot" aria-hidden="true"></span>
        <?php echo esc_html(get_post_meta($pid, 'hp_hero_trust_line', true) ?: 'Trusted across North Florida'); ?>
      </div>

      <h1 class="home-hero__headline js-hero-title">
        <?php echo esc_html(get_post_meta($pid, 'hp_hero_headline', true) ?: 'Real Estate Media That Stops Scroll & Sells Listings Faster'); ?>
      </h1>

      <p class="home-hero__sub js-hero-sub">
        <?php echo esc_html(get_post_meta($pid, 'hp_hero_subheadline', true) ?: 'High-quality photo, video, and content designed to help real estate agents win more listings and businesses attract more clients.'); ?>
      </p>

      <div class="home-hero__actions js-hero-actions">
        <a class="btn home-hero__btn-primary" href="<?php echo esc_url($order_url); ?>">
          <?php echo esc_html(get_post_meta($pid, 'hp_hero_cta_primary', true) ?: 'Book a Shoot'); ?>
        </a>
        <a class="btn home-hero__btn-secondary" href="<?php echo esc_url($portfolio_url); ?>">
          <?php echo esc_html(get_post_meta($pid, 'hp_hero_cta_secondary', true) ?: 'View Our Work'); ?>
        </a>
      </div>

      <ul class="home-hero__trust js-hero-trust" aria-label="Key trust points">
        <li>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
          <?php echo esc_html(get_post_meta($pid, 'hp_hero_badge_1', true) ?: '24–48 hour delivery'); ?>
        </li>
        <li>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
          <?php echo esc_html(get_post_meta($pid, 'hp_hero_badge_2', true) ?: 'Dedicated client support'); ?>
        </li>
        <li>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
          <?php echo esc_html(get_post_meta($pid, 'hp_hero_badge_3', true) ?: 'Media quality built for conversion'); ?>
        </li>
      </ul>

    <div class="home-heroSlider__actions js-hero-actions">
      <a class="btn home-heroSlider__btn--primary" href="<?php echo esc_url($order_url); ?>"><?php echo esc_html(get_post_meta($pid, 'hp_hero_cta_primary', true) ?: 'Book a Shoot'); ?></a>
      <a class="btn home-heroSlider__btn--secondary" href="<?php echo esc_url($portfolio_url); ?>"><?php echo esc_html(get_post_meta($pid, 'hp_hero_cta_secondary', true) ?: 'View Our Work'); ?></a>
    </div>

    <p class="home-heroSlider__tagline js-hero-tagline"><?php echo esc_html(get_post_meta($pid, 'hp_hero_trust_line', true) ?: 'Trusted by agents and brands across North Florida'); ?></p>

    <ul class="home-heroSlider__trust js-hero-trust" aria-label="Why clients choose us">
      <li><?php echo esc_html(get_post_meta($pid, 'hp_hero_badge_1', true) ?: '24-hour standard delivery'); ?></li>
      <li><?php echo esc_html(get_post_meta($pid, 'hp_hero_badge_2', true) ?: 'Dedicated client support'); ?></li>
      <li><?php echo esc_html(get_post_meta($pid, 'hp_hero_badge_3', true) ?: 'Media quality built for conversion'); ?></li>
    </ul>
  </div>
</section>
