<?php
/**
 * Template Name: Service - Floor Plans
 */
if (!defined('ABSPATH')) exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
$create_account_url = add_query_arg('mode', 'signup', slm_login_url());

$description = [
  'Floor plans provide structural clarity that photos and video alone cannot. They help buyers understand layout, flow, and room relationships quickly.',
  'For agents, this increases perceived professionalism and supports listing presentations with clear, practical information that builds seller confidence.',
  'When used as part of a complete media package, floor plans contribute to stronger decision-making and a more scalable, systemized marketing process.',
];

$benefits = [
  ['title' => 'Layout Clarity at a Glance', 'description' => 'Helps buyers instantly understand room flow and property structure.'],
  ['title' => 'Improved Buyer Confidence', 'description' => 'Reduces uncertainty before showings and supports informed decisions.'],
  ['title' => 'Stronger Listing Presentations', 'description' => 'Adds a practical planning layer sellers value during listing discussions.'],
  ['title' => 'Complete Media Package Value', 'description' => 'Integrates seamlessly with photography, video, and tours for full coverage.'],
];

$why_choose = [
  'Clear and professional plan outputs built for real-world marketing use',
  'Outcome-focused process tied to listing quality and buyer clarity',
  'Reliable timelines and communication that support agent workflows',
  'Flexible service options based on listing needs and scale',
  'Business-minded partnership that values long-term client success',
  'Consistent standards that strengthen your brand presentation',
];

get_template_part('template-parts/blocks/service-detail', null, [
  'title' => 'Floor Plans',
  'subtitle' => 'Professional floor plans that improve buyer understanding, strengthen presentations, and complete your listing strategy.',
  'hero_image' => $placeholder,
  'description_image' => $placeholder,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'book_url' => $create_account_url,
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Add Structure and Clarity to Every Listing',
  'cta_text' => 'Break the standard. Showcase the difference. Use floor plans to deliver complete, confidence-building marketing assets.',
]);

get_footer();
