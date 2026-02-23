# System Patterns

## Architecture
Custom WordPress theme ("SLM Theme") on Bluehost hosting. All functionality is built into the theme via PHP includes — no reliance on heavy page builders or third-party WordPress plugins for core features. External integrations with Aryeo (ordering) and Stripe (subscriptions) via their respective APIs.

## Key Design Patterns
- **Custom theme with page templates** — each major page has its own template file in `/templates/`
- **Includes-based module system** — core logic split into `/inc/` files (aryeo.php, subscriptions.php, testimonials.php, portfolio-gallery.php, page-editable-text.php, footer-customizer.php)
- **Custom Post Types** — `portfolio` (gallery items) and `testimonial` (client reviews) registered via theme
- **Editable page meta fields** — About and Contact pages use post meta for admin-editable text content
- **Front-end authentication** — custom login/registration template (no wp-login.php redirect)
- **Role-based portal routing** — admins → Admin Portal, clients → Client Portal
- **Aryeo API integration** — order form sessions, order tracking, webhook processing
- **Stripe API integration** — subscription checkout, billing portal, webhook handling
- **Template parts** — reusable components in `/template-parts/` for homepage sections, nav, footer
- **Cache-aware** — bypasses full-page cache for logged-in users

## Project Structure
```
/ (theme root)
├── style.css                          # Theme metadata
├── functions.php                      # Core theme setup, includes, hooks
├── header.php / footer.php            # Global layout wrappers
├── front-page.php                     # Homepage (assembles template parts)
├── home.php                           # Blog listing
├── page.php / single.php / index.php  # Default templates
├── archive.php                        # Blog/post archive
├── archive-portfolio.php              # Portfolio archive
├── single-portfolio.php               # Single portfolio item (gallery slider)
├── 404.php                            # 404 page
│
├── templates/                         # Page templates (assigned via WP Admin)
│   ├── page-services.php              # Services & memberships
│   ├── page-portfolio.php             # Portfolio gallery with lightbox
│   ├── page-about.php                 # About (admin-editable text)
│   ├── page-contact.php               # Contact form (native, no plugin)
│   ├── page-blog.php                  # Blog page template
│   ├── page-login.php                 # Front-end login/registration
│   ├── page-portal.php                # Client portal (dashboard, orders, subscription, profile)
│   ├── admin-portal.php               # Admin portal (all orders, stats, settings, notifications)
│   ├── page-service-*.php             # Individual service detail pages (7 services)
│   ├── page-privacy-policy.php        # Privacy policy
│   └── page-terms-of-service.php      # Terms of service
│
├── template-parts/
│   ├── home/                          # Homepage sections
│   │   ├── hero-slider.php            # Image slider hero
│   │   ├── services-links.php         # Services overview grid
│   │   ├── how-it-works.php           # How it works section
│   │   ├── testimonials.php           # Testimonials carousel
│   │   └── cta.php                    # Call-to-action section
│   ├── site/
│   │   ├── nav.php                    # Global navigation
│   │   ├── footer.php                 # Global footer (customizer-powered)
│   │   └── guest-dashboard.php        # Unauthenticated portal landing
│   └── blocks/                        # Block-based components
│
├── inc/                               # PHP includes (business logic)
│   ├── aryeo.php                      # Aryeo API client, ordering, webhooks (~1155 lines)
│   ├── subscriptions.php              # Stripe subscriptions, billing, webhooks (~1177 lines)
│   ├── testimonials.php               # Testimonial CPT + meta boxes
│   ├── portfolio-gallery.php          # Portfolio gallery meta + admin UI
│   ├── page-editable-text.php         # Admin-editable text fields for pages
│   └── footer-customizer.php          # Footer customizer settings
│
├── assets/
│   ├── css/
│   │   ├── base.css                   # Reset, variables, typography
│   │   ├── components.css             # Reusable UI components
│   │   ├── nav.css                    # Navigation styles
│   │   └── pages.css                  # Page-specific styles (~53KB)
│   ├── js/
│   │   ├── main.js                    # Site-wide JavaScript (~16KB)
│   │   └── admin-portfolio-gallery.js  # WP Admin gallery picker
│   ├── img/                           # Theme images, logos
│   └── media/                         # Sample/seed media (photos, drone, floor plans, etc.)
```

## Coding Conventions
- PHP following WordPress coding standards
- Clean, semantic HTML5
- Custom CSS with blue & gold brand palette (Outfit + Plus Jakarta Sans fonts)
- No heavy page builder plugins — all templates hand-coded
- Security: nonce verification on all forms, `sanitize_*` on inputs, `esc_*` on outputs
- `ABSPATH` check at top of every PHP file
- File-based cache busting via `slm_asset_ver()`
- Prefixed functions/meta keys with `slm_` namespace

## Component Relationships
- **Header/Footer** → shared across all pages via `get_header()` / `get_footer()`
- **Homepage** → assembles 5 template parts (hero, services, how-it-works, testimonials, CTA)
- **Services page** → data-driven from PHP arrays, links to Aryeo/portal for booking
- **Portfolio** → custom post type + page gallery meta, lightbox with carousel
- **Login/Registration** → custom front-end form, redirects to role-appropriate portal
- **Client Portal** → tabs: dashboard, my-orders, place-order, subscription, account
- **Admin Portal** → tabs: dashboard, all-jobs, order-detail, subscriptions, settings, notifications
- **Aryeo** → API integration for order forms, order tracking, webhook notifications
- **Stripe** → API integration for subscription checkout, billing portal, webhook events
