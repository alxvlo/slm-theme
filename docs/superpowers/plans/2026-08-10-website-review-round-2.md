# Website Review Round 2 (FAQ + Service FAQs + Cleanup) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Close the remaining THEME items from `memory-bank/website-review-2026-08-06.md` — a sitewide FAQ page with `FAQPage` schema (item 18), per-service FAQ blocks (the deliverable half of item 20), the Mentorship page SEO gap (item 7's last unchecked box), and a documentation sync — without touching anything the live site already does correctly.

**Architecture:** Follow the theme's existing patterns exactly: page templates in `templates/`, auto-created WordPress pages via transient-guarded `init` hooks in `functions.php` (the `for-businesses` pattern, which also sets `slm_meta_title`/`slm_meta_description` post meta consumed by `inc/seo.php`), URL helpers via `slm_page_url_by_template()`, shared UI in `template-parts/blocks/`, page CSS appended to `assets/css/pages.css`, and repo tests in `tests/` run by `php run-tests.php`.

**Tech Stack:** PHP (WordPress theme, no build step), vanilla CSS, native `<details>/<summary>` accordions (no JS needed), JSON-LD via `wp_json_encode()`.

## Verified baseline (live site, 2026-08-10, viewed logged-in as admin)

Captured before any changes, for after/before comparison:

- **Home** — unified nav (Home / Services ▾ / More ▾ / Dashboard / Book a Shoot / Logout), hero slider working.
- **/our-portfolio/** — "Our Work Speaks for Itself", featured project "6000 on the River", **gallery now populated with many photos** (item 1 has progressed since the review; per-category counts still need verification in WP Admin).
- **/services/** — new hero copy, Services dropdown group headers ("Listing Media", "Social & Brand Content", "Memberships") render as **non-clickable labels** (item 5 not reproducible in this view).
- **/for-businesses/** — live and complete (item 13's page exists; the Services-page card links to it via `slm_for_businesses_url()` — `templates/page-services.php:325`).
- **/faq/** — **404**. Item 18 is the confirmed missing piece.
- Footer everywhere — agents-AND-businesses wording live (item 11 confirmed fixed in production).
- **Prod renders an assigned WP menu, not the code fallback** (the live "More" dropdown contains items — e.g. Mentorship — that `slm_primary_nav_fallback()` in `functions.php:347-362` does not emit). Any new nav link therefore needs BOTH the code fallback (staging) and a WP Admin menu edit (prod).
- Logged-in admin view bypasses cache; items 3/11/12 still need a **logged-out** re-check after a cache purge (item 4).

### Review item reality check

| Item | Review status | Actual status found 2026-08-10 |
|------|--------------|-------------------------------|
| 1 Portfolio content | open (ADMIN) | Largely populated; verify per-category counts in WP Admin |
| 3 Two nav menus | open (ADMIN) | Not reproducible logged-in; re-check logged-out after purge |
| 4 Stale cache | open (INFRA) | User purge required (Bluehost "Caching" + any CDN) |
| 5 Dead `#` links | open (ADMIN) | Headers render non-clickable now; re-check logged-out |
| 9 Testimonials | open (ADMIN) | Untouched — WP Admin entry, needs written permission |
| 13 Services card link | open (THEME+ADMIN) | **Already fixed and live** — update doc status |
| 18 FAQ page | open (THEME+ADMIN) | **Missing — Task 1/2 of this plan** |
| 20 Per-service content | open (THEME) | FAQ half → Task 3; detail/pricing half **blocked on client facts** |
| 7 SEO rollout | done except 3 pages | For Businesses + SMM covered via post meta; **Mentorship still uncovered → Task 4** |

## Global Constraints

Copied from `AGENTS.md` (Content Consistency Rules) — every task below implicitly includes these:

- Turnaround time is always written **"24–48 hours"** (en dash). Never "24-hour standard delivery".
- Booking CTAs resolve through **`slm_book_url()`** — never a new per-template `$cta_url` / `$order_url` / `$book_url` definition.
- Portfolio URL resolves via `slm_page_url_by_template('templates/page-portfolio.php', '/our-portfolio/')`.
- Exactly **one `<h1>` per page**.
- Any new page template must have SEO title + meta description (here: via `slm_meta_title` / `slm_meta_description` post meta set in the auto-create hook, consumed by `inc/seo.php`).
- Prefix all new functions/meta/transients with `slm_`; slugs lowercase kebab-case.
- 2-space indentation; `ABSPATH` guard at top of every PHP entry file; escape all output (`esc_html`, `esc_url`, `esc_attr`).
- CSS uses the variables in `assets/css/base.css` (`--surface`, `--border`, `--muted-foreground`, `--accent`, `--foreground`, `--primary`); page-specific styles go in `assets/css/pages.css`.
- Syntax-check every changed file: `php -l <file>`. Full suite: `php run-tests.php` from the repo root.
- Work on the `staging` branch. Do NOT deploy from this repo — deployment is a manual cPanel action (see Handoff).
- Do not invent business facts (photo counts, shoot durations, policies, certifications). Every FAQ answer below is sourced from existing site copy or canonical review decisions; answers marked **[client-approve]** in Task 1's copy table are conservatively phrased and listed in the Handoff for client sign-off before the production deploy.

## File Structure

- Create: `tests/test-faq-page.php` — guards the FAQ template, schema, auto-creation, and footer link.
- Create: `templates/page-faq.php` — FAQ page template (content array → accordion markup + `FAQPage` JSON-LD from the same array).
- Modify: `functions.php` — `slm_faq_url()` helper (near the other URL helpers, after `slm_social_media_management_url()` around line 176); FAQ auto-create `init` hook (priority 11, after the service-area hook at priority 10); Mentorship auto-create `init` hook (priority 12); FAQ link in `slm_primary_nav_fallback()`.
- Modify: `template-parts/site/footer.php` — FAQ link in "Important Links".
- Modify: `assets/css/pages.css` — `.faq-*` styles (shared by the FAQ page and service FAQ blocks).
- Create: `tests/test-service-faqs.php` — guards the per-service FAQ rollout.
- Modify: `template-parts/blocks/service-detail.php` — new `faqs` arg → FAQ section + JSON-LD.
- Modify: all eight `templates/page-service-*.php` — pass `faqs` data.
- Modify: `memory-bank/website-review-2026-08-06.md` — status sync.

---

### Task 1: FAQ page (review item 18)

**Files:**
- Create: `tests/test-faq-page.php`
- Create: `templates/page-faq.php`
- Modify: `functions.php` (helper after `slm_social_media_management_url()` ~line 176; init hook after the priority-10 service-area hook ~line 905; nav fallback `slm_primary_nav_fallback()` ~line 347)
- Modify: `template-parts/site/footer.php:135-138`
- Modify: `assets/css/pages.css` (append)

**Interfaces:**
- Consumes: `slm_page_url_by_template()` (`functions.php:111`), `slm_book_url()` (`functions.php:205`), `inc/seo.php` post-meta SEO system, existing `.svc-hero` CSS pattern.
- Produces: `slm_faq_url(): string` — used by footer, nav fallback, and later tasks. CSS classes `.faq-list`, `.faq-item` — reused by Task 3's service FAQ block.

- [ ] **Step 1: Write the failing tests**

Create `tests/test-faq-page.php`:

```php
<?php
/**
 * Review item 18 — FAQ page: template, FAQPage schema, auto-creation, links.
 */

