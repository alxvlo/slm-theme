<?php
/**
 * Template Name: Services
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$service_page_button_label = 'View Service Page';

$re_photography_page_url = slm_service_page_url('re-photography');
$re_videography_page_url = slm_service_page_url('re-videography');
$drone_photography_page_url = slm_service_page_url('drone-photography');
$virtual_tours_page_url = slm_service_page_url('virtual-tours');
$zillow_showcase_page_url = slm_page_url_by_template('templates/page-service-zillow-showcase.php', '/service-zillow-showcase/');

$social_media_packages_page_url = slm_page_url_by_template('templates/page-social-media-packages.php', '/social-media-packages/');
$social_media_assistance_page_url = slm_page_url_by_template('templates/page-social-media-assistance.php', '/social-media-assistance/');
$memberships_page_url = slm_memberships_url();
$monthly_memberships_page_url = $memberships_page_url . '#monthly-content-memberships';
$agent_memberships_page_url = $memberships_page_url . '#listings-agent-memberships';

$listing_packages = [
  [
    'name' => 'Simply Photos',
    'service_url' => $re_photography_page_url,
    'price_cue' => 'Starting at $145',
    'features' => [
      'High-quality MLS-ready photos',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name' => 'Key Package',
    'service_url' => $re_photography_page_url,
    'features' => [
      'Magazine-quality photography',
      'Up to 7 aerial photos',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name' => 'Zillow Showcase Delight',
    'service_url' => $zillow_showcase_page_url,
    'features' => [
      'Magazine-quality photos',
      'Zillow 3D virtual tour',
      'Zillow floor plan',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name' => 'Spotlight Package',
    'service_url' => $re_videography_page_url,
    'features' => [
      'Magazine-quality photos and drone photos',
      '60-120 second premium video (drone included)',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name' => 'Virtual Package',
    'service_url' => $virtual_tours_page_url,
    'features' => [
      'Magazine-quality photos',
      'Up to 7 aerial photos',
      '45-120 second virtual video',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name' => 'Lot and Land Package',
    'service_url' => $drone_photography_page_url,
    'features' => [
      'Up to 10-12 magazine-quality drone photos',
      'Lot lines added to photos',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
];

$social_packages = [
  [
    'name' => 'Content Refresh',
    'features' => [
      '30 minute session',
      '2 edited reels',
      '1 horizontal video',
    ],
  ],
  [
    'name' => 'Brand Builder',
    'popular' => true,
    'features' => [
      '1 hour session',
      '4 edited reels',
      '1 talking head video',
      '1 horizontal video',
    ],
  ],
  [
    'name' => 'Consistency Creator',
    'features' => [
      '1.5 hour session',
      '6 edited reels',
      '1 talking head video',
      '1 horizontal video',
      '4 branded Instagram posts',
    ],
  ],
  [
    'name' => 'Authority Builder',
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
    'name' => 'Market Dominator',
    'features' => [
      'Half-day content shoot',
      '20 edited reels',
      '2 talking head videos',
      '2 horizontal videos',
      '15 branded Instagram posts',
      'Social media post plan',
      'Caption Suggestions',
    ],
  ],
];

$social_assistance_products = [
  [
    'name' => 'Local Talk Reel',
    'description' => 'Cinematic drone footage of the area you serve + your 30–90 second voice memo. We craft a fully branded reel that positions you as the local authority—no on-camera filming required.',
    'inputs' => [
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
    'name' => 'Brand Presence Reel',
    'description' => 'Send phone videos (client moments, walkthroughs, open houses, day-in-the-life, tips). We turn them into a polished branded reel so your feed stays active and professional.',
    'inputs' => [
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
    'slug' => 'monthly-momentum',
    'name' => 'Monthly Momentum',
    'features' => [
      '45 minute session',
      '4 edited reels',
      '1 talking head video',
    ],
  ],
  [
    'slug' => 'growth-engine',
    'name' => 'Growth Engine',
    'popular' => true,
    'features' => [
      '1.5 hour session',
      '10 edited reels',
      '1 talking head video',
      '1 horizontal video',
      '5 branded Instagram posts',
    ],
  ],
  [
    'slug' => 'brand-authority',
    'name' => 'Brand Authority',
    'features' => [
      '2 hour session',
      '15 edited reels',
      '2 talking head videos',
      '1 horizontal video',
      '8 branded Instagram posts',
    ],
  ],
  [
    'slug' => 'elite-presence',
    'name' => 'Elite Presence',
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
    'slug' => 'vip-presence',
    'name' => 'VIP Presence',
    'features' => [
      'Full-day content shoot',
      '30 edited reels',
      '3 talking head videos',
      '3 horizontal videos',
      '15 branded Instagram posts',
      'Social media post plan',
      'Caption Suggestions',
      'Strategic media analysis',
    ],
  ],
];

$agent_memberships = [
  [
    'slug' => 'agent-starting',
    'name' => 'Starting',
    'features' => [
      '1 listing shoot',
      '1 AI video for 1 listing',
      '1 staged photo or 1 dusk conversion',
    ],
  ],
  [
    'slug' => 'agent-growing',
    'name' => 'Growing',
    'popular' => true,
    'features' => [
      '3 listing shoots',
      '1 AI video for 1 listing',
      '1 agent intro video',
      '2 staged or dusk conversions',
      '3 branded Instagram posts',
    ],
  ],
  [
    'slug' => 'agent-established',
    'name' => 'Established',
    'features' => [
      '5 listing shoots',
      '2 AI videos for listings',
      '2 agent intro videos',
      '1 horizontal video/tour',
      '5 branded Instagram posts',
    ],
  ],
  [
    'slug' => 'agent-elite',
    'name' => 'Elite',
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
    'slug' => 'agent-top-tier',
    'name' => 'Top-Tier',
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
    'name' => 'Zillow add-on',
    'desc' => 'Add a Zillow 3D tour and interactive floor plan to boost your listing\'s visibility on Zillow.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
  ],
  [
    'name' => 'Spotlight reel',
    'desc' => 'A short, scroll-stopping video highlight of your listing built for social media.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>',
  ],
  [
    'name' => 'Agent intro clip',
    'desc' => 'A branded video intro featuring you and the property to build trust with potential buyers.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
  ],
  [
    'name' => 'Full agent video',
    'desc' => 'A polished, full-length agent-branded walkthrough video for listings or marketing.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"/><line x1="7" y1="2" x2="7" y2="22"/><line x1="17" y1="2" x2="17" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="2" y1="7" x2="7" y2="7"/><line x1="2" y1="17" x2="7" y2="17"/><line x1="17" y1="7" x2="22" y2="7"/><line x1="17" y1="17" x2="22" y2="17"/></svg>',
  ],
  [
    'name' => 'AI Twilight Photography',
    'desc' => 'AI-enhanced dusk exterior shots that add dramatic sky and lighting without an evening shoot.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a5 5 0 0 0-10 0"/><line x1="12" y1="9" x2="12" y2="2"/><line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/><line x1="1" y1="18" x2="3" y2="18"/><line x1="21" y1="18" x2="23" y2="18"/><line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/><line x1="23" y1="22" x2="1" y2="22"/><polyline points="16 5 12 9 8 5"/></svg>',
  ],
  [
    'name' => 'In-person Twilight Photography',
    'desc' => 'On-location twilight session capturing the property in real golden-hour and dusk lighting.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>',
  ],
  [
    'name' => 'Dusk conversions',
    'desc' => 'Transform daytime exterior photos into warm, eye-catching dusk scenes using AI editing.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
  ],
  [
    'name' => 'Drone add-on',
    'desc' => 'Aerial photography showcasing property scale, lot layout, and neighborhood surroundings.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="8 11 12 15 16 9"/></svg>',
  ],
  [
    'name' => 'Drone video add-on',
    'desc' => 'Cinematic aerial video footage that highlights the full scope of the property and area.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>',
  ],
  [
    'name' => 'Virtual Video',
    'desc' => 'An AI-generated walkthrough video created from your listing photos — no extra shoot needed.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>',
  ],
  [
    'name' => 'Detail photos',
    'desc' => 'Close-up shots of unique finishes, fixtures, and features that set the property apart.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>',
  ],
  [
    'name' => 'Virtual staging',
    'desc' => 'Digitally furnished rooms that help buyers visualize the space and boost listing appeal.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>',
  ],
  [
    'name' => 'Heavy photoshopping',
    'desc' => 'Advanced retouching for removing clutter, swapping skies, or correcting major visual distractions.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>',
  ],
];

?>

<main>
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>Real Estate Photography &amp; Video Services in Jacksonville &amp; North Florida</h1>
      <p class="page-hero__sub">Professional photography, videography, and branding content tailored to help agents and businesses stand out.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section" id="listing-media-packages">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Real Estate Photography &amp; Video</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">High-quality, MLS-ready photos and cinematic video designed to showcase listings in their best light.</p>

      <div class="pkg-grid">
        <?php foreach ($listing_packages as $pkg): ?>
          <div class="pkg-card">
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <?php if (!empty($pkg['price_cue'])): ?>
              <div style="text-align: center; color: var(--primary); font-weight: 600; margin-top: -10px; margin-bottom: 20px;"><?php echo esc_html($pkg['price_cue']); ?></div>
            <?php endif; ?>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url((string) ($pkg['service_url'] ?? home_url('/services/'))); ?>"><?php echo esc_html($service_page_button_label); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="social-media-packages">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Social Media Content</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Custom short-form videos to increase engagement and attract new clients.</p>

      <div class="pkg-grid">
        <?php foreach ($social_packages as $pkg): ?>
          <article class="pkg-card<?php echo !empty($pkg['popular']) ? ' pkg-card--popular' : ''; ?>">
            <?php if (!empty($pkg['popular'])): ?>
              <div class="pkg-badge">Most Popular</div>
            <?php endif; ?>
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url($social_media_packages_page_url); ?>"><?php echo esc_html($service_page_button_label); ?></a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section" id="drone-services">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Drone Photography &amp; Video</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Aerial imagery to showcase properties and locations from a unique perspective.</p>

      <div class="pkg-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); max-width: 800px; margin: 0 auto;">
        <article class="pkg-card">
          <h3 class="pkg-title">Drone Photography</h3>
          <ul class="pkg-features">
            <li><svg class="pkg-check" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> <span>High-resolution aerial photos</span></li>
            <li><svg class="pkg-check" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> <span>Showcase lot boundaries & location</span></li>
            <li><svg class="pkg-check" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> <span>Perfect for land, estates, and waterfronts</span></li>
          </ul>
          <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url($drone_photography_page_url); ?>"><?php echo esc_html($service_page_button_label); ?></a>
        </article>

        <article class="pkg-card">
          <h3 class="pkg-title">Drone Videography</h3>
          <ul class="pkg-features">
            <li><svg class="pkg-check" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> <span>Cinematic 4K aerial video</span></li>
            <li><svg class="pkg-check" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> <span>Highlight neighborhood amenities</span></li>
            <li><svg class="pkg-check" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> <span>Fully edited with licensed music</span></li>
          </ul>
          <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url(slm_service_page_url('drone-videography')); ?>"><?php echo esc_html($service_page_button_label); ?></a>
        </article>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="social-content-packages">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Business Branding Content</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Professional photo and video content for local businesses to attract new clients.</p>

      <div class="pkg-grid">
        <?php foreach ($social_assistance_products as $product): ?>
          <article class="pkg-card">
            <h3 class="pkg-title"><?php echo esc_html((string) $product['name']); ?></h3>
            <p class="sub" style="margin:0 0 16px;"><?php echo esc_html((string) ($product['description'] ?? '')); ?></p>

            <h4 style="margin:0 0 8px; font-size:1rem;">Inputs</h4>
            <ul class="pkg-features" aria-label="<?php echo esc_attr((string) $product['name']); ?> inputs">
              <?php foreach ((array) ($product['inputs'] ?? []) as $input): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html((string) $input); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>

            <h4 style="margin:8px 0 8px; font-size:1rem;">Deliverables</h4>
            <ul class="pkg-features" aria-label="<?php echo esc_attr((string) $product['name']); ?> deliverables">
              <?php foreach ((array) ($product['deliverables'] ?? []) as $deliverable): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html((string) $deliverable); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>

            <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url($social_media_assistance_page_url); ?>" aria-label="View Social Media Assistance service page"><?php echo esc_html($service_page_button_label); ?></a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="monthly-content-memberships">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Monthly Memberships</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Recurring monthly plans for creators, teams,
        and brands that need consistent media output.</p>

      <div class="pkg-grid">
        <?php foreach ($monthly_memberships as $pkg): ?>
          <div class="pkg-card<?php echo !empty($pkg['popular']) ? ' pkg-card--popular' : ''; ?>">
            <?php if (!empty($pkg['popular'])): ?>
              <div class="pkg-badge">Most Popular</div>
            <?php endif; ?>
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta"
              href="<?php echo esc_url($monthly_memberships_page_url); ?>"><?php echo esc_html($service_page_button_label); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="center sub" style="margin-top:20px;">Monthly memberships require a minimum 3-month commitment.</p>
    </div>
  </section>

  <section class="page-section" id="listings-agent-memberships">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Listings-Agent Memberships</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Membership tiers for agents combining shoots,
        AI edits, videos, tours, and branded social assets.</p>

      <div class="pkg-grid">
        <?php foreach ($agent_memberships as $pkg): ?>
          <div class="pkg-card<?php echo !empty($pkg['popular']) ? ' pkg-card--popular' : ''; ?>">
            <?php if (!empty($pkg['popular'])): ?>
              <div class="pkg-badge">Most Popular</div>
            <?php endif; ?>
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta"
              href="<?php echo esc_url($agent_memberships_page_url); ?>"><?php echo esc_html($service_page_button_label); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="center sub" style="margin-top:20px;">Listings-agent memberships run on a 12-month agreement.</p>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="popular-add-ons">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Add-ons &amp; Extras</h2>
      <p class="center sub" style="margin-bottom:10px; max-width:820px;">Optional add-ons like Zillow walkthroughs, twilight photography, and heavy edits to tailor each order.</p>
      <div class="addon-grid" style="margin-top: 32px;">
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
</main>

<?php get_footer(); ?>
