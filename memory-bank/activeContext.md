# Active Context

## Current Work Focus
Theme is substantially built. Core pages, portals, API integrations, and styling are in place. Recent service updates applied per client feedback. Focus is now on refinement, content, and deployment readiness.

## Current Tasks
- [ ] Finalize brand assets (confirm exact color codes, logo placement)
- [x] Populate testimonials and portfolio items (managed via WP Admin)
- [ ] Configure Square for recurring membership billing
- [ ] Test full user journey: guest → registration → login → order → delivery tracking
- [ ] Test membership flow: logged-in user → join membership → Square billing
- [ ] Mobile responsiveness audit and polish
- [ ] SEO optimization (meta tags, Open Graph, page titles)
- [ ] Create "Zillow Showcase" page in WP Admin and assign the new template

## Recent Changes
- **(2026-02-24)** Service updates per client feedback:
  - Added "Zillow Showcase" service detail page (`page-service-zillow-showcase.php`)
  - Split twilight add-on into "AI Twilight Photography" and "In-person Twilight Photography"
  - Added "Virtual Video" add-on
  - Updated anchor map in `functions.php` for new Zillow Showcase template
- **(2026-02-24)** Memory Bank updated with corrections:
  - Stripe → Square for recurring membership billing
  - Aryeo integration is via WP plugin (not just theme-level)
  - Clarified three user types: Guest, Logged-in User, Admin
  - Testimonials and portfolio items managed via WP Admin

## Active Decisions
- Pricing display: guests cannot see prices; logged-in users can (currently hidden for all with "Contact us for current rates")
- How to expose pricing to logged-in users only (PHP conditional or CSS-based)
- Final deployment workflow (FTP vs Git-based deployment to Bluehost)
- Square integration details (API vs hosted checkout vs embeds)

## Next Steps
- Configure Square for membership billing
- Implement guest vs logged-in pricing visibility on services page
- Create "Zillow Showcase" page in WP Admin with the new template
- End-to-end testing on Bluehost
- DNS and SSL setup if not already configured
- Launch readiness checklist
