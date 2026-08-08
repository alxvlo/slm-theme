# Progress

## What Works
- ✅ WordPress installed on Bluehost
- ✅ Custom theme ("SLM Theme") with blue & gold branding
- ✅ Global header with responsive navigation
- ✅ Global footer with customizer settings
- ✅ Homepage (hero slider, services grid, how-it-works, testimonials, CTA)
- ✅ Services/Packages page (listing, social, monthly, agent memberships, add-ons)
- ✅ 8 individual service detail pages (RE photography, RE videography, drone photo/video, twilight, floor plans, virtual tours, Zillow Showcase)
- ✅ Add-ons updated: AI Twilight Photography, In-person Twilight Photography, Virtual Video
- ✅ Portfolio page with carousel and lightbox
- ✅ Portfolio CPT with gallery meta and sortable admin picker (managed via WP Admin)
- ✅ Testimonials CPT with star ratings, source, role, location meta (managed via WP Admin)
- ✅ About page with admin-editable text fields
- ✅ Contact page with native form, validation, and email delivery
- ✅ Blog templates (archive, single post, blog page)
- ✅ Front-end login/registration (themed, not wp-login.php)
- ✅ Client Portal (dashboard, my-orders, place-order, subscription, account views)
- ✅ Admin Portal (dashboard, all-jobs, order detail, subscriptions, settings, notifications)
- ✅ Aryeo integration via WP plugin (order forms, tracking, webhooks)
- ✅ Legal pages auto-generation (Privacy Policy, Terms of Service)
- ✅ Role-based portal routing (admin vs client)
- ✅ Three user types: Guest (no prices/orders), Logged-in (prices + orders), Admin (full control)
- ✅ Cache bypass for logged-in users
- ✅ Nonce protection on all forms
- ✅ CSS framework (base, components, nav, pages)
- ✅ Google Fonts (Outfit + Plus Jakarta Sans)
- ✅ Sample media assets (photos, drone, floor plans, staged, twilight, virtual tours)

## What's In Progress
- 🟡 Post-launch remediation from the 2026-08-06 review — see [website-review-2026-08-06.md](website-review-2026-08-06.md)
- 🟡 Square integration for recurring membership billing
- 🟡 Guest vs logged-in pricing visibility on services page
- 🟡 Mobile responsiveness audit

## What's Left to Build
- ⬜ Portfolio content load: 8–12 items per category from the client Drive folders (review #1)
- ⬜ Single canonical "Book a Shoot" URL helper replacing the ad-hoc CTA definitions (review #2)
- ⬜ Title tags + meta descriptions for all 15 service sub-pages (review #7)
- ⬜ For Businesses, Social Media Management, and Mentorship page templates (review #13)
- ⬜ FAQ page with FAQPage schema (review #18)
- ⬜ LocalBusiness schema, hours, and service-area block on Contact (review #17)
- ⬜ Per-service differentiation: pricing, FAQ, shoot specifics (review #20)
- ⬜ Square recurring billing integration and testing
- ⬜ Guest/logged-in conditional pricing display
- ⬜ Create "Zillow Showcase" page in WP Admin with new template
- ⬜ Full end-to-end user journey testing (all 3 user types)
- ⬜ Performance optimization (image compression, lazy loading audit)
- ⬜ DNS / SSL / domain setup (if not done)
- ⬜ Launch readiness checklist and go-live

## Known Issues

### Content / production state
- **Portfolio renders almost no work.** The filter UI is correct
  (`templates/page-portfolio.php:210-216`) but the CPT is effectively empty. This
  is the highest-impact open issue for a visual business. (review #1)
- **No usable testimonials.** The prior set came from Real Tours Google reviews and
  cannot be reused; replacements must be entered from client emails with
  `source = Email`. (review #9)
- **Stale cache in production** — WP 7.0.2 vs 6.9.4 reported across pages, the
  likely cause of the duplicate nav menu and mismatched footer. Purge before
  chasing those two as code bugs. (review #4)

### Code defects confirmed on 2026-08-08
- **Booking CTA is split across two destinations.** Each surface defines its own
  `$cta_url` / `$order_url` / `$book_url`; `template-parts/site/nav.php:8` falls
  back to signup while `:9` uses the Aryeo order URL. (review #2)
- **Portfolio slug mismatch.** `functions.php:726,960` create the page as
  `our-portfolio`, but `template-parts/home/hero-slider.php:20` and
  `template-parts/home/solution.php:45` hardcode `home_url('/portfolio/')`. (review #6)
- **Service sub-pages have no SEO metadata.** Only `page-about.php`,
  `page-contact.php`, and `page-services.php` register `pre_get_document_title`
  plus a meta description; with `add_theme_support('title-tag')` at
  `functions.php:745`, every other page falls back to its raw slug. (review #7)
- **Turnaround time stated three ways** — `hero-slider.php:22,53` ("24-hour
  standard delivery"), `why.php:44`, `how-it-works.php:33`. Canonical value is
  "24–48 hours". (review #10)
- **Footer copy** hardcodes the agents-only variant at
  `template-parts/site/footer.php:65-66`; should be the agents-AND-businesses line. (review #11)
- **Wrong Services card link** — `templates/page-services.php:444` points the
  Business Branding card at Contact instead of a For Businesses page that does not
  yet exist. (review #13)
- **13 add-ons are unlinked text** — `templates/page-services.php:308-375`, render
  loop at `:655-663`, no anchor emitted. (review #14)
- **Two H1s on the homepage** — `hero-slider.php:42` and `services-links.php:16`. (review #16)
- **Logo `alt` is empty** at `template-parts/site/nav.php:22`, inside an
  `aria-hidden="true"` wrapper; fix the pair together or not at all. (review #19)

### Carried over
- Pricing is currently hidden for ALL users ("Contact us for current rates") — needs to be visible for logged-in users only
- "Zillow Showcase" WP page needs to be created in admin and assigned the template

### Reported but not reproducible in code
- Footer social links showing raw URLs — `template-parts/site/footer.php:70-90`
  already renders labeled SVG icons. Re-check after the cache purge. (review #12)
- Two different nav menus — the theme renders one `wp_nav_menu()` at
  `template-parts/site/nav.php:30-39`. (review #3)
- Dead `#` links in the Services dropdown — no `href="#"` exists in any nav
  template; these are WP Admin menu items. (review #5)
