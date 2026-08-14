# Showcase Listings Media — Custom WordPress Theme & Client Platform

> ⚠️ **This is live production software.** The theme runs a real business's public
> website and the portal its paying clients log into. Nothing here is a demo.
> Before changing anything, read [`AGENTS.md`](AGENTS.md) (engineering rules) and
> [`DEPLOY.md`](DEPLOY.md) (two-environment deploy, backups, rollback).

A bespoke WordPress theme — no page builder, no build step, no framework — that
serves as both the marketing site and the operational platform for **Showcase
Listings Media**, a real estate media company (photography, video, drone, virtual
tours, floor plans) selling to real estate agents.

**Live:** <https://showcaselistingsmedia.com>

---

## What it actually is

Most "custom WordPress theme" projects are a stylesheet over stock WordPress.
This one carries the business logic too:

| Layer | What it does |
| --- | --- |
| **Marketing site** | ~28 hand-built page templates — homepage, 9 individual service pages, packages, memberships, portfolio, FAQ, blog, legal |
| **Client portal** | Authenticated dashboard: order history, delivery downloads, subscription state, profile, credit balance |
| **Admin portal** | Front-end ops console — orders, customers, revenue stats, delivery uploads, notification controls — outside `/wp-admin` |
| **Aryeo integration** | API client + webhook handler for booking, scheduling, order status and deliverables (`inc/aryeo.php`, ~1,150 lines) |
| **Square subscriptions** | Recurring membership billing, tier/term switching, provider-specific states (`inc/subscriptions.php`, ~1,180 lines) |
| **Member credits** | Credit ledger tied to membership tier, spendable against orders |
| **Editorial control** | Admin-editable page copy, portfolio CPT with gallery + video, testimonials CPT, service FAQs — all via native WP meta, no ACF Pro dependency |

Roughly **34k lines** of PHP, CSS and vanilla JS across **159 tracked files**.

## Engineering decisions worth pointing at

**No build pipeline, deliberately.** Shared Bluehost hosting, no Node runtime, a
solo maintainer. Sass and Webpack would have added a failure mode that buys
nothing here. CSS is layered by hand (`base` → `components` → `nav` → `pages` →
per-surface) with custom properties as the design system; cache busting comes
from `slm_asset_ver()` off file mtime.

**Function-oriented PHP, not a class hierarchy.** WordPress is a hook system;
the theme leans into that instead of fighting it. Every public symbol is `slm_`
prefixed. Modules live in `inc/` and are wired with plain `require_once` from
`functions.php`. No Composer, no autoloader, no namespaces — nothing to break on
a host you don't control.

**Integration contracts are treated as frozen.** REST routes, meta keys, option
names and webhook fields are load-bearing across Aryeo and Square. Renaming one
is a repo-wide audit, not a refactor.

**Security is the boundary, not a sprinkle.** Nonces on every state-changing form
and AJAX action, capability checks before privileged work, `wp_unslash()` →
narrowest sanitizer on input, matching `esc_*()` at render, `$wpdb->prepare()`
for dynamic SQL, `dbDelta()` for schema. Auth-sensitive pages are excluded from
caching so a logged-in portal never leaks into a guest's page.

**Progressive enhancement.** Every interaction — portfolio carousel, lightbox,
nav, hero animation — degrades to something usable with JS off. Keyboard
navigation, `:focus-visible`, `aria-*` and reduced-motion are baseline, not
backlog.

**Tests exist, in a codebase that "can't have tests."** WordPress themes are
notoriously untestable without a full WP bootstrap. `run-tests.php` is a ~90-line
runner that discovers `tests/test-*.php`, stubs the WP functions each test needs,
and executes every `test_*` function **in its own PHP process** — because theme
helpers memoize into statics that can't be reset in-process. It guards the things
that actually regressed in production: content consistency across duplicated
copy, canonical URLs, SEO metadata registration, portfolio media rendering.

```bash
php run-tests.php
```

**A content-consistency test suite, born from a real audit.** A 2026 site review
found the same marketing copy and URLs duplicated across templates and drifting
apart — turnaround times that disagreed, three different "Book a Shoot" URL
helpers, a page whose missing `<meta name="description">` put a raw slug
(`service-re-photography`) into Google's search results. The fix wasn't a
one-time cleanup; it was canonicalising the values, routing every CTA through a
single helper, and writing tests that fail if a new template reintroduces the
drift.

## Stack

PHP · WordPress templates & hooks · vanilla CSS (custom properties, BEM-ish) ·
vanilla JS (IIFEs, `data-*` hooks) · jQuery + `wp.media` only where WP admin
requires it · MySQL · Aryeo API · Square · Bluehost shared hosting

## Repo map

```
functions.php           bootstrap, includes, enqueueing, AJAX, shared helpers
inc/*.php               business logic — aryeo, subscriptions, credits, seo, CPTs
templates/*.php         full page templates, assigned in WP Admin
template-parts/         shared UI fragments (blocks / home / site)
assets/css/             base, components, nav, pages, pages-{public,portal,admin}
assets/js/              main.js, hero-animations.js, admin-*.js
tests/                  process-isolated assertion tests
run-tests.php           test runner
scripts/deploy.sh       SSH rsync deploy (never deletes remote files)
.cpanel.yml             cPanel Git deploy — backs up, prunes, rsyncs
memory-bank/            project brief, context, patterns, changelog, review notes
AGENTS.md               engineering rules for anyone (or anything) editing this
DEPLOY.md               environments, deploy paths, backup & rollback procedure
```

## Deploying

`main` → production, `staging` → staging, both on Bluehost. Deploys run through
cPanel Git Version Control (`.cpanel.yml` takes a timestamped tarball backup,
keeps the newest 5, then rsyncs) or `./scripts/deploy.sh`. **Neither method ever
deletes remote files**, so server-side uploads and the media library survive a
deploy. Pushing to GitHub alone deploys nothing — the deploy is an explicit
action. Full procedure and rollback commands in [`DEPLOY.md`](DEPLOY.md).

## Working on this

1. Read [`AGENTS.md`](AGENTS.md) — style, security, escaping and editing rules.
2. Make surgical diffs, especially in `inc/aryeo.php` and `inc/subscriptions.php`.
3. Syntax-check what you touched: `php -l <file>` / `node --check <file>`.
4. Run `php run-tests.php`.
5. Manually walk the flow you changed — guest, client, admin, membership or
   portfolio, whichever applies.
6. Ship to `staging` first. Verify. Then `main`.
