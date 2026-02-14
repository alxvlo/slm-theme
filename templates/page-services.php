<?php
/**
 * Template Name: Services
 */
get_header();

$lock_svg = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M6 11h12v10H6V11Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M12 15v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';

$packages = [
  [
    'name' => 'Essential',
    'features' => [
      '20-30 Professional Photos',
      'HDR Processing',
      'Virtual Staging (1 room)',
      '24-hour delivery',
    ],
    'popular' => false,
  ],
  [
    'name' => 'Premium',
    'features' => [
      '40-50 Professional Photos',
      'HDR Processing',
      'Aerial Drone Photography',
      'Virtual Staging (2 rooms)',
      'Property Website',
      '24-hour delivery',
    ],
    'popular' => true,
  ],
  [
    'name' => 'Luxury',
    'features' => [
      '60+ Professional Photos',
      'HDR Processing',
      'Aerial Drone Video',
      'Cinematic Video Tour',
      '3D Virtual Tour',
      'Virtual Staging (3 rooms)',
      'Property Website',
      'Same-day delivery available',
    ],
    'popular' => false,
  ],
];

$services = [
  ['name' => 'Real Estate Photography', 'url' => home_url('/services/real-estate-photography/')],
  ['name' => 'Drone Photography', 'url' => home_url('/services/drone-photography/')],
  ['name' => 'Real Estate Videography', 'url' => home_url('/services/real-estate-videography/')],
  ['name' => 'Drone Videography', 'url' => home_url('/services/drone-videography/')],
  ['name' => '3D Virtual Tours', 'url' => home_url('/services/virtual-tours/')],
  ['name' => 'Floor Plans', 'url' => home_url('/services/floor-plans/')],
  ['name' => 'Twilight Photography', 'url' => home_url('/services/twilight-photography/')],
];

$login_url = add_query_arg('mode', 'login', slm_login_url());
$signup_url = add_query_arg('mode', 'signup', slm_login_url());
$proof_points = [
  'Fast turnaround options for active listings',
  'Consistent media quality across every property',
  'Flexible service mix for teams and solo agents',
];
?>

<main>
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>Our Services &amp; Packages</h1>
      <p class="page-hero__sub">Professional media packages designed for every listing type and budget</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section page-section--secondary page-section--compact">
    <div class="container">
      <ul class="services-proof" aria-label="Why agents choose this workflow">
        <?php foreach ($proof_points as $point): ?>
          <li><?php echo esc_html($point); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <section class="page-section">
    <div class="container">
      <div class="pkg-grid">
        <?php foreach ($packages as $pkg): ?>
          <div class="pkg-card<?php echo $pkg['popular'] ? ' pkg-card--popular' : ''; ?>">
            <?php if ($pkg['popular']): ?>
              <div class="pkg-badge">Most Popular</div>
            <?php endif; ?>

            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>

            <div class="pkg-price" aria-label="Pricing locked">
              <div class="pkg-price__blur"><span class="pkg-price__val">$XXX</span></div>
              <div class="pkg-price__lock">
                <div class="lock-pill"><?php echo $lock_svg; ?><span>Login to view pricing</span></div>
              </div>
            </div>

            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $f): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <span><?php echo esc_html($f); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>

            <?php if ($pkg['popular']): ?>
              <a class="btn btn--accent pkg-cta" href="<?php echo esc_url($signup_url); ?>">Get Started</a>
            <?php else: ?>
              <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url($signup_url); ?>">Get Started</a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="center" style="margin-top:42px;">
        <p class="sub" style="margin-bottom:14px;">Need a custom package? We'll work with you to create the perfect solution.</p>
        <a class="btn" href="<?php echo esc_url($login_url); ?>">Sign in to see full pricing</a>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Individual Services</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Explore our professional real estate media services available à la carte or as part of our packages</p>

      <div class="svc-tiles">
        <?php foreach ($services as $s): ?>
          <a class="svc-tile" href="<?php echo esc_url($s['url']); ?>">
            <h3 style="margin:0 0 8px;"><?php echo esc_html($s['name']); ?></h3>
            <p>Learn More →</p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
