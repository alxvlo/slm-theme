<?php
if (!defined('ABSPATH')) exit;

/**
 * Custom Meta Box for the Homepage.
 */

add_action('add_meta_boxes', function () {
  $post_id = $_GET['post'] ?? ($_POST['post_ID'] ?? null);
  if (!$post_id) return;
  
  if ((int) $post_id !== (int) get_option('page_on_front')) return;

  // Add the 9 meta boxes
  add_meta_box('slm_hp_hero_meta', 'Section 1 — Hero', 'slm_render_hp_meta_box', 'page', 'normal', 'high', ['section' => 'hero']);
  add_meta_box('slm_hp_problem_meta', 'Section 2 — Problem', 'slm_render_hp_meta_box', 'page', 'normal', 'high', ['section' => 'problem']);
  add_meta_box('slm_hp_solution_meta', 'Section 3 — Solution', 'slm_render_hp_meta_box', 'page', 'normal', 'high', ['section' => 'solution']);
  add_meta_box('slm_hp_who_meta', 'Section 4 — Who We Help', 'slm_render_hp_meta_box', 'page', 'normal', 'high', ['section' => 'who']);
  add_meta_box('slm_hp_services_meta', 'Section 5 — Services', 'slm_render_hp_meta_box', 'page', 'normal', 'high', ['section' => 'services']);
  add_meta_box('slm_hp_why_meta', 'Section 6 — Why Showcase', 'slm_render_hp_meta_box', 'page', 'normal', 'high', ['section' => 'why']);
  add_meta_box('slm_hp_testimonials_meta', 'Section 7 — Testimonials', 'slm_render_hp_meta_box', 'page', 'normal', 'high', ['section' => 'testimonials']);
  add_meta_box('slm_hp_process_meta', 'Section 8 — How It Works', 'slm_render_hp_meta_box', 'page', 'normal', 'high', ['section' => 'process']);
  add_meta_box('slm_hp_finalcta_meta', 'Section 9 — Final CTA', 'slm_render_hp_meta_box', 'page', 'normal', 'high', ['section' => 'finalcta']);

  // Retain BEFORE/AFTER
  add_meta_box('slm_homepage_before_after', 'Homepage: Before/After Visuals', 'slm_render_homepage_meta_box_before_after', 'page', 'normal', 'high');
});

