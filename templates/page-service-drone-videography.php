<?php
/**
 * Template Name: Service - Drone Videography
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$create_account_url = add_query_arg('mode', 'signup', slm_login_url());
// Retrieve videos specifically assigned via this page's Portfolio Admin Settings
$page_id = get_queried_object_id();
$video_ids_raw = $page_id > 0 ? get_post_meta($page_id, 'slm_portfolio_video_ids', true) : '';
$video_ids_array = preg_split('/[\s,]+/', (string) $video_ids_raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];

$video_urls = [];
foreach ($video_ids_array as $vid_id) {
  $url = wp_get_attachment_url((int) $vid_id);
  if (is_string($url) && $url !== '') {
    $video_urls[] = $url;
  }
}

$hero_media = !empty($video_urls) ? $video_urls[0] : '';
$gallery_media = count($video_urls) > 1 ? array_slice($video_urls, 1) : [];

$description = [
  'Drone videography delivers cinematic context and movement that helps your listing marketing feel premium, modern, and memorable.',
  'By combining aerial motion with strategic storytelling, this service helps agents communicate property scale, environment, and lifestyle in a way static media cannot.',
  'Our execution is designed for business outcomes: stronger engagement, higher perceived brand authority, and more compelling marketing campaigns.',
];

$benefits = [
  ['title' => 'Cinematic Differentiation', 'description' => 'Dynamic aerial motion helps listings stand out in competitive markets.'],
  ['title' => 'Stronger Audience Retention', 'description' => 'Video storytelling keeps viewers engaged longer across platforms.'],
  ['title' => 'Premium Marketing Perception', 'description' => 'Elevates how sellers and buyers perceive your brand and service quality.'],
  ['title' => 'Cross-Channel Content Value', 'description' => 'Use aerial video in MLS, websites, social media, and ads.'],
];

$why_choose = [
  'Strategic aerial storytelling tied to listing and brand outcomes',
  'Consistent quality standards for smooth, polished delivery',
  'Service-first communication throughout planning and execution',
  'Flexible support structures for different listing needs',
  'Local market awareness that informs what to showcase',
  'Long-term partnership built around client growth, not one-off jobs',
];

get_template_part('template-parts/blocks/service-detail', null, [
  'title' => 'Drone Videography',
  'subtitle' => 'Cinematic aerial video that adds scale, movement, and authority to your listing marketing system.',
  'hero_image' => $hero_media,
  'gallery' => $gallery_media,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'book_url' => $create_account_url,
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Upgrade Your Listing Story With Aerial Motion',
  'cta_text' => 'Break the standard. Showcase the difference. Create your account to launch higher-impact aerial campaigns.',
]);

get_footer();
