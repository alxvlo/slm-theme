<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');
?>

<section id="home-problem" class="home-problem" aria-labelledby="home-problem-title">
  <div class="container">
    <div class="home-problem__content js-reveal">
      <h2 id="home-problem-title"><?php echo esc_html(get_post_meta($pid, 'hp_problem_headline', true) ?: "Looking good isn't enough anymore."); ?></h2>
      <p class="home-problem__lead"><?php echo esc_html(get_post_meta($pid, 'hp_problem_intro', true) ?: "Most listings and businesses blend in."); ?></p>
      <ul class="home-problem__list">
        <li><?php echo esc_html(get_post_meta($pid, 'hp_problem_bullet_1', true) ?: "Photos look the same"); ?></li>
        <li><?php echo esc_html(get_post_meta($pid, 'hp_problem_bullet_2', true) ?: "Videos feel generic"); ?></li>
        <li><?php echo esc_html(get_post_meta($pid, 'hp_problem_bullet_3', true) ?: "Content gets ignored"); ?></li>
        <?php if ($bullet_4 = get_post_meta($pid, 'hp_problem_bullet_4', true)): ?>
          <li><?php echo esc_html($bullet_4); ?></li>
        <?php endif; ?>
      </ul>
      <p class="home-problem__close">
        <?php echo esc_html(get_post_meta($pid, 'hp_problem_closing', true) ?: "If your marketing isn't stopping the scroll, it's costing you opportunities."); ?>
      </p>
      <?php if ($btn = get_post_meta($pid, 'hp_problem_cta', true)): ?>
        <a class="btn home-problem__cta" style="margin-top:20px;" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php echo esc_html($btn); ?></a>
      <?php endif; ?>
    </div>
  </div>
</section>
