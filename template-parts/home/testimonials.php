<?php
if (!defined('ABSPATH')) exit;

$q = new WP_Query([
  'post_type' => 'testimonial',
  'post_status' => 'publish',
  'posts_per_page' => 6,
  'no_found_rows' => true,
]);

if (!$q->have_posts()) return;

$star_svg = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17.3 5.8 20.8l1.2-7.1L1.8 8.7l7.2-1L12 1.2l3 6.5 7.2 1-5.2 5 1.2 7.1L12 17.3Z" fill="currentColor"/></svg>';
?>

<section class="home-testimonials" aria-labelledby="home-testimonials-title">
  <div class="container">
    <header class="home-testimonials__header">
      <h2 id="home-testimonials-title">What Clients Say</h2>
      <p>Real feedback from agents and teams who trust us with their listing media.</p>
    </header>

    <div class="home-testimonials__grid">
      <?php while ($q->have_posts()): $q->the_post(); ?>
        <?php
          $id = get_the_ID();
          $rating = (int) get_post_meta($id, 'slm_testimonial_rating', true);
          $rating = max(1, min(5, $rating ?: 5));
          $source = (string) get_post_meta($id, 'slm_testimonial_source', true);
          $role = (string) get_post_meta($id, 'slm_testimonial_role', true);
          $location = (string) get_post_meta($id, 'slm_testimonial_location', true);

          $name = get_the_title() ?: 'Client';

          $meta_parts = [];
          if ($role !== '') $meta_parts[] = $role;
          if ($location !== '') $meta_parts[] = $location;
          $meta_line = implode(' • ', $meta_parts);

          $time_label = get_the_date();
        ?>

        <article class="tCard">
          <header class="tCard__head">
            <div class="tCard__who">
              <div class="tCard__avatar" aria-hidden="true">
                <?php if (has_post_thumbnail()): ?>
                  <?php the_post_thumbnail('thumbnail', ['loading' => 'lazy']); ?>
                <?php else: ?>
                  <span><?php echo esc_html(strtoupper(substr($name, 0, 1))); ?></span>
                <?php endif; ?>
              </div>

              <div class="tCard__identity">
                <strong class="tCard__name"><?php echo esc_html($name); ?></strong>
                <?php if ($meta_line !== ''): ?>
                  <div class="tCard__meta"><?php echo esc_html($meta_line); ?></div>
                <?php endif; ?>
              </div>
            </div>

            <div class="tCard__rating">
              <div class="tStars" aria-label="<?php echo esc_attr($rating . ' out of 5 stars'); ?>">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="tStar <?php echo $i <= $rating ? 'is-on' : 'is-off'; ?>"><?php echo $star_svg; ?></span>
                <?php endfor; ?>
              </div>

              <div class="tCard__source">
                <?php if ($source !== ''): ?>
                  <span><?php echo esc_html($source); ?></span>
                <?php endif; ?>
                <?php if ($time_label): ?>
                  <span><?php echo esc_html($time_label); ?></span>
                <?php endif; ?>
              </div>
            </div>
          </header>

          <div class="tCard__body">
            <?php
              $content = get_the_content();
              $content = wp_strip_all_tags($content);
              echo '<p>' . esc_html(trim($content)) . '</p>';
            ?>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  </div>
</section>
