<?php
if (!defined('ABSPATH'))
  exit;

require_once __DIR__ . '/inc/aryeo.php';
require_once __DIR__ . '/inc/subscriptions.php';
require_once __DIR__ . '/inc/testimonials.php';
require_once __DIR__ . '/inc/portfolio-gallery.php';
require_once __DIR__ . '/inc/footer-customizer.php';
require_once __DIR__ . '/inc/page-editable-text.php';

/**
 * Hint compatible cache layers to bypass full-page cache for signed-in users.
 */
add_action('init', function () {
  if (is_user_logged_in() && !defined('DONOTCACHEPAGE')) {
    define('DONOTCACHEPAGE', true);
  }
}, 1);

function slm_theme_ver()
{
  $t = wp_get_theme();
  return $t->get('Version') ?: '0.1.0';
}

/**
 * True when the given user can access the admin dashboard area.
 */
function slm_user_is_admin($user = null): bool
{
  if (!$user instanceof WP_User) {
    $user = wp_get_current_user();
  }
  return ($user instanceof WP_User) && user_can($user, 'manage_options');
}

/**
 * Resolve a published page URL by assigned page template.
 */
function slm_page_url_by_template(string $template_file, string $fallback_path): string
{
  static $cache = [];

  $cache_key = $template_file . '|' . $fallback_path;
  if (isset($cache[$cache_key])) {
    return $cache[$cache_key];
  }

  $candidates = [$template_file];
  if (strpos($template_file, '/') === false) {
    $candidates[] = 'templates/' . ltrim($template_file, '/');
  }

  foreach ($candidates as $candidate) {
    $page_ids = get_posts([
      'post_type' => 'page',
      'post_status' => 'publish',
      'posts_per_page' => 1,
      'fields' => 'ids',
      'no_found_rows' => true,
      'meta_key' => '_wp_page_template',
      'meta_value' => $candidate,
    ]);

    if (!empty($page_ids)) {
      $permalink = get_permalink((int) $page_ids[0]);
      if (is_string($permalink) && $permalink !== '') {
        $cache[$cache_key] = $permalink;
        return $permalink;
      }
    }
  }

  $cache[$cache_key] = home_url($fallback_path);
  return $cache[$cache_key];
}

function slm_admin_portal_url(): string
{
  return slm_page_url_by_template('admin-portal.php', '/admin-portal/');
}

function slm_portal_url(): string
{
  return slm_page_url_by_template('page-portal.php', '/portal/');
}

function slm_login_url(): string
{
  return slm_page_url_by_template('page-login.php', '/login/');
}

/**
 * Role-aware dashboard target.
 */
function slm_dashboard_url($user = null): string
{
  return slm_user_is_admin($user) ? slm_admin_portal_url() : slm_portal_url();
}

/**
 * File-based cache busting. Pass a theme-relative path like: /assets/css/base.css
 */
function slm_asset_ver(string $rel_path): string
{
  $abs = get_template_directory() . $rel_path;
  if (is_file($abs))
    return (string) filemtime($abs);
  return slm_theme_ver();
}

/**
 * Canonical fallback legal copy used only when the legal pages are empty.
 */
