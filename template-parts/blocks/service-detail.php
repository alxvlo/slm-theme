<?php
if (!defined('ABSPATH')) exit;

$args = wp_parse_args($args ?? [], [
  'title' => '',
  'subtitle' => '',
  'hero_image' => '',
  'description_image' => '',
  'description' => [],   // array of paragraphs
  'benefits' => [],      // array of ['title' => '', 'description' => '']
  'why_choose' => [],    // array of strings
  'book_url' => add_query_arg('mode', 'signup', slm_login_url()),
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Ready to Break the Standard?',
  'cta_text' => 'Build stronger listing presentations, elevate your brand perception, and create marketing momentum with a partner invested in your long-term success.',
]);

$is_logged_in = is_user_logged_in();
$primary_url = $is_logged_in ? slm_dashboard_url() : (string) $args['book_url'];
$primary_label = $is_logged_in ? 'Go to Dashboard' : (string) $args['book_label'];
$secondary_url = $is_logged_in ? slm_dashboard_url() : add_query_arg('mode', 'login', slm_login_url());
$secondary_label = $is_logged_in ? 'Open Dashboard' : 'Already Have an Account? Sign In';

$render_media = static function (string $src): void {
  if ($src === '') return;

  $path = (string) parse_url($src, PHP_URL_PATH);
  $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
  if (in_array($ext, ['mp4', 'webm', 'mov'], true)) {
    ?>
    <video src="<?php echo esc_url($src); ?>" controls playsinline preload="metadata"></video>
    <?php
    return;
  }

  ?>
  <img src="<?php echo esc_url($src); ?>" alt="" loading="lazy" decoding="async">
  <?php
};
?>

<main class="service-page">
  <section class="service-hero">
    <div class="container">
      <div class="service-hero__grid">
        <div class="service-hero__copy">
          <p class="service-kicker">Where Listings Become Showcase-Worthy</p>
          <h1><?php echo esc_html($args['title']); ?></h1>
          <p><?php echo esc_html($args['subtitle']); ?></p>
          <p class="service-hero__actions">
            <a class="btn btn--accent" href="<?php echo esc_url($primary_url); ?>"><?php echo esc_html($primary_label); ?></a>
          </p>
        </div>

        <?php if (!empty($args['hero_image'])): ?>
          <div class="service-mediaCard">
            <?php $render_media((string) $args['hero_image']); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="service-section service-section--alt">
    <div class="container">
      <div class="service-overview">
        <div>
          <h2>Overview</h2>
          <?php foreach ((array) $args['description'] as $p): ?>
            <p><?php echo esc_html($p); ?></p>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($args['description_image'])): ?>
          <div class="service-mediaCard">
            <?php $render_media((string) $args['description_image']); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($args['benefits'])): ?>
    <section class="service-section">
      <div class="container">
        <h2>Benefits</h2>
        <div class="service-benefits">
          <?php foreach ($args['benefits'] as $b): ?>
            <article class="service-benefitCard">
              <h3><?php echo esc_html($b['title'] ?? ''); ?></h3>
              <p><?php echo esc_html($b['description'] ?? ''); ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if (!empty($args['why_choose'])): ?>
    <section class="service-section service-section--alt">
      <div class="container">
        <h2>Why Choose Us</h2>
        <div class="service-whyCard">
          <ul class="service-whyList">
            <?php foreach ($args['why_choose'] as $li): ?>
              <li><?php echo esc_html($li); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="service-section">
    <div class="container">
      <div class="service-finalCta">
        <h2><?php echo esc_html($args['cta_title']); ?></h2>
        <p><?php echo esc_html($args['cta_text']); ?></p>
        <div class="service-finalCta__actions">
          <a class="btn btn--accent" href="<?php echo esc_url($primary_url); ?>"><?php echo esc_html($primary_label); ?></a>
          <a class="btn btn--secondary" href="<?php echo esc_url($secondary_url); ?>"><?php echo esc_html($secondary_label); ?></a>
        </div>
      </div>
    </div>
  </section>
</main>
