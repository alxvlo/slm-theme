<?php
if (!defined('ABSPATH'))
  exit;

/**
 * Portfolio item metadata (title / category / metrics / featured) stored server-side
 * as a JSON blob on the Portfolio page, so admin edits are visible to real visitors.
 *
 * Images/videos are handled separately by inc/portfolio-gallery.php — not touched here.
 */

function slm_portfolio_items_meta_key(): string
{
  return 'slm_portfolio_items_json';
}

function slm_portfolio_items_categories(): array
{
  return [
    'Real Estate Photography',
    'Cinematic Video',
    'Drone',
    'Twilight',
    'Social Media / Reels',
    'Business Branding',
  ];
}

/**
 * Derive a portfolio category from what the asset actually is.
 *
 * Categories used to be assigned by position in the gallery, which meant a
 * dining room photo was filed under Drone and four still photographs sat under
 * Cinematic Video. Deriving from the caption and filename keeps the category
 * honest no matter how the gallery is reordered.
 *
 * First match wins, and the order is deliberate:
 *   - Twilight beats Drone, so "Twilight Aerial Front Exterior" is twilight
 *     work that happens to be aerial, not aerial work shot at dusk.
 *   - Video intent beats Drone, so a drone *video* is still a video.
 *
 * Pure string function: no DB reads, no get_post(), no attachment lookups.
 *
 * @param string $type 'video' for moving media, anything else for stills.
 */
function slm_portfolio_classify_media(string $title, string $filename, string $type): string
{
  $haystack = strtolower($title . ' ' . $filename);

  // Filenames separate words with hyphens/underscores where captions use
  // spaces. Search both forms so "Lot-Lines.webp" matches "lot line" while
  // the raw form still matches filename-only markers like "vert_".
  $haystack .= ' ' . str_replace(['-', '_'], ' ', $haystack);

  if (preg_match('/twilight|dusk|sunset/', $haystack)) {
    return 'Twilight';
  }

  if ($type === 'video') {
    if (preg_match('/reel|shorts|vertical|vert_/', $haystack)) {
      return 'Social Media / Reels';
    }
    if (preg_match('/brand|studio|promo|business/', $haystack)) {
      return 'Business Branding';
    }
    return 'Cinematic Video';
  }

  if (preg_match('/aerial|drone|overview|lot line/', $haystack)) {
    return 'Drone';
  }

  return 'Real Estate Photography';
}

/**
 * The filename an attachment URL was uploaded under, for classification.
 */
function slm_portfolio_media_filename(string $url): string
{
  $path = (string) parse_url($url, PHP_URL_PATH);
  return $path !== '' ? basename($path) : basename($url);
}

/**
 * True when an attachment title is really just the uploaded filename.
 *
 * Deliberately conservative: a false positive silently discards a real title
 * that someone typed, which is worse than letting an odd filename through.
 * Anything containing a space is treated as a human title.
 */
function slm_portfolio_title_looks_like_filename(string $title): bool
{
  $title = trim($title);
  if ($title === '') {
    return true;
  }
  if (preg_match('/\.(webp|jpe?g|png|mp4|mov)$/i', $title)) {
    return true;
  }
  if (strpos($title, ' ') === false && preg_match_all('/[-_]/', $title) >= 2) {
    return true;
  }
  return false;
}

/**
 * Resolve a portfolio card title: caption, then title, then a neutral label.
 *
 * The attachment title is only trusted when it does not look like a raw
 * filename — otherwise cards render as "18-Primary-Bathroom-1-of-3.webp".
 */
function slm_portfolio_resolve_media_title(string $caption, string $title, string $fallback): string
{
  $caption = trim($caption);
  if ($caption !== '') {
    return $caption;
  }

  $title = trim($title);
  if ($title !== '' && !slm_portfolio_title_looks_like_filename($title)) {
    return $title;
  }

  return $fallback;
}

/**
 * Resolve the Portfolio page gallery to a list of image records.
 *
 * Single source for attachment resolution — both the public grid's hero image and
 * slm_portfolio_default_items() call this, so there is one place to fix.
 *
 * @return array<int, array{id:int, full:string, thumb:string, title:string}>
 */
