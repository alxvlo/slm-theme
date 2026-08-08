<?php
/**
 * Simple test runner for SLM Theme.
 *
 * Discovers every tests/test-*.php file and runs each `test_*` function it
 * declares. Every test function runs in its own process, because theme helpers
 * such as slm_page_url_by_template() memoize into static variables that cannot
 * be reset in-process.
 */

$base_dir = __DIR__;
$test_files = glob($base_dir . '/tests/test-*.php') ?: [];
sort($test_files);

if (empty($test_files)) {
    echo "No test files found in tests/.\n";
    exit(1);
}

$exit_code = 0;

/**
 * Run a snippet of PHP in a fresh process with assertions enabled.
 * zend.assertions=1 enables assert(); assert.exception=1 throws on failure.
 */
function slm_run_php(string $code, bool $capture = false)
{
    $tmp_file = tempnam(sys_get_temp_dir(), 'slmtest') . '.php';
    file_put_contents($tmp_file, $code);
    $command = "php -d zend.assertions=1 -d assert.exception=1 " . escapeshellarg($tmp_file);

    if ($capture) {
        $output = shell_exec($command . ' 2>NUL') ?: shell_exec($command . ' 2>/dev/null');
        unlink($tmp_file);
        return (string) $output;
    }

    passthru($command, $return_var);
    unlink($tmp_file);
    return $return_var;
}

foreach ($test_files as $test_file) {
    echo "== " . basename($test_file) . " ==\n";

    // Phase 1 — discover the test functions the file declares.
    $names = slm_run_php(<<<PHP
<?php
\$before = get_defined_functions()['user'];
require_once '{$test_file}';
\$after = get_defined_functions()['user'];
foreach (array_diff(\$after, \$before) as \$name) {
    if (strpos(\$name, 'test_') === 0) {
        echo \$name . "\\n";
    }
}
PHP, true);

    $tests = array_values(array_filter(array_map('trim', explode("\n", (string) $names))));

    if (empty($tests)) {
        echo "FAILED: no test_* functions declared in " . basename($test_file) . "\n\n";
        $exit_code = 1;
        continue;
    }

    // Phase 2 — each test in its own process.
    foreach ($tests as $test) {
        $return_var = slm_run_php(<<<PHP
<?php
require_once '{$test_file}';
try {
    {$test}();
} catch (Throwable \$e) {
    fwrite(STDERR, "FAIL: {$test} — " . \$e->getMessage() . "\\n");
    exit(1);
}
PHP);

        if ($return_var !== 0) {
            $exit_code = 1;
        }
    }

    echo "\n";
}

exit($exit_code);