function slm_legal_default_content(): array
{
  $last_updated = '<p><em>Last updated: ' . esc_html(date('F j, Y')) . '</em></p>';

  $privacy_content = implode("\n", [
    $last_updated,
    '<h2>Overview</h2>',
    '<p>This Privacy Policy explains what information we collect, how we use it, and how we protect it when you use this website or book services with us.</p>',
    '<h2>Information We Collect</h2>',
    '<ul>',
    '<li>Contact details you submit (name, email, phone).</li>',
    '<li>Account information (if you register).</li>',
    '<li>Order details entered through our portal.</li>',
    '<li>Basic technical usage data (for example browser/device information) for security and functionality.</li>',
    '</ul>',
    '<h2>How We Use Information</h2>',
    '<ul>',
    '<li>To respond to inquiries and provide customer support.</li>',
    '<li>To schedule, deliver, and manage services.</li>',
    '<li>To communicate order updates and service notices.</li>',
    '<li>To maintain website security and improve user experience.</li>',
    '</ul>',
    '<h2>Sharing</h2>',
    '<p>We may share information with trusted providers used to operate our website and service delivery (for example hosting, email, and scheduling/order integrations). We do not sell personal information.</p>',
    '<h2>Cookies</h2>',
    '<p>We use essential cookies for login sessions, security, and core website functionality. If optional analytics cookies are enabled, they are used only to improve site performance and user experience.</p>',
    '<h2>Data Retention</h2>',
    '<p>We keep data only as long as needed for service delivery, recordkeeping, and legal requirements.</p>',
    '<h2>Contact</h2>',
    '<p>Questions about this policy can be sent to the email listed on our Contact page.</p>',
  ]);

  $terms_content = implode("\n", [
    $last_updated,
    '<h2>Booking and Acceptance</h2>',
    '<p>By scheduling a service with us, you agree to these Terms and Conditions. The person scheduling the appointment is responsible for communicating these terms to homeowners, occupants, and any third parties involved with the property.</p>',
    '<h2>Scheduling, Access, and Property Readiness</h2>',
    '<p>Clients are responsible for ensuring safe property access at the scheduled time and confirming the property is photo-ready before arrival.</p>',
    '<h2>Rescheduling, Cancellations, and No-Shows</h2>',
    '<p>Rescheduling and cancellation requests should be submitted as early as possible. Late changes, denied access, or no-shows may result in a fee.</p>',
    '<h2>Weather and Drone/Flight Restrictions</h2>',
    '<p>Outdoor and drone services depend on weather, safety, and FAA/airspace restrictions. If conditions are unsafe or legally restricted, we may reschedule or adjust the scope of service.</p>',
    '<h2>Deliverables and Turnaround</h2>',
    '<p>Typical turnaround windows are shared at booking. Delivery timing may vary based on scope, revisions, weather delays, and peak volume.</p>',
    '<h2>Add-Ons and Package Compatibility</h2>',
    '<p>Some add-ons are not available with packages that already include similar services. Ineligible combinations may be restricted at checkout or adjusted by our team.</p>',
    '<h2>Payment and Pricing</h2>',
    '<p>Pricing and payment terms are shown at booking. Orders may be subject to confirmation and scheduling availability.</p>',
    '<h2>Usage and Intellectual Property</h2>',
    '<p>Delivered media is licensed to the client for agreed marketing use. Website content and brand assets remain our property unless otherwise stated in writing.</p>',
    '<h2>Limitation of Liability</h2>',
    '<p>To the fullest extent permitted by law, services and website access are provided as-is. We are not liable for indirect, incidental, or consequential damages.</p>',
    '<h2>Updates and Contact</h2>',
    '<p>We may update these terms by posting revisions on this page. Questions can be sent to the email listed on our Contact page.</p>',
  ]);

  return [
    'privacy' => $privacy_content,
    'terms' => $terms_content,
  ];
}

/**
 * Ensure legal pages exist (and use the correct page templates).
 *
 * This is safe to call multiple times; it only creates pages when missing.
 */
function slm_ensure_legal_pages(): void
{
  $ensure_page = static function (string $title, string $slug, string $template, string $content_html): int {
    $existing = get_page_by_path($slug) ?: get_page_by_title($title);
    if ($existing) {
      $update = ['ID' => $existing->ID];
      $should_update = false;
      $current_content = trim((string) $existing->post_content);

      if ($existing->post_status !== 'publish') {
        $update['post_status'] = 'publish';
        $should_update = true;
      }

      $should_seed_content = ($current_content === '');
      if (!$should_seed_content && $slug === 'terms-of-service') {
        $looks_like_legacy_default = strpos($current_content, '<h2>Acceptance</h2>') !== false;
        $already_has_new_default = strpos($current_content, '<h2>Booking and Acceptance</h2>') !== false;
        if ($looks_like_legacy_default && !$already_has_new_default) {
          $should_seed_content = true;
        }
      }

      if ($should_seed_content) {
        $update['post_content'] = $content_html;
        $should_update = true;
      }
      if ($should_update) {
        wp_update_post($update);
      }

      update_post_meta($existing->ID, '_wp_page_template', $template);
      return (int) $existing->ID;
    }

    $id = wp_insert_post([
      'post_title' => $title,
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => $slug,
      'post_content' => $content_html,
    ]);
    if ($id && !is_wp_error($id)) {
      update_post_meta((int) $id, '_wp_page_template', $template);
      return (int) $id;
    }
    return 0;
  };

  $defaults = slm_legal_default_content();
  $ensure_page('Privacy Policy', 'privacy-policy', 'templates/page-privacy-policy.php', $defaults['privacy']);
  $ensure_page('Terms of Service', 'terms-of-service', 'templates/page-terms-of-service.php', $defaults['terms']);
}

