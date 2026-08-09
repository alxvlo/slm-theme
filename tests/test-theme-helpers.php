<?php
/**
 * Covers the two helpers introduced for review items 2 and 7:
 *   slm_book_url() — the single canonical "Book a Shoot" destination.
 *   slm_page_seo() — registers a page title and meta description together.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../functions.php';

/* ---------------------------------------------------------------- item 2 */

function test_book_url_logged_out_goes_to_signup()
{
    WP_Mock::reset();
    WP_Mock::$is_logged_in = false;
    WP_Mock::$home_url_result = 'http://example.com';

    $url = slm_book_url();
    assert(is_string($url) && $url !== '', 'slm_book_url() should return a non-empty string');
    assert(strpos($url, '/login/') !== false, "Logged-out booking URL should hit the login page, got: $url");
    assert(strpos($url, 'mode=signup') !== false, "Logged-out booking URL should carry mode=signup, got: $url");
    echo "PASS: test_book_url_logged_out_goes_to_signup\n";
}

function test_book_url_logged_in_starts_an_order()
{
    WP_Mock::reset();
    WP_Mock::$is_logged_in = true;
    WP_Mock::$home_url_result = 'http://example.com';

    $url = slm_book_url();
    assert(strpos($url, 'mode=signup') === false, "Logged-in users should not be sent to signup, got: $url");
    assert(
        strpos($url, 'slm_aryeo_start_order') !== false || strpos($url, 'place-order') !== false,
        "Logged-in booking URL should start an order, got: $url"
    );
    echo "PASS: test_book_url_logged_in_starts_an_order\n";
}

/** The whole point of item 2: no template may define its own booking URL. */
function test_no_adhoc_booking_url_definitions()
{
    $offenders = [];
    foreach (slm_test_theme_php_files() as $file) {
        $relative = slm_test_relative($file);
        if (strpos($relative, 'functions.php') === 0 || strpos($relative, 'inc/') === 0) {
            continue; // helper definitions live here
        }
        $body = (string) file_get_contents($file);
        if (preg_match('/\$(cta_url|order_url|book_url|place_order_url)\s*=\s*\$?is_logged_in/', $body)) {
            $offenders[] = $relative;
        }
    }
    assert(
        $offenders === [],
        'Ad-hoc booking URL definitions still present in: ' . implode(', ', $offenders)
            . ' — route them through slm_book_url()'
    );
    echo "PASS: test_no_adhoc_booking_url_definitions\n";
}

/**
 * The shared service-detail block renders the primary CTA for all eight
 * service pages, so it is the single highest-traffic booking button.
 */
function test_service_detail_block_uses_canonical_booking_url()
{
    $block = slm_test_read('template-parts/blocks/service-detail.php');

    assert(
        strpos($block, 'slm_book_url()') !== false,
        'service-detail.php should resolve its primary CTA through slm_book_url()'
    );
    assert(
        !preg_match('/\$primary_url\s*=\s*\$is_logged_in/', $block),
        'service-detail.php should not branch on login state to build the booking URL'
    );
    echo "PASS: test_service_detail_block_uses_canonical_booking_url\n";
}

/* ---------------------------------------------------------------- item 7 */

function test_page_seo_registers_title_and_description()
{
    WP_Mock::reset();
    slm_page_seo('Aerial Photography | Showcase Listings Media', 'Drone photography for North Florida.');

    $title_filters = WP_Mock::callbacks(WP_Mock::$filters, 'pre_get_document_title');
    assert(count($title_filters) === 1, 'slm_page_seo() should register exactly one pre_get_document_title filter');
    assert(
        $title_filters[0]('fallback') === 'Aerial Photography | Showcase Listings Media',
        'The registered filter should return the supplied title'
    );

    $head_actions = WP_Mock::callbacks(WP_Mock::$actions, 'wp_head');
    assert(count($head_actions) === 1, 'slm_page_seo() should register exactly one wp_head action');

    ob_start();
    $head_actions[0]();
    $head = (string) ob_get_clean();
    assert(
        strpos($head, '<meta name="description" content="Drone photography for North Florida.">') !== false,
        "wp_head output should contain the meta description, got: $head"
    );
    echo "PASS: test_page_seo_registers_title_and_description\n";
}

function test_page_seo_escapes_the_description()
{
    WP_Mock::reset();
    slm_page_seo('T', 'Jacksonville\'s "best" <agents>');

    $head_actions = WP_Mock::callbacks(WP_Mock::$actions, 'wp_head');
    ob_start();
    $head_actions[0]();
    $head = (string) ob_get_clean();

    assert(strpos($head, '<agents>') === false, "Raw angle brackets must be escaped, got: $head");
    assert(strpos($head, '&quot;best&quot;') !== false, "Double quotes must be escaped, got: $head");
    echo "PASS: test_page_seo_escapes_the_description\n";
}

/**
 * Review item 7: every public page template must register title + description,
 * or WordPress falls back to the raw slug.
 */
function test_every_public_page_template_registers_seo()
{
    $exempt = [
        // Auth-sensitive or utility templates that are intentionally not indexed.
        'page-portal.php',
        'admin-portal.php',
        'page-login.php',
        'page-privacy-policy.php',
        'page-terms-of-service.php',
        // 301-redirects to the homepage before rendering; never has a title.
        'page-blog.php',
    ];

    $missing = [];
    foreach (glob(slm_test_theme_dir() . '/templates/*.php') ?: [] as $template) {
        if (in_array(basename($template), $exempt, true)) {
            continue;
        }
        $body = (string) file_get_contents($template);
        $has_seo = strpos($body, 'slm_page_seo(') !== false
            || (strpos($body, 'pre_get_document_title') !== false && strpos($body, 'name="description"') !== false);

        if (!$has_seo) {
            $missing[] = basename($template);
        }
    }

    assert(
        $missing === [],
        'Page templates missing title/description registration: ' . implode(', ', $missing)
    );
    echo "PASS: test_every_public_page_template_registers_seo\n";
}
