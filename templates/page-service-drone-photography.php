<?php
/**
 * Template Name: Service - Drone Photography
 */
if (!defined('ABSPATH')) exit;

get_header();

$theme_uri = get_template_directory_uri();
$create_account_url = add_query_arg('mode', 'signup', slm_login_url());
$hero_media = $theme_uri . '/assets/media/drone-photos/08-52-aerial-front-exterior-1.jpg';
$description_media = $theme_uri . '/assets/media/drone-photos/10-75-aerial-view-2.jpg';
$gallery_media = [
  $theme_uri . '/assets/media/drone-photos/05-3-aerial-overview.jpg',
  $theme_uri . '/assets/media/drone-photos/09-6-aerial-overview-3.jpg',
  $theme_uri . '/assets/media/drone-photos/07-4-aerial-rear-view-1.jpg',
];

$description = [
  'Drone photography adds context that ground-level media cannot provide. It helps buyers understand lot layout, surroundings, and location value at a glance.',
  'For agents, these visuals strengthen listing presentations and improve perceived professionalism by showing a complete marketing approach, not just basic coverage.',
  'Our aerial work is captured with safety, precision, and strategic framing so each image contributes to better positioning and stronger market visibility.',
];

$benefits = [
  ['title' => 'Better Location Storytelling', 'description' => 'Show neighborhood context, lot orientation, and nearby amenities clearly.'],
  ['title' => 'Standout Listing Presence', 'description' => 'Aerial visuals create differentiation in crowded listing feeds.'],
  ['title' => 'Stronger Seller Confidence', 'description' => 'Comprehensive coverage reinforces your marketing professionalism.'],
  ['title' => 'Flexible Marketing Usage', 'description' => 'Use aerial photos across MLS, social campaigns, and presentation decks.'],
];

$why_choose = [
  'Professional aerial capture with disciplined composition standards',
  'Outcome-focused framing tied to listing performance goals',
  'Reliable communication and scheduling coordination',
  'Flexible service model that scales with your business',
  'Local insight that informs what buyers care about in each area',
  'Partnership approach rooted in long-term growth and trust',
];

get_template_part('template-parts/blocks/service-detail', null, [
  'title' => 'Drone Photography',
  'subtitle' => 'Aerial listing imagery that strengthens property context, improves differentiation, and supports stronger market positioning.',
  'hero_image' => $hero_media,
  'description_image' => $description_media,
  'gallery' => $gallery_media,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'book_url' => $create_account_url,
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Add Aerial Context to Your Listing Strategy',
  'cta_text' => 'Break the standard. Showcase the difference. Use aerial media to elevate presentation quality and competitive advantage.',
]);

get_footer();
