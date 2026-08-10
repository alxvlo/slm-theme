<?php
/**
 * Template Name: FAQ
 *
 * Review item 18. SEO title/description are post meta set by the auto-create
 * hook in functions.php and emitted by inc/seo.php — no inline filter here.
 * The FAQPage JSON-LD and the visible accordion are both rendered from
 * $faq_groups so the schema can never drift from the page.
 *
 * Answers flagged in docs/superpowers/plans/2026-08-10-website-review-round-2.md
 * as [client-approve] use deliberately conservative wording until the client
 * confirms the real policy.
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$book_url    = slm_book_url();
$contact_url = home_url('/contact/');

$faq_groups = [
  [
    'title' => 'Booking & Scheduling',
    'items' => [
      [
        'q' => 'How do I book a shoot?',
        'a' => 'Click "Book a Shoot" anywhere on the site to schedule online — pick your services, choose a time, and you are set. Prefer to talk it through first? Send us a message on the contact page and we will help you build the right order.',
      ],
      [
        'q' => 'How far in advance should I book?',
        'a' => 'As soon as you have a date in mind. We do our best to accommodate short-notice and next-day requests when the schedule allows — reach out and we will find a slot.',
      ],
      [
        'q' => 'What areas do you serve?',
        'a' => 'We cover five North Florida counties: Duval, St. Johns, Clay, Nassau, and Putnam. From Ponte Vedra to Fleming Island, if your listing or storefront is in Northeast Florida, we have you covered.',
      ],
      [
        'q' => 'What happens if it rains?',
        'a' => 'North Florida weather can turn quickly. If conditions would compromise your media, we will work with you to reschedule as soon as possible, and exterior or drone segments can often be completed on a follow-up visit.',
      ],
      [
        'q' => 'Does the seller need to be home during the shoot?',
        'a' => 'No — we just need access to the property. Homes photograph best when they are show-ready before we arrive: lights on, blinds open, counters clear.',
      ],
    ],
  ],
  [
    'title' => 'Delivery & Media',
    'items' => [
      [
        'q' => 'How fast will I get my media?',
        'a' => 'Edited photos and video are delivered within 24–48 hours of the shoot.',
      ],
      [
        'q' => 'How many photos will I receive?',
        'a' => 'It depends on the property and the services you order — larger homes and add-ons like drone or twilight increase the count. Every delivery includes enough coverage to market the property across MLS, social, and print.',
      ],
      [
        'q' => 'How is my media delivered?',
        'a' => 'Through your client portal — ready to download, share, and post the moment it lands.',
      ],
    ],
  ],
  [
    'title' => 'Services & Policies',
    'items' => [
      [
        'q' => 'Do you work with businesses, or just real estate agents?',
        'a' => 'Both. We produce premium listing media for agents and the same scroll-stopping content and social media systems for local businesses across North Florida.',
      ],
      [
        'q' => 'Are your drone pilots certified?',
        'a' => 'Yes — our aerial photo and video work is FAA-certified.',
      ],
      [
        'q' => 'Do you offer memberships?',
        'a' => 'Yes. Listing memberships reduce your cost per shoot, and content memberships keep your brand visible between listings — for agents and businesses alike.',
      ],
      [
        'q' => 'What is your cancellation policy?',
        'a' => 'Plans change — let us know as soon as possible and we will reschedule your shoot for the next time that works. Reach out through the contact page or your client portal.',
      ],
    ],
  ],
];

$schema_entities = [];
foreach ($faq_groups as $group) {
  foreach ($group['items'] as $item) {
    $schema_entities[] = [
      '@type' => 'Question',
      'name' => (string) $item['q'],
      'acceptedAnswer' => [
        '@type' => 'Answer',
        'text' => (string) $item['a'],
      ],
    ];
  }
}
$faq_schema = [
  '@context' => 'https://schema.org',
  '@type' => 'FAQPage',
  'mainEntity' => $schema_entities,
];
?>

<main id="main-content">

  <section class="svc-hero" aria-label="Frequently asked questions">
    <div class="container">
      <div class="svc-hero__content">
        <p class="svc-hero__eyebrow">Jacksonville &amp; North Florida</p>
        <h1 class="svc-hero__title">Frequently Asked Questions</h1>
        <p class="svc-hero__sub">Everything you need to know about booking, shoot day, and getting your media — answered.</p>
      </div>
    </div>
  </section>

  <section class="faq-section" aria-label="Answers">
    <div class="container">
      <?php foreach ($faq_groups as $group): ?>
        <div class="faq-group">
          <h2><?php echo esc_html($group['title']); ?></h2>
          <div class="faq-list">
            <?php foreach ($group['items'] as $item): ?>
              <details class="faq-item">
                <summary><?php echo esc_html($item['q']); ?></summary>
                <p><?php echo esc_html($item['a']); ?></p>
              </details>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="faq-cta">
        <h2>Still have a question?</h2>
        <p>We are happy to help — send us a message, or book your shoot and we will handle the details.</p>
        <p class="faq-cta__actions">
          <a class="btn btn--accent" href="<?php echo esc_url($book_url); ?>">Book a Shoot</a>
          <a class="btn btn--secondary" href="<?php echo esc_url($contact_url); ?>">Contact Us</a>
        </p>
      </div>
    </div>
  </section>

  <script type="application/ld+json"><?php echo wp_json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>

</main>

<?php
get_footer();
