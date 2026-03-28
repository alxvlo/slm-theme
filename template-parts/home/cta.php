<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in ? add_query_arg('view', 'place-order', slm_portal_url()) : add_query_arg('mode', 'signup', slm_login_url());
$cta_label = $is_logged_in ? 'Place Order' : 'Create Account';
?>

<section class="home-start">
  <div class="container">
    <div class="home-start__card">
      <h2>Ready to stand out?</h2>
      <p>Content that works for you. Start winning more listings and attracting more clients.</p>
      <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>">Book a Shoot Today</a>
    </div>
  </div>
</section>
