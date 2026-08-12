<?php
/**
 * Template Name: Portfolio
 */
if (!defined('ABSPATH'))
  exit;

slm_page_seo(
  'Portfolio | Real Estate Photo & Video in North Florida | Showcase Listings Media',
  'See recent listing photography, cinematic video, drone, and business branding work from Showcase Listings Media across North Florida.'
);

get_header();

$cta_url      = slm_book_url();
$contact_url  = home_url('/contact/');

$pid = get_the_ID();

// ── Portfolio items: server-saved metadata wins, defaults otherwise ──
// Both branches are resolved in PHP (inc/portfolio-items.php). The browser gets a
// finished list and has no say in it — there is no client-side store to override it.
$portfolio_items_json = slm_portfolio_get_items();
if (empty($portfolio_items_json)) {
  $portfolio_items_json = slm_portfolio_default_items();
}

// Featured Project: the item flagged "featured" in the admin portal's Portfolio
// Manager wins; otherwise the first item. Label, name, and badges all come from
// the same record as the media, so they can never disagree with the picture.
$featured_item = null;
foreach ($portfolio_items_json as $candidate) {
  if (!empty($candidate['featured'])) {
    $featured_item = $candidate;
    break;
  }
}
if ($featured_item === null && !empty($portfolio_items_json)) {
  $featured_item = $portfolio_items_json[0];
}
?>

<main id="main-content">

  <?php if (current_user_can('edit_page', $pid)): ?>
    <a href="<?php echo get_edit_post_link($pid); ?>" class="btn"
      style="position:fixed;bottom:24px;right:24px;z-index:1100;padding:12px 20px;font-size:14px;box-shadow:0 4px 20px rgba(0,0,0,0.25);border-radius:999px;">&#9998; Edit Gallery</a>
  <?php endif; ?>

  <!-- ============================================================
       Section 1 — Hero (dark navy)
       ============================================================ -->
  <section class="port-hero" aria-label="Portfolio overview">
    <div class="container">
      <div class="port-hero__content js-reveal">
        <h1 class="port-hero__title">Our Work Speaks for Itself</h1>
        <p class="port-hero__sub">Real estate media and brand content built to stop the scroll, elevate listings, and drive real results across Jacksonville and North Florida.</p>
        <span class="port-hero__trust">Trusted by agents and brands across North Florida</span>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Section 2 — Featured Work (dark navy continues)
       ============================================================ -->
  <?php if ($featured_item && !empty($featured_item['image'])): ?>
  <?php
    $featured_src      = (string) $featured_item['image'];
    $featured_is_video = (($featured_item['type'] ?? 'image') === 'video');
    $featured_title    = (string) ($featured_item['title'] ?? '');
    $featured_cat      = (string) ($featured_item['category'] ?? '');
    $featured_poster   = (string) ($featured_item['thumb'] ?? '');
    $featured_metrics  = array_slice(array_filter((array) ($featured_item['metrics'] ?? []), 'is_string'), 0, 2);
  ?>
  <section class="port-featured" aria-label="Featured project">
    <div class="container">
      <p class="port-featured__eyebrow">Featured Project</p>
      <div class="port-featured__frame js-reveal">
        <?php if ($featured_is_video): ?>
          <video
            src="<?php echo esc_url($featured_src); ?>"
            <?php if ($featured_poster !== ''): ?>poster="<?php echo esc_url($featured_poster); ?>"<?php endif; ?>
            autoplay muted loop playsinline preload="metadata"></video>
        <?php else: ?>
          <img
            src="<?php echo esc_url($featured_src); ?>"
            alt="<?php echo esc_attr($featured_title !== '' ? $featured_title . ' — featured project' : 'Featured project'); ?>"
            loading="eager"
            decoding="async">
        <?php endif; ?>
        <div class="port-featured__overlay">
          <div class="port-featured__overlay-inner">
            <div class="port-featured__meta">
              <?php if ($featured_cat !== ''): ?>
                <span class="port-featured__cat"><?php echo esc_html($featured_cat); ?></span>
              <?php endif; ?>
              <?php if ($featured_title !== ''): ?>
                <h2 class="port-featured__name"><?php echo esc_html($featured_title); ?></h2>
              <?php endif; ?>
            </div>
            <?php if (!empty($featured_metrics)): ?>
              <div class="port-featured__badges">
                <?php foreach ($featured_metrics as $featured_metric): ?>
                  <span class="port-featured__badge">&#9733; <?php echo esc_html($featured_metric); ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ============================================================
       Section 3 — Filterable Masonry Grid (light)
       ============================================================ -->
  <section class="port-grid-section" aria-labelledby="port-grid-title">
    <div class="container">

      <h2 id="port-grid-title" class="port-grid-section__title js-reveal">Our Portfolio</h2>

      <!-- Filter Bar -->
      <div class="port-filters js-reveal" role="group" aria-label="Filter portfolio by category" id="portFilters">
        <button class="port-filter port-filter--active" data-filter="All" type="button">All</button>
        <?php foreach (slm_portfolio_items_categories() as $port_category): ?>
          <button class="port-filter" data-filter="<?php echo esc_attr($port_category); ?>" type="button"><?php echo esc_html($port_category); ?></button>
        <?php endforeach; ?>
      </div>

      <!-- Masonry Grid -->
      <div class="port-masonry" id="portMasonry" aria-live="polite"></div>

    </div>
  </section>

  <!-- ============================================================
       Section 5 — Final CTA (dark navy gradient)
       ============================================================ -->
  <section class="svc-final-cta js-reveal" aria-label="Book a service">
    <div class="container">
      <h2>Ready to Create Content That Gets Results?</h2>
      <p>Whether you're an agent or a business — let's build something that works for you.</p>
      <div class="svc-final-cta__btns">
        <a class="btn svc-final-cta__primary" href="<?php echo esc_url($cta_url); ?>">Book a Shoot</a>
        <a class="btn svc-final-cta__secondary" href="tel:+19042945809">Call (904) 294-5809</a>
        <a class="btn svc-final-cta__secondary" href="<?php echo esc_url($contact_url); ?>">Send a Message</a>
      </div>
    </div>
  </section>

