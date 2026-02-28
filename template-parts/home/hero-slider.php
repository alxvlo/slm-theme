<?php
if (!defined('ABSPATH')) exit;

$theme_uri = get_template_directory_uri();

// Replace each image path below with your real hero photos when ready.
$slides = [
  $theme_uri . '/assets/img/Homepage1.jpg',
  $theme_uri . '/assets/img/Homepage2.jpg',
  $theme_uri . '/assets/img/Homepage3.jpg',
  $theme_uri . '/assets/media/drone-photos/08-52-aerial-front-exterior-1.jpg',
  $theme_uri . '/assets/media/photos/08-1-front-exterior.jpg',
  $theme_uri . '/assets/media/twilight/01-99-twilight-aerial-front-exterior-3.jpg',
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
        style="background-image: url('<?php echo esc_url($img); ?>');"
        aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>"
      ></div>
    <?php endforeach; ?>
    <div class="home-heroSlider__overlay" aria-hidden="true"></div>
  </div>

  <div class="container home-heroSlider__content">
    <h1>Elevate Your Listings with Premium Media</h1>
    <p>Professional real estate photography, video, and aerial services that help your properties stand out and sell faster. We also help businesses with broader content needs through Google-ready 3D scan delivery, social video production, and promotional photography.</p>
    <div class="home-heroSlider__actions">
      <a class="btn btn--accent" href="<?php echo esc_url($order_url); ?>">Order Now</a>
      <a class="btn btn--ghostLight" href="<?php echo esc_url(home_url('/services/')); ?>">View Services</a>
      <a class="btn btn--outlineLight" href="<?php echo esc_url($contact_url); ?>">Talk to Us</a>
    </div>

    <ul class="home-heroSlider__trust" aria-label="Why clients choose us">
      <?php foreach ($trust_points as $point): ?>
        <li><?php echo esc_html($point); ?></li>
      <?php endforeach; ?>
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
