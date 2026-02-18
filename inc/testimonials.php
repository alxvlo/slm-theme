<?php
if (!defined('ABSPATH')) exit;

/**
 * Testimonials (CPT + simple meta fields).
 *
 * Add testimonials in WP Admin -> Testimonials.
 * Homepage pulls the latest published testimonials.
 */

function slm_testimonial_meta_key(string $suffix): string {
  return 'slm_testimonial_' . $suffix;
}

add_action('init', function () {
  register_post_type('testimonial', [
    'labels' => [
      'name' => 'Testimonials',
      'singular_name' => 'Testimonial',
      'add_new_item' => 'Add New Testimonial',
      'edit_item' => 'Edit Testimonial',
    ],
    'public' => true,
    'has_archive' => false,
    'rewrite' => ['slug' => 'testimonials'],
    'menu_icon' => 'dashicons-format-quote',
    'supports' => ['title', 'editor', 'thumbnail'],
    'show_in_rest' => true,
  ]);
});

add_action('add_meta_boxes', function () {
  add_meta_box(
    'slm_testimonial_details',
    'Testimonial Details',
    'slm_render_testimonial_meta_box',
    'testimonial',
    'normal',
    'default'
  );
});

function slm_render_testimonial_meta_box(WP_Post $post): void {
  wp_nonce_field('slm_testimonial_meta_save', 'slm_testimonial_meta_nonce');

  $rating_key = slm_testimonial_meta_key('rating');
  $source_key = slm_testimonial_meta_key('source');
  $role_key = slm_testimonial_meta_key('role');
  $location_key = slm_testimonial_meta_key('location');

  $rating = (int) get_post_meta($post->ID, $rating_key, true);
  $source = (string) get_post_meta($post->ID, $source_key, true);
  $role = (string) get_post_meta($post->ID, $role_key, true);
  $location = (string) get_post_meta($post->ID, $location_key, true);

  if ($rating <= 0) $rating = 5;
  if ($source === '') $source = 'Google';

  echo '<table class="form-table" role="presentation">';

  echo '<tr>';
  echo '<th scope="row"><label for="slm_testimonial_rating">Star Rating</label></th>';
  echo '<td>';
  echo '<select name="slm_testimonial_rating" id="slm_testimonial_rating">';
  for ($i = 5; $i >= 1; $i--) {
    $sel = selected($rating, $i, false);
    echo '<option value="' . esc_attr((string) $i) . '"' . $sel . '>' . esc_html((string) $i) . ' stars</option>';
  }
  echo '</select>';
  echo '<p class="description">1 to 5 stars.</p>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<th scope="row"><label for="slm_testimonial_source">Source</label></th>';
  echo '<td>';
  echo '<input type="text" class="regular-text" name="slm_testimonial_source" id="slm_testimonial_source" value="' . esc_attr($source) . '" placeholder="Google" />';
  echo '<p class="description">Where this review came from (ex: Google, Zillow).</p>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<th scope="row"><label for="slm_testimonial_role">Role / Title</label></th>';
  echo '<td>';
  echo '<input type="text" class="regular-text" name="slm_testimonial_role" id="slm_testimonial_role" value="' . esc_attr($role) . '" placeholder="Realtor" />';
  echo '<p class="description">Optional (ex: Agent, Broker, Homeowner).</p>';
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<th scope="row"><label for="slm_testimonial_location">Location</label></th>';
  echo '<td>';
  echo '<input type="text" class="regular-text" name="slm_testimonial_location" id="slm_testimonial_location" value="' . esc_attr($location) . '" placeholder="Jacksonville, FL" />';
  echo '<p class="description">Optional.</p>';
  echo '</td>';
  echo '</tr>';

  echo '</table>';
}

add_action('save_post_testimonial', function (int $post_id) {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  $nonce = (string) ($_POST['slm_testimonial_meta_nonce'] ?? '');
  if (!wp_verify_nonce($nonce, 'slm_testimonial_meta_save')) return;

  $rating = (int) ($_POST['slm_testimonial_rating'] ?? 5);
  $rating = max(1, min(5, $rating));

  $source = sanitize_text_field((string) ($_POST['slm_testimonial_source'] ?? ''));
  $role = sanitize_text_field((string) ($_POST['slm_testimonial_role'] ?? ''));
  $location = sanitize_text_field((string) ($_POST['slm_testimonial_location'] ?? ''));

  update_post_meta($post_id, slm_testimonial_meta_key('rating'), $rating);
  update_post_meta($post_id, slm_testimonial_meta_key('source'), $source);
  update_post_meta($post_id, slm_testimonial_meta_key('role'), $role);
  update_post_meta($post_id, slm_testimonial_meta_key('location'), $location);
});

