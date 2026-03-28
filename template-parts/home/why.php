<?php
if (!defined('ABSPATH')) exit;
$order_url = add_query_arg('view', 'place-order', slm_portal_url());
?>

<section class="home-why home-section--alt" aria-labelledby="home-why-title">
  <div class="container">
    <header class="home-why__header">
      <h2 id="home-why-title">Why Showcase Listings Media</h2>
      <p>We do things differently because you need different results.</p>
    </header>

    <div class="home-why__grid">
      <article class="home-why__card">
        <h3>Treating Every Listing Differently</h3>
        <p>Your listings shouldn't look like an assembly line. We adapt to the property and the brand.</p>
      </article>

      <article class="home-why__card">
        <h3>Focusing on Your Brand</h3>
        <p>It's not just about the house; it's about making you the trusted authority in your market.</p>
      </article>

      <article class="home-why__card">
        <h3>A Personalized Partnership</h3>
        <p>We get to know agents and businesses, building custom strategies instead of cookie-cutter packages.</p>
      </article>

      <article class="home-why__card">
        <h3>Content That Converts</h3>
        <p>Our media isn't just pretty; it's strategically designed to capture attention and win deals.</p>
      </article>
    </div>

    <div class="home-why__cta">
      <a href="<?php echo esc_url($order_url); ?>" class="btn btn--accent">Book a Shoot</a>
    </div>
  </div>
</section>