/**
 * Keep legal pages available even when the theme was activated before this logic existed.
 */
add_action('init', function () {
  if (wp_installing())
    return;

  $privacy = get_page_by_path('privacy-policy');
  $terms = get_page_by_path('terms-of-service');
  if (!$privacy || !$terms) {
    slm_ensure_legal_pages();
  }
}, 5);

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', [
    'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'
  ]);

  add_theme_support('custom-logo', [
    'height' => 48,
    'width' => 48,
    'flex-height' => true,
    'flex-width' => true,
  ]);

  register_nav_menus([
    'primary' => __('Primary Menu', 'slm'),
    'footer' => __('Footer Menu', 'slm'),
  ]);
});

add_action('wp_enqueue_scripts', function () {
  $uri = get_template_directory_uri();

  $fonts_url = 'https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap';
  wp_enqueue_style('slm-fonts', $fonts_url, [], null);

  // Base layers (always) - load in a predictable order.
  $base = '/assets/css/base.css';
  $components = '/assets/css/components.css';
  $nav = '/assets/css/nav.css';
  $pages = '/assets/css/pages.css';

  wp_enqueue_style('slm-base', $uri . $base, ['slm-fonts'], slm_asset_ver($base));
  wp_enqueue_style('slm-components', $uri . $components, ['slm-base'], slm_asset_ver($components));
  wp_enqueue_style('slm-nav', $uri . $nav, ['slm-components'], slm_asset_ver($nav));
  wp_enqueue_style('slm-pages', $uri . $pages, ['slm-nav'], slm_asset_ver($pages));

  $main = '/assets/js/main.js';
  wp_enqueue_script('slm-main', $uri . $main, [], slm_asset_ver($main), true);
});

/**
 * Force role-based redirect after successful login, regardless of requested URL.
 */
add_filter('login_redirect', function ($redirect_to, $requested_redirect_to, $user) {
  if ($user instanceof WP_User) {
    return slm_dashboard_url($user);
  }
  return $redirect_to;
}, 10, 3);

/**
 * Keep failed front-end login attempts on the themed login page.
 */
add_action('wp_login_failed', function () {
  $referrer = wp_get_referer();
  if (!$referrer)
    return;

  $login_url = slm_login_url();
  if (strpos($referrer, $login_url) === false)
    return;

  $target = add_query_arg('auth', 'failed', remove_query_arg('auth', $referrer));
  wp_safe_redirect($target);
  exit;
});

/**
 * Optional redirect of legacy individual service pages to anchor sections.
 * Disabled by default so individual service pages remain accessible.
 */
add_action('template_redirect', function () {
  if (!apply_filters('slm_enable_service_anchor_redirects', false))
    return;

  if (is_admin() || !is_page())
    return;

  $page_id = (int) get_queried_object_id();
  if ($page_id <= 0)
    return;

  $template = (string) get_page_template_slug($page_id);
  if ($template === '')
    return;

  $anchor_map = [
    'templates/page-service-re-photography.php' => '#listing-media-packages',
    'templates/page-service-re-videography.php' => '#listing-media-packages',
    'templates/page-service-drone-photography.php' => '#listing-media-packages',
    'templates/page-service-virtual-tours.php' => '#listing-media-packages',
    'templates/page-service-floor-plans.php' => '#listing-media-packages',
    'templates/page-service-zillow-showcase.php' => '#listing-media-packages',
    'templates/page-service-drone-videography.php' => '#popular-add-ons',
    'templates/page-service-twilight-photography.php' => '#popular-add-ons',
  ];

  if (!isset($anchor_map[$template]))
    return;

  $target = home_url('/services/' . $anchor_map[$template]);
  wp_safe_redirect($target, 301);
  exit;
});

