<?php
/**
 * Template Name: Portfolio
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$hero_img = get_the_post_thumbnail_url(get_queried_object_id(), 'full');
$hero_has_image = is_string($hero_img) && $hero_img !== '';
$hero_class = $hero_has_image ? 'page-hero--image' : 'page-hero--solid';

$pid = get_the_ID();
$config = function_exists('slm_get_editable_fields_for_template') ? slm_get_editable_fields_for_template('templates/page-portfolio.php') : [];

$meta_get = function ($key) use ($pid, $config) {
  $v = get_post_meta($pid, $key, true);
  if ($v === '') {
    if ($config && isset($config[$key]))
      return $config[$key]['default'];
  }
  return $v;
};

$title = $meta_get('slm_portfolio_title');
$sub = $meta_get('slm_portfolio_sub');

function slm_page_gallery_ids(int $post_id): array
{
  $raw = (string)get_post_meta($post_id, 'slm_portfolio_gallery_ids', true);
  if ($raw === '')
    return [];
  $parts = preg_split('/[\\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
  $ids = [];
  foreach ($parts as $p) {
    if ((int)$p > 0)
      $ids[] = (int)$p;
  }
  return array_values(array_unique($ids));
}

$gallery_ids = slm_page_gallery_ids($pid);

?>
<style>
/* Modern Carousel & Lightbox */
.portfolio-carousel {
    display: flex;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    gap: 30px;
    padding: 30px 20px;
    scrollbar-width: none; /* Firefox */
    align-items: center;
}
.portfolio-carousel::-webkit-scrollbar {
    display: none; /* Safari and Chrome */
}
.portfolio-carousel__item {
    scroll-snap-align: center;
    flex: 0 0 85%;
    max-width: 900px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    transition: transform 0.4s ease, box-shadow 0.4s ease;
    cursor: pointer;
    position: relative;
}
.portfolio-carousel__item:hover {
    transform: scale(1.03);
    box-shadow: 0 20px 50px rgba(0,0,0,0.2);
}
.portfolio-carousel__item img {
    width: 100%;
    height: 65vh;
    object-fit: cover;
    display: block;
}
.portfolio-carousel__overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.portfolio-carousel__item:hover .portfolio-carousel__overlay {
    opacity: 1;
}
.portfolio-carousel__overlay span {
    color: #fff;
    font-size: 24px;
    font-weight: 600;
    background: rgba(0,0,0,0.6);
    padding: 10px 20px;
    border-radius: 30px;
}

/* Lightbox */
.slm-lightbox {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.95);
    z-index: 100000;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}
.slm-lightbox.is-open {
    display: flex;
    animation: fadeIn 0.3s forwards;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.slm-lightbox__img {
    max-width: 90%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    transition: opacity 0.3s ease;
}
.slm-lightbox__close {
    position: absolute;
    top: 20px;
    right: 30px;
    color: #fff;
    font-size: 40px;
    cursor: pointer;
    background: none; border: none;
}
.slm-lightbox__controls {
    display: flex;
    gap: 20px;
    margin-top: 30px;
}
.slm-lightbox__btn {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    padding: 12px 24px;
    border-radius: 30px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background 0.2s, transform 0.2s;
}
.slm-lightbox__btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
}
.slm-lightbox__indicator {
    position: absolute;
    bottom: 30px;
    color: rgba(255,255,255,0.7);
    font-size: 14px;
}
</style>

<main>
  <?php if (current_user_can('edit_page', $pid)): ?>
    <a href="<?php echo get_edit_post_link($pid); ?>" class="btn" style="position:fixed; bottom:24px; right:24px; z-index:1100; padding:12px 20px; font-size:14px; box-shadow:0 4px 20px rgba(0,0,0,0.25); border-radius:999px;">&#9998; Edit Page &amp; Gallery</a>
  <?php
endif; ?>

  <section class="page-hero <?php echo esc_attr($hero_class); ?>" <?php echo $hero_has_image ? 'style="background-image:url(\'' . esc_url($hero_img) . '\');"' : ''; ?>>
    <?php if ($hero_has_image): ?>
      <div class="page-hero__overlay"></div>
    <?php
endif; ?>
    <div class="container page-hero__content">
      <h1><?php echo esc_html($title); ?></h1>
      <p class="page-hero__sub"><?php echo esc_html($sub); ?></p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section" style="padding-top: 20px; overflow: hidden;">
    <div style="max-width: 1600px; margin: 0 auto;">
      <?php if (!empty($gallery_ids)): ?>
        <p class="center sub" style="margin-bottom: 10px;">Swipe or scroll to explore our gallery</p>
        <div class="portfolio-carousel">
          <?php foreach ($gallery_ids as $index => $att_id): ?>
            <?php
    $thumb = wp_get_attachment_image_url($att_id, 'large');
    $full = wp_get_attachment_image_url($att_id, 'full');
    if (!$thumb)
      continue;
?>
            <div class="portfolio-carousel__item" data-full="<?php echo esc_url($full); ?>" data-index="<?php echo $index; ?>">
              <img src="<?php echo esc_url($thumb); ?>" alt="Portfolio Image" loading="lazy">
              <div class="portfolio-carousel__overlay">
                  <span>Enlarge Picture</span>
              </div>
            </div>
          <?php
  endforeach; ?>
        </div>
      <?php
else: ?>
         <div class="center" style="padding: 100px 20px;">
            <p>No portfolio images yet. Admin can add them by clicking "Edit Page & Gallery" at the top.</p>
         </div>
      <?php
endif; ?>
    </div>
  </section>
</main>

<div class="slm-lightbox" id="slmLightbox">
    <button class="slm-lightbox__close" id="lbClose">&times;</button>
    <img src="" alt="Enlarged" class="slm-lightbox__img" id="lbImg">
    <div class="slm-lightbox__controls">
        <button class="slm-lightbox__btn" id="lbPrev">&larr; Previous</button>
        <button class="slm-lightbox__btn" id="lbNext">Next &rarr;</button>
    </div>
    <div class="slm-lightbox__indicator" id="lbIndicator"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const items = document.querySelectorAll('.portfolio-carousel__item');
    const lightbox = document.getElementById('slmLightbox');
    const lbImg = document.getElementById('lbImg');
    const lbClose = document.getElementById('lbClose');
    const lbPrev = document.getElementById('lbPrev');
    const lbNext = document.getElementById('lbNext');
    const lbIndicator = document.getElementById('lbIndicator');
    let currentIndex = 0;
    const images = Array.from(items).map(item => item.getAttribute('data-full'));

    if (items.length === 0) return;

    function updateIndicator() {
        lbIndicator.textContent = `${currentIndex + 1} / ${images.length}`;
    }

    function openLightbox(index) {
        currentIndex = index;
        lbImg.src = images[currentIndex];
        updateIndicator();
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function showNext() {
        currentIndex = (currentIndex + 1) % images.length;
        lbImg.src = images[currentIndex];
        updateIndicator();
    }

    function showPrev() {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        lbImg.src = images[currentIndex];
        updateIndicator();
    }

    items.forEach((item, index) => {
        item.addEventListener('click', () => openLightbox(index));
    });

    lbClose.addEventListener('click', closeLightbox);
    lbNext.addEventListener('click', showNext);
    lbPrev.addEventListener('click', showPrev);

    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', function(e) {
        if (!lightbox.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') showNext();
        if (e.key === 'ArrowLeft') showPrev();
    });
});
</script>

<?php get_footer(); ?>
