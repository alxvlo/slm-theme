<?php
/**
 * Template Name: Social Mentorship Program
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$contact_email = 'showcaselistingsmedia@gmail.com';
$cta_url = 'mailto:' . $contact_email . '?subject=' . rawurlencode('Social Mentorship Program');

$walkaway = [
  [
    'title' => 'Optimized IG + Facebook',
    'description' => 'A fully optimized Instagram and Facebook presence built to be found and followed.',
  ],
  [
    'title' => 'Custom content strategy',
    'description' => 'A content strategy built around your specific business, not a generic template.',
  ],
  [
    'title' => 'Repeatable posting system',
    'description' => 'A weekly posting system so you never have to guess what to post again.',
  ],
  [
    'title' => 'Capture while you work',
    'description' => 'Learn to capture content while you are already working — no extra time wasted.',
  ],
  [
    'title' => 'Real confidence online',
    'description' => 'The habits and confidence to keep showing up long after we are done.',
  ],
  [
    'title' => 'Weekly content rhythm',
    'description' => '3 short-form Reels, 2–3 educational posts, and 1 authority post — captions and SEO hashtags ready to go.',
  ],
];

$pricing = [
  [
    'name' => '2-Month Program',
    'price' => '$1,200',
    'cadence' => '/month',
    'total' => '$2,400 total',
    'note' => 'A focused build to get your system up and running.',
    'recommended' => false,
  ],
  [
    'name' => '3-Month Program',
    'price' => '$1,000',
    'cadence' => '/month',
    'total' => '$3,000 total',
    'note' => 'Recommended for best results — more time to build lasting habits.',
    'recommended' => true,
  ],
];
?>

<main id="main-content">
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <p class="smp-eyebrow">A service by Brittney Tribble</p>
      <h1>Social Mentorship Program</h1>
      <p class="smp-tagline">Social media that <em>actually builds your business.</em></p>
      <p class="page-hero__sub">A 2–3 month hands-on program where I work with you to build a social media system from scratch — strategy, content, scheduling, and the habits to keep it going long after we're done.</p>
      <div class="smp-hero-actions">
        <button type="button" class="btn btn--accent" data-smp-open>Learn More</button>
        <a class="btn btn--secondary" href="<?php echo esc_url($cta_url); ?>">Get Started</a>
      </div>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section" id="smp-overview">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Who It's For</h2>
      <p class="center sub" style="max-width:820px; margin:14px auto 0;">Any local business owner — contractors, realtors, service providers, restaurants, salons, you name it — who wants to stop posting randomly and start showing up with a real plan.</p>
    </div>
  </section>

  <section class="page-section page-section--secondary" id="smp-walkaway">
    <div class="container">
      <h2 class="center" style="margin-top:0;">What You Walk Away With</h2>
      <div class="addon-grid" style="margin-top:30px;">
        <?php foreach ($walkaway as $item): ?>
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

  <section class="page-section" id="smp-pricing">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Pricing</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Choose the program length that fits your goals. Both include the full hands-on build.</p>

      <div class="pkg-grid smp-pricing-grid">
        <?php foreach ($pricing as $plan): ?>
          <article class="pkg-card<?php echo $plan['recommended'] ? ' smp-pkg--featured' : ''; ?>">
            <?php if ($plan['recommended']): ?>
              <span class="smp-badge">Recommended</span>
            <?php endif; ?>
            <h3 class="pkg-title"><?php echo esc_html((string) $plan['name']); ?></h3>
            <p class="smp-price"><?php echo esc_html((string) $plan['price']); ?><span class="smp-price__cadence"><?php echo esc_html((string) $plan['cadence']); ?></span></p>
            <p class="smp-price__total"><?php echo esc_html((string) $plan['total']); ?></p>
            <p class="sub" style="margin:6px 0 18px;"><?php echo esc_html((string) $plan['note']); ?></p>
            <a class="btn btn--accent smp-btn-block" href="<?php echo esc_url($cta_url); ?>">Get Started</a>
          </article>
        <?php endforeach; ?>
      </div>

      <p class="center sub" style="margin-top:24px;">After mentorship: optional $200/session monthly check-ins.</p>
    </div>
  </section>

  <section class="service-section" id="smp-cta">
    <div class="container">
      <div class="service-finalCta">
        <h2>Ready to stop guessing and start showing up?</h2>
        <p class="sub">This program fixes the three things most businesses get wrong online — and builds something that keeps working even when you're not actively marketing.</p>
        <div class="service-finalCta__actions">
          <button type="button" class="btn btn--accent" data-smp-open>See the Full Breakdown</button>
          <a class="btn btn--secondary" href="<?php echo esc_url($cta_url); ?>">Email Me Directly</a>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- Learn More modal -->
<div class="smp-modal" data-smp-modal hidden>
  <div class="smp-modal__overlay" data-smp-close></div>
  <div class="smp-modal__card" role="dialog" aria-modal="true" aria-labelledby="smp-modal-title">
    <button type="button" class="smp-modal__close" data-smp-close aria-label="Close">&times;</button>
    <h2 id="smp-modal-title" class="smp-modal__title">Social Mentorship Program</h2>
    <p>Happy to walk you through it! Here's a quick breakdown of everything the mentorship covers:</p>

    <h3>What it is</h3>
    <p>A 2–3 month hands-on program where I work with you to build a social media system from scratch — strategy, content, scheduling, and the habits to keep it going long after we're done. Setting you up with different options for programs to help streamline your social handling.</p>

    <h3>Who it's for</h3>
    <p>Any local business owner — contractors, realtors, service providers, restaurants, salons, you name it — who wants to stop posting randomly and start showing up with a real plan.</p>

    <h3>What you walk away with</h3>
    <ul>
      <li>A fully optimized Instagram and Facebook presence</li>
      <li>A custom content strategy built around your business</li>
      <li>A repeatable weekly posting system (no more guessing what to post)</li>
      <li>The ability to capture content while you're already working, no extra time wasted</li>
      <li>Real confidence showing up online</li>
    </ul>

    <h3>The weekly rhythm</h3>
    <p>3 short-form videos (Reels), 2–3 educational posts, and 1 authority/credibility post — all with captions and SEO hashtags ready to go.</p>

    <h3>Pricing</h3>
    <ul>
      <li>2-month program: $1,200/month ($2,400 total)</li>
      <li>3-month program: $1,000/month ($3,000 total) — recommended for best results</li>
      <li>After mentorship: optional $200/session, monthly check-ins</li>
    </ul>

    <h3>The big picture</h3>
    <p>Most businesses post randomly, don't educate their audience, and never build real trust online. This program fixes all three and builds something that keeps working for you even when you're not actively marketing. It also teaches you to do it on your own without constantly needing a social media coordinator.</p>

    <p>If you're ready to get started or just have questions, reach out directly at <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a> — I'd love to chat!</p>

    <div class="smp-modal__actions">
      <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>">Email Me to Get Started</a>
    </div>
  </div>
</div>

<style>
  /* ---- Social Mentorship Program: scoped styles ---- */
  .smp-eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.18em;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--accent);
    margin: 0 0 10px;
  }
  .smp-tagline {
    font-family: var(--font-display);
    font-size: clamp(1.3rem, 2.4vw, 1.9rem);
    font-weight: 600;
    color: #ffffff;
    margin: 14px auto 0;
    max-width: 760px;
  }
  .smp-tagline em {
    font-style: italic;
    color: var(--accent);
  }
  .smp-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    justify-content: center;
    margin-top: 26px;
    position: relative;
    z-index: 2;
  }

  .smp-pricing-grid {
    max-width: 880px;
    margin-inline: auto;
  }
  .smp-pkg--featured {
    border: 2px solid var(--accent);
    position: relative;
    box-shadow: var(--shadow-md);
  }
  .smp-badge {
    position: absolute;
    top: -14px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--accent);
    color: var(--accent-foreground);
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 6px 14px;
    border-radius: 999px;
    white-space: nowrap;
  }
  .smp-price {
    font-family: var(--font-display);
    font-size: 2.4rem;
    font-weight: 700;
    color: var(--primary);
    margin: 10px 0 0;
    line-height: 1;
  }
  .smp-price__cadence {
    font-size: 1rem;
    font-weight: 500;
    color: var(--muted-foreground);
    margin-left: 4px;
  }
  .smp-price__total {
    color: var(--muted-foreground);
    margin: 4px 0 0;
  }
  .smp-btn-block {
    display: inline-block;
    width: 100%;
    text-align: center;
  }

  /* ---- Modal ---- */
  .smp-modal[hidden] { display: none; }
  .smp-modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }
  .smp-modal__overlay {
    position: absolute;
    inset: 0;
    background: rgba(11, 25, 44, 0.6);
    -webkit-backdrop-filter: blur(3px);
    backdrop-filter: blur(3px);
  }
  .smp-modal__card {
    position: relative;
    z-index: 1;
    background: var(--surface);
    color: var(--foreground);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    max-width: 620px;
    width: 100%;
    max-height: 86vh;
    overflow-y: auto;
    padding: 40px 38px 34px;
  }
  .smp-modal__title {
    margin: 0 0 6px;
    color: var(--primary);
  }
  .smp-modal__card h3 {
    margin: 22px 0 6px;
    font-size: 1.12rem;
    color: var(--primary);
  }
  .smp-modal__card p { margin: 0 0 10px; }
  .smp-modal__card ul {
    margin: 0 0 10px;
    padding-left: 20px;
  }
  .smp-modal__card li { margin: 0 0 6px; }
  .smp-modal__close {
    position: absolute;
    top: 14px;
    right: 16px;
    background: transparent;
    border: 0;
    font-size: 2rem;
    line-height: 1;
    color: var(--muted-foreground);
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 8px;
  }
  .smp-modal__close:hover { color: var(--foreground); background: var(--muted); }
  .smp-modal__actions { margin-top: 22px; }
  body.smp-modal-open { overflow: hidden; }

  @media (max-width: 560px) {
    .smp-modal__card { padding: 34px 22px 26px; }
    .smp-price { font-size: 2rem; }
  }
</style>

<?php get_footer();
