<?php
/**
 * Template Name: Service - Virtual Tours
 */
if (!defined('ABSPATH')) exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
$create_account_url = add_query_arg('mode', 'signup', slm_login_url());

$description = [
  'Virtual tours give buyers on-demand access to property flow and room-to-room context from any device. That accessibility increases listing quality and reduces friction in early decision-making.',
  'For agents, tours support stronger positioning by showing a complete and modern marketing strategy that builds trust with both buyers and sellers.',
  'We deliver tours designed for practical business value: better lead quality, improved efficiency, and more scalable listing workflows.',
];

$benefits = [
  ['title' => '24/7 Buyer Accessibility', 'description' => 'Prospects can explore listings anytime, from anywhere, on mobile or desktop.'],
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
  'subtitle' => 'Immersive property tours that increase buyer confidence, improve lead quality, and strengthen your listing strategy.',
  'hero_image' => $placeholder,
  'description_image' => $placeholder,
  'description' => $description,
  'benefits' => $benefits,
  'why_choose' => $why_choose,
  'book_url' => $create_account_url,
  'book_label' => 'Create Account to Order',
  'cta_title' => 'Give Buyers a Better Way to Experience Your Listings',
  'cta_text' => 'Break the standard. Showcase the difference. Create your account and add virtual tours to your growth-ready marketing system.',
]);

get_footer();
