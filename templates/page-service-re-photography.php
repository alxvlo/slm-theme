<?php
/**
 * Template Name: Service - Real Estate Photography
 */
if (!defined('ABSPATH')) exit;

get_header();

$theme_uri = get_template_directory_uri();
$create_account_url = add_query_arg('mode', 'signup', slm_login_url());
$hero_media = $theme_uri . '/assets/media/photos/08-1-front-exterior.jpg';
$description_media = $theme_uri . '/assets/media/photos/02-12-living-room-5-of-6.jpg';
$gallery_media = [
  $theme_uri . '/assets/media/photos/10-28-dining-room-1-of-3.jpg',
  $theme_uri . '/assets/media/photos/14-35-primary-bedroom-4-of-4.jpg',
  $theme_uri . '/assets/media/photos/03-17-dining-room-1-of-4.jpg',
];

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
  'hero_image' => $hero_media,
  'description_image' => $description_media,
  'gallery' => $gallery_media,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'book_url' => $create_account_url,
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Turn Every Listing Into a Stronger First Impression',
  'cta_text' => 'Create your account to launch polished media that lifts first impressions and supports measurable listing performance.',
]);

get_footer();
