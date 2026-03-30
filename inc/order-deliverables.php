<?php
if (!defined('ABSPATH')) {
  exit;
}

const SLM_ORDER_DELIVERABLES_SCHEMA_VERSION = '1.0.0';
const SLM_ORDER_DELIVERABLES_MAX_IMAGES = 200;
const SLM_ORDER_DELIVERABLES_ACCESS_TTL = 900;

function slm_order_deliverables_table(): string
{
  global $wpdb;
  return $wpdb->prefix . 'slm_order_deliveries';
}

function slm_order_deliverables_schema_install(): void
{
  global $wpdb;
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';

  $table = slm_order_deliverables_table();
  $charset = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE {$table} (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    order_ref varchar(191) NOT NULL,
    order_number varchar(191) NOT NULL DEFAULT '',
    customer_email varchar(191) NOT NULL DEFAULT '',
    status varchar(20) NOT NULL DEFAULT 'draft',
    media_json longtext NULL,
    zip_attachment_id bigint(20) unsigned NULL,
    finished_at_gmt datetime NULL,
    finished_by_user_id bigint(20) unsigned NULL,
    notified_at_gmt datetime NULL,
    notified_by_user_id bigint(20) unsigned NULL,
    created_at_gmt datetime NOT NULL,
    updated_at_gmt datetime NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY order_ref (order_ref),
    KEY status (status),
    KEY customer_email (customer_email),
    KEY updated_at_gmt (updated_at_gmt)
  ) {$charset};";

  dbDelta($sql);
  update_option('slm_order_deliverables_schema_version', SLM_ORDER_DELIVERABLES_SCHEMA_VERSION, false);
}

add_action('after_switch_theme', 'slm_order_deliverables_schema_install');
add_action('init', function (): void {
  if (wp_installing()) {
    return;
  }
  if ((string) get_option('slm_order_deliverables_schema_version', '') !== SLM_ORDER_DELIVERABLES_SCHEMA_VERSION) {
    slm_order_deliverables_schema_install();
  }
}, 6);

function slm_order_deliverables_normalize_order_ref(string $order_ref): string
{
  $order_ref = trim($order_ref);
  if ($order_ref === '') {
    return '';
  }
  if (strlen($order_ref) > 191) {
    $order_ref = substr($order_ref, 0, 191);
  }
  return $order_ref;
}

function slm_order_deliverables_normalize_status(string $status): string
{
  $status = sanitize_key($status);
  if (!in_array($status, ['draft', 'finished'], true)) {
    return 'draft';
  }
  return $status;
}

function slm_order_deliverables_decode_media($value): array
{
  if (is_array($value)) {
    $media = $value;
  } else {
    $raw = is_string($value) ? trim($value) : '';
    $media = $raw !== '' ? json_decode($raw, true) : [];
  }

  if (!is_array($media)) {
    $media = [];
  }

  $images = $media['images'] ?? [];
  if (!is_array($images)) {
    $images = [];
  }

  $normalized = [];
  foreach ($images as $idx => $row) {
    if (!is_array($row)) {
      continue;
    }
    $attachment_id = max(0, (int) ($row['attachment_id'] ?? 0));
    if ($attachment_id <= 0) {
      continue;
    }
    $preview_attachment_id = max(0, (int) ($row['preview_attachment_id'] ?? 0));
    $position = isset($row['position']) ? (int) $row['position'] : (int) $idx;
    $normalized[] = [
      'attachment_id' => $attachment_id,
      'preview_attachment_id' => $preview_attachment_id,
      'position' => $position,
    ];
  }

  usort($normalized, static function (array $a, array $b): int {
    return ((int) ($a['position'] ?? 0)) <=> ((int) ($b['position'] ?? 0));
  });

  $images_out = [];
  foreach ($normalized as $position => $row) {
    $images_out[] = [
      'attachment_id' => (int) $row['attachment_id'],
      'preview_attachment_id' => (int) $row['preview_attachment_id'],
      'position' => (int) $position,
    ];
  }

  return ['images' => $images_out];
}

function slm_order_deliverables_encode_media(array $images): string
{
  $rows = [];
  foreach ($images as $position => $attachment_id) {
    if (is_array($attachment_id)) {
      $full_id = max(0, (int) ($attachment_id['attachment_id'] ?? 0));
      $preview_id = max(0, (int) ($attachment_id['preview_attachment_id'] ?? 0));
      $row_position = isset($attachment_id['position']) ? (int) $attachment_id['position'] : (int) $position;
      if ($full_id <= 0) {
        continue;
      }
      $rows[] = [
        'attachment_id' => $full_id,
        'preview_attachment_id' => $preview_id,
        'position' => $row_position,
      ];
      continue;
    }

    $full_id = max(0, (int) $attachment_id);
    if ($full_id <= 0) {
      continue;
    }
    $rows[] = [
      'attachment_id' => $full_id,
      'preview_attachment_id' => 0,
      'position' => (int) $position,
    ];
  }

  return (string) wp_json_encode(['images' => array_values($rows)]);
}

