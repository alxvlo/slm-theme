<?php
/**
 * Template Name: Services
 */
if (!defined('ABSPATH'))
  exit;

// SEO
add_filter('pre_get_document_title', function () {
  return 'Real Estate Photography North Florida | Showcase Listings Media';
}, 99);
add_action('wp_head', function () {
  echo '<meta name="description" content="Professional real estate photography, videography, and branding content for agents and businesses in Jacksonville and North Florida.">' . "\n";
  echo '<meta name="robots" content="index, follow">' . "\n";
}, 1);

get_header();

$pid          = get_the_ID();
$is_logged_in = is_user_logged_in();
$cta_url      = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
$contact_url  = home_url('/contact/');

$re_photography_page_url    = slm_service_page_url('re-photography');
$re_videography_page_url    = slm_service_page_url('re-videography');

$drone_photography_page_url = slm_service_page_url('drone-photography');
$virtual_tours_page_url     = slm_service_page_url('virtual-tours');
$zillow_showcase_page_url   = slm_page_url_by_template('templates/page-service-zillow-showcase.php', '/service-zillow-showcase/');

$social_media_packages_page_url   = slm_page_url_by_template('templates/page-social-media-packages.php', '/social-media-packages/');
$social_media_assistance_page_url = slm_page_url_by_template('templates/page-social-media-assistance.php', '/social-media-assistance/');
$memberships_page_url             = slm_memberships_url();
$monthly_memberships_page_url     = $memberships_page_url . '#monthly-content-memberships';
$agent_memberships_page_url       = $memberships_page_url . '#listings-agent-memberships';

