<?php
/**
 * Template Name: Service - Real Estate Photography
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$theme_uri = get_template_directory_uri();
$create_account_url = add_query_arg('mode', 'signup', slm_login_url());
// Retrieve media specifically assigned via this page's Portfolio Admin Settings
$page_id = get_queried_object_id();
$photo_ids = $page_id > 0 ? get_post_meta($page_id, 'slm_portfolio_gallery_ids', true) : '';
$video_ids = $page_id > 0 ? get_post_meta($page_id, 'slm_portfolio_video_ids', true) : '';
$media_ids_raw = trim($photo_ids . ',' . $video_ids, ',');
$media_ids_array = preg_split('/[\s,]+/', (string) $media_ids_raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];

$media_urls = [];
foreach ($media_ids_array as $m_id) {
  $url = wp_get_attachment_url((int) $m_id);
  if (is_string($url) && $url !== '') {
    $media_urls[] = $url;
  }
}

// Fallbacks if no media is configured in the portfolio settings
$hero_media = !empty($media_urls) ? $media_urls[0] : $theme_uri . '/assets/media/photos/08-1-front-exterior.jpg';

// The rest go into the gallery block
$gallery_media = count($media_urls) > 1 ? array_slice($media_urls, 1) : [
  $theme_uri . '/assets/media/photos/02-12-living-room-5-of-6.jpg',
  $theme_uri . '/assets/media/photos/10-28-dining-room-1-of-3.jpg',
  $theme_uri . '/assets/media/photos/14-35-primary-bedroom-4-of-4.jpg',
  $theme_uri . '/assets/media/photos/03-17-dining-room-1-of-4.jpg',
];

$description = [
  'Real estate photography is not just a deliverable. It is the first layer of your listing strategy and a major driver of perception. We capture each property with intention so buyers and sellers immediately see quality, professionalism, and value.',
  'Our process is built to support business outcomes: stronger listing presentations, elevated brand consistency, and marketing assets that help you stand out in competitive markets.',
  'As a service-first partner, we focus on clear communication, high standards, and reliable turnaround so you can move from booking to market with less friction.',
];

$benefits = [
  ['title' => 'Stronger First Impressions', 'description' => 'Present every listing with polished visuals that elevate buyer and seller confidence.'],
  ['title' => 'Higher Perceived Value', 'description' => 'Intentional composition and editing support premium positioning in your market.'],
  ['title' => 'Faster Launch to Market', 'description' => 'Consistent delivery timelines keep your listing momentum intact.'],
  ['title' => 'Brand-Level Consistency', 'description' => 'Every shoot reinforces a professional standard across your portfolio.'],
];

$why_choose = [
  'Outcome-driven approach focused on listing performance, not just file delivery',
  'Service-first partnership with responsive communication and accountability',
  'High standards for detail, composition, and final presentation',
  'Flexible support that adapts to your listing workflow and volume',
  'Local market awareness that informs framing and visual priorities',
  'Long-term collaboration mindset built around your growth',
];

get_template_part('template-parts/blocks/service-detail', null, [
  'title' => 'Real Estate Photography',
  'subtitle' => 'Professional listing photography designed to elevate perception, strengthen presentations, and help you win more opportunities.',
  'hero_image' => $hero_media,
  'gallery' => $gallery_media,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'book_url' => $create_account_url,
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Turn Every Listing Into a Stronger First Impression',
  'cta_text' => 'Create your account to launch polished media that lifts first impressions and supports measurable listing performance.',
]);

get_footer();