function slm_order_deliverables_normalize_row(array $row): array
{
  $media = slm_order_deliverables_decode_media($row['media_json'] ?? []);
  return [
    'id' => (int) ($row['id'] ?? 0),
    'order_ref' => slm_order_deliverables_normalize_order_ref((string) ($row['order_ref'] ?? '')),
    'order_number' => trim((string) ($row['order_number'] ?? '')),
    'customer_email' => sanitize_email((string) ($row['customer_email'] ?? '')),
    'status' => slm_order_deliverables_normalize_status((string) ($row['status'] ?? 'draft')),
    'media' => $media,
    'zip_attachment_id' => max(0, (int) ($row['zip_attachment_id'] ?? 0)),
    'finished_at_gmt' => trim((string) ($row['finished_at_gmt'] ?? '')),
    'finished_by_user_id' => max(0, (int) ($row['finished_by_user_id'] ?? 0)),
    'notified_at_gmt' => trim((string) ($row['notified_at_gmt'] ?? '')),
    'notified_by_user_id' => max(0, (int) ($row['notified_by_user_id'] ?? 0)),
    'created_at_gmt' => trim((string) ($row['created_at_gmt'] ?? '')),
    'updated_at_gmt' => trim((string) ($row['updated_at_gmt'] ?? '')),
  ];
}

function slm_order_deliverables_get_delivery(string $order_ref): array
{
  global $wpdb;
  $order_ref = slm_order_deliverables_normalize_order_ref($order_ref);
  if ($order_ref === '') {
    return [];
  }

  $table = slm_order_deliverables_table();
  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE order_ref = %s LIMIT 1", $order_ref), ARRAY_A);
  if (!is_array($row) || $row === []) {
    return [];
  }

  return slm_order_deliverables_normalize_row($row);
}

function slm_order_deliverables_attachment_ids_from_media(array $media): array
{
  $ids = [];
  $images = is_array($media['images'] ?? null) ? (array) $media['images'] : [];
  foreach ($images as $row) {
    if (!is_array($row)) {
      continue;
    }
    $attachment_id = max(0, (int) ($row['attachment_id'] ?? 0));
    if ($attachment_id > 0) {
      $ids[] = $attachment_id;
    }
  }
  return array_values(array_unique($ids));
}

function slm_order_deliverables_preview_ids_from_media(array $media): array
{
  $ids = [];
  $images = is_array($media['images'] ?? null) ? (array) $media['images'] : [];
  foreach ($images as $row) {
    if (!is_array($row)) {
      continue;
    }
    $preview_id = max(0, (int) ($row['preview_attachment_id'] ?? 0));
    if ($preview_id > 0) {
      $ids[] = $preview_id;
    }
  }
  return array_values(array_unique($ids));
}

function slm_order_deliverables_sanitize_image_ids($raw_ids): array
{
  $parts = [];
  if (is_array($raw_ids)) {
    $parts = $raw_ids;
  } else {
    $raw = is_string($raw_ids) ? $raw_ids : '';
    $parts = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
  }

  $ids = [];
  foreach ($parts as $part) {
    $id = (int) $part;
    if ($id > 0) {
      $ids[] = $id;
    }
  }

  $ids = array_values(array_unique($ids));
  if (count($ids) > SLM_ORDER_DELIVERABLES_MAX_IMAGES) {
    $ids = array_slice($ids, 0, SLM_ORDER_DELIVERABLES_MAX_IMAGES);
  }
  return $ids;
}

function slm_order_deliverables_validate_image_ids(array $image_ids)
{
  if ($image_ids === []) {
    return true;
  }

  $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
  foreach ($image_ids as $image_id) {
    $image_id = (int) $image_id;
    if ($image_id <= 0) {
      return new WP_Error('slm_delivery_invalid_image', 'Invalid media selection.');
    }

    $mime = (string) get_post_mime_type($image_id);
    if (!in_array($mime, $allowed_mimes, true)) {
      return new WP_Error('slm_delivery_invalid_mime', 'Only JPG, PNG, and WEBP images are supported.');
    }

    $path = get_attached_file($image_id);
    if (!is_string($path) || $path === '' || !is_file($path)) {
      return new WP_Error('slm_delivery_missing_file', 'One or more selected images are missing files.');
    }
  }

  return true;
}

function slm_order_deliverables_generated_attachment_mark(int $attachment_id, string $order_ref, string $kind): void
{
  if ($attachment_id <= 0) {
    return;
  }
  update_post_meta($attachment_id, '_slm_delivery_generated', '1');
  update_post_meta($attachment_id, '_slm_delivery_order_ref', $order_ref);
  update_post_meta($attachment_id, '_slm_delivery_kind', $kind);
}

function slm_order_deliverables_delete_generated_attachment(int $attachment_id): void
{
  if ($attachment_id <= 0) {
    return;
  }
  if ((string) get_post_meta($attachment_id, '_slm_delivery_generated', true) !== '1') {
    return;
  }
  wp_delete_attachment($attachment_id, true);
}

function slm_order_deliverables_cleanup_generated_assets(array $delivery_row): void
{
  $media = is_array($delivery_row['media'] ?? null) ? (array) $delivery_row['media'] : [];
  $preview_ids = slm_order_deliverables_preview_ids_from_media($media);
  foreach ($preview_ids as $preview_id) {
    slm_order_deliverables_delete_generated_attachment((int) $preview_id);
  }

  $zip_id = max(0, (int) ($delivery_row['zip_attachment_id'] ?? 0));
  if ($zip_id > 0) {
    slm_order_deliverables_delete_generated_attachment($zip_id);
  }
}

