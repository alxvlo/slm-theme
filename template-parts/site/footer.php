<?php
if (!defined('ABSPATH')) exit;

$login_url = add_query_arg('mode', 'login', slm_login_url());

$default_email = 'Showcaselistingsmedia@gmail.com';
$default_phone = '(904)-294-5809';

$email = function_exists('slm_footer_setting') ? slm_footer_setting('slm_footer_email', $default_email) : $default_email;
$phone = function_exists('slm_footer_setting') ? slm_footer_setting('slm_footer_phone', $default_phone) : $default_phone;
if ($email === '') { $email = $default_email; }
if ($phone === '') { $phone = $default_phone; }

$phone_href = preg_replace('/\D+/', '', $phone);
if (strlen($phone_href) === 10) { $phone_href = '1' . $phone_href; }
if ($phone_href !== '') { $phone_href = '+' . ltrim($phone_href, '+'); }
$addr1 = function_exists('slm_footer_setting') ? slm_footer_setting('slm_footer_address_line1', '1230 Glengarry Rd.') : '1230 Glengarry Rd.';
$addr2 = function_exists('slm_footer_setting') ? slm_footer_setting('slm_footer_address_line2', 'Jacksonville, FL. 32207') : 'Jacksonville, FL. 32207';
if ($addr1 === '') { $addr1 = '1230 Glengarry Rd.'; }
if ($addr2 === '') { $addr2 = 'Jacksonville, FL. 32207'; }

$default_social = [
  'youtube' => 'https://www.youtube.com/@TheShowcaselistingsmedia',
  'facebook' => 'https://www.facebook.com/profile.php?id=61578356661096',
  'instagram' => 'https://www.instagram.com/brittneyshowcaselistingsmedia/',
  'threads' => 'https://www.threads.com/@brittneyshowcaselistingsmedia',
  'linkedin' => '',
];

$social = [
  'youtube' => function_exists('slm_footer_setting') ? slm_footer_setting('slm_social_youtube', $default_social['youtube']) : $default_social['youtube'],
  'facebook' => function_exists('slm_footer_setting') ? slm_footer_setting('slm_social_facebook', $default_social['facebook']) : $default_social['facebook'],
  'instagram' => function_exists('slm_footer_setting') ? slm_footer_setting('slm_social_instagram', $default_social['instagram']) : $default_social['instagram'],
  'threads' => function_exists('slm_footer_setting') ? slm_footer_setting('slm_social_threads', $default_social['threads']) : $default_social['threads'],
  'linkedin' => function_exists('slm_footer_setting') ? slm_footer_setting('slm_social_linkedin', $default_social['linkedin']) : $default_social['linkedin'],
];
foreach ($default_social as $network => $url) {
  if ($url !== '' && (!isset($social[$network]) || $social[$network] === '')) {
    $social[$network] = $url;
  }
}

$has_social = false;
foreach ($social as $url) {
  if (is_string($url) && $url !== '') { $has_social = true; break; }
}

$privacy_page = get_page_by_path('privacy-policy');
$privacy_url = $privacy_page ? get_permalink($privacy_page->ID) : home_url('/privacy-policy/');
$terms_page = get_page_by_path('terms-of-service');
$terms_url = $terms_page ? get_permalink($terms_page->ID) : home_url('/terms-of-service/');

$has_footer_menu = has_nav_menu('footer');
?>

