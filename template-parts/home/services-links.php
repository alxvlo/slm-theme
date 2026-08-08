<?php
if (!defined('ABSPATH')) exit;

$services_url  = home_url('/services/');
$pid           = get_option('page_on_front');

/**
 * Get a service background image URL via ACF field, falling back to a theme file.
 */
function slm_svc_bg(string $field, int $pid, string $fallback = ''): string {
  if (!function_exists('get_field')) return $fallback;
  $val = get_field($field, $pid);
  if (!$val) return $fallback;
  if (is_numeric($val)) {
    $url = wp_get_attachment_image_url((int) $val, 'large');
    return ($url !== false) ? $url : $fallback;
  }
  return is_string($val) ? esc_url_raw($val) : $fallback;
}

$theme_uri = get_template_directory_uri();

// Service data: label, desc, icon, field, URL, fallback image
$services = [
  [
    'title'    => get_post_meta($pid, 'hp_service_1_title', true) ?: 'Real Estate Photography',
    'body'     => get_post_meta($pid, 'hp_service_1_body', true) ?: 'MLS-ready photos that make listings stop the scroll and attract buyers.',
    'url'      => home_url('/service-real-estate-photography/'),
    'field'    => 'hp_svc_1_img',
    'fallback' => $theme_uri . '/assets/img/Homepage1.jpeg',
    'icon'     => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="1.8"/>',
  ],
  [
    'title'    => get_post_meta($pid, 'hp_service_2_title', true) ?: 'Cinematic Listing Videos',
    'body'     => get_post_meta($pid, 'hp_service_2_body', true) ?: 'Smooth, modern walkthroughs built for MLS and social media.',
    'url'      => home_url('/service-real-estate-videography/'),
    'field'    => 'hp_svc_2_img',
    'fallback' => $theme_uri . '/assets/img/Homepage2.jpg',
    'icon'     => '<rect x="2" y="7" width="15" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="m17 9 5-2v10l-5-2V9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>',
  ],
  [
    'title'    => get_post_meta($pid, 'hp_service_3_title', true) ?: 'Social Media Content',
    'body'     => get_post_meta($pid, 'hp_service_3_body', true) ?: 'Custom Reels and Shorts designed to grow your presence and drive engagement.',
    'url'      => home_url('/social-media-packages/'),
    'field'    => 'hp_svc_3_img',
    'fallback' => $theme_uri . '/assets/img/Homepage3.jpg',
    'icon'     => '<rect x="5" y="2" width="14" height="20" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M10 9.5 15 12l-5 2.5V9.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>',
  ],
  [
    'title'    => get_post_meta($pid, 'hp_service_4_title', true) ?: 'Business Branding Content',
    'body'     => get_post_meta($pid, 'hp_service_4_body', true) ?: 'Professional photo and video that attracts clients and elevates your brand.',
    'url'      => home_url('/services/'),
    'field'    => 'hp_svc_4_img',
    'fallback' => $theme_uri . '/assets/img/Homepage4.jpg',
    'icon'     => '<rect x="2" y="7" width="20" height="14" rx="2.5" stroke="currentColor" stroke-width="1.8"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" stroke="currentColor" stroke-width="1.8"/>',
  ],
  [
    'title'    => get_post_meta($pid, 'hp_service_5_title', true) ?: 'Drone Photography & Video',
    'body'     => get_post_meta($pid, 'hp_service_5_body', true) ?: 'Aerial imagery that showcases land, views, and property surroundings.',
    'url'      => home_url('/service-drone-photography/'),
    'field'    => 'hp_svc_5_img',
    'fallback' => $theme_uri . '/assets/img/Homepage5.jpg',
    'icon'     => '<circle cx="12" cy="12" r="2" stroke="currentColor" stroke-width="1.8"/><path d="M5 5.5 8.5 9M19 5.5 15.5 9M5 18.5 8.5 15M19 18.5 15.5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="4" cy="5" r="2" stroke="currentColor" stroke-width="1.8"/><circle cx="20" cy="5" r="2" stroke="currentColor" stroke-width="1.8"/><circle cx="4" cy="19" r="2" stroke="currentColor" stroke-width="1.8"/><circle cx="20" cy="19" r="2" stroke="currentColor" stroke-width="1.8"/>',
  ],
  [
    'title'    => get_post_meta($pid, 'hp_service_6_title', true) ?: 'Zillow / Marketing Add-Ons',
    'body'     => get_post_meta($pid, 'hp_service_6_body', true) ?: 'Zillow walkthroughs, marketing add-ons, and partnership packages available.',
    'url'      => home_url('/services/'),
    'field'    => 'hp_svc_6_img',
    'fallback' => $theme_uri . '/assets/img/Homepage6.jpg',
    'icon'     => '<path d="M12 2 2 7l10 5 10-5-10-5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M2 12l10 5 10-5M2 17l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
  ],
];
?>

<section id="services" class="home-services" aria-labelledby="home-services-title">
  <div class="container">

    <header class="home-services__header js-reveal">
      <span class="section-eyebrow"><?php echo esc_html(get_post_meta($pid, 'hp_services_eyebrow', true) ?: 'What We Offer'); ?></span>
      <h2 id="home-services-title"><?php echo esc_html(get_post_meta($pid, 'hp_services_headline', true) ?: 'Professional Media, Every Format'); ?></h2>
      <p><?php echo esc_html(get_post_meta($pid, 'hp_services_subheadline', true) ?: 'Professional photo, video, and content services — built for agents and businesses who want to stand out.'); ?></p>
    </header>

    <div class="home-services__bento js-reveal">
      <?php foreach ($services as $i => $svc):
        $bg = slm_svc_bg($svc['field'], (int) $pid, $svc['fallback']);
      ?>
        <a
          class="home-svcCard home-svcCard--<?php echo $i + 1; ?>"
          href="<?php echo esc_url($svc['url']); ?>"
          aria-label="<?php echo esc_attr($svc['title']); ?>"
          <?php if ($bg): ?>style="--svc-bg: url('<?php echo esc_url($bg); ?>')"<?php endif; ?>
        >
          <div class="home-svcCard__inner">
            <span class="home-svcCard__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none"><?php echo $svc['icon']; ?></svg>
            </span>
            <div class="home-svcCard__text">
              <h3><?php echo esc_html($svc['title']); ?></h3>
              <p><?php echo esc_html($svc['body']); ?></p>
            </div>
            <span class="home-svcCard__arrow" aria-hidden="true">→</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="home-services__footer js-reveal">
      <p class="home-services__pricing"><?php echo esc_html(get_post_meta($pid, 'hp_services_pricing_line', true) ?: 'Pricing starting as low as $145 — no membership required to book.'); ?></p>
      <a class="btn btn--outline home-services__viewAll" href="<?php echo esc_url($services_url); ?>">View All Services</a>
    </div>

  </div>
</section>
