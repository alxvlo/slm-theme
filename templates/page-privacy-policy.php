<?php
/**
 * Template Name: Privacy Policy
 */
if (!defined('ABSPATH')) exit;

get_header();

$title = get_the_title() ?: 'Privacy Policy';
?>

<main class="legal-page legal-page--privacy">
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1><?php echo esc_html($title); ?></h1>
      <p class="page-hero__sub">How Showcase Listings Media collects, uses, shares, and protects information across bookings, accounts, payments, and client portal activity.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section">
    <div class="container legal-page__wrap">
      <article class="prose legal-doc legal-doc--privacy">
        <?php if (function_exists('slm_privacy_policy_content_html')): ?>
          <?php echo wp_kses_post(slm_privacy_policy_content_html()); ?>
        <?php elseif (have_posts()): while (have_posts()): the_post(); ?>
          <?php the_content(); ?>
        <?php endwhile; endif; ?>
      </article>
    </div>
  </section>
</main>

<?php get_footer(); ?>

