<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');
?>

<section id="home-solution" class="home-solution page-section--secondary" aria-labelledby="home-solution-title">
  <div class="container">
    <div class="home-solution__content js-reveal">
      <h2 id="home-solution-title"><?php echo esc_html(get_post_meta($pid, 'hp_solution_headline', true) ?: "We don't just create content — we create attention."); ?></h2>
      <p class="home-solution__lead">
        <?php echo esc_html(get_post_meta($pid, 'hp_solution_body', true) ?: "At Showcase Listings Media, every project is approached with intention.\nWe don't shoot to fill a gallery."); ?>
      </p>
      <div class="home-solution__pillars">
        <div class="home-solution__pillar">
          <span class="home-solution__check" aria-hidden="true">✓</span>
          <div>
            <strong><?php echo esc_html(get_post_meta($pid, 'hp_solution_point_1', true) ?: "Stop the scroll"); ?></strong>
            <p>Content that makes people pause, look twice, and take action.</p>
          </div>
        </div>
        <div class="home-solution__pillar">
          <span class="home-solution__check" aria-hidden="true">✓</span>
          <div>
            <strong><?php echo esc_html(get_post_meta($pid, 'hp_solution_point_2', true) ?: "Elevate your brand"); ?></strong>
            <p>We shoot for your identity, not just the space.</p>
          </div>
        </div>
        <div class="home-solution__pillar">
          <span class="home-solution__check" aria-hidden="true">✓</span>
          <div>
            <strong><?php echo esc_html(get_post_meta($pid, 'hp_solution_point_3', true) ?: "Stand out in a crowded market"); ?></strong>
            <p>Whether it's a listing or a business — we make people pay attention.</p>
          </div>
        </div>
        <?php if ($pt4 = get_post_meta($pid, 'hp_solution_point_4', true)): ?>
        <div class="home-solution__pillar">
          <span class="home-solution__check" aria-hidden="true">✓</span>
          <div>
            <strong><?php echo esc_html($pt4); ?></strong>
            <p>Built for impact.</p>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <a class="btn home-solution__cta" href="<?php echo esc_url(home_url('/portfolio/')); ?>"><?php echo esc_html(get_post_meta($pid, 'hp_solution_cta', true) ?: "See Our Work"); ?></a>
    </div>
  </div>
</section>
