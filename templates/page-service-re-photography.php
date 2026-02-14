<?php
/**
 * Template Name: Service - Real Estate Photography
 */
if (!defined('ABSPATH')) exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
$create_account_url = add_query_arg('mode', 'signup', slm_login_url());

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
  'hero_image' => $placeholder,
  'description_image' => $placeholder,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'book_url' => $create_account_url,
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Turn Every Listing Into a Stronger First Impression',
  'cta_text' => 'Break the standard. Showcase the difference. Create your account to start ordering media that supports measurable business outcomes.',
]);

get_footer();
