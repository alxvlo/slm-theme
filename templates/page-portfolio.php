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
  : add_query_arg('mode', 'signup', slm_login_url());
$contact_url  = home_url('/contact/');

$pid       = get_the_ID();
$theme_uri = get_template_directory_uri();
$uploads   = wp_upload_dir();
$uploads_base = $uploads['baseurl'];

// ── Load gallery from WP meta ──────────────────────────────────
$raw_ids = (string) get_post_meta($pid, 'slm_portfolio_gallery_ids', true);
$att_ids = [];
if ($raw_ids !== '') {
  foreach (preg_split('/[\s,]+/', $raw_ids, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $p) {
    $id = (int) $p;
    if ($id > 0) $att_ids[] = $id;
  }
}

// ── Build image data from WP attachments ──────────────────────
$wp_images = [];
foreach (array_unique($att_ids) as $att_id) {
  $full  = wp_get_attachment_image_url($att_id, 'full');
  $large = wp_get_attachment_image_url($att_id, 'large');
  if ($full) {
    $wp_images[] = [
      'full'  => $full,
      'thumb' => $large ?: $full,
      'title' => get_the_title($att_id),
    ];
  }
}

// ── Fallback: numbered uploads if no WP gallery set ───────────
if (empty($wp_images)) {
  for ($i = 1; $i <= 20; $i++) {
    $url = $uploads_base . '/2026/02/' . $i . '.png';
    $wp_images[] = [
      'full'  => $url,
      'thumb' => $url,
      'title' => 'Portfolio Image ' . $i,
    ];
  }
}

// ── Default category + metric mapping (assignable via admin panel) ─
$default_categories = [
  'Real Estate Photography', // 0
  'Real Estate Photography', // 1
  'Real Estate Photography', // 2
  'Real Estate Photography', // 3
  'Real Estate Photography', // 4
  'Real Estate Photography', // 5
  'Drone',                   // 6
  'Drone',                   // 7
  'Drone',                   // 8
  'Drone',                   // 9
  'Cinematic Video',         // 10
  'Cinematic Video',         // 11
  'Cinematic Video',         // 12
  'Cinematic Video',         // 13
  'Social Media / Reels',    // 14
  'Social Media / Reels',    // 15
  'Social Media / Reels',    // 16
  'Business Branding',       // 17
  'Business Branding',       // 18
  'Business Branding',       // 19
];

$default_metrics = [
  ['Sold in 8 days', 'MLS Featured'],
  ['Sold in 5 days', '38 Showings'],
  ['Sold Over Asking', 'MLS Featured'],
  ['Listed & Under Contract in 6 Days', '14,200 Video Views'],
  ['Sold in 11 days', 'Featured on Zillow'],
  ['Sold in 4 days', '60+ Inquiries'],
  ['Listed & Under Contract in 6 Days', 'Aerial Coverage'],
  ['Lot Sold in 14 days', 'Drone Survey'],
  ['Sold in 9 days', 'Aerial Featured'],
  ['Featured Listing', 'Aerial + Ground Coverage'],
  ['14,200 Video Views', 'Listed & Under Contract in 6 Days'],
  ['8,400 Video Views', 'Sold in 7 days'],
  ['22,000 Video Views', 'Featured on Social'],
  ['11,000 Video Views', 'Sold Over Asking'],
  ['18,000 Reel Views', '320 Saves'],
  ['24,000 Reel Views', '410 Saves'],
  ['15,500 Reel Views', 'Featured by Client'],
  ['Brand Refresh', '40% Engagement Increase'],
  ['Brand Campaign', '500+ New Followers'],
  ['Business Launch', 'Multi-Platform'],
];

$sample_titles = [
  '6000 on the River',
  'Riverside Estates',
  'Ponte Vedra Luxury Home',
  'Fleming Island Pool Home',
  'Mandarin Family Home',
  'Nocatee New Build',
  'St. Johns Aerial',
  'Waterfront Lot Survey',
  'Nassau County Aerial',
  'Amelia Island Overview',
  'River View Cinematic Tour',
  'Luxury Walkthrough',
  'New Construction Film',
  'Sunset Home Tour',
  'Agent Brand Reel',
  'Market Update Reel',
  'Behind the Scenes Reel',
  'Corporate Brand Session',
  'Business Launch Campaign',
  'Team & Culture Shoot',
];

// ── Build PHP-side default portfolio items for JS ─────────────
$portfolio_items_json = [];
foreach ($wp_images as $idx => $img) {
  $cat_idx = $idx < count($default_categories) ? $idx : ($idx % count($default_categories));
  $met_idx = $idx < count($default_metrics) ? $idx : ($idx % count($default_metrics));
  $ttl_idx = $idx < count($sample_titles) ? $idx : ($idx % count($sample_titles));

  $title = $img['title'] !== '' && $img['title'] !== 'Portfolio Image ' . ($idx + 1)
    ? $img['title']
    : $sample_titles[$ttl_idx];

  $portfolio_items_json[] = [
    'id'       => $idx + 1,
    'title'    => $title,
    'category' => $default_categories[$cat_idx],
    'image'    => $img['full'],
    'thumb'    => $img['thumb'],
    'metrics'  => $default_metrics[$met_idx],
  ];
}

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
       Admin Panel (hidden by default)
       ============================================================ -->
  <div class="port-admin" id="portAdmin" aria-hidden="true">
    <div class="container">
      <div class="port-admin__inner">
        <div class="port-admin__head">
          <h2 class="port-admin__title">Edit Portfolio Items</h2>
          <button class="port-admin__close" id="portAdminClose" type="button" aria-label="Close admin panel">&times;</button>
        </div>
        <div class="port-admin__list" id="portAdminList"></div>
        <div class="port-admin__actions">
          <button class="port-admin__save" id="portAdminSave" type="button">Save Changes</button>
          <button class="port-admin__reset" id="portAdminReset" type="button">Reset to Default</button>
        </div>
      </div>
    </div>
  </div>

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

<!-- Admin trigger (discreet footer link) -->
<div style="text-align:center;padding:12px 0 4px;">
  <button id="portAdminTrigger" type="button" style="background:none;border:none;color:#9ca3af;font-size:0.75rem;cursor:pointer;font-family:inherit;padding:4px 8px;">Admin Edit</button>
</div>

<script>
(function () {
  /* ── Default data from PHP ── */
  var DEFAULT_ITEMS = <?php echo wp_json_encode($portfolio_items_json); ?>;

  var CATEGORIES = [
    'Real Estate Photography',
    'Cinematic Video',
    'Drone',
    'Social Media / Reels',
    'Business Branding'
  ];

  var ADMIN_PASS = 'showcase2024';
  var LS_KEY     = 'slm_portfolio_items_v1';

  /* ── Load items (localStorage overrides defaults) ── */
  function loadItems() {
    try {
      var saved = localStorage.getItem(LS_KEY);
      if (saved) {
        var parsed = JSON.parse(saved);
        if (Array.isArray(parsed) && parsed.length > 0) return parsed;
      }
    } catch (e) {}
    return JSON.parse(JSON.stringify(DEFAULT_ITEMS));
  }

  function saveItems(items) {
    try { localStorage.setItem(LS_KEY, JSON.stringify(items)); } catch (e) {}
  }

  var portfolioItems = loadItems();
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

        card.innerHTML =
          '<img class="port-card__img" src="' + escAttr(item.thumb || item.image) + '" alt="' + escAttr(item.title) + '" loading="lazy" decoding="async">' +
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

  /* ── Admin Panel ── */
  var adminPanel   = document.getElementById('portAdmin');
  var adminList    = document.getElementById('portAdminList');
  var adminSave    = document.getElementById('portAdminSave');
  var adminReset   = document.getElementById('portAdminReset');
  var adminClose   = document.getElementById('portAdminClose');
  var adminTrigger = document.getElementById('portAdminTrigger');

  function renderAdminList() {
    if (!adminList) return;
    adminList.innerHTML = '';
    portfolioItems.forEach(function (item) {
      var row = document.createElement('div');
      row.className = 'port-admin__row';
      row.setAttribute('data-id', item.id);

      var catOptions = CATEGORIES.map(function (c) {
        return '<option value="' + escAttr(c) + '"' + (c === item.category ? ' selected' : '') + '>' + escHtml(c) + '</option>';
      }).join('');

      row.innerHTML =
        '<img class="port-admin__thumb" src="' + escAttr(item.thumb || item.image) + '" alt="">' +
        '<div class="port-admin__fields">' +
          '<input class="port-admin__input" type="text" placeholder="Title" value="' + escAttr(item.title) + '" data-field="title">' +
          '<select class="port-admin__select" data-field="category">' + catOptions + '</select>' +
          '<input class="port-admin__input" type="text" placeholder="Metrics (comma separated)" value="' + escAttr((item.metrics || []).join(', ')) + '" data-field="metrics">' +
        '</div>';

      adminList.appendChild(row);
    });
  }

  if (adminTrigger) {
    adminTrigger.addEventListener('click', function () {
      var pass = window.prompt('Enter admin password:');
      if (pass === null) return;
      if (pass === ADMIN_PASS) {
        renderAdminList();
        adminPanel.classList.add('is-open');
        adminPanel.setAttribute('aria-hidden', 'false');
      } else {
        alert('Incorrect password.');
      }
    });
  }

  adminClose && adminClose.addEventListener('click', function () {
    adminPanel.classList.remove('is-open');
    adminPanel.setAttribute('aria-hidden', 'true');
  });

  adminSave && adminSave.addEventListener('click', function () {
    if (!adminList) return;
    var rows = adminList.querySelectorAll('.port-admin__row');
    rows.forEach(function (row) {
      var id = parseInt(row.getAttribute('data-id'), 10);
      var item = portfolioItems.find(function (p) { return p.id === id; });
      if (!item) return;
      var titleEl    = row.querySelector('[data-field="title"]');
      var catEl      = row.querySelector('[data-field="category"]');
      var metricsEl  = row.querySelector('[data-field="metrics"]');
      if (titleEl)   item.title    = titleEl.value.trim();
      if (catEl)     item.category = catEl.value;
      if (metricsEl) item.metrics  = metricsEl.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    });
    saveItems(portfolioItems);
    renderGrid();
    adminPanel.classList.remove('is-open');
    adminPanel.setAttribute('aria-hidden', 'true');
    alert('Changes saved!');
  });

  adminReset && adminReset.addEventListener('click', function () {
    if (!confirm('Reset all portfolio data to defaults?')) return;
    localStorage.removeItem(LS_KEY);
    portfolioItems = JSON.parse(JSON.stringify(DEFAULT_ITEMS));
    renderAdminList();
    renderGrid();
    alert('Reset to defaults.');
  });

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
