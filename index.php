<?php get_header(); ?>

<main id="main-content" class="container" style="padding-top:40px; padding-bottom:60px;">
  <?php if (have_posts()) : ?>
    <h1 style="margin-bottom:28px;"><?php echo is_search() ? 'Search Results for: ' . esc_html(get_search_query()) : 'Latest Posts'; ?></h1>
    <div class="post-grid">
      <?php while (have_posts()) : the_post(); ?>
        <article class="post-card">
          <?php if (has_post_thumbnail()) : ?>
            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large'); ?></a>
          <?php endif; ?>
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p><?php echo wp_trim_words(get_the_excerpt(), 24); ?></p>
          <a class="btn btn--secondary" href="<?php the_permalink(); ?>">Read More</a>
        </article>
      <?php endwhile; ?>
    </div>
    <nav class="pagination" aria-label="Posts navigation" style="margin-top:32px; text-align:center;">
      <?php the_posts_pagination(['mid_size' => 2, 'prev_text' => '&laquo; Previous', 'next_text' => 'Next &raquo;']); ?>
    </nav>
  <?php else : ?>
    <h1>Nothing Found</h1>
    <p style="color:var(--muted-foreground);">No posts matched your criteria. Try a different search.</p>
    <?php get_search_form(); ?>
  <?php endif; ?>
</main>

<?php get_footer(); ?>