function slm_portfolio_gallery_images(): array
{
  $page_id = slm_portfolio_page_id();
  $images = [];

  $raw_ids = $page_id > 0
    ? (string) get_post_meta($page_id, 'slm_portfolio_gallery_ids', true)
    : '';

  $att_ids = [];
  if ($raw_ids !== '') {
    foreach (preg_split('/[\s,]+/', $raw_ids, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $p) {
      $id = (int) $p;
      if ($id > 0)
        $att_ids[] = $id;
    }
  }

  foreach (array_unique($att_ids) as $att_id) {
    $full = wp_get_attachment_image_url($att_id, 'full');
    $large = wp_get_attachment_image_url($att_id, 'large');
    if ($full) {
      $images[] = [
        'id' => (int) $att_id,
        'full' => $full,
        'thumb' => $large ?: $full,
        'title' => get_the_title($att_id),
        'caption' => (string) wp_get_attachment_caption($att_id),
      ];
    }
  }

  // Fallback: numbered uploads if no WP gallery is set on the page.
  if (empty($images)) {
    $uploads = wp_upload_dir();
    $uploads_base = $uploads['baseurl'];
    for ($i = 1; $i <= 20; $i++) {
      $url = $uploads_base . '/2026/02/' . $i . '.png';
      $images[] = [
        'id' => 0,
        'full' => $url,
        'thumb' => $url,
        'title' => 'Portfolio Image ' . $i,
        'caption' => '',
      ];
    }
  }

  return $images;
}

/**
 * Resolve the Portfolio page video picker to a list of video records.
 *
 * Reads the same slm_portfolio_video_ids meta the WP Admin "Video Portfolio"
 * picker saves. Poster comes from the attachment's featured image when set.
 *
 * @return array<int, array{id:int, url:string, poster:string, title:string}>
 */
function slm_portfolio_gallery_videos(): array
{
  $page_id = slm_portfolio_page_id();
  if ($page_id <= 0)
    return [];

  $raw_ids = (string) get_post_meta($page_id, slm_portfolio_video_meta_key(), true);
  if (trim($raw_ids) === '')
    return [];

  $videos = [];
  foreach (slm_portfolio_sanitize_ids($raw_ids) as $att_id) {
    $url = (string) wp_get_attachment_url($att_id);
    if ($url === '')
      continue;

    $poster = '';
    $poster_id = (int) get_post_thumbnail_id($att_id);
    if ($poster_id > 0) {
      $poster = (string) wp_get_attachment_image_url($poster_id, 'large');
    }

    $videos[] = [
      'id' => (int) $att_id,
      'url' => $url,
      'poster' => $poster,
      'title' => (string) get_the_title($att_id),
    ];
  }

  return $videos;
}

/**
 * The site's default portfolio items, derived from real media-library attachments.
 *
 * This is the ONLY default-items implementation in the theme. Both the public
 * portfolio grid and the portal Portfolio Manager render from it, so the two can
 * never drift apart or ship guessed image paths.
 *
 * Images come first, then videos from the Video Portfolio picker. Both are
 * categorized by slm_portfolio_classify_media() from the asset's own caption
 * and filename, so reordering the gallery can never re-file a dining room
 * under Drone.
 *
 * @return array<int, array<string, mixed>>
 */
function slm_portfolio_default_items(): array
{
  // Metrics ship empty. The previous defaults asserted sale timelines, view
  // counts and engagement figures that were never sourced from a real
  // campaign, and they rendered live on every card. Only figures entered
  // deliberately through the Portfolio Manager should ever appear.

  $items = [];
  foreach (slm_portfolio_gallery_images() as $idx => $img) {
    $title = slm_portfolio_resolve_media_title(
      (string) ($img['caption'] ?? ''),
      (string) ($img['title'] ?? ''),
      'Portfolio Image ' . ($idx + 1)
    );

    $items[] = [
      'id' => $idx + 1,
      'title' => $title,
      'category' => slm_portfolio_classify_media(
        $title,
        slm_portfolio_media_filename((string) ($img['full'] ?? '')),
        'image'
      ),
      'type' => 'image',
      'image' => $img['full'],
      'thumb' => $img['thumb'],
      'attachment_id' => (int) ($img['id'] ?? 0),
      'metrics' => [],
      // The featured item drives the Featured Project section on the portfolio
      // page (first item flagged wins; the page falls back to the first item).
      'featured' => ($idx === 0),
    ];
  }

  $next_id = count($items) + 1;
  foreach (slm_portfolio_gallery_videos() as $v_idx => $video) {
    $title = slm_portfolio_resolve_media_title(
      '',
      (string) ($video['title'] ?? ''),
      'Cinematic Tour ' . ($v_idx + 1)
    );

    $items[] = [
      'id' => $next_id++,
      'title' => $title,
      'category' => slm_portfolio_classify_media(
        $title,
        slm_portfolio_media_filename((string) ($video['url'] ?? '')),
        'video'
      ),
      'type' => 'video',
      'image' => $video['url'],
      'thumb' => $video['poster'],
      'attachment_id' => (int) ($video['id'] ?? 0),
      'metrics' => [],
      'featured' => false,
    ];
  }

  return $items;
}

function slm_portfolio_items_sanitize(array $raw): array
{
  $categories = slm_portfolio_items_categories();
  $clean = [];

  foreach ($raw as $entry) {
    if (!is_array($entry))
      continue;

    $id = isset($entry['id']) ? (int) $entry['id'] : 0;
    $title = sanitize_text_field((string) ($entry['title'] ?? ''));
    $category = sanitize_text_field((string) ($entry['category'] ?? ''));
    $image = esc_url_raw((string) ($entry['image'] ?? ''));
    $thumb = esc_url_raw((string) ($entry['thumb'] ?? ''));

    if ($id <= 0 || $title === '' || $image === '')
      continue;
    if (!in_array($category, $categories, true))
      continue;

    $type = strtolower(sanitize_text_field((string) ($entry['type'] ?? 'image')));
    if (!in_array($type, ['image', 'video'], true))
      $type = 'image';

    // A video's thumb is its optional poster image — never fall back to the
    // video URL itself, which is not a valid <img> source.
    if ($thumb === '' && $type !== 'video')
      $thumb = $image;

    $metrics = [];
    $raw_metrics = $entry['metrics'] ?? [];
    if (is_array($raw_metrics)) {
      foreach ($raw_metrics as $metric) {
        $metric = sanitize_text_field((string) $metric);
        if ($metric === '')
          continue;
        $metrics[] = $metric;
        if (count($metrics) >= 3)
          break;
      }
    }

    $clean[] = [
      'id' => $id,
      'title' => $title,
      'category' => $category,
      'type' => $type,
      'image' => $image,
      'thumb' => $thumb,
      // Optional on purpose. Entries saved before attachment IDs were carried
      // must survive untouched — requiring it would silently drop every one of
      // them on the next save.
      'attachment_id' => isset($entry['attachment_id']) ? (int) $entry['attachment_id'] : 0,
      'metrics' => $metrics,
      'featured' => (bool) filter_var($entry['featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
    ];
  }

  return $clean;
}

function slm_portfolio_get_items(): array
{
  $page_id = slm_portfolio_page_id();
  if ($page_id <= 0)
    return [];

  $json = (string) get_post_meta($page_id, slm_portfolio_items_meta_key(), true);
  if (trim($json) === '')
    return [];

  $decoded = json_decode($json, true);
  if (!is_array($decoded))
    return [];

  return $decoded;
}

function slm_portfolio_save_items(array $items): array
{
  $page_id = slm_portfolio_page_id();
  if ($page_id <= 0)
    return [];

  $clean = slm_portfolio_items_sanitize($items);
  update_post_meta($page_id, slm_portfolio_items_meta_key(), wp_json_encode($clean));

  return $clean;
}

/**
 * Clear saved portfolio item metadata.
 *
 * Deletes the meta key outright rather than overwriting it with a hardcoded list,
 * so slm_portfolio_get_items() returns [] and every caller falls through to
 * slm_portfolio_default_items() — i.e. back to real media-library attachments.
 */
function slm_portfolio_reset_items(): bool
{
  $page_id = slm_portfolio_page_id();
  if ($page_id <= 0)
    return false;

  delete_post_meta($page_id, slm_portfolio_items_meta_key());

  return true;
}

/**
 * AJAX handler: save portfolio item metadata from the portal Portfolio Manager.
 */
add_action('wp_ajax_slm_save_portfolio_items', function () {
  if (!is_user_logged_in()) {
    wp_send_json_error(['message' => 'Not logged in.'], 403);
  }
  if (!current_user_can('edit_pages')) {
    wp_send_json_error(['message' => 'You do not have permission to edit the portfolio.'], 403);
  }
  if (!check_ajax_referer('slm_save_portfolio_items', 'nonce', false)) {
    wp_send_json_error(['message' => 'Session expired. Please refresh the page.'], 403);
  }

  if (slm_portfolio_page_id() <= 0) {
    wp_send_json_error(['message' => 'No page is assigned to the Portfolio template yet.']);
  }

  $raw = (string) wp_unslash($_POST['items'] ?? '');
  $decoded = json_decode($raw, true);
  if (!is_array($decoded)) {
    wp_send_json_error(['message' => 'Could not read the portfolio items sent by the browser.']);
  }

  $saved = slm_portfolio_save_items($decoded);
  wp_send_json_success(['items' => $saved]);
});

/**
 * AJAX handler: clear saved portfolio item metadata, restoring the site defaults.
 */
add_action('wp_ajax_slm_reset_portfolio_items', function () {
  if (!is_user_logged_in()) {
    wp_send_json_error(['message' => 'Not logged in.'], 403);
  }
  if (!current_user_can('edit_pages')) {
    wp_send_json_error(['message' => 'You do not have permission to edit the portfolio.'], 403);
  }
  if (!check_ajax_referer('slm_reset_portfolio_items', 'nonce', false)) {
    wp_send_json_error(['message' => 'Session expired. Please refresh the page.'], 403);
  }

  if (slm_portfolio_page_id() <= 0) {
    wp_send_json_error(['message' => 'No page is assigned to the Portfolio template yet.']);
  }

  slm_portfolio_reset_items();
  wp_send_json_success(['items' => slm_portfolio_default_items()]);
});
