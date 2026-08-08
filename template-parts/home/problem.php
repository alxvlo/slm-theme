<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');
?>

<section id="home-problem" class="home-problem" aria-labelledby="home-problem-title">
  <div class="container">

    <header class="home-problem__header js-reveal">
      <span class="section-eyebrow"><?php echo esc_html(get_post_meta($pid, 'hp_problem_eyebrow', true) ?: 'The Problem'); ?></span>
      <h2 id="home-problem-title"><?php echo esc_html(get_post_meta($pid, 'hp_problem_headline', true) ?: "Looking good isn't enough anymore."); ?></h2>
      <p class="home-problem__lead"><?php echo esc_html(get_post_meta($pid, 'hp_problem_intro', true) ?: 'Most listings and businesses blend in.'); ?></p>
    </header>

    <div class="home-problem__cards js-reveal">

      <div class="home-problem__card">
        <span class="home-problem__card-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="5"/><path d="M3 21c0-5 3.6-8 9-8s9 3 9 8"/></svg>
        </span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_problem_card_1_title', true) ?: 'Buyers scroll past bland listings'); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_problem_bullet_1', true) ?: 'Photos look the same — nothing stops the scroll or attracts attention.'); ?></p>
      </div>

      <div class="home-problem__card">
        <span class="home-problem__card-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 10 8.5 16.5M9.5 10 3 16.5M21 5.5l-4.5 4.5M3 3l18 18"/></svg>
        </span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_problem_card_2_title', true) ?: 'Generic content gets ignored'); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_problem_bullet_2', true) ?: 'Videos feel flat and forgettable — they don\'t tell a story or drive action.'); ?></p>
      </div>

      <div class="home-problem__card">
        <span class="home-problem__card-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_problem_card_3_title', true) ?: "Missed opportunities cost money"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_problem_closing', true) ?: "If your marketing isn't stopping the scroll, it's costing you opportunities."); ?></p>
      </div>

    </div>

  </div>
</section>
