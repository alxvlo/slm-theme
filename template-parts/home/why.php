<?php
if (!defined('ABSPATH')) exit;
$contact_url = home_url('/contact/');
?>

<section class="home-why home-section--alt" aria-labelledby="home-why-title">
  <div class="container">
    <header class="home-why__header">
      <h2 id="home-why-title">Why Showcase Listings Media</h2>
      <p>The difference between generic media and a tailored approach.</p>
    </header>

    <div class="home-why__grid">
      <article class="home-why__card">
        <h3>Locally Based</h3>
        <p>We are based right here in Jacksonville, FL, understanding the local market better than anyone.</p>
      </article>

      <article class="home-why__card">
        <h3>Fast Turnaround</h3>
        <p>Speed is critical. We deliver most assets within 24–48 hours to get your listings live fast.</p>
      </article>

      <article class="home-why__card">
        <h3>Tailored Approach</h3>
        <p>No cookie-cutter templates here. We design specifically for the agent’s or business's brand.</p>
      </article>

      <article class="home-why__card">
        <h3>Focus on Conversions</h3>
        <p>Our media isn't just pretty; it's strategically designed to capture attention and convert leads.</p>
      </article>
    </div>

    <div class="home-why__cta">
      <a href="<?php echo esc_url($contact_url); ?>" class="btn btn--accent">Schedule a consultation</a>
    </div>
  </div>
</section>