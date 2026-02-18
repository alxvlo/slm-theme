<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in ? add_query_arg('view', 'place-order', slm_portal_url()) : add_query_arg('mode', 'signup', slm_login_url());
$cta_label = $is_logged_in ? 'Place Order' : 'Create Account';
?>

<section class="home-start">
  <div class="container">
    <div class="home-start__card">
      <h2>Ready to Get Started?</h2>
      <p>Join agents and brokers who trust us to showcase their listings.</p>
      <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
    </div>
  </div>
</section>
