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
    'Social Media / Reels',
    'Business Branding',
  ];
}

/**
 * Resolve the Portfolio page gallery to a list of image records.
 *
 * Single source for attachment resolution — both the public grid's hero image and
 * slm_portfolio_default_items() call this, so there is one place to fix.
 *
 * @return array<int, array{full:string, thumb:string, title:string}>
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
        'full' => $full,
        'thumb' => $large ?: $full,
        'title' => get_the_title($att_id),
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
        'full' => $url,
        'thumb' => $url,
        'title' => 'Portfolio Image ' . $i,
      ];
    }
  }

  return $images;
}

/**
 * The site's default portfolio items, derived from real media-library attachments.
 *
 * This is the ONLY default-items implementation in the theme. Both the public
 * portfolio grid and the portal Portfolio Manager render from it, so the two can
 * never drift apart or ship guessed image paths.
 *
 * @return array<int, array<string, mixed>>
 */
function slm_portfolio_default_items(): array
{
  $default_categories = [
    'Real Estate Photography', // 0
    'Real Estate Photography', // 1
    'Real Estate Photography', // 2
    'Real Estate Photography', // 3
    'Real Estate Photography', // 4
    'Real Estate Photography', // 5
    'Drone',                   // 6
    'Drone',                   // 7
    'Drone',                   // 8
    'Drone',                   // 9
    'Cinematic Video',         // 10
    'Cinematic Video',         // 11
    'Cinematic Video',         // 12
    'Cinematic Video',         // 13
    'Social Media / Reels',    // 14
    'Social Media / Reels',    // 15
    'Social Media / Reels',    // 16
    'Business Branding',       // 17
    'Business Branding',       // 18
    'Business Branding',       // 19
  ];

  $default_metrics = [
    ['Sold in 8 days', 'MLS Featured'],
    ['Sold in 5 days', '38 Showings'],
    ['Sold Over Asking', 'MLS Featured'],
    ['Listed & Under Contract in 6 Days', '14,200 Video Views'],
    ['Sold in 11 days', 'Featured on Zillow'],
    ['Sold in 4 days', '60+ Inquiries'],
    ['Listed & Under Contract in 6 Days', 'Aerial Coverage'],
    ['Lot Sold in 14 days', 'Drone Survey'],
    ['Sold in 9 days', 'Aerial Featured'],
    ['Featured Listing', 'Aerial + Ground Coverage'],
    ['14,200 Video Views', 'Listed & Under Contract in 6 Days'],
    ['8,400 Video Views', 'Sold in 7 days'],
    ['22,000 Video Views', 'Featured on Social'],
    ['11,000 Video Views', 'Sold Over Asking'],
    ['18,000 Reel Views', '320 Saves'],
    ['24,000 Reel Views', '410 Saves'],
    ['15,500 Reel Views', 'Featured by Client'],
    ['Brand Refresh', '40% Engagement Increase'],
    ['Brand Campaign', '500+ New Followers'],
    ['Business Launch', 'Multi-Platform'],
  ];

  $sample_titles = [
    '6000 on the River',
    'Riverside Estates',
    'Ponte Vedra Luxury Home',
    'Fleming Island Pool Home',
    'Mandarin Family Home',
    'Nocatee New Build',
    'St. Johns Aerial',
    'Waterfront Lot Survey',
    'Nassau County Aerial',
    'Amelia Island Overview',
    'River View Cinematic Tour',
    'Luxury Walkthrough',
    'New Construction Film',
    'Sunset Home Tour',
    'Agent Brand Reel',
    'Market Update Reel',
    'Behind the Scenes Reel',
    'Corporate Brand Session',
    'Business Launch Campaign',
    'Team & Culture Shoot',
  ];

  $items = [];
  foreach (slm_portfolio_gallery_images() as $idx => $img) {
    $cat_idx = $idx < count($default_categories) ? $idx : ($idx % count($default_categories));
    $met_idx = $idx < count($default_metrics) ? $idx : ($idx % count($default_metrics));
    $ttl_idx = $idx < count($sample_titles) ? $idx : ($idx % count($sample_titles));

    $title = $img['title'] !== '' && $img['title'] !== 'Portfolio Image ' . ($idx + 1)
      ? $img['title']
      : $sample_titles[$ttl_idx];

    $items[] = [
      'id' => $idx + 1,
      'title' => $title,
      'category' => $default_categories[$cat_idx],
      'image' => $img['full'],
      'thumb' => $img['thumb'],
      'metrics' => $default_metrics[$met_idx],
      // NOTE: 'featured' is currently WRITE-ONLY. It exists so defaults match the
      // shape slm_portfolio_items_sanitize() emits and the portal's editor expects.
      // Nothing reads it — the portfolio hero image comes from the first gallery
      // image, not from this flag. Do not assume it drives the hero.
      'featured' => ($idx === 0),
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

    if ($thumb === '')
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
      'image' => $image,
      'thumb' => $thumb,
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
