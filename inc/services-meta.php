<?php
if (!defined('ABSPATH')) exit;

/**
 * Shared Edit Page button helper.
 * Outputs a fixed-position admin-only "Edit Page" button.
 * Pass $post_id explicitly, or leave null to use get_the_ID().
 */
function slm_edit_page_button(?int $post_id = null): void {
  if (!current_user_can('edit_pages')) return;
  $pid  = $post_id ?? (int) get_the_ID();
  $link = get_edit_post_link($pid);
  if (!$link) return;
  echo '<a href="' . esc_url($link) . '" class="slm-edit-page-btn" aria-label="Edit this page in WordPress admin">&#9998; Edit Page</a>' . "\n";
}

/**
 * Services Page Meta Boxes
 */
add_action('add_meta_boxes', function () {
  $post_id = (int) ($_GET['post'] ?? $_POST['post_ID'] ?? 0);
  if (!$post_id) return;

  $template = get_post_meta($post_id, '_wp_page_template', true);
  if ($template !== 'templates/page-services.php') return;

  add_meta_box('slm_svc_hero', 'Services — Hero Section', 'slm_render_svc_hero_meta_box', 'page', 'normal', 'high');
  add_meta_box('slm_svc_core', 'Services — Core Service Descriptions (5)', 'slm_render_svc_core_meta_box', 'page', 'normal', 'high');
  add_meta_box('slm_svc_why', 'Services — Why Choose Us', 'slm_render_svc_why_meta_box', 'page', 'normal', 'high');
  add_meta_box('slm_svc_cta', 'Services — Final CTA', 'slm_render_svc_cta_meta_box', 'page', 'normal', 'high');
});

/* ── Render helpers ───────────────────────────────────────────────── */

function slm_svc_text_row(int $post_id, string $key, string $label, bool $textarea = false): void {
  $val = (string) get_post_meta($post_id, $key, true);
  echo '<div style="margin-bottom:14px;">';
  echo '<label for="' . esc_attr($key) . '" style="display:block;font-weight:600;margin-bottom:4px;">' . esc_html($label) . '</label>';
  if ($textarea) {
    echo '<textarea id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" rows="3" style="width:100%;">' . esc_textarea($val) . '</textarea>';
  } else {
    echo '<input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" style="width:100%;" />';
  }
  echo '</div>';
}

function slm_render_svc_hero_meta_box(WP_Post $post): void {
  wp_nonce_field('slm_svc_save', 'slm_svc_nonce');
  slm_svc_text_row($post->ID, 'svc_hero_eyebrow',   'Eyebrow (e.g. "Jacksonville & North Florida")');
  slm_svc_text_row($post->ID, 'svc_hero_headline',  'H1 Headline');
  slm_svc_text_row($post->ID, 'svc_hero_subheadline', 'Intro paragraph', true);
}

function slm_render_svc_core_meta_box(WP_Post $post): void {
  $services = [
    1 => 'Real Estate Photography',
    2 => 'Cinematic Listing Videos & Walkthroughs',
    3 => 'Real Estate Reels & Short-Form Content',
    4 => 'Brand Content for Businesses',
    5 => 'Aerial Photography & Video',
  ];
  echo '<h4 style="margin:16px 0 8px;border-bottom:1px solid #eee;padding-bottom:4px;">Section Settings</h4>';
  slm_svc_text_row($post->ID, 'svc_core_pricing_line', 'Pricing Line (e.g. "Pricing starting as low as $145...")');

  foreach ($services as $n => $default) {
    echo '<h4 style="margin:16px 0 8px;border-bottom:1px solid #eee;padding-bottom:4px;">Service ' . $n . ' — ' . esc_html($default) . '</h4>';
    slm_svc_text_row($post->ID, "svc_service_{$n}_headline", 'Headline');
    slm_svc_text_row($post->ID, "svc_service_{$n}_body",     'Body text', true);
  }
}

function slm_render_svc_why_meta_box(WP_Post $post): void {
  slm_svc_text_row($post->ID, 'svc_why_headline', 'Section Headline');
  slm_svc_text_row($post->ID, 'svc_why_bullet_1', 'Bullet 1');
  slm_svc_text_row($post->ID, 'svc_why_bullet_2', 'Bullet 2');
  slm_svc_text_row($post->ID, 'svc_why_bullet_3', 'Bullet 3');
  slm_svc_text_row($post->ID, 'svc_why_bullet_4', 'Bullet 4');
  slm_svc_text_row($post->ID, 'svc_why_location', 'Footer location line (e.g. "Serving Jacksonville & North Florida")');
}

function slm_render_svc_cta_meta_box(WP_Post $post): void {
  slm_svc_text_row($post->ID, 'svc_cta_headline',    'Headline');
  slm_svc_text_row($post->ID, 'svc_cta_sub',         'Sub-text', true);
  slm_svc_text_row($post->ID, 'svc_cta_btn_primary', 'Primary button label');
  slm_svc_text_row($post->ID, 'svc_cta_btn_call',    'Call button label');
  slm_svc_text_row($post->ID, 'svc_cta_btn_message', 'Message button label');
}

/* ── Save ─────────────────────────────────────────────────────────── */

add_action('save_post', function (int $post_id) {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_page', $post_id)) return;
  $nonce = (string) ($_POST['slm_svc_nonce'] ?? '');
  if (!wp_verify_nonce($nonce, 'slm_svc_save')) return;

  $text_fields = [
    'svc_hero_eyebrow', 'svc_hero_headline',
    'svc_core_pricing_line',
    'svc_why_headline', 'svc_why_bullet_1', 'svc_why_bullet_2',
    'svc_why_bullet_3', 'svc_why_bullet_4', 'svc_why_location',
    'svc_cta_headline', 'svc_cta_btn_primary', 'svc_cta_btn_call', 'svc_cta_btn_message',
  ];
  for ($i = 1; $i <= 5; $i++) {
    $text_fields[] = "svc_service_{$i}_headline";
  }
  $textarea_fields = [
    'svc_hero_subheadline', 'svc_cta_sub',
  ];
  for ($i = 1; $i <= 5; $i++) {
    $textarea_fields[] = "svc_service_{$i}_body";
  }

  foreach ($text_fields as $key) {
    if (isset($_POST[$key])) {
      update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
    }
  }
  foreach ($textarea_fields as $key) {
    if (isset($_POST[$key])) {
      update_post_meta($post_id, $key, sanitize_textarea_field($_POST[$key]));
    }
  }
});
