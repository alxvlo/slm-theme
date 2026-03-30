<?php
if (!defined('ABSPATH')) exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
$archive_title = get_the_archive_title();
$archive_desc = get_the_archive_description();
if ($archive_desc === '') {
  $archive_desc = 'Latest updates and articles from the blog.';
}
?>

<main>
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1><?php echo esc_html($archive_title); ?></h1>
      <p class="page-hero__sub"><?php echo wp_kses_post($archive_desc); ?></p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section">
    <div class="container">
      <?php if (have_posts()): ?>
        <div class="blog-list">
          <?php while (have_posts()): the_post(); ?>
            <?php
              $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
              if (!$img) $img = $placeholder;
              $excerpt = get_the_excerpt();
              if ($excerpt === '') {
                $excerpt = wp_trim_words(wp_strip_all_tags((string) get_the_content()), 24);
              }
            ?>
            <article class="post-card">
              <a class="post-card__img" href="<?php the_permalink(); ?>">
                <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
              </a>
              <div class="post-card__body">
                <p class="post-card__meta"><?php echo esc_html(get_the_date('M j, Y')); ?></p>
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p class="post-card__excerpt"><?php echo esc_html($excerpt); ?></p>
              </div>
            </article>
          <?php endwhile; ?>
        </div>

        <div class="loop-pagination">
          <?php
          echo paginate_links([
            'type' => 'list',
            'prev_text' => 'Previous',
            'next_text' => 'Next',
          ]);
          ?>
        </div>
      <?php else: ?>
        <div class="loop-empty card">
          <h2>No posts found</h2>
          <p class="sub">Try another category or publish new content to populate this archive.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
