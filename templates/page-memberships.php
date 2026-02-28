<?php
/**
 * Template Name: Memberships
 */
if (!defined('ABSPATH')) exit;

get_header();

$is_logged_in = is_user_logged_in();
$is_admin = $is_logged_in && slm_user_is_admin();
$portal_membership_url = add_query_arg('view', 'membership-shop', slm_portal_url());
$portal_membership_auth_url = add_query_arg([
  'redirect_to' => $portal_membership_url,
], slm_login_url());
$admin_dashboard_url = slm_admin_portal_url();
$services_url = home_url('/services/');

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

$membership_cta = static function (array $pkg) use ($is_logged_in, $is_admin, $portal_membership_url, $portal_membership_auth_url, $admin_dashboard_url): array {
  if ($is_admin) {
    return [
      'url' => $admin_dashboard_url,
      'label' => 'Admin Dashboard',
    ];
  }

  if ($is_logged_in) {
    return [
      'url' => $portal_membership_url,
      'label' => 'Open Membership Shop',
    ];
  }

  return [
    'url' => $portal_membership_auth_url,
    'label' => 'Log In / Create Account',
  ];
};
?>

<main id="main-content">
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>Membership Listings</h1>
      <p class="page-hero__sub">Browse monthly and listings-agent memberships designed for consistent media output, strategic support, and scalable growth.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section page-section--secondary" id="monthly-content-memberships">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Monthly Memberships</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Recurring plans for creators, teams, and brands that need consistent monthly production. Membership checkout is available only after sign-in in the client portal.</p>

      <div class="pkg-grid">
        <?php foreach ($monthly_memberships as $pkg): ?>
          <?php $cta = $membership_cta($pkg); ?>
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
            <p class="sub" style="margin:10px 0 12px; font-size:.9rem;">Agreement options available in portal: Month-to-Month, 6-Month, or 12-Month.</p>
            <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url((string) $cta['url']); ?>"><?php echo esc_html((string) $cta['label']); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="center sub" style="margin-top:20px; max-width:920px; margin-left:auto; margin-right:auto;">
        <p style="margin:0 0 8px;">Month-to-month social memberships require a minimum 3-month commitment.</p>
        <p style="margin:0 0 8px;">6-month agreement: $100 off the first two months (averaged discount option in Square may display as approximately $33.33/mo across 6 months).</p>
        <p style="margin:0;">12-month agreement includes 1 complimentary listing shoot per month while the agreement remains active.</p>
      </div>
    </div>
  </section>

  <section class="page-section" id="listings-agent-memberships">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Listings-Agent Memberships</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Tiered options for agents combining listings coverage, AI edits, and social support workflows.</p>

      <div class="pkg-grid">
        <?php foreach ($agent_memberships as $pkg): ?>
          <?php $cta = $membership_cta($pkg); ?>
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
            <p class="sub" style="margin:8px 0 0; font-size:.9rem;">Agreement Term: <strong>12-Month</strong></p>
            <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url((string) $cta['url']); ?>"><?php echo esc_html((string) $cta['label']); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="center sub" style="margin-top:20px;">Listings-agent memberships run on a 12-month agreement and include 1 complimentary listing shoot per month with rollover while the term remains active.</p>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="membership-application">
    <div class="container memberships-applyWrap">
      <div class="portal-card memberships-applyCard">
        <h2>Need Help Choosing a Membership?</h2>
        <p class="sub">Use the plan cards above to sign in and open the client portal membership shop for checkout. If you need one-time work or a custom recommendation, review services first.</p>
        <a class="btn btn--accent" href="<?php echo esc_url($services_url); ?>">View Services</a>
        <p class="sub memberships-applyCard__note"><a href="<?php echo esc_url($portal_membership_auth_url); ?>">Already a client? Open the membership shop and billing tools in your portal.</a></p>
      </div>
    </div>
  </section>
</main>

<?php get_footer();
