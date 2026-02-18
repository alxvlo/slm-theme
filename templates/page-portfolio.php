<?php
/**
 * Template Name: Portfolio
 */
if (!defined('ABSPATH')) exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
$hero_img = get_the_post_thumbnail_url(get_queried_object_id(), 'full');
$hero_has_image = is_string($hero_img) && $hero_img !== '';
$hero_class = $hero_has_image ? 'page-hero--image' : 'page-hero--solid';
$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in ? slm_dashboard_url() : add_query_arg('mode', 'signup', slm_login_url());
$cta_label = $is_logged_in ? 'Go to Dashboard' : 'Create Account to Order';

$portfolio_query = new WP_Query([
  'post_type' => 'portfolio',
  'post_status' => 'publish',
  'posts_per_page' => 9,
  'orderby' => 'date',
  'order' => 'DESC',
]);

$fallback_items = [
  [
    'title' => 'Listing Presentation Upgrade',
    'text' => 'Marketing assets designed to help agents present with more authority and win more listings.',
  ],
  [
    'title' => 'Brand Perception Elevation',
    'text' => 'Visual consistency that positions agents as premium professionals in competitive markets.',
  ],
  [
    'title' => 'Workflow and Scale Support',
    'text' => 'Media systems built to reduce friction and support growth as listing volume increases.',
  ],
];
?>

<main>
  <section class="page-hero <?php echo esc_attr($hero_class); ?>" <?php echo $hero_has_image ? 'style="background-image:url(\'' . esc_url($hero_img) . '\');"' : ''; ?>>
    <?php if ($hero_has_image): ?>
      <div class="page-hero__overlay"></div>
    <?php endif; ?>
    <div class="container page-hero__content">
      <h1><?php the_title(); ?></h1>
      <p class="page-hero__sub">Featured listing media and campaign examples built to help agents stand out.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section">
    <div class="container">
      <?php if ($portfolio_query->have_posts()): ?>
        <div class="portfolio-grid">
          <?php while ($portfolio_query->have_posts()): $portfolio_query->the_post(); ?>
            <?php
              $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
              if (!$img) $img = $placeholder;
              $excerpt = get_the_excerpt();
              if ($excerpt === '') {
                $excerpt = wp_trim_words(wp_strip_all_tags((string) get_the_content()), 22);
              }
            ?>
            <article class="post-card">
              <a class="post-card__img" href="<?php the_permalink(); ?>">
                <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async">
              </a>
              <div class="post-card__body">
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p class="post-card__meta"><?php echo esc_html(get_the_date('M j, Y')); ?></p>
                <p class="post-card__excerpt"><?php echo esc_html($excerpt); ?></p>
              </div>
            </article>
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
      <?php else: ?>
        <div class="portfolio-grid">
          <?php foreach ($fallback_items as $item): ?>
            <article class="post-card">
              <div class="post-card__img"><img src="<?php echo esc_url($placeholder); ?>" alt="" loading="lazy" decoding="async"></div>
              <div class="post-card__body">
                <h3><?php echo esc_html($item['title']); ?></h3>
                <p class="post-card__excerpt"><?php echo esc_html($item['text']); ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="center" style="margin-top:40px;">
        <p class="sub" style="margin-bottom:14px;">Want your listings to look and perform like this?</p>
        <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
