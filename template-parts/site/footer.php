<div class="footer">
  <strong><?php bloginfo('name'); ?></strong>
  <?php
    wp_nav_menu([
      'theme_location' => 'footer',
      'container' => false,
      'menu_class' => 'footer__menu',
      'fallback_cb' => '__return_false',
    ]);
  ?>
  <small>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></small>
</div>