function slm_order_deliverables_upsert(array $payload): array
{
  global $wpdb;

  $table = slm_order_deliverables_table();
  $order_ref = slm_order_deliverables_normalize_order_ref((string) ($payload['order_ref'] ?? ''));
  if ($order_ref === '') {
    return [];
  }

  $now = gmdate('Y-m-d H:i:s');
  $existing = slm_order_deliverables_get_delivery($order_ref);

  $row = [
    'order_ref' => $order_ref,
    'order_number' => trim((string) ($payload['order_number'] ?? ($existing['order_number'] ?? ''))),
    'customer_email' => sanitize_email((string) ($payload['customer_email'] ?? ($existing['customer_email'] ?? ''))),
    'status' => slm_order_deliverables_normalize_status((string) ($payload['status'] ?? ($existing['status'] ?? 'draft'))),
    'media_json' => (string) ($payload['media_json'] ?? ($existing !== [] ? wp_json_encode($existing['media']) : wp_json_encode(['images' => []]))),
    'zip_attachment_id' => max(0, (int) ($payload['zip_attachment_id'] ?? ($existing['zip_attachment_id'] ?? 0))),
    'finished_at_gmt' => trim((string) ($payload['finished_at_gmt'] ?? ($existing['finished_at_gmt'] ?? ''))),
    'finished_by_user_id' => max(0, (int) ($payload['finished_by_user_id'] ?? ($existing['finished_by_user_id'] ?? 0))),
    'notified_at_gmt' => trim((string) ($payload['notified_at_gmt'] ?? ($existing['notified_at_gmt'] ?? ''))),
    'notified_by_user_id' => max(0, (int) ($payload['notified_by_user_id'] ?? ($existing['notified_by_user_id'] ?? 0))),
    'updated_at_gmt' => $now,
  ];

  $formats = ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%d', '%s'];

  if ($existing === []) {
    $row['created_at_gmt'] = $now;
    $insert_formats = array_merge($formats, ['%s']);
    $ok = $wpdb->insert($table, $row, $insert_formats);
    if ($ok === false) {
      return [];
    }
    return slm_order_deliverables_get_delivery($order_ref);
  }

  $ok = $wpdb->update(
    $table,
    $row,
    ['order_ref' => $order_ref],
    $formats,
    ['%s']
  );
  if ($ok === false) {
    return [];
  }

  return slm_order_deliverables_get_delivery($order_ref);
}

function slm_order_deliverables_save_draft(string $order_ref, string $order_number, string $customer_email, $image_ids, int $actor_user_id = 0)
{
  $order_ref = slm_order_deliverables_normalize_order_ref($order_ref);
  if ($order_ref === '') {
    return new WP_Error('slm_delivery_missing_order', 'Missing order reference.');
  }

  $images = slm_order_deliverables_sanitize_image_ids($image_ids);
  $valid = slm_order_deliverables_validate_image_ids($images);
  if (is_wp_error($valid)) {
    return $valid;
  }

  $existing = slm_order_deliverables_get_delivery($order_ref);

  $media_json = slm_order_deliverables_encode_media($images);
  $saved = slm_order_deliverables_upsert([
    'order_ref' => $order_ref,
    'order_number' => $order_number,
    'customer_email' => $customer_email,
    'status' => 'draft',
    'media_json' => $media_json,
    'zip_attachment_id' => 0,
    'finished_at_gmt' => '',
    'finished_by_user_id' => 0,
    'notified_at_gmt' => '',
    'notified_by_user_id' => 0,
  ]);

  if ($saved === []) {
    return new WP_Error('slm_delivery_save_failed', 'Unable to save draft delivery.');
  }

  if ($existing !== []) {
    slm_order_deliverables_cleanup_generated_assets($existing);
  }

  return $saved;
}

