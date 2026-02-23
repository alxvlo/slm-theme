<?php
/**
 * Template Name: Service - Zillow Showcase
 */
if (!defined('ABSPATH'))
    exit;

get_header();

$theme_uri = get_template_directory_uri();
$create_account_url = add_query_arg('mode', 'signup', slm_login_url());
$hero_media = $theme_uri . '/assets/media/floor-plans/02-floor-plan-main.jpg';
$description_media = $theme_uri . '/assets/media/floor-plans/04-floor-plan-combined.jpg';
$gallery_media = [
    $theme_uri . '/assets/media/staged/03-15-living-room-5-of-6-virtually-staged.jpeg',
    $theme_uri . '/assets/media/floor-plans/03-floor-plan-main-alt.jpg',
    $theme_uri . '/assets/media/staged/05-32-primary-bedroom-1-of-4-virtually-staged.jpeg',
];

$description = [
    'Zillow Showcase combines an interactive 3D tour with a professional floorplan to create a comprehensive, buyer-ready listing experience. Together, these assets increase Zillow visibility and keep buyers engaged longer.',
    'For agents, this service strengthens listing presentations by showing sellers a complete, technology-forward marketing approach that stands out from standard photo-only competition.',
    'Our Zillow Showcase workflow is built for fast turnaround and seamless integration with your existing listing media packages, so your listings launch fully equipped on day one.',
];

$benefits = [
    ['title' => 'Enhanced Zillow Visibility', 'description' => 'Zillow prioritizes listings with 3D tours and floorplans, increasing your reach and impressions.'],
    ['title' => 'Complete Buyer Understanding', 'description' => 'Buyers can explore the full property layout and flow before scheduling a showing.'],
    ['title' => 'Stronger Listing Presentations', 'description' => 'Show sellers you offer the full Zillow marketing toolkit, not just basic photography.'],
    ['title' => 'Reduced Time on Market', 'description' => 'Better-informed buyers make faster decisions, helping listings move more efficiently.'],
    ['title' => 'Competitive Differentiation', 'description' => 'Most agents skip the 3D tour and floorplan — offering both immediately sets you apart.'],
];

$why_choose = [
    'Bundled 3D tour and floorplan workflow built specifically for Zillow requirements',
    'Fast turnaround to ensure your listing launches fully equipped',
    'Outcome-focused process tied to buyer engagement and listing visibility',
    'Seamless integration with photography, video, and drone packages',
    'Reliable communication and professional delivery standards',
    'Partnership model focused on long-term agent success',
];

get_template_part('template-parts/blocks/service-detail', null, [
    'title' => 'Zillow Showcase',
    'subtitle' => 'Interactive 3D tours and professional floorplans bundled for maximum Zillow visibility and buyer engagement.',
    'hero_image' => $hero_media,
    'description_image' => $description_media,
    'gallery' => $gallery_media,
    'description' => $description,
    'benefits' => $benefits,
    'why_choose' => $why_choose,
    'book_url' => $create_account_url,
    'book_label' => 'Create Account to Order',
    'cta_title' => 'Maximize Your Zillow Listing Impact',
    'cta_text' => 'Break the standard. Showcase the difference. Add 3D tours and floorplans to every listing and give buyers the complete experience they expect.',
]);

get_footer();
