<?php
define('ABSPATH', __DIR__ . '/../');
define('DAY_IN_SECONDS', 86400);

class WP_Mock {
    public static $get_posts_result = [];
    public static $get_permalink_result = '';
    public static $home_url_result = '';
    public static $cache = [];

    public static function reset() {
        self::$get_posts_result = [];
        self::$get_permalink_result = '';
        self::$home_url_result = '';
        self::$cache = [];
    }
}

/**
 * We need to wrap the target functions in a way that we can clear their static variables.
 * However, PHP doesn't easily allow clearing static variables within a function.
 * Since we are in a mock environment, we might need a different approach if we want to test multiple states
 * without restarting the process. But our test runner runs each file in a new process anyway.
 * Wait, the test runner runs EACH FILE in a new process, but within the file, we call multiple tests.
 */

if (!function_exists('add_action')) {
    function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {}
}
if (!function_exists('add_filter')) {
    function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {}
}
if (!function_exists('register_post_type')) {
    function register_post_type($post_type, $args = []) {}
}
if (!function_exists('register_nav_menus')) {
    function register_nav_menus($locations = []) {}
}
if (!function_exists('add_theme_support')) {
    function add_theme_support($feature, $args = []) {}
}
if (!function_exists('register_setting')) {
    function register_setting($option_group, $option_name, $args = []) {}
}
if (!function_exists('add_settings_section')) {
    function add_settings_section($id, $title, $callback, $page) {}
}
if (!function_exists('add_settings_field')) {
    function add_settings_field($id, $title, $callback, $page, $section = 'default', $args = []) {}
}
if (!function_exists('add_options_page')) {
    function add_options_page($page_title, $menu_title, $capability, $menu_slug, $callback = '', $icon_url = '', $position = null) {}
}
if (!function_exists('register_rest_route')) {
    function register_rest_route($namespace, $route, $args = [], $override = false) {}
}
if (!function_exists('__')) {
    function __($text, $domain = 'default') { return $text; }
}
if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value, ...$args) { return $value; }
}
if (!function_exists('get_option')) {
    function get_option($option, $default = false) { return $default; }
}
if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) { return true; }
}
if (!function_exists('trailingslashit')) {
    function trailingslashit($string) { return rtrim($string, '/\\') . '/'; }
}
if (!function_exists('get_posts')) {
    function get_posts($args = null) { return WP_Mock::$get_posts_result; }
}
if (!function_exists('get_permalink')) {
    function get_permalink($post = 0, $leavename = false) { return WP_Mock::$get_permalink_result; }
}
if (!function_exists('home_url')) {
    function home_url($path = '', $scheme = null) {
        $base = WP_Mock::$home_url_result ?: 'http://example.com';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}
if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) { return false; }
}
if (!function_exists('get_transient')) {
    function get_transient($transient) { return false; }
}
if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) { return true; }
}
if (!function_exists('get_template_directory')) {
    function get_template_directory() { return __DIR__ . '/../'; }
}
if (!function_exists('get_template_directory_uri')) {
    function get_template_directory_uri() { return 'http://example.com/wp-content/themes/slm'; }
}
if (!function_exists('wp_get_theme')) {
    function wp_get_theme($stylesheet = '', $theme_root = '') {
        return new class {
            public function get($header) { return '1.0.0'; }
        };
    }
}
if (!function_exists('is_admin')) {
    function is_admin() { return false; }
}
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() { return false; }
}
if (!function_exists('wp_get_current_user')) {
    function wp_get_current_user() { return new stdClass(); }
}
if (!function_exists('is_page_template')) {
    function is_page_template($template = '') { return false; }
}
if (!function_exists('esc_url_raw')) {
    function esc_url_raw($url) { return $url; }
}
if (!function_exists('esc_url')) {
    function esc_url($url) { return $url; }
}
if (!function_exists('esc_html')) {
    function esc_html($text) { return $text; }
}
if (!function_exists('add_query_arg')) {
    function add_query_arg(...$args) { return 'http://example.com/query'; }
}
if (!function_exists('wp_reset_postdata')) {
    function wp_reset_postdata() {}
}
if (!function_exists('update_post_meta')) {
    function update_post_meta($post_id, $meta_key, $meta_value, $prev_value = '') { return true; }
}
if (!class_exists('WP_Query')) {
    class WP_Query {
        public $posts = [];
        public function have_posts() { return !empty($this->posts); }
        public function __construct($args = []) {}
    }
}
if (!function_exists('get_page_by_path')) {
    function get_page_by_path($path, $output = 'OBJECT', $post_type = 'page') { return null; }
}
if (!function_exists('get_page_by_title')) {
    function get_page_by_title($page_title, $output = 'OBJECT', $post_type = 'page') { return null; }
}
if (!function_exists('wp_insert_post')) {
    function wp_insert_post($postarr, $wp_error = false) { return 1; }
}
