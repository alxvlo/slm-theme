# SLM Theme Agent Guide

## Project Snapshot
- Custom WordPress theme named `SLM Theme`.
- Stack: PHP, WordPress templates/hooks, vanilla CSS, vanilla browser JS, plus small WP admin jQuery scripts.
- No theme build pipeline; assets are served directly from `assets/css` and `assets/js`.
- Core integrations live in the theme: Aryeo, memberships, credits, client portal, admin portal.
- Highest-risk files: `inc/aryeo.php` and `inc/subscriptions.php`; edit them surgically.

## Repo Map
- `functions.php` - bootstrap, includes, enqueueing, AJAX, shared helpers.
- `inc/*.php` - business logic modules.
- `templates/*.php` - full page templates assigned in WP Admin.
- `template-parts/*` - shared UI fragments.
- `assets/css/base.css`, `components.css`, `nav.css`, `pages.css` - CSS layers.
- `assets/js/main.js` - public interactions.
- `assets/js/admin-*.js` - WP admin interactions, often with jQuery and `wp.media`.

## Rules Sources
- No `.cursor/rules/`, `.cursorrules`, or `.github/copilot-instructions.md` were found.
- If any of those files are added later, merge their repo-specific guidance into this document.

## Build / Run
- No root `package.json`, `composer.json`, `phpunit.xml`, or project-local lint config was found.
- There is no asset build step; WordPress loads the files directly.
- Theme asset cache busting uses `slm_asset_ver()`.

```bash
# Optional if WP-CLI exists
wp theme activate slm-theme
```

## Lint / Syntax
- Prefer targeted syntax checks; there is no committed lint runner.
- In the current CLI environment, `node` exists but `php` and `wp` may not.

```bash
# Single PHP file
php -l path/to/file.php

# Common PHP checks
php -l functions.php
php -l inc/aryeo.php
php -l inc/subscriptions.php

# All tracked PHP files
git ls-files '*.php' | xargs -n1 php -l

# Single JS file
node --check path/to/file.js

# Common JS checks
node --check assets/js/main.js
node --check assets/js/admin-portfolio-gallery.js
node --check assets/js/admin-order-delivery.js

# Generic diff hygiene
git diff --check
```

- Optional, only if WordPressCS is already installed globally: `phpcs --standard=WordPress path/to/file.php` and `phpcbf --standard=WordPress path/to/file.php`.

## Test Strategy
- No automated PHPUnit, Jest, Vitest, Playwright, or `tests/` directory was detected.
- In this repo, a "single test" usually means:
  1. syntax-check the changed PHP or JS file, and
  2. manually verify the affected WordPress flow.

```bash
# Single-test equivalents
php -l templates/page-contact.php
node --check assets/js/main.js

# Optional WP-CLI smoke checks
wp eval 'echo slm_portal_url();'
wp eval 'echo slm_admin_portal_url();'
wp eval 'echo slm_login_url();'
```

## Manual QA Expectations
- Guest flow: homepage -> services -> contact -> create account/login.
- Client flow: login -> portal -> place order -> account updates.
- Admin flow: admin portal -> orders -> memberships -> notifications.
- Membership flow: term switching, checkout links, provider-specific notices.
- Portfolio flow: slider, lightbox, keyboard navigation, and mobile behavior.
- Auth-sensitive pages must not leak cached content.

## Architecture and Imports
- Keep the theme function-oriented; do not introduce Composer, namespaces, or a new class hierarchy unless explicitly requested.
- PHP "imports" here are `require_once` statements near the top of `functions.php`.
- If you add a new `inc/*.php` module, register it with the existing `require_once __DIR__ . '/inc/...';` list.
- Front-end JS is not module-bundled; do not add `import` / `export` syntax.
- WP admin JS may use jQuery when needed for admin media or existing admin UI.

