<?php
if (!defined('ABSPATH')) exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
$archive_url = get_post_type_archive_link('portfolio');
if (!is_string($archive_url) || $archive_url === '') {
  $archive_url = home_url('/portfolio/');
}

function slm_portfolio_gallery_ids_for_post(int $post_id): array {
  $raw = (string) get_post_meta($post_id, 'slm_portfolio_gallery_ids', true);
  if ($raw === '') return [];
  $parts = preg_split('/[\\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
  $ids = [];
  foreach ($parts as $p) {
    $id = (int) $p;
    if ($id > 0) $ids[] = $id;
  }
  return array_values(array_unique($ids));
}
?>

<main>
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <?php
      $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
      if (!$img) $img = $placeholder;

      $subtitle = trim((string) get_the_excerpt());
      $gallery_ids = slm_portfolio_gallery_ids_for_post((int) get_the_ID());
    ?>

    <section class="page-hero page-hero--image" style="background-image:url('<?php echo esc_url($img); ?>');">
      <div class="page-hero__overlay"></div>
      <div class="container page-hero__content">
        <p class="kicker" style="color:#fff; opacity:.9; margin:0 0 8px;">Portfolio</p>
        <h1><?php the_title(); ?></h1>
        <?php if ($subtitle !== ''): ?>
          <p class="page-hero__sub"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
      </div>
      <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
        <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
      </svg>
    </section>

    <section class="page-section">
      <div class="container post-wrap">
        <?php if ($archive_url): ?>
          <p style="margin:0 0 18px;">
            <a href="<?php echo esc_url($archive_url); ?>" style="text-decoration:none;">&larr; Back to Portfolio</a>
          </p>
        <?php endif; ?>

        <?php if (!empty($gallery_ids)): ?>
          <section class="pSlider" data-portfolio-slider aria-label="Portfolio gallery" style="margin:0 0 22px;">
            <div class="pSlider__stage">
              <button class="pSlider__nav pSlider__nav--prev" type="button" data-ps-prev aria-label="Previous image">
                <span aria-hidden="true">&#x2039;</span>
              </button>

              <div class="pSlider__slides">
                <?php foreach ($gallery_ids as $i => $att_id): ?>
                  <figure class="pSlider__slide <?php echo $i === 0 ? 'is-active' : ''; ?>" aria-hidden="<?php echo $i === 0 ? 'false' : 'true'; ?>">
                    <?php
                      $img_html = wp_get_attachment_image($att_id, 'large', false, [
                        'loading' => $i === 0 ? 'eager' : 'lazy',
                        'decoding' => 'async',
                      ]);
                      echo $img_html ? $img_html : '';
                    ?>
                  </figure>
                <?php endforeach; ?>
              </div>

              <button class="pSlider__nav pSlider__nav--next" type="button" data-ps-next aria-label="Next image">
                <span aria-hidden="true">&#x203A;</span>
              </button>
            </div>

            <div class="pSlider__thumbs" data-ps-thumbs>
              <?php foreach ($gallery_ids as $i => $att_id): ?>
                <button
                  type="button"
                  class="pThumb <?php echo $i === 0 ? 'is-active' : ''; ?>"
                  data-ps-thumb
                  data-ps-index="<?php echo esc_attr((string) $i); ?>"
                  aria-label="<?php echo esc_attr('Show image ' . ($i + 1)); ?>"
                  aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                >
                  <?php
                    $thumb_html = wp_get_attachment_image($att_id, 'thumbnail', false, [
                      'loading' => 'lazy',
                      'decoding' => 'async',
                    ]);
                    echo $thumb_html ? $thumb_html : '';
                  ?>
                </button>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <article class="post-content">
          <?php the_content(); ?>
        </article>
      </div>
    </section>
  <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