require_once __DIR__ . '/helpers.php';

function test_faq_template_exists()
{
    assert(
        file_exists(slm_test_theme_dir() . '/templates/page-faq.php'),
        'templates/page-faq.php is missing'
    );
    echo "PASS: test_faq_template_exists\n";
}

function test_faq_template_emits_faqpage_schema_from_data()
{
    $faq = slm_test_read('templates/page-faq.php');
    assert(
        strpos($faq, 'application/ld+json') !== false,
        'page-faq.php should emit a JSON-LD block'
    );
    assert(
        strpos($faq, "'FAQPage'") !== false,
        'The JSON-LD block should declare @type FAQPage'
    );
    assert(
        strpos($faq, 'wp_json_encode') !== false,
        'Schema must be built with wp_json_encode from the same $faq_groups array — no hand-written JSON'
    );
    echo "PASS: test_faq_template_emits_faqpage_schema_from_data\n";
}

function test_faq_template_uses_canonical_copy()
{
    $faq = slm_test_read('templates/page-faq.php');
    assert(
        strpos($faq, '24–48 hours') !== false,
        'The turnaround answer must use the canonical "24–48 hours" wording'
    );
    assert(
        strpos($faq, 'slm_book_url()') !== false,
        'The booking CTA must resolve through slm_book_url()'
    );
    assert(
        substr_count($faq, '<h1') === 1,
        'Exactly one <h1> on the FAQ page'
    );
    echo "PASS: test_faq_template_uses_canonical_copy\n";
}

function test_faq_page_is_auto_created_with_seo_meta()
{
    $functions = slm_test_read('functions.php');
    assert(
        strpos($functions, "get_transient('slm_faq_page_exists')") !== false,
        'functions.php should auto-create the FAQ page behind the slm_faq_page_exists transient'
    );
    assert(
        strpos($functions, "'post_name' => 'faq'") !== false,
        'The auto-created page slug must be "faq"'
    );
    $hook = substr($functions, strpos($functions, "get_transient('slm_faq_page_exists')"));
    $hook = substr($hook, 0, strpos($hook, 'set_transient'));
    assert(
        substr_count($hook, "'slm_meta_title'") === 2 && substr_count($hook, "'slm_meta_description'") === 2,
        'Both the create and already-exists branches must set slm_meta_title and slm_meta_description'
    );
    echo "PASS: test_faq_page_is_auto_created_with_seo_meta\n";
}

