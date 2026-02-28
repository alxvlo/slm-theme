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

function slm_portfolio_video_meta_key(): string
{
  return 'slm_portfolio_video_ids';
}

function slm_portfolio_page_id(): int
{
  $ids = get_posts([
    'post_type' => 'page',
    'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
    'posts_per_page' => 1,
    'fields' => 'ids',
    'no_found_rows' => true,
    'meta_key' => '_wp_page_template',
    'meta_value' => 'templates/page-portfolio.php',
  ]);

  return !empty($ids) ? (int) $ids[0] : 0;
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

function slm_portfolio_is_portfolio_template_page(int $post_id): bool
{
  if ($post_id <= 0 || get_post_type($post_id) !== 'page') {
    return false;
  }
  return (string) get_page_template_slug($post_id) === 'templates/page-portfolio.php';
}

function slm_portfolio_media_thumb_html(int $id, string $type): string
{
  if ($type === 'video') {
    $icon = wp_mime_type_icon($id);
    $title = trim((string) get_the_title($id));
    if ($title === '') {
      $title = 'Video';
    }
    $label = esc_html($title);
    $icon_html = $icon
      ? '<img src="' . esc_url($icon) . '" alt="" style="width:28px; height:28px; display:block; margin:0 auto 6px; opacity:.9;" />'
      : '<span style="display:block; font-size:18px; margin-bottom:4px;">&#9654;</span>';

    return '<div style="width:84px; height:84px; border-radius:8px; background:#12253f; color:#fff; display:flex; align-items:center; justify-content:center; text-align:center; padding:8px; font-size:10px; line-height:1.2;">'
      . '<div>' . $icon_html . '<span style="display:block; max-width:68px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' . $label . '</span></div>'
      . '</div>';
  }

  $thumb = wp_get_attachment_image($id, 'thumbnail', false, [
    'style' => 'width:84px; height:84px; object-fit:cover; display:block; border-radius:8px;',
  ]);
  return $thumb ? $thumb : '';
}

function slm_portfolio_render_media_picker_panel(string $type, array $ids): void
{
  $is_video = $type === 'video';
  $id_prefix = $is_video ? 'slm-portfolio-video' : 'slm-portfolio-gallery';
  $title = $is_video ? 'Video Portfolio' : 'Photo Portfolio';
  $description = $is_video
    ? 'Select videos for the Portfolio page video section. Drag to reorder.'
    : 'Pick multiple images to show as a slider (main image + thumbnails). Drag to reorder.';
  $button_label = $is_video ? 'Add / Edit Videos' : 'Add / Edit Images';
  $ids_str = implode(',', $ids);

  echo '<div style="margin-top:' . ($is_video ? '20px' : '10px') . ';">';
  echo '<h4 style="margin:0 0 6px;">' . esc_html($title) . '</h4>';
  echo '<p class="description" style="margin:0 0 8px;">' . esc_html($description) . '</p>';
  echo '<input type="hidden" id="' . esc_attr($id_prefix . '-ids') . '" name="' . esc_attr($is_video ? 'slm_portfolio_video_ids' : 'slm_portfolio_gallery_ids') . '" value="' . esc_attr($ids_str) . '" />';
  echo '<div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">';
  echo '  <button type="button" class="button button-primary" id="' . esc_attr($id_prefix . '-add') . '" data-media-type="' . esc_attr($is_video ? 'video' : 'image') . '">' . esc_html($button_label) . '</button>';
  echo '  <button type="button" class="button" id="' . esc_attr($id_prefix . '-clear') . '">Clear</button>';
  echo '  <span class="description">Drag thumbnails to reorder.</span>';
  echo '</div>';
  echo '<ul id="' . esc_attr($id_prefix . '-list') . '" style="margin:14px 0 0; display:flex; flex-wrap:wrap; gap:10px; padding:0;">';
  foreach ($ids as $id) {
    $thumb_html = slm_portfolio_media_thumb_html((int) $id, $is_video ? 'video' : 'image');
    if ($thumb_html === '') {
      continue;
    }
    echo '<li class="slm-portfolio-thumb" data-id="' . esc_attr((string) $id) . '" style="list-style:none; position:relative; width:84px;">';
    echo '  <span style="cursor:move; display:block; border:1px solid rgba(0,0,0,.12); border-radius:10px; padding:4px; background:#fff;">' . $thumb_html . '</span>';
    echo '  <button type="button" class="button-link-delete slm-portfolio-thumb-remove" style="position:absolute; top:-6px; right:-4px; background:#fff; border:1px solid rgba(0,0,0,.18); border-radius:999px; width:22px; height:22px; line-height:20px; text-align:center; text-decoration:none;">&times;</button>';
    echo '</li>';
  }
  echo '</ul>';
  echo '</div>';
}

add_action('add_meta_boxes', function () {
  add_meta_box(
    'slm_portfolio_gallery',
    'Portfolio Media',
    'slm_render_portfolio_gallery_meta_box',
    'portfolio',
    'normal',
    'high'
  );
});

add_action('add_meta_boxes_page', function (WP_Post $post): void {
  $template = (string) get_page_template_slug($post->ID);
  if ($template !== 'templates/page-portfolio.php') {
    return;
  }

  add_meta_box(
    'slm_portfolio_gallery',
    'Portfolio Media',
    'slm_render_portfolio_gallery_meta_box',
    'page',
    'normal',
    'high'
  );
});

function slm_render_portfolio_gallery_meta_box(WP_Post $post): void
{
  wp_nonce_field('slm_portfolio_gallery_save', 'slm_portfolio_gallery_nonce');

  $image_ids = slm_portfolio_sanitize_ids(get_post_meta($post->ID, slm_portfolio_gallery_meta_key(), true));
  slm_portfolio_render_media_picker_panel('image', $image_ids);

  if (slm_portfolio_is_portfolio_template_page((int) $post->ID)) {
    $video_ids = slm_portfolio_sanitize_ids(get_post_meta($post->ID, slm_portfolio_video_meta_key(), true));
    slm_portfolio_render_media_picker_panel('video', $video_ids);
  }
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

  if (slm_portfolio_is_portfolio_template_page($post_id)) {
    $video_ids = slm_portfolio_sanitize_ids($_POST['slm_portfolio_video_ids'] ?? '');
    update_post_meta($post_id, slm_portfolio_video_meta_key(), implode(',', $video_ids));
  }
});

