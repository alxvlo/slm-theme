<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = is_user_logged_in();
$cta_url      = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
$cta_label    = $is_logged_in ? 'Place Order' : 'Book a Shoot';
$contact_url  = home_url('/contact/');
?>

<section class="svc-final-cta" aria-label="Get started with Showcase Listings Media">
  <div class="container">
    <h2>Ready to Stand Out?</h2>
    <p>Whether you're an agent or a business, your content should work for you — not blend in.</p>
    <div class="svc-final-cta__btns">
      <a class="btn svc-final-cta__primary" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
      <a class="btn svc-final-cta__secondary" href="tel:+19042945809">Call (904) 294-5809</a>
      <a class="btn svc-final-cta__secondary" href="<?php echo esc_url($contact_url); ?>">Send a Message</a>
    </div>
  </div>
</section>
