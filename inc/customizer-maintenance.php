<?php
if (!defined('ABSPATH')) exit;

/**
 * Customizer panel for Site Status / Maintenance Mode.
 *
 * Stored as a WP option (not a theme mod) so it persists across theme changes
 * and is reachable from non-customizer code via get_option('slm_maintenance_mode').
 */

function slm_maintenance_sanitize_checkbox($value): int
{
  return (!empty($value) && $value !== '0' && $value !== 'false') ? 1 : 0;
}

add_action('customize_register', function (WP_Customize_Manager $wp_customize): void {
  $wp_customize->add_section('slm_site_status', [
    'title' => __('Site Status', 'slm'),
    'priority' => 5,
    'description' => __('Toggle a branded coming-soon page for all non-admin visitors.', 'slm'),
  ]);

  $wp_customize->add_setting('slm_maintenance_mode', [
    'type' => 'option',
    'default' => 0,
    'capability' => 'manage_options',
    'sanitize_callback' => 'slm_maintenance_sanitize_checkbox',
    'transport' => 'refresh',
  ]);

  $wp_customize->add_control('slm_maintenance_mode', [
    'section' => 'slm_site_status',
    'label' => __('Maintenance Mode', 'slm'),
    'description' => __('When enabled, public visitors see the coming-soon page. Administrators still see the live site.', 'slm'),
    'type' => 'checkbox',
  ]);
});