function slm_order_deliverables_preview_attachment_from_source(int $source_attachment_id, string $order_ref, int $position)
{
  if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
    return new WP_Error('slm_delivery_preview_unsupported', 'Preview generation requires GD image functions.');
  }

  $source_path = get_attached_file($source_attachment_id);
  if (!is_string($source_path) || $source_path === '' || !is_file($source_path)) {
    return new WP_Error('slm_delivery_preview_source_missing', 'Source image file could not be found.');
  }

  $binary = file_get_contents($source_path);
  if (!is_string($binary) || $binary === '') {
    return new WP_Error('slm_delivery_preview_source_read', 'Source image could not be read.');
  }

  $source_img = @imagecreatefromstring($binary);
  if (!$source_img) {
    return new WP_Error('slm_delivery_preview_decode', 'Source image could not be decoded.');
  }

  $source_w = imagesx($source_img);
  $source_h = imagesy($source_img);
  if ($source_w <= 0 || $source_h <= 0) {
    imagedestroy($source_img);
    return new WP_Error('slm_delivery_preview_dimensions', 'Source image has invalid dimensions.');
  }

  $max_dim = 1200;
  $scale = min(1.0, (float) $max_dim / (float) max($source_w, $source_h));
  $target_w = max(240, (int) round($source_w * $scale));
  $target_h = max(180, (int) round($source_h * $scale));

  $preview = imagecreatetruecolor($target_w, $target_h);
  if (!$preview) {
    imagedestroy($source_img);
    return new WP_Error('slm_delivery_preview_alloc', 'Preview image could not be allocated.');
  }

  imagealphablending($preview, true);
  imagesavealpha($preview, true);
  $bg = imagecolorallocate($preview, 245, 245, 245);
  imagefilledrectangle($preview, 0, 0, $target_w, $target_h, $bg);

  imagecopyresampled($preview, $source_img, 0, 0, 0, 0, $target_w, $target_h, $source_w, $source_h);
  imagedestroy($source_img);

  for ($i = 0; $i < 8; $i++) {
    imagefilter($preview, IMG_FILTER_GAUSSIAN_BLUR);
  }
  imagefilter($preview, IMG_FILTER_BRIGHTNESS, -18);
  imagefilter($preview, IMG_FILTER_CONTRAST, 8);

  $overlay = imagecolorallocatealpha($preview, 20, 30, 50, 85);
  imagefilledrectangle($preview, 0, 0, $target_w, $target_h, $overlay);

  $uploads = wp_upload_dir();
  if (!empty($uploads['error'])) {
    imagedestroy($preview);
    return new WP_Error('slm_delivery_upload_path', 'Upload path unavailable for preview generation.');
  }

  $ref = preg_replace('/[^a-zA-Z0-9\-_]+/', '-', strtolower($order_ref));
  $ref = trim((string) $ref, '-');
  if ($ref === '') {
    $ref = 'order';
  }

  $filename = wp_unique_filename($uploads['path'], 'slm-preview-' . $ref . '-' . $source_attachment_id . '-' . $position . '.jpg');
  $target_path = trailingslashit($uploads['path']) . $filename;

  $ok = imagejpeg($preview, $target_path, 62);
  imagedestroy($preview);
  if (!$ok || !is_file($target_path)) {
    return new WP_Error('slm_delivery_preview_write', 'Preview image could not be written.');
  }

  $title = trim((string) get_the_title($source_attachment_id));
  if ($title === '') {
    $title = 'Order Preview';
  }

  $attachment_id = wp_insert_attachment([
    'post_title' => $title . ' (Preview)',
    'post_mime_type' => 'image/jpeg',
    'post_status' => 'inherit',
    'post_content' => '',
  ], $target_path, 0);

  if (!$attachment_id || is_wp_error($attachment_id)) {
    @unlink($target_path);
    return new WP_Error('slm_delivery_preview_attach', 'Preview image could not be attached to media library.');
  }

  require_once ABSPATH . 'wp-admin/includes/image.php';
  $meta = wp_generate_attachment_metadata((int) $attachment_id, $target_path);
  if (is_array($meta)) {
    wp_update_attachment_metadata((int) $attachment_id, $meta);
  }

  slm_order_deliverables_generated_attachment_mark((int) $attachment_id, $order_ref, 'preview');
  return (int) $attachment_id;
}

function slm_order_deliverables_generate_zip_attachment(array $attachment_ids, string $order_ref)
{
  if ($attachment_ids === []) {
    return new WP_Error('slm_delivery_zip_empty', 'No images available for ZIP.');
  }
  if (!class_exists('ZipArchive')) {
    return new WP_Error('slm_delivery_zip_unavailable', 'ZIP support is unavailable on this server.');
  }

  $uploads = wp_upload_dir();
  if (!empty($uploads['error'])) {
    return new WP_Error('slm_delivery_zip_upload_error', 'Upload directory is unavailable for ZIP generation.');
  }

  $ref = preg_replace('/[^a-zA-Z0-9\-_]+/', '-', strtolower($order_ref));
  $ref = trim((string) $ref, '-');
  if ($ref === '') {
    $ref = 'order';
  }

  $filename = wp_unique_filename($uploads['path'], 'slm-delivery-' . $ref . '-' . gmdate('Ymd-His') . '.zip');
  $zip_path = trailingslashit($uploads['path']) . $filename;

  $zip = new ZipArchive();
  $opened = $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
  if ($opened !== true) {
    return new WP_Error('slm_delivery_zip_open_failed', 'ZIP file could not be created.');
  }

  $name_counter = [];
  foreach ($attachment_ids as $attachment_id) {
    $attachment_id = (int) $attachment_id;
    if ($attachment_id <= 0) {
      continue;
    }

    $path = get_attached_file($attachment_id);
    if (!is_string($path) || $path === '' || !is_file($path)) {
      continue;
    }

    $base = wp_basename($path);
    $safe = preg_replace('/[^a-zA-Z0-9._-]+/', '-', (string) $base);
    if (!is_string($safe) || trim($safe) === '') {
      $safe = 'image-' . $attachment_id . '.jpg';
    }

    if (!isset($name_counter[$safe])) {
      $name_counter[$safe] = 0;
      $entry = $safe;
    } else {
      $name_counter[$safe]++;
      $dot = strrpos($safe, '.');
      if ($dot === false) {
        $entry = $safe . '-' . $name_counter[$safe];
      } else {
        $entry = substr($safe, 0, $dot) . '-' . $name_counter[$safe] . substr($safe, $dot);
      }
    }

    $zip->addFile($path, $entry);
  }

  $zip->close();

  if (!is_file($zip_path)) {
    return new WP_Error('slm_delivery_zip_missing', 'ZIP file was not created.');
  }

  $attachment_id = wp_insert_attachment([
    'post_title' => 'Order Delivery ZIP ' . strtoupper($ref),
    'post_mime_type' => 'application/zip',
    'post_status' => 'inherit',
    'post_content' => '',
  ], $zip_path, 0);

  if (!$attachment_id || is_wp_error($attachment_id)) {
    @unlink($zip_path);
    return new WP_Error('slm_delivery_zip_attach_failed', 'ZIP could not be registered in media library.');
  }

  slm_order_deliverables_generated_attachment_mark((int) $attachment_id, $order_ref, 'zip');
  return (int) $attachment_id;
}

