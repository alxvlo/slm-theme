<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = is_user_logged_in();
$book_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
$pid = get_option('page_on_front');
?>

<section class="home-how page-section--secondary" aria-labelledby="home-how-title">
  <div class="container">
    <header class="home-how__header">
      <h2 id="home-how-title"><?php echo esc_html(get_post_meta($pid, 'hp_process_headline', true) ?: "How It Works"); ?></h2>
      <p><?php echo esc_html(get_post_meta($pid, 'hp_process_subheadline', true) ?: "From booking to delivery — here's exactly what to expect."); ?></p>
    </header>

    <div class="home-how__grid">
      <article class="home-howCard">
        <span class="home-howCard__step">1</span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_step_1_title', true) ?: "Book your shoot"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_step_1_body', true) ?: "Choose your service, pick a date, and schedule online in minutes."); ?></p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">2</span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_step_2_title', true) ?: "We capture your content"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_step_2_body', true) ?: "Our team arrives prepared to capture your property or brand at its best."); ?></p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">3</span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_step_3_title', true) ?: "Edits delivered within 24–48 hours"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_step_3_body', true) ?: "Your finished assets land in your inbox, ready for MLS, social, and marketing."); ?></p>
      </article>

      <article class="home-howCard">
        <span class="home-howCard__step">4</span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_step_4_title', true) ?: "You post, market, and stand out"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_step_4_body', true) ?: "Go live with content that stops the scroll and gets your listing or brand noticed."); ?></p>
      </article>
    </div>

    <div class="home-how__cta">
      <a class="btn home-heroSlider__btn--primary" href="<?php echo esc_url($book_url); ?>"><?php echo esc_html(get_post_meta($pid, 'hp_process_cta', true) ?: "Book Your Shoot Now"); ?></a>
    </div>
  </div>
</section>