## PHP Style Guidelines
- Start PHP entry files with the `ABSPATH` guard.
- Follow the repo's existing 2-space indentation and local brace style.
- Avoid mass reformatting unrelated lines; this codebase mixes compact and expanded formatting.
- Prefer small named helpers over large nested inline closures when logic grows.
- Add scalar parameter and return types when the WordPress API contract is clear.
- Cast WP return values explicitly: `(string)`, `(int)`, `(bool)`, `(array)`.
- Use early returns for invalid state, auth failures, and missing dependencies.
- Prefix theme functions, meta keys, options, AJAX actions, and custom identifiers with `slm_`.
- Keep slug-like values lowercase and kebab-case.
- Reuse existing helpers before adding new variants, especially in `inc/aryeo.php` and `inc/subscriptions.php`.

## Request Data, Validation, and Escaping
- Never trust `$_GET`, `$_POST`, `$_COOKIE`, or webhook payloads directly.
- Follow the existing request pattern: `wp_unslash()` first when needed, then sanitize.
- Use the narrowest sanitizer that fits the value: `sanitize_text_field()`, `sanitize_textarea_field()`, `sanitize_email()`, `sanitize_key()`, `esc_url_raw()`.
- Escape output at render time with the matching function: `esc_html()`, `esc_attr()`, `esc_url()`, `esc_textarea()`.
- Verify nonces on all state-changing forms and AJAX actions.
- Check capabilities before privileged actions.

## Error Handling
- Return `WP_Error` from low-level API/data helpers when callers need to branch on failure.
- Check `is_wp_error()` immediately after API, DB, and remote-call boundaries.
- For AJAX, use `wp_send_json_error()` / `wp_send_json_success()` with readable messages.
- For admin/template flows, surface notices instead of failing silently.
- For remote requests, keep explicit timeouts and response-code handling.
- For payment and webhook flows, log enough context to debug dedupe and edge cases.

## Database and Integration Rules
- Use `$wpdb->prepare()` for dynamic SQL.
- Use `dbDelta()` for schema creation or updates, matching the current tables.
- Keep provider secrets and webhook secrets in WordPress options, never in source.
- Preserve existing dedupe, transient, and cache behavior around Aryeo and membership events.
- Do not casually rename REST routes, meta keys, option names, or webhook fields; they are integration contracts.

## JS Style Guidelines
- Wrap browser scripts in IIFEs, matching `assets/js/main.js`.
- Bail out early when expected DOM nodes are missing.
- Prefer `const` and `let` in standalone JS files.
- In legacy inline blocks that already use `var`, keep edits local unless you are safely refactoring the whole block.
- Use `data-*` attributes as JS hooks instead of brittle selector chains where possible.
- Keep interactions progressively enhanced; pages should remain usable if JS fails.
- Respect accessibility with `aria-*`, keyboard handlers, and reduced-motion behavior.

## CSS / HTML Guidelines
- Use CSS variables from `assets/css/base.css` before inventing new colors, spacing, or shadows.
- Put reusable styles in `components.css`, nav work in `nav.css`, and page-specific work in `pages.css`.
- Follow the existing BEM-like naming style such as `nav__menu` and `btn--accent`.
- Preserve the brand direction: blue/gold palette, `Outfit` for display, `Plus Jakarta Sans` for body text.
- Keep layouts responsive around the existing breakpoints, especially `980px`.
- Maintain visible `:focus-visible` states and semantic HTML.
- Avoid new inline CSS/JS unless the file already uses inline blocks and extraction would increase risk.

## Editing Strategy for Large Files
- Make surgical diffs in `inc/aryeo.php`, `inc/subscriptions.php`, and portal templates.
- Extract a helper before adding another deep conditional branch.
- Do not rename public hooks, AJAX actions, template filenames, or data attributes without a repo-wide audit.
- Keep unrelated formatting churn out of functional changes.

## Pre-Handoff Checklist
- Run syntax checks for each changed PHP or JS file.
- Manually test the exact user flow touched by the change.
- Confirm request data is sanitized and output is escaped.
- Confirm auth, capability, and nonce checks still exist.
- Confirm no secrets, API keys, or webhook secrets were added to source.
- Confirm mobile behavior for any changed front-end UI.