/**
 * Local/dev convenience: ensure WP uses a static front page when the theme is activated.
 * Only runs if no front page is currently set.
 */
add_action('after_switch_theme', function () {
  // Always ensure legal pages exist, even if a front page is already set.
  slm_ensure_legal_pages();
  update_option('page_for_posts', 0);

  // Keep an editable company-values page available when blog is disabled.
  $about = get_page_by_path('about') ?: get_page_by_title('About');
  if (!$about) {
    $about_id = wp_insert_post([
      'post_title' => 'About',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => 'about',
    ]);
    if ($about_id && !is_wp_error($about_id)) {
      update_post_meta((int) $about_id, '_wp_page_template', 'templates/page-about.php');
    }
  } else {
    update_post_meta((int) $about->ID, '_wp_page_template', 'templates/page-about.php');
  }

  if (get_option('page_on_front'))
    return;

  // Home page
  $home = get_page_by_path('home') ?: get_page_by_title('Home');
  if (!$home) {
    $home_id = wp_insert_post([
      'post_title' => 'Home',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => 'home',
    ]);
  } else {
    $home_id = $home->ID;
  }

  update_option('show_on_front', 'page');
  update_option('page_on_front', (int) $home_id);
});

function slm_import_theme_image_attachment(int $post_id, string $relative_path, string $title = ''): int
{
  $relative_path = ltrim($relative_path, '/');
  if ($relative_path === '') {
    return 0;
  }

  $existing = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'meta_key' => '_slm_theme_seed_source',
    'meta_value' => $relative_path,
    'no_found_rows' => true,
  ]);
  if (!empty($existing)) {
    return (int) $existing[0];
  }

  $source_path = trailingslashit(get_template_directory()) . $relative_path;
  if (!is_file($source_path) || !is_readable($source_path)) {
    return 0;
  }

  $uploads = wp_upload_dir();
  if (!empty($uploads['error'])) {
    return 0;
  }

  $filename = wp_unique_filename($uploads['path'], wp_basename($source_path));
  $target_path = trailingslashit($uploads['path']) . $filename;
  if (!@copy($source_path, $target_path)) {
    return 0;
  }

  require_once ABSPATH . 'wp-admin/includes/image.php';
  require_once ABSPATH . 'wp-admin/includes/file.php';
  require_once ABSPATH . 'wp-admin/includes/media.php';

  $filetype = wp_check_filetype($filename, null);
  $attach_id = wp_insert_attachment([
    'post_mime_type' => (string) ($filetype['type'] ?? ''),
    'post_title' => $title !== '' ? $title : preg_replace('/\.[^.]+$/', '', $filename),
    'post_content' => '',
    'post_status' => 'inherit',
  ], $target_path, $post_id);

  if (!$attach_id || is_wp_error($attach_id)) {
    return 0;
  }

  $attach_data = wp_generate_attachment_metadata((int) $attach_id, $target_path);
  if (is_array($attach_data)) {
    wp_update_attachment_metadata((int) $attach_id, $attach_data);
  }

  update_post_meta((int) $attach_id, '_slm_theme_seed_source', $relative_path);
  return (int) $attach_id;
}

