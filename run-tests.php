<?php
/**
 * Simple test runner for SLM Theme.
 */

$test_files = [
    'test_slm_admin_portal_url_returns_string',
    'test_slm_admin_portal_url_returns_permalink_when_page_exists',
    'test_slm_admin_portal_url_returns_fallback_when_page_missing',
];

$exit_code = 0;
$base_dir = __DIR__;

foreach ($test_files as $test_name) {
    echo "Running $test_name...\n";
    // We use a wrapper script to run each test in a separate process
    $test_code = "<?php
require_once '$base_dir/tests/test-slm-admin-portal-url.php';
$test_name();
";
    $tmp_file = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tmp_file, $test_code);

    // Use zend.assertions=1 to enable assertions and assert.exception=1 to throw exceptions on failure
    passthru("php -d zend.assertions=1 -d assert.exception=1 " . escapeshellarg($tmp_file), $return_var);
    unlink($tmp_file);

    if ($return_var !== 0) {
        echo "FAILED: $test_name\n";
        $exit_code = 1;
    } else {
        echo "COMPLETED: $test_name\n\n";
    }
}

exit($exit_code);
