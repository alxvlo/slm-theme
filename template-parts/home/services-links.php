<?php
if (!defined('ABSPATH')) exit;

$services_url         = home_url('/services/');
$photography_url      = $services_url . '#listing-media-packages';
$video_url            = $services_url . '#listing-media-packages';
$social_url           = $services_url . '#social-content-packages';
$branding_url         = $services_url . '#business-branding';
$drone_url            = $services_url . '#drone-services';
$addons_url           = $services_url . '#popular-add-ons';
$pid = get_option('page_on_front');?>

<section id="services" class="home-services page-section--secondary" aria-label="Real Estate Media Services">
  <div class="container">
    <header class="home-services__header">
      <h1 id="home-services-title"><?php echo esc_html(get_post_meta($pid, 'hp_services_headline', true) ?: "What We Offer"); ?></h1>
      <p><?php echo esc_html(get_post_meta($pid, 'hp_services_subheadline', true) ?: "Professional photo, video, and content services — built for agents and businesses who want to stand out."); ?></p>
    </header>

    <div class="home-services__grid">

      <!-- Icon: Real Estate Photography -->
      <a class="home-serviceCard" href="<?php echo esc_url($photography_url); ?>" aria-label="Real Estate Photography">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </span>
        <h2><?php echo esc_html(get_post_meta($pid, 'hp_service_1_title', true) ?: "Real Estate Photography"); ?></h2>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_service_1_body', true) ?: "MLS-ready photos that make listings stop the scroll and attract buyers."); ?></p>
      </a>

      <!-- Icon: Cinematic Listing Videos -->
      <a class="home-serviceCard" href="<?php echo esc_url($video_url); ?>" aria-label="Cinematic Listing Videos">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <rect x="2" y="7" width="15" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="m17 9 5-2v10l-5-2V9Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
          </svg>
        </span>
        <h2><?php echo esc_html(get_post_meta($pid, 'hp_service_2_title', true) ?: "Cinematic Listing Videos"); ?></h2>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_service_2_body', true) ?: "Smooth, modern walkthroughs built for MLS and social media."); ?></p>
      </a>

      <!-- Icon: Social Media Content -->
      <a class="home-serviceCard" href="<?php echo esc_url($social_url); ?>" aria-label="Social Media Content">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <rect x="5" y="2" width="14" height="20" rx="3" stroke="currentColor" stroke-width="1.8"/>
            <path d="M10 9.5 15 12l-5 2.5V9.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
          </svg>
        </span>
        <h2><?php echo esc_html(get_post_meta($pid, 'hp_service_3_title', true) ?: "Social Media Content"); ?></h2>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_service_3_body', true) ?: "Custom Reels and Shorts designed to grow your presence and drive engagement."); ?></p>
      </a>

      <!-- Icon: Business Branding Content -->
      <a class="home-serviceCard" href="<?php echo esc_url($branding_url); ?>" aria-label="Business Branding Content">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <rect x="2" y="7" width="20" height="14" rx="2.5" stroke="currentColor" stroke-width="1.8"/>
            <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 12v2M6 14h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </span>
        <h2><?php echo esc_html(get_post_meta($pid, 'hp_service_4_title', true) ?: "Business Branding Content"); ?></h2>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_service_4_body', true) ?: "Professional photo and video that attracts clients and elevates your brand."); ?></p>
      </a>

      <!-- Icon: Drone Photography & Video -->
      <a class="home-serviceCard" href="<?php echo esc_url($drone_url); ?>" aria-label="Drone Photography & Video">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="2" stroke="currentColor" stroke-width="1.8"/>
            <path d="M5 5.5 8.5 9M19 5.5 15.5 9M5 18.5 8.5 15M19 18.5 15.5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="4" cy="5" r="2" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="20" cy="5" r="2" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="4" cy="19" r="2" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="20" cy="19" r="2" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </span>
        <h2><?php echo esc_html(get_post_meta($pid, 'hp_service_5_title', true) ?: "Drone Photography & Video"); ?></h2>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_service_5_body', true) ?: "Aerial imagery that showcases land, views, and property surroundings."); ?></p>
      </a>

      <!-- Icon: Add-Ons & Memberships -->
      <a class="home-serviceCard" href="<?php echo esc_url($addons_url); ?>" aria-label="Add-Ons & Memberships">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M12 2 2 7l10 5 10-5-10-5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M2 17l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
        <h2><?php echo esc_html(get_post_meta($pid, 'hp_service_6_title', true) ?: "Zillow / Marketing Add-Ons"); ?></h2>
        <p><?php echo esc_html(get_post_meta($pid, 'hp_service_6_body', true) ?: "Zillow walkthroughs, marketing add-ons, and partnership packages available."); ?></p>
      </a>

    </div>

    <div class="home-services__footer">
      <p class="home-services__pricing"><?php echo esc_html(get_post_meta($pid, 'hp_services_pricing_line', true) ?: "Pricing starting as low as $145 — no membership required to book."); ?></p>
      <a class="btn btn--outline home-services__viewAll" href="<?php echo esc_url($services_url); ?>">View All Services</a>
    </div>
  </div>
</section>
