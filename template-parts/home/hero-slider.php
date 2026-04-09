<?php
if (!defined('ABSPATH')) exit;

$theme_uri = get_template_directory_uri();
$pid       = get_option('page_on_front');

/**
 * Helper: resolve an ACF image field to a URL.
 * Accepts an attachment ID (ACF free returns ID when return_format = 'id').
 * Falls back to the provided $default path if no field value exists.
 */
function slm_acf_img_url(string $field_name, int $post_id, string $default = ''): string {
  if (!function_exists('get_field')) {
    return $default;
  }
  $val = get_field($field_name, $post_id);
  if (!$val) return $default;
  if (is_numeric($val)) {
    $url = wp_get_attachment_image_url((int) $val, 'full');
    return ($url !== false) ? $url : $default;
  }
  if (is_string($val) && $val !== '') return esc_url_raw($val);
  return $default;
}

// Build slides array: ACF individual image fields → fallback to theme files
$slide_fields = [
  'hp_slide_1_image' => $theme_uri . '/assets/img/Homepage1.jpeg',
  'hp_slide_2_image' => $theme_uri . '/assets/img/Homepage2.jpg',
  'hp_slide_3_image' => $theme_uri . '/assets/img/Homepage3.jpg',
  'hp_slide_4_image' => $theme_uri . '/assets/img/Homepage4.jpg',
  'hp_slide_5_image' => $theme_uri . '/assets/img/Homepage5.jpg',
  'hp_slide_6_image' => $theme_uri . '/assets/img/Homepage6.jpg',
];

$slides = [];
foreach ($slide_fields as $field => $fallback) {
  $slides[] = slm_acf_img_url($field, (int) $pid, $fallback);
}
// Remove empty entries
$slides = array_filter($slides);

$is_logged_in = is_user_logged_in();
$order_url    = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
$portfolio_url = home_url('/portfolio/');
?>

<section class="home-hero" aria-label="Featured Property Media">
  <!-- Slides -->
  <div class="home-hero__slides" data-home-slider aria-hidden="true">
    <?php foreach ($slides as $i => $img): ?>
      <div
        class="home-hero__slide <?php echo $i === 0 ? 'is-active' : ''; ?>"
        data-bg-image="<?php echo esc_url($img); ?>"
      ></div>
    <?php endforeach; ?>
    <div class="home-hero__overlay"></div>
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
          <?php echo esc_html(get_post_meta($pid, 'hp_hero_badge_1', true) ?: '24-hour standard delivery'); ?>
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

    </div>
  </div>

  <!-- Scroll indicator -->
  <div class="home-hero__scroll" aria-hidden="true">
    <svg width="20" height="28" viewBox="0 0 20 28" fill="none"><rect x="1" y="1" width="18" height="26" rx="9" stroke="rgba(255,255,255,0.5)" stroke-width="1.5"/><circle class="home-hero__scroll-dot" cx="10" cy="8" r="3" fill="white"/></svg>
  </div>

  <!-- Slide controls -->
  <?php if (count($slides) > 1): ?>
  <div class="home-hero__dots" role="tablist" aria-label="Slide navigation">
    <?php foreach ($slides as $i => $img): ?>
      <button
        class="home-hero__dot <?php echo $i === 0 ? 'is-active' : ''; ?>"
        role="tab"
        aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
        aria-label="Slide <?php echo $i + 1; ?>"
        data-slide="<?php echo $i; ?>"
      ></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
