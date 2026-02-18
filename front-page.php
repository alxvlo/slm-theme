<?php
if (!defined('ABSPATH')) exit;

get_header();
?>

<main class="home-main">
  <?php get_template_part('template-parts/home/hero-slider'); ?>
  <?php get_template_part('template-parts/home/services-links'); ?>
  <?php get_template_part('template-parts/home/how-it-works'); ?>
  <?php get_template_part('template-parts/home/testimonials'); ?>
  <?php get_template_part('template-parts/home/cta'); ?>
</main>

<?php get_footer(); ?>
