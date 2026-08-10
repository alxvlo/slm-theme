# Website Review — 2026-08-06

Source: client/stakeholder review of the live site (showcaselistingsmedia.com).
Pages audited: Home, Services, Portfolio, About, Contact, Real Estate Photography.

This document is the working backlog for that review. Every item below was
cross-checked against the theme source in this repo on 2026-08-08; the
**Verified** notes record what the code actually shows, which is not always what
the live site shows (several findings are stale-cache artifacts).

## How to read this

Each item is tagged with the lane that owns the fix:

| Lane | Meaning |
|------|---------|
| **THEME** | Fix lives in this repo (PHP/CSS/JS). |
| **ADMIN** | Fix lives in WP Admin (menus, pages, CPT content, customizer). |
| **INFRA** | Fix lives in hosting/CDN (cache, redirects, DNS). |

Status values: `open`, `in-progress`, `done`, `wont-fix`, `not-reproducible`.

**Update 2026-08-08:** all THEME items except 13, 18, and 20 are now done and
covered by tests in `tests/` (`php run-tests.php`). ADMIN and INFRA items are
untouched — they cannot be fixed from this repo.

---

## Headline findings

Three issues were called out as actively costing bookings:

1. The Portfolio page renders almost no work (highest priority — this is a visual business).
2. "Book a Shoot" resolves to two different destinations depending on the page.
3. Two different versions of the primary navigation menu are live across the site.

---

## P1 — Broken or losing money

### 1. Portfolio page is nearly empty — `ADMIN` — status: open

The page ("Our Work Speaks for Itself") renders filter tabs but no media loads
under them. Only one featured project (6000 on the River) is present.

**Verified:** `templates/page-portfolio.php:210-216` defines six filter buttons —
`All`, `Real Estate Photography`, `Cinematic Video`, `Drone`,
`Social Media / Reels`, `Business Branding`. The filter UI is correct; the
gallery is populated from the Portfolio CPT via `inc/portfolio-gallery.php`, so
this is a **content** gap, not a code defect.

Note: the review wrote the tab as "Social Media"; the actual tab label is
**"Social Media / Reels"**. Use the exact label when mapping assets.

**Fix:** load 8–12 items into each of the five non-`All` categories.

**Source media (client-provided, 2026-08-06):**

| Category | Drive folder |
|----------|--------------|
| Photography (general) | https://drive.google.com/drive/folders/1gsRwkmqTKuur1vhqp7a1sdJpz92cTMu3 |
| Virtual staging | https://drive.google.com/drive/folders/1HFnOTUdrRp9A7_FqrdfkpgDAoPiMENgm |
| Interior shots | https://drive.google.com/drive/folders/1XenceGOckX_crAxsoI2pDHCOtEaJzCaz |
| Exterior shots | https://drive.google.com/drive/folders/1zkrqEfNFvKMVeGYa1TzE37HWjUmsEdzY |
| Video 1 | https://drive.google.com/drive/folders/12qZY5RiPh3WrIKr72L6_aXdedo8yavrn |
| Video 2 | https://drive.google.com/drive/folders/1bIPp92Phjk3zL2Ug74J67_bb-ki5t9s9 |
| Video 3 | https://drive.google.com/drive/folders/1fMtfgUn9JtRiqLp-Ej0dLpq_UY_HyUP8 |
| Drone | https://drive.google.com/drive/folders/1rO7J2NNr5gvT313RCAYu0f8bPDuB0wfs |
| Business 1 | https://drive.google.com/drive/folders/1ca2RlUJK4cMF_gWp6wQzG5m62bwZQjlC |
| Business 2 (same folder as Drone) | https://drive.google.com/drive/folders/1rO7J2NNr5gvT313RCAYu0f8bPDuB0wfs |
| Business 3 | https://drive.google.com/drive/folders/1XFM7QnC9Q_b3HfarLPhxy3Gm7_gJhNjs |
| Business 4 | https://drive.google.com/drive/folders/1Eis3hKlIrcudRNdtszmyYaCaTwVJPTHq |
| Business 5 | https://drive.google.com/drive/folders/1KPzCxc4EgWZsQY5qF26tNJzR4--apPwh |