function slm_order_deliverables_publish_delivery(string $order_ref, int $actor_user_id = 0)
{
  $order_ref = slm_order_deliverables_normalize_order_ref($order_ref);
  if ($order_ref === '') {
    return new WP_Error('slm_delivery_missing_order', 'Missing order reference.');
  }

  $existing = slm_order_deliverables_get_delivery($order_ref);
  if ($existing === []) {
    return new WP_Error('slm_delivery_missing_draft', 'No delivery draft exists for this order yet.');
  }

  $source_ids = slm_order_deliverables_attachment_ids_from_media((array) ($existing['media'] ?? []));
  if ($source_ids === []) {
    return new WP_Error('slm_delivery_no_images', 'Select at least one image before publishing.');
  }
  if (count($source_ids) > SLM_ORDER_DELIVERABLES_MAX_IMAGES) {
    return new WP_Error('slm_delivery_too_many_images', 'Too many images selected for a single delivery.');
  }

  $valid = slm_order_deliverables_validate_image_ids($source_ids);
  if (is_wp_error($valid)) {
    return $valid;
  }

  $new_preview_ids = [];
  $preview_rows = [];
  foreach ($source_ids as $position => $source_id) {
    $preview_id = slm_order_deliverables_preview_attachment_from_source((int) $source_id, $order_ref, (int) $position);
    if (is_wp_error($preview_id)) {
      foreach ($new_preview_ids as $cleanup_id) {
        slm_order_deliverables_delete_generated_attachment((int) $cleanup_id);
      }
      return $preview_id;
    }

    $new_preview_ids[] = (int) $preview_id;
    $preview_rows[] = [
      'attachment_id' => (int) $source_id,
      'preview_attachment_id' => (int) $preview_id,
      'position' => (int) $position,
    ];
  }

  $zip_attachment_id = slm_order_deliverables_generate_zip_attachment($source_ids, $order_ref);
  if (is_wp_error($zip_attachment_id)) {
    foreach ($new_preview_ids as $cleanup_id) {
      slm_order_deliverables_delete_generated_attachment((int) $cleanup_id);
    }
    return $zip_attachment_id;
  }

  $old_previews = slm_order_deliverables_preview_ids_from_media((array) ($existing['media'] ?? []));
  $old_zip_id = max(0, (int) ($existing['zip_attachment_id'] ?? 0));

  $saved = slm_order_deliverables_upsert([
    'order_ref' => $order_ref,
    'order_number' => (string) ($existing['order_number'] ?? ''),
    'customer_email' => (string) ($existing['customer_email'] ?? ''),
    'status' => 'finished',
    'media_json' => slm_order_deliverables_encode_media($preview_rows),
    'zip_attachment_id' => (int) $zip_attachment_id,
    'finished_at_gmt' => gmdate('Y-m-d H:i:s'),
    'finished_by_user_id' => max(0, $actor_user_id),
  ]);

  if ($saved === []) {
    foreach ($new_preview_ids as $cleanup_id) {
      slm_order_deliverables_delete_generated_attachment((int) $cleanup_id);
    }
    slm_order_deliverables_delete_generated_attachment((int) $zip_attachment_id);
    return new WP_Error('slm_delivery_publish_failed', 'Unable to finalize delivery publish state.');
  }

  foreach ($old_previews as $old_preview_id) {
    slm_order_deliverables_delete_generated_attachment((int) $old_preview_id);
  }
  if ($old_zip_id > 0) {
    slm_order_deliverables_delete_generated_attachment($old_zip_id);
  }

  return $saved;
}

function slm_order_deliverables_build_order_media_url(string $order_ref): string
{
  $order_ref = slm_order_deliverables_normalize_order_ref($order_ref);
  if ($order_ref === '') {
    return '';
  }
  if (!function_exists('slm_portal_url')) {
    return '';
  }
  return add_query_arg([
    'view' => 'order-media',
    'order_id' => $order_ref,
  ], slm_portal_url());
}

