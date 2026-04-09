<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');

// CTA background image: ACF field or fallback
$cta_bg = '';
if (function_exists('get_field')) {
  $val = get_field('hp_cta_bg_image', (int) $pid);
  if ($val && is_numeric($val)) {
    $url = wp_get_attachment_image_url((int) $val, 'full');
    $cta_bg = ($url !== false) ? $url : '';
  }
}
if (!$cta_bg) {
  $cta_bg = get_template_directory_uri() . '/assets/img/Homepage1.jpeg';
}

$is_logged_in = is_user_logged_in();
$cta_url      = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
$cta_label    = $is_logged_in ? 'Place Order' : 'Book a Shoot';
$contact_url  = home_url('/contact/');
?>

<section
  class="home-finalcta"
  aria-label="Get started with Showcase Listings Media"
  style="--cta-bg: url('<?php echo esc_url($cta_bg); ?>')"
>
  <div class="home-finalcta__overlay" aria-hidden="true"></div>
  <div class="container home-finalcta__inner js-reveal">
    <span class="section-eyebrow home-finalcta__eyebrow"><?php echo esc_html(get_post_meta($pid, 'hp_finalcta_eyebrow', true) ?: 'Ready to Stand Out?'); ?></span>
    <h2><?php echo esc_html(get_post_meta($pid, 'hp_finalcta_headline', true) ?: 'Ready to stand out?'); ?></h2>
    <p><?php echo esc_html(get_post_meta($pid, 'hp_finalcta_sub', true) ?: "Whether you're an agent or a business, your content should work for you — not blend in."); ?></p>
    <div class="home-finalcta__btns">
      <a class="btn home-finalcta__primary" href="<?php echo esc_url($cta_url); ?>">
        <?php echo esc_html(get_post_meta($pid, 'hp_finalcta_btn_primary', true) ?: 'Book a Shoot Today'); ?>
      </a>
      <a class="btn home-finalcta__secondary" href="tel:+19042945809">
        <?php echo esc_html(get_post_meta($pid, 'hp_finalcta_btn_call', true) ?: 'Call (904) 294-5809'); ?>
      </a>
      <a class="btn home-finalcta__secondary" href="<?php echo esc_url($contact_url); ?>">
        <?php echo esc_html(get_post_meta($pid, 'hp_finalcta_btn_message', true) ?: 'Send a Message'); ?>
      </a>
    </div>
  </div>
</section>
