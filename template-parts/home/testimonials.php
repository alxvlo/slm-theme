<?php
if (!defined('ABSPATH')) exit;

$pid = get_option('page_on_front');

// posts_per_page => -1 so a newly published review can never be silently
// truncated; menu_order gives Brittney explicit control of the running order
// (WP_Query falls back to date DESC when every menu_order is 0).
$q = new WP_Query([
  'post_type' => 'testimonial',
  'post_status' => 'publish',
  'posts_per_page' => -1,
  'orderby' => 'menu_order',
  'order' => 'ASC',
  'no_found_rows' => true,
]);

// NOTE: no early return here. This partial also renders the before/after grid
// and the "Book Your Next Shoot" CTA below, and returning early above them
// removed both from the homepage whenever no testimonials were published.
$has_testimonials = $q->have_posts();

$cta_url = slm_book_url();

$star_svg = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17.3 5.8 20.8l1.2-7.1L1.8 8.7l7.2-1L12 1.2l3 6.5 7.2 1-5.2 5 1.2 7.1L12 17.3Z" fill="currentColor"/></svg>';
?>

<?php if ($has_testimonials): ?>
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
                <?php
                  // The publish date is deliberately not rendered. These
                  // reviews arrived by email and were entered in one sitting,
                  // so every card would carry the same date and read as
                  // bulk-created rather than as reviews received over time.
                ?>
              </div>
            </div>
          </header>

          <div class="tCard__body">
            <?php
              // Never rewrite a customer's words. The old str_replace() here
              // silently swapped "Real Tours" for the current brand name.
              //
              // Strip tags first so the field can never emit raw HTML, then
              // escape, then rebuild paragraphs from the surviving blank
              // lines — multi-paragraph reviews used to collapse into one
              // run-on block.
              $content = wp_strip_all_tags(get_the_content());
              foreach (preg_split('/\R{2,}/', trim($content)) ?: [] as $para) {
                $para = trim($para);
                if ($para !== '') {
                  echo '<p>' . nl2br(esc_html($para)) . '</p>';
                }
              }
            ?>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div> <!-- end .home-testimonials__grid -->
  </div> <!-- end .container -->
</section>
<?php endif; ?>

<?php
// The two stat tiles that used to sit here ("8 — Avg. Days to Sell" and
// "14% — Above Asking Price") were removed 2026-08-12. Both were unsourced
// hardcoded fallbacks that would have rendered as soon as this section became
// visible. They can return once real figures are supplied.
?>

<?php
// Resolve image URLs accurately handling meta IDs
$before_meta = get_post_meta(get_option('page_on_front') ?: get_the_ID(), 'before_image', true);
$before = $before_meta ? (is_numeric($before_meta) ? wp_get_attachment_url($before_meta) : $before_meta) : '';

$after_meta = get_post_meta(get_option('page_on_front') ?: get_the_ID(), 'after_image', true);
$after  = $after_meta ? (is_numeric($after_meta) ? wp_get_attachment_url($after_meta) : $after_meta) : '';

// Only show the Before/After grid when BOTH real images are set.
$has_before_after = ($before !== '' && $after !== '');
?>
<section style="background:#EEF2F7; padding:80px 20px;">
  <?php if ($has_before_after): ?>
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
  <?php endif; ?>

  <div class="home-testimonials__cta js-reveal" style="text-align:center;<?php echo $has_before_after ? ' margin-top: 60px;' : ''; ?>">
    <a class="btn" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html(get_post_meta($pid, 'hp_proof_cta', true) ?: "Book Your Next Shoot"); ?></a>
  </div>
</section>
