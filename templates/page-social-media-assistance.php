<?php
/**
 * Template Name: Social Media Assistance
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
$cta_label = 'Get Started';

$assistance_areas = [
  [
    'title' => 'Content planning and shoot guidance',
    'description' => 'What to film, what to say, and practical shot lists for stronger content sessions.',
  ],
  [
    'title' => 'Reel editing',
    'description' => 'Hooks, pacing, and retention cuts designed to keep viewers watching.',
  ],
  [
    'title' => 'Captions and on-screen text overlays',
    'description' => 'Clear messaging with readable overlays that support each video goal.',
  ],
  [
    'title' => 'Branding consistency',
    'description' => 'Fonts, colors, and logo placement aligned to your existing presence.',
  ],
  [
    'title' => 'Music selection and timing',
    'description' => 'Licensed/platform-safe tracks timed to improve flow and watch time.',
  ],
  [
    'title' => 'Posting-ready delivery',
    'description' => 'Vertical formats and optimized exports ready to publish.',
  ],
];

$done_for_you_reels = [
  [
    'name' => 'Local Talk Reel',
    'description' => 'Cinematic drone footage of the area you serve plus your 30–90 second voice memo. We craft a fully branded reel that positions you as the local authority—no on-camera filming required.',
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

$how_it_works = [
  'Send clips/voice memo + a quick note on the goal',
  'We edit with branding, captions, and retention pacing',
  'You review (one revision round)',
  'We deliver posting-ready files',
];

$faqs = [
  [
    'q' => 'Do I need to be on camera?',
    'a' => 'No. Voiceover options are available.',
  ],
  [
    'q' => 'What file types do you accept?',
    'a' => 'Phone videos and voice memos.',
  ],
  [
    'q' => 'Turnaround time?',
    'a' => 'Typically 2–4 business days after receiving assets.',
  ],
  [
    'q' => 'Can you match my branding?',
    'a' => 'Yes. Provide your logo/colors or we can match your existing profiles.',
  ],
];
?>

<main id="main-content">
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>Social Media Assistance</h1>
      <p class="page-hero__sub">We turn your raw clips and ideas into polished, branded content—without you spending hours editing.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section" id="social-assistance-areas">
    <div class="container">
      <h2 class="center" style="margin-top:0;">What We Help With</h2>
      <div class="addon-grid" style="margin-top:30px;">
        <?php foreach ($assistance_areas as $area): ?>
          <article class="addon-card">
            <div class="addon-card__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="addon-card__body">
              <h3 class="addon-card__title"><?php echo esc_html((string) $area['title']); ?></h3>
              <p class="addon-card__desc"><?php echo esc_html((string) $area['description']); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="done-for-you-reels">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Done-For-You Reel Options</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Choose the option that fits the type of content you already have available.</p>

      <div class="pkg-grid">
        <?php foreach ($done_for_you_reels as $option): ?>
          <article class="pkg-card">
            <h3 class="pkg-title"><?php echo esc_html((string) $option['name']); ?></h3>
            <p class="sub" style="margin:0 0 16px;"><?php echo esc_html((string) $option['description']); ?></p>

            <h4 style="margin:0 0 8px; font-size:1rem;">Inputs</h4>
            <ul class="pkg-features" aria-label="<?php echo esc_attr((string) $option['name']); ?> inputs">
              <?php foreach ((array) ($option['inputs'] ?? []) as $input): ?>
                <li>
                  <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <span><?php echo esc_html((string) $input); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>

            <h4 style="margin:8px 0 8px; font-size:1rem;">Deliverables</h4>
            <ul class="pkg-features" aria-label="<?php echo esc_attr((string) $option['name']); ?> deliverables">
              <?php foreach ((array) ($option['deliverables'] ?? []) as $deliverable): ?>
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
    </div>
  </section>

  <section class="page-section" id="social-assistance-how-it-works">
    <div class="container">
      <h2 class="center" style="margin-top:0;">How It Works</h2>
      <div class="portal-card" style="max-width:860px; margin:26px auto 0;">
        <ol style="margin:0; padding-left:22px;">
          <?php foreach ($how_it_works as $step): ?>
            <li style="margin:0 0 10px;"><?php echo esc_html((string) $step); ?></li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="social-assistance-faq">
    <div class="container">
      <h2 class="center" style="margin-top:0;">FAQ</h2>
      <div class="pkg-grid" style="margin-top:30px;">
        <?php foreach ($faqs as $faq): ?>
          <article class="pkg-card">
            <h3 class="pkg-title" style="font-size:1.15rem;"><?php echo esc_html((string) $faq['q']); ?></h3>
            <p style="margin:0;"><?php echo esc_html((string) $faq['a']); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="service-section" id="social-assistance-cta">
    <div class="container">
      <div class="service-finalCta">
        <h2>Want us to take editing off your plate?</h2>
        <p class="sub">Send your clips and we'll turn them into branded content you can post immediately.</p>
        <div class="service-finalCta__actions">
          <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>" aria-label="<?php echo esc_attr($cta_label); ?>"><?php echo esc_html($cta_label); ?></a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php get_footer();
