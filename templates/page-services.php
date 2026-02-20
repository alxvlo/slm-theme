<?php
/**
 * Template Name: Services
 */
if (!defined('ABSPATH')) exit;

get_header();

$listing_packages = [
  [
    'name' => 'Key Package',
    'features' => [
      'Magazine-quality photography',
      'Up to 7 aerial photos',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name' => 'Zillow Showcase Delight',
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
    'features' => [
      'Magazine-quality photos and drone photos',
      '60-120 second premium video (drone included)',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name' => 'Virtual Package',
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
    'features' => [
      'Up to 10-12 magazine-quality drone photos',
      'Lot lines added to photos',
      'Blue sky guarantee',
      'Fast turnaround',
    ],
  ],
  [
    'name' => 'Simply Photos',
    'features' => [
      'Magazine-quality photography',
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
  'Zillow add-on',
  'Spotlight reel',
  'Agent intro clip',
  'Full agent video',
  'Twilight photos',
  'Dusk conversions',
  'Drone add-on',
  'Drone video add-on',
  'Virtual twilight',
  'Detail photos',
  'Virtual staging',
  'Heavy photoshopping',
];

$proof_points = [
  'Offer structure aligned to your current PDF packages',
  'Anchor-based sections for clean menu dropdown linking',
  'Flexible add-ons and memberships for scaling media output',
];

$create_account_url = add_query_arg('mode', 'signup', slm_login_url());
$portal_membership_url = add_query_arg('view', 'account', slm_portal_url());
$is_logged_in = is_user_logged_in();
$subscriptions_enabled = function_exists('slm_subscriptions_can_accept_checkout') && slm_subscriptions_can_accept_checkout();
$membership_cta = static function (array $pkg) use ($is_logged_in, $subscriptions_enabled, $create_account_url, $portal_membership_url): array {
  $plan_slug = sanitize_key((string) ($pkg['slug'] ?? ''));
  if ($is_logged_in && $subscriptions_enabled && $plan_slug !== '' && function_exists('slm_subscriptions_start_url')) {
    return [
      'url' => slm_subscriptions_start_url($plan_slug),
      'label' => 'Start Membership',
    ];
  }

  if ($is_logged_in) {
    return [
      'url' => $portal_membership_url,
      'label' => 'Manage Membership',
    ];
  }

  return [
    'url' => $create_account_url,
    'label' => 'Create Account to Order',
  ];
};
?>

<main>
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>Services and Memberships</h1>
      <p class="page-hero__sub">Current package structure for listings, content, and recurring growth support.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section page-section--secondary page-section--compact">
    <div class="container">
      <ul class="services-proof" aria-label="How services are organized">
        <?php foreach ($proof_points as $point): ?>
          <li><?php echo esc_html($point); ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="center sub" style="margin:16px 0 0;">Pricing is currently hidden. Contact us for current rates.</p>
    </div>
  </section>

  <section class="page-section" id="listing-media-packages">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Listing Packages</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Property media packages built for listing launch, Zillow visibility, and premium presentation.</p>

      <div class="pkg-grid">
        <?php foreach ($listing_packages as $pkg): ?>
          <div class="pkg-card">
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url($create_account_url); ?>">Create Account to Order</a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="social-content-packages">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Social Packages</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">One-time social production packages for reels, talking head clips, and branded post content.</p>

      <div class="pkg-grid">
        <?php foreach ($social_packages as $pkg): ?>
          <div class="pkg-card<?php echo !empty($pkg['popular']) ? ' pkg-card--popular' : ''; ?>">
            <?php if (!empty($pkg['popular'])): ?>
              <div class="pkg-badge">Most Popular</div>
            <?php endif; ?>
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url($create_account_url); ?>">Create Account to Order</a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section" id="monthly-content-memberships">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Monthly Memberships</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Recurring monthly plans for creators, teams, and brands that need consistent media output.</p>

      <div class="pkg-grid">
        <?php foreach ($monthly_memberships as $pkg): ?>
          <?php $cta = $membership_cta($pkg); ?>
          <div class="pkg-card">
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url((string) $cta['url']); ?>"><?php echo esc_html((string) $cta['label']); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="center sub" style="margin-top:20px;">Monthly memberships require a minimum 3-month commitment.</p>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="listings-agent-memberships">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Listings-Agent Memberships</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Membership tiers for agents combining shoots, AI edits, videos, tours, and branded social assets.</p>

      <div class="pkg-grid">
        <?php foreach ($agent_memberships as $pkg): ?>
          <?php $cta = $membership_cta($pkg); ?>
          <div class="pkg-card">
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url((string) $cta['url']); ?>"><?php echo esc_html((string) $cta['label']); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="center sub" style="margin-top:20px;">Listings-agent memberships run on a 12-month agreement.</p>
    </div>
  </section>

  <section class="page-section" id="popular-add-ons">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Add-Ons</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Optional add-ons to customize each order based on listing needs.</p>
      <p class="center sub" style="margin:-8px auto 26px; max-width:820px;">Add-on availability depends on the selected package. Incompatible combinations are intentionally restricted at checkout.</p>
      <div class="svc-tiles">
        <?php foreach ($addons as $addon): ?>
          <div class="svc-tile">
            <h3 style="margin:0;"><?php echo esc_html($addon); ?></h3>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