function slm_hp_get_fields_schema() {
  return [
    'hero' => [
      'hp_hero_headline' => ['label' => 'hp_hero_headline', 'type' => 'text'],
      'hp_hero_subheadline' => ['label' => 'hp_hero_subheadline', 'type' => 'textarea'],
      'hp_hero_cta_primary' => ['label' => 'hp_hero_cta_primary', 'type' => 'text'],
      'hp_hero_cta_secondary' => ['label' => 'hp_hero_cta_secondary', 'type' => 'text'],
      'hp_hero_trust_line' => ['label' => 'hp_hero_trust_line', 'type' => 'text'],
      'hp_hero_badge_1' => ['label' => 'hp_hero_badge_1', 'type' => 'text'],
      'hp_hero_badge_2' => ['label' => 'hp_hero_badge_2', 'type' => 'text'],
      'hp_hero_badge_3' => ['label' => 'hp_hero_badge_3', 'type' => 'text'],
    ],
    'problem' => [
      'hp_problem_headline' => ['label' => 'hp_problem_headline', 'type' => 'text'],
      'hp_problem_intro' => ['label' => 'hp_problem_intro', 'type' => 'text'],
      'hp_problem_bullet_1' => ['label' => 'hp_problem_bullet_1', 'type' => 'text'],
      'hp_problem_bullet_2' => ['label' => 'hp_problem_bullet_2', 'type' => 'text'],
      'hp_problem_bullet_3' => ['label' => 'hp_problem_bullet_3', 'type' => 'text'],
      'hp_problem_bullet_4' => ['label' => 'hp_problem_bullet_4', 'type' => 'text'],
      'hp_problem_closing' => ['label' => 'hp_problem_closing', 'type' => 'text'],
      'hp_problem_cta' => ['label' => 'hp_problem_cta', 'type' => 'text'],
    ],
    'solution' => [
      'hp_solution_headline' => ['label' => 'hp_solution_headline', 'type' => 'text'],
      'hp_solution_body' => ['label' => 'hp_solution_body', 'type' => 'textarea'],
      'hp_solution_point_1' => ['label' => 'hp_solution_point_1', 'type' => 'text'],
      'hp_solution_point_2' => ['label' => 'hp_solution_point_2', 'type' => 'text'],
      'hp_solution_point_3' => ['label' => 'hp_solution_point_3', 'type' => 'text'],
      'hp_solution_point_4' => ['label' => 'hp_solution_point_4', 'type' => 'text'],
      'hp_solution_cta' => ['label' => 'hp_solution_cta', 'type' => 'text'],
    ],
    'who' => [
      'hp_who_headline' => ['label' => 'hp_who_headline', 'type' => 'text'],
      'hp_who_subheadline' => ['label' => 'hp_who_subheadline', 'type' => 'text'],
      'hp_who_agents_title' => ['label' => 'hp_who_agents_title', 'type' => 'text'],
      'hp_who_agents_bullet_1' => ['label' => 'hp_who_agents_bullet_1', 'type' => 'text'],
      'hp_who_agents_bullet_2' => ['label' => 'hp_who_agents_bullet_2', 'type' => 'text'],
      'hp_who_agents_bullet_3' => ['label' => 'hp_who_agents_bullet_3', 'type' => 'text'],
      'hp_who_agents_bullet_4' => ['label' => 'hp_who_agents_bullet_4', 'type' => 'text'],
      'hp_who_agents_cta' => ['label' => 'hp_who_agents_cta', 'type' => 'text'],
      'hp_who_biz_title' => ['label' => 'hp_who_biz_title', 'type' => 'text'],
      'hp_who_biz_bullet_1' => ['label' => 'hp_who_biz_bullet_1', 'type' => 'text'],
      'hp_who_biz_bullet_2' => ['label' => 'hp_who_biz_bullet_2', 'type' => 'text'],
      'hp_who_biz_bullet_3' => ['label' => 'hp_who_biz_bullet_3', 'type' => 'text'],
      'hp_who_biz_bullet_4' => ['label' => 'hp_who_biz_bullet_4', 'type' => 'text'],
      'hp_who_biz_cta' => ['label' => 'hp_who_biz_cta', 'type' => 'text'],
      'hp_who_main_cta' => ['label' => 'hp_who_main_cta', 'type' => 'text'],
    ],
    'services' => [
      'hp_services_headline' => ['label' => 'hp_services_headline', 'type' => 'text'],
      'hp_services_subheadline' => ['label' => 'hp_services_subheadline', 'type' => 'text'],
      'hp_service_1_title' => ['label' => 'hp_service_1_title', 'type' => 'text'],
      'hp_service_1_body' => ['label' => 'hp_service_1_body', 'type' => 'textarea'],
      'hp_service_2_title' => ['label' => 'hp_service_2_title', 'type' => 'text'],
      'hp_service_2_body' => ['label' => 'hp_service_2_body', 'type' => 'textarea'],
      'hp_service_3_title' => ['label' => 'hp_service_3_title', 'type' => 'text'],
      'hp_service_3_body' => ['label' => 'hp_service_3_body', 'type' => 'textarea'],
      'hp_service_4_title' => ['label' => 'hp_service_4_title', 'type' => 'text'],
      'hp_service_4_body' => ['label' => 'hp_service_4_body', 'type' => 'textarea'],
      'hp_service_5_title' => ['label' => 'hp_service_5_title', 'type' => 'text'],
      'hp_service_5_body' => ['label' => 'hp_service_5_body', 'type' => 'textarea'],
      'hp_service_6_title' => ['label' => 'hp_service_6_title', 'type' => 'text'],
      'hp_service_6_body' => ['label' => 'hp_service_6_body', 'type' => 'textarea'],
      'hp_services_pricing_line' => ['label' => 'hp_services_pricing_line', 'type' => 'text'],
    ],
    'why' => [
      'hp_why_headline' => ['label' => 'hp_why_headline', 'type' => 'text'],
      'hp_why_subheadline' => ['label' => 'hp_why_subheadline', 'type' => 'text'],
      'hp_why_1_title' => ['label' => 'hp_why_1_title', 'type' => 'text'],
      'hp_why_1_body' => ['label' => 'hp_why_1_body', 'type' => 'textarea'],
      'hp_why_2_title' => ['label' => 'hp_why_2_title', 'type' => 'text'],
      'hp_why_2_body' => ['label' => 'hp_why_2_body', 'type' => 'textarea'],
      'hp_why_3_title' => ['label' => 'hp_why_3_title', 'type' => 'text'],
      'hp_why_3_body' => ['label' => 'hp_why_3_body', 'type' => 'textarea'],
      'hp_why_4_title' => ['label' => 'hp_why_4_title', 'type' => 'text'],
      'hp_why_4_body' => ['label' => 'hp_why_4_body', 'type' => 'textarea'],
      'hp_why_5_title' => ['label' => 'hp_why_5_title', 'type' => 'text'],
      'hp_why_5_body' => ['label' => 'hp_why_5_body', 'type' => 'textarea'],
      'hp_why_6_title' => ['label' => 'hp_why_6_title', 'type' => 'text'],
      'hp_why_6_body' => ['label' => 'hp_why_6_body', 'type' => 'textarea'],
      'hp_why_cta' => ['label' => 'hp_why_cta', 'type' => 'text'],
    ],
    'testimonials' => [
      'hp_proof_headline' => ['label' => 'hp_proof_headline', 'type' => 'text'],
      'hp_proof_subheadline' => ['label' => 'hp_proof_subheadline', 'type' => 'text'],
      'hp_stat_1_number' => ['label' => 'hp_stat_1_number', 'type' => 'text'],
      'hp_stat_1_label' => ['label' => 'hp_stat_1_label', 'type' => 'text'],
      'hp_stat_2_number' => ['label' => 'hp_stat_2_number', 'type' => 'text'],
      'hp_stat_2_label' => ['label' => 'hp_stat_2_label', 'type' => 'text'],
      'hp_proof_cta' => ['label' => 'hp_proof_cta', 'type' => 'text'],
    ],
    'process' => [
      'hp_process_headline' => ['label' => 'hp_process_headline', 'type' => 'text'],
      'hp_process_subheadline' => ['label' => 'hp_process_subheadline', 'type' => 'text'],
      'hp_step_1_title' => ['label' => 'hp_step_1_title', 'type' => 'text'],
      'hp_step_1_body' => ['label' => 'hp_step_1_body', 'type' => 'textarea'],
      'hp_step_2_title' => ['label' => 'hp_step_2_title', 'type' => 'text'],
      'hp_step_2_body' => ['label' => 'hp_step_2_body', 'type' => 'textarea'],
      'hp_step_3_title' => ['label' => 'hp_step_3_title', 'type' => 'text'],
      'hp_step_3_body' => ['label' => 'hp_step_3_body', 'type' => 'textarea'],
      'hp_step_4_title' => ['label' => 'hp_step_4_title', 'type' => 'text'],
      'hp_step_4_body' => ['label' => 'hp_step_4_body', 'type' => 'textarea'],
      'hp_process_cta' => ['label' => 'hp_process_cta', 'type' => 'text'],
    ],
    'finalcta' => [
      'hp_finalcta_headline' => ['label' => 'hp_finalcta_headline', 'type' => 'text'],
      'hp_finalcta_sub' => ['label' => 'hp_finalcta_sub', 'type' => 'textarea'],
      'hp_finalcta_btn_primary' => ['label' => 'hp_finalcta_btn_primary', 'type' => 'text'],
      'hp_finalcta_btn_call' => ['label' => 'hp_finalcta_btn_call', 'type' => 'text'],
      'hp_finalcta_btn_message' => ['label' => 'hp_finalcta_btn_message', 'type' => 'text'],
    ]
  ];
}

