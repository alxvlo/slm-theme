<?php
if (!defined('ABSPATH')) exit;

$t_query = new WP_Query([
  'post_type' => 'testimonial',
  'posts_per_page' => 3,
  'orderby' => 'menu_order',
  'order' => 'ASC',
  'post_status' => 'publish',
  'no_found_rows' => true,
]);

if (!$t_query->have_posts()) {
  return;
}

$theme_uri = get_template_directory_uri();
?>

<section class="home-testimonials" aria-labelledby="home-testimonials-title">
  <div class="container">
    <header class="home-testimonials__header reveal">
      <h2 id="home-testimonials-title">Trusted by Top Agents &amp; Brands</h2>
      <p>Hear from the professionals who rely on us to scale their marketing, win more deals, and build authority in their markets.</p>
    </header>

    <div class="home-testimonials__grid reveal">
      <?php while ($t_query->have_posts()):
        $t_query->the_post();
        $pid = get_the_ID();

        $title = get_post_meta($pid, 'slm_testimonial_role', true);
        $company = get_post_meta($pid, 'slm_testimonial_location', true);
        $source = get_post_meta($pid, 'slm_testimonial_source', true);
        $rating = (int) get_post_meta($pid, 'slm_testimonial_rating', true);
        if ($rating === 0) $rating = 5;

        $img = get_the_post_thumbnail_url($pid, 'thumbnail');
        $initials = '';
        if (!$img) {
          $name = get_the_title();
          $parts = array_filter(explode(' ', $name));
          if (!empty($parts)) {
            $initials .= mb_substr(reset($parts), 0, 1);
            if (count($parts) > 1) {
              $initials .= mb_substr(end($parts), 0, 1);
            }
          }
          $initials = mb_strtoupper($initials);
        }
        ?>
        <article class="tCard">
          <header class="tCard__head">
            <div class="tCard__who">
              <div class="tCard__avatar">
                <?php if ($img): ?>
                  <img src="<?php echo esc_url($img); ?>" alt="Photo of <?php echo esc_attr(get_the_title()); ?>" loading="lazy" decoding="async" />
                <?php else: ?>
                  <?php echo esc_html($initials); ?>
                <?php endif; ?>
              </div>
              <div class="tCard__info">
                <strong class="tCard__name"><?php the_title(); ?></strong>
                <?php if ($title || $company): ?>
                  <div class="tCard__meta">
                    <?php if ($title)
                      echo esc_html($title); ?>
                    <?php if ($title && $company)
                      echo ' • '; ?>
                    <?php if ($company)
                      echo esc_html($company); ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="tCard__rating" aria-label="<?php echo esc_attr((string) $rating); ?> out of 5 stars">
              <div class="tStars" aria-hidden="true">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="tStar <?php echo $i <= $rating ? 'is-on' : 'is-off'; ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                    </svg>
                  </span>
                <?php endfor; ?>
              </div>
              <?php if ($source): ?>
                <div class="tCard__source">
                  <span><?php echo esc_html($source); ?></span>
                </div>
              <?php endif; ?>
            </div>
          </header>

          <div class="tCard__body">
            <?php the_content(); ?>
          </div>
        </article>
      <?php endwhile;
      wp_reset_postdata(); ?>
    </div>

    <div style="text-align: center; margin-top: 48px;" class="reveal">
      <a href="<?php echo esc_url(add_query_arg('view', 'place-order', slm_portal_url())); ?>" class="btn btn--accent">Book a Shoot</a>
    </div>
  </div>
</section>
