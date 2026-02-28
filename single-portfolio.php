<?php
if (!defined('ABSPATH'))
  exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
$archive_url = get_post_type_archive_link('portfolio');
if (!is_string($archive_url) || $archive_url === '') {
  $archive_url = home_url('/portfolio/');
}

function slm_portfolio_gallery_ids_for_post(int $post_id): array
{
  $raw = (string) get_post_meta($post_id, 'slm_portfolio_gallery_ids', true);
  if ($raw === '')
    return [];
  $parts = preg_split('/[\\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
  $ids = [];
  foreach ($parts as $p) {
    $id = (int) $p;
    if ($id > 0)
      $ids[] = $id;
  }
  return array_values(array_unique($ids));
}
?>

<main>
  <?php if (have_posts()):
    while (have_posts()):
      the_post(); ?>
      <?php
      $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
      if (!$img)
        $img = $placeholder;

      $subtitle = trim((string) get_the_excerpt());
      $gallery_ids = slm_portfolio_gallery_ids_for_post((int) get_the_ID());
      $total_images = count($gallery_ids);
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
        <div class="container">
          <?php if ($archive_url): ?>
            <a class="pBreadcrumb" href="<?php echo esc_url($archive_url); ?>">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
              Back to Portfolio
            </a>
          <?php endif; ?>
        </div>

        <?php if (!empty($gallery_ids)): ?>
          <div class="pMasonry" data-masonry-gallery>
            <?php foreach ($gallery_ids as $i => $att_id):
              $full_url = wp_get_attachment_image_url($att_id, 'full');
              if (!$full_url)
                continue;
              ?>
              <figure class="pMasonry__item" data-masonry-index="<?php echo esc_attr((string) $i); ?>">
                <?php
                echo wp_get_attachment_image($att_id, 'large', false, [
                  'loading' => $i < 6 ? 'eager' : 'lazy',
                  'decoding' => 'async',
                  'class' => 'pMasonry__img',
                ]);
                ?>
                <div class="pMasonry__overlay">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <line x1="11" y1="8" x2="11" y2="14"></line>
                    <line x1="8" y1="11" x2="14" y2="11"></line>
                  </svg>
                </div>
                <input type="hidden" class="pMasonry__full" value="<?php echo esc_url($full_url); ?>">
              </figure>
            <?php endforeach; ?>
          </div>

          <!-- Lightbox -->
          <div class="pLightbox" data-masonry-lightbox aria-hidden="true">
            <button class="pLightbox__close" type="button" data-lb-close aria-label="Close lightbox">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
            <button class="pLightbox__nav pLightbox__nav--prev" type="button" data-lb-prev aria-label="Previous image">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
            </button>
            <img class="pLightbox__img" src="" alt="" data-lb-img>
            <button class="pLightbox__nav pLightbox__nav--next" type="button" data-lb-next aria-label="Next image">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 6 15 12 9 18"></polyline>
              </svg>
            </button>
            <span class="pLightbox__counter" data-lb-counter></span>
          </div>
        <?php endif; ?>

        <div class="container post-wrap">
          <article class="post-content">
            <?php the_content(); ?>
          </article>
        </div>
      </section>
    <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>