function slm_order_deliverables_delivery_access_state(array $normalized_order, array $delivery_row): array
{
  $status = slm_order_deliverables_normalize_status((string) ($delivery_row['status'] ?? 'draft'));
  $media = is_array($delivery_row['media'] ?? null) ? (array) $delivery_row['media'] : ['images' => []];
  $has_media = count(slm_order_deliverables_attachment_ids_from_media($media)) > 0;
  $finished = $status === 'finished' && $has_media;
  $zip_ready = $finished && max(0, (int) ($delivery_row['zip_attachment_id'] ?? 0)) > 0;

  $due_amount = (float) ($normalized_order['due_amount'] ?? 0);
  $is_paid = $due_amount <= 0.009;
  $unlocked = $finished && $is_paid;

  $state = 'in-production';
  if (!$has_media) {
    $state = 'not-started';
  } elseif ($finished && !$is_paid) {
    $state = 'finished-locked';
  } elseif ($finished && $is_paid) {
    $state = 'finished-unlocked';
  }

  return [
    'wp_delivery_status' => $status,
    'wp_delivery_has_media' => $has_media,
    'wp_delivery_finished' => $finished,
    'wp_delivery_zip_ready' => $zip_ready,
    'wp_delivery_unlocked' => $unlocked,
    'wp_delivery_state' => $state,
    'wp_delivery_paid' => $is_paid,
  ];
}

function slm_order_deliverables_apply_to_order(array $normalized_order): array
{
  $order_ref = slm_order_deliverables_normalize_order_ref((string) ($normalized_order['raw_id'] ?? $normalized_order['id'] ?? ''));
  if ($order_ref === '') {
    return array_merge($normalized_order, [
      'wp_delivery_status' => 'draft',
      'wp_delivery_has_media' => false,
      'wp_delivery_finished' => false,
      'wp_delivery_zip_ready' => false,
      'wp_delivery_unlocked' => false,
      'wp_delivery_state' => 'not-started',
      'wp_delivery_row' => [],
      'wp_delivery_order_url' => '',
    ]);
  }

  $delivery = slm_order_deliverables_get_delivery($order_ref);
  if ($delivery === []) {
    return array_merge($normalized_order, [
      'wp_delivery_status' => 'draft',
      'wp_delivery_has_media' => false,
      'wp_delivery_finished' => false,
      'wp_delivery_zip_ready' => false,
      'wp_delivery_unlocked' => false,
      'wp_delivery_state' => 'not-started',
      'wp_delivery_row' => [],
      'wp_delivery_order_url' => slm_order_deliverables_build_order_media_url($order_ref),
    ]);
  }

  $state = slm_order_deliverables_delivery_access_state($normalized_order, $delivery);
  return array_merge($normalized_order, $state, [
    'wp_delivery_row' => $delivery,
    'wp_delivery_order_url' => slm_order_deliverables_build_order_media_url($order_ref),
  ]);
}

function slm_order_deliverables_prepare_completion_order(array $normalized_order): array
{
  $order_ref = slm_order_deliverables_normalize_order_ref((string) ($normalized_order['raw_id'] ?? $normalized_order['id'] ?? ''));
  if ($order_ref === '') {
    return $normalized_order;
  }

  $delivery = slm_order_deliverables_get_delivery($order_ref);
  if ($delivery === []) {
    return $normalized_order;
  }

  $state = slm_order_deliverables_delivery_access_state($normalized_order, $delivery);
  if (empty($state['wp_delivery_finished'])) {
    return $normalized_order;
  }

  $updated = $normalized_order;
  $order_media_url = slm_order_deliverables_build_order_media_url($order_ref);
  if ($order_media_url !== '') {
    $updated['delivery_url'] = $order_media_url;
  }
  if ((string) ($updated['delivery_at'] ?? '') === '' && (string) ($delivery['finished_at_gmt'] ?? '') !== '') {
    $updated['delivery_at'] = (string) $delivery['finished_at_gmt'];
  }
  return $updated;
}

function slm_order_deliverables_access_key(int $user_id, string $order_ref): string
{
  return 'slm_delivery_access_' . md5($user_id . '|' . $order_ref);
}

function slm_order_deliverables_mark_access_context(array $normalized_order, array $delivery_row, int $user_id): array
{
  $user_id = max(0, $user_id);
  if ($user_id <= 0) {
    return [];
  }

  $order_ref = slm_order_deliverables_normalize_order_ref((string) ($normalized_order['raw_id'] ?? $normalized_order['id'] ?? ''));
  if ($order_ref === '') {
    return [];
  }

  $state = slm_order_deliverables_delivery_access_state($normalized_order, $delivery_row);
  $payload = [
    'user_id' => $user_id,
    'order_ref' => $order_ref,
    'can_preview' => !empty($state['wp_delivery_finished']),
    'unlocked' => !empty($state['wp_delivery_unlocked']),
    'created_at' => time(),
  ];

  set_transient(slm_order_deliverables_access_key($user_id, $order_ref), $payload, SLM_ORDER_DELIVERABLES_ACCESS_TTL);
  return $payload;
}

function slm_order_deliverables_read_access_context(int $user_id, string $order_ref): array
{
  $user_id = max(0, $user_id);
  $order_ref = slm_order_deliverables_normalize_order_ref($order_ref);
  if ($user_id <= 0 || $order_ref === '') {
    return [];
  }

  $value = get_transient(slm_order_deliverables_access_key($user_id, $order_ref));
  if (!is_array($value)) {
    return [];
  }

  if ((int) ($value['user_id'] ?? 0) !== $user_id) {
    return [];
  }
  if ((string) ($value['order_ref'] ?? '') !== $order_ref) {
    return [];
  }

  return $value;
}

