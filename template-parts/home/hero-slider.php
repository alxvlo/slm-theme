<?php
if (!defined('ABSPATH')) exit;

$theme_uri = get_template_directory_uri();

// Replace each image path below with your real hero photos when ready.
$slides = [
  $theme_uri . '/assets/img/Homepage1.jpeg',
  $theme_uri . '/assets/img/Homepage2.jpg',
  $theme_uri . '/assets/img/Homepage3.jpg',
  $theme_uri . '/assets/img/Homepage4.jpg',
  $theme_uri . '/assets/img/Homepage5.jpg',
  $theme_uri . '/assets/img/Homepage6.jpg',
];

$is_logged_in = is_user_logged_in();
$order_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
$portfolio_url = home_url('/portfolio/');
$trust_points = [
  '24-hour standard delivery',
  'Dedicated client support',
  'Media quality built for conversion',
];
?>

<section class="home-heroSlider" aria-label="Featured Property Media">
  <div class="home-heroSlider__slides" data-home-slider>
    <?php foreach ($slides as $i => $img): ?>
      <div
        class="home-heroSlider__slide <?php echo $i === 0 ? 'is-active' : ''; ?>"
        data-bg-image="<?php echo esc_url($img); ?>"
        aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>"
      ></div>
    <?php endforeach; ?>
    <div class="home-heroSlider__overlay" aria-hidden="true"></div>
  </div>

  <div class="container home-heroSlider__content">
    <h1 class="js-hero-title">Real Estate Media That Stops Scroll & Sells Listings Faster</h1>
    <p class="js-hero-sub">Photo, video, and content designed to make your listings stand out and get agents and businesses more deals.</p>

    <div class="home-heroSlider__actions js-hero-actions">
      <a class="btn home-heroSlider__btn--primary" href="<?php echo esc_url($order_url); ?>">Book a Shoot</a>
      <a class="btn home-heroSlider__btn--secondary" href="<?php echo esc_url($portfolio_url); ?>">View Our Work</a>
    </div>

    <p class="home-heroSlider__tagline js-hero-tagline">Trusted by agents and brands across North Florida</p>

    <ul class="home-heroSlider__trust js-hero-trust" aria-label="Why clients choose us">
      <?php foreach ($trust_points as $point): ?>
        <li><?php echo esc_html($point); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
