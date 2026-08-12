<?php
if (!defined('ABSPATH')) exit;

$service_area_url = function_exists('slm_service_area_url')
  ? slm_service_area_url()
  : home_url('/service-area/');
?>

<section id="home-service-area" class="home-service-area" aria-labelledby="home-service-area-title" aria-label="Service Area">
  <div class="container">
    <div class="home-service-area__inner js-reveal">
      <h2 id="home-service-area-title">Serving Jacksonville &amp; North Florida</h2>
      <p class="home-service-area__lead">From Ponte Vedra to Fleming Island, we cover six counties across Northeast Florida.</p>
      <ul class="home-service-area__list" aria-label="Counties served">
        <li>Duval</li>
        <li>St. Johns</li>
        <li>Clay</li>
        <li>Nassau</li>
        <li>Putnam</li>
        <li>Baker</li>
      </ul>
      <a class="btn btn--outline" href="<?php echo esc_url($service_area_url); ?>">See Our Service Area</a>
    </div>
  </div>
</section>
