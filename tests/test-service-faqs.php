<?php
/**
 * Review item 20 (FAQ half) — every service detail page carries a per-service
 * FAQ block, and the shared block emits FAQPage JSON-LD from the same data.
 */

require_once __DIR__ . '/helpers.php';

function test_service_detail_block_supports_faqs()
{
    $block = slm_test_read('template-parts/blocks/service-detail.php');
    assert(
        strpos($block, "'faqs' => []") !== false,
        'service-detail.php must declare a faqs arg defaulting to []'
    );
    assert(
        strpos($block, "'FAQPage'") !== false && strpos($block, 'wp_json_encode') !== false,
        'service-detail.php must emit FAQPage JSON-LD built with wp_json_encode'
    );
    assert(
        strpos($block, '<details class="faq-item">') !== false,
        'The FAQ section should reuse the .faq-item accordion pattern from Task 1'
    );
    echo "PASS: test_service_detail_block_supports_faqs\n";
}

function test_every_service_template_passes_faqs()
{
    $missing = [];
    foreach (glob(slm_test_theme_dir() . '/templates/page-service-*.php') as $template) {
        if (strpos((string) file_get_contents($template), "'faqs' =>") === false) {
            $missing[] = basename($template);
        }
    }
    assert(
        $missing === [],
        'Service templates without a faqs array: ' . implode(', ', $missing)
    );
    echo "PASS: test_every_service_template_passes_faqs\n";
}

function test_service_faqs_use_canonical_turnaround()
{
    $offenders = [];
    foreach (glob(slm_test_theme_dir() . '/templates/page-service-*.php') as $template) {
        $body = (string) file_get_contents($template);
        if (strpos($body, '24-48') !== false || strpos($body, '24 hour') !== false) {
            $offenders[] = basename($template);
        }
    }
    assert(
        $offenders === [],
        'Service templates with non-canonical turnaround copy (must be "24–48 hours"): ' . implode(', ', $offenders)
    );
    echo "PASS: test_service_faqs_use_canonical_turnaround\n";
}