function slm_seed_portfolio_content(): void
{
  if (wp_installing()) {
    return;
  }

  $seed_version = '2026-02-launch';
  if (get_option('slm_portfolio_seed_version') === $seed_version) {
    return;
  }

  $existing = get_posts([
    'post_type' => 'portfolio',
    'post_status' => ['publish', 'future', 'draft', 'pending', 'private'],
    'posts_per_page' => 1,
    'fields' => 'ids',
    'no_found_rows' => true,
  ]);

  if (!empty($existing)) {
    update_option('slm_portfolio_seed_version', $seed_version, false);
    return;
  }

  $items = [
    [
      'title' => 'Riverfront Listing Showcase',
      'excerpt' => 'Launch-ready listing media with polished interiors, clean lighting, and sales-focused framing.',
      'content' => 'A full listing package with interior highlights, exterior curb-appeal coverage, and edit consistency for MLS and social use.',
      'featured' => 'assets/media/photos/01-0-front-exterior.jpg',
      'gallery' => [
        'assets/media/photos/02-12-living-room-5-of-6.jpg',
        'assets/media/photos/03-17-dining-room-1-of-4.jpg',
        'assets/media/photos/14-35-primary-bedroom-4-of-4.jpg',
      ],
    ],
    [
      'title' => 'Aerial + Exterior Property Campaign',
      'excerpt' => 'Drone-first campaign assets showing lot scale, orientation, and neighborhood context.',
      'content' => 'Built for listings where lot positioning and surrounding geography need to be clear to buyers at first glance.',
      'featured' => 'assets/media/drone-photos/03-1-aerial-front-exterior-1.jpg',
      'gallery' => [
        'assets/media/drone-photos/04-2-aerial-front-exterior-2.jpg',
        'assets/media/drone-photos/05-3-aerial-overview.jpg',
        'assets/media/drone-photos/07-4-aerial-rear-view-1.jpg',
      ],
    ],
    [
      'title' => 'Lifestyle + Virtual Staging Series',
      'excerpt' => 'Before-and-after style visual direction designed for high-engagement listing promotion.',
      'content' => 'Combines clean base photography with staged alternates for stronger perceived value and audience retention.',
      'featured' => 'assets/media/staged/01-13-living-room-5-of-6-virtually-staged.jpeg',
      'gallery' => [
        'assets/media/staged/04-26-breakfast-bar-virtually-staged.jpeg',
        'assets/media/staged/05-32-primary-bedroom-1-of-4-virtually-staged.jpeg',
        'assets/media/staged/08-46-lanai-virtually-staged.jpeg',
      ],
    ],
  ];

  $gallery_meta_key = function_exists('slm_portfolio_gallery_meta_key')
    ? slm_portfolio_gallery_meta_key()
    : 'slm_portfolio_gallery_ids';

  $created = 0;
  foreach ($items as $item) {
    $post_id = wp_insert_post([
      'post_type' => 'portfolio',
      'post_status' => 'publish',
      'post_title' => (string) $item['title'],
      'post_excerpt' => (string) $item['excerpt'],
      'post_content' => (string) $item['content'],
    ]);

    if (!$post_id || is_wp_error($post_id)) {
      continue;
    }

    $featured_id = slm_import_theme_image_attachment((int) $post_id, (string) $item['featured'], (string) $item['title']);
    if ($featured_id > 0) {
      set_post_thumbnail((int) $post_id, $featured_id);
    }

    $gallery_ids = [];
    foreach ((array) ($item['gallery'] ?? []) as $idx => $path) {
      $title = (string) $item['title'] . ' Gallery ' . ((int) $idx + 1);
      $image_id = slm_import_theme_image_attachment((int) $post_id, (string) $path, $title);
      if ($image_id > 0 && $image_id !== $featured_id) {
        $gallery_ids[] = $image_id;
      }
    }

    if (!empty($gallery_ids)) {
      update_post_meta((int) $post_id, $gallery_meta_key, implode(',', $gallery_ids));
    }

    $created++;
  }

  if ($created > 0) {
    update_option('slm_portfolio_seed_version', $seed_version, false);
  }
}

add_action('init', function () {
  register_post_type('portfolio', [
    'labels' => [
      'name' => 'Portfolio',
      'singular_name' => 'Portfolio Item',
      'add_new_item' => 'Add New Portfolio Item',
      'edit_item' => 'Edit Portfolio Item',
    ],
    'public' => true,
    'has_archive' => true,
    'rewrite' => ['slug' => 'portfolio'],
    'menu_icon' => 'dashicons-format-gallery',
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
    'show_in_rest' => true,
  ]);
});

add_action('init', 'slm_seed_portfolio_content', 20);
