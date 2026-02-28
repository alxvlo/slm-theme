<?php get_header(); ?>

<main id="main-content">
  <?php while (have_posts()) : the_post(); ?>
    <section class="page-hero page-hero--solid">
      <div class="container page-hero__content">
        <h1><?php the_title(); ?></h1>
      </div>
      <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
        <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
      </svg>
    </section>

    <section class="page-section">
      <div class="container">
        <article class="content"><?php the_content(); ?></article>
      </div>
    </section>
  <?php endwhile; ?>
</main>

<?php get_footer(); ?>
