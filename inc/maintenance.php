<?php
if (!defined('ABSPATH')) exit;

/**
 * Soft maintenance mode. When enabled, replaces public output with a
 * branded coming-soon page. Admins (manage_options) are bypassed.
 */

function slm_maintenance_is_enabled(): bool
{
  return (bool) get_option('slm_maintenance_mode', false);
}

function slm_maintenance_should_bypass(): bool
{
  if (is_user_logged_in() && current_user_can('manage_options')) {
    return true;
  }
  return false;
}

add_action('template_redirect', function (): void {
  global $pagenow;

  if (is_admin()) return;
  if (wp_doing_ajax()) return;
  if (wp_doing_cron()) return;
  if (defined('REST_REQUEST') && REST_REQUEST) return;
  if (defined('WP_CLI') && WP_CLI) return;
  if (isset($pagenow) && $pagenow === 'wp-login.php') return;

  $script = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '';
  if ($script !== '' && (
    strpos($script, '/wp-login.php') !== false
    || strpos($script, '/wp-admin/') !== false
    || strpos($script, '/wp-cron.php') !== false
  )) {
    return;
  }

  if (!slm_maintenance_is_enabled()) return;
  if (slm_maintenance_should_bypass()) return;

  status_header(200);
  nocache_headers();

  get_template_part('templates/page-maintenance');
  exit;
}, 1);
