<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = is_user_logged_in();
$book_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
$pid = get_option('page_on_front');
?>

<section class="home-how" aria-labelledby="home-how-title">
  <div class="container">

    <header class="home-how__header js-reveal">
      <span class="section-eyebrow"><?php echo esc_html(get_post_meta($pid, 'hp_process_eyebrow', true) ?: 'Simple Process'); ?></span>
      <h2 id="home-how-title"><?php echo esc_html(get_post_meta($pid, 'hp_process_headline', true) ?: 'How It Works'); ?></h2>
      <p><?php echo esc_html(get_post_meta($pid, 'hp_process_subheadline', true) ?: 'From booking to delivery — here\'s exactly what to expect.'); ?></p>
    </header>

    <div class="home-how__steps">

      <div class="home-how__connector" aria-hidden="true"></div>

      <?php
      $steps = [
        ['num' => '01', 'title_key' => 'hp_step_1_title', 'body_key' => 'hp_step_1_body', 'default_title' => 'Book your shoot',              'default_body' => 'Choose your service, pick a date, and schedule online in minutes.'],
        ['num' => '02', 'title_key' => 'hp_step_2_title', 'body_key' => 'hp_step_2_body', 'default_title' => 'We capture your content',      'default_body' => 'Our team arrives prepared to capture your property or brand at its best.'],
        ['num' => '03', 'title_key' => 'hp_step_3_title', 'body_key' => 'hp_step_3_body', 'default_title' => 'Delivered within 24–48 hours',  'default_body' => 'Your finished assets land in your inbox, ready for MLS, social, and marketing.'],
        ['num' => '04', 'title_key' => 'hp_step_4_title', 'body_key' => 'hp_step_4_body', 'default_title' => 'You post, market, and stand out','default_body' => 'Go live with content that stops the scroll and gets your listing or brand noticed.'],
      ];
      foreach ($steps as $step):
      ?>
        <article class="home-howStep js-reveal">
          <div class="home-howStep__num" aria-hidden="true"><?php echo esc_html($step['num']); ?></div>
          <h3><?php echo esc_html(get_post_meta($pid, $step['title_key'], true) ?: $step['default_title']); ?></h3>
          <p><?php echo esc_html(get_post_meta($pid, $step['body_key'], true) ?: $step['default_body']); ?></p>
        </article>
      <?php endforeach; ?>

    </div>

    <div class="home-how__cta js-reveal">
      <a class="btn" href="<?php echo esc_url($book_url); ?>">
        <?php echo esc_html(get_post_meta($pid, 'hp_process_cta', true) ?: 'Book Your Shoot Now'); ?>
      </a>
    </div>

  </div>
</section>
