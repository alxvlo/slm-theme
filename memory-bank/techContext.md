# Tech Context

## Tech Stack
- **CMS:** WordPress (latest stable)
- **Language:** PHP, HTML, CSS, JavaScript
- **Fonts:** Outfit (headings), Plus Jakarta Sans (body) via Google Fonts
- **Hosting:** Bluehost
- **Database:** MySQL (Bluehost-provided)
- **Payment — Orders:** Aryeo (API integration, order form sessions, webhooks)
- **Payment — Subscriptions:** Stripe (checkout, billing portal, webhooks)
- **Styling:** Custom CSS — blue & gold palette, modern & sleek

## Development Commands
```bash
# Theme is developed locally and deployed to Bluehost
# No build step required — vanilla PHP/CSS/JS

# WordPress CLI (if available on Bluehost):
wp theme activate slm-theme
```

## Technical Constraints
- Must be hosted on **Bluehost** (shared hosting — no Node.js server-side)
- Order payment must go through **Aryeo** (API key + order form sessions)
- Subscription billing must go through **Stripe** (API keys + webhooks)
- Performance matters — agents browse on mobile
- No build tooling — vanilla CSS and JS (no Sass, no Webpack)

## Key Dependencies
- **WordPress core** — CMS foundation
- **Aryeo API** — order form creation, order tracking, webhook processing
  - Config: API key, default order form ID, webhook secret (stored in WP options)
  - Endpoints: orders, order forms, webhooks
- **Stripe API** — subscription checkout, customer management, billing portal
  - Config: secret key, publishable key, webhook secret, portal config ID (stored in WP options)
  - Handles: checkout sessions, customer creation, subscription lifecycle webhooks
- **Google Fonts** — Outfit & Plus Jakarta Sans
- **Native contact form** — built into the theme (no Contact Form 7)
- **Custom DB table** — `slm_subscription_events` for webhook event logging

## Avoid These
- Heavy page builders (Elementor, Divi)
- Unnecessary WordPress plugins
- Inline styles — use the theme's stylesheet system
- Third-party payment solutions other than Aryeo and Stripe
- Build tooling / preprocessors (keep it vanilla)

## Environment Variables / WP Options
- `slm_aryeo_api_key` — Aryeo API authentication
- `slm_aryeo_default_order_form_id` — Default order form
- `slm_aryeo_webhook_secret` — Webhook signature verification
- `slm_subscriptions_stripe_secret_key` — Stripe secret key
- `slm_subscriptions_stripe_publishable_key` — Stripe publishable key
- `slm_subscriptions_stripe_webhook_secret` — Stripe webhook verification
- `slm_subscriptions_stripe_portal_config_id` — Stripe billing portal config
- `slm_subscriptions_plans` — JSON array of subscription plan definitions
- `slm_ops_notifications_enabled` — Toggle for admin email notifications
- `slm_ops_notification_email` — Recipient for notification emails