$listing_packages = [
  [
    'name'        => 'Key Package',
    'service_url' => $re_photography_page_url,
    'features'    => [
      'Magazine-quality photography',
      'Up to 7 aerial photos',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name'        => 'Zillow Showcase Delight',
    'service_url' => $zillow_showcase_page_url,
    'features'    => [
      'Magazine-quality photos',
      'Zillow 3D virtual tour',
      'Zillow floor plan',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name'        => 'Spotlight Package',
    'service_url' => $re_videography_page_url,
    'features'    => [
      'Magazine-quality photos and drone photos',
      '60–120 second premium video (drone included)',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name'        => 'Virtual Package',
    'service_url' => $virtual_tours_page_url,
    'features'    => [
      'Magazine-quality photos',
      'Up to 7 aerial photos',
      '45–120 second virtual video',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name'        => 'Lot and Land Package',
    'service_url' => $drone_photography_page_url,
    'features'    => [
      'Up to 10–12 magazine-quality drone photos',
      'Lot lines added to photos',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name'        => 'Simply Photos',
    'service_url' => $re_photography_page_url,
    'features'    => [
      'Magazine-quality photography',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
];

$social_packages = [
  [
    'name'     => 'Content Refresh',
    'features' => [
      '30 minute session',
      '2 edited reels',
      '1 horizontal video',
    ],
  ],
  [
    'name'     => 'Brand Builder',
    'popular'  => true,
    'features' => [
      '1 hour session',
      '4 edited reels',
      '1 talking head video',
      '1 horizontal video',
    ],
  ],
  [
    'name'     => 'Consistency Creator',
    'features' => [
      '1.5 hour session',
      '6 edited reels',
      '1 talking head video',
      '1 horizontal video',
      '4 branded Instagram posts',
    ],
  ],
  [
    'name'     => 'Authority Builder',
    'features' => [
      '2 hour session',
      '10 edited reels',
      '1 talking head video',
      '1 horizontal video',
      '10 branded Instagram posts',
      'Social media post plan',
    ],
  ],
  [
    'name'     => 'Market Dominator',
    'features' => [
      'Half-day content shoot',
      '20 edited reels',
      '2 talking head videos',
      '2 horizontal videos',
      '15 branded Instagram posts',
      'Social media post plan',
      'Caption suggestions',
    ],
  ],
];

$social_assistance_products = [
  [
    'name'        => 'Local Talk Reel',
    'description' => 'Cinematic drone footage of the area you serve + your 30–90 second voice memo. We craft a fully branded reel that positions you as the local authority — no on-camera filming required.',
    'inputs'      => [
      'Voice memo (30–90 sec)',
      'Topic + address/area',
    ],
    'deliverables' => [
      '1 vertical reel',
      'Captions + retention pacing',
      'Branded text overlays',
      'Licensed music',
      'Delivery ready to post',
    ],
  ],
  [
    'name'        => 'Brand Presence Reel',
    'description' => 'Send phone videos (client moments, walkthroughs, open houses, day-in-the-life, tips). We turn them into a polished branded reel so your feed stays active and professional.',
    'inputs'      => [
      'Any raw clips (selfie, walkthrough, open house, tips)',
    ],
    'deliverables' => [
      'Structured storytelling',
      'Hook + retention editing',
      'Branding overlays',
      'Music + captions',
    ],
  ],
];

$monthly_memberships = [
  [
    'slug'     => 'monthly-momentum',
    'name'     => 'Monthly Momentum',
    'features' => [
      '45 minute session',
      '4 edited reels',
      '1 talking head video',
    ],
  ],
  [
    'slug'     => 'growth-engine',
    'name'     => 'Growth Engine',
    'popular'  => true,
    'features' => [
      '1.5 hour session',
      '10 edited reels',
      '1 talking head video',
      '1 horizontal video',
      '5 branded Instagram posts',
    ],
  ],
  [
    'slug'     => 'brand-authority',
    'name'     => 'Brand Authority',
    'features' => [
      '2 hour session',
      '15 edited reels',
      '2 talking head videos',
      '1 horizontal video',
      '8 branded Instagram posts',
    ],
  ],
  [
    'slug'     => 'elite-presence',
    'name'     => 'Elite Presence',
    'features' => [
      'Half-day session',
      '25 edited reels',
      '2 talking head videos',
      '2 horizontal videos',
      '10 branded Instagram posts',
      'Social media post plan',
    ],
  ],
  [
    'slug'     => 'vip-presence',
    'name'     => 'VIP Presence',
    'features' => [
      'Full-day content shoot',
      '30 edited reels',
      '3 talking head videos',
      '3 horizontal videos',
      '15 branded Instagram posts',
      'Social media post plan',
      'Caption suggestions',
      'Strategic media analysis',
    ],
  ],
];

$agent_memberships = [
  [
    'slug'     => 'agent-starting',
    'name'     => 'Starting',
    'features' => [
      '1 listing shoot',
      '1 AI video for 1 listing',
      '1 staged photo or 1 dusk conversion',
    ],
  ],
  [
    'slug'     => 'agent-growing',
    'name'     => 'Growing',
    'popular'  => true,
    'features' => [
      '3 listing shoots',
      '1 AI video for 1 listing',
      '1 agent intro video',
      '2 staged or dusk conversions',
      '3 branded Instagram posts',
    ],
  ],
  [
    'slug'     => 'agent-established',
    'name'     => 'Established',
    'features' => [
      '5 listing shoots',
      '2 AI videos for listings',
      '2 agent intro videos',
      '1 horizontal video/tour',
      '5 branded Instagram posts',
    ],
  ],
  [
    'slug'     => 'agent-elite',
    'name'     => 'Elite',
    'features' => [
      '9 listing shoots',
      '5 AI videos',
      '4 agent intro videos',
      '2 horizontal videos/tours',
      '10 branded Instagram posts',
      '4 staged or dusk conversions',
    ],
  ],
  [
    'slug'     => 'agent-top-tier',
    'name'     => 'Top-Tier',
    'features' => [
      '15 listing shoots',
      '7 AI videos',
      '6 agent intro videos',
      '4 horizontal videos/tours',
      '15 branded Instagram posts',
      '8 staged or dusk conversions',
    ],
  ],
];

$addons = [
  [
    'name' => 'Zillow Add-On',
    'desc' => 'Add a Zillow 3D tour and interactive floor plan to boost your listing\'s visibility on Zillow.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
  ],
  [
    'name' => 'Spotlight Reel',
    'desc' => 'A short, scroll-stopping video highlight of your listing built for social media.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>',
  ],
  [
    'name' => 'Agent Intro Clip',
    'desc' => 'A branded video intro featuring you and the property to build trust with potential buyers.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
  ],
  [
    'name' => 'Full Agent Video',
    'desc' => 'A polished, full-length agent-branded walkthrough video for listings or marketing.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/><line x1="17" y1="17" x2="22" y2="17"/></svg>',
  ],
  [
    'name' => 'AI Twilight Photography',
    'desc' => 'AI-enhanced dusk exterior shots that add dramatic sky and lighting without an evening shoot.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a5 5 0 0 0-10 0"/><line x1="12" y1="9" x2="12" y2="2"/><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/><line x1="1" y1="18" x2="3" y2="18"/><line x1="21" y1="18" x2="23" y2="18"/><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/><line x1="23" y1="22" x2="1" y2="22"/><polyline points="16 5 12 9 8 5"/></svg>',
  ],
  [
    'name' => 'In-Person Twilight Photography',
    'desc' => 'On-location twilight session capturing the property in real golden-hour and dusk lighting.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>',
  ],
  [
    'name' => 'Dusk Conversions',
    'desc' => 'Transform daytime exterior photos into warm, eye-catching dusk scenes using AI editing.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
  ],
  [
    'name' => 'Drone Add-On',
    'desc' => 'Aerial photography showcasing property scale, lot layout, and neighborhood surroundings.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="8 11 12 15 16 9"/></svg>',
  ],
  [
    'name' => 'Drone Video Add-On',
    'desc' => 'Cinematic aerial video footage that highlights the full scope of the property and area.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>',
  ],
  [
    'name' => 'Virtual Video',
    'desc' => 'An AI-generated walkthrough video created from your listing photos — no extra shoot needed.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>',
  ],
  [
    'name' => 'Detail Photos',
    'desc' => 'Close-up shots of unique finishes, fixtures, and features that set the property apart.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>',
  ],
  [
    'name' => 'Virtual Staging',
    'desc' => 'Digitally furnished rooms that help buyers visualize the space and boost listing appeal.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>',
  ],
  [
    'name' => 'Heavy Photoshopping',
    'desc' => 'Advanced retouching for removing clutter, swapping skies, or correcting major visual distractions.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>',
  ],
];

?>

<main id="main-content">

  <!-- ============================================================
       Page Header — Dark Navy, SEO Headline
       ============================================================ -->
  <section class="svc-hero" aria-label="Services overview">
    <div class="container">
      <div class="svc-hero__content">
        <p class="svc-hero__eyebrow">Jacksonville &amp; North Florida</p>
        <h1 class="svc-hero__title">Real Estate Photography &amp; Video Services in Jacksonville &amp; North Florida</h1>
        <p class="svc-hero__sub">We offer professional real estate photography, videography, and branding content designed to help agents and businesses stand out in Jacksonville and throughout North Florida. Whether you&rsquo;re marketing a listing or building your brand, our services are built to deliver high-quality visuals that drive attention and results.</p>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Service Cards — 5 Core Services (card grid, PDF Brief)
       ============================================================ -->
  <section class="svc-core-cards" id="our-services" aria-labelledby="svc-core-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="svc-core-title">Our Services</h2>
        <p>Professional media services built for real estate agents and businesses across North Florida.</p>
      </header>

      <p style="text-align:center; font-family:'Plus Jakarta Sans',sans-serif; font-size:0.9rem; font-weight:600; color:#C9922A; margin-top:-12px; margin-bottom:40px; letter-spacing:0.02em;"><?php echo esc_html(get_post_meta($pid, 'svc_core_pricing_line', true) ?: 'Pricing starting as low as $145 — no membership required to book'); ?></p>

      <div class="svc-cards-grid">

        <!-- Card 1: Photography -->
        <div class="svc-card">
          <span class="svc-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
          </span>
          <h3 class="svc-card__title">Professional Real Estate Photography in North Florida</h3>
          <p class="svc-card__body">High-quality, MLS-ready photos that make your listings impossible to scroll past. Every shot is composed and lit to highlight the property&rsquo;s best features — ensuring your listing stands out online and attracts the right buyers.</p>
          <a class="svc-card__btn" href="<?php echo esc_url($re_photography_page_url); ?>" aria-label="Learn more about Real Estate Photography">Learn More</a>
        </div>

        <!-- Card 2: Video -->
        <div class="svc-card">
          <span class="svc-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="15" height="13" rx="2"/><path d="m17 9 5-2v10l-5-2V9Z"/></svg>
          </span>
          <h3 class="svc-card__title">Cinematic Listing Videos &amp; Walkthroughs</h3>
          <p class="svc-card__body">Smooth, modern video walkthroughs built for MLS and social media. Elevate your marketing, set yourself apart from other agents, and win more listings with cinematic content that showcases every room at its best.</p>
          <a class="svc-card__btn" href="<?php echo esc_url($re_videography_page_url); ?>" aria-label="Learn more about Cinematic Listing Videos">Learn More</a>
        </div>

        <!-- Card 3: Reels -->
        <div class="svc-card">
          <span class="svc-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="3"/><path d="M10 9.5 15 12l-5 2.5V9.5Z"/></svg>
          </span>
          <h3 class="svc-card__title">Real Estate Reels &amp; Short-Form Content</h3>
          <p class="svc-card__body">Custom Reels and Shorts designed specifically for real estate agents and businesses. Increase engagement, grow your presence online, and stay consistently visible to potential clients with content built to stop the scroll.</p>
          <a class="svc-card__btn" href="<?php echo esc_url($social_media_packages_page_url); ?>" aria-label="Learn more about Social Media Content">Learn More</a>
        </div>

        <!-- Card 4: Brand Content -->
        <div class="svc-card">
          <span class="svc-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2.5"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><path d="M12 12v2M6 14h12" stroke-linecap="round"/></svg>
          </span>
          <h3 class="svc-card__title">Brand Content for Businesses in North Florida</h3>
          <p class="svc-card__body">Professional photo and video content that highlights your brand, attracts new clients, and elevates your online presence. Whether you&rsquo;re a local business, service provider, or growing brand &mdash; we create content that makes people take notice.</p>
          <a class="svc-card__btn" href="<?php echo esc_url($contact_url); ?>" aria-label="Learn more about Business Branding Content">Learn More</a>
        </div>

        <!-- Card 5: Aerial / Drone -->
        <div class="svc-card">
          <span class="svc-card__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="2"/><path d="M5 5.5 8.5 9M19 5.5 15.5 9M5 18.5 8.5 15M19 18.5 15.5 15"/><circle cx="4" cy="5" r="2"/><circle cx="20" cy="5" r="2"/><circle cx="4" cy="19" r="2"/><circle cx="20" cy="19" r="2"/></svg>
          </span>
          <h3 class="svc-card__title">Aerial Photography &amp; Video</h3>
          <p class="svc-card__body">Gain a unique perspective with high-quality drone imagery that showcases land, views, and property surroundings. Perfect for listings with acreage, waterfront features, or location advantages that ground-level photos simply can&rsquo;t capture.</p>
          <a class="svc-card__btn" href="<?php echo esc_url($drone_photography_page_url); ?>" aria-label="Learn more about Aerial Photography">Learn More</a>
        </div>

      </div>
    </div>
  </section>

  <!-- ============================================================
       Section 1 — Listing Media Packages
       ============================================================ -->
  <section class="svc-section svc-section--alt" id="listing-media-packages" aria-labelledby="svc-listing-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="svc-listing-title">Listing Packages</h2>
        <p>Property media packages built for listing launch, Zillow visibility, and premium presentation.</p>
      </header>

      <div class="svc-grid">
        <?php foreach ($listing_packages as $pkg): ?>
          <article class="svc-card" aria-label="<?php echo esc_attr($pkg['name']); ?> listing package">
            <h3 class="svc-card__name"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="svc-card__features" aria-label="<?php echo esc_attr($pkg['name']); ?> features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary svc-card__cta" href="<?php echo esc_url((string) ($pkg['service_url'] ?? home_url('/services/'))); ?>" aria-label="View <?php echo esc_attr($pkg['name']); ?> service page">View Service Page</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Section 2 — Social Media Packages
       ============================================================ -->
  <section class="svc-section" id="social-media-packages" aria-labelledby="svc-social-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="svc-social-title">Social Media Packages</h2>
        <p>One-time social production packages for reels, talking head clips, and branded post content.</p>
      </header>

      <div class="svc-grid svc-grid--5">
        <?php foreach ($social_packages as $pkg): ?>
          <article class="svc-card<?php echo !empty($pkg['popular']) ? ' svc-card--popular' : ''; ?>" aria-label="<?php echo esc_attr($pkg['name']); ?> social media package">
            <?php if (!empty($pkg['popular'])): ?>
              <div class="svc-card__badge" aria-label="Most popular plan">Most Popular</div>
            <?php endif; ?>
            <h3 class="svc-card__name"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="svc-card__features" aria-label="<?php echo esc_attr($pkg['name']); ?> features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary svc-card__cta" href="<?php echo esc_url($social_media_packages_page_url); ?>">View Service Page</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Section 3 — Social Media Assistance
       ============================================================ -->
  <section class="svc-section" id="social-content-packages" aria-labelledby="svc-assistance-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="svc-assistance-title">Social Media Assistance</h2>
        <p>Done-for-you reel products built from your clips, voice memos, and local market insights — no on-camera sessions required.</p>
      </header>

      <div class="svc-grid svc-grid--2col">
        <?php foreach ($social_assistance_products as $product): ?>
          <article class="svc-card svc-card--assistance" aria-label="<?php echo esc_attr((string) $product['name']); ?> service">
            <h3 class="svc-card__name"><?php echo esc_html((string) $product['name']); ?></h3>
            <p class="svc-card__desc"><?php echo esc_html((string) ($product['description'] ?? '')); ?></p>

            <p class="svc-card__label">What You Send</p>
            <ul class="svc-card__features" aria-label="<?php echo esc_attr((string) $product['name']); ?> inputs">
              <?php foreach ((array) ($product['inputs'] ?? []) as $input): ?>
                <li>
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html((string) $input); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>

            <p class="svc-card__label">What You Get</p>
            <ul class="svc-card__features" aria-label="<?php echo esc_attr((string) $product['name']); ?> deliverables">
              <?php foreach ((array) ($product['deliverables'] ?? []) as $deliverable): ?>
                <li>
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html((string) $deliverable); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>

            <a class="btn btn--secondary svc-card__cta" href="<?php echo esc_url($social_media_assistance_page_url); ?>" aria-label="View Social Media Assistance service page for <?php echo esc_attr((string) $product['name']); ?>">View Service Page</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Section 4 — Monthly Content Memberships
       ============================================================ -->
  <section class="svc-section svc-section--alt" id="monthly-content-memberships" aria-labelledby="svc-monthly-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="svc-monthly-title">Monthly Memberships</h2>
        <p>Recurring monthly plans for creators, teams, and brands that need consistent media output.</p>
      </header>

      <div class="svc-grid svc-grid--5">
        <?php foreach ($monthly_memberships as $pkg): ?>
          <article class="svc-card<?php echo !empty($pkg['popular']) ? ' svc-card--popular' : ''; ?>" aria-label="<?php echo esc_attr($pkg['name']); ?> monthly membership">
            <?php if (!empty($pkg['popular'])): ?>
              <div class="svc-card__badge" aria-label="Most popular plan">Most Popular</div>
            <?php endif; ?>
            <h3 class="svc-card__name"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="svc-card__features" aria-label="<?php echo esc_attr($pkg['name']); ?> features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary svc-card__cta" href="<?php echo esc_url($monthly_memberships_page_url); ?>">View Membership Page</a>
          </article>
        <?php endforeach; ?>
      </div>
      <p class="svc-section__note">Monthly memberships require a minimum 3-month commitment.</p>
    </div>
  </section>

  <!-- ============================================================
       Section 5 — Listings-Agent Memberships
       ============================================================ -->
  <section class="svc-section" id="listings-agent-memberships" aria-labelledby="svc-agent-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="svc-agent-title">Listings-Agent Memberships</h2>
        <p>Membership tiers for agents combining shoots, AI edits, videos, tours, and branded social assets — all in one monthly plan.</p>
      </header>

      <div class="svc-grid svc-grid--5">
        <?php foreach ($agent_memberships as $pkg): ?>
          <article class="svc-card<?php echo !empty($pkg['popular']) ? ' svc-card--popular' : ''; ?>" aria-label="<?php echo esc_attr($pkg['name']); ?> agent membership">
            <?php if (!empty($pkg['popular'])): ?>
              <div class="svc-card__badge" aria-label="Most popular plan">Most Popular</div>
            <?php endif; ?>
            <h3 class="svc-card__name"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="svc-card__features" aria-label="<?php echo esc_attr($pkg['name']); ?> features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary svc-card__cta" href="<?php echo esc_url($agent_memberships_page_url); ?>">View Membership Page</a>
          </article>
        <?php endforeach; ?>
      </div>
      <p class="svc-section__note">Listings-agent memberships run on a 12-month agreement.</p>
    </div>
  </section>

  <!-- ============================================================
       Section 6 — Enhance Your Package (Add-Ons)
       ============================================================ -->
  <section class="svc-section svc-section--alt" id="popular-add-ons" aria-labelledby="svc-addons-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="svc-addons-title">Additional Services</h2>
        <p>Customize your order with optional upgrades — including Zillow walkthrough videos, marketing add-ons, custom content packages, membership packages, and partnership-focused options.</p>
        <p class="svc-section__note" style="margin-top:10px;">Add-on availability depends on the selected package. Incompatible combinations are intentionally restricted at checkout.</p>
      </header>

      <div class="addon-grid">
        <?php foreach ($addons as $addon): ?>
          <div class="addon-card">
            <div class="addon-card__icon" aria-hidden="true">
              <?php echo $addon['icon']; ?>
            </div>
            <div class="addon-card__body">
              <h3 class="addon-card__title"><?php echo esc_html($addon['name']); ?></h3>
              <p class="addon-card__desc"><?php echo esc_html($addon['desc']); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ============================================================
       Why Choose Us
       ============================================================ -->
  <section class="svc-section svc-section--alt" aria-labelledby="svc-why-title">
    <div class="container">
      <header class="svc-section__header">
        <h2 id="svc-why-title">Why Choose Showcase Listings Media</h2>
      </header>
      <ul class="svc-why__list">
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
          <span>Locally based in Jacksonville, FL</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span>Fast turnaround (24&ndash;48 hours)</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          <span>Tailored approach for every project</span>
        </li>
        <li>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span>Trusted by agents and businesses across North Florida</span>
        </li>
      </ul>
      <p class="svc-why__location">Serving Jacksonville &amp; North Florida</p>
    </div>
  </section>

  <!-- ============================================================
       Final CTA
       ============================================================ -->
  <section class="svc-final-cta" aria-label="Book a Service">
    <div class="container">
      <h2>Ready to Elevate Your Listings?</h2>
      <p>Let&rsquo;s build content that gets attention, drives action, and helps you grow &mdash; starting with your next shoot.</p>
      <div class="svc-final-cta__btns">
        <a class="btn svc-final-cta__primary" href="<?php echo esc_url($cta_url); ?>">Book a Shoot Today</a>
        <a class="btn svc-final-cta__secondary" href="tel:+19042945809">Call (904) 294-5809</a>
        <a class="btn svc-final-cta__secondary" href="<?php echo esc_url($contact_url); ?>">Send a Message</a>
      </div>
    </div>
  </section>

</main>

<?php slm_edit_page_button($pid); ?>

<?php get_footer(); ?>
