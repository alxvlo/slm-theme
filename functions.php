<?php
if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/inc/aryeo.php';

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
 * Local/dev convenience: ensure WP uses a static front page when the theme is activated.
 * Only runs if no front page is currently set.
 */
add_action('after_switch_theme', function () {
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
