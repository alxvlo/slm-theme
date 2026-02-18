<?php get_header(); ?>
<main class="container">
  <?php while (have_posts()) : the_post(); ?>
    <article class="page">
      <h1><?php the_title(); ?></h1>
      <div class="content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; ?>
</main>
<?php get_footer(); ?>
