<?php
/**
 * Template Name: Service - Virtual Tours
 */
if (!defined('ABSPATH'))
  exit;

slm_page_seo(
  '3D Virtual Tours in Jacksonville, FL | Showcase Listings Media',
  'Interactive 3D virtual tours that let buyers walk North Florida listings 24/7. Zillow-ready workflow, delivered in 24–48 hours.'
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
  'faqs' => [
    ['q' => 'What is a virtual tour?', 'a' => 'An interactive 3D walkthrough that lets buyers explore the property room by room from any device, any time.'],
    ['q' => 'Can I share or embed the tour?', 'a' => 'Yes — share the tour link directly or embed it in your MLS listing, website, and social posts.'],
    ['q' => 'What areas do you cover?', 'a' => 'We serve six North Florida counties: Duval, St. Johns, Clay, Nassau, Putnam, and Baker.'],
  ],
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Give Buyers a Better Way to Experience Your Listings',
  'cta_text' => 'Break the standard. Showcase the difference. Create your account and add virtual tours to your growth-ready marketing system.',
]);

get_footer();
