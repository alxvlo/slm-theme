# Website Changelog — Showcase Listings Media

A plain-English record of what has changed on showcaselistingsmedia.com since
the site review on August 6, 2026, and what is still waiting on input.
Newest changes first. Item numbers (e.g. "review item 18") refer to
`website-review-2026-08-06.md`, the review checklist this work comes from.

> **Where changes go live:** updates are first published to a private staging
> copy of the site for checking, then to the public site. A change listed here
> may still be on staging — the "Status" line on each entry says where it is.

---

## ❗ What we need from the client

Nothing below blocks the site from running — but each of these makes the site
more accurate or more effective, and only the client can provide them:

1. **Approve the FAQ wording.** Five answers on the new FAQ page were written
   conservatively because we don't know the official policy. Please confirm or
   correct: what happens when it rains, the cancellation policy, whether the
   seller needs to be home, how many photos come with a shoot, and how far in
   advance to book.
2. **Review the portfolio labels.** Every photo and video on the Portfolio
   page needs a project title and the right category (Real Estate Photography,
   Cinematic Video, Drone, Social Media / Reels, or Business Branding). This
   is done in the site's Portfolio Manager — no technical knowledge needed.
3. **Provide real project stats — or remove the samples.** The portfolio
   cards currently show *sample* results ("Sold in 8 days", "14,200 Video
   Views"). These are placeholders, not real numbers. Please supply real
   stats per project, or tell us to take the badges down.
4. **Choose the featured project.** The big showcase at the top of the
   Portfolio page can be any photo or video — pick which project it should be.
5. **Service page details.** To finish the service pages we need, per service:
   how many photos are included, typical shoot duration, and what's included
   in each package — plus a decision on whether prices show publicly or only
   to logged-in clients.
6. **Confirm the Drive folder mix-up.** The "Drone" Google Drive folder was
   listed under both Drone and Business Branding — was that intentional?
7. **Testimonial permissions.** Written permission from each client whose
   email testimonial we'll publish, before it goes on the site.

---

## August 10, 2026 — Portfolio: videos now show, labels fixed

**Status: published to staging — needs a deploy to the public site.**

- **Videos appear in the portfolio.** Videos uploaded to the portfolio were
  being saved but never shown — the page simply had no video support. They
  now appear in the grid with a play button, play in the full-screen viewer,
  and are automatically filed under "Cinematic Video" until recategorized.
- **The Featured Project no longer lies.** The big featured card was
  permanently labeled "Cinematic Video — 6000 on the River" no matter what
  media was actually shown. It now displays the real title, category, and
  stats of whichever project is marked "Featured" in the Portfolio Manager.
- **Wrong categories explained + the fix path.** Uploaded photos were being
  labeled by their position in the upload list (first six "Real Estate
  Photography", next four "Drone", and so on) — which is why labels looked
  random. Correct labels are set per item in the Portfolio Manager (see the
  client list above); once saved there, they stick.
- **Old portfolio address redirects.** The site briefly had two portfolio
  pages (an old `/portfolio/` and the current one). Visitors and search
  engines landing on the old address are now permanently redirected to the
  right page.

## August 10, 2026 — New FAQ page, service-page FAQs, duplicate page fixed

**Status: FAQ + service FAQs are live on staging and verified; deploy to the
public site pending.**

- **New FAQ page** at `/faq/` — twelve common questions about booking,
  delivery, weather, and policies, in an accessible accordion. Written so
  Google can show the answers directly in search results. Linked from the
  site footer (and added to the site menu).
- **Every service page got its own mini-FAQ** — three factual questions per
  service (delivery time, coverage area, certifications, how to book),
  also structured for Google.
- **Mentorship page cleanup.** A technical safeguard accidentally created a
  second copy of the Mentorship page on staging. The duplicate was removed,
  the original page (and its address) kept, and the safeguard fixed so it
  can't happen again. The Mentorship page also received proper search-engine
  titles.
- **Two quiet regressions caught and fixed.** An earlier layout restore had
  brought back the old "24-hour" delivery wording and a broken portfolio
  link on the homepage. Both were restored to correct values, with automated
  checks added so they can't slip back silently.

## August 10, 2026 — Site checked against the review

A page-by-page check of the live site against the August 6 review found:
booking buttons unified (item 2), the For Businesses page live and linked
correctly (item 13), the footer wording correct everywhere (item 11), and
the navigation menu consistent (items 3/5 — the earlier inconsistency was
almost certainly stale cache, since cleared). The portfolio gallery had been
populated. The review checklist was updated to match.

## August 8–10, 2026 — Behind the scenes

- A safe **staging copy** of the site was set up so every change can be
  checked privately before the public site updates, with automatic backups
  before each deploy.
- The site's **navigation** no longer goes blank if no menu is assigned.

## August 8, 2026 — First round of review fixes

All from the August 6 review, verified by automated checks:

- **One booking button** — every "Book a Shoot" button now goes to the same
  place (item 2; previously two different destinations split visitors).
- **Search-friendly page titles** — service pages stopped showing internal
  code names like "service-re-photography" in Google (item 7).
- **Delivery time consistent** — "24–48 hours" everywhere (item 10).
- **Footer covers both audiences** — agents *and* local businesses (item 11).
- **The 13 add-on services became clickable** and lead to booking (item 14).
- **"Login" renamed "Client Login"** so new visitors aren't confused (item 15).
- **One main heading per page** for cleaner search indexing (item 16).
- **Contact page** gained local-business info that helps Google show the
  business for local searches (item 17).

## August 6, 2026 — Review received

Full site review logged as `website-review-2026-08-06.md` with every finding
categorized (website code / site content / hosting) and prioritized. Headline
issues: near-empty portfolio, two different booking destinations, and
inconsistent navigation menus.

---

*Technical reference: the work above lives in the `slm-theme` repository —
round 1 in commit `8617d81`, round 2 in `4404c7b`–`6be011c`, portfolio media
in `cba3c23`. The automated check suite (`php run-tests.php`) covers 35 tests
guarding these fixes.*
