# System Patterns

## Architecture
WordPress CMS on Bluehost hosting. Theme-based structure with custom page templates and Aryeo integration for booking/payment processing.

## Key Design Patterns
- **CMS-driven content** — pages, posts, and media managed via WordPress Admin
- **Aryeo integration** — external embed/redirect for payment and scheduling workflows
- **Responsive, mobile-first design** — all pages built to work seamlessly on mobile devices
- **Section-based page layouts** — hero sections, services grids, testimonials, CTAs, and portfolio galleries
- **Outcomes-driven content structure** — messaging focused on agent results, not just features

## Project Structure
```
/wp-content/themes/showcase-theme/
├── style.css                 # Theme stylesheet & metadata
├── functions.php             # Theme functions & setup
├── header.php                # Global header
├── footer.php                # Global footer
├── front-page.php            # Homepage template
├── page-services.php         # Services/packages page
├── page-portfolio.php        # Portfolio/gallery page
├── page-about.php            # About page
├── page-contact.php          # Contact page
├── assets/
│   ├── css/                  # Additional stylesheets
│   ├── js/                   # JavaScript files
│   └── img/                  # Theme images & icons
└── template-parts/           # Reusable template components
    ├── hero.php
    ├── services-grid.php
    ├── testimonials.php
    └── cta-section.php
```
`[ASSUMED — structure will be finalized during development]`

## Coding Conventions
- Clean, semantic HTML5
- CSS with blue & gold brand color palette
- Minimal plugin dependencies — keep the site fast
- Accessible markup (WCAG 2.1 AA basics)
- Mobile-first responsive approach
- PHP following WordPress coding standards

## Component Relationships
- **Header/Footer** — shared across all pages, contains navigation and brand elements
- **Homepage** — assembles template parts (hero, services overview, outcomes, testimonials, CTA)
- **Services page** — displays packages/memberships with links to Aryeo for booking
- **Portfolio page** — gallery/carousel of past work with lightbox viewing
- **Aryeo** — external service handling payment processing and shoot scheduling
