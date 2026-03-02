<?php
/**
 * Template Name: Service - Virtual Tours
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
$hero_media = !empty($media_urls) ? $media_urls[0] : $theme_uri . '/assets/media/horizontal-videos/02-inside-this-stunning-north-florida-home-real-tours-north-florida.mp4';

// The rest go into the gallery block
$gallery_media = count($media_urls) > 1 ? array_slice($media_urls, 1) : [
  $theme_uri . '/assets/media/staged/05-32-primary-bedroom-1-of-4-virtually-staged.jpeg',
  $theme_uri . '/assets/media/staged/03-15-living-room-5-of-6-virtually-staged.jpeg',
  $theme_uri . '/assets/media/staged/08-46-lanai-virtually-staged.jpeg',
  $theme_uri . '/assets/media/staged/06-34-primary-bedroom-4-of-4-virtually-staged.jpeg',
];

$description = [
  'Virtual tours give buyers on-demand access to property flow and room-to-room context from any device. That accessibility increases listing quality and reduces friction in early decision-making.',
  'Our tour workflow aligns with Zillow-focused deliverables where needed, including support for 3D experiences and floor-plan-ready capture.',
  'For agents, this service strengthens positioning by showing a complete and modern marketing strategy that builds trust with both buyers and sellers.',
];

$benefits = [
  ['title' => '24/7 Buyer Accessibility', 'description' => 'Prospects can explore listings anytime, from anywhere, on mobile or desktop.'],
  ['title' => 'Zillow-Ready Workflow', 'description' => 'Supports listings that need Zillow 3D tour and floor plan style presentation.'],
  ['title' => 'Better Lead Qualification', 'description' => 'Buyers arrive with stronger understanding before booking a showing.'],
  ['title' => 'Time-Saving Workflow', 'description' => 'Reduce repetitive walkthrough requests and streamline scheduling.'],
  ['title' => 'Modern Brand Positioning', 'description' => 'Shows sellers you market listings with current, high-value tools.'],
];

$why_choose = [
  'Outcome-first approach centered on efficiency and conversion quality',
  'Reliable process and communication from capture to delivery',
  'Clean, professional outputs ready for immediate marketing use',
  'Flexible support for different property types and timelines',
  'Strategic mindset that connects media to business growth',
  'Partnership model focused on long-term client success',
];

get_template_part('template-parts/blocks/service-detail', null, [
  'title' => 'Virtual Tours',
  'subtitle' => 'Immersive property tours that increase buyer confidence and align with Zillow 3D and modern listing expectations.',
  'hero_image' => $hero_media,
  'gallery' => $gallery_media,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'book_url' => $create_account_url,
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Give Buyers a Better Way to Experience Your Listings',
  'cta_text' => 'Break the standard. Showcase the difference. Create your account and add virtual tours to your growth-ready marketing system.',
]);

get_footer();