</main>

<!-- Lightbox -->
<div class="slm-lightbox" id="slmLightbox" aria-hidden="true">
  <button class="slm-lightbox__close" id="lbClose" aria-label="Close viewer">&times;</button>
  <img src="" alt="Enlarged portfolio image" class="slm-lightbox__img" id="lbImg">
  <video class="slm-lightbox__img" id="lbVideo" controls playsinline style="display:none" aria-label="Portfolio video"></video>
  <div class="slm-lightbox__controls">
    <button class="slm-lightbox__btn" id="lbPrev" type="button">&larr;</button>
    <button class="slm-lightbox__btn" id="lbNext" type="button">&rarr;</button>
  </div>
  <div class="slm-lightbox__indicator" id="lbIndicator"></div>
</div>

<script>
(function () {
  /* ── Items resolved server-side (saved portal metadata, else site defaults) ── */
  var DEFAULT_ITEMS = <?php echo wp_json_encode($portfolio_items_json); ?>;

  var portfolioItems = JSON.parse(JSON.stringify(DEFAULT_ITEMS));
  var activeFilter   = 'All';

  /* ── Render masonry grid ── */
  var grid = document.getElementById('portMasonry');

  function renderGrid() {
    if (!grid) return;
    var filtered = activeFilter === 'All'
      ? portfolioItems
      : portfolioItems.filter(function (item) { return item.category === activeFilter; });

    /* Fade out existing */
    Array.prototype.forEach.call(grid.children, function (card) {
      card.style.opacity = '0';
      card.style.transform = 'scale(0.95)';
    });

    setTimeout(function () {
      grid.innerHTML = '';
      filtered.forEach(function (item, i) {
        var card = document.createElement('div');
        card.className = 'port-card';
        card.setAttribute('data-id', item.id);
        card.setAttribute('data-full', item.image);
        card.style.opacity = '0';
        card.style.transform = 'scale(0.95)';
        card.style.transition = 'opacity 0.3s ease ' + (i * 0.05) + 's, transform 0.3s ease ' + (i * 0.05) + 's';

        var metricsHtml = (item.metrics || []).slice(0, 2).map(function (m) {
          return '<span class="port-card__metric">&#9733; ' + escHtml(m) + '</span>';
        }).join('');

        /* Alt text names the work, the kind of work, and where it was shot.
           There is no per-item location on a portfolio record, so the region
           is the whole service area — never a guessed town. */
        var altText = (item.category ? item.title + ' — ' + item.category : item.title) +
          ' — Jacksonville & North Florida';

        var isVideo = item.type === 'video';
        var mediaHtml;
        if (isVideo) {
          mediaHtml =
            '<video class="port-card__img" src="' + escAttr(item.image) + '"' +
            (item.thumb ? ' poster="' + escAttr(item.thumb) + '"' : '') +
            ' muted loop playsinline preload="metadata" aria-label="' + escAttr(altText) + '"></video>' +
            '<span class="port-card__play" aria-hidden="true">&#9654;</span>';
        } else {
          mediaHtml =
            '<img class="port-card__img" src="' + escAttr(item.thumb || item.image) + '" alt="' + escAttr(altText) + '" loading="lazy" decoding="async">';
        }

        card.innerHTML =
          mediaHtml +
          '<div class="port-card__overlay">' +
            '<div class="port-card__overlay-body">' +
              '<span class="port-card__cat">' + escHtml(item.category) + '</span>' +
              '<p class="port-card__name">' + escHtml(item.title) + '</p>' +
              '<div class="port-card__metrics">' + metricsHtml + '</div>' +
            '</div>' +
          '</div>';

        card.addEventListener('click', function () { openLightbox(item.image); });
        grid.appendChild(card);
      });

      /* Trigger fade-in */
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          Array.prototype.forEach.call(grid.children, function (card) {
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
          });
        });
      });
    }, 180);
  }

  /* ── Filter buttons ── */
  var filterBar = document.getElementById('portFilters');
  if (filterBar) {
    filterBar.addEventListener('click', function (e) {
      var btn = e.target.closest('.port-filter');
      if (!btn) return;
      activeFilter = btn.getAttribute('data-filter') || 'All';
      Array.prototype.forEach.call(filterBar.querySelectorAll('.port-filter'), function (b) {
        b.classList.toggle('port-filter--active', b === btn);
      });
      renderGrid();
    });
  }

  /* ── Lightbox ── */
  var lb        = document.getElementById('slmLightbox');
  var lbImg     = document.getElementById('lbImg');
  var lbVideo   = document.getElementById('lbVideo');
  var lbClose   = document.getElementById('lbClose');
  var lbPrev    = document.getElementById('lbPrev');
  var lbNext    = document.getElementById('lbNext');
  var lbInd     = document.getElementById('lbIndicator');
  var lbItems   = [];
  var lbCurrent = 0;

  function buildLbList() {
    var filtered = activeFilter === 'All'
      ? portfolioItems
      : portfolioItems.filter(function (item) { return item.category === activeFilter; });
    lbItems = filtered.map(function (item) {
      return { url: item.image, type: item.type === 'video' ? 'video' : 'image' };
    });
  }

  function openLightbox(url) {
    buildLbList();
    lbCurrent = 0;
    for (var i = 0; i < lbItems.length; i++) {
      if (lbItems[i].url === url) { lbCurrent = i; break; }
    }
    showLbMedia();
    lb.classList.add('is-open');
    lb.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function showLbMedia() {
    var entry = lbItems[lbCurrent];
    if (!entry) return;
    var isVideo = entry.type === 'video';
    if (lbVideo) {
      lbVideo.pause();
      lbVideo.style.display = isVideo ? 'block' : 'none';
      if (isVideo) { lbVideo.src = entry.url; } else { lbVideo.removeAttribute('src'); }
    }
    if (lbImg) {
      lbImg.style.display = isVideo ? 'none' : 'block';
      lbImg.src = isVideo ? '' : entry.url;
    }
    if (lbInd) lbInd.textContent = (lbCurrent + 1) + ' / ' + lbItems.length;
  }

  function closeLightbox() {
    lb.classList.remove('is-open');
    lb.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    lbImg.src = '';
    if (lbVideo) {
      lbVideo.pause();
      lbVideo.removeAttribute('src');
    }
  }

  if (lb) {
    lbClose && lbClose.addEventListener('click', closeLightbox);
    lb.addEventListener('click', function (e) { if (e.target === lb) closeLightbox(); });
    lbPrev && lbPrev.addEventListener('click', function () {
      lbCurrent = (lbCurrent - 1 + lbItems.length) % lbItems.length;
      showLbMedia();
    });
    lbNext && lbNext.addEventListener('click', function () {
      lbCurrent = (lbCurrent + 1) % lbItems.length;
      showLbMedia();
    });
    document.addEventListener('keydown', function (e) {
      if (!lb.classList.contains('is-open')) return;
      if (e.key === 'Escape') closeLightbox();
      else if (e.key === 'ArrowRight') { lbCurrent = (lbCurrent + 1) % lbItems.length; showLbMedia(); }
      else if (e.key === 'ArrowLeft')  { lbCurrent = (lbCurrent - 1 + lbItems.length) % lbItems.length; showLbMedia(); }
    });
  }

  /* ── Utility ── */
  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function escAttr(str) {
    return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  /* ── Init ── */
  renderGrid();
})();
</script>

<?php get_footer(); ?>
