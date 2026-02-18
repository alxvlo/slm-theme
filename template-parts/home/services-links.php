<?php
if (!defined('ABSPATH')) exit;

$services_url = home_url('/services/');
$listing_packages_url = $services_url . '#listing-media-packages';
$social_packages_url = $services_url . '#social-content-packages';
$monthly_memberships_url = $services_url . '#monthly-content-memberships';
$agent_memberships_url = $services_url . '#listings-agent-memberships';
$addons_url = $services_url . '#popular-add-ons';
?>

<section class="home-services" aria-labelledby="home-services-title">
  <div class="container">
    <header class="home-services__header">
      <h1 id="home-services-title">What We Offer</h1>
      <p>Explore the current offerings exactly as structured in your package and membership model.</p>
    </header>

    <div class="home-services__grid">
      <a class="home-serviceCard" href="<?php echo esc_url($listing_packages_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M4 10.5 12 4l8 6.5V20H4v-9.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M9 20v-5h6v5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
          </svg>
        </span>
        <h2>Listing Packages</h2>
        <p>Media packages for active listings, Zillow workflows, and lot or land marketing.</p>
      </a>

      <a class="home-serviceCard" href="<?php echo esc_url($social_packages_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <rect x="4" y="5" width="16" height="14" rx="3" stroke="currentColor" stroke-width="1.8"/>
            <path d="M8 10h8M8 14h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </span>
        <h2>Social Packages</h2>
        <p>One-time content packages for reels, talking head clips, and branded social assets.</p>
      </a>

      <a class="home-serviceCard" href="<?php echo esc_url($monthly_memberships_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <rect x="4" y="5" width="16" height="15" rx="2.5" stroke="currentColor" stroke-width="1.8"/>
            <path d="M8 3v4M16 3v4M7 11h10M7 15h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </span>
        <h2>Monthly Memberships</h2>
        <p>Recurring monthly plans for consistent content production and brand growth.</p>
      </a>

      <a class="home-serviceCard" href="<?php echo esc_url($agent_memberships_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <circle cx="8" cy="9" r="3" stroke="currentColor" stroke-width="1.8"/>
            <circle cx="16.5" cy="10.5" r="2.5" stroke="currentColor" stroke-width="1.8"/>
            <path d="M3.5 19c.9-2.7 3.2-4.2 6.5-4.2S15.6 16.3 16.5 19M14.5 19c.5-1.8 1.9-2.9 4.1-2.9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </span>
        <h2>Listings-Agent Memberships</h2>
        <p>Tiered options for agents combining listing shoots, AI edits, and social support.</p>
      </a>

      <a class="home-serviceCard" href="<?php echo esc_url($addons_url); ?>">
        <span class="home-serviceCard__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path d="M12 4v16M4 12h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </span>
        <h2>Add-Ons</h2>
        <p>Optional add-ons like Zillow, drone video, twilight, staging, and heavy edits.</p>
      </a>
    </div>
  </div>
</section>