function test_faq_url_helper_exists()
{
    $functions = slm_test_read('functions.php');
    assert(
        strpos($functions, 'function slm_faq_url()') !== false,
        'slm_faq_url() helper is missing from functions.php'
    );
    assert(
        strpos($functions, "slm_page_url_by_template('templates/page-faq.php', '/faq/')") !== false,
        'slm_faq_url() must resolve via slm_page_url_by_template with the /faq/ fallback'
    );
    echo "PASS: test_faq_url_helper_exists\n";
}

function test_footer_and_nav_fallback_link_to_faq()
{
    $footer = slm_test_read('template-parts/site/footer.php');
    assert(
        strpos($footer, 'slm_faq_url()') !== false,
        'The footer Important Links section should link to the FAQ page'
    );
    $functions = slm_test_read('functions.php');
    assert(
        strpos($functions, '\'<li><a href="\' . esc_url(slm_faq_url()) . \'">FAQ</a></li>\'') !== false,
        'slm_primary_nav_fallback() should echo the FAQ link (staging renders the fallback)'
    );
    echo "PASS: test_footer_and_nav_fallback_link_to_faq\n";
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run from the repo root: `php run-tests.php`
Expected: the six new `test_faq_*` tests FAIL (template missing, helper missing); every pre-existing test still PASSES.

- [ ] **Step 3: Add `slm_faq_url()` to `functions.php`**

Insert directly after the `slm_social_media_management_url()` function (ends ~line 177), before `slm_service_area_url()`:

```php
function slm_faq_url(): string
{
  return slm_page_url_by_template('templates/page-faq.php', '/faq/');
}
```

- [ ] **Step 4: Create `templates/page-faq.php`**

The content lives in one `$faq_groups` array; the accordion markup and the JSON-LD are both generated from it, so they can never drift apart. Answers marked `[client-approve]` in the table below are also listed in the Handoff section.

| # | Question | Sourced from |
|---|----------|--------------|
| 1 | How do I book a shoot? | Booking flow (item 2, `slm_book_url()`) |
| 2 | How far in advance should I book? | Conservative phrasing — **[client-approve]** |
| 3 | What areas do you serve? | `templates/page-service-area.php:18-36` (five counties) |
| 4 | What happens if it rains? | Conservative phrasing — **[client-approve]** |
| 5 | Does the seller need to be home? | Conservative phrasing — **[client-approve]** |
| 6 | How fast will I get my media? | Canonical "24–48 hours" (item 10) |
| 7 | How many photos will I receive? | Generic until client supplies counts — **[client-approve]** |
| 8 | How is my media delivered? | Client portal exists (`templates/page-portal.php`) |
| 9 | Do you work with businesses or just agents? | `/for-businesses/` page |
| 10 | Are your drone pilots certified? | "FAA-certified" — `templates/page-for-businesses.php:101` |
| 11 | Do you offer memberships? | `/memberships/` page |
| 12 | What is your cancellation policy? | Conservative phrasing — **[client-approve]** |

```php
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
```

Note: `wp_json_encode()` output is emitted unescaped inside the script tag on purpose — that is the correct pattern for JSON-LD (matching `inc/seo.php:279`); `esc_html()` would corrupt the JSON. The array values are theme-authored constants, not user input.

- [ ] **Step 5: Add the auto-create hook to `functions.php`**

Insert directly after the service-area hook block that ends `}, 10);` (~line 905), following the `for-businesses` pattern exactly:

```php
add_action('init', function () {
  if (wp_installing()) {
    return;
  }

  if (get_transient('slm_faq_page_exists')) {
    return;
  }

  $faq = get_page_by_path('faq') ?: get_page_by_title('FAQ');
  if (!$faq) {
    $faq_id = wp_insert_post([
      'post_title' => 'FAQ',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => 'faq',
    ]);
    if ($faq_id && !is_wp_error($faq_id)) {
      update_post_meta((int) $faq_id, '_wp_page_template', 'templates/page-faq.php');
      update_post_meta((int) $faq_id, 'slm_meta_title', 'FAQ — Booking, Turnaround & Delivery | Jacksonville, FL');
      update_post_meta((int) $faq_id, 'slm_meta_description', 'Answers to common questions about booking, 24–48 hour delivery, weather policy, and working with Showcase Listings Media in Jacksonville & North Florida.');
    }
  } else {
    update_post_meta((int) $faq->ID, '_wp_page_template', 'templates/page-faq.php');
    update_post_meta((int) $faq->ID, 'slm_meta_title', 'FAQ — Booking, Turnaround & Delivery | Jacksonville, FL');
    update_post_meta((int) $faq->ID, 'slm_meta_description', 'Answers to common questions about booking, 24–48 hour delivery, weather policy, and working with Showcase Listings Media in Jacksonville & North Florida.');
  }

  set_transient('slm_faq_page_exists', '1', DAY_IN_SECONDS);
}, 11);
```

(`get_page_by_title()` is deprecated in core but is the established pattern in this file — keep consistency; do not refactor the neighbors.)

- [ ] **Step 6: Link the FAQ from the footer and the nav fallback**

In `template-parts/site/footer.php`, change the hard-coded Important Links list (lines 135-138) to:

```php
    <ul class="footer__menu">
      <li><a href="<?php echo esc_url(slm_faq_url()); ?>">FAQ</a></li>
      <li><a href="<?php echo esc_url($privacy_url); ?>">Privacy Policy</a></li>
      <li><a href="<?php echo esc_url($terms_url); ?>">Terms of Service</a></li>
    </ul>
```

In `functions.php` `slm_primary_nav_fallback()`, add before the Contact line:

```php
  echo '<li><a href="' . esc_url(slm_faq_url()) . '">FAQ</a></li>';
```

(Prod renders an assigned WP menu, so prod's nav additionally needs the WP Admin menu edit listed in the Handoff. The fallback covers staging and any environment without an assigned menu.)

- [ ] **Step 7: Add FAQ styles to `assets/css/pages.css`**

Append at the end of the file:

```css
/* ============================================================
   FAQ page + service FAQ blocks (review items 18 & 20)
   ============================================================ */
.faq-section {
  padding: 64px 0;
}

.faq-group {
  max-width: 820px;
  margin: 0 auto 40px;
}

.faq-group h2 {
  margin-bottom: 16px;
}

.faq-list {
  display: grid;
  gap: 12px;
}

.faq-item {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
}

.faq-item summary {
  cursor: pointer;
  padding: 16px 20px;
  font-weight: 600;
  color: var(--foreground);
  list-style: none;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.faq-item summary::-webkit-details-marker {
  display: none;
}

.faq-item summary::after {
  content: "+";
  font-size: 1.3em;
  line-height: 1;
  color: var(--accent);
  flex: none;
}

.faq-item[open] summary::after {
  content: "\2013";
}

.faq-item summary:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.faq-item p {
  margin: 0;
  padding: 0 20px 16px;
  color: var(--muted-foreground);
}

.faq-cta {
  max-width: 820px;
  margin: 0 auto;
  text-align: center;
  padding-top: 24px;
}

.faq-cta__actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 16px;
}

@media (max-width: 980px) {
  .faq-section {
    padding: 40px 0;
  }
}
```

- [ ] **Step 8: Syntax-check and run the tests**

```bash
php -l templates/page-faq.php
php -l functions.php
php -l template-parts/site/footer.php
php run-tests.php
```
Expected: all lints clean, all tests PASS (new six + every pre-existing test).

- [ ] **Step 9: Commit**

```bash
git add tests/test-faq-page.php templates/page-faq.php functions.php template-parts/site/footer.php assets/css/pages.css
git commit -m "feat: FAQ page with FAQPage schema, auto-creation, and footer/nav links (review item 18)"
```

---

### Task 2: Local visual check of the FAQ page

The theme cannot run locally (no local WP install), so the render check happens on staging after deploy — but the markup can be sanity-checked now.

- [ ] **Step 1: Grep the rendered-markup invariants**

```bash
grep -c "<details" templates/page-faq.php   # expect 1 (the loop template)
grep -c "esc_html" templates/page-faq.php   # expect >= 3 (question, answer, group title)
git diff --check
```
Expected: no whitespace errors; escaping present on every echoed content value.

- [ ] **Step 2: Confirm no other template claims the `/faq/` slug**

```bash
grep -rn "post_name' => 'faq'" functions.php   # exactly 1 hit (the new hook)
grep -rn "'/faq/'" --include=*.php .           # only slm_faq_url() and the new hook/test
```
Expected: no collisions. (The live check on 2026-08-10 confirmed `/faq/` currently 404s, so no page with that slug exists in prod's DB.)

No commit — verification only.

---

### Task 3: Per-service FAQ blocks (review item 20, FAQ half)

The detail half of item 20 (photo counts, shoot durations, package inclusions, pricing visibility) stays **blocked on client facts** — do not invent numbers. This task ships the FAQ half using only facts already published in the theme.

**Files:**
- Create: `tests/test-service-faqs.php`
- Modify: `template-parts/blocks/service-detail.php` (insert new section between the `why_choose` section ending ~line 178 and the final-CTA section starting ~line 180)
- Modify: all eight `templates/page-service-*.php` (add `'faqs'` to the existing `get_template_part()` args array)

**Interfaces:**
- Consumes: `.faq-item` / `.faq-list` CSS from Task 1; existing `service-detail` args contract (`template-parts/blocks/service-detail.php:5-19`).
- Produces: new optional block arg `'faqs' => array` of `['q' => string, 'a' => string]`. Absent or empty → section not rendered (all non-service callers unaffected).

- [ ] **Step 1: Write the failing tests**

Create `tests/test-service-faqs.php`:

```php
<?php
/**
 * Review item 20 (FAQ half) — every service detail page carries a per-service
 * FAQ block, and the shared block emits FAQPage JSON-LD from the same data.
 */

require_once __DIR__ . '/helpers.php';

function test_service_detail_block_supports_faqs()
{
    $block = slm_test_read('template-parts/blocks/service-detail.php');
    assert(
        strpos($block, "'faqs' => []") !== false,
        'service-detail.php must declare a faqs arg defaulting to []'
    );
    assert(
        strpos($block, "'FAQPage'") !== false && strpos($block, 'wp_json_encode') !== false,
        'service-detail.php must emit FAQPage JSON-LD built with wp_json_encode'
    );
    assert(
        strpos($block, '<details class="faq-item">') !== false,
        'The FAQ section should reuse the .faq-item accordion pattern from Task 1'
    );
    echo "PASS: test_service_detail_block_supports_faqs\n";
}

function test_every_service_template_passes_faqs()
{
    $missing = [];
    foreach (glob(slm_test_theme_dir() . '/templates/page-service-*.php') as $template) {
        if (strpos((string) file_get_contents($template), "'faqs' =>") === false) {
            $missing[] = basename($template);
        }
    }
    assert(
        $missing === [],
        'Service templates without a faqs array: ' . implode(', ', $missing)
    );
    echo "PASS: test_every_service_template_passes_faqs\n";
}

function test_service_faqs_use_canonical_turnaround()
{
    $offenders = [];
    foreach (glob(slm_test_theme_dir() . '/templates/page-service-*.php') as $template) {
        $body = (string) file_get_contents($template);
        if (strpos($body, '24-48') !== false || strpos($body, '24 hour') !== false) {
            $offenders[] = basename($template);
        }
    }
    assert(
        $offenders === [],
        'Service templates with non-canonical turnaround copy (must be "24–48 hours"): ' . implode(', ', $offenders)
    );
    echo "PASS: test_service_faqs_use_canonical_turnaround\n";
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php run-tests.php`
Expected: the three new `test_service_*` tests FAIL; everything else PASSES.

- [ ] **Step 3: Extend `template-parts/blocks/service-detail.php`**

Add to the `wp_parse_args` defaults (after `'why_choose' => [],` on line 13):

```php
  'faqs' => [],          // array of ['q' => '', 'a' => ''] — renders FAQ + FAQPage JSON-LD
```

Insert this section between the `why_choose` section's closing `<?php endif; ?>` (~line 178) and the final-CTA `<section class="service-section">` (~line 180):

```php
  <?php
  $faq_items = array_values(array_filter((array) $args['faqs'], static function ($item): bool {
    return is_array($item) && trim((string) ($item['q'] ?? '')) !== '' && trim((string) ($item['a'] ?? '')) !== '';
  }));
  ?>
  <?php if (!empty($faq_items)): ?>
    <section class="service-section">
      <div class="container">
        <h2>Frequently Asked Questions</h2>
        <div class="faq-list">
          <?php foreach ($faq_items as $faq): ?>
            <details class="faq-item">
              <summary><?php echo esc_html((string) $faq['q']); ?></summary>
              <p><?php echo esc_html((string) $faq['a']); ?></p>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
      <?php
      $service_faq_schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(static function (array $faq): array {
          return [
            '@type' => 'Question',
            'name' => (string) $faq['q'],
            'acceptedAnswer' => [
              '@type' => 'Answer',
              'text' => (string) $faq['a'],
            ],
          ];
        }, $faq_items),
      ];
      ?>
      <script type="application/ld+json"><?php echo wp_json_encode($service_faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    </section>
  <?php endif; ?>
```

- [ ] **Step 4: Add the `faqs` array to each of the eight service templates**

In each template, append a `'faqs'` key to the existing `get_template_part('template-parts/blocks/service-detail', null, [...])` array (after `'why_choose'`). Exact copy per template — every answer is sourced from existing theme copy, the canonical turnaround, or the FAA claim at `templates/page-for-businesses.php:101`:

`page-service-re-photography.php`:
```php
  'faqs' => [
    ['q' => 'How fast will I get my listing photos?', 'a' => 'Edited, MLS-ready photos are delivered within 24–48 hours of the shoot.'],
    ['q' => 'What areas do you photograph?', 'a' => 'We shoot across five North Florida counties: Duval, St. Johns, Clay, Nassau, and Putnam.'],
    ['q' => 'Can I add more services to a photo shoot?', 'a' => 'Yes — add drone, twilight, video, or a floor plan to the same order when you book and we will capture everything in one visit.'],
  ],
```

`page-service-re-videography.php`:
```php
  'faqs' => [
    ['q' => 'How long until my video is delivered?', 'a' => 'Edited video is delivered within 24–48 hours of the shoot.'],
    ['q' => 'Where can I use my listing video?', 'a' => 'Anywhere you market the listing — your MLS where video is permitted, YouTube, Instagram, Facebook, and your own website.'],
    ['q' => 'Can I book video and photos together?', 'a' => 'Yes — add videography to your photo order when booking and we will capture both in a single visit.'],
  ],
```

`page-service-drone-photography.php`:
```php
  'faqs' => [
    ['q' => 'Are your drone pilots certified?', 'a' => 'Yes — our aerial photo and video work is FAA-certified.'],
    ['q' => 'How fast will I get my aerial photos?', 'a' => 'Edited aerial photos are delivered within 24–48 hours of the shoot.'],
    ['q' => 'When does drone photography make the biggest difference?', 'a' => 'Large lots, waterfront and preserve views, acreage, and any listing where the location and surroundings are part of the story.'],
  ],
```

`page-service-drone-videography.php`:
```php
  'faqs' => [
    ['q' => 'Are your drone pilots certified?', 'a' => 'Yes — our aerial photo and video work is FAA-certified.'],
    ['q' => 'How long until my aerial video is delivered?', 'a' => 'Edited aerial video is delivered within 24–48 hours of the shoot.'],
    ['q' => 'Can drone video be combined with ground video?', 'a' => 'Yes — add both to one order and we will blend aerial and interior footage into a single cinematic piece.'],
  ],
```

`page-service-twilight-photography.php`:
```php
  'faqs' => [
    ['q' => 'Do you shoot real twilight or edit it?', 'a' => 'Both are available: in-person twilight shoots captured at dusk, and dusk conversions edited from daytime photos — choose whichever fits your timeline and budget.'],
    ['q' => 'How fast will I get my twilight photos?', 'a' => 'Edited twilight photos are delivered within 24–48 hours of the shoot.'],
    ['q' => 'Why add twilight photography to a listing?', 'a' => 'Warm dusk lighting makes a listing stand out in search results and thumbnails, where most buyers form their first impression.'],
  ],
```

`page-service-virtual-tours.php`:
```php
  'faqs' => [
    ['q' => 'What is a virtual tour?', 'a' => 'An interactive 3D walkthrough that lets buyers explore the property room by room from any device, any time.'],
    ['q' => 'Can I share or embed the tour?', 'a' => 'Yes — share the tour link directly or embed it in your MLS listing, website, and social posts.'],
    ['q' => 'What areas do you cover?', 'a' => 'We serve five North Florida counties: Duval, St. Johns, Clay, Nassau, and Putnam.'],
  ],
```

`page-service-floor-plans.php`:
```php
  'faqs' => [
    ['q' => 'Why add a floor plan to my listing?', 'a' => 'Floor plans help buyers understand layout and flow before they ever visit, which keeps them engaged with the listing longer.'],
    ['q' => 'Can I order a floor plan with my photo shoot?', 'a' => 'Yes — add it to your order when booking and we will capture everything in one visit.'],
    ['q' => 'What areas do you cover?', 'a' => 'We serve five North Florida counties: Duval, St. Johns, Clay, Nassau, and Putnam.'],
  ],
```

`page-service-zillow-showcase.php`:
```php
  'faqs' => [
    ['q' => 'What is Zillow Showcase?', 'a' => 'Zillow\'s premium listing presentation — immersive media and elevated placement that helps your listing stand out on the platform buyers use most.'],
    ['q' => 'How do I add Zillow Showcase to my order?', 'a' => 'Book it as its own service or add the Zillow Add-On to any listing shoot when you order.'],
    ['q' => 'What areas do you cover?', 'a' => 'We serve five North Florida counties: Duval, St. Johns, Clay, Nassau, and Putnam.'],
  ],
```

- [ ] **Step 5: Syntax-check and run the tests**

```bash
php -l template-parts/blocks/service-detail.php
for f in templates/page-service-*.php; do php -l "$f"; done
php run-tests.php
```
Expected: all lints clean, all tests PASS.

- [ ] **Step 6: Commit**

```bash
git add tests/test-service-faqs.php template-parts/blocks/service-detail.php templates/page-service-*.php
git commit -m "feat: per-service FAQ blocks with FAQPage schema (review item 20, FAQ half)"
```

---

### Task 4: Mentorship page auto-creation + SEO (closes review item 7's last box)

`templates/page-social-mentorship-program.php` exists but no `init` hook creates its page or sets its SEO meta — the last uncovered template from item 7's checklist. `page-for-businesses.php:31` already links to it via `slm_page_url_by_template()`, which currently falls back to a dead path on any environment where the page was never hand-created.

**Files:**
- Create: `tests/test-mentorship-page.php`
- Modify: `functions.php` (new init hook at priority 12, directly after Task 1's FAQ hook)

**Interfaces:**
- Consumes: the auto-create hook pattern (`functions.php:818-847`), `inc/seo.php` post-meta system.
- Produces: an auto-created published page at `/social-mentorship-program/` with template + SEO meta.

- [ ] **Step 1: Write the failing test**

Create `tests/test-mentorship-page.php`:

```php
<?php
/**
 * Review item 7 (last unchecked box) — the Mentorship page is auto-created
 * with SEO meta like every other theme-owned page.
 */

require_once __DIR__ . '/helpers.php';

function test_mentorship_page_is_auto_created_with_seo_meta()
{
    $functions = slm_test_read('functions.php');
    assert(
        strpos($functions, "get_transient('slm_mentorship_page_exists')") !== false,
        'functions.php should auto-create the Mentorship page behind the slm_mentorship_page_exists transient'
    );
    assert(
        strpos($functions, "'post_name' => 'social-mentorship-program'") !== false,
        'The auto-created page slug must match the slm_page_url_by_template fallback (/social-mentorship-program/)'
    );
    $hook = substr($functions, strpos($functions, "get_transient('slm_mentorship_page_exists')"));
    $hook = substr($hook, 0, strpos($hook, 'set_transient'));
    assert(
        substr_count($hook, "'slm_meta_title'") === 2 && substr_count($hook, "'slm_meta_description'") === 2,
        'Both branches must set slm_meta_title and slm_meta_description'
    );
    echo "PASS: test_mentorship_page_is_auto_created_with_seo_meta\n";
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php run-tests.php`
Expected: the new test FAILS; everything else PASSES.

- [ ] **Step 3: Add the hook to `functions.php`**

Insert directly after Task 1's FAQ hook (`}, 11);`):

```php
add_action('init', function () {
  if (wp_installing()) {
    return;
  }

  if (get_transient('slm_mentorship_page_exists')) {
    return;
  }

  $mentorship = get_page_by_path('social-mentorship-program') ?: get_page_by_title('Social Mentorship Program');
  if (!$mentorship) {
    $mentorship_id = wp_insert_post([
      'post_title' => 'Social Mentorship Program',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => 'social-mentorship-program',
    ]);
    if ($mentorship_id && !is_wp_error($mentorship_id)) {
      update_post_meta((int) $mentorship_id, '_wp_page_template', 'templates/page-social-mentorship-program.php');
      update_post_meta((int) $mentorship_id, 'slm_meta_title', 'Social Media Mentorship for Agents & Businesses | Jacksonville, FL');
      update_post_meta((int) $mentorship_id, 'slm_meta_description', 'A hands-on mentorship that teaches North Florida agents and business owners to plan, capture, and post their own content — strategy, systems, and on-camera confidence.');
    }
  } else {
    update_post_meta((int) $mentorship->ID, '_wp_page_template', 'templates/page-social-mentorship-program.php');
    update_post_meta((int) $mentorship->ID, 'slm_meta_title', 'Social Media Mentorship for Agents & Businesses | Jacksonville, FL');
    update_post_meta((int) $mentorship->ID, 'slm_meta_description', 'A hands-on mentorship that teaches North Florida agents and business owners to plan, capture, and post their own content — strategy, systems, and on-camera confidence.');
  }

  set_transient('slm_mentorship_page_exists', '1', DAY_IN_SECONDS);
}, 12);
```

- [ ] **Step 4: Syntax-check and run the tests**

```bash
php -l functions.php
php run-tests.php
```
Expected: clean lint, all tests PASS.

- [ ] **Step 5: Commit**

```bash
git add tests/test-mentorship-page.php functions.php
git commit -m "feat: auto-create the Mentorship page with SEO meta (review item 7)"
```

---

### Task 5: Sync the review document to reality

**Files:**
- Modify: `memory-bank/website-review-2026-08-06.md`

- [ ] **Step 1: Update statuses and add dated verification notes**

Make exactly these edits:

1. Item 1 heading stays `status: open`; append after the compression note:
   `**Update 2026-08-10:** live check shows the gallery is now populated (many photos render under the grid). Remaining ADMIN work: confirm each of the five filter categories holds 8–12 items, and get the client's answer on the Drone folder double-use before mapping Business assets.`
2. Item 3: append `**Update 2026-08-10:** logged-in live check shows one unified menu; group headers render as labels. Re-verify logged-out after the cache purge (item 4), then close.`
3. Item 5: append the same dated note form: `**Update 2026-08-10:** headers render non-clickable on the live dropdown (hard-coded fallback shipped in a75f1ef, or the menu was fixed). Re-verify logged-out after the purge, then close.`
4. Item 13 heading: change `status: open` → `status: done`; append `**Update 2026-08-10:** verified live — the card links via slm_for_businesses_url() (templates/page-services.php:325) and /for-businesses/ is published.`
5. Item 7 checklist: tick `page-portfolio.php`, `Mentorship page`, `For Businesses page`, `Social Media Management page` (all four now covered — the last three via `slm_meta_title`/`slm_meta_description` post meta set by auto-create hooks; note this inline).
6. Item 18 heading: change `status: open` → `status: done`; append `**Update 2026-08-10:** templates/page-faq.php ships the page with FAQPage JSON-LD; the page auto-creates at /faq/. Remaining ADMIN: add FAQ to the assigned primary menu in Appearance → Menus (prod does not render the code fallback), and have the client approve the flagged policy answers (rain, cancellation, seller-home, photo counts, advance booking).`
7. Item 20 heading: change `status: open` → `status: in-progress`; append `**Update 2026-08-10:** per-service FAQ blocks shipped via template-parts/blocks/service-detail.php. Per-service details (photo counts, durations, inclusions) remain blocked on client facts and the pricing-visibility decision — do not invent numbers.`

- [ ] **Step 2: Commit**

```bash
git add memory-bank/website-review-2026-08-06.md
git commit -m "docs: sync review statuses with 2026-08-10 live verification and round-2 work"
```

---

### Task 6: Full verification pass (pre-handoff checklist)

- [ ] **Step 1: Run the complete gate**

```bash
php run-tests.php
git ls-files '*.php' | xargs -n1 php -l
git diff --check
git log --oneline origin/staging..HEAD
```
Expected: every test PASSES, every file lints clean, no whitespace errors, and the log shows exactly the four commits from Tasks 1, 3, 4, 5.

- [ ] **Step 2: Push the staging branch**

```bash
git push origin staging
```

(Nothing deploys from a push — Bluehost only updates when the user clicks Deploy; see Handoff.)

---

## Handoff: actions outside VS Code / Claude

Everything below is manual — Bluehost cPanel, WP Admin, or client communication. In order:

**A. Deploy to staging and verify (Bluehost cPanel)**
1. cPanel → Files → **Git Version Control** → Manage the **staging** clone (`staging/5169` path) → *Update from Remote* → *Deploy HEAD Commit*.
2. Browse `https://showcaselistingsmedia.com/staging/5169/` (staging shares prod's database under a different table prefix, so the FAQ and Mentorship pages auto-create in staging's tables on first page load):
   - `/faq/` renders: hero, three accordion groups, working `<details>` toggles, Book a Shoot + Contact buttons, FAQ link in footer and nav.
   - Any service page (e.g. Real Estate Photography): new "Frequently Asked Questions" section above the final CTA.
   - View source on both: one `<script type="application/ld+json">` containing `"FAQPage"`.
   - Mobile width (≤980px): accordions and CTA buttons wrap cleanly.
3. Compare against the 2026-08-10 baseline: home, services, for-businesses, portfolio should be **visually unchanged**.

**B. Client approvals (before prod)**
4. Send the client the flagged FAQ answers for sign-off or corrected wording: rain policy, cancellation policy, seller-need-to-be-home, photo counts, advance-booking guidance.
5. Ask for the item-20 detail facts when ready: per-service photo counts, typical shoot durations, package inclusions — plus the pending pricing-visibility decision. (Separate future round.)
6. Confirm the Drone Drive folder double-use (Drone + Business categories) from item 1.

**C. Deploy to production (git + Bluehost cPanel)**
7. Merge and push: `git checkout main && git merge staging && git push origin main` (or open a PR if preferred).
8. cPanel → Git Version Control → Manage the **production** clone → *Update from Remote* → *Deploy HEAD Commit*.

**D. Cache purge + re-audit (review item 4 — INFRA)**
9. In WP Admin's toolbar click **Caching** → purge everything (Bluehost's page cache); purge Cloudflare/CDN too if enabled.
10. In a logged-out/incognito window re-check items **3** (same menu on Home vs Services vs About), **11** (footer wording identical everywhere), **12** (footer social icons, not raw URLs). If all clean, mark items 3, 5, 12 closed in the review doc.

**E. WP Admin content work (prod)**
11. **Appearance → Menus:** add **FAQ** (the new page) to the assigned primary menu — suggested spot: the "More" dropdown. The code fallback only covers environments with no assigned menu; prod uses an assigned menu.
12. **Portfolio (item 1):** open each filter category and confirm 8–12 items each — All, Real Estate Photography, Cinematic Video, Drone, Social Media / Reels, Business Branding (exact labels). Upload any gaps from the client's Drive folders (compress → WebP/AVIF first).
13. **Testimonials (item 9):** after written permission, enter the email testimonials in the Testimonials CPT with `source` set to `"Email"` (the field defaults to `"Google"` — change it per entry).
14. Optional QA: open the FAQ page in the editor and confirm the SEO Settings meta box shows the title/description the hook set.

## Not in scope (deliberately)

- Item 20's detail/pricing content — blocked on client facts and the pricing-visibility decision.
- Items 1, 3, 5, 9 — ADMIN lane, cannot be fixed from this repo (handoff steps above).
- Item 4 — INFRA lane (purge steps above).
- Restructuring the two SEO systems (`slm_page_seo()` inline vs `inc/seo.php` post meta) — both are shipped and working; consolidation is a refactor for another day.
