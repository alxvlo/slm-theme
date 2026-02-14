<?php
if (!defined('ABSPATH')) exit;

$resolve_page = static function (string $template_file, string $fallback_path): string {
  if (function_exists('slm_page_url_by_template')) {
    return slm_page_url_by_template($template_file, $fallback_path);
  }
  return home_url($fallback_path);
};

$photo_url = $resolve_page('page-service-re-photography.php', '/services/real-estate-photography/');
$video_url = $resolve_page('page-service-re-videography.php', '/services/real-estate-videography/');
$drone_url = $resolve_page('page-service-drone-photography.php', '/services/drone-photography/');
$drone_video_url = $resolve_page('page-service-drone-videography.php', '/services/drone-videography/');
$tour_url = $resolve_page('page-service-virtual-tours.php', '/services/virtual-tours/');
$floor_plans_url = $resolve_page('page-service-floor-plans.php', '/services/floor-plans/');
$twilight_url = $resolve_page('page-service-twilight-photography.php', '/services/twilight-photography/');
?>

<section class="home-services" aria-labelledby="home-services-title">
  <div class="container">
    <header class="home-services__header">
      <h1 id="home-services-title">Our Services</h1>
      <p>From stunning photography to immersive virtual tours, we have everything you need to showcase your properties.</p>
    </header>

    <div class="home-services__grid">
      <a class="home-serviceCard" href="<?php echo esc_url($photo_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M8 7.5 9.5 5h5L16 7.5h2.5A1.5 1.5 0 0 1 20 9v8a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 17V9a1.5 1.5 0 0 1 1.5-1.5H8Z" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="12" cy="13" r="3" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </span>
        <h2>Professional Photography</h2>
        <p>Stunning HDR photos that showcase your listing at its finest.</p>
      </a>

      <a class="home-serviceCard" href="<?php echo esc_url($video_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <rect x="4" y="7" width="11" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M15 10.5 20 8v8l-5-2.5v-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
          </svg>
        </span>
        <h2>Cinematic Video Tours</h2>
        <p>Engaging walkthrough videos that bring properties to life.</p>
      </a>

      <a class="home-serviceCard" href="<?php echo esc_url($drone_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="m4 13 4.2 1 2.9-2.9L8.6 9.5 9.8 8.3l2.4 2.4 3.1-3.1c1-1 2.6-1 3.6 0l.2.2c1 1 1 2.6 0 3.6l-3.1 3.1 2.4 2.4-1.2 1.2-1.6-2.5-2.9 2.9L13 22H11l.2-4.1L8.3 15l-3.9.2.6-2.2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
          </svg>
        </span>
        <h2>Aerial Drone Shots</h2>
        <p>Breathtaking aerial perspectives of property and surroundings.</p>
      </a>

      <a class="home-serviceCard" href="<?php echo esc_url($tour_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
            <path d="M4 12h16M12 4a13 13 0 0 1 0 16M12 4a13 13 0 0 0 0 16" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </span>
        <h2>3D Virtual Tours</h2>
        <p>Immersive 360 degree experiences for remote buyers.</p>
      </a>

      <a class="home-serviceCard" href="<?php echo esc_url($drone_video_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <rect x="6" y="7" width="9" height="10" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M15 10.5 20 8v8l-5-2.5v-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M4 9.5h2M4 14.5h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </span>
        <h2>Drone Videography</h2>
        <p>Cinematic aerial video coverage for standout listing marketing.</p>
      </a>

      <a class="home-serviceCard" href="<?php echo esc_url($floor_plans_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M4 4h16v16H4V4Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 4v8H4M20 12h-8v8" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </span>
        <h2>Floor Plans</h2>
        <p>Clear layout visuals that help buyers understand the space instantly.</p>
      </a>

      <a class="home-serviceCard" href="<?php echo esc_url($twilight_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M12 3v3M6.2 5.2l2.1 2.1M3 11h3M6.2 16.8l2.1-2.1M18 11h3M15.7 7.3l2.1-2.1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </span>
        <h2>Twilight Photography</h2>
        <p>Premium dusk imagery that gives listings a dramatic luxury look.</p>
      </a>
    </div>
  </div>
</section>
