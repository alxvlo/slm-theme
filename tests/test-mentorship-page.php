<?php
/**
 * Review item 7 (last unchecked box) — the Mentorship page is auto-created
 * with SEO meta like every other theme-owned page.
 */

require_once __DIR__ . '/helpers.php';

function test_mentorship_page_is_auto_created_with_seo_meta()
{
    $functions = slm_test_read('functions.php');
    assert(
        strpos($functions, "get_transient('slm_mentorship_page_exists')") !== false,
        'functions.php should auto-create the Mentorship page behind the slm_mentorship_page_exists transient'
    );
    assert(
        strpos($functions, "'post_name' => 'social-mentorship-program'") !== false,
        'The auto-created page slug must match the slm_page_url_by_template fallback (/social-mentorship-program/)'
    );
    $hook = substr($functions, strpos($functions, "get_transient('slm_mentorship_page_exists')"));
    $hook = substr($hook, 0, strpos($hook, 'set_transient'));
    assert(
        substr_count($hook, "'slm_meta_title'") === 2 && substr_count($hook, "'slm_meta_description'") === 2,
        'Both branches must set slm_meta_title and slm_meta_description'
    );
    assert(
        strpos($hook, "slm_page_by_template('templates/page-social-mentorship-program.php')") !== false,
        'The lookup must fall back to template assignment — the live "Mentorship Program" page '
            . 'uses this template under a different slug, and slug/title-only matching duplicated it'
    );
    echo "PASS: test_mentorship_page_is_auto_created_with_seo_meta\n";
}
