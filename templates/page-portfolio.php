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
$photo_urls = $dedupe_urls($selected_photo_urls);

$selected_video_urls = [];
foreach ($video_ids as $att_id) {
  $video_url = wp_get_attachment_url($att_id);
  if (is_string($video_url) && $video_url !== '') {
    $selected_video_urls[] = $video_url;
  }
}
$video_urls = $dedupe_urls($selected_video_urls);

// Build mixed media array: interleave videos among photos
$media_items = []; // Each item: ['type' => 'image'|'video', 'url' => '...']
$video_queue = $video_urls;
$video_interval = count($photo_urls) > 0 && count($video_urls) > 0
  ? max(2, (int) floor(count($photo_urls) / (count($video_urls) + 1)))
  : 0;
$video_index = 0;
foreach ($photo_urls as $i => $url) {
  $media_items[] = ['type' => 'image', 'url' => $url];
  if ($video_interval > 0 && ($i + 1) % $video_interval === 0 && $video_index < count($video_queue)) {
    $media_items[] = ['type' => 'video', 'url' => $video_queue[$video_index]];
    $video_index++;
  }
}
// Append any remaining videos at the end
while ($video_index < count($video_queue)) {
  $media_items[] = ['type' => 'video', 'url' => $video_queue[$video_index]];
  $video_index++;
}
?>

<main>
  <?php if (current_user_can('edit_page', $pid)): ?>
    <a href="<?php echo get_edit_post_link($pid); ?>" class="btn"
      style="position:fixed; bottom:24px; right:24px; z-index:1100; padding:12px 20px; font-size:14px; box-shadow:0 4px 20px rgba(0,0,0,0.25); border-radius:999px;">&#9998;
      Edit Page &amp; Gallery</a>
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
      <?php if (!empty($media_items)): ?>

        <div class="pMasonry">
          <?php foreach ($media_items as $index => $item): ?>
            <?php if ($item['type'] === 'image'): ?>
              <div class="pMasonry__item" data-type="image" data-full="<?php echo esc_url($item['url']); ?>"
                data-index="<?php echo esc_attr((string) $index); ?>">
                <img class="pMasonry__img" src="<?php echo esc_url($item['url']); ?>"
                  alt="Portfolio photo <?php echo esc_attr((string) ($index + 1)); ?>" loading="lazy" decoding="async">
              </div>
            <?php else: ?>
              <div class="pMasonry__item pMasonry__item--video" data-type="video"
                data-full="<?php echo esc_url($item['url']); ?>" data-index="<?php echo esc_attr((string) $index); ?>">
                <video class="pMasonry__video" autoplay loop muted playsinline preload="metadata"
                  src="<?php echo esc_url($item['url']); ?>"></video>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="center" style="padding:60px 20px;">
          <p>No portfolio media available yet. Add them using "Edit Page &amp; Gallery".</p>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<div class="slm-lightbox" id="slmLightbox" aria-hidden="true">
  <button class="slm-lightbox__close" id="lbClose" aria-label="Close media viewer">&times;</button>
  <img src="" alt="Enlarged portfolio image" class="slm-lightbox__img" id="lbImg">
  <video class="slm-lightbox__video" id="lbVideo" controls playsinline preload="none" style="display:none;"></video>
  <div class="slm-lightbox__controls">
    <button class="slm-lightbox__btn" id="lbPrev" type="button">&larr; Previous</button>
    <button class="slm-lightbox__btn" id="lbNext" type="button">Next &rarr;</button>
  </div>
  <div class="slm-lightbox__indicator" id="lbIndicator"></div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var items = document.querySelectorAll('.pMasonry__item');
    var lightbox = document.getElementById('slmLightbox');
    var lbImg = document.getElementById('lbImg');
    var lbVideo = document.getElementById('lbVideo');
    var lbClose = document.getElementById('lbClose');
    var lbPrev = document.getElementById('lbPrev');
    var lbNext = document.getElementById('lbNext');
    var lbIndicator = document.getElementById('lbIndicator');
    var currentIndex = 0;

    var mediaList = Array.prototype.map.call(items, function (item) {
      return {
        type: item.getAttribute('data-type') || 'image',
        url: item.getAttribute('data-full')
      };
    });

    if (!mediaList.length || !lightbox || !lbImg || !lbVideo || !lbClose || !lbPrev || !lbNext || !lbIndicator) {
      return;
    }

    function updateIndicator() {
      var label = mediaList[currentIndex].type === 'video' ? 'Video' : 'Photo';
      lbIndicator.textContent = label + ' ' + (currentIndex + 1) + ' / ' + mediaList.length;
    }

    function showMedia(index) {
      var item = mediaList[index];
      if (item.type === 'video') {
        lbImg.style.display = 'none';
        lbVideo.style.display = 'block';
        lbVideo.src = item.url;
        lbVideo.currentTime = 0;
        lbVideo.play();
      } else {
        lbVideo.pause();
        lbVideo.removeAttribute('src');
        lbVideo.style.display = 'none';
        lbImg.style.display = 'block';
        lbImg.src = item.url;
      }
      updateIndicator();
    }

    function openLightbox(index) {
      currentIndex = index;
      showMedia(currentIndex);
      lightbox.classList.add('is-open');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      lbVideo.pause();
      lbVideo.removeAttribute('src');
      lightbox.classList.remove('is-open');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    function showNext() {
      lbVideo.pause();
      currentIndex = (currentIndex + 1) % mediaList.length;
      showMedia(currentIndex);
    }

    function showPrev() {
      lbVideo.pause();
      currentIndex = (currentIndex - 1 + mediaList.length) % mediaList.length;
      showMedia(currentIndex);
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