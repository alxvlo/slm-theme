# Tech Context

## Tech Stack
- **CMS:** WordPress (latest stable)
- **Language:** PHP, HTML, CSS, JavaScript
- **Fonts:** Outfit (headings), Plus Jakarta Sans (body) via Google Fonts
- **Hosting:** Bluehost
- **Database:** MySQL (Bluehost-provided)
- **Payment — Orders:** Aryeo (via WP plugin — handles order forms, scheduling, and payment)
- **Payment — Memberships:** Square (recurring billing for membership plans)
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
- Orders handled through **Aryeo** (WP plugin)
- Recurring membership billing through **Square**
- Performance matters — agents browse on mobile
- No build tooling — vanilla CSS and JS (no Sass, no Webpack)

## Key Dependencies
- **WordPress core** — CMS foundation
- **Aryeo WP Plugin** — order forms, payment processing, scheduling, order tracking
- **Square** — recurring billing for membership subscriptions
- **Google Fonts** — Outfit & Plus Jakarta Sans
- **Native contact form** — built into the theme (no Contact Form 7)
- **inc/subscriptions.php** — membership/subscription logic (~1177 lines)
- **inc/aryeo.php** — Aryeo API client and webhook handling (~1155 lines)

## Avoid These
- Heavy page builders (Elementor, Divi)
- Unnecessary WordPress plugins
- Inline styles — use the theme's stylesheet system
- Build tooling / preprocessors (keep it vanilla)

## Environment Variables / WP Options
- Aryeo configuration is managed via the Aryeo WP plugin settings
- Square configuration for recurring billing (API keys managed separately)
- `slm_ops_notifications_enabled` — Toggle for admin email notifications
- `slm_ops_notification_email` — Recipient for notification emails