The Drone folder is listed under both Drone and Business — confirm with the
client whether that was intentional before assigning it to two categories.

Compress and convert to WebP/AVIF before upload; set explicit dimensions.

**Update 2026-08-10:** live check shows the gallery is now populated (many photos render under the grid). Remaining ADMIN work: confirm each of the five filter categories holds 8–12 items, and get the client's answer on the Drone folder double-use before mapping Business assets.

---

### 2. "Book a Shoot" has two destinations — `THEME` — status: done

Home / Portfolio / Contact / service pages point at
`showcase-listings-media-1.aryeo.com/order`; Services and About point at
`/login/?mode=signup`. Two booking paths confuse users and split the analytics.

**Verified:** the split is real and originates in the templates. Each surface
defines its own CTA URL variable:

| Location | Variable | Logged-out destination |
|----------|----------|------------------------|
| `template-parts/site/nav.php:8,49` | `$order_url` | `$signup_url` (`/login/?mode=signup`) |
| `template-parts/site/nav.php:9-11,45` | `$place_order_url` | `slm_aryeo_start_order_url()` |
| `template-parts/home/hero-slider.php:17` | `$order_url` | conditional |
| `template-parts/home/cta.php:5` | `$cta_url` | conditional |
| `template-parts/home/how-it-works.php:5` | `$book_url` | conditional |
| `template-parts/home/testimonials.php:16` | `$cta_url` | conditional |
| `templates/page-about.php:19` | `$cta_url` | conditional |
| `templates/page-contact.php:19` | `$book_url` | conditional |
| `templates/page-portfolio.php:11` | `$cta_url` | conditional |
| `templates/page-services.php:21` | `$cta_url` | conditional |
| `templates/page-social-media-*.php:14` | `$cta_url` | Aryeo form URL |

**Fix:** introduce a single canonical helper (e.g. `slm_book_url()`) that every
"Book a Shoot" CTA calls, and replace the ad-hoc definitions above. Pick one
logged-out destination and use it sitewide. Keep existing logged-in behavior
(portal place-order) unless the client says otherwise.

This is the highest-leverage code change in the review — it removes a whole
class of drift rather than patching one page.

---

### 3. Two different navigation menus — `ADMIN` — status: open

Home / Portfolio / Contact use one menu; Services / About use another. The
Services/About variant is missing **For Businesses** and **Social Media
Management**, places **Zillow Showcase** differently, and promotes **Mentorship**
to a top-level item instead of nesting it.

**Verified:** `template-parts/site/nav.php:30-39` renders a single
`wp_nav_menu()` at theme location `primary`. The theme cannot produce two
different menus — so this is either a WP Admin menu assignment problem or (more
likely, given item 4) a stale page cache serving an older menu. Purge cache
first, then re-check before editing menus.

**Update 2026-08-10:** logged-in live check shows one unified menu; group headers render as labels. Re-verify logged-out after the cache purge (item 4), then close.

---

### 4. Pages served from stale cache — `INFRA` — status: open

Page source reports WordPress 7.0.2 on some pages and 6.9.4 on others, which
explains the menu and footer mismatches.

**Fix:** purge site cache and CDN/Cloudflare cache, then re-audit items 3, 11,
and 12 — all three are plausible cache artifacts. Confirm auth-sensitive pages
still bypass cache (see `AGENTS.md`, Manual QA Expectations).

---

### 5. Dead links in the Services dropdown — `ADMIN` — status: open

Group headers "Listing Media", "Social & Brand Content", "Memberships", and the
"More" tab are clickable but link to `#`.

**Verified:** no `href="#"` exists in any nav template — the only `#` hrefs in
the repo are two intentionally disabled checkout buttons
(`templates/page-portal.php:863,867`). The dead links come from WP Admin menu
items, not theme code.

