<?php
/**
 * Template Name: Services
 */
if (!defined('ABSPATH')) {
  die();
}

get_header();

$service_page_button_label = 'View Details';
$book_shoot_button_label = 'Book a Shoot';

$re_photography_page_url = slm_service_page_url('re-photography');
$re_videography_page_url = slm_service_page_url('re-videography');
$drone_photography_page_url = slm_service_page_url('drone-photography');
$virtual_tours_page_url = slm_service_page_url('virtual-tours');
$zillow_showcase_page_url = slm_page_url_by_template('templates/page-service-zillow-showcase.php', '/service-zillow-showcase/');

$social_media_packages_page_url = slm_page_url_by_template('templates/page-social-media-packages.php', '/social-media-packages/');
$social_media_assistance_page_url = slm_page_url_by_template('templates/page-social-media-assistance.php', '/social-media-assistance/');

$order_url = add_query_arg('view', 'place-order', slm_portal_url());

$photo_showcasing = [
  [
    'name' => 'Listing Photography',
    'service_url' => $re_photography_page_url,
    'price_cue' => 'Starting at $145',
    'features' => [
      'High-quality MLS-ready photos',
      'Magazine-quality angles and lighting',
      'Blue sky guarantee',
      '24-hour turnaround',
    ],
  ],
  [
    'name' => 'Drone Photography',
    'service_url' => $drone_photography_page_url,
    'price_cue' => 'Starting at $125',
    'features' => [
      'High-resolution aerial photos',
      'Showcase lot boundaries & location',
      'Highlight neighborhood amenities',
      'Fast turnaround',
    ],
  ],
  [
    'name' => 'Virtual Staging',
    'service_url' => $re_photography_page_url,
    'price_cue' => 'Starting at $40/photo',
    'features' => [
      'Digitally furnished rooms',
      'Help buyers visualize the space',
      'Multiple style options',
      'Fast turnaround',
    ],
  ],
];

$video_showcasing = [
  [
    'name' => 'Listing Video Walkthrough',
    'service_url' => $re_videography_page_url,
    'price_cue' => 'Starting at $250',
    'features' => [
      'Cinematic 60-120 second video',
      'Drone footage included (weather permitting)',
      'Licensed music',
      '48-hour turnaround',
    ],
  ],
  [
    'name' => 'Social Spotlight Reel',
    'service_url' => $re_videography_page_url,
    'price_cue' => 'Starting at $150',
    'features' => [
      'Short, vertical video format',
      'Optimized for Instagram and TikTok',
      'Fast-paced, scroll-stopping edits',
      '48-hour turnaround',
    ],
  ],
  [
    'name' => 'Agent Intro Video',
    'service_url' => $re_videography_page_url,
    'price_cue' => 'Starting at $200',
    'features' => [
      'Branded video featuring you',
      'Builds trust with potential buyers',
      'Professional audio and lighting',
      '48-hour turnaround',
    ],
  ],
];

$zillow_packages = [
    [
      'name' => 'Zillow 3D Tour & Floor Plan',
      'service_url' => $zillow_showcase_page_url,
      'price_cue' => 'Starting at $150',
      'features' => [
        'Zillow 3D virtual tour',
        'Interactive floor plan',
        'Boosts visibility in Zillow search',
        'Fast turnaround',
      ],
    ]
];

$social_content_packages = [
  [
    'name' => 'Brand Presence Reel',
    'service_url' => $social_media_assistance_page_url,
    'price_cue' => 'Starting at $195',
    'features' => [
      'Send raw clips (walkthroughs, tips)',
      'Structured storytelling edit',
      'Branding overlays and captions',
      'Licensed music',
    ],
  ],
  [
    'name' => 'Content Refresh Session',
    'service_url' => $social_media_packages_page_url,
    'price_cue' => 'Starting at $350',
    'features' => [
      '30-minute content shoot',
      '2 edited social reels',
      '1 horizontal video',
      'Professional direction',
    ],
  ],
];

$addons = [
  [
    'name' => 'Twilight Photography (In-person)',
    'desc' => 'On-location twilight session capturing the property in real golden-hour and dusk lighting.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>',
  ],
  [
    'name' => 'AI Dusk Conversions',
    'desc' => 'Transform daytime exterior photos into warm, eye-catching dusk scenes using AI editing.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>',
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
    'name' => 'Heavy Photoshopping',
    'desc' => 'Advanced retouching for removing clutter, swapping skies, or correcting major visual distractions.',
    'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>',
  ],
];

?>

