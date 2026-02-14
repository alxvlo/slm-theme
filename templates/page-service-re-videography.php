<?php
/**
 * Template Name: Service - Real Estate Videography
 */
if (!defined('ABSPATH')) exit;

get_header();

$theme_uri = get_template_directory_uri();
$create_account_url = add_query_arg('mode', 'signup', slm_login_url());
$hero_media = $theme_uri . '/assets/media/horizontal-videos/02-inside-this-stunning-north-florida-home-real-tours-north-florida.mp4';
$description_media = $theme_uri . '/assets/media/staged/03-15-living-room-5-of-6-virtually-staged.jpeg';

$description = [
  'Video is one of the strongest tools for helping buyers feel a property before they ever step inside. Our real estate videography is built to create emotional connection while communicating the practical flow of the home.',
  'Beyond visual quality, we focus on outcome: stronger listing presentation performance, better audience retention, and elevated brand perception across your marketing channels.',
  'From planning to final delivery, we run a streamlined process that reduces decision fatigue and gives your business a predictable, repeatable media system.',
];

$benefits = [
  ['title' => 'Higher Buyer Engagement', 'description' => 'Video keeps attention longer and drives stronger interest in your listing.'],
  ['title' => 'Premium Brand Perception', 'description' => 'Cinematic execution helps position both listing and agent at a higher standard.'],
  ['title' => 'Social and Web Ready Assets', 'description' => 'Final exports are optimized for listing platforms, social channels, and ads.'],
  ['title' => 'Story-Driven Property Flow', 'description' => 'Intentional sequencing highlights layout, lifestyle, and key selling points.'],
];

$why_choose = [
  'Strategic creative direction built around agent business goals',
  'Production quality aligned with premium listing expectations',
  'Consistent workflows that save time and reduce coordination stress',
  'Flexible service structures for changing listing volume',
  'Local-first perspective shaped by real market expectations',
  'Partnership model focused on long-term client outcomes',
];

get_template_part('template-parts/blocks/service-detail', null, [
  'title' => 'Real Estate Videography',
  'subtitle' => 'Cinematic listing videos that build trust, increase engagement, and position your marketing above the standard.',
  'hero_image' => $hero_media,
  'description_image' => $description_media,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'book_url' => $create_account_url,
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Create Video Marketing That Works Harder for Your Business',
  'cta_text' => 'Break the standard. Showcase the difference. Build a stronger listing system with video assets designed for competitive advantage.',
]);

get_footer();
