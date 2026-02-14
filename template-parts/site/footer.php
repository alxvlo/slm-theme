<?php
if (!defined('ABSPATH')) exit;

$login_url = add_query_arg('mode', 'login', slm_login_url());
?>

<div class="footer">
  <div class="footer__brand">
    <strong><?php bloginfo('name'); ?></strong>
    <p>Premium real estate media for agents and broker teams that want stronger listing presentation and faster marketing execution.</p>
  </div>

  <div class="footer__links">
    <?php
      wp_nav_menu([
        'theme_location' => 'footer',
        'container' => false,
        'menu_class' => 'footer__menu',
        'fallback_cb' => '__return_false',
      ]);
    ?>
  </div>

  <div class="footer__meta">
    <small>&copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. All rights reserved.</small>
    <a href="<?php echo esc_url($login_url); ?>">Client Portal Login</a>
  </div>
</div>
