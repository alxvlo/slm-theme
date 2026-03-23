<?php
/**
 * Template Name: Terms of Service
 */
if (!defined('ABSPATH')) exit;

get_header();

$title = get_the_title() ?: 'Terms of Service';
?>

<main class="legal-page legal-page--terms">
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1><?php echo esc_html($title); ?></h1>
      <p class="page-hero__sub">Booking, delivery, revision, payment, and media usage policies for Showcase Listings Media services.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section">
    <div class="container legal-page__wrap">
      <article class="prose legal-doc legal-doc--terms">
        <?php if (function_exists('slm_terms_of_service_content_html')): ?>
          <?php echo wp_kses_post(slm_terms_of_service_content_html()); ?>
        <?php elseif (have_posts()): while (have_posts()): the_post(); ?>
          <?php the_content(); ?>
        <?php endwhile; endif; ?>
      </article>
    </div>
  </section>
</main>

<?php get_footer(); ?>

