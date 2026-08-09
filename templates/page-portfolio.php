<?php
/**
 * Template Name: Portfolio
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$is_logged_in = is_user_logged_in();
$cta_url      = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : slm_booking_cta_url();
$contact_url  = home_url('/contact/');

$pid = get_the_ID();

// ── Portfolio items: server-saved metadata wins, defaults otherwise ──
// Both branches are resolved in PHP (inc/portfolio-items.php). The browser gets a
// finished list and has no say in it — there is no client-side store to override it.
$portfolio_items_json = slm_portfolio_get_items();
if (empty($portfolio_items_json)) {
  $portfolio_items_json = slm_portfolio_default_items();
}

// Hero stays sourced from the first GALLERY image, not the first saved item, so
// reordering items in the portal never silently swaps the featured photo.
$wp_images    = slm_portfolio_gallery_images();
$featured_img = $wp_images[0]['full'] ?? '';
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
  <?php if ($featured_img): ?>
  <section class="port-featured" aria-label="Featured project">
    <div class="container">
      <p class="port-featured__eyebrow">Featured Project</p>
      <div class="port-featured__frame js-reveal">
        <img
          src="<?php echo esc_url($featured_img); ?>"
          alt="6000 on the River — featured real estate media project"
          loading="eager"
          decoding="async">
        <div class="port-featured__overlay">
          <div class="port-featured__overlay-inner">
            <div class="port-featured__meta">
              <span class="port-featured__cat">Cinematic Video</span>
              <h2 class="port-featured__name">6000 on the River</h2>
            </div>
            <div class="port-featured__badges">
              <span class="port-featured__badge">&#9733; Listed &amp; Under Contract in 6 Days</span>
              <span class="port-featured__badge">&#9733; 14,200 Video Views</span>
            </div>
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
        <button class="port-filter" data-filter="Real Estate Photography" type="button">Real Estate Photography</button>
        <button class="port-filter" data-filter="Cinematic Video" type="button">Cinematic Video</button>
        <button class="port-filter" data-filter="Drone" type="button">Drone</button>
        <button class="port-filter" data-filter="Social Media / Reels" type="button">Social Media / Reels</button>
        <button class="port-filter" data-filter="Business Branding" type="button">Business Branding</button>
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

        var altText = item.category ? item.category + ' — ' + item.title : item.title;

        card.innerHTML =
          '<img class="port-card__img" src="' + escAttr(item.thumb || item.image) + '" alt="' + escAttr(altText) + '" loading="lazy" decoding="async">' +
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
    lbItems = filtered.map(function (item) { return item.image; });
  }

  function openLightbox(url) {
    buildLbList();
    lbCurrent = lbItems.indexOf(url);
    if (lbCurrent < 0) lbCurrent = 0;
    showLbMedia();
    lb.classList.add('is-open');
    lb.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function showLbMedia() {
    if (!lbImg || !lbItems[lbCurrent]) return;
    lbImg.src = lbItems[lbCurrent];
    if (lbInd) lbInd.textContent = 'Photo ' + (lbCurrent + 1) + ' / ' + lbItems.length;
  }

  function closeLightbox() {
    lb.classList.remove('is-open');
    lb.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    lbImg.src = '';
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