<div class="footer">
  <div class="footer__brand">
    <strong><?php bloginfo('name'); ?></strong>
    <p class="footer__desc"><strong>Where Listings Become Showcase-Worthy.</strong> Premium real estate media for agents and broker teams that want stronger listing presentation and faster marketing execution.</p>
  </div>

  <div class="footer__col">
    <?php if ($has_social): ?>
      <h3 class="footer__title">Follow Us</h3>
      <div class="footer__social">
        <?php if ($social['youtube'] !== ''): ?>
          <a href="<?php echo esc_url($social['youtube']); ?>" target="_blank" rel="noopener" aria-label="YouTube">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.6 7.2a3 3 0 0 0-2.1-2.1C17.7 4.6 12 4.6 12 4.6s-5.7 0-7.5.5A3 3 0 0 0 2.4 7.2 31.2 31.2 0 0 0 2 12a31.2 31.2 0 0 0 .4 4.8 3 3 0 0 0 2.1 2.1c1.8.5 7.5.5 7.5.5s5.7 0 7.5-.5a3 3 0 0 0 2.1-2.1A31.2 31.2 0 0 0 22 12a31.2 31.2 0 0 0-.4-4.8ZM10 15.5v-7l6 3.5-6 3.5Z" fill="currentColor"/></svg>
          </a>
        <?php endif; ?>
        <?php if ($social['facebook'] !== ''): ?>
          <a href="<?php echo esc_url($social['facebook']); ?>" target="_blank" rel="noopener" aria-label="Facebook">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 22v-8h2.7l.4-3H13.5V9c0-.9.3-1.6 1.7-1.6h1.5V4.6c-.3 0-1.4-.1-2.7-.1-2.7 0-4.5 1.6-4.5 4.6v2H7v3h2.5v8h4Z" fill="currentColor"/></svg>
          </a>
        <?php endif; ?>
        <?php if ($social['instagram'] !== ''): ?>
          <a href="<?php echo esc_url($social['instagram']); ?>" target="_blank" rel="noopener" aria-label="Instagram">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm10 2H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3Zm-5 4.5A3.5 3.5 0 1 1 8.5 12 3.5 3.5 0 0 1 12 8.5Zm0 2A1.5 1.5 0 1 0 13.5 12 1.5 1.5 0 0 0 12 10.5ZM18.2 6.7a.9.9 0 1 1-.9-.9.9.9 0 0 1 .9.9Z" fill="currentColor"/></svg>
          </a>
        <?php endif; ?>
        <?php if ($social['threads'] !== ''): ?>
          <a href="<?php echo esc_url($social['threads']); ?>" target="_blank" rel="noopener" aria-label="Threads">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16.5 10.8c-.2-2.2-1.5-3.4-3.9-3.5-2.9-.1-4.8 1.7-5 4.4-.1 1.7.6 3.2 1.9 4.1 1.1.8 2.5 1.1 3.9.8 1.5-.2 2.7-1.2 3.2-2.6.1-.2.1-.5.1-.8-.1-1.2-.9-2-2.1-2.2-.9-.2-1.8-.2-2.7 0-.8.2-1.4.8-1.5 1.6-.1.9.4 1.6 1.3 2 .7.3 1.6.3 2.3 0 .5-.2.8-.7.8-1.2 0-.1 0-.2-.1-.3h1.4c.1.3.1.6.1.9 0 1.2-.8 2.3-2 2.8-1 .4-2.2.4-3.2 0-1.6-.6-2.4-2.2-2.1-4 .3-1.6 1.5-2.8 3.2-3 1.2-.2 2.4.1 3.3.9.7.6 1 1.4 1.1 2.3h-1.3Zm-2.9 2.4c-.5 0-1 .1-1.4.2-.4.1-.6.4-.6.7 0 .4.3.7.8.8.7.2 1.5-.1 1.9-.7.1-.1.1-.2.1-.4-.2-.4-.5-.6-.8-.6Z" fill="currentColor"/></svg>
          </a>
        <?php endif; ?>
        <?php if ($social['linkedin'] !== ''): ?>
          <a href="<?php echo esc_url($social['linkedin']); ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.5 7.2A1.7 1.7 0 1 1 6.5 4a1.7 1.7 0 0 1 0 3.2ZM5 20V9h3v11H5Zm5 0V9h2.9v1.5h.1A3.2 3.2 0 0 1 16 8.8c3.2 0 4 2.1 4 4.8V20h-3v-5.6c0-1.3 0-3-1.9-3s-2.2 1.4-2.2 2.9V20h-3Z" fill="currentColor"/></svg>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <h3 class="footer__title" style="margin-top:16px;">Contact</h3>
    <ul class="footer__list">
      <?php if ($phone !== ''): ?>
        <li>Call us at <a href="tel:<?php echo esc_attr($phone_href); ?>"><?php echo esc_html($phone); ?></a></li>
      <?php endif; ?>
      <?php if ($email !== ''): ?>
        <li>Contact us at <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></li>
      <?php endif; ?>
    </ul>
  </div>

  <div class="footer__col">
    <h3 class="footer__title">Address</h3>
    <ul class="footer__list">
      <?php if ($addr1 !== ''): ?><li><?php echo esc_html($addr1); ?></li><?php endif; ?>
      <?php if ($addr2 !== ''): ?><li><?php echo esc_html($addr2); ?></li><?php endif; ?>
      <?php if ($addr1 === '' && $addr2 === ''): ?><li>Address coming soon.</li><?php endif; ?>
    </ul>
  </div>

  <div class="footer__col">
    <h3 class="footer__title">Important Links</h3>
    <?php if ($has_footer_menu): ?>
      <?php
        wp_nav_menu([
          'theme_location' => 'footer',
          'container' => false,
          'menu_class' => 'footer__menu',
          'fallback_cb' => '__return_false',
        ]);
      ?>
    <?php endif; ?>
    <ul class="footer__menu">
      <li><a href="<?php echo esc_url($privacy_url); ?>">Privacy Policy</a></li>
      <li><a href="<?php echo esc_url($terms_url); ?>">Terms of Service</a></li>
    </ul>
  </div>

  <div class="footer__meta">
    <small>&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. All rights reserved.</small>
    <a href="<?php echo esc_url($login_url); ?>">Client Portal Login</a>
  </div>
</div>
