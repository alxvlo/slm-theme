<?php
if (!defined('ABSPATH')) exit;

$cta_url = add_query_arg('view', 'place-order', slm_portal_url());
?>

<section class="home-start">
  <div class="container">
    <div class="home-start__card">
      <h2>Ready to stand out?</h2>
      <p>Content that works for you. Start winning more listings and attracting more clients.</p>
      <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>">Book a Shoot</a>
    </div>
  </div>
</section>
