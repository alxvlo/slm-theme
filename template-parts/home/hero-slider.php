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
$contact_url = home_url('/contact/');
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
    <h1>Real Estate Media That Stops Scroll &amp; Sells Listings Faster</h1>
    <p>Photo, video and content designed to make your listings stand out and get agents and businesses more deals.</p>
    <div class="home-heroSlider__actions">
      <a class="btn btn--accent" href="<?php echo esc_url($order_url); ?>">Book a Shoot</a>
      <a class="btn btn--ghostLight" href="<?php echo esc_url(slm_page_url_by_template('templates/page-portfolio.php', '/portfolio/')); ?>">View Our Work</a>
    </div>

    <ul class="home-heroSlider__trust" aria-label="Why clients choose us">
      <li>Trusted by agents and brands across North Florida.</li>
    </ul>

    <div class="home-heroSlider__dots" data-home-slider-dots>
      <?php foreach ($slides as $i => $img): ?>
        <button
          type="button"
          class="home-heroSlider__dot <?php echo $i === 0 ? 'is-active' : ''; ?>"
          data-slide-index="<?php echo esc_attr((string) $i); ?>"
          aria-label="<?php echo esc_attr('Show slide ' . ($i + 1)); ?>"
          aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>"
        ></button>
      <?php endforeach; ?>
    </div>
  </div>
</section>
