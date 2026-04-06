<?php
if (!defined('ABSPATH')) exit;

$pid = get_option('page_on_front');

$q = new WP_Query([
  'post_type' => 'testimonial',
  'post_status' => 'publish',
  'posts_per_page' => 6,
  'no_found_rows' => true,
]);

if (!$q->have_posts()) return;

$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());

$star_svg = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17.3 5.8 20.8l1.2-7.1L1.8 8.7l7.2-1L12 1.2l3 6.5 7.2 1-5.2 5 1.2 7.1L12 17.3Z" fill="currentColor"/></svg>';
?>

<section class="home-testimonials" aria-labelledby="home-testimonials-title">
  <div class="container">
    <header class="home-testimonials__header">
      <h2 id="home-testimonials-title"><?php echo esc_html(get_post_meta($pid, 'hp_proof_headline', true) ?: "Real Results. Real Clients."); ?></h2>
      <p><?php echo esc_html(get_post_meta($pid, 'hp_proof_subheadline', true) ?: "Don't take our word for it — here's what agents and businesses across North Florida are saying."); ?></p>
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
              $content = str_replace('Real Tours', 'Showcase Listings Media', $content);
              echo '<p>' . esc_html(trim($content)) . '</p>';
            ?>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div> <!-- end .home-testimonials__grid -->
  </div> <!-- end .container -->
</section>

<section style="background:#0D1B2A; padding:80px 20px; text-align:center;">
  <div style="display:flex; justify-content:center; align-items:center; gap:80px; flex-wrap:wrap; max-width:600px; margin:0 auto;">
    <div style="text-align:center;">
      <div style="font-family:'Outfit',sans-serif; font-size:clamp(3rem,6vw,5rem); font-weight:800; color:#C9922A; line-height:1;"><?php echo esc_html(get_post_meta($pid, 'hp_stat_1_number', true) ?: "XX"); ?></div>
      <div style="font-family:'Plus Jakarta Sans',sans-serif; font-size:0.75rem; font-weight:600; color:#ffffff; text-transform:uppercase; letter-spacing:0.1em; margin-top:8px;"><?php echo esc_html(get_post_meta($pid, 'hp_stat_1_label', true) ?: "Days to Close"); ?></div>
    </div>
    <div style="width:1px; height:60px; background:rgba(255,255,255,0.2);"></div>
    <div style="text-align:center;">
      <div style="font-family:'Outfit',sans-serif; font-size:clamp(3rem,6vw,5rem); font-weight:800; color:#C9922A; line-height:1;"><?php echo esc_html(get_post_meta($pid, 'hp_stat_2_number', true) ?: "XX%"); ?></div>
      <div style="font-family:'Plus Jakarta Sans',sans-serif; font-size:0.75rem; font-weight:600; color:#ffffff; text-transform:uppercase; letter-spacing:0.1em; margin-top:8px;"><?php echo esc_html(get_post_meta($pid, 'hp_stat_2_label', true) ?: "Above Asking"); ?></div>
    </div>
  </div>
</section>

<?php
// Resolve image URLs accurately handling meta IDs
$before_meta = get_post_meta(get_option('page_on_front') ?: get_the_ID(), 'before_image', true);
$before = $before_meta ? (is_numeric($before_meta) ? wp_get_attachment_url($before_meta) : $before_meta) : get_template_directory_uri() . '/assets/img/Homepage4.jpg';

$after_meta = get_post_meta(get_option('page_on_front') ?: get_the_ID(), 'after_image', true);
$after  = $after_meta ? (is_numeric($after_meta) ? wp_get_attachment_url($after_meta) : $after_meta) : get_template_directory_uri() . '/assets/img/Homepage5.jpg';
?>
<section style="background:#EEF2F7; padding:80px 20px;">
  <div style="max-width:900px; margin:0 auto; display:grid; grid-template-columns:1fr 1fr; gap:24px;">
    <div style="border:2px dashed #C9922A; border-radius:12px; overflow:hidden; background:#fff;">
      <div style="padding:16px 20px 8px; font-family:'Plus Jakarta Sans',sans-serif; font-size:0.75rem; font-weight:700; color:#C9922A; text-transform:uppercase; letter-spacing:0.1em;">Before</div>
      <img src="<?php echo esc_url($before); ?>" alt="Before" style="width:100%; aspect-ratio:4/3; object-fit:cover; display:block;">
    </div>
    <div style="border:2px dashed #C9922A; border-radius:12px; overflow:hidden; background:#fff;">
      <div style="padding:16px 20px 8px; font-family:'Plus Jakarta Sans',sans-serif; font-size:0.75rem; font-weight:700; color:#C9922A; text-transform:uppercase; letter-spacing:0.1em;">After</div>
      <img src="<?php echo esc_url($after); ?>" alt="After" style="width:100%; aspect-ratio:4/3; object-fit:cover; display:block;">
    </div>
  </div>

  <div class="home-testimonials__cta js-reveal" style="text-align:center; margin-top: 60px;">
    <a class="btn" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html(get_post_meta($pid, 'hp_proof_cta', true) ?: "Book Your Next Shoot"); ?></a>
  </div>
</section>
