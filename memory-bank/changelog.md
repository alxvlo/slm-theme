# Changelog

Theme changes tracked against the client review backlog
(`website-review-2026-08-06.md`). Item numbers below refer to that document.
Newest first. "Deployed" reflects where the work has actually shipped — code
merged to `staging`/`main` does nothing on Bluehost until a cPanel deploy runs.

## 2026-08-10 — Round 2: FAQ, per-service FAQs, SEO stragglers, regression repairs

**Deployed:** staging (verified live on the staging URL). Production pending
the `staging` → `main` merge and cPanel deploy.

### Added

- **FAQ page** (item 18) — `templates/page-faq.php` with 12 answers in three
  groups, native `<details>` accordions, and `FAQPage` JSON-LD generated from
  the same array as the visible markup. Page auto-creates at `/faq/` with SEO
  meta; linked from the footer and the hard-coded nav fallback. Five policy
  answers (rain, cancellation, seller-at-home, photo counts, advance booking)
  use conservative wording pending client sign-off. `4404c7b`
- **Per-service FAQ blocks** (item 20, FAQ half) — `service-detail` block
  gained an optional `faqs` arg rendering an FAQ section + `FAQPage` schema;
  all eight `page-service-*.php` templates carry three factual Q&As each.
  `6a57768`
- **Mentorship page auto-creation + SEO meta** (item 7's last gap) —
  priority-12 init hook. `f168d85`
- **Service-area SEO meta** (item 7) — title/description added to its
  auto-create hook. `fb3856f`
- `slm_consult_or_order_url()` — moves the For Businesses page's
  login-state CTA branch out of the template, per item 2's rule. `fb3856f`
- `slm_page_by_template()` — page lookup by assigned template. `6be011c`
- Tests: `test-faq-page.php`, `test-service-faqs.php`,
  `test-mentorship-page.php`; suite now 30 tests, all green.

### Fixed

- **Item 10 regression** — `49831d6` (hero markup restore) had silently
  re-introduced "24-hour standard delivery" in `hero-slider.php`. Restored to
  the canonical "24–48 hour delivery". `fb3856f`
- **Item 6 regression** — same restore re-introduced hardcoded
  `home_url('/portfolio/')` in `hero-slider.php` and `solution.php`. Restored
  to `slm_page_url_by_template()` resolution. `fb3856f`
- **Mentorship page duplicated on staging** — the new auto-create hook
  matched only the `social-mentorship-program` slug/title and missed the
  pre-existing June "Mentorship Program" page (slug `mentorship-program`,
  same template), creating a duplicate. Hook now falls back to lookup by
  template assignment. `6be011c`. Data side: duplicate (post 312) moved to
  Trash on staging via WP-CLI; SEO meta set on the original (post 233), which
  keeps its URL and menu link. **Staging must be redeployed with `6be011c`
  before ~2026-08-11 08:40** or the still-deployed old hook recreates the
  duplicate when its transient guard expires.
- Stale test expectations — `test_every_public_page_template_registers_seo`
  now accepts the post-meta SEO pattern (scoped per hook body);
  `page-maintenance.php` exempted. `fb3856f`, `6b489f0`

### Docs

- Implementation plan: `docs/superpowers/plans/2026-08-10-website-review-round-2.md`
  (includes the manual Handoff checklist: cPanel deploys, cache purge, WP
  Admin menu/portfolio/testimonials work, client approvals). `53d40c6`
- Review doc statuses synced to verified reality. `9613f18`

## 2026-08-09 → 2026-08-10 — Deploy pipeline and staging environment

- cPanel Git deploys routed by clone directory so staging and prod share one
  `.cpanel.yml` (`f2ded9b`, `9cd8856`, `29c9fb8`); deploy forces 755/644 so
  Apache can serve assets (`f8e72c8`); backups named per environment.
- Hard-coded nav fallback when no menu is assigned (`a75f1ef`) — staging
  clones without a menu no longer render an empty nav.
- Hero/solution markup restored to match shipped CSS (`49831d6`) — this is
  the commit that regressed items 6/10, repaired above.
- `DEPLOY.md` documents both deploy paths, backups/rollback, and that staging
  shares prod's database separated only by table prefix.

## 2026-08-08 — Round 1: THEME fixes from the client review

All from `memory-bank/website-review-2026-08-06.md`; reconciled into
`8617d81` and covered by tests in `tests/`:

- Item 2 — single canonical booking CTA (`slm_book_url()`) sitewide.
- Item 6 — `/our-portfolio/` canonical, helper-resolved.
- Item 7 — `slm_page_seo()` helper + rollout across service templates;
  later pages moved to the `slm_meta_title`/`slm_meta_description`
  post-meta pattern read by `inc/seo.php`.
- Item 10 — turnaround unified to "24–48 hours".
- Item 11 — footer description covers agents AND local businesses.
- Item 14 — the 13 add-ons link to the canonical booking URL.
- Item 15 — "Login" relabeled "Client Login".
- Item 16 — homepage down to one `<h1>` (hero owns it).
- Item 17 — Contact page LocalBusiness JSON-LD with phone + area served.
- Item 19 — logo mark kept deliberately decorative (documented in nav.php).

## 2026-08-06 — Client review received

Stakeholder review of the live site logged as
`memory-bank/website-review-2026-08-06.md`; items tagged THEME / ADMIN /
INFRA. Headline findings: near-empty portfolio, two booking destinations,
two navigation menus.
