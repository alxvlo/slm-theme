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
$theme_uri = get_template_directory_uri();
$config = function_exists('slm_get_editable_fields_for_template') ? slm_get_editable_fields_for_template('templates/page-portfolio.php') : [];

$meta_get = function ($key) use ($pid, $config) {
  $v = get_post_meta($pid, $key, true);
  if ($v === '' && $config && isset($config[$key])) {
    return $config[$key]['default'];
  }
  return $v;
};

$page_media_ids = static function (int $post_id, string $meta_key): array {
  $raw = (string) get_post_meta($post_id, $meta_key, true);
  if ($raw === '') {
    return [];
  }
  $parts = preg_split('/[\\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
  $ids = [];
  foreach ($parts as $p) {
    $id = (int) $p;
    if ($id > 0) {
      $ids[] = $id;
    }
  }
  return array_values(array_unique($ids));
};

$dedupe_urls = static function (array $urls): array {
  $result = [];
  $seen = [];
  foreach ($urls as $url) {
    $url = trim((string) $url);
    if ($url === '') {
      continue;
    }
    $path = (string) parse_url($url, PHP_URL_PATH);
    $key = strtolower($path !== '' ? $path : $url);
    if (isset($seen[$key])) {
      continue;
    }
    $seen[$key] = true;
    $result[] = $url;
  }
  return $result;
};

$title = $meta_get('slm_portfolio_title');
$sub = $meta_get('slm_portfolio_sub');

$gallery_ids = $page_media_ids($pid, 'slm_portfolio_gallery_ids');
$video_ids = $page_media_ids($pid, 'slm_portfolio_video_ids');

$selected_photo_urls = [];
foreach ($gallery_ids as $att_id) {
  $full = wp_get_attachment_image_url($att_id, 'full');
  if (is_string($full) && $full !== '') {
    $selected_photo_urls[] = $full;
  }
}

$seed_photo_paths = [
  '/assets/media/photos/01-0-front-exterior.jpg',
  '/assets/media/photos/08-1-front-exterior.jpg',
  '/assets/media/photos/13-2-front-exterior-3.jpg',
  '/assets/media/photos/16-5-front-exterior-4.jpg',
  '/assets/media/drone-photos/05-3-aerial-overview.jpg',
  '/assets/media/drone-photos/08-52-aerial-front-exterior-1.jpg',
  '/assets/media/photos/03-17-dining-room-1-of-4.jpg',
  '/assets/media/photos/09-20-dining-room-4-of-4.jpg',
];
$seed_photo_urls = array_map(static function ($path) use ($theme_uri) {
  return $theme_uri . $path;
}, $seed_photo_paths);
$photo_urls = $dedupe_urls(array_merge($selected_photo_urls, $seed_photo_urls));

$selected_video_urls = [];
foreach ($video_ids as $att_id) {
  $video_url = wp_get_attachment_url($att_id);
  if (is_string($video_url) && $video_url !== '') {
    $selected_video_urls[] = $video_url;
  }
}

$seed_video_paths = [
  '/assets/media/horizontal-videos/02-inside-this-stunning-north-florida-home-real-tours-north-florida.mp4',
  '/assets/media/horizontal-videos/03-thank-you-aubrey-wessolowski.mp4',
  '/assets/media/horizontal-videos/04-drone-video.mp4',
];
$seed_video_urls = array_map(static function ($path) use ($theme_uri) {
  return $theme_uri . $path;
}, $seed_video_paths);
$video_urls = $dedupe_urls(array_merge($selected_video_urls, $seed_video_urls));
?>

<main>
  <?php if (current_user_can('edit_page', $pid)): ?>
    <a href="<?php echo get_edit_post_link($pid); ?>" class="btn" style="position:fixed; bottom:24px; right:24px; z-index:1100; padding:12px 20px; font-size:14px; box-shadow:0 4px 20px rgba(0,0,0,0.25); border-radius:999px;">&#9998; Edit Page &amp; Gallery</a>
  <?php endif; ?>

  <section class="page-hero <?php echo esc_attr($hero_class); ?>" <?php echo $hero_has_image ? 'style="background-image:url(\'' . esc_url($hero_img) . '\');"' : ''; ?>>
    <?php if ($hero_has_image): ?>
      <div class="page-hero__overlay"></div>
    <?php endif; ?>
    <div class="container page-hero__content">
      <h1><?php echo esc_html($title); ?></h1>
      <p class="page-hero__sub"><?php echo esc_html($sub); ?></p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section page-section--compact">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Photo Portfolio</h2>
      <?php if (!empty($photo_urls)): ?>
        <p class="center sub" style="margin-bottom:10px;">Swipe or scroll to explore our full photo portfolio.</p>
        <div class="portfolio-carousel">
          <?php foreach ($photo_urls as $index => $url): ?>
            <div class="portfolio-carousel__item" data-full="<?php echo esc_url($url); ?>" data-index="<?php echo esc_attr((string) $index); ?>">
              <img src="<?php echo esc_url($url); ?>" alt="Portfolio photo <?php echo esc_attr((string) ($index + 1)); ?>" loading="lazy" decoding="async">
              <div class="portfolio-carousel__overlay">
                <span>Enlarge Picture</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="center" style="padding:60px 20px;">
          <p>No portfolio images available yet. Add them using "Edit Page &amp; Gallery".</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php if (!empty($video_urls)): ?>
    <section class="page-section page-section--secondary">
      <div class="container">
        <h2 class="center" style="margin-top:0;">Video Portfolio</h2>
        <p class="center sub" style="margin-bottom:24px; max-width:860px;">A selection of listing and brand videos showcasing cinematic walkthroughs, social-ready edits, and aerial coverage.</p>
        <div class="portfolio-videoGrid">
          <?php foreach ($video_urls as $video_url): ?>
            <article class="portfolio-videoCard">
              <video controls playsinline preload="metadata" src="<?php echo esc_url($video_url); ?>"></video>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>
</main>

<div class="slm-lightbox" id="slmLightbox" aria-hidden="true">
  <button class="slm-lightbox__close" id="lbClose" aria-label="Close image viewer">&times;</button>
  <img src="" alt="Enlarged portfolio image" class="slm-lightbox__img" id="lbImg">
  <div class="slm-lightbox__controls">
    <button class="slm-lightbox__btn" id="lbPrev" type="button">&larr; Previous</button>
    <button class="slm-lightbox__btn" id="lbNext" type="button">Next &rarr;</button>
  </div>
  <div class="slm-lightbox__indicator" id="lbIndicator"></div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var items = document.querySelectorAll('.portfolio-carousel__item');
    var lightbox = document.getElementById('slmLightbox');
    var lbImg = document.getElementById('lbImg');
    var lbClose = document.getElementById('lbClose');
    var lbPrev = document.getElementById('lbPrev');
    var lbNext = document.getElementById('lbNext');
    var lbIndicator = document.getElementById('lbIndicator');
    var currentIndex = 0;
    var images = Array.prototype.map.call(items, function (item) {
      return item.getAttribute('data-full');
    });

    if (!images.length || !lightbox || !lbImg || !lbClose || !lbPrev || !lbNext || !lbIndicator) {
      return;
    }

    function updateIndicator() {
      lbIndicator.textContent = (currentIndex + 1) + ' / ' + images.length;
    }

    function openLightbox(index) {
      currentIndex = index;
      lbImg.src = images[currentIndex];
      updateIndicator();
      lightbox.classList.add('is-open');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      lightbox.classList.remove('is-open');
      lightbox.setAttribute('aria-hidden', 'true');
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

    items.forEach(function (item, index) {
      item.addEventListener('click', function () {
        openLightbox(index);
      });
    });

    lbClose.addEventListener('click', closeLightbox);
    lbNext.addEventListener('click', showNext);
    lbPrev.addEventListener('click', showPrev);

    lightbox.addEventListener('click', function (event) {
      if (event.target === lightbox) {
        closeLightbox();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (!lightbox.classList.contains('is-open')) {
        return;
      }
      if (event.key === 'Escape') {
        closeLightbox();
      } else if (event.key === 'ArrowRight') {
        showNext();
      } else if (event.key === 'ArrowLeft') {
        showPrev();
      }
    });
  });
</script>

<?php get_footer(); ?>