function slm_order_deliverables_asset_nonce_action(string $order_ref, int $attachment_id, string $variant): string
{
  return 'slm_delivery_asset|' . $order_ref . '|' . $attachment_id . '|' . $variant;
}

function slm_order_deliverables_zip_nonce_action(string $order_ref): string
{
  return 'slm_delivery_zip|' . $order_ref;
}

function slm_order_deliverables_asset_url(string $order_ref, int $attachment_id, string $variant = 'preview', bool $download = false): string
{
  $order_ref = slm_order_deliverables_normalize_order_ref($order_ref);
  $attachment_id = max(0, $attachment_id);
  $variant = $variant === 'full' ? 'full' : 'preview';
  if ($order_ref === '' || $attachment_id <= 0) {
    return '';
  }

  $url = add_query_arg([
    'action' => 'slm_delivery_asset',
    'order_id' => $order_ref,
    'attachment_id' => (string) $attachment_id,
    'variant' => $variant,
    'dl' => $download ? '1' : '0',
  ], admin_url('admin-post.php'));

  return wp_nonce_url($url, slm_order_deliverables_asset_nonce_action($order_ref, $attachment_id, $variant));
}

function slm_order_deliverables_zip_url(string $order_ref): string
{
  $order_ref = slm_order_deliverables_normalize_order_ref($order_ref);
  if ($order_ref === '') {
    return '';
  }
  $url = add_query_arg([
    'action' => 'slm_delivery_zip',
    'order_id' => $order_ref,
  ], admin_url('admin-post.php'));

  return wp_nonce_url($url, slm_order_deliverables_zip_nonce_action($order_ref));
}

function slm_order_deliverables_gallery_items(array $delivery_row, string $order_ref): array
{
  $items = [];
  $media = is_array($delivery_row['media'] ?? null) ? (array) $delivery_row['media'] : ['images' => []];
  $images = is_array($media['images'] ?? null) ? (array) $media['images'] : [];

  foreach ($images as $row) {
    if (!is_array($row)) {
      continue;
    }

    $attachment_id = max(0, (int) ($row['attachment_id'] ?? 0));
    if ($attachment_id <= 0) {
      continue;
    }

    $preview_id = max(0, (int) ($row['preview_attachment_id'] ?? 0));
    $title = trim((string) get_the_title($attachment_id));
    if ($title === '') {
      $title = 'Delivered Image';
    }

    $preview_url = $preview_id > 0
      ? slm_order_deliverables_asset_url($order_ref, $preview_id, 'preview', false)
      : slm_order_deliverables_asset_url($order_ref, $attachment_id, 'preview', false);

    $items[] = [
      'attachment_id' => $attachment_id,
      'preview_attachment_id' => $preview_id,
      'title' => $title,
      'preview_url' => $preview_url,
      'full_url' => slm_order_deliverables_asset_url($order_ref, $attachment_id, 'full', false),
      'download_url' => slm_order_deliverables_asset_url($order_ref, $attachment_id, 'full', true),
    ];
  }

  return $items;
}

function slm_order_deliverables_stream_attachment(int $attachment_id, bool $download, string $fallback_name = ''): void
{
  $attachment_id = max(0, $attachment_id);
  if ($attachment_id <= 0) {
    wp_die('Invalid file request.', 'File Error', ['response' => 404]);
  }

  $path = get_attached_file($attachment_id);
  if (!is_string($path) || $path === '' || !is_file($path)) {
    wp_die('Requested file not found.', 'File Error', ['response' => 404]);
  }

  $mime = (string) get_post_mime_type($attachment_id);
  if ($mime === '') {
    $mime = 'application/octet-stream';
  }

  $name = wp_basename($path);
  if ($fallback_name !== '') {
    $name = sanitize_file_name($fallback_name);
  }

  nocache_headers();
  $size = @filesize($path);
  header('X-Content-Type-Options: nosniff');
  header('Content-Type: ' . $mime);
  if (is_int($size) && $size >= 0) {
    header('Content-Length: ' . (string) $size);
  }
  header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . str_replace('"', '', $name) . '"');

  $handle = fopen($path, 'rb');
  if ($handle === false) {
    wp_die('Unable to read file.', 'File Error', ['response' => 500]);
  }

  while (!feof($handle)) {
    echo fread($handle, 1024 * 1024);
  }
  fclose($handle);
  exit;
}

function slm_order_deliverables_request_context(string $order_ref, bool $require_unlocked, bool $require_preview)
{
  if (!is_user_logged_in()) {
    return new WP_Error('slm_delivery_login_required', 'You must be logged in to access delivery files.');
  }

  if (current_user_can('manage_options')) {
    return [
      'is_admin' => true,
      'unlocked' => true,
      'can_preview' => true,
    ];
  }

  $user_id = get_current_user_id();
  $context = slm_order_deliverables_read_access_context($user_id, $order_ref);
  if ($context === []) {
    return new WP_Error('slm_delivery_access_context_missing', 'Access session expired. Refresh the order media page and try again.');
  }

  if ($require_preview && empty($context['can_preview'])) {
    return new WP_Error('slm_delivery_preview_forbidden', 'Preview access is not available for this order.');
  }

  if ($require_unlocked && empty($context['unlocked'])) {
    return new WP_Error('slm_delivery_locked', 'Full files are locked until payment is complete.');
  }

  return $context;
}

