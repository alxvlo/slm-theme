<?php
/**
 * Template Name: Service - Twilight Photography
 */
if (!defined('ABSPATH'))
  exit;

slm_page_seo(
  'Twilight Real Estate Photography in Jacksonville, FL | Showcase Listings Media',
  'Twilight and dusk listing photography that makes North Florida homes stand out in search results. In-person and AI twilight options.'
);

get_header();

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

$hero_media = !empty($media_urls) ? $media_urls[0] : '';
$gallery_media = count($media_urls) > 1 ? array_slice($media_urls, 1) : [];

$description = [
  'Twilight photography creates visual impact that instantly separates a listing from standard daytime coverage. The result is a premium, attention-grabbing presentation.',
  'For agents, this service supports stronger brand perception and helps communicate elevated marketing effort in listing presentations and online campaigns.',
  'We execute twilight sessions with precision timing and detail-focused editing to produce imagery that feels polished, intentional, and market-ready.',
];

$benefits = [
  ['title' => 'High-Impact Visual Differentiation', 'description' => 'Twilight imagery helps your listing stand out quickly in competitive feeds.'],
  ['title' => 'Premium Perception', 'description' => 'Creates a luxury-forward presentation that supports higher-value positioning.'],
  ['title' => 'Seller Confidence Boost', 'description' => 'Shows clients a strategic and elevated approach to marketing their property.'],
  ['title' => 'Strong Campaign Versatility', 'description' => 'Twilight assets perform well across listing sites, social media, and ads.'],
];

$why_choose = [
  'Precision capture timing and editing for consistent, premium results',
  'Outcome-based strategy focused on visibility and positioning impact',
  'Service-first communication from planning through delivery',
  'Flexible support that integrates with full media packages',
  'Local market knowledge to align visuals with buyer expectations',
  'Long-term partnership approach centered on agent growth',
];

get_template_part('template-parts/blocks/service-detail', null, [
  'title' => 'Twilight Photography',
  'subtitle' => 'Premium twilight imagery that elevates listing perception and helps your marketing stand out with authority.',
  'hero_image' => $hero_media,
  'gallery' => $gallery_media,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'faqs' => [
    ['q' => 'Do you shoot real twilight or edit it?', 'a' => 'Both are available: in-person twilight shoots captured at dusk, and dusk conversions edited from daytime photos — choose whichever fits your timeline and budget.'],
    ['q' => 'How fast will I get my twilight photos?', 'a' => 'Edited twilight photos are delivered within 24–48 hours of the shoot.'],
    ['q' => 'Why add twilight photography to a listing?', 'a' => 'Warm dusk lighting makes a listing stand out in search results and thumbnails, where most buyers form their first impression.'],
  ],
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Showcase Listings With Premium Twilight Impact',
  'cta_text' => 'Break the standard. Showcase the difference. Create your account and add high-impact twilight visuals to your next listing campaign.',
]);

get_footer();
