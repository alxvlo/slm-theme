<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = is_user_logged_in();
$contact_url  = home_url('/contact/');
$services_url = home_url('/services/');
$pid = get_option('page_on_front');
?>

<section id="home-who" class="home-who" aria-labelledby="home-who-title">
  <div class="container">
    <header class="home-who__header js-reveal">
      <h2 id="home-who-title"><?php echo esc_html(get_post_meta($pid, 'hp_who_headline', true) ?: "Built for agents. Designed for brands."); ?></h2>
      <p><?php echo esc_html(get_post_meta($pid, 'hp_who_subheadline', true) ?: "We serve two audiences — and we speak directly to both."); ?></p>
    </header>

    <div class="home-who__grid js-reveal">
      <div class="home-who__col">
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_who_agents_title', true) ?: "For Real Estate Agents"); ?></h3>
        <ul>
          <li><?php echo esc_html(get_post_meta($pid, 'hp_who_agents_bullet_1', true) ?: "Win more listings with stronger visuals"); ?></li>
          <li><?php echo esc_html(get_post_meta($pid, 'hp_who_agents_bullet_2', true) ?: "Stand out from other agents in your market"); ?></li>
          <li><?php echo esc_html(get_post_meta($pid, 'hp_who_agents_bullet_3', true) ?: "Showcase homes at a higher level"); ?></li>
          <li><?php echo esc_html(get_post_meta($pid, 'hp_who_agents_bullet_4', true) ?: "Build a recognizable personal brand"); ?></li>
        </ul>
        <a class="btn btn--outline-light home-who__cta" href="<?php echo esc_url($services_url); ?>"><?php echo esc_html(get_post_meta($pid, 'hp_who_agents_cta', true) ?: "View Agent Services"); ?></a>
      </div>

      <div class="home-who__divider" aria-hidden="true"></div>

      <div class="home-who__col">
        <h3><?php echo esc_html(get_post_meta($pid, 'hp_who_biz_title', true) ?: "For Businesses"); ?></h3>
        <ul>
          <li><?php echo esc_html(get_post_meta($pid, 'hp_who_biz_bullet_1', true) ?: "Attract more clients with professional content"); ?></li>
          <li><?php echo esc_html(get_post_meta($pid, 'hp_who_biz_bullet_2', true) ?: "Create a strong, lasting first impression"); ?></li>
          <li><?php echo esc_html(get_post_meta($pid, 'hp_who_biz_bullet_3', true) ?: "Elevate your online presence and visibility"); ?></li>
          <li><?php echo esc_html(get_post_meta($pid, 'hp_who_biz_bullet_4', true) ?: "Turn views and clicks into real customers"); ?></li>
        </ul>
        <a class="btn btn--outline-light home-who__cta" href="<?php echo esc_url($services_url); ?>"><?php echo esc_html(get_post_meta($pid, 'hp_who_biz_cta', true) ?: "View Business Services"); ?></a>
      </div>
    </div>

    <div class="home-who__bottom js-reveal">
      <a class="btn btn--accent" href="<?php echo esc_url($contact_url); ?>"><?php echo esc_html(get_post_meta($pid, 'hp_who_main_cta', true) ?: "Let's Create Your Content"); ?></a>
    </div>
  </div>
</section>
