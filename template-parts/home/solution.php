<?php
if (!defined('ABSPATH')) exit;
$portfolio_url = slm_page_url_by_template('templates/page-portfolio.php', '/portfolio/');
?>

<section class="home-solution home-section--alt" aria-labelledby="home-solution-title">
  <div class="container home-solution__container">
    <h2 id="home-solution-title" class="home-solution__title">We make your listings pop and your business grow.</h2>
    <p class="home-solution__text">
      We strategically and intentionally help you grow. We help agents and businesses stand out, get more eyes on your brand, win more listings, and attract more clients. Every piece of media is crafted for one purpose: results.
    </p>
    <a href="<?php echo esc_url(add_query_arg('view', 'place-order', slm_portal_url())); ?>" class="btn btn--accent" style="margin-right: 12px;">Book a Shoot</a>
    <a href="<?php echo esc_url($portfolio_url); ?>" class="btn btn--secondary">See Results</a>
  </div>
</section>
