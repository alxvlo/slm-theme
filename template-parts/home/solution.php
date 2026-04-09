<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');

// Solution image: ACF field first, then theme fallback
$solution_img = '';
if (function_exists('get_field')) {
  $val = get_field('hp_solution_image', (int) $pid);
  if ($val && is_numeric($val)) {
    $url = wp_get_attachment_image_url((int) $val, 'large');
    $solution_img = ($url !== false) ? $url : '';
  }
}
if (!$solution_img) {
  $solution_img = get_template_directory_uri() . '/assets/img/Homepage3.jpg';
}
?>

<section id="home-solution" class="home-solution" aria-labelledby="home-solution-title">
  <div class="container">
    <div class="home-solution__grid">

      <!-- Left: text -->
      <div class="home-solution__text js-reveal">
        <span class="section-eyebrow"><?php echo esc_html(get_post_meta($pid, 'hp_solution_eyebrow', true) ?: 'Our Approach'); ?></span>
        <h2 id="home-solution-title"><?php echo esc_html(get_post_meta($pid, 'hp_solution_headline', true) ?: "We don't just create content — we create attention."); ?></h2>
        <p class="home-solution__lead"><?php echo esc_html(get_post_meta($pid, 'hp_solution_body', true) ?: "At Showcase Listings Media, every project is approached with intention. We don't shoot to fill a gallery."); ?></p>

        <ul class="home-solution__pillars">
          <li>
            <span aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span><strong><?php echo esc_html(get_post_meta($pid, 'hp_solution_point_1', true) ?: 'Stop the scroll'); ?></strong> — Content that makes people pause, look twice, and take action.</span>
          </li>
          <li>
            <span aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span><strong><?php echo esc_html(get_post_meta($pid, 'hp_solution_point_2', true) ?: 'Elevate your brand'); ?></strong> — We shoot for your identity, not just the space.</span>
          </li>
          <li>
            <span aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span><strong><?php echo esc_html(get_post_meta($pid, 'hp_solution_point_3', true) ?: 'Stand out in a crowded market'); ?></strong> — Whether it's a listing or a business — we make people pay attention.</span>
          </li>
          <?php if ($pt4 = get_post_meta($pid, 'hp_solution_point_4', true)): ?>
          <li>
            <span aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span><strong><?php echo esc_html($pt4); ?></strong> — Built for impact.</span>
          </li>
          <?php endif; ?>
        </ul>

        <a class="btn home-solution__cta" href="<?php echo esc_url(home_url('/portfolio/')); ?>">
          <?php echo esc_html(get_post_meta($pid, 'hp_solution_cta', true) ?: 'See Our Work'); ?>
        </a>
      </div>

      <!-- Right: media image -->
      <div class="home-solution__media js-reveal">
        <div class="home-solution__img-wrap">
          <img
            src="<?php echo esc_url($solution_img); ?>"
            alt="Showcase Listings Media — professional real estate photography"
            loading="lazy"
            decoding="async"
            width="700"
            height="525"
          >
          <div class="home-solution__img-accent" aria-hidden="true"></div>
        </div>
      </div>

    </div>
  </div>
</section>
