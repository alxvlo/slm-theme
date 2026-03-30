<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = is_user_logged_in();
$contact_url  = home_url('/contact/');
$services_url = home_url('/services/');
?>

<section id="home-who" class="home-who" aria-labelledby="home-who-title">
  <div class="container">
    <header class="home-who__header js-reveal">
      <h2 id="home-who-title">Built for agents. Designed for brands.</h2>
      <p>We serve two audiences — and we speak directly to both.</p>
    </header>

    <div class="home-who__grid js-reveal">
      <div class="home-who__col">
        <h3>For Real Estate Agents</h3>
        <ul>
          <li>Win more listings with stronger visuals</li>
          <li>Stand out from other agents in your market</li>
          <li>Showcase homes at a higher level</li>
          <li>Build a recognizable personal brand</li>
        </ul>
        <a class="btn btn--outline-light home-who__cta" href="<?php echo esc_url($services_url); ?>">View Agent Services</a>
      </div>

      <div class="home-who__divider" aria-hidden="true"></div>

      <div class="home-who__col">
        <h3>For Businesses</h3>
        <ul>
          <li>Attract more clients with professional content</li>
          <li>Create a strong, lasting first impression</li>
          <li>Elevate your online presence and visibility</li>
          <li>Turn views and clicks into real customers</li>
        </ul>
        <a class="btn btn--outline-light home-who__cta" href="<?php echo esc_url($services_url); ?>">View Business Services</a>
      </div>
    </div>

    <div class="home-who__bottom js-reveal">
      <a class="btn btn--accent" href="<?php echo esc_url($contact_url); ?>">Let's Create Your Content</a>
    </div>
  </div>
</section>
