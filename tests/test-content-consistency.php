<?php
/**
 * Guards the canonical copy and URL rules in AGENTS.md ("Content Consistency
 * Rules"), which came out of memory-bank/website-review-2026-08-06.md.
 */

require_once __DIR__ . '/helpers.php';

/** Review item 10 — turnaround time is always "24–48 hours". */
function test_turnaround_copy_has_no_24_hour_variant()
{
    $offenders = slm_test_grep_theme('24-hour standard delivery');
    assert(
        $offenders === [],
        'Non-canonical turnaround copy "24-hour standard delivery" found in: ' . implode(', ', $offenders)
    );
    echo "PASS: test_turnaround_copy_has_no_24_hour_variant\n";
}

function test_hero_trust_badge_uses_canonical_turnaround()
{
    $hero = slm_test_read('template-parts/home/hero-slider.php');
    assert(
        stripos($hero, '24–48 hour') !== false,
        'hero-slider.php should state the canonical "24–48 hour" turnaround'
    );
    echo "PASS: test_hero_trust_badge_uses_canonical_turnaround\n";
}

/** Review item 11 — footer copy must cover agents AND businesses. */
function test_footer_description_covers_businesses()
{
    $footer = slm_test_read('template-parts/site/footer.php');
    assert(
        stripos($footer, 'local businesses') !== false,
        'footer__desc must use the agents-AND-businesses wording (mentions "local businesses")'
    );
    assert(
        strpos($footer, 'agents and broker teams that want stronger listing') === false,
        'The agents-only footer variant should be gone'
    );
    echo "PASS: test_footer_description_covers_businesses\n";
}

/** Review item 15 — the logged-out login link reads "Client Login". */
function test_nav_login_label_is_client_login()
{
    $nav = slm_test_read('template-parts/site/nav.php');
    assert(
        strpos($nav, '>Client Login<') !== false,
        'nav.php should label the logged-out link "Client Login"'
    );
    echo "PASS: test_nav_login_label_is_client_login\n";
}

/** Review item 16 — exactly one <h1> on the homepage; the hero owns it. */
function test_homepage_has_exactly_one_h1()
{
    $home_parts = glob(slm_test_theme_dir() . '/template-parts/home/*.php') ?: [];
    $with_h1 = [];
    foreach ($home_parts as $part) {
        if (strpos((string) file_get_contents($part), '<h1') !== false) {
            $with_h1[] = basename($part);
        }
    }
    assert(
        $with_h1 === ['hero-slider.php'],
        'Only the hero should render an <h1>; found in: ' . implode(', ', $with_h1)
    );
    echo "PASS: test_homepage_has_exactly_one_h1\n";
}

/** Review item 6 — /our-portfolio/ is canonical; resolve it via the helper. */
function test_no_hardcoded_portfolio_url()
{
    $offenders = slm_test_grep_theme("home_url('/portfolio/')");
    assert(
        $offenders === [],
        'Hardcoded home_url(\'/portfolio/\') found in: ' . implode(', ', $offenders)
            . ' — use slm_page_url_by_template() instead'
    );
    echo "PASS: test_no_hardcoded_portfolio_url\n";
}

/** Review item 14 — the 13 add-ons must be bookable, not plain text. */
function test_addons_render_as_links()
{
    $services = slm_test_read('templates/page-services.php');

    $start = strpos($services, '<div class="addon-grid">');
    assert($start !== false, 'Could not locate the add-on grid in page-services.php');
    $end = strpos($services, '</div>', strpos($services, 'endforeach', $start));
    $block = substr($services, $start, $end - $start);

    assert(
        strpos($block, '<a ') !== false && strpos($block, 'href=') !== false,
        'Each add-on card should render as a link to the booking URL'
    );
    assert(
        strpos($block, 'slm_book_url()') !== false || strpos($block, '$addon_url') !== false,
        'Add-on links should resolve through the canonical booking URL (item 2)'
    );
    echo "PASS: test_addons_render_as_links\n";
}

function test_all_thirteen_addons_are_present()
{
    $services = slm_test_read('templates/page-services.php');
    $expected = [
        'Zillow Add-On', 'Spotlight Reel', 'Agent Intro Clip', 'Full Agent Video',
        'AI Twilight Photography', 'In-Person Twilight Photography', 'Dusk Conversions',
        'Drone Add-On', 'Drone Video Add-On', 'Virtual Video', 'Detail Photos',
        'Virtual Staging', 'Heavy Photoshopping',
    ];
    $missing = [];
    foreach ($expected as $name) {
        if (strpos($services, "'name' => '" . $name . "'") === false) {
            $missing[] = $name;
        }
    }
    assert($missing === [], 'Add-ons missing from the services template: ' . implode(', ', $missing));
    echo "PASS: test_all_thirteen_addons_are_present\n";
}

/** Review item 17 — the Contact page carries LocalBusiness structured data. */
function test_contact_page_has_local_business_schema()
{
    $contact = slm_test_read('templates/page-contact.php');

    assert(
        strpos($contact, 'application/ld+json') !== false,
        'page-contact.php should emit a JSON-LD block'
    );
    assert(
        strpos($contact, 'LocalBusiness') !== false,
        'The JSON-LD block should declare @type LocalBusiness'
    );
    foreach (['telephone', 'areaServed', 'url'] as $property) {
        assert(
            strpos($contact, $property) !== false,
            "LocalBusiness schema is missing the {$property} property"
        );
    }
    echo "PASS: test_contact_page_has_local_business_schema\n";
}

function test_portfolio_helper_fallback_is_our_portfolio()
{
    $offenders = [];
    foreach (slm_test_theme_php_files() as $file) {
        $body = (string) file_get_contents($file);
        if (preg_match_all("/slm_page_url_by_template\(\s*'templates\/page-portfolio\.php'\s*,\s*'([^']+)'/", $body, $m)) {
            foreach ($m[1] as $fallback) {
                if ($fallback !== '/our-portfolio/') {
                    $offenders[] = slm_test_relative($file) . " => {$fallback}";
                }
            }
        }
    }
    assert(
        $offenders === [],
        'Portfolio fallback path must be /our-portfolio/; found: ' . implode(', ', $offenders)
    );
    echo "PASS: test_portfolio_helper_fallback_is_our_portfolio\n";
}
