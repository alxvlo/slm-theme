<?php
/**
 * Template Name: Social Media Management
 *
 * Done-for-you social media management landing page. Copy is verbatim from
 * Brittney's "SOCIAL MEDIA MANAGEMENT — Page Copy" doc. SEO title/description are
 * set as post meta in functions.php (slm_meta_title / slm_meta_description) and
 * emitted by the generic system in inc/seo.php — no inline filter needed here.
 *
 * Pricing for the three tiers is intentionally omitted pending confirmed numbers;
 * every CTA points at the contact/strategy-call flow instead of a price.
 */
if (!defined('ABSPATH'))
  exit;

get_header();

// Consult-first offer: the tiers have no set price, so every CTA routes to the
// contact page (the lead form) rather than the Aryeo order form used by the
// one-time Social Media Packages / Assistance pages.
$cta_url   = home_url('/contact/');
$cta_label = 'Book a Free Strategy Call';
$phone_tel = 'tel:+19042945809';

$mentorship_url = slm_page_url_by_template('templates/page-social-mentorship-program.php', '/social-mentorship-program/');

// Positioning table — Content Packages give you the assets; Management does the
// work of getting them posted and turned into leads.
$comparison_rows = [
  ['label' => 'Professional shoot',             'content' => true,  'management' => true],
  ['label' => 'Edited reels & branded posts',   'content' => true,  'management' => true],
  ['label' => 'Captions & hashtags',            'content' => true,  'management' => true],
  ['label' => 'Posting & scheduling',           'content' => false, 'management' => true],
  ['label' => 'Profile optimization',           'content' => false, 'management' => true],
  ['label' => 'DM keyword lead capture',        'content' => false, 'management' => true],
  ['label' => 'Comment & DM monitoring',        'content' => false, 'management' => true],
  ['label' => 'Monthly performance report',     'content' => false, 'management' => true],
];

$whats_included = [
  [
    'title' => 'Content creation',
    'description' => 'A monthly on-site shoot plus edited reels, talking-head clips, and branded posts, all matched to your brand colors, fonts, and voice.',
  ],
  [
    'title' => 'Strategy & calendar',
    'description' => "A monthly content calendar built around your goals, your seasonality, and what's working — reviewed with you before anything goes live.",
  ],
  [
    'title' => 'Captions, hashtags & SEO',
    'description' => 'Every post written to be found: local keywords, researched hashtag sets, and hooks that stop the scroll.',
  ],
  [
    'title' => 'Posting & scheduling',
    'description' => 'We publish at the right times, on the right platforms (Instagram + Facebook standard; add-ons available).',
  ],
  [
    'title' => 'DM lead capture',
    'description' => 'Keyword automations (like "GREEN" or "READY") that turn commenters and viewers into real leads in your inbox — with every lead tracked.',
  ],
  [
    'title' => 'Engagement monitoring',
    'description' => 'We keep an eye on comments and DMs during business hours and flag hot leads to you same-day.',
  ],
  [
    'title' => 'Monthly reporting',
    'description' => "A plain-English scorecard: what we posted, what performed, what leads came in, and what we're doing next month.",
  ],
];

$tiers = [
  [
    'name' => 'Presence',
    'lead' => 'For businesses that need to stay consistently visible.',
    'features' => [
      '1 content session per month (45 min)',
      '8 posts/month (mix of reels + branded posts)',
      'Captions, hashtags, scheduling & posting',
      'Profile optimization (one-time setup)',
      'Monthly report',
    ],
  ],
  [
    'name' => 'Growth',
    'popular' => true,
    'lead' => 'For businesses ready to turn social into a lead channel.',
    'features' => [
      '1 content session per month (90 min)',
      '12–16 posts/month including 8+ reels',
      'Everything in Presence, plus:',
      'DM keyword lead capture & tracking',
      'Comment/DM monitoring with same-day lead flagging',
      'Monthly strategy call',
    ],
  ],
  [
    'name' => 'Authority',
    'lead' => 'For brands that want to own their market.',
    'features' => [
      '2 content sessions per month (or half-day monthly)',
      "20+ posts/month across Instagram, Facebook + 1 add'l platform",
      'Everything in Growth, plus:',
      'Stories support & community engagement',
      'Quarterly content strategy refresh',
      'Priority scheduling & turnaround',
    ],
  ],
];

$faqs = [
  [
    'q' => 'What platforms do you manage?',
    'a' => 'Instagram and Facebook are standard. Additional platforms (TikTok, YouTube Shorts, Google Business Profile, LinkedIn) are available as add-ons.',
  ],
  [
    'q' => 'Do I approve posts before they go live?',
    'a' => "Yes — you'll see the monthly calendar up front, and nothing is published without your sign-off on the plan. (Prefer full autopilot after month one? That works too.)",
  ],
  [
    'q' => 'Who owns the content?',
    'a' => 'You do. Everything we create for your accounts is yours to keep, forever — even if we part ways.',
  ],
  [
    'q' => 'Is there a contract?',
    'a' => 'A 3-month minimum to start (real momentum takes at least that long), then month-to-month.',
  ],
  [
    'q' => 'What do you need from me?',
    'a' => 'About 2 hours a month: the content session plus a quick strategy check-in. We handle the rest.',
  ],
];
?>