function slm_render_hp_meta_box(WP_Post $post, array $meta): void {
  wp_nonce_field('slm_homepage_text_save', 'slm_homepage_text_nonce');
  $section = $meta['args']['section'];
  $schema = slm_hp_get_fields_schema()[$section] ?? [];

  echo '<div style="display:flex; flex-direction:column; gap:16px;">';
  foreach ($schema as $key => $field) {
    $value = get_post_meta($post->ID, $key, true);
    echo '<div class="slm-meta-field">';
    echo '<strong>' . esc_html($field['label']) . '</strong><br/>';
    if ($field['type'] === 'textarea') {
      echo '<textarea name="' . esc_attr($key) . '" style="width:100%; margin-bottom:12px;" rows="4">' . esc_textarea($value) . '</textarea>';
    } else {
      echo '<input type="text" name="' . esc_attr($key) . '" style="width:100%; margin-bottom:12px;" value="' . esc_attr($value) . '" />';
    }
    echo '</div>';
  }
  echo '</div>';
}

function slm_render_homepage_meta_box_before_after(WP_Post $post): void {
  wp_nonce_field('slm_homepage_meta_save', 'slm_homepage_meta_nonce');

  $before_image = get_post_meta($post->ID, 'before_image', true);
  $after_image = get_post_meta($post->ID, 'after_image', true);
  $before_label = get_post_meta($post->ID, 'before_label', true) ?: 'Before';
  $after_label = get_post_meta($post->ID, 'after_label', true) ?: 'After';

  wp_enqueue_media();
  ?>
  <style>
    .slm-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .slm-meta-field { margin-bottom: 15px; }
    .slm-meta-field label { display: block; font-weight: bold; margin-bottom: 5px; }
    .slm-meta-preview { 
      margin-top: 10px; 
      width: 100%; 
      max-width: 200px; 
      height: 150px; 
      background: #f0f0f0; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      border: 1px solid #ddd;
      background-size: cover;
      background-position: center;
    }
    .slm-meta-preview img { max-width: 100%; max-height: 100%; display: block; }
  </style>

  <div class="slm-meta-grid">
    <div class="slm-meta-field">
      <label for="before_image">Before Photo</label>
      <input type="hidden" name="before_image" id="before_image" value="<?php echo esc_attr($before_image); ?>" />
      <div id="before_image_预览" class="slm-meta-preview">
        <?php if ($before_image): ?>
          <?php echo wp_get_attachment_image($before_image, 'thumbnail'); ?>
        <?php else: ?>
          <span>No image selected</span>
        <?php endif; ?>
      </div>
      <p>
        <button type="button" class="button slm-upload-button" data-input="before_image">Select Image</button>
        <button type="button" class="button slm-remove-button" data-input="before_image">Remove</button>
      </p>
      <label for="before_label">Before Label</label>
      <input type="text" name="before_label" id="before_label" class="regular-text" value="<?php echo esc_attr($before_label); ?>" />
    </div>

    <div class="slm-meta-field">
      <label for="after_image">After Photo</label>
      <input type="hidden" name="after_image" id="after_image" value="<?php echo esc_attr($after_image); ?>" />
      <div id="after_image_预览" class="slm-meta-preview">
        <?php if ($after_image): ?>
          <?php echo wp_get_attachment_image($after_image, 'thumbnail'); ?>
        <?php else: ?>
          <span>No image selected</span>
        <?php endif; ?>
      </div>
      <p>
        <button type="button" class="button slm-upload-button" data-input="after_image">Select Image</button>
        <button type="button" class="button slm-remove-button" data-input="after_image">Remove</button>
      </p>
      <label for="after_label">After Label</label>
      <input type="text" name="after_label" id="after_label" class="regular-text" value="<?php echo esc_attr($after_label); ?>" />
    </div>
  </div>

  <script>
  jQuery(document).ready(function($){
    var frame;
    $('.slm-upload-button').on('click', function(e){
      e.preventDefault();
      var button = $(this);
      var input = $('#' + button.data('input'));
      var preview = $('#' + button.data('input') + '_预览');

      if (frame) { frame.open(); return; }

      frame = wp.media({
        title: 'Select Image',
        button: { text: 'Use this image' },
        multiple: false
      });

      frame.on('select', function() {
        var attachment = frame.state().get('selection').first().toJSON();
        input.val(attachment.id);
        preview.html('<img src="' + attachment.url + '" />');
      });

      frame.open();
    });

    $('.slm-remove-button').on('click', function(e){
      e.preventDefault();
      var button = $(this);
      $('#' + button.data('input')).val('');
      $('#' + button.data('input') + '_预览').html('<span>No image selected</span>');
    });
  });
  </script>
  <?php
}

add_action('save_post', function (int $post_id) {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_page', $post_id)) return;

  // Save BEFORE/AFTER images
  $nonce1 = (string) ($_POST['slm_homepage_meta_nonce'] ?? '');
  if (wp_verify_nonce($nonce1, 'slm_homepage_meta_save')) {
    $fields = ['before_image', 'after_image', 'before_label', 'after_label'];
    foreach ($fields as $field) {
      if (isset($_POST[$field])) {
        update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
      }
    }
  }

  // Save Text Fields
  $nonce2 = (string) ($_POST['slm_homepage_text_nonce'] ?? '');
  if (wp_verify_nonce($nonce2, 'slm_homepage_text_save')) {
    $schema = slm_hp_get_fields_schema();
    foreach ($schema as $section_key => $fields) {
      foreach ($fields as $key => $field) {
        if (isset($_POST[$key])) {
          if ($field['type'] === 'textarea') {
            update_post_meta($post_id, $key, sanitize_textarea_field($_POST[$key]));
          } else {
            update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
          }
        }
      }
    }
  }
});
