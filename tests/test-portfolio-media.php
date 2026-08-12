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
        strpos($items_module, "'type' => 'video'") !== false,
        'Default items should append video records typed video'
    );
    // Categories are derived per asset now, not assigned by gallery position.
    assert(
        strpos($items_module, '$default_categories') === false,
        'The positional $default_categories array must be gone — it filed a dining room under Drone'
    );
    echo "PASS: test_portfolio_defaults_include_videos\n";
}

/**
 * The classifier decides every default category, so it is checked against the
 * real prod media set rather than a hand-written sample.
 */
function test_portfolio_classifier_matches_live_media()
{
    $fixture = [
        // [title, filename, type, expected]
        ['Aerial Amenity', '1-Aerial-Amenity.webp', 'image', 'Drone'],
        ['Aerial Side Exterior — Day to Dusk', '2-Aerial-Side-Exterior-Day-to-Dusk.webp', 'image', 'Twilight'],
        ['Aerial View', '3-Aerial-View.webp', 'image', 'Drone'],
        ['Lanai', '4-Lanai-1-of-3.webp', 'image', 'Real Estate Photography'],
        ['Primary Bathroom', '18-Primary-Bathroom-1-of-3.webp', 'image', 'Real Estate Photography'],
        ['Kitchen', '6-Kitchen-2-of-3.webp', 'image', 'Real Estate Photography'],
        ['Dining Room', '8-Dining-Room-3-of-3.webp', 'image', 'Real Estate Photography'],
        ['Aerial Rear View', '9-Aerial-Rear-View.webp', 'image', 'Drone'],
        ['Aerial Overview', '10-Aerial-Overview.webp', 'image', 'Drone'],
        ['Swimming Pool at Dusk', '11-Swimming-Pool-at-Dusk.webp', 'image', 'Twilight'],
        ['Front Exterior', '12-Front-Exterior.webp', 'image', 'Real Estate Photography'],
        ['Aerial Front Exterior', '13-Aerial-Front-Exterior.webp', 'image', 'Drone'],
        ['Front Exterior at Dusk', '14-Front-Exterior-at-Dusk.webp', 'image', 'Twilight'],
        ['Twilight Aerial Front Exterior', '99-Twilight-Aerial-Front-Exterior-3.webp', 'image', 'Twilight'],
        ['Inside This Stunning North Florida Home', 'inside-this-stunning-north-florida-home.mp4', 'video', 'Cinematic Video'],
        ["Shahid Khan's Superyacht Kismet", 'shahid-khans-superyacht-kismet.mp4', 'video', 'Cinematic Video'],
        ['Ortega Bridge Jacksonville, FL.', 'ortega-bridge-jacksonville-fl.mp4', 'video', 'Cinematic Video'],
        ['Jax Mini Sessions Studio — Brand Video', 'jax-mini-sessions-studio-brand-video.mp4', 'video', 'Business Branding'],
        ['Properties are experiences #shorts', 'Properties-are-experiences-shorts.mp4', 'video', 'Social Media / Reels'],
        ['Social Media Reel', 'social-media-reel.mp4', 'video', 'Social Media / Reels'],
        ['Virtual Staging', 'virtual-staging.mp4', 'video', 'Cinematic Video'],
    ];

    foreach ($fixture as $row) {
        list($title, $filename, $type, $expected) = $row;
        $actual = slm_portfolio_classify_media($title, $filename, $type);
        assert(
            $actual === $expected,
            sprintf('"%s" should classify as %s, got %s', $title, $expected, $actual)
        );
    }

    // The headline bug: a dining room photo used to be filed under Drone.
    assert(
        slm_portfolio_classify_media('Dining Room', '8-Dining-Room-3-of-3.webp', 'image') === 'Real Estate Photography',
        'A dining room photograph is Real Estate Photography, not Drone'
    );
    // Twilight beats Drone — twilight work that happens to be aerial.
    assert(
        slm_portfolio_classify_media('Twilight Aerial Front Exterior', '99-Twilight-Aerial-Front-Exterior-3.webp', 'image') === 'Twilight',
        'Twilight must win over Drone when both markers are present'
    );
    // A filename-only marker still classifies when the title says nothing.
    assert(
        slm_portfolio_classify_media('Untitled Clip', 'vert_vid1.mp4', 'video') === 'Social Media / Reels',
        'A vertical-video filename marker should reach Social Media / Reels'
    );
    // Nothing matches: stills fall back to the primary category.
    assert(
        slm_portfolio_classify_media('Foyer', 'foyer.webp', 'image') === 'Real Estate Photography',
        'An unmatched still falls back to Real Estate Photography'
    );
    // "short sale" is core real-estate vocabulary — it must NOT be read as a
    // short-form video. This is why the marker is 'shorts', not 'short'.
    assert(
        slm_portfolio_classify_media('Short Sale Walkthrough', 'short-sale-walkthrough.mp4', 'video') === 'Cinematic Video',
        'A short sale video is Cinematic Video, not a reel'
    );
    // The real short-form marker still works, from title or filename.
    assert(
        slm_portfolio_classify_media('Properties are experiences #shorts', 'Properties-are-experiences-shorts.mp4', 'video') === 'Social Media / Reels',
        'The #shorts marker must still reach Social Media / Reels'
    );
    echo "PASS: test_portfolio_classifier_matches_live_media\n";
}