<main id="main-content">
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>We Post. You Grow.</h1>
      <p class="page-hero__sub">Full-service social media management for North Florida agents and businesses. We create the content, write the captions, schedule the posts, capture the leads, and report the results — so your presence grows while you do the work you're actually good at.</p>
      <p style="margin:24px 0 0;">
        <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>" aria-label="<?php echo esc_attr($cta_label); ?>"><?php echo esc_html($cta_label); ?></a>
      </p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section" id="sm-management-positioning">
    <div class="container">
      <h2 class="center" style="margin-top:0;">The difference between content and management</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Content packages hand you great assets. Management makes sure they actually get posted, seen, and turned into leads.</p>

      <div class="cmp-wrap">
        <table class="cmp-table">
          <thead>
            <tr>
              <th scope="col" class="cmp-feature">What you get</th>
              <th scope="col">Content Packages</th>
              <th scope="col" class="cmp-col--mgmt">Management</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($comparison_rows as $row): ?>
              <tr>
                <th scope="row" class="cmp-feature"><?php echo esc_html((string) $row['label']); ?></th>
                <td>
                  <?php if (!empty($row['content'])): ?>
                    <svg class="cmp-check" viewBox="0 0 24 24" fill="none" role="img" aria-label="Included">
                      <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  <?php else: ?>
                    <span class="cmp-no" aria-label="Not included">&mdash;</span>
                  <?php endif; ?>
                </td>
                <td class="cmp-col--mgmt">
                  <?php if (!empty($row['management'])): ?>
                    <svg class="cmp-check" viewBox="0 0 24 24" fill="none" role="img" aria-label="Included">
                      <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  <?php else: ?>
                    <span class="cmp-no" aria-label="Not included">&mdash;</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <p class="center sub" style="margin-top:28px; max-width:820px; font-style:italic;">If you've ever paid for great content and then watched it sit in a folder — this is the fix.</p>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="sm-management-included">
    <div class="container">
      <h2 class="center" style="margin-top:0;">What's Included</h2>
      <div class="addon-grid" style="margin-top:30px;">
        <?php foreach ($whats_included as $item): ?>
          <article class="addon-card">
            <div class="addon-card__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <div class="addon-card__body">
              <h3 class="addon-card__title"><?php echo esc_html((string) $item['title']); ?></h3>
              <p class="addon-card__desc"><?php echo esc_html((string) $item['description']); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section" id="sm-management-tiers">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Choose Your Level of Support</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Every plan is done-for-you. Pick the level of output and support that matches where your business is right now.</p>

      <div class="pkg-grid">
        <?php foreach ($tiers as $tier): ?>
          <article class="pkg-card<?php echo !empty($tier['popular']) ? ' pkg-card--popular' : ''; ?>">
            <?php if (!empty($tier['popular'])): ?>
              <div class="pkg-badge">Most Popular</div>
            <?php endif; ?>
            <h3 class="pkg-title"><?php echo esc_html((string) $tier['name']); ?></h3>
            <p class="sub" style="margin:0 0 18px; text-align:center;"><?php echo esc_html((string) $tier['lead']); ?></p>
            <ul class="pkg-features" aria-label="<?php echo esc_attr((string) $tier['name']); ?> features">
              <?php foreach ((array) ($tier['features'] ?? []) as $feature): ?>
                <?php if (strpos((string) $feature, 'Everything in') === 0): ?>
                  <li class="pkg-feature--group"><span><?php echo esc_html((string) $feature); ?></span></li>
                <?php else: ?>
                  <li>
                    <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span><?php echo esc_html((string) $feature); ?></span>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ul>
            <a class="btn btn--secondary pkg-cta" href="<?php echo esc_url($cta_url); ?>" aria-label="<?php echo esc_attr($cta_label . ' — ' . (string) $tier['name']); ?>"><?php echo esc_html($cta_label); ?></a>
          </article>
        <?php endforeach; ?>
      </div>

      <p class="center sub" style="margin-top:26px;">All tiers: 3-month minimum, then month-to-month.</p>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="sm-management-who">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Who It's For</h2>
      <p class="center sub" style="margin:24px auto 0; max-width:820px;">Local businesses and real estate agents who are past DIY: you know social media drives business, you don't have 10 hours a week to do it right, and you'd rather pay for results than another course. (If you'd rather learn to run it yourself, that's exactly what the <a href="<?php echo esc_url($mentorship_url); ?>">Mentorship Program</a> is for.)</p>
    </div>
  </section>

  <section class="page-section" id="sm-management-faq">
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

  <section class="service-section" id="sm-management-cta">
    <div class="container">
      <div class="service-finalCta">
        <h2>Your competitors are posting. Let's make sure you're the one they can't keep up with.</h2>
        <div class="service-finalCta__actions">
          <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>" aria-label="<?php echo esc_attr($cta_label); ?>"><?php echo esc_html($cta_label); ?></a>
          <a class="btn btn--outlineLight" href="<?php echo esc_url($phone_tel); ?>">Call (904) 294-5809</a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php get_footer();
