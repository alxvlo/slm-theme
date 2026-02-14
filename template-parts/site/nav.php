<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = is_user_logged_in();
$login_url = add_query_arg('mode', 'login', slm_login_url());
$signup_url = add_query_arg('mode', 'signup', slm_login_url());
$dashboard_url = $is_logged_in ? slm_dashboard_url() : $login_url;
$order_url = $is_logged_in ? $dashboard_url : $signup_url;

$logo_src = get_template_directory_uri() . '/assets/img/logo-icon.png';
$logo_abs = get_template_directory() . '/assets/img/logo-icon.png';
$has_logo = file_exists($logo_abs);
?>

<nav class="nav" aria-label="Primary">
  <a class="nav__brand" href="<?php echo esc_url(home_url('/')); ?>">
    <span class="nav__brandLogo" aria-hidden="true">
      <?php if ($has_logo): ?>
        <img class="nav__logoImg" src="<?php echo esc_url($logo_src); ?>" alt="" width="34" height="34" decoding="async" loading="eager">
      <?php else: ?>
        <span class="nav__logoFallback">SLM</span>
      <?php endif; ?>
    </span>
    <span class="nav__brandText"><?php echo esc_html(get_bloginfo('name')); ?></span>
  </a>

  <div class="nav__center">
    <?php
      wp_nav_menu([
        'theme_location' => 'primary',
        'container' => false,
        'menu_class' => 'nav__menu',
        'fallback_cb' => '__return_false',
      ]);
    ?>
  </div>

  <div class="nav__right">
    <?php if ($is_logged_in): ?>
      <a class="nav__login" href="<?php echo esc_url($dashboard_url); ?>">Dashboard</a>
      <a class="btn btn--secondary" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
    <?php else: ?>
      <a class="nav__login" href="<?php echo esc_url($login_url); ?>">Login</a>
      <a class="btn" href="<?php echo esc_url($order_url); ?>">Order Now</a>
    <?php endif; ?>
  </div>
</nav>
