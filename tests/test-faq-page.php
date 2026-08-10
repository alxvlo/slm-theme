<?php
/**
 * Review item 18 — FAQ page: template, FAQPage schema, auto-creation, links.
 */

require_once __DIR__ . '/helpers.php';

function test_faq_template_exists()
{
    assert(
        file_exists(slm_test_theme_dir() . '/templates/page-faq.php'),
        'templates/page-faq.php is missing'
    );
    echo "PASS: test_faq_template_exists\n";
}

function test_faq_template_emits_faqpage_schema_from_data()
{
    $faq = slm_test_read('templates/page-faq.php');
    assert(
        strpos($faq, 'application/ld+json') !== false,
        'page-faq.php should emit a JSON-LD block'
    );
    assert(
        strpos($faq, "'FAQPage'") !== false,
        'The JSON-LD block should declare @type FAQPage'
    );
    assert(
        strpos($faq, 'wp_json_encode') !== false,
        'Schema must be built with wp_json_encode from the same $faq_groups array — no hand-written JSON'
    );
    echo "PASS: test_faq_template_emits_faqpage_schema_from_data\n";
}

function test_faq_template_uses_canonical_copy()
{
    $faq = slm_test_read('templates/page-faq.php');
    assert(
        strpos($faq, '24–48 hours') !== false,
        'The turnaround answer must use the canonical "24–48 hours" wording'
    );
    assert(
        strpos($faq, 'slm_book_url()') !== false,
        'The booking CTA must resolve through slm_book_url()'
    );
    assert(
        substr_count($faq, '<h1') === 1,
        'Exactly one <h1> on the FAQ page'
    );
    echo "PASS: test_faq_template_uses_canonical_copy\n";
}

function test_faq_page_is_auto_created_with_seo_meta()
{
    $functions = slm_test_read('functions.php');
    assert(
        strpos($functions, "get_transient('slm_faq_page_exists')") !== false,
        'functions.php should auto-create the FAQ page behind the slm_faq_page_exists transient'
    );
    assert(
        strpos($functions, "'post_name' => 'faq'") !== false,
        'The auto-created page slug must be "faq"'
    );
    $hook = substr($functions, strpos($functions, "get_transient('slm_faq_page_exists')"));
    $hook = substr($hook, 0, strpos($hook, 'set_transient'));
    assert(
        substr_count($hook, "'slm_meta_title'") === 2 && substr_count($hook, "'slm_meta_description'") === 2,
        'Both the create and already-exists branches must set slm_meta_title and slm_meta_description'
    );
    echo "PASS: test_faq_page_is_auto_created_with_seo_meta\n";
}

function test_faq_url_helper_exists()
{
    $functions = slm_test_read('functions.php');
    assert(
        strpos($functions, 'function slm_faq_url()') !== false,
        'slm_faq_url() helper is missing from functions.php'
    );
    assert(
        strpos($functions, "slm_page_url_by_template('templates/page-faq.php', '/faq/')") !== false,
        'slm_faq_url() must resolve via slm_page_url_by_template with the /faq/ fallback'
    );
    echo "PASS: test_faq_url_helper_exists\n";
}

function test_footer_and_nav_fallback_link_to_faq()
{
    $footer = slm_test_read('template-parts/site/footer.php');
    assert(
        strpos($footer, 'slm_faq_url()') !== false,
        'The footer Important Links section should link to the FAQ page'
    );
    $functions = slm_test_read('functions.php');
    assert(
        strpos($functions, '\'<li><a href="\' . esc_url(slm_faq_url()) . \'">FAQ</a></li>\'') !== false,
        'slm_primary_nav_fallback() should echo the FAQ link (staging renders the fallback)'
    );
    echo "PASS: test_footer_and_nav_fallback_link_to_faq\n";
}
