<?php
if (!defined('ABSPATH')) exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
$posts_page_id = (int) get_option('page_for_posts');
$blog_title = $posts_page_id ? get_the_title($posts_page_id) : 'Blog';
$blog_sub = '';
if ($posts_page_id) {
  $blog_sub = trim((string) get_post_field('post_excerpt', $posts_page_id));
  if ($blog_sub === '') {
    $blog_sub = trim((string) wp_strip_all_tags((string) get_post_field('post_content', $posts_page_id)));
    $blog_sub = wp_trim_words($blog_sub, 22, '...');
  }
}
if ($blog_sub === '') {
  $blog_sub = 'Insights on media strategy, market positioning, and agent growth systems.';
}

$hero_img = $posts_page_id ? get_the_post_thumbnail_url($posts_page_id, 'full') : '';
$hero_has_image = is_string($hero_img) && $hero_img !== '';
$hero_class = $hero_has_image ? 'page-hero--image' : 'page-hero--solid';
?>

<main>
  <section class="page-hero <?php echo esc_attr($hero_class); ?>" <?php echo $hero_has_image ? 'style="background-image:url(\'' . esc_url($hero_img) . '\');"' : ''; ?>>
    <?php if ($hero_has_image): ?>
      <div class="page-hero__overlay"></div>
    <?php endif; ?>
    <div class="container page-hero__content">
      <h1><?php echo esc_html($blog_title ?: 'Blog'); ?></h1>
      <p class="page-hero__sub"><?php echo esc_html($blog_sub); ?></p>
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
                <p class="post-card__meta"><?php echo esc_html(get_the_date('M j, Y')); ?><?php $cat = get_the_category(); if (!empty($cat)) echo '  |  ' . esc_html($cat[0]->name); ?></p>
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
          <h2>No posts published yet</h2>
          <p class="sub">Publish your first post in WordPress Admin to populate the blog feed.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
