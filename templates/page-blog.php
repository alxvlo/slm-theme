<?php
/**
 * Template Name: Blog
 */
if (!defined('ABSPATH')) exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in ? slm_dashboard_url() : add_query_arg('mode', 'signup', slm_login_url());
$cta_label = $is_logged_in ? 'Go to Dashboard' : 'Create Account to Order';

$blog_query = new WP_Query([
  'post_type' => 'post',
  'post_status' => 'publish',
  'posts_per_page' => 9,
  'orderby' => 'date',
  'order' => 'DESC',
]);

$fallback_posts = [
  [
    'title' => 'Why Outcome-Driven Marketing Beats Transactional Media',
    'excerpt' => 'Agents do not need more files. They need systems that help them win listings, save time, and grow with consistency.',
  ],
  [
    'title' => 'What Agents Gain From Strategic Media Partnerships',
    'excerpt' => 'From stronger presentations to improved brand authority, the right media model creates measurable business momentum.',
  ],
  [
    'title' => 'Why Agents Switch: Better Systems, Better Support, Better Results',
    'excerpt' => 'Most switches happen when agents outgrow rigid packages and choose flexible, growth-aligned support.',
  ],
];
?>

<main>
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1><?php the_title(); ?></h1>
      <p class="page-hero__sub">Insights on media strategy, market positioning, and agent growth systems.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section">
    <div class="container">
      <?php if ($blog_query->have_posts()): ?>
        <div class="blog-list">
          <?php while ($blog_query->have_posts()): $blog_query->the_post(); ?>
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
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
      <?php else: ?>
        <div class="blog-list">
          <?php foreach ($fallback_posts as $item): ?>
            <article class="post-card">
              <div class="post-card__img"><img src="<?php echo esc_url($placeholder); ?>" alt="" loading="lazy" decoding="async"></div>
              <div class="post-card__body">
                <h3><?php echo esc_html($item['title']); ?></h3>
                <p class="post-card__excerpt"><?php echo esc_html($item['excerpt']); ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="center" style="margin-top:40px;">
        <p class="sub" style="margin-bottom:14px;">Want to turn these insights into listing results?</p>
        <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
