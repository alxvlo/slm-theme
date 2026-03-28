<?php
if (!defined('ABSPATH')) exit;
$order_url = add_query_arg('view', 'place-order', slm_portal_url());
?>

<section class="home-how" aria-labelledby="home-how-title">
  <div class="container">
    <header class="home-how__header">
      <h2 id="home-how-title">A simplified process to remove friction</h2>
      <p>Stop stressing over media. Book, shoot, and get back to business.</p>
    </header>

    <div class="home-how__grid" style="grid-template-columns: repeat(5, minmax(0, 1fr));">
      <article class="home-howCard">
        <span class="home-howCard__step">1</span>
        <h3>Book</h3>
        <p>Easily select services and schedule your shoot.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">2</span>
        <h3>Shoot</h3>
        <p>We arrive on site to capture your listing or business.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">3</span>
        <h3>Delivery</h3>
        <p>Receive your polished assets in 24-48 hours.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">4</span>
        <h3>Go Live</h3>
        <p>Post and let your media go to work for you.</p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">5</span>
        <h3>Grow</h3>
        <p>Attract buyers, win your next listing, and repeat.</p>
      </article>
    </div>

    <div style="text-align: center; margin-top: 48px;">
      <a href="<?php echo esc_url($order_url); ?>" class="btn btn--accent">Contact Now</a>
    </div>
  </div>
</section>
