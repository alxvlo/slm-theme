<?php
if (!defined('ABSPATH')) exit;

/**
 * Footer settings in Customizer.
 */

function slm_footer_setting(string $key, $default = '') {
  $val = get_theme_mod($key, $default);
  return is_string($val) ? trim($val) : $default;
}

add_action('customize_register', function (WP_Customize_Manager $wp_customize) {
  $wp_customize->add_section('slm_footer', [
    'title' => __('Footer', 'slm'),
    'priority' => 160,
  ]);

  $wp_customize->add_setting('slm_footer_email', [
    'default' => 'Showcaselistingsmedia@gmail.com',
    'sanitize_callback' => 'sanitize_email',
  ]);
  $wp_customize->add_control('slm_footer_email', [
    'section' => 'slm_footer',
    'label' => __('Email', 'slm'),
    'type' => 'text',
  ]);

  $wp_customize->add_setting('slm_footer_phone', [
    'default' => '(904)-294-5809',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('slm_footer_phone', [
    'section' => 'slm_footer',
    'label' => __('Phone', 'slm'),
    'type' => 'text',
  ]);

  $wp_customize->add_setting('slm_footer_address_line1', [
    'default' => '1230 Glengarry Rd.',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('slm_footer_address_line1', [
    'section' => 'slm_footer',
    'label' => __('Address Line 1', 'slm'),
    'type' => 'text',
  ]);

  $wp_customize->add_setting('slm_footer_address_line2', [
    'default' => 'Jacksonville, FL. 32207',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $wp_customize->add_control('slm_footer_address_line2', [
    'section' => 'slm_footer',
    'label' => __('Address Line 2', 'slm'),
    'type' => 'text',
  ]);

  $social = [
    'slm_social_youtube' => [
      'label' => 'YouTube URL',
      'default' => 'https://www.youtube.com/@TheShowcaselistingsmedia',
    ],
    'slm_social_facebook' => [
      'label' => 'Facebook URL',
      'default' => 'https://www.facebook.com/profile.php?id=61578356661096',
    ],
    'slm_social_instagram' => [
      'label' => 'Instagram URL',
      'default' => 'https://www.instagram.com/brittneyshowcaselistingsmedia/',
    ],
    'slm_social_threads' => [
      'label' => 'Threads URL',
      'default' => 'https://www.threads.com/@brittneyshowcaselistingsmedia',
    ],
    'slm_social_linkedin' => [
      'label' => 'LinkedIn URL',
      'default' => '',
    ],
  ];

  foreach ($social as $key => $config) {
    $wp_customize->add_setting($key, [
      'default' => $config['default'],
      'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control($key, [
      'section' => 'slm_footer',
      'label' => __($config['label'], 'slm'),
      'type' => 'url',
    ]);
  }
});
