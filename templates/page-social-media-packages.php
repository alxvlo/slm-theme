<?php
/**
 * Template Name: Social Media Packages
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$aryeo_public_order_form_url = function_exists('slm_aryeo_public_order_form_url')
  ? slm_aryeo_public_order_form_url()
  : '';

$cta_url = $aryeo_public_order_form_url !== ''
  ? $aryeo_public_order_form_url
  : (function_exists('slm_aryeo_start_order_url')
    ? slm_aryeo_start_order_url()
    : slm_page_url_by_template('templates/page-contact.php', '/contact/'));
$cta_label = 'Book a Social Shoot';

$packages = [
  [
    'name' => 'Content Refresh',
    'deliverables' => [
      '30 minute session',
      '2 edited reels',
      '1 horizontal video',
    ],
  ],
  [
    'name' => 'Brand Builder',
    'popular' => true,
    'deliverables' => [
      '1 hour session',
      '4 edited reels',
      '1 talking head video',
      '1 horizontal video',
    ],
  ],
  [
    'name' => 'Consistency Creator',
    'deliverables' => [
      '1.5 hour session',
      '6 edited reels',
      '1 talking head video',
      '1 horizontal video',
      '4 branded insta posts',
    ],
  ],
  [
    'name' => 'Authority Builder',
    'deliverables' => [
      '2 hour session',
      '10 edited reels',
      '1 talking head video',
      '1 horizontal video',
      '10 branded insta posts',
      'Social media post plan',
    ],
  ],
  [
    'name' => 'Market Dominator',
    'deliverables' => [
      'Half-day content shoot',
      '20 edited reels',
      '2 talking head videos',
      '2 horizontal videos',
      '15 branded insta posts',
      'Social media post plan',
    ],
  ],
];
?>

<main id="main-content">
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>Social Media Packages</h1>
      <p class="page-hero__sub">On-brand content, professionally shot and edited for consistent posting and lead generation.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section" id="social-media-packages">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Packages (One-Time)</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Choose the package that matches your current content cadence and growth goals.</p>

      <div class="pkg-grid">
        <?php foreach ($packages as $pkg): ?>
          <article class="pkg-card<?php echo !empty($pkg['popular']) ? ' pkg-card--popular' : ''; ?>">
            <?php if (!empty($pkg['popular'])): ?>
              <div class="pkg-badge">Most Popular</div>
            <?php endif; ?>
            <h3 class="pkg-title"><?php echo esc_html((string) $pkg['name']); ?></h3>
            <ul class="pkg-features" aria-label="<?php echo esc_attr((string) $pkg['name']); ?> deliverables">
              <?php foreach ((array) ($pkg['deliverables'] ?? []) as $deliverable): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html((string) $deliverable); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="center sub" style="margin-top:42px; max-width:940px; margin-left:auto; margin-right:auto; text-align:left;">
        <p style="margin:0 0 8px;"><strong>Definitions:</strong></p>
        <p style="margin:0 0 6px;"><strong>Reel:</strong> short vertical video (typically under 60–90 seconds)</p>
        <p style="margin:0 0 6px;"><strong>Horizontal:</strong> landscape video for tours/introductions with a more luxury feel</p>
        <p style="margin:0 0 6px;"><strong>Talking Head:</strong> agent speaks to camera to share information/insights</p>
        <p style="margin:0 0 6px;"><strong>Branded Insta Posts:</strong> branded photo/graphic posts that reflect your identity (colors, logo, fonts, messaging)</p>
        <p style="margin:0;"><strong>Social Media Plan:</strong> structured outline of what to post, when to post, and why</p>
      </div>
    </div>
  </section>

  <section class="service-section" id="social-media-packages-cta">
    <div class="container">
      <div class="service-finalCta">
        <h2>Ready to Build Consistent Social Content?</h2>
        <p class="sub">Pick a package and let us handle the shooting and editing so you can stay visible and focused on clients.</p>
        <div class="service-finalCta__actions">
          <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>" aria-label="<?php echo esc_attr($cta_label); ?>"><?php echo esc_html($cta_label); ?></a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php get_footer();
