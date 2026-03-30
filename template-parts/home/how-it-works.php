<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = is_user_logged_in();
$book_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
?>

<section class="home-how" aria-labelledby="home-how-title">
  <div class="container">
    <header class="home-how__header">
      <h2 id="home-how-title">How It Works</h2>
      <p>From booking to delivery — here's exactly what to expect.</p>
    </header>

    <div class="home-how__grid">
      <article class="home-howCard">
        <span class="home-howCard__step">1</span>
        <h3>Book Your Shoot</h3>
        <p>Choose your service, pick a date, and schedule online in minutes.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">2</span>
        <h3>We Show Up Ready</h3>
        <p>Our team arrives prepared to capture your property or brand at its best.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">3</span>
        <h3>We Capture & Create</h3>
        <p>Professional photos, video, and content — shot with intention, not just to fill a gallery.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">4</span>
        <h3>Edits Delivered in 24–48 Hours</h3>
        <p>Your finished assets land in your inbox, ready for MLS, social, and marketing.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">5</span>
        <h3>You Post & Stand Out</h3>
        <p>Go live with content that stops the scroll and gets your listing or brand noticed.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">6</span>
        <h3>More Clients. More Deals. Repeat.</h3>
        <p>The results speak for themselves — and we're ready for your next shoot.</p>
      </article>
    </div>

    <div class="home-how__cta">
      <a class="btn home-heroSlider__btn--primary" href="<?php echo esc_url($book_url); ?>">Book Your Shoot Now</a>
    </div>
  </div>
</section>
