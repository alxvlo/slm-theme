<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');
?>

<section id="home-why" class="home-why" aria-labelledby="home-why-title" aria-label="Why Choose Showcase Listings Media">
  <div class="container">

    <header class="home-why__header js-reveal">
      <h2 id="home-why-title"><?php echo esc_html(get_post_meta($pid, 'hp_why_headline', true) ?: "Why Showcase Listings Media"); ?></h2>
      <p><?php echo esc_html(get_post_meta($pid, 'hp_why_subheadline', true) ?: "We're not just another media company. Here's what makes us different."); ?></p>
    </header>

    <div class="home-why__grid js-reveal">

      <div class="home-why__card">
        <span class="home-why__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M12 2a7 7 0 1 1 0 14A7 7 0 0 1 12 2zm0 0v2m0 12v2M4.2 4.2l1.4 1.4m12.8 12.8 1.4 1.4M2 12h2m16 0h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/>
          </svg>
        </span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_why_1_title', true) ?: "Every Shoot Is Tailored"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_why_1_body', true) ?: "No cookie-cutter approach. Every project is built around your property, your brand, and your goals."); ?></p>
      </div>

      <div class="home-why__card">
        <span class="home-why__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
          </svg>
        </span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_why_2_title', true) ?: "We Shoot for Your Brand"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_why_2_body', true) ?: "We get to know you and your business so the content reflects your identity — not just the space."); ?></p>
      </div>

      <div class="home-why__card">
        <span class="home-why__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 6v6l4 2"/>
          </svg>
        </span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_why_3_title', true) ?: "Fast 24–48 Hour Turnaround"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_why_3_body', true) ?: "Your finished content delivered fast — so you can list, post, and market without delays."); ?></p>
      </div>

      <div class="home-why__card">
        <span class="home-why__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
          </svg>
        </span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_why_4_title', true) ?: "Clear Communication Always"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_why_4_body', true) ?: "From booking to delivery, you always know what's happening. No chasing, no guessing."); ?></p>
      </div>

      <div class="home-why__card">
        <span class="home-why__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
        </span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_why_5_title', true) ?: "Locally Based in Jacksonville"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_why_5_body', true) ?: "We know North Florida — the market, the neighborhoods, and what makes listings stand out here."); ?></p>
      </div>

      <div class="home-why__card">
        <span class="home-why__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M12 20V10M18 20V4M6 20v-4"/>
          </svg>
        </span>
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_why_6_title', true) ?: "Focused on Results"); ?></h3>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_why_6_body', true) ?: "We don't just make things look good — we create content that gets attention, drives action, and helps you grow."); ?></p>
      </div>

    </div>

    <div class="home-why__cta js-reveal">
      <a class="btn" href="tel:+19042945809"><?php echo esc_html(get_post_meta($pid, 'hp_why_cta', true) ?: "CALL US NOW"); ?></a>
    </div>

  </div>
</section>