**Fix:** in Appearance → Menus, convert those headers to non-clickable labels or
point them at real landing pages.

**Update 2026-08-10:** headers render non-clickable on the live dropdown (hard-coded fallback shipped in a75f1ef, or the menu was fixed). Re-verify logged-out after the purge, then close.

---

### 6. Portfolio has two URLs — `THEME` + `INFRA` — status: done

The menu links to `/portfolio/` but the page lives at `/our-portfolio/`.

**Verified — real code defect.** `functions.php:726-732` and `:960-966` create
and look up the page with `post_name => 'our-portfolio'`, but two templates
hardcode the other slug:

- `template-parts/home/hero-slider.php:20` — `home_url('/portfolio/')`
- `template-parts/home/solution.php:45` — `home_url('/portfolio/')`

`functions.php:285` does it correctly, via
`slm_page_url_by_template('templates/page-portfolio.php', '/portfolio/')`.

**Fix:** pick `/our-portfolio/` as canonical (it matches the created page),
replace both hardcoded `home_url('/portfolio/')` calls with the
`slm_page_url_by_template()` helper, and add a 301 from `/portfolio/`.

---

### 7. Service sub-pages expose raw slugs as SEO titles — `THEME` — status: done

The Real Estate Photography page's title tag is literally
`service-re-photography | Showcase Listings Media`, with no meta description.

**Verified — real code defect.** Only three templates register SEO metadata via
`pre_get_document_title` plus a `<meta name="description">` echo:

- `templates/page-about.php:9-13`
- `templates/page-contact.php:9-13`
- `templates/page-services.php:9-13`

`functions.php:745` enables `add_theme_support('title-tag')`, so every other
template falls back to the page slug. All service sub-pages are affected.

**Fix:** add the same title/description pair to every service template. Pattern:

```
Title:       Real Estate Photography in Jacksonville, FL | Showcase Listings Media
Description: Professional MLS-ready listing photography for Jacksonville and North
             Florida agents. 24–48 hour turnaround. Book online.
```

Client direction: titles should lead with the searchable service term —
"Aerial", "Business Marketing", "Real Estate Marketing" — not the internal slug.

Templates needing coverage:

- [ ] `page-service-re-photography.php`
- [ ] `page-service-re-videography.php`
- [ ] `page-service-drone-photography.php`
- [ ] `page-service-drone-videography.php`
- [ ] `page-service-twilight-photography.php`
- [ ] `page-service-virtual-tours.php`
- [ ] `page-service-floor-plans.php`
- [ ] `page-service-zillow-showcase.php`
- [ ] `page-social-media-packages.php`
- [ ] `page-social-media-assistance.php`
- [ ] `page-memberships.php`
- [x] `page-portfolio.php`
- [x] Mentorship page (no template yet — covered via `slm_meta_title`/`slm_meta_description` post meta set by auto-create hooks)
- [x] For Businesses page (no template yet — see item 13; covered via `slm_meta_title`/`slm_meta_description` post meta set by auto-create hooks)
- [x] Social Media Management page (no template yet; covered via `slm_meta_title`/`slm_meta_description` post meta set by auto-create hooks)

Consider extracting a shared `slm_page_seo(string $title, string $desc): void`
helper rather than copy-pasting the filter into fifteen files.

---

## P2 — Hurting conversions

### 9. No testimonials or reviews — `ADMIN` — status: open

The previous testimonials came from Real Tours Google reviews and cannot be
reused. The client has sent client emails about Showcase/Brittney that should be
entered using the clients' own wording.

