# Active Context

## Current Work Focus
The site is live and has been through a stakeholder review (2026-08-06). Theme
build is substantially complete; the current focus is **post-launch remediation**
— fixing consistency defects, filling the Portfolio with real work, and closing
the SEO gaps on service sub-pages.

The authoritative backlog is [website-review-2026-08-06.md](website-review-2026-08-06.md).
Work that document rather than re-deriving priorities here.

## Current Tasks

### From the 2026-08-06 review (see the review doc for detail and code refs)
- [ ] **P1** Purge site + CDN/Cloudflare cache; re-verify menu and footer mismatches (review #4)
- [ ] **P1** Populate Portfolio categories with 8–12 items each from the client Drive folders (review #1)
- [ ] **P1** Consolidate every "Book a Shoot" CTA behind one canonical URL helper (review #2)
- [ ] **P1** Reconcile the duplicate primary nav menu in WP Admin (review #3)
- [ ] **P1** Remove `#` dead links from the Services dropdown headers (review #5)
- [ ] **P1** Fix the `/portfolio/` vs `/our-portfolio/` slug split + 301 (review #6)
- [ ] **P1** Add real title tags and meta descriptions to all 15 service sub-pages (review #7)
- [ ] **P2** Enter email-sourced testimonials with `source = Email` (review #9)
- [ ] **P2** Standardize turnaround copy to "24–48 hours" (review #10)
- [ ] **P2** Switch footer copy to the agents-AND-businesses wording (review #11)
- [ ] **P2** Repoint the Business Branding "Learn More" card to `/for-businesses/` (review #13)
- [ ] **P2** Make the 13 add-ons link to booking (review #14)
- [ ] **P2** Relabel "Login" → "Client Login" and de-emphasize it (review #15)
- [ ] **P3** Demote homepage "What We Offer" from `<h1>` to `<h2>` (review #16)
- [ ] **P3** Add hours, service-area line, and LocalBusiness schema to Contact (review #17)
- [ ] **P3** Build an FAQ page with FAQPage schema (review #18)
- [ ] **P3** Resolve the logo `alt` / `aria-hidden` decision (review #19)
- [ ] **P3** Differentiate service page content: pricing, FAQ, specifics (review #20)

### Carried over from build phase
- [ ] Finalize brand assets (confirm exact color codes, logo placement)
- [ ] Populate testimonials and portfolio items (managed via WP Admin) — **reopened: the Portfolio and testimonials are effectively empty in production (review #1, #9)**
- [ ] Configure Square for recurring membership billing
- [ ] Test full user journey: guest → registration → login → order → delivery tracking
- [ ] Test membership flow: logged-in user → join membership → Square billing
- [ ] Mobile responsiveness audit and polish
- [ ] Create "Zillow Showcase" page in WP Admin and assign the new template

## Pages that still need to be created
Referenced by the reviewed navigation, but no template exists in this repo:
- `/for-businesses/` — blocks review #13; currently only a homepage block (`template-parts/home/who.php:32`)
- Social Media Management
- Mentorship (should nest under Services, not sit top-level)

## Recent Changes
- **(2026-08-06)** Stakeholder website review completed; findings recorded and
  code-verified in [website-review-2026-08-06.md](website-review-2026-08-06.md).
  Headline issues: empty Portfolio, split booking CTA, duplicate nav menu.
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

### Settled by the 2026-08-06 review
- **Turnaround copy:** "24–48 hours" everywhere. Retire "24-hour standard delivery".
- **Footer copy:** use the agents-AND-businesses variant — the business line stays.
- **Canonical portfolio slug:** `/our-portfolio/` (matches the page `functions.php` creates); 301 `/portfolio/` to it.
- **Booking CTA:** one destination sitewide. The two-path split is a defect, not a segmentation strategy.

### Still open
- Which single booking destination wins — Aryeo order form or `/login/?mode=signup` (needed before review #2 can land)
- Whether the Drone Drive folder should genuinely serve both Drone and Business Branding portfolio categories
- Pricing display: guests cannot see prices; logged-in users can (currently hidden for all with "Contact us for current rates")
- How to expose pricing to logged-in users only (PHP conditional or CSS-based)
- Final deployment workflow (FTP vs Git-based deployment to Bluehost)
- Square integration details (API vs hosted checkout vs embeds)
- Whether the hero H1 should be rewritten to cover business marketing / social media management (review #16)

## Next Steps
Ordered per the review's execution plan:
1. Purge site + CDN cache, then re-verify the menu/footer mismatches (review #4)
2. Decide the canonical booking destination, then build the shared URL helper (review #2)
3. Load the Portfolio categories from the client Drive folders (review #1) — highest revenue impact
4. Land the remaining theme fixes: slug, SEO titles, turnaround, footer, H1
5. Create For Businesses / Social Media Management / Mentorship pages, then fix the nav and the Services card link
6. Resume build-phase work: Square membership billing, guest vs logged-in pricing, end-to-end testing on Bluehost
