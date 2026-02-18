<?php
if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/inc/aryeo.php';
require_once __DIR__ . '/inc/testimonials.php';
require_once __DIR__ . '/inc/portfolio-gallery.php';
require_once __DIR__ . '/inc/footer-customizer.php';

/**
 * Hint compatible cache layers to bypass full-page cache for signed-in users.
 */
add_action('init', function () {
  if (is_user_logged_in() && !defined('DONOTCACHEPAGE')) {
    define('DONOTCACHEPAGE', true);
  }
}, 1);

function slm_theme_ver() {
  $t = wp_get_theme();
  return $t->get('Version') ?: '0.1.0';
}

/**
 * True when the given user can access the admin dashboard area.
 */
function slm_user_is_admin($user = null): bool {
  if (!$user instanceof WP_User) {
    $user = wp_get_current_user();
  }
  return ($user instanceof WP_User) && user_can($user, 'manage_options');
}

/**
 * Resolve a published page URL by assigned page template.
 */
function slm_page_url_by_template(string $template_file, string $fallback_path): string {
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

function slm_admin_portal_url(): string {
  return slm_page_url_by_template('admin-portal.php', '/admin-portal/');
}

function slm_portal_url(): string {
  return slm_page_url_by_template('page-portal.php', '/portal/');
}

function slm_login_url(): string {
  return slm_page_url_by_template('page-login.php', '/login/');
}

/**
 * Role-aware dashboard target.
 */
function slm_dashboard_url($user = null): string {
  return slm_user_is_admin($user) ? slm_admin_portal_url() : slm_portal_url();
}

/**
 * File-based cache busting. Pass a theme-relative path like: /assets/css/base.css
 */
function slm_asset_ver(string $rel_path): string {
  $abs = get_template_directory() . $rel_path;
  if (is_file($abs)) return (string) filemtime($abs);
  return slm_theme_ver();
}

/**
 * Ensure portal pages exist (and use the correct page templates).
 *
 * This is safe to call multiple times; it only creates pages when missing.
 */
function slm_ensure_portal_pages(): void {
  $pages = [
    [
      'title' => 'Login',
      'slug' => 'login',
      'template' => 'templates/page-login.php',
      'content' => '',
    ],
    [
      'title' => 'Portal',
      'slug' => 'portal',
      'template' => 'templates/page-portal.php',
      'content' => '',
    ],
    [
      'title' => 'Admin Portal',
      'slug' => 'admin-portal',
      'template' => 'templates/admin-portal.php',
      'content' => '',
    ],
  ];

  foreach ($pages as $page) {
    $title = (string) ($page['title'] ?? '');
    $slug = (string) ($page['slug'] ?? '');
    $template = (string) ($page['template'] ?? '');
    $content = (string) ($page['content'] ?? '');

    if ($title === '' || $slug === '' || $template === '') {
      continue;
    }

    $existing = get_page_by_path($slug) ?: get_page_by_title($title);
    if ($existing) {
      $update = ['ID' => $existing->ID];
      $should_update = false;

      if ($existing->post_status !== 'publish') {
        $update['post_status'] = 'publish';
        $should_update = true;
      }

      if ($content !== '' && trim((string) $existing->post_content) === '') {
        $update['post_content'] = $content;
        $should_update = true;
      }

      if ($should_update) {
        wp_update_post($update);
      }

      update_post_meta($existing->ID, '_wp_page_template', $template);
      continue;
    }

    $id = wp_insert_post([
      'post_title' => $title,
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => $slug,
      'post_content' => $content,
    ]);
    if ($id && !is_wp_error($id)) {
      update_post_meta((int) $id, '_wp_page_template', $template);
    }
  }
}

/**
 * Ensure legal pages exist (and use the correct page templates).
 *
 * This is safe to call multiple times; it only creates pages when missing.
 */
function slm_ensure_legal_pages(): void {
  $ensure_page = static function (string $title, string $slug, string $template, string $content_html): int {
    $existing = get_page_by_path($slug) ?: get_page_by_title($title);
    if ($existing) {
      $update = ['ID' => $existing->ID];
      $should_update = false;

      if ($existing->post_status !== 'publish') {
        $update['post_status'] = 'publish';
        $should_update = true;
      }
      if (trim((string) $existing->post_content) === '') {
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

  $privacy_content = implode("\n", [
    '<p><em>Last updated: ' . esc_html(date('F j, Y')) . '</em></p>',
    '<h2>Overview</h2>',
    '<p>This Privacy Policy explains how we collect, use, and protect information when you use this website, contact us, or create an account.</p>',
    '<h2>Information We Collect</h2>',
    '<ul>',
    '<li>Contact details you submit (such as name, email, phone).</li>',
    '<li>Account information (if you register).</li>',
    '<li>Order-related information you submit through our portal.</li>',
    '<li>Basic usage data (for example browser/device information and approximate location) via cookies or analytics, if enabled.</li>',
    '</ul>',
    '<h2>How We Use Information</h2>',
    '<ul>',
    '<li>To respond to inquiries and provide customer support.</li>',
    '<li>To provide and improve our services and portal experience.</li>',
    '<li>To communicate about orders, updates, and service-related messages.</li>',
    '</ul>',
    '<h2>Sharing</h2>',
    '<p>We may share information with service providers we use to operate the website and deliver services (for example hosting, email delivery, and order/portal integrations). We do not sell personal information.</p>',
    '<h2>Cookies</h2>',
    '<p>This site may use cookies for login sessions, security, and basic functionality. You can control cookies in your browser settings.</p>',
    '<h2>Data Retention</h2>',
    '<p>We retain information as needed to provide services, meet legal obligations, and resolve disputes.</p>',
    '<h2>Contact</h2>',
    '<p>If you have questions about this policy, contact us using the email listed on our Contact page.</p>',
  ]);

  $terms_content = implode("\n", [
    '<p><em>Last updated: ' . esc_html(date('F j, Y')) . '</em></p>',
    '<h2>Acceptance</h2>',
    '<p>By accessing or using this website and any related services, you agree to these Terms of Service.</p>',
    '<h2>Services</h2>',
    '<p>We provide real estate media services and an online portal for ordering, communication, and delivery.</p>',
    '<h2>Accounts</h2>',
    '<p>If you create an account, you are responsible for maintaining the confidentiality of your login credentials and for activities that occur under your account.</p>',
    '<h2>Orders, Pricing, and Payment</h2>',
    '<p>Pricing, service details, and payment requirements may be presented at the time of order. Orders may be subject to confirmation and scheduling availability.</p>',
    '<h2>Intellectual Property</h2>',
    '<p>Website content, branding, and materials are owned by us or our licensors. You may not copy or reuse them without permission.</p>',
    '<h2>Prohibited Use</h2>',
    '<p>You agree not to misuse the website, attempt unauthorized access, or interfere with the website&apos;s operation.</p>',
    '<h2>Disclaimers and Limitation of Liability</h2>',
    '<p>The website and services are provided &quot;as is&quot; to the maximum extent permitted by law. We are not liable for indirect or consequential damages.</p>',
    '<h2>Changes</h2>',
    '<p>We may update these terms from time to time by posting changes on this page.</p>',
    '<h2>Contact</h2>',
    '<p>Questions about these terms can be sent to the email listed on our Contact page.</p>',
  ]);

  $ensure_page('Privacy Policy', 'privacy-policy', 'templates/page-privacy-policy.php', $privacy_content);
  $ensure_page('Terms of Service', 'terms-of-service', 'templates/page-terms-of-service.php', $terms_content);
}

/**
 * Keep legal pages available even when the theme was activated before this logic existed.
 */
add_action('init', function () {
  if (wp_installing()) return;

  $login = get_page_by_path('login');
  $portal = get_page_by_path('portal');
  $admin_portal = get_page_by_path('admin-portal');
  if (!$login || !$portal || !$admin_portal) {
    slm_ensure_portal_pages();
  }

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
    'search-form','comment-form','comment-list','gallery','caption','style','script'
  ]);

  add_theme_support('custom-logo', [
    'height'      => 48,
    'width'       => 48,
    'flex-height' => true,
    'flex-width'  => true,
  ]);

  register_nav_menus([
    'primary' => __('Primary Menu', 'slm'),
    'footer'  => __('Footer Menu', 'slm'),
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

  wp_enqueue_style('slm-base',       $uri . $base, ['slm-fonts'], slm_asset_ver($base));
  wp_enqueue_style('slm-components', $uri . $components, ['slm-base'], slm_asset_ver($components));
  wp_enqueue_style('slm-nav',        $uri . $nav, ['slm-components'], slm_asset_ver($nav));
  wp_enqueue_style('slm-pages',      $uri . $pages, ['slm-nav'], slm_asset_ver($pages));

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
  if (!$referrer) return;

  $login_url = slm_login_url();
  if (strpos($referrer, $login_url) === false) return;

  $target = add_query_arg('auth', 'failed', remove_query_arg('auth', $referrer));
  wp_safe_redirect($target);
  exit;
});

/**
 * Optional redirect of legacy individual service pages to anchor sections.
 * Disabled by default so individual service pages remain accessible.
 */
add_action('template_redirect', function () {
  if (!apply_filters('slm_enable_service_anchor_redirects', false)) return;

  if (is_admin() || !is_page()) return;

  $page_id = (int) get_queried_object_id();
  if ($page_id <= 0) return;

  $template = (string) get_page_template_slug($page_id);
  if ($template === '') return;

  $anchor_map = [
    'templates/page-service-re-photography.php' => '#listing-media-packages',
    'templates/page-service-re-videography.php' => '#listing-media-packages',
    'templates/page-service-drone-photography.php' => '#listing-media-packages',
    'templates/page-service-virtual-tours.php' => '#listing-media-packages',
    'templates/page-service-floor-plans.php' => '#listing-media-packages',
    'templates/page-service-drone-videography.php' => '#popular-add-ons',
    'templates/page-service-twilight-photography.php' => '#popular-add-ons',
  ];

  if (!isset($anchor_map[$template])) return;

  $target = home_url('/services/' . $anchor_map[$template]);
  wp_safe_redirect($target, 301);
  exit;
});

/**
 * Local/dev convenience: ensure WP uses a static front page when the theme is activated.
 * Only runs if no front page is currently set.
 */
add_action('after_switch_theme', function () {
  // Ensure role-routed dashboard pages exist.
  slm_ensure_portal_pages();

  // Always ensure legal pages exist, even if a front page is already set.
  slm_ensure_legal_pages();

  if (get_option('page_on_front')) return;

  // Home page
  $home = get_page_by_path('home') ?: get_page_by_title('Home');
  if (!$home) {
    $home_id = wp_insert_post([
      'post_title'  => 'Home',
      'post_status' => 'publish',
      'post_type'   => 'page',
      'post_name'   => 'home',
    ]);
  } else {
    $home_id = $home->ID;
  }

  // Blog page (optional, but keeps posts clean)
  $blog = get_page_by_path('blog') ?: get_page_by_title('Blog');
  if (!$blog) {
    $blog_id = wp_insert_post([
      'post_title'  => 'Blog',
      'post_status' => 'publish',
      'post_type'   => 'page',
      'post_name'   => 'blog',
    ]);
  } else {
    $blog_id = $blog->ID;
  }

  update_option('show_on_front', 'page');
  update_option('page_on_front', (int) $home_id);
  update_option('page_for_posts', (int) $blog_id);

  // Legal pages
  $ensure_page = static function (string $title, string $slug, string $template, string $content_html): int {
    $existing = get_page_by_path($slug) ?: get_page_by_title($title);
    if ($existing) {
      if ($existing->post_status !== 'publish') {
        wp_update_post(['ID' => $existing->ID, 'post_status' => 'publish']);
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

  $privacy_content = implode("\n", [
    '<p><em>Last updated: ' . esc_html(date('F j, Y')) . '</em></p>',
    '<h2>Overview</h2>',
    '<p>This Privacy Policy explains how we collect, use, and protect information when you use this website, contact us, or create an account.</p>',
    '<h2>Information We Collect</h2>',
    '<ul>',
    '<li>Contact details you submit (such as name, email, phone).</li>',
    '<li>Account information (if you register).</li>',
    '<li>Order-related information you submit through our portal.</li>',
    '<li>Basic usage data (for example browser/device information and approximate location) via cookies or analytics, if enabled.</li>',
    '</ul>',
    '<h2>How We Use Information</h2>',
    '<ul>',
    '<li>To respond to inquiries and provide customer support.</li>',
    '<li>To provide and improve our services and portal experience.</li>',
    '<li>To communicate about orders, updates, and service-related messages.</li>',
    '</ul>',
    '<h2>Sharing</h2>',
    '<p>We may share information with service providers we use to operate the website and deliver services (for example hosting, email delivery, and order/portal integrations). We do not sell personal information.</p>',
    '<h2>Cookies</h2>',
    '<p>This site may use cookies for login sessions, security, and basic functionality. You can control cookies in your browser settings.</p>',
    '<h2>Data Retention</h2>',
    '<p>We retain information as needed to provide services, meet legal obligations, and resolve disputes.</p>',
    '<h2>Contact</h2>',
    '<p>If you have questions about this policy, contact us using the email listed on our Contact page.</p>',
  ]);

  $terms_content = implode("\n", [
    '<p><em>Last updated: ' . esc_html(date('F j, Y')) . '</em></p>',
    '<h2>Acceptance</h2>',
    '<p>By accessing or using this website and any related services, you agree to these Terms of Service.</p>',
    '<h2>Services</h2>',
    '<p>We provide real estate media services and an online portal for ordering, communication, and delivery.</p>',
    '<h2>Accounts</h2>',
    '<p>If you create an account, you are responsible for maintaining the confidentiality of your login credentials and for activities that occur under your account.</p>',
    '<h2>Orders, Pricing, and Payment</h2>',
    '<p>Pricing, service details, and payment requirements may be presented at the time of order. Orders may be subject to confirmation and scheduling availability.</p>',
    '<h2>Intellectual Property</h2>',
    '<p>Website content, branding, and materials are owned by us or our licensors. You may not copy or reuse them without permission.</p>',
    '<h2>Prohibited Use</h2>',
    '<p>You agree not to misuse the website, attempt unauthorized access, or interfere with the website’s operation.</p>',
    '<h2>Disclaimers and Limitation of Liability</h2>',
    '<p>The website and services are provided “as is” to the maximum extent permitted by law. We are not liable for indirect or consequential damages.</p>',
    '<h2>Changes</h2>',
    '<p>We may update these terms from time to time by posting changes on this page.</p>',
    '<h2>Contact</h2>',
    '<p>Questions about these terms can be sent to the email listed on our Contact page.</p>',
  ]);

  $ensure_page('Privacy Policy', 'privacy-policy', 'templates/page-privacy-policy.php', $privacy_content);
  $ensure_page('Terms of Service', 'terms-of-service', 'templates/page-terms-of-service.php', $terms_content);
});

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
