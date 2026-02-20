<?php
if (!defined('ABSPATH'))
  exit;

/**
 * Portfolio gallery meta (multiple images for a slider on single portfolio pages).
 */

function slm_portfolio_gallery_meta_key(): string
{
  return 'slm_portfolio_gallery_ids';
}

function slm_portfolio_sanitize_ids($raw): array
{
  if (is_array($raw)) {
    $parts = $raw;
  }
  else {
    $raw = (string)$raw;
    $parts = preg_split('/[\\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
  }

  $ids = [];
  foreach ($parts as $p) {
    $id = (int)$p;
    if ($id > 0)
      $ids[] = $id;
  }
  $ids = array_values(array_unique($ids));
  return $ids;
}

add_action('add_meta_boxes', function () {
  $post_types = ['portfolio', 'page'];
  foreach ($post_types as $pt) {
    add_meta_box(
      'slm_portfolio_gallery',
      'Portfolio Gallery Images',
      'slm_render_portfolio_gallery_meta_box',
      $pt,
      'normal',
      'high'
    );
  }
});

function slm_render_portfolio_gallery_meta_box(WP_Post $post): void
{
  wp_nonce_field('slm_portfolio_gallery_save', 'slm_portfolio_gallery_nonce');

  $ids = slm_portfolio_sanitize_ids(get_post_meta($post->ID, slm_portfolio_gallery_meta_key(), true));
  $ids_str = implode(',', $ids);

  echo '<p class="description">Pick multiple images to show as a slider (main image + thumbnails) on the single portfolio page.</p>';
  echo '<input type="hidden" id="slm-portfolio-gallery-ids" name="slm_portfolio_gallery_ids" value="' . esc_attr($ids_str) . '" />';

  echo '<div id="slm-portfolio-gallery" style="margin-top:10px;">';
  echo '  <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">';
  echo '    <button type="button" class="button button-primary" id="slm-portfolio-gallery-add">Add / Edit Images</button>';
  echo '    <button type="button" class="button" id="slm-portfolio-gallery-clear">Clear</button>';
  echo '    <span class="description">Drag thumbnails to reorder.</span>';
  echo '  </div>';

  echo '  <ul id="slm-portfolio-gallery-list" style="margin:14px 0 0; display:flex; flex-wrap:wrap; gap:10px; padding:0;">';
  foreach ($ids as $id) {
    $thumb = wp_get_attachment_image($id, 'thumbnail', false, [
      'style' => 'width:84px; height:84px; object-fit:cover; display:block; border-radius:8px;',
    ]);
    if (!$thumb)
      continue;
    echo '    <li class="slm-portfolio-thumb" data-id="' . esc_attr((string)$id) . '" style="list-style:none; position:relative; width:84px;">';
    echo '      <span style="cursor:move; display:block; border:1px solid rgba(0,0,0,.12); border-radius:10px; padding:4px; background:#fff;">' . $thumb . '</span>';
    echo '      <button type="button" class="button-link-delete slm-portfolio-thumb-remove" style="position:absolute; top:-6px; right:-4px; background:#fff; border:1px solid rgba(0,0,0,.18); border-radius:999px; width:22px; height:22px; line-height:20px; text-align:center; text-decoration:none;">&times;</button>';
    echo '    </li>';
  }
  echo '  </ul>';
  echo '</div>';
}

add_action('save_post', function (int $post_id) {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    return;
  if (!current_user_can('edit_post', $post_id))
    return;

  $nonce = (string)($_POST['slm_portfolio_gallery_nonce'] ?? '');
  if (!wp_verify_nonce($nonce, 'slm_portfolio_gallery_save'))
    return;

  $ids = slm_portfolio_sanitize_ids($_POST['slm_portfolio_gallery_ids'] ?? '');
  update_post_meta($post_id, slm_portfolio_gallery_meta_key(), implode(',', $ids));
});

add_action('admin_enqueue_scripts', function (string $hook) {
  if ($hook !== 'post.php' && $hook !== 'post-new.php')
    return;

  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  $bType = $screen ? ($screen->post_type ?? '') : '';
  if ($bType !== 'portfolio' && $bType !== 'page')
    return;

  wp_enqueue_media();
  wp_enqueue_script('jquery-ui-sortable');

  $rel = '/assets/js/admin-portfolio-gallery.js';
  $src = get_template_directory_uri() . $rel;
  $ver = function_exists('slm_asset_ver') ? slm_asset_ver($rel) : null;
  wp_enqueue_script('slm-admin-portfolio-gallery', $src, ['jquery', 'jquery-ui-sortable'], $ver, true);
});
