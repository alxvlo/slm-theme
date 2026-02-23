# Tech Context

## Tech Stack
- **CMS:** WordPress (latest stable)
- **Language:** PHP, HTML, CSS, JavaScript
- **Hosting:** Bluehost
- **Payment/Booking:** Aryeo (external integration)
- **Styling:** Custom CSS — blue & gold palette, modern & sleek aesthetic
- **Database:** MySQL (provided by Bluehost/WordPress)

## Development Commands
```bash
# WordPress on Bluehost — primarily admin-based workflow
# Local development (if using Local by Flywheel or similar):
# 1. Import site to Local
# 2. Develop theme locally
# 3. Push changes to Bluehost via FTP/SFTP or Git

# If using WP-CLI on Bluehost:
wp theme activate showcase-theme
wp plugin install contact-form-7 --activate
```
`[ASSUMED — workflow to be finalized based on local dev setup]`

## Technical Constraints
- Must be hosted on **Bluehost**
- Payment processing must go through **Aryeo**
- Must work on shared hosting environment (no Node.js server-side)
- Performance matters — agents browse on mobile with varying connections

## Key Dependencies
- **WordPress core** — CMS foundation
- **Aryeo** — booking, payment, and scheduling (via embed or redirect links)
- **Contact form plugin** — for contact page inquiries `[ASSUMED — e.g., Contact Form 7 or WPForms]`

## Avoid These
- Heavy page builders (Elementor, Divi) unless client specifically requests `[ASSUMED]`
- Unnecessary plugins that bloat page load times
- Inline styles — use the theme's stylesheet system
- Third-party payment solutions other than Aryeo

## Environment Variables
- Database credentials managed by Bluehost / `wp-config.php`
- Aryeo API keys or embed URLs (if applicable)
- SMTP configuration for contact form emails `[ASSUMED]`
