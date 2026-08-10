<?php
/**
 * Portfolio media follow-ups (2026-08-10): videos render on the portfolio page,
 * the featured project is data-driven instead of hardcoded, and the legacy
 * /portfolio/ page 301s to the canonical page.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/helpers.php';

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) { return trim(strip_tags((string) $str)); }
}

require_once __DIR__ . '/../functions.php';

/** The Video Portfolio picker's meta must actually reach the public page. */
function test_portfolio_defaults_include_videos()
{
    $items_module = slm_test_read('inc/portfolio-items.php');
    assert(
        strpos($items_module, 'function slm_portfolio_gallery_videos()') !== false,
        'inc/portfolio-items.php should resolve slm_portfolio_video_ids into video records'
    );
    assert(
        strpos($items_module, "'type' => 'video'") !== false
            && strpos($items_module, "'category' => 'Cinematic Video'") !== false,
        'Default items should append video records typed video, defaulting to Cinematic Video'
    );
    echo "PASS: test_portfolio_defaults_include_videos\n";
}

function test_portfolio_sanitizer_accepts_type()
{
    $items = slm_portfolio_items_sanitize([
        [
            'id' => 1,
            'title' => 'Cinematic Tour',
            'category' => 'Cinematic Video',
            'type' => 'video',
            'image' => 'http://example.com/tour.mp4',
            'thumb' => '',
            'metrics' => ['14,200 Video Views'],
        ],
        [
            'id' => 2,
            'title' => 'Pool Home',
            'category' => 'Real Estate Photography',
            'type' => 'bogus',
            'image' => 'http://example.com/pool.jpg',
            'thumb' => '',
            'metrics' => [],
        ],
    ]);

    assert(count($items) === 2, 'Both items should survive sanitization');
    assert($items[0]['type'] === 'video', 'Video type must be preserved');
    assert(
        $items[0]['thumb'] === '',
        'A video with no poster keeps an empty thumb — the video URL is not a valid <img> source'
    );
    assert($items[1]['type'] === 'image', 'Unknown types must fall back to image');
    assert($items[1]['thumb'] === 'http://example.com/pool.jpg', 'Image thumb still falls back to the image URL');
    echo "PASS: test_portfolio_sanitizer_accepts_type\n";
}

/** The featured project must come from item data, not hardcoded copy. */
function test_featured_project_is_data_driven()
{
    $template = slm_test_read('templates/page-portfolio.php');
    assert(
        strpos($template, '$featured_item') !== false,
        'page-portfolio.php should resolve a featured item from the items list'
    );
    assert(
        strpos($template, '<span class="port-featured__cat">Cinematic Video</span>') === false,
        'The featured category label must not be hardcoded'
    );
    assert(
        strpos($template, '<h2 class="port-featured__name">6000 on the River</h2>') === false,
        'The featured project name must not be hardcoded'
    );
    echo "PASS: test_featured_project_is_data_driven\n";
}

function test_portfolio_grid_and_lightbox_support_video()
{
    $template = slm_test_read('templates/page-portfolio.php');
    assert(
        strpos($template, 'id="lbVideo"') !== false,
        'The lightbox needs a video element'
    );
    assert(
        strpos($template, 'port-card__play') !== false,
        'Video cards should carry a play badge'
    );
    assert(
        strpos($template, "item.type === 'video'") !== false,
        'The grid renderer should branch on item type'
    );
    echo "PASS: test_portfolio_grid_and_lightbox_support_video\n";
}

/** Review item 6 — the legacy /portfolio/ page redirects to the canonical one. */
function test_legacy_portfolio_page_redirects()
{
    $functions = slm_test_read('functions.php');
    $hook = strpos($functions, "add_action('template_redirect'");
    assert($hook !== false, 'functions.php should register a template_redirect handler');
    $body = substr($functions, $hook, 1200);
    assert(
        strpos($body, 'slm_portfolio_page_id()') !== false
            && strpos($body, 'wp_safe_redirect($target, 301)') !== false,
        'Non-canonical pages using the portfolio template must 301 to the canonical page'
    );
    echo "PASS: test_legacy_portfolio_page_redirects\n";
}
