<?php
if (!defined('ABSPATH')) exit;

$pid = get_option('page_on_front');

$q = new WP_Query([
  'post_type'      => 'testimonial',
  'post_status'    => 'publish',
  'posts_per_page' => 6,
  'no_found_rows'  => true,
]);

if (!$q->have_posts()) return;

$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());

$star_svg = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17.3 5.8 20.8l1.2-7.1L1.8 8.7l7.2-1L12 1.2l3 6.5 7.2 1-5.2 5 1.2 7.1L12 17.3Z" fill="currentColor"/></svg>';

// Before/after images from ACF or fallback
$before_meta = get_post_meta((int) $pid, 'before_image', true);
$before = $before_meta ? (is_numeric($before_meta) ? wp_get_attachment_url($before_meta) : $before_meta) : get_template_directory_uri() . '/assets/img/Homepage4.jpg';

$after_meta = get_post_meta((int) $pid, 'after_image', true);
$after  = $after_meta ? (is_numeric($after_meta) ? wp_get_attachment_url($after_meta) : $after_meta) : get_template_directory_uri() . '/assets/img/Homepage5.jpg';
?>

<!-- Testimonials grid -->
<section class="home-testimonials" aria-labelledby="home-testimonials-title">
  <div class="container">

    <header class="home-testimonials__header js-reveal">
      <span class="section-eyebrow"><?php echo esc_html(get_post_meta($pid, 'hp_proof_eyebrow', true) ?: 'Client Results'); ?></span>
      <h2 id="home-testimonials-title"><?php echo esc_html(get_post_meta($pid, 'hp_proof_headline', true) ?: 'Real Results. Real Clients.'); ?></h2>
      <p><?php echo esc_html(get_post_meta($pid, 'hp_proof_subheadline', true) ?: "Don't take our word for it — here's what agents and businesses across North Florida are saying."); ?></p>
    </header>

    <div class="home-testimonials__grid js-reveal">
      <?php while ($q->have_posts()): $q->the_post(); ?>
        <?php
          $id       = get_the_ID();
          $rating   = max(1, min(5, (int) get_post_meta($id, 'slm_testimonial_rating', true) ?: 5));
          $source   = (string) get_post_meta($id, 'slm_testimonial_source', true);
          $role     = (string) get_post_meta($id, 'slm_testimonial_role', true);
          $location = (string) get_post_meta($id, 'slm_testimonial_location', true);
          $name     = get_the_title() ?: 'Client';
          $meta_parts = array_filter([$role, $location]);
          $meta_line  = implode(' · ', $meta_parts);
          $content    = wp_strip_all_tags(get_the_content());
          $content    = str_replace('Real Tours', 'Showcase Listings Media', $content);
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
                <?php if ($meta_line): ?>
                  <div class="tCard__meta"><?php echo esc_html($meta_line); ?></div>
                <?php endif; ?>
              </div>
            </div>
            <div class="tCard__stars" aria-label="<?php echo esc_attr($rating . ' out of 5 stars'); ?>">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="tStar <?php echo $i <= $rating ? 'is-on' : 'is-off'; ?>"><?php echo $star_svg; ?></span>
              <?php endfor; ?>
            </div>
          </header>
          <div class="tCard__body">
            <p><?php echo esc_html(trim($content)); ?></p>
          </div>
          <?php if ($source): ?>
          <footer class="tCard__foot">
            <span class="tCard__source"><?php echo esc_html($source); ?></span>
          </footer>
          <?php endif; ?>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

  </div>
</section>

<!-- Stats bar -->
<section class="home-stats" aria-label="Performance statistics">
  <div class="container">
    <div class="home-stats__items">
      <div class="home-stats__item">
        <div class="home-stats__num"><?php echo esc_html(get_post_meta($pid, 'hp_stat_1_number', true) ?: '200+'); ?></div>
        <div class="home-stats__label"><?php echo esc_html(get_post_meta($pid, 'hp_stat_1_label', true) ?: 'Shoots Completed'); ?></div>
      </div>
      <div class="home-stats__divider" aria-hidden="true"></div>
      <div class="home-stats__item">
        <div class="home-stats__num"><?php echo esc_html(get_post_meta($pid, 'hp_stat_2_number', true) ?: '24hr'); ?></div>
        <div class="home-stats__label"><?php echo esc_html(get_post_meta($pid, 'hp_stat_2_label', true) ?: 'Standard Turnaround'); ?></div>
      </div>
      <div class="home-stats__divider" aria-hidden="true"></div>
      <?php if ($val3 = get_post_meta($pid, 'hp_stat_3_number', true)): ?>
      <div class="home-stats__item">
        <div class="home-stats__num"><?php echo esc_html($val3); ?></div>
        <div class="home-stats__label"><?php echo esc_html(get_post_meta($pid, 'hp_stat_3_label', true) ?: ''); ?></div>
      </div>
      <div class="home-stats__divider" aria-hidden="true"></div>
      <?php endif; ?>
      <div class="home-stats__item">
        <div class="home-stats__num"><?php echo esc_html(get_post_meta($pid, 'hp_stat_4_number', true) ?: '5★'); ?></div>
        <div class="home-stats__label"><?php echo esc_html(get_post_meta($pid, 'hp_stat_4_label', true) ?: 'Client Rating'); ?></div>
      </div>
    </div>
  </div>
</section>

<!-- Before / After comparison -->
<section class="home-before-after" aria-labelledby="home-ba-title">
  <div class="container">
    <h2 id="home-ba-title" class="js-reveal">See the Difference</h2>
    <div class="home-ba__grid js-reveal">
      <div class="home-ba__panel">
        <div class="home-ba__label">Before</div>
        <img src="<?php echo esc_url($before); ?>" alt="Before — standard listing photo" loading="lazy" decoding="async">
      </div>
      <div class="home-ba__panel home-ba__panel--after">
        <div class="home-ba__label home-ba__label--after">After</div>
        <img src="<?php echo esc_url($after); ?>" alt="After — Showcase Listings Media professional photo" loading="lazy" decoding="async">
      </div>
    </div>
    <div class="home-before-after__cta js-reveal">
      <a class="btn" href="<?php echo esc_url($cta_url); ?>">
        <?php echo esc_html(get_post_meta($pid, 'hp_proof_cta', true) ?: 'Book Your Next Shoot'); ?>
      </a>
    </div>
  </div>
</section>