/** One category list, read by the filter bar and both portal dropdowns. */
function test_portfolio_categories_have_one_source()
{
    $categories = slm_portfolio_items_categories();
    assert(count($categories) === 6, 'There should be exactly six portfolio categories');
    assert(
        $categories === [
            'Real Estate Photography',
            'Cinematic Video',
            'Drone',
            'Twilight',
            'Social Media / Reels',
            'Business Branding',
        ],
        'Category strings and order are the filter-bar contract — renaming one deletes its items on save'
    );

    $template = slm_test_read('templates/page-portfolio.php');
    assert(
        strpos($template, 'slm_portfolio_items_categories()') !== false
            && strpos($template, 'data-filter="Drone"') === false,
        'The filter bar must render from slm_portfolio_items_categories(), not hardcoded buttons'
    );

    $portal = slm_test_read('templates/admin-portal.php');
    assert(
        substr_count($portal, 'slm_portfolio_items_categories()') >= 3,
        'Both portal dropdowns and the JS category list must read the shared source'
    );
    assert(
        strpos($portal, "'Real Estate Photography'") === false
            && strpos($portal, '"Real Estate Photography"') === false,
        'The portal must not hardcode a category literal as its JS fallback'
    );
    echo "PASS: test_portfolio_categories_have_one_source\n";
}

/**
 * attachment_id is the link back to the media library. It must round-trip, and
 * its absence must never drop an item — saved data predates the field.
 */
function test_portfolio_sanitizer_round_trips_attachment_id()
{
    $items = slm_portfolio_items_sanitize([
        [
            'id' => 1,
            'title' => 'Twilight Aerial Front Exterior',
            'category' => 'Twilight',
            'type' => 'image',
            'image' => 'http://example.com/twilight.webp',
            'attachment_id' => '412',
        ],
        [
            'id' => 2,
            'title' => 'Legacy Entry With No Attachment',
            'category' => 'Real Estate Photography',
            'type' => 'image',
            'image' => 'http://example.com/legacy.webp',
        ],
    ]);

    assert(count($items) === 2, 'An entry without attachment_id must still survive sanitization');
    assert($items[0]['attachment_id'] === 412, 'attachment_id must round-trip as an int');
    assert($items[1]['attachment_id'] === 0, 'A missing attachment_id defaults to 0, not a dropped item');
    echo "PASS: test_portfolio_sanitizer_round_trips_attachment_id\n";
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
