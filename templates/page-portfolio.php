<?php
/**
 * Template Name: Portfolio
 */
if (!defined('ABSPATH')) {
  die();
}

get_header();

$hero_img = get_the_post_thumbnail_url(get_queried_object_id(), 'full');
$hero_has_image = is_string($hero_img) && $hero_img !== '';
$hero_class = $hero_has_image ? 'page-hero--image' : 'page-hero--solid';

$pid = get_the_ID();
$theme_uri = get_template_directory_uri();
$config = function_exists('slm_get_editable_fields_for_template') ? slm_get_editable_fields_for_template('templates/page-portfolio.php') : [];

$meta_get = function ($key) use ($pid, $config) {
  $v = get_post_meta($pid, $key, true);
  if ($v === '' && $config && isset($config[$key])) {
    return $config[$key]['default'];
  }
  return $v;
};

$title = $meta_get('slm_portfolio_title');
$sub = $meta_get('slm_portfolio_sub');

// Query portfolio post type
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$portfolio_args = [
    'post_type' => 'portfolio',
    'posts_per_page' => 12,
    'paged' => $paged,
    'post_status' => 'publish',
];
$portfolio_query = new WP_Query($portfolio_args);
?>

<main>
  <section class="page-hero <?php echo esc_attr($hero_class); ?>" <?php echo $hero_has_image ? 'style="background-image:url(\'' . esc_url($hero_img) . '\');"' : ''; ?>>
    <?php if ($hero_has_image): ?>
      <div class="page-hero__overlay"></div>
    <?php endif; ?>
    <div class="container page-hero__content">
      <h1><?php echo esc_html($title); ?></h1>
      <p class="page-hero__sub"><?php echo esc_html($sub); ?></p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section page-section--compact">
    <div class="container">
      <?php if ($portfolio_query->have_posts()): ?>

        <div class="portfolio-grid">
          <?php while ($portfolio_query->have_posts()): $portfolio_query->the_post();
             $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
             $placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
             if (!$img) $img = $placeholder;
          ?>
              <article class="post-card post-card--portfolio">
                <a class="post-card__img post-card__img--portfolio" href="<?php the_permalink(); ?>">
                  <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
                </a>
                <div class="post-card__body">
                  <h3><a href="<?php the_permalink(); ?>" style="text-decoration:none; color:inherit;"><?php the_title(); ?></a></h3>
                  <div class="post-card__excerpt" style="margin-top:8px;">
                     <?php the_excerpt(); ?>
                  </div>
                  <?php
                  $results_meta = get_post_meta(get_the_ID(), 'slm_portfolio_results', true);
                  if ($results_meta) {
                    echo '<div class="portfolio-results" style="margin-top: 12px; font-weight: bold; font-size: 0.9em; color: var(--primary);">';
                    echo esc_html($results_meta);
                    echo '</div>';
                  }
                  ?>
                </div>
              </article>
          <?php endwhile; ?>
        </div>

        <div class="loop-pagination">
          <?php
          echo paginate_links([
            'total' => $portfolio_query->max_num_pages,
            'current' => $paged,
            'type' => 'list',
            'prev_text' => 'Previous',
            'next_text' => 'Next',
          ]);
          ?>
        </div>
        <?php wp_reset_postdata(); ?>
      <?php else: ?>
        <div class="center" style="padding:60px 20px;">
          <p>No portfolio items available yet. Add them using the Portfolio section in the WP Admin.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