add_action('admin_menu', function () {
  add_theme_page(
    'Portfolio Gallery',
    'Portfolio Gallery',
    'edit_pages',
    'slm-portfolio-gallery',
    'slm_render_portfolio_gallery_admin_page'
  );
});

add_action('admin_post_slm_save_portfolio_page_gallery', function () {
  if (!current_user_can('edit_pages')) {
    wp_die('You do not have permission to do that.');
  }

  check_admin_referer('slm_portfolio_gallery_save_admin', 'slm_portfolio_gallery_nonce');

  $portfolio_page_id = slm_portfolio_page_id();
  if ($portfolio_page_id <= 0) {
    wp_safe_redirect(add_query_arg('slm_gallery_notice', 'missing-page', admin_url('themes.php?page=slm-portfolio-gallery')));
    exit;
  }

  $ids = slm_portfolio_sanitize_ids($_POST['slm_portfolio_gallery_ids'] ?? '');
  update_post_meta($portfolio_page_id, slm_portfolio_gallery_meta_key(), implode(',', $ids));
  $video_ids = slm_portfolio_sanitize_ids($_POST['slm_portfolio_video_ids'] ?? '');
  update_post_meta($portfolio_page_id, slm_portfolio_video_meta_key(), implode(',', $video_ids));

  wp_safe_redirect(add_query_arg('slm_gallery_notice', 'saved', admin_url('themes.php?page=slm-portfolio-gallery')));
  exit;
});

function slm_render_portfolio_gallery_admin_page(): void
{
  if (!current_user_can('edit_pages')) {
    wp_die('You do not have permission to view this page.');
  }

  $portfolio_page_id = slm_portfolio_page_id();
  $notice = isset($_GET['slm_gallery_notice']) ? sanitize_key((string) $_GET['slm_gallery_notice']) : '';

  echo '<div class="wrap">';
  echo '<h1>Portfolio Page Gallery</h1>';

  if ($notice === 'saved') {
    echo '<div class="notice notice-success is-dismissible"><p>Portfolio gallery updated.</p></div>';
  }

  if ($portfolio_page_id <= 0) {
    echo '<div class="notice notice-warning"><p>No page is assigned to the <strong>Portfolio</strong> template yet. Create or edit a page and set template to <strong>Portfolio</strong>, then return here.</p></div>';
    echo '</div>';
    return;
  }

  $ids = slm_portfolio_sanitize_ids(get_post_meta($portfolio_page_id, slm_portfolio_gallery_meta_key(), true));
  $video_ids = slm_portfolio_sanitize_ids(get_post_meta($portfolio_page_id, slm_portfolio_video_meta_key(), true));

  echo '<p>Manage the photo and video media shown on your public Portfolio page.</p>';
  echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
  echo '<input type="hidden" name="action" value="slm_save_portfolio_page_gallery" />';
  wp_nonce_field('slm_portfolio_gallery_save_admin', 'slm_portfolio_gallery_nonce');

  slm_portfolio_render_media_picker_panel('image', $ids);
  slm_portfolio_render_media_picker_panel('video', $video_ids);
  submit_button('Save Portfolio Gallery');
  echo '</form>';
  echo '</div>';
}

add_action('admin_enqueue_scripts', function (string $hook) {
  if ($hook !== 'post.php' && $hook !== 'post-new.php' && $hook !== 'appearance_page_slm-portfolio-gallery')
    return;

  if ($hook === 'post.php' || $hook === 'post-new.php') {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $bType = $screen ? ($screen->post_type ?? '') : '';
    if ($bType !== 'portfolio' && $bType !== 'page') {
      return;
    }

    if ($bType === 'page') {
      $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
      if ($post_id <= 0 && isset($_POST['post_ID'])) {
        $post_id = (int) $_POST['post_ID'];
      }

      if ($post_id > 0) {
        $template = (string) get_page_template_slug($post_id);
        if ($template !== 'templates/page-portfolio.php') {
          return;
        }
      }
    }
  }

  if ($hook === 'post-new.php') {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $bType = $screen ? ($screen->post_type ?? '') : '';
    if ($bType === 'page') {
      return;
    }
  }

  if ($hook === 'post.php' || $hook === 'post-new.php' || $hook === 'appearance_page_slm-portfolio-gallery') {
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');

    $rel = '/assets/js/admin-portfolio-gallery.js';
    $src = get_template_directory_uri() . $rel;
    $ver = function_exists('slm_asset_ver') ? slm_asset_ver($rel) : null;
    wp_enqueue_script('slm-admin-portfolio-gallery', $src, ['jquery', 'jquery-ui-sortable'], $ver, true);
  }
});
