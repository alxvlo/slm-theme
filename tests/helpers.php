<?php
/**
 * Shared helpers for source-text assertions.
 *
 * Several review findings are copy/URL drift that lives in template literals,
 * so the honest check is to read the theme source. These helpers keep that
 * reading consistent across test files.
 */

function slm_test_theme_dir(): string
{
    return dirname(__DIR__);
}

function slm_test_relative(string $absolute): string
{
    return ltrim(str_replace('\\', '/', substr($absolute, strlen(slm_test_theme_dir()))), '/');
}

function slm_test_read(string $relative_path): string
{
    $path = slm_test_theme_dir() . '/' . ltrim($relative_path, '/');
    assert(file_exists($path), "Expected theme file to exist: {$relative_path}");
    return (string) file_get_contents($path);
}

/**
 * Every theme PHP file that renders or resolves front-end output.
 */
function slm_test_theme_php_files(): array
{
    $dir = slm_test_theme_dir();
    $files = array_merge(
        glob($dir . '/*.php') ?: [],
        glob($dir . '/inc/*.php') ?: [],
        glob($dir . '/templates/*.php') ?: [],
        glob($dir . '/template-parts/*.php') ?: [],
        glob($dir . '/template-parts/*/*.php') ?: []
    );

    // The runner quotes some of the strings under audit; exclude it.
    return array_values(array_filter($files, static function ($file) {
        return basename($file) !== 'run-tests.php';
    }));
}

/**
 * Theme files (relative paths) containing a literal needle.
 */
function slm_test_grep_theme(string $needle): array
{
    $hits = [];
    foreach (slm_test_theme_php_files() as $file) {
        if (strpos((string) file_get_contents($file), $needle) !== false) {
            $hits[] = slm_test_relative($file);
        }
    }
    return $hits;
}
