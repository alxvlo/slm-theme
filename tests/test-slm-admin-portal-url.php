<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../functions.php';

function test_slm_admin_portal_url_returns_string() {
    WP_Mock::reset();
    $url = slm_admin_portal_url();
    assert(is_string($url), 'slm_admin_portal_url should return a string');
    echo "PASS: test_slm_admin_portal_url_returns_string\n";
}

function test_slm_admin_portal_url_returns_permalink_when_page_exists() {
    WP_Mock::reset();
    $expected_url = 'http://example.com/admin-portal-custom';
    WP_Mock::$get_posts_result = [123];
    WP_Mock::$get_permalink_result = $expected_url;

    $url = slm_admin_portal_url();
    assert($url === $expected_url, "slm_admin_portal_url should return the permalink if page exists. Expected $expected_url, got $url");
    echo "PASS: test_slm_admin_portal_url_returns_permalink_when_page_exists\n";
}

function test_slm_admin_portal_url_returns_fallback_when_page_missing() {
    WP_Mock::reset();
    WP_Mock::$get_posts_result = [];
    WP_Mock::$home_url_result = 'http://example.com';
    $expected_url = 'http://example.com/admin-portal/';

    $url = slm_admin_portal_url();
    assert($url === $expected_url, "slm_admin_portal_url should return fallback URL if page is missing. Expected $expected_url, got $url");
    echo "PASS: test_slm_admin_portal_url_returns_fallback_when_page_missing\n";
}
