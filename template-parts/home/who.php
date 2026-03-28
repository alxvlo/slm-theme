<?php
if (!defined('ABSPATH')) exit;
$order_url = is_user_logged_in()
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
?>

<section class="home-who home-section" aria-labelledby="home-who-title">
  <div class="container">
    <header class="home-who__header">
      <h2 id="home-who-title">Who We Help</h2>
    </header>

    <div class="home-who__grid">
      <article class="home-who__card">
        <h3>For Real Estate Agents</h3>
        <ul>
          <li><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg> Win more listings</li>
          <li><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg> Attract more buyers</li>
          <li><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg> Build a trusted brand</li>
        </ul>
        <a href="<?php echo esc_url($order_url); ?>" class="btn btn--accent">Book a Shoot</a>
      </article>

      <article class="home-who__card">
        <h3>For Businesses</h3>
        <ul>
          <li><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg> Attract new clients</li>
          <li><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg> Tell your brand story</li>
          <li><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg> Dominate social media</li>
        </ul>
        <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="btn btn--secondary">Contact Us</a>
      </article>
    </div>
  </div>
</section>