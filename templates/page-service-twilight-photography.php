<?php
/**
 * Template Name: Service - Twilight Photography
 */
if (!defined('ABSPATH')) exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
$create_account_url = add_query_arg('mode', 'signup', slm_login_url());

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
  'hero_image' => $placeholder,
  'description_image' => $placeholder,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'book_url' => $create_account_url,
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Showcase Listings With Premium Twilight Impact',
  'cta_text' => 'Break the standard. Showcase the difference. Create your account and add high-impact twilight visuals to your next listing campaign.',
]);

get_footer();