**Verified:** `inc/testimonials.php` provides a Testimonials CPT with `source`,
role, location, and star-rating meta (`inc/testimonials.php:11-116`). The
`source` field defaults to `"Google"` (`:57`) — set it to `"Email"` (or the
client's name) for these entries so provenance stays accurate.

Get written permission before publishing names from private email.

---

### 10. Turnaround time stated three different ways — `THEME` — status: done

**Verified — real code defect.** Three different default strings:

| File | Line | Current string |
|------|------|----------------|
| `template-parts/home/hero-slider.php` | 22, 53 | `24-hour standard delivery` |
| `template-parts/home/why.php` | 44 | `Fast 24–48 Hour Turnaround` |
| `template-parts/home/how-it-works.php` | 33 | `Edits delivered within 24–48 hours` |

**Canonical value (client decision): "24–48 hours".** Update the hero badge
default. Note these are `get_post_meta()` defaults — any value already saved in
WP Admin overrides the code, so audit the homepage meta fields too.

---

### 11. Footer text differs across pages — `THEME` — status: done

Two variants are live. The client wants the one that covers **both** audiences:

> Premium real estate media for North Florida agents AND the same
> scroll-stopping content and social media systems for local businesses.

**Verified:** `template-parts/site/footer.php:65-66` currently hardcodes the
other variant ("...for agents and broker teams that want stronger listing
presentation and faster marketing execution"). The footer is global, so the
per-page variation is a cache artifact — but the code still needs updating to
the AND-business wording.

---

### 12. Footer social links show raw URLs — `THEME` — status: not-reproducible

**Verified:** `template-parts/site/footer.php:70-90` already renders inline SVG
icons wrapped in `<a aria-label="YouTube">` etc., with URLs sourced from
customizer settings (`:25-47`). The current code does **not** print raw URLs as
visible text.

**Conclusion:** almost certainly the stale cache from item 4. Re-check after the
purge; only reopen if it survives.

---

### 13. Wrong link on the Services page — `THEME` + `ADMIN` — status: done

"Brand Content for Businesses in North Florida" → Learn More goes to Contact
instead of the For Businesses page.

**Verified — real code defect.** `templates/page-services.php:444` uses
`$contact_url` while its four sibling cards (`:414`, `:424`, `:434`, `:454`) use
proper service page URLs.

**Blocker:** there is no For Businesses page or template. `For Businesses`
exists only as a homepage block heading (`template-parts/home/who.php:32`). The
page must be created before the link can be repointed at `/for-businesses/`.

**Update 2026-08-10:** verified live — the card links via slm_for_businesses_url() (templates/page-services.php:325) and /for-businesses/ is published.

---

### 14. The 13 add-ons are plain text with no links — `THEME` — status: done

**Verified.** `templates/page-services.php:308-375` defines 13 add-ons; the
render loop at `:655-663` outputs `name` + `desc` only — no anchor.

The 13: Zillow Add-On, Spotlight Reel, Agent Intro Clip, Full Agent Video, AI
Twilight Photography, In-Person Twilight Photography, Dusk Conversions, Drone
Add-On, Drone Video Add-On, Virtual Video, Detail Photos, Virtual Staging, Heavy
Photoshopping.

**Fix:** add a `url` key to each `$addons` entry and render a link in the loop,
pointing at the canonical booking URL from item 2. Do item 2 first so these
inherit the single source of truth.

---

### 15. "Login" button confuses cold visitors — `THEME` — status: done

**Verified.** `template-parts/site/nav.php:48` renders `Login` with class
`nav__login` for logged-out visitors.

**Fix:** relabel to "Client Login" and de-emphasize it relative to the primary
Book button (`assets/css/nav.css`).

---

## P3 — Fix when there's time

### 16. Two H1 headings on the homepage — `THEME` — status: done

**Verified — real code defect.**

- `template-parts/home/hero-slider.php:42` — `<h1>` hero headline
- `template-parts/home/services-links.php:16` — `<h1 id="home-services-title">`
  "What We Offer"

**Fix:** demote "What We Offer" to `<h2>` (check `assets/css/pages.css` for a
matching style so visual weight is preserved).

Also per the client: the hero headline should mention business marketing /
social media management, not real estate alone. Current default is
`Real Estate Media That Stops Scroll & Sells Listings Faster`
(`hero-slider.php:42`).

---

### 17. Contact page missing local business info — `THEME` — status: done

No address or service-area statement, no hours, no map, no LocalBusiness schema.

**Fix:** add hours, a service-area line (the five counties are already named
elsewhere on the site), and `LocalBusiness` JSON-LD carrying the phone number
and North Florida area to `templates/page-contact.php`.

---

### 18. No FAQ page — `THEME` + `ADMIN` — status: done

Unanswered: rain policy, whether the seller must be home, photo counts per
package, cancellation policy, commercial work. These rank well in search.

**Fix:** build an FAQ page with `FAQPage` schema; consider per-service FAQ
blocks (ties into item 20).

**Update 2026-08-10:** templates/page-faq.php ships the page with FAQPage JSON-LD; the page auto-creates at /faq/. Remaining ADMIN: add FAQ to the assigned primary menu in Appearance → Menus (prod does not render the code fallback), and have the client approve the flagged policy answers (rain, cancellation, seller-home, photo counts, advance booking).

---

### 19. Logo image has no alt text — `THEME` — status: done

**Verified — real code defect.** `template-parts/site/nav.php:22` renders
`alt=""`.

Nuance: the `<img>` sits inside `<span class="nav__brandLogo" aria-hidden="true">`
next to a visible `nav__brandText` with the site name (`:20-27`). So the link is
*not* currently unlabeled — the empty alt is defensible decorative markup.
Setting `alt="Showcase Listings Media logo"` while the parent stays
`aria-hidden="true"` would still hide it from assistive tech.

**Fix properly:** either drop `aria-hidden` from the wrapper and set the alt, or
leave the decorative pattern intact. Do not do half of it. Low severity.

---

### 20. Service pages lack pricing, FAQ, and unique structure — `THEME` — status: in-progress

Every service page repeats Overview → Featured Work → Benefits → Why Choose Us.
Search engines prefer unique per-page content and buyers want specifics.

**Fix:** add per-service detail (photo counts, shoot duration, what's included)
and a per-service FAQ. Interacts with the open pricing-visibility decision —
prices are currently hidden for all users pending the guest vs logged-in split.

**Update 2026-08-10:** per-service FAQ blocks shipped via template-parts/blocks/service-detail.php. Per-service details (photo counts, durations, inclusions) remain blocked on client facts and the pricing-visibility decision — do not invent numbers.

---

## Confirmed working — do not change

- Positioning: addressing agents AND businesses separately on the homepage.
- Copy quality, especially "Looking good isn't enough anymore" and the
  problem/solution flow.
- The How It Works 4-step booking walkthrough.
- CTA coverage and click-to-call phone number.
- The About page — founder photo, franchise backstory, Partners section
  (Modern Florida Home Staging, realMLS Photographer Network badge).
- Package structure: listing, social, memberships, agent memberships.
- Service area with all five counties named.
- Meta descriptions on Home, Services, About, Contact.
- Contact form fields, including brokerage/team and service dropdown.
- Accessibility basics: skip-to-content link, correct mobile viewport.
- Privacy Policy and Terms of Service pages.

---

## Suggested execution order

1. **INFRA:** purge site + CDN cache (item 4), then re-verify items 3, 11, 12.
2. **THEME:** canonical booking URL helper (item 2) — unblocks item 14.
3. **ADMIN:** populate the Portfolio categories (item 1) — highest revenue impact.
4. **THEME:** portfolio slug (6), SEO title helper + rollout (7), turnaround
   string (10), footer copy (11), H1 demotion (16).
5. **ADMIN:** create For Businesses / Social Media Management / Mentorship pages,
   then fix the Services card link (13) and the menu (3, 5).
6. **THEME:** add-on links (14), Client Login relabel (15), LocalBusiness schema
   (17), FAQ (18), logo alt decision (19), per-service content (20).
7. **ADMIN:** enter email testimonials with `source = Email` (9).

Follow the Pre-Handoff Checklist in `AGENTS.md` for every theme change:
`php -l` each changed file, manually walk the affected flow, and confirm mobile
behavior.

---

## Note on item numbering

The source review skips item 8 — it jumps from 7 to 9. Numbering here matches
the client's document so the two can be compared line by line.