add_action('admin_post_slm_delivery_asset', function (): void {
  $order_ref = slm_order_deliverables_normalize_order_ref((string) ($_GET['order_id'] ?? ''));
  $attachment_id = max(0, (int) ($_GET['attachment_id'] ?? 0));
  $variant = sanitize_key((string) ($_GET['variant'] ?? 'preview'));
  if (!in_array($variant, ['preview', 'full'], true)) {
    $variant = 'preview';
  }
  $download = (string) ($_GET['dl'] ?? '0') === '1';

  if ($order_ref === '' || $attachment_id <= 0) {
    wp_die('Invalid asset request.', 'Delivery Access', ['response' => 400]);
  }

  $nonce = (string) ($_GET['_wpnonce'] ?? '');
  if ($nonce === '' || !wp_verify_nonce($nonce, slm_order_deliverables_asset_nonce_action($order_ref, $attachment_id, $variant))) {
    wp_die('Invalid or expired download link.', 'Delivery Access', ['response' => 403]);
  }

  $delivery = slm_order_deliverables_get_delivery($order_ref);
  if ($delivery === []) {
    wp_die('Delivery record not found.', 'Delivery Access', ['response' => 404]);
  }

  $state = slm_order_deliverables_delivery_access_state([], $delivery);
  if (empty($state['wp_delivery_finished'])) {
    wp_die('Delivery files are not published yet.', 'Delivery Access', ['response' => 403]);
  }

  $media = is_array($delivery['media'] ?? null) ? (array) $delivery['media'] : ['images' => []];
  $allowed_preview_ids = slm_order_deliverables_preview_ids_from_media($media);
  $allowed_full_ids = slm_order_deliverables_attachment_ids_from_media($media);

  if ($variant === 'preview') {
    if (!in_array($attachment_id, $allowed_preview_ids, true)) {
      wp_die('Preview file does not belong to this order.', 'Delivery Access', ['response' => 403]);
    }
    $ctx = slm_order_deliverables_request_context($order_ref, false, true);
    if (is_wp_error($ctx)) {
      wp_die($ctx->get_error_message(), 'Delivery Access', ['response' => 403]);
    }
    slm_order_deliverables_stream_attachment($attachment_id, false);
  }

  if (!in_array($attachment_id, $allowed_full_ids, true)) {
    wp_die('Requested file does not belong to this order.', 'Delivery Access', ['response' => 403]);
  }

  $ctx = slm_order_deliverables_request_context($order_ref, true, true);
  if (is_wp_error($ctx)) {
    wp_die($ctx->get_error_message(), 'Delivery Access', ['response' => 403]);
  }

  $title = trim((string) get_the_title($attachment_id));
  $ext = pathinfo((string) get_attached_file($attachment_id), PATHINFO_EXTENSION);
  if ($ext === '') {
    $ext = 'jpg';
  }
  $filename = sanitize_file_name(($title !== '' ? $title : 'delivery-image') . '.' . $ext);
  slm_order_deliverables_stream_attachment($attachment_id, $download, $filename);
});

add_action('admin_post_slm_delivery_zip', function (): void {
  $order_ref = slm_order_deliverables_normalize_order_ref((string) ($_GET['order_id'] ?? ''));
  if ($order_ref === '') {
    wp_die('Invalid ZIP request.', 'Delivery Access', ['response' => 400]);
  }

  $nonce = (string) ($_GET['_wpnonce'] ?? '');
  if ($nonce === '' || !wp_verify_nonce($nonce, slm_order_deliverables_zip_nonce_action($order_ref))) {
    wp_die('Invalid or expired ZIP link.', 'Delivery Access', ['response' => 403]);
  }

  $delivery = slm_order_deliverables_get_delivery($order_ref);
  if ($delivery === []) {
    wp_die('Delivery record not found.', 'Delivery Access', ['response' => 404]);
  }

  $zip_attachment_id = max(0, (int) ($delivery['zip_attachment_id'] ?? 0));
  if ($zip_attachment_id <= 0) {
    wp_die('ZIP package is not available yet.', 'Delivery Access', ['response' => 404]);
  }

  $state = slm_order_deliverables_delivery_access_state([], $delivery);
  if (empty($state['wp_delivery_finished'])) {
    wp_die('Delivery package is not published yet.', 'Delivery Access', ['response' => 403]);
  }

  $ctx = slm_order_deliverables_request_context($order_ref, true, true);
  if (is_wp_error($ctx)) {
    wp_die($ctx->get_error_message(), 'Delivery Access', ['response' => 403]);
  }

  $filename = 'order-' . sanitize_file_name($order_ref) . '-delivery.zip';
  slm_order_deliverables_stream_attachment($zip_attachment_id, true, $filename);
});