<main>
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>Media Solutions to Grow Your Business</h1>
      <p class="page-hero__sub">Professional photography, videography, and branding content tailored to help agents and businesses stand out and win more deals.</p>
      <div style="margin-top: 24px;">
        <a class="btn btn--accent" href="<?php echo esc_url($order_url); ?>" style="padding: 14px 24px; font-size: 1.1rem;"><?php echo esc_html($book_shoot_button_label); ?></a>
      </div>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section" id="photo-showcasing">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Photo Showcasing</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">High-quality imagery designed to make your properties look their absolute best and capture buyer attention instantly.</p>

      <div class="pkg-grid">
        <?php foreach ($photo_showcasing as $pkg): ?>
          <div class="pkg-card">
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <?php if (!empty($pkg['price_cue'])): ?>
              <div style="text-align: center; color: var(--primary); font-weight: 600; margin-top: -10px; margin-bottom: 20px;"><?php echo esc_html($pkg['price_cue']); ?></div>
            <?php endif; ?>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" style="margin-bottom: 8px;" href="<?php echo esc_url($order_url); ?>"><?php echo esc_html($book_shoot_button_label); ?></a>
            <a class="btn btn--outlineLight pkg-cta" href="<?php echo esc_url((string) ($pkg['service_url'] ?? home_url('/services/'))); ?>" style="border: 1px solid rgba(0,0,0,0.1); background: transparent; color: inherit;"><?php echo esc_html($service_page_button_label); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="video-showcasing">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Video Showcasing</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Cinematic video tours and engaging social reels that tell a story and keep potential buyers engaged longer.</p>

      <div class="pkg-grid">
        <?php foreach ($video_showcasing as $pkg): ?>
          <div class="pkg-card">
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <?php if (!empty($pkg['price_cue'])): ?>
              <div style="text-align: center; color: var(--primary); font-weight: 600; margin-top: -10px; margin-bottom: 20px;"><?php echo esc_html($pkg['price_cue']); ?></div>
            <?php endif; ?>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" style="margin-bottom: 8px;" href="<?php echo esc_url($order_url); ?>"><?php echo esc_html($book_shoot_button_label); ?></a>
            <a class="btn btn--outlineLight pkg-cta" href="<?php echo esc_url((string) ($pkg['service_url'] ?? home_url('/services/'))); ?>" style="border: 1px solid rgba(0,0,0,0.1); background: transparent; color: inherit;"><?php echo esc_html($service_page_button_label); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section" id="social-content">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Business Branding Content</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Professional photo and video content for agents and local businesses to attract new clients and build authority.</p>

      <div class="pkg-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); max-width: 800px; margin: 0 auto;">
        <?php foreach ($social_content_packages as $pkg): ?>
          <div class="pkg-card">
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <?php if (!empty($pkg['price_cue'])): ?>
              <div style="text-align: center; color: var(--primary); font-weight: 600; margin-top: -10px; margin-bottom: 20px;"><?php echo esc_html($pkg['price_cue']); ?></div>
            <?php endif; ?>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" style="margin-bottom: 8px;" href="<?php echo esc_url($order_url); ?>"><?php echo esc_html($book_shoot_button_label); ?></a>
            <a class="btn btn--outlineLight pkg-cta" href="<?php echo esc_url((string) ($pkg['service_url'] ?? home_url('/services/'))); ?>" style="border: 1px solid rgba(0,0,0,0.1); background: transparent; color: inherit;"><?php echo esc_html($service_page_button_label); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="zillow-tours">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Interactive Tours</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Give buyers an immersive experience that keeps them exploring your listings.</p>

      <div class="pkg-grid" style="grid-template-columns: repeat(1, minmax(0, 1fr)); max-width: 400px; margin: 0 auto;">
         <?php foreach ($zillow_packages as $pkg): ?>
          <div class="pkg-card">
            <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
            <?php if (!empty($pkg['price_cue'])): ?>
              <div style="text-align: center; color: var(--primary); font-weight: 600; margin-top: -10px; margin-bottom: 20px;"><?php echo esc_html($pkg['price_cue']); ?></div>
            <?php endif; ?>
            <ul class="pkg-features">
              <?php foreach ($pkg['features'] as $feature): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html($feature); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" style="margin-bottom: 8px;" href="<?php echo esc_url($order_url); ?>"><?php echo esc_html($book_shoot_button_label); ?></a>
            <a class="btn btn--outlineLight pkg-cta" href="<?php echo esc_url((string) ($pkg['service_url'] ?? home_url('/services/'))); ?>" style="border: 1px solid rgba(0,0,0,0.1); background: transparent; color: inherit;"><?php echo esc_html($service_page_button_label); ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section" id="popular-add-ons">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Enhance Your Order</h2>
      <p class="center sub" style="margin-bottom:10px; max-width:820px;">Optional upgrades to tailor your content exactly how you need it.</p>
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

  <section class="page-section page-section--secondary">
      <div class="container" style="text-align: center;">
          <h2>Need something consistent?</h2>
          <p class="sub" style="margin-bottom: 24px;">We offer memberships for agents and businesses looking for recurring, high-quality content output.</p>
          <a class="btn btn--accent" href="<?php echo esc_url(slm_memberships_url()); ?>" style="padding: 12px 24px;">View Memberships</a>
      </div>
  </section>
</main>

<?php get_footer(); ?>
