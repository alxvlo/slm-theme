<?php
if (!defined('ABSPATH'))
  exit;

$args = wp_parse_args($args ?? [], [
  'title' => '',
  'subtitle' => '',
  'hero_image' => '',
  'description_image' => '',
  'gallery' => [],       // array of media URLs
  'description' => [],   // array of paragraphs
  'benefits' => [],      // array of ['title' => '', 'description' => '']
  'why_choose' => [],    // array of strings
  'tour_embed' => '',    // URL for 3D tour iframe embed
  'tour_title' => 'Experience the 3D Tour',
  'book_url' => add_query_arg('mode', 'signup', slm_login_url()),
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Ready to Break the Standard?',
  'cta_text' => 'Build stronger listing presentations, elevate your brand perception, and create marketing momentum with a partner invested in your long-term success.',
]);

$is_logged_in = is_user_logged_in();
$place_order_url = add_query_arg('view', 'place-order', slm_portal_url());
$primary_url = $is_logged_in ? $place_order_url : (string) $args['book_url'];
$primary_label = $is_logged_in ? 'Place Order' : (string) $args['book_label'];
$admin_gallery_url = '';
if (current_user_can('manage_options')) {
  $current_page_id = get_queried_object_id();
  if (
    $current_page_id > 0
    && function_exists('slm_portfolio_is_gallery_supported_page')
    && slm_portfolio_is_gallery_supported_page((int) $current_page_id)
  ) {
    $admin_gallery_url = add_query_arg(
      ['post' => (int) $current_page_id, 'action' => 'edit'],
      admin_url('post.php')
    ) . '#slm_portfolio_gallery';
  }
}
$has_hero_media = !empty($args['hero_image']);
$has_description_media = !empty($args['description_image']);
$gallery_items = array_values(array_filter((array) $args['gallery'], static function ($item): bool {
  return is_string($item) && trim($item) !== '';
}));

$render_media = static function (string $src): void {
  if ($src === '')
    return;

  $path = (string) parse_url($src, PHP_URL_PATH);
  $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
  if (in_array($ext, ['mp4', 'webm', 'mov'], true)) {
    ?>
    <video src="<?php echo esc_url($src); ?>" autoplay loop muted playsinline preload="metadata"></video>
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
      <div class="service-hero__grid<?php echo !$has_hero_media ? ' service-hero__grid--single' : ''; ?>">
        <div class="service-hero__copy">
          <h1><?php echo esc_html($args['title']); ?></h1>
          <p><?php echo esc_html($args['subtitle']); ?></p>
          <p class="service-hero__actions">
            <a class="btn btn--accent"
              href="<?php echo esc_url($primary_url); ?>"><?php echo esc_html($primary_label); ?></a>
            <?php if ($admin_gallery_url !== ''): ?>
              <a class="btn btn--secondary"
                href="<?php echo esc_url($admin_gallery_url); ?>">Edit This Service Gallery</a>
            <?php endif; ?>
          </p>
        </div>

        <?php if ($has_hero_media): ?>
          <div class="service-mediaCard">
            <?php $render_media((string) $args['hero_image']); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if (!empty($args['tour_embed'])): ?>
    <section class="service-tourShowcase">
      <div class="container">
        <div class="service-tourShowcase__header">
          <span class="service-tourShowcase__badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
            Interactive 3D Tour
          </span>
          <h2><?php echo esc_html($args['tour_title']); ?></h2>
          <p>Walk through the property from any angle. Click, drag, and explore every room as if you were there in person.</p>
        </div>
        <div class="service-tourShowcase__frame">
          <iframe
            src="<?php echo esc_url($args['tour_embed']); ?>"
            title="<?php echo esc_attr($args['tour_title']); ?>"
            width="100%"
            height="100%"
            frameborder="0"
            allowfullscreen="allowfullscreen"
            loading="lazy"
          ></iframe>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="service-section service-section--alt">
    <div class="container">
      <div class="service-overview service-overview--single">
        <div>
          <h2>Overview</h2>
          <?php foreach ((array) $args['description'] as $p): ?>
            <p><?php echo esc_html($p); ?></p>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($gallery_items)): ?>
    <section class="service-section">
      <div class="container">
        <h2>Featured Work</h2>
        <div class="service-gallery">
          <?php foreach ($gallery_items as $media): ?>
            <div class="service-mediaCard">
              <?php $render_media((string) $media); ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

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
          <a class="btn btn--accent"
            href="<?php echo esc_url($primary_url); ?>"><?php echo esc_html($primary_label); ?></a>
        </div>
      </div>
    </div>
  </section>
</main>