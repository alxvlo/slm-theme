<?php
if (!defined('ABSPATH')) exit;
?>

<section class="home-how" aria-labelledby="home-how-title">
  <div class="container">
    <header class="home-how__header">
      <h2 id="home-how-title">Book › Shoot › Delivery › Go Live › Grow</h2>
      <p>A simplified process to remove friction and get your listings live fast.</p>
    </header>

    <div class="home-how__grid">
      <article class="home-howCard">
        <span class="home-howCard__step">1</span>
        <h3>Book</h3>
        <p>Easily select services and schedule your shoot.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">2</span>
        <h3>Shoot</h3>
        <p>We arrive on site to capture stunning media.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">3</span>
        <h3>Delivery</h3>
        <p>Receive your polished assets in 24-48 hours.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">4</span>
        <h3>Go Live & Grow</h3>
        <p>Stand out online, attract buyers, and win your next listing.</p>
      </article>
    </div>

    <div style="text-align: center; margin-top: 48px;">
      <a href="<?php echo esc_url(is_user_logged_in() ? add_query_arg('view', 'place-order', slm_portal_url()) : add_query_arg('mode', 'signup', slm_login_url())); ?>" class="btn btn--accent">Book a shoot</a>
    </div>
  </div>
</section>
