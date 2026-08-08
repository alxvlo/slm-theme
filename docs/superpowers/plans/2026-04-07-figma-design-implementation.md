# Figma Design Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the Figma/React design from `alxvlo/figma-slm` into the existing PHP WordPress theme, section-by-section, with WordPress Admin media pickers for all page assets and an in-page admin panel for the portfolio (no WP Admin required for portfolio).

**Architecture:** Each page template and `template-parts/home/` partial maps 1:1 to a Figma component. CSS lives in `assets/css/` split by concern (`base.css` = tokens/global, `components.css` = shared UI, `pages.css` = section-specific). PHP pulls editable content from WordPress post meta (managed via meta boxes in `inc/`). The portfolio admin panel is a password-protected in-page JS panel — no WP Admin needed.

**Tech Stack:** PHP 8+, WordPress 6.x, Vanilla CSS (CSS custom properties), Vanilla JS, WordPress Media Uploader (wp.media), ACF Free (hero slides).

---

## Design System Reference

| Token | Current | Figma Target |
|---|---|---|
| `--accent` | `#C8934A` | `#C9922A` |
| `--primary` | `#0D1B2A` | `#0D1B2A` (same) |
| `--background` | `#f5f7fa` | `#f3f6fb` |
| `--foreground` | `#0d1b2a` | `#13223a` |
| `--muted-foreground` | `#5a6a82` | `#60708a` |
| `--font-display` | Outfit, Sora, Manrope | Outfit |
| `--font-sans` | Plus Jakarta Sans, Manrope | Plus Jakarta Sans |
| Container max-width | `1220px` | `1280px` |

---

## File Map

| File | Action | What changes |
|---|---|---|
| `assets/css/base.css` | Modify | Token updates, container 1280px |
| `assets/css/components.css` | Modify | Button variants, card styles, badge |
| `assets/css/nav.css` | Modify | Transparent hero nav, Services dropdown |
| `assets/css/pages.css` | Modify | All section CSS (Problem, Solution, etc.) |
| `template-parts/site/nav.php` | Modify | Services dropdown, transparent-on-home |
| `template-parts/home/problem.php` | Modify | Centered pill-list layout |
| `template-parts/home/solution.php` | Modify | 3-pillar cards, updated icons/CTA |
| `template-parts/home/who.php` | Modify | Dark navy 2-card layout |
| `template-parts/home/services-links.php` | Modify | 6-card icon grid |
| `template-parts/home/why.php` | Modify | 6-card grid, updated icon styling |
| `template-parts/home/how-it-works.php` | Modify | 4-step horizontal layout with connectors |
| `template-parts/home/testimonials.php` | Modify | 6-card grid + Before/After slider |
| `template-parts/home/cta.php` | Modify | Dark gradient, gold blur orbs, 3 buttons |
| `templates/page-about.php` | Modify | Add Story, Beliefs, Partners, Approach sections |
| `templates/page-contact.php` | Modify | Service dropdown, 2-column layout |
| `templates/page-services.php` | Modify | Photo/Video/Drone/Add-ons grouped layout |
| `templates/page-portfolio.php` | Modify | New categories, updated admin panel |
| `inc/homepage-meta.php` | Modify | Add image picker fields for Solution, Testimonials |
| `inc/about-meta.php` | Create | Partner photo pickers, About page text fields |
| `inc/services-meta.php` | Modify | Add image pickers for each service card |
| `functions.php` | Modify | require `inc/about-meta.php` |

---

## Task 1: Update CSS Design Tokens and Base Styles

**Files:**
- Modify: `assets/css/base.css`
- Modify: `assets/css/components.css`

- [ ] **Step 1: Update design tokens in `base.css`**

In `assets/css/base.css`, update the `:root` block:
```css
:root {
  --font-size: 18.5px;
  --font-sans: "Plus Jakarta Sans", "Manrope", "Avenir Next", "Segoe UI", sans-serif;
  --font-display: "Outfit", "Sora", "Manrope", "Avenir Next", sans-serif;

  --background: #f3f6fb;        /* was #f5f7fa */
  --foreground: #13223a;        /* was #0d1b2a */
  --surface: #ffffff;
  --surface-2: #ebf2ff;         /* was #edf2ff */
  --card: #ffffff;
  --card-foreground: #13223a;
  --popover: #ffffff;
  --popover-foreground: #13223a;

  --primary: #0D1B2A;
  --primary-strong: #0a1521;    /* was #080f18 */
  --primary-foreground: #ffffff;
  --secondary: #ebf2ff;
  --secondary-foreground: #0D1B2A;
  --muted: #eef2f8;
  --muted-foreground: #60708a;  /* was #5a6a82 */
  --accent: #C9922A;            /* was #C8934A */
  --accent-light: rgba(201, 146, 42, 0.12);
  --accent-foreground: #ffffff;
  --destructive: #d4183d;
  --destructive-foreground: #ffffff;
  --border: rgba(15, 39, 68, 0.13);  /* was rgba(13,27,42,0.11) */
  --border-strong: rgba(15, 39, 68, 0.18);
  --input: transparent;
  --input-background: #f8faff;
  --switch-background: #c8d4e4;
  --ring: rgba(21, 52, 91, 0.22);

  --glass-bg: rgba(255, 255, 255, 0.72);
  --glass-bg-dark: rgba(13, 27, 42, 0.55);
  --glass-border: rgba(255, 255, 255, 0.32);
  --glass-border-dark: rgba(255, 255, 255, 0.14);
  --glass-blur: saturate(180%) blur(18px);
  --glow-gold: 0 0 32px rgba(201, 146, 42, 0.28);

  --font-weight-medium: 500;
  --font-weight-normal: 400;

  --radius: 16px;
  --radius-sm: 10px;
  --radius-lg: 24px;
  --radius-xl: 32px;
  --shadow-sm: 0 4px 16px rgba(11, 25, 44, 0.07);
  --shadow-md: 0 12px 36px rgba(11, 25, 44, 0.11);
  --shadow-lg: 0 24px 56px rgba(11, 25, 44, 0.15);
  --shadow-xl: 0 40px 80px rgba(11, 25, 44, 0.18);

  --container: 1280px;          /* was 1220px */
  --pad: 24px;

  --sidebar: #0d1e32;
  --sidebar-foreground: #ffffff;
  --sidebar-primary: #c89a5f;
  --sidebar-primary-foreground: #ffffff;
  --sidebar-accent: #1a3652;
  --sidebar-accent-foreground: #ffffff;
  --sidebar-border: rgba(255, 255, 255, 0.11);
  --sidebar-ring: #c89a5f;

  --ease-out: cubic-bezier(0.22, 1, 0.36, 1);
  --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
  --duration-fast: 150ms;
  --duration-base: 220ms;
  --duration-slow: 400ms;
}
```

- [ ] **Step 2: Update shared button styles in `components.css`**

Find and update the `.btn` base and variants to match Figma:
```css
/* Primary button — gold fill */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  border-radius: 999px;
  font-family: var(--font-sans);
  font-size: 14px;
  font-weight: 700;
  line-height: 1;
  text-decoration: none;
  cursor: pointer;
  border: none;
  transition:
    background var(--duration-base) ease,
    box-shadow var(--duration-base) ease,
    transform var(--duration-fast) var(--ease-out);

  background: var(--accent);
  color: var(--accent-foreground);
  box-shadow: 0 4px 24px rgba(201, 146, 42, 0.35);
}

.btn:hover {
  background: #b8841f;
  box-shadow: 0 4px 24px rgba(201, 146, 42, 0.5);
  transform: translateY(-1px);
  color: #ffffff;
}

/* Dark navy button */
.btn--dark {
  background: var(--primary);
  color: #ffffff;
  box-shadow: 0 4px 16px rgba(13, 27, 42, 0.2);
}

.btn--dark:hover {
  background: #1a3050;
  box-shadow: 0 4px 24px rgba(13, 27, 42, 0.3);
  color: #ffffff;
}

/* Ghost/outline button */
.btn--ghost {
  background: transparent;
  color: rgba(255, 255, 255, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.22);
  box-shadow: none;
}

.btn--ghost:hover {
  background: rgba(255, 255, 255, 0.10);
  border-color: rgba(255, 255, 255, 0.40);
  color: #ffffff;
  box-shadow: none;
  transform: none;
}

/* Section eyebrow label */
.section-eyebrow {
  display: block;
  font-family: var(--font-sans);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--accent);
  margin-bottom: 12px;
}
```

- [ ] **Step 3: Verify in browser**

Load the homepage. Buttons and accent colors should shift to the new gold (#C9922A). No other layout changes yet.

- [ ] **Step 4: Commit**
```bash
git add assets/css/base.css assets/css/components.css
git commit -m "style: update design tokens and button variants to match Figma spec"
```

---

## Task 2: Update Navigation (Transparent + Services Dropdown)

**Files:**
- Modify: `template-parts/site/nav.php`
- Modify: `assets/css/nav.css`

The Figma navbar is:
- **Transparent** with white text when on the homepage AND not scrolled
- **Solid white** (glassmorphism) when scrolled OR on any non-homepage page
- Has a **Services dropdown** listing all 11 service sub-pages
- Desktop CTAs: `Login` (border button) + `Book a Shoot` (gold button)

- [ ] **Step 1: Rewrite `template-parts/site/nav.php`**

```php
<?php
if (!defined('ABSPATH')) exit;

$is_logged_in  = is_user_logged_in();
$login_url     = add_query_arg('mode', 'login', slm_login_url());
$signup_url    = add_query_arg('mode', 'signup', slm_login_url());
$dashboard_url = $is_logged_in ? slm_dashboard_url() : $login_url;
$place_order_url = function_exists('slm_aryeo_start_order_url')
  ? slm_aryeo_start_order_url()
  : add_query_arg('view', 'place-order', slm_portal_url());

$logo_src = get_template_directory_uri() . '/assets/img/logo-icon.png';
$logo_abs = get_template_directory() . '/assets/img/logo-icon.png';
$has_logo = file_exists($logo_abs);

$service_links = [
  ['label' => 'All Services',             'url' => home_url('/services/')],
  ['label' => 'RE Photography',           'url' => home_url('/services/real-estate-photography/')],
  ['label' => 'RE Videography',           'url' => home_url('/services/real-estate-videography/')],
  ['label' => 'Drone Photography',        'url' => home_url('/services/drone-photography/')],
  ['label' => 'Drone Videography',        'url' => home_url('/services/drone-videography/')],
  ['label' => 'Virtual Tours',            'url' => home_url('/services/virtual-tours/')],
  ['label' => 'Floor Plans',              'url' => home_url('/services/floor-plans/')],
  ['label' => 'Twilight Photography',     'url' => home_url('/services/twilight-photography/')],
  ['label' => 'Zillow Showcase',          'url' => home_url('/services/zillow-showcase/')],
  ['label' => 'Social Media Packages',    'url' => home_url('/services/social-media-packages/')],
  ['label' => 'Social Media Assistance',  'url' => home_url('/services/social-media-assistance/')],
];
?>

<nav class="nav" aria-label="Primary" data-nav>
  <!-- Logo / Brand -->
  <a class="nav__brand" href="<?php echo esc_url(home_url('/')); ?>">
    <span class="nav__brandLogo" aria-hidden="true">
      <?php if ($has_logo): ?>
        <img class="nav__logoImg" src="<?php echo esc_url($logo_src); ?>" alt="" width="34" height="34" decoding="async" loading="eager">
      <?php else: ?>
        <span class="nav__logoFallback">SLM</span>
      <?php endif; ?>
    </span>
    <span class="nav__brandText"><?php echo esc_html(get_bloginfo('name')); ?></span>
  </a>

  <!-- Desktop center nav -->
  <div class="nav__center" id="site-primary-nav" data-nav-panel>
    <?php
      wp_nav_menu([
        'theme_location' => 'primary',
        'container'      => false,
        'menu_id'        => 'site-primary-menu',
        'menu_class'     => 'nav__menu',
        'fallback_cb'    => '__return_false',
      ]);
    ?>

    <!-- Services dropdown -->
    <div class="nav__dropdown-wrap" data-dropdown>
      <button class="nav__dropdown-trigger" type="button" aria-expanded="false" aria-haspopup="true" data-dropdown-trigger>
        Services
        <svg class="nav__dropdown-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
      </button>
      <div class="nav__dropdown" data-dropdown-panel role="menu">
        <?php foreach ($service_links as $i => $link): ?>
          <a
            class="nav__dropdown-item <?php echo $i === 0 ? 'nav__dropdown-item--featured' : ''; ?>"
            href="<?php echo esc_url($link['url']); ?>"
            role="menuitem"
          >
            <?php echo esc_html($link['label']); ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Desktop right CTAs -->
  <div class="nav__right">
    <?php if ($is_logged_in): ?>
      <a class="nav__login" href="<?php echo esc_url($dashboard_url); ?>">Dashboard</a>
      <a class="btn btn--dark nav__cta" href="<?php echo esc_url($place_order_url); ?>">Book a Shoot</a>
      <a class="btn btn--ghost" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
    <?php else: ?>
      <a class="nav__login" href="<?php echo esc_url($login_url); ?>">Login</a>
      <a class="btn nav__cta" href="<?php echo esc_url($signup_url); ?>">Book a Shoot</a>
    <?php endif; ?>

    <button
      class="nav__toggle"
      type="button"
      aria-label="Toggle navigation menu"
      aria-expanded="false"
      aria-controls="site-primary-nav"
      data-nav-toggle
    >
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </button>
  </div>
</nav>
```

- [ ] **Step 2: Add dropdown CSS to `assets/css/nav.css`**

Append to `nav.css`:
```css
/* ── Transparent hero nav (homepage only, not scrolled) ── */
.site-header--hero-transparent {
  border-bottom-color: transparent;
}

.site-header--hero-transparent::before {
  background: transparent !important;
}

.site-header--hero-transparent .nav__brandText,
.site-header--hero-transparent .nav__menu a,
.site-header--hero-transparent .nav__login,
.site-header--hero-transparent .nav__dropdown-trigger {
  color: rgba(255, 255, 255, 0.88);
}

.site-header--hero-transparent .nav__brandText:hover,
.site-header--hero-transparent .nav__menu a:hover,
.site-header--hero-transparent .nav__dropdown-trigger:hover {
  color: #ffffff;
}

.site-header--hero-transparent .nav__login {
  border-color: rgba(255, 255, 255, 0.22);
}

.site-header--hero-transparent .nav__toggle span {
  background: #ffffff;
}

/* ── Services Dropdown ── */
.nav__dropdown-wrap {
  position: relative;
}

.nav__dropdown-trigger {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 8px 14px;
  border-radius: 10px;
  background: none;
  border: none;
  cursor: pointer;
  font-family: var(--font-sans);
  font-size: 13.5px;
  font-weight: 600;
  color: inherit;
  transition: color var(--duration-base) ease, background var(--duration-base) ease;
}

.nav__dropdown-trigger:hover {
  background: rgba(13, 27, 42, 0.05);
}

.nav__dropdown-chevron {
  transition: transform var(--duration-base) ease;
}

.nav__dropdown-wrap[aria-expanded="true"] .nav__dropdown-chevron,
.nav__dropdown-wrap.is-open .nav__dropdown-chevron {
  transform: rotate(180deg);
}

.nav__dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 280px;
  background: #ffffff;
  border-radius: 18px;
  box-shadow: 0 16px 48px rgba(13, 27, 42, 0.14);
  border: 1px solid rgba(15, 39, 68, 0.07);
  overflow: hidden;
  opacity: 0;
  transform: translateY(-8px) scale(0.97);
  pointer-events: none;
  transition:
    opacity var(--duration-base) var(--ease-out),
    transform var(--duration-base) var(--ease-out);
  z-index: 200;
}

.nav__dropdown-wrap.is-open .nav__dropdown {
  opacity: 1;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

.nav__dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 18px;
  font-family: var(--font-sans);
  font-size: 13.5px;
  font-weight: 600;
  color: #1a2e45;
  text-decoration: none;
  transition: background var(--duration-fast) ease;
}

.nav__dropdown-item:hover {
  background: #f5f7fb;
}

.nav__dropdown-item--featured {
  border-bottom: 1px solid rgba(15, 39, 68, 0.07);
  color: var(--accent);
  font-weight: 700;
}

.nav__dropdown-item--featured:hover {
  background: rgba(201, 146, 42, 0.06);
}
```

- [ ] **Step 3: Update `assets/js/main.js` to handle dropdown + transparent nav**

Add to `main.js` (find the nav init section or add at document ready):
```js
// ── Transparent hero nav (homepage only) ──
(function () {
  var header = document.querySelector('.site-header');
  if (!header) return;

  var isHome = document.body.classList.contains('home') || document.body.classList.contains('front-page');
  if (!isHome) return;

  function updateNav() {
    if (window.scrollY < 60) {
      header.classList.add('site-header--hero-transparent');
    } else {
      header.classList.remove('site-header--hero-transparent');
    }
  }

  updateNav();
  window.addEventListener('scroll', updateNav, { passive: true });
})();

// ── Services dropdown ──
(function () {
  var wraps = document.querySelectorAll('[data-dropdown]');
  wraps.forEach(function (wrap) {
    var trigger = wrap.querySelector('[data-dropdown-trigger]');
    var panel   = wrap.querySelector('[data-dropdown-panel]');
    if (!trigger || !panel) return;

    function open() {
      wrap.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
    }
    function close() {
      wrap.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
    }

    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      wrap.classList.contains('is-open') ? close() : open();
    });

    document.addEventListener('click', function (e) {
      if (!wrap.contains(e.target)) close();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') close();
    });
  });
})();
```

- [ ] **Step 4: Verify in browser**

- On homepage, header should be transparent (white text) until user scrolls
- Services link should show dropdown with all 11 service links on click
- On `/about`, `/services`, etc., header should be solid white immediately

- [ ] **Step 5: Commit**
```bash
git add template-parts/site/nav.php assets/css/nav.css assets/js/main.js
git commit -m "feat: add transparent hero nav and Services dropdown to match Figma"
```

---

## Task 3: Homepage — Problem Section Redesign

**Files:**
- Modify: `template-parts/home/problem.php`
- Modify: `assets/css/pages.css`

Figma changes the Problem section from 3 cards → centered single-column with an alert icon, headline, 3 red bullet pills, a closing statement, and a CTA button.

- [ ] **Step 1: Rewrite `template-parts/home/problem.php`**

```php
<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');
?>

<section id="home-problem" class="home-problem" aria-labelledby="home-problem-title">
  <div class="container">
    <div class="home-problem__inner js-reveal">

      <div class="home-problem__icon-wrap" aria-hidden="true">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
      </div>

      <h2 id="home-problem-title">
        <?php echo esc_html(get_post_meta($pid, 'hp_problem_headline', true) ?: "Looking good isn't enough anymore."); ?>
      </h2>

      <p class="home-problem__lead">
        <?php echo esc_html(get_post_meta($pid, 'hp_problem_intro', true) ?: "Most listings and businesses blend in. Here's why:"); ?>
      </p>

      <ul class="home-problem__pills" aria-label="Common problems">
        <li class="home-problem__pill">
          <span class="home-problem__pill-dot" aria-hidden="true"></span>
          <?php echo esc_html(get_post_meta($pid, 'hp_problem_bullet_1', true) ?: 'Photos look the same as every other listing'); ?>
        </li>
        <li class="home-problem__pill">
          <span class="home-problem__pill-dot" aria-hidden="true"></span>
          <?php echo esc_html(get_post_meta($pid, 'hp_problem_bullet_2', true) ?: 'Videos feel generic and uninspired'); ?>
        </li>
        <li class="home-problem__pill">
          <span class="home-problem__pill-dot" aria-hidden="true"></span>
          <?php echo esc_html(get_post_meta($pid, 'hp_problem_bullet_3', true) ?: 'Content gets ignored — zero engagement'); ?>
        </li>
      </ul>

      <p class="home-problem__closing">
        <?php echo esc_html(get_post_meta($pid, 'hp_problem_closing', true) ?: "If your marketing isn't stopping the scroll, it's costing you opportunities."); ?>
      </p>

      <?php
        $cta = get_post_meta($pid, 'hp_problem_cta', true) ?: 'Book a Shoot — We\'ll Fix That';
        $is_logged_in = is_user_logged_in();
        $cta_url = $is_logged_in
          ? add_query_arg('view', 'place-order', slm_portal_url())
          : add_query_arg('mode', 'signup', slm_login_url());
      ?>
      <a class="btn" href="<?php echo esc_url($cta_url); ?>">
        <?php echo esc_html($cta); ?>
      </a>

    </div>
  </div>
</section>
```

- [ ] **Step 2: Update Problem CSS in `assets/css/pages.css`**

Find and replace the existing `.home-problem` block:
```css
/* ─── PROBLEM ─────────────────────────────────────────────── */
.home-problem {
  position: relative;
  overflow: hidden;
  background: #ffffff;
}

.home-problem::before {
  content: '';
  position: absolute;
  top: 0; left: 0;
  width: 288px; height: 288px;
  border-radius: 50%;
  background: rgba(201, 146, 42, 0.05);
  filter: blur(60px);
  translate: -50% -50%;
  pointer-events: none;
}

.home-problem__inner {
  max-width: 640px;
  margin: 0 auto;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  padding: 96px 0;
}

.home-problem__icon-wrap {
  width: 56px; height: 56px;
  border-radius: 18px;
  background: rgba(201, 146, 42, 0.10);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
  flex-shrink: 0;
}

.home-problem__lead {
  color: var(--muted-foreground);
  font-size: 17px;
  line-height: 1.65;
  margin: 0;
}

.home-problem__pills {
  list-style: none;
  margin: 4px 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
  width: 100%;
}

.home-problem__pill {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  border-radius: 12px;
  background: #fef4f4;
  border: 1px solid #f8d8d8;
  font-family: var(--font-sans);
  font-size: 15px;
  font-weight: 500;
  color: var(--foreground);
  text-align: left;
}

.home-problem__pill-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: var(--destructive);
  flex-shrink: 0;
}

.home-problem__closing {
  font-size: 17px;
  font-weight: 600;
  line-height: 1.6;
  color: var(--foreground);
  margin: 0;
}
```

- [ ] **Step 3: Verify in browser**

Problem section should show: icon, h2, lead text, 3 red pills, closing statement, gold CTA button — all centered.

- [ ] **Step 4: Commit**
```bash
git add template-parts/home/problem.php assets/css/pages.css
git commit -m "feat: redesign Problem section to centered pill-list layout per Figma"
```

---

## Task 4: Homepage — Solution Section Redesign

**Files:**
- Modify: `template-parts/home/solution.php`
- Modify: `assets/css/pages.css`

Figma: light `#f3f6fb` background, 3 icon cards (Eye, Star, TrendingUp), "See Our Work" CTA link at bottom.

- [ ] **Step 1: Update `template-parts/home/solution.php`**

```php
<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');

$pillars = [
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
    'title_key' => 'hp_solution_point_1',
    'title_default' => 'Stop the scroll',
    'body_key' => '',
    'body_default' => 'Content that makes people pause, look twice, and take action.',
  ],
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
    'title_key' => 'hp_solution_point_2',
    'title_default' => 'Elevate your brand',
    'body_key' => '',
    'body_default' => 'We shoot for your identity, not just the space.',
  ],
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
    'title_key' => 'hp_solution_point_3',
    'title_default' => 'Stand out in a crowded market',
    'body_key' => '',
    'body_default' => 'Whether it\'s a listing or a business — we make people pay attention.',
  ],
];
?>

<section class="home-solution" aria-labelledby="home-solution-title">
  <div class="container">

    <div class="home-solution__header js-reveal">
      <span class="section-eyebrow">Our Approach</span>
      <h2 id="home-solution-title">
        <?php echo esc_html(get_post_meta($pid, 'hp_solution_headline', true) ?: "We don't just create content — we create attention."); ?>
      </h2>
      <p class="home-solution__sub">
        <?php echo esc_html(get_post_meta($pid, 'hp_solution_body', true) ?: "Every project is approached with intention. We don't shoot to fill a gallery — we shoot to get you more clients."); ?>
      </p>
    </div>

    <div class="home-solution__cards">
      <?php foreach ($pillars as $i => $p): ?>
        <div class="home-solution__card js-reveal" style="transition-delay: <?php echo $i * 0.12; ?>s">
          <div class="home-solution__card-icon" aria-hidden="true">
            <?php echo $p['icon']; ?>
          </div>
          <h3><?php echo esc_html(get_post_meta($pid, $p['title_key'], true) ?: $p['title_default']); ?></h3>
          <p><?php echo esc_html($p['body_default']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="home-solution__cta js-reveal">
      <a class="btn btn--dark" href="<?php echo esc_url(home_url('/portfolio/')); ?>">
        <?php echo esc_html(get_post_meta($pid, 'hp_solution_cta', true) ?: 'See Our Work'); ?>
      </a>
    </div>

  </div>
</section>
```

- [ ] **Step 2: Add/update Solution CSS in `assets/css/pages.css`**

```css
/* ─── SOLUTION ──────────────────────────────────────────────── */
.home-solution {
  background: var(--background);
  padding: 96px 0;
}

.home-solution__header {
  text-align: center;
  margin-bottom: 56px;
}

.home-solution__sub {
  color: var(--muted-foreground);
  font-size: 17px;
  line-height: 1.65;
  max-width: 540px;
  margin: 16px auto 0;
}

.home-solution__cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

@media (max-width: 860px) {
  .home-solution__cards { grid-template-columns: 1fr; }
}

.home-solution__card {
  padding: 32px;
  border-radius: 20px;
  background: #ffffff;
  border: 1px solid rgba(15, 39, 68, 0.06);
  box-shadow: 0 2px 16px rgba(11, 25, 44, 0.04);
  transition:
    box-shadow var(--duration-slow) var(--ease-out),
    transform var(--duration-base) var(--ease-out);
}

.home-solution__card:hover {
  box-shadow: 0 8px 32px rgba(11, 25, 44, 0.10);
  transform: translateY(-4px);
}

.home-solution__card-icon {
  width: 48px; height: 48px;
  border-radius: 14px;
  background: rgba(201, 146, 42, 0.10);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
  margin-bottom: 20px;
  transition: background var(--duration-base) ease;
}

.home-solution__card:hover .home-solution__card-icon {
  background: rgba(201, 146, 42, 0.20);
}

.home-solution__card h3 {
  font-size: 20px;
  color: var(--primary-strong);
  margin-bottom: 8px;
}

.home-solution__card p {
  color: var(--muted-foreground);
  font-size: 15px;
  line-height: 1.6;
  margin: 0;
}

.home-solution__cta {
  text-align: center;
  margin-top: 40px;
}
```

- [ ] **Step 3: Verify, commit**
```bash
git add template-parts/home/solution.php assets/css/pages.css
git commit -m "feat: redesign Solution section — 3 pillar cards with icon and See Our Work CTA"
```

---

## Task 5: Homepage — WhoWeServe Section Redesign

**Files:**
- Modify: `template-parts/home/who.php`
- Modify: `assets/css/pages.css`

Figma: dark navy `#0D1B2A` background with gold blurs, 2 frosted glass cards (Real Estate Agents, Businesses) each with 4 bullets and a CTA link.

- [ ] **Step 1: Update `template-parts/home/who.php`**

```php
<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');
$services_url = home_url('/services/');
$is_logged_in = is_user_logged_in();
$order_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());

$cards = [
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
    'title' => get_post_meta($pid, 'hp_who_agents_title', true) ?: 'For Real Estate Agents',
    'bullets' => [
      get_post_meta($pid, 'hp_who_agents_bullet_1', true) ?: 'Win more listings with stronger visuals',
      get_post_meta($pid, 'hp_who_agents_bullet_2', true) ?: 'Stand out from other agents in your market',
      get_post_meta($pid, 'hp_who_agents_bullet_3', true) ?: 'Showcase homes at a higher level',
      get_post_meta($pid, 'hp_who_agents_bullet_4', true) ?: 'Build a recognizable personal brand',
    ],
    'cta_label' => get_post_meta($pid, 'hp_who_agents_cta', true) ?: 'View Agent Services',
    'cta_url'   => $services_url,
  ],
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
    'title' => get_post_meta($pid, 'hp_who_biz_title', true) ?: 'For Businesses',
    'bullets' => [
      get_post_meta($pid, 'hp_who_biz_bullet_1', true) ?: 'Attract more clients with professional content',
      get_post_meta($pid, 'hp_who_biz_bullet_2', true) ?: 'Create a strong, lasting first impression',
      get_post_meta($pid, 'hp_who_biz_bullet_3', true) ?: 'Elevate your online presence and visibility',
      get_post_meta($pid, 'hp_who_biz_bullet_4', true) ?: 'Turn views and clicks into real customers',
    ],
    'cta_label' => get_post_meta($pid, 'hp_who_biz_cta', true) ?: 'View Business Services',
    'cta_url'   => $services_url,
  ],
];
?>

<section class="home-who" aria-labelledby="home-who-title">
  <div class="home-who__bg-blur-1" aria-hidden="true"></div>
  <div class="home-who__bg-blur-2" aria-hidden="true"></div>

  <div class="container">

    <div class="home-who__header js-reveal">
      <span class="section-eyebrow"><?php echo esc_html(get_post_meta($pid, 'hp_who_subheadline', true) ?: 'Who We Serve'); ?></span>
      <h2 id="home-who-title">
        <?php echo esc_html(get_post_meta($pid, 'hp_who_headline', true) ?: 'Built for agents. Designed for brands.'); ?>
      </h2>
      <p class="home-who__sub">
        Whether you're a real estate agent in Jacksonville, a growing brand in North Florida, or a business looking to elevate your presence — the goal is the same.
      </p>
    </div>

    <div class="home-who__cards">
      <?php foreach ($cards as $i => $card): ?>
        <div class="home-who__card js-reveal" style="transition-delay: <?php echo $i * 0.15; ?>s">
          <div class="home-who__card-icon" aria-hidden="true">
            <?php echo $card['icon']; ?>
          </div>
          <h3><?php echo esc_html($card['title']); ?></h3>
          <ul class="home-who__card-list">
            <?php foreach ($card['bullets'] as $bullet): ?>
              <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                <?php echo esc_html($bullet); ?>
              </li>
            <?php endforeach; ?>
          </ul>
          <a class="home-who__card-cta" href="<?php echo esc_url($card['cta_url']); ?>">
            <?php echo esc_html($card['cta_label']); ?>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>
```

- [ ] **Step 2: Add Who CSS in `assets/css/pages.css`**

```css
/* ─── WHO WE SERVE ──────────────────────────────────────────── */
.home-who {
  position: relative;
  overflow: hidden;
  background: var(--primary);
  padding: 96px 0;
}

.home-who__bg-blur-1,
.home-who__bg-blur-2 {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
}

.home-who__bg-blur-1 {
  top: 0; left: 0;
  width: 384px; height: 384px;
  background: rgba(201, 146, 42, 0.10);
  filter: blur(100px);
}

.home-who__bg-blur-2 {
  bottom: 0; right: 0;
  width: 384px; height: 384px;
  background: rgba(26, 48, 80, 0.50);
  filter: blur(100px);
}

.home-who__header {
  text-align: center;
  margin-bottom: 56px;
}

.home-who__header .section-eyebrow { color: var(--accent); }

.home-who__header h2 {
  color: #ffffff;
  margin: 0 0 16px;
}

.home-who__sub {
  color: rgba(255, 255, 255, 0.60);
  font-size: 17px;
  margin: 0 auto;
  max-width: 640px;
}

.home-who__cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

@media (max-width: 720px) {
  .home-who__cards { grid-template-columns: 1fr; }
}

.home-who__card {
  padding: 32px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(4px);
}

.home-who__card-icon {
  width: 48px; height: 48px;
  border-radius: 14px;
  background: rgba(201, 146, 42, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
  margin-bottom: 20px;
}

.home-who__card h3 {
  color: #ffffff;
  font-size: 22px;
  margin-bottom: 20px;
}

.home-who__card-list {
  list-style: none;
  padding: 0;
  margin: 0 0 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.home-who__card-list li {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  color: rgba(255, 255, 255, 0.80);
  font-size: 15px;
  line-height: 1.5;
}

.home-who__card-list svg {
  color: var(--accent);
  flex-shrink: 0;
  margin-top: 2px;
}

.home-who__card-cta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--accent);
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  transition: gap var(--duration-fast) ease;
}

.home-who__card-cta:hover { gap: 10px; }
```

- [ ] **Step 3: Verify, commit**
```bash
git add template-parts/home/who.php assets/css/pages.css
git commit -m "feat: redesign WhoWeServe section — dark navy 2-card layout per Figma"
```

---

## Task 6: Homepage — Services Links Section Redesign

**Files:**
- Modify: `template-parts/home/services-links.php`
- Modify: `assets/css/pages.css`

Figma: white background, 6-card grid, each card has an icon + title + description + implicit link to service page. "View All Services + Pricing" CTA below.

- [ ] **Step 1: Update `template-parts/home/services-links.php`**

```php
<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');

$services = [
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>',
    'title_key' => 'hp_service_1_title',
    'title_default' => 'Real Estate Photography',
    'body_key' => 'hp_service_1_body',
    'body_default' => 'MLS-ready photos that make listings stop the scroll and attract buyers.',
    'url' => home_url('/services/real-estate-photography/'),
  ],
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>',
    'title_key' => 'hp_service_2_title',
    'title_default' => 'Cinematic Listing Videos',
    'body_key' => 'hp_service_2_body',
    'body_default' => 'Smooth, modern walkthroughs built for MLS and social media.',
    'url' => home_url('/services/real-estate-videography/'),
  ],
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg>',
    'title_key' => 'hp_service_3_title',
    'title_default' => 'Social Media Content',
    'body_key' => 'hp_service_3_body',
    'body_default' => 'Custom Reels and Shorts designed to grow your presence and drive engagement.',
    'url' => home_url('/services/social-media-packages/'),
  ],
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
    'title_key' => 'hp_service_4_title',
    'title_default' => 'Business Branding Content',
    'body_key' => 'hp_service_4_body',
    'body_default' => 'Professional photo and video that attracts clients and elevates your brand.',
    'url' => home_url('/services/'),
  ],
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
    'title_key' => 'hp_service_5_title',
    'title_default' => 'Drone Photography & Video',
    'body_key' => 'hp_service_5_body',
    'body_default' => 'Aerial imagery that showcases land, views, and property surroundings.',
    'url' => home_url('/services/drone-photography/'),
  ],
  [
    'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/></svg>',
    'title_key' => 'hp_service_6_title',
    'title_default' => 'Zillow / Marketing Add-Ons',
    'body_key' => 'hp_service_6_body',
    'body_default' => 'Zillow walkthroughs, marketing add-ons, and partnership packages available.',
    'url' => home_url('/services/zillow-showcase/'),
  ],
];
?>

<section class="home-services" aria-labelledby="home-services-title">
  <div class="container">

    <div class="home-services__header js-reveal">
      <span class="section-eyebrow">
        <?php echo esc_html(get_post_meta($pid, 'hp_services_headline', true) ?: 'Our Services'); ?>
      </span>
      <h2 id="home-services-title">
        <?php echo esc_html(get_post_meta($pid, 'hp_services_subheadline', true) ?: 'What We Offer'); ?>
      </h2>
      <p class="home-services__sub">
        Professional photo, video, and content services — built for agents and businesses who want to stand out.
      </p>
    </div>

    <div class="home-services__grid">
      <?php foreach ($services as $i => $svc): ?>
        <a class="home-services__card js-reveal" href="<?php echo esc_url($svc['url']); ?>" style="transition-delay: <?php echo $i * 0.08; ?>s">
          <div class="home-services__card-icon" aria-hidden="true">
            <?php echo $svc['icon']; ?>
          </div>
          <h3><?php echo esc_html(get_post_meta($pid, $svc['title_key'], true) ?: $svc['title_default']); ?></h3>
          <p><?php echo esc_html(get_post_meta($pid, $svc['body_key'], true) ?: $svc['body_default']); ?></p>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="home-services__cta js-reveal">
      <a class="btn btn--dark" href="<?php echo esc_url(home_url('/services/')); ?>">
        <?php echo esc_html(get_post_meta($pid, 'hp_services_pricing_line', true) ?: 'View All Services + Pricing'); ?>
      </a>
    </div>

  </div>
</section>
```

- [ ] **Step 2: Add Services CSS in `assets/css/pages.css`**

```css
/* ─── HOME SERVICES ─────────────────────────────────────────── */
.home-services {
  background: #ffffff;
  padding: 96px 0;
  position: relative;
  overflow: hidden;
}

.home-services::after {
  content: '';
  position: absolute;
  bottom: 0; right: 0;
  width: 320px; height: 320px;
  border-radius: 50%;
  background: rgba(201, 146, 42, 0.05);
  filter: blur(60px);
  translate: 50% 50%;
  pointer-events: none;
}

.home-services__header {
  text-align: center;
  margin-bottom: 56px;
}

.home-services__sub {
  color: var(--muted-foreground);
  font-size: 17px;
  line-height: 1.65;
  max-width: 540px;
  margin: 16px auto 0;
}

.home-services__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

@media (max-width: 980px) {
  .home-services__grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 600px) {
  .home-services__grid { grid-template-columns: 1fr; }
}

.home-services__card {
  display: block;
  padding: 28px;
  border-radius: 20px;
  border: 1px solid rgba(15, 39, 68, 0.06);
  background: #f9fbff;
  text-decoration: none;
  color: inherit;
  transition:
    background var(--duration-base) ease,
    border-color var(--duration-base) ease,
    box-shadow var(--duration-slow) var(--ease-out),
    transform var(--duration-base) var(--ease-out);
}

.home-services__card:hover {
  background: #ffffff;
  border-color: rgba(201, 146, 42, 0.20);
  box-shadow: 0 10px 40px rgba(11, 25, 44, 0.08);
  transform: translateY(-4px);
}

.home-services__card-icon {
  width: 48px; height: 48px;
  border-radius: 14px;
  background: rgba(13, 27, 42, 0.05);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  margin-bottom: 20px;
  transition: background var(--duration-base) ease, color var(--duration-base) ease;
}

.home-services__card:hover .home-services__card-icon {
  background: rgba(201, 146, 42, 0.10);
  color: var(--accent);
}

.home-services__card h3 {
  font-size: 18px;
  color: var(--primary-strong);
  margin-bottom: 6px;
}

.home-services__card p {
  color: var(--muted-foreground);
  font-size: 14.5px;
  line-height: 1.6;
  margin: 0;
}

.home-services__cta {
  text-align: center;
  margin-top: 40px;
}
```

- [ ] **Step 3: Verify, commit**
```bash
git add template-parts/home/services-links.php assets/css/pages.css
git commit -m "feat: redesign Services section — 6-card icon grid per Figma"
```

---

## Task 7: Homepage — Why Us Section Redesign

**Files:**
- Modify: `template-parts/home/why.php`
- Modify: `assets/css/pages.css`

Figma: light background, 6-card grid, each card has icon (gradient bg), title, description, "Call Us Now" CTA.

- [ ] **Step 1: Update `template-parts/home/why.php`**

```php
<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');

$cards = [
  [
    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>',
    'title_key' => 'hp_why_1_title',
    'title_default' => 'Every Shoot Is Tailored',
    'body_key' => 'hp_why_1_body',
    'body_default' => 'No cookie-cutter approach. Every project is built around your property, your brand, and your goals.',
  ],
  [
    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    'title_key' => 'hp_why_2_title',
    'title_default' => 'We Shoot for Your Brand',
    'body_key' => 'hp_why_2_body',
    'body_default' => 'We get to know you and your business so the content reflects your identity — not just the space.',
  ],
  [
    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    'title_key' => 'hp_why_3_title',
    'title_default' => 'Fast 24-48 Hour Turnaround',
    'body_key' => 'hp_why_3_body',
    'body_default' => 'Your finished content delivered fast — so you can list, post, and market without delays.',
  ],
  [
    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
    'title_key' => 'hp_why_4_title',
    'title_default' => 'Clear Communication Always',
    'body_key' => 'hp_why_4_body',
    'body_default' => 'From booking to delivery, you always know what\'s happening. No chasing, no guessing.',
  ],
  [
    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
    'title_key' => 'hp_why_5_title',
    'title_default' => 'Locally Based in Jacksonville',
    'body_key' => 'hp_why_5_body',
    'body_default' => 'We know North Florida — the market, the neighborhoods, and what makes listings stand out here.',
  ],
  [
    'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    'title_key' => 'hp_why_6_title',
    'title_default' => 'Focused on Results',
    'body_key' => 'hp_why_6_body',
    'body_default' => 'We don\'t just make things look good — we create content that gets attention, drives action, and helps you grow.',
  ],
];
?>

<section id="why" class="home-why" aria-labelledby="home-why-title">
  <div class="container">

    <div class="home-why__header js-reveal">
      <span class="section-eyebrow">Why Choose Us</span>
      <h2 id="home-why-title">
        <?php echo esc_html(get_post_meta($pid, 'hp_why_headline', true) ?: 'Why Showcase Listings Media'); ?>
      </h2>
      <p class="home-why__sub">
        <?php echo esc_html(get_post_meta($pid, 'hp_why_subheadline', true) ?: "We're not just another media company. Here's what makes us different."); ?>
      </p>
    </div>

    <div class="home-why__grid">
      <?php foreach ($cards as $i => $card): ?>
        <div class="home-why__card js-reveal" style="transition-delay: <?php echo $i * 0.08; ?>s">
          <div class="home-why__card-icon" aria-hidden="true">
            <?php echo $card['icon']; ?>
          </div>
          <h3><?php echo esc_html(get_post_meta($pid, $card['title_key'], true) ?: $card['title_default']); ?></h3>
          <p><?php echo esc_html(get_post_meta($pid, $card['body_key'], true) ?: $card['body_default']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="home-why__cta js-reveal">
      <a class="btn btn--dark" href="tel:+19042945809">
        <?php echo esc_html(get_post_meta($pid, 'hp_why_cta', true) ?: 'Call Us Now'); ?>
      </a>
    </div>

  </div>
</section>
```

- [ ] **Step 2: Add WhyUs CSS in `assets/css/pages.css`**

```css
/* ─── WHY US ────────────────────────────────────────────────── */
.home-why {
  background: var(--background);
  padding: 96px 0;
}

.home-why__header {
  text-align: center;
  margin-bottom: 56px;
}

.home-why__sub {
  color: var(--muted-foreground);
  font-size: 17px;
  max-width: 520px;
  margin: 16px auto 0;
}

.home-why__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

@media (max-width: 980px) { .home-why__grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .home-why__grid { grid-template-columns: 1fr; } }

.home-why__card {
  padding: 28px;
  border-radius: 20px;
  background: #ffffff;
  border: 1px solid rgba(15, 39, 68, 0.06);
  box-shadow: 0 2px 12px rgba(11, 25, 44, 0.03);
  transition:
    box-shadow var(--duration-slow) var(--ease-out),
    transform var(--duration-base) var(--ease-out);
}

.home-why__card:hover {
  box-shadow: 0 8px 32px rgba(11, 25, 44, 0.08);
  transform: translateY(-4px);
}

.home-why__card-icon {
  width: 44px; height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(201, 146, 42, 0.15), rgba(201, 146, 42, 0.05));
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
  margin-bottom: 16px;
}

.home-why__card h3 {
  font-size: 17px;
  color: var(--primary-strong);
  margin-bottom: 6px;
}

.home-why__card p {
  color: var(--muted-foreground);
  font-size: 14.5px;
  line-height: 1.6;
  margin: 0;
}

.home-why__cta {
  text-align: center;
  margin-top: 40px;
}
```

- [ ] **Step 3: Verify, commit**
```bash
git add template-parts/home/why.php assets/css/pages.css
git commit -m "feat: redesign Why Us section — 6-card icon grid per Figma"
```

---

## Task 8: Homepage — How It Works Section Redesign

**Files:**
- Modify: `template-parts/home/how-it-works.php`
- Modify: `assets/css/pages.css`

Figma: white background, 4 steps in a horizontal grid, large step numbers (opacity 0.3), connecting line between steps on desktop, gold CTA button.

- [ ] **Step 1: Update `template-parts/home/how-it-works.php`**

```php
<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');
$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());

$steps = [
  [
    'num' => '01',
    'title_key' => 'hp_step_1_title',
    'title_default' => 'Book your shoot',
    'body_key' => 'hp_step_1_body',
    'body_default' => 'Choose your service, pick a date, and schedule online in minutes.',
  ],
  [
    'num' => '02',
    'title_key' => 'hp_step_2_title',
    'title_default' => 'We capture your content',
    'body_key' => 'hp_step_2_body',
    'body_default' => 'Our team arrives prepared to capture your property or brand at its best.',
  ],
  [
    'num' => '03',
    'title_key' => 'hp_step_3_title',
    'title_default' => 'Edits delivered in 24-48 hours',
    'body_key' => 'hp_step_3_body',
    'body_default' => 'Your finished assets land in your inbox, ready for MLS, social, and marketing.',
  ],
  [
    'num' => '04',
    'title_key' => 'hp_step_4_title',
    'title_default' => 'You post, market, and stand out',
    'body_key' => 'hp_step_4_body',
    'body_default' => 'Go live with content that stops the scroll and gets your listing or brand noticed.',
  ],
];
?>

<section id="how" class="home-how" aria-labelledby="home-how-title">
  <div class="container">

    <div class="home-how__header js-reveal">
      <span class="section-eyebrow">The Process</span>
      <h2 id="home-how-title">
        <?php echo esc_html(get_post_meta($pid, 'hp_process_headline', true) ?: 'How It Works'); ?>
      </h2>
      <p class="home-how__sub">
        <?php echo esc_html(get_post_meta($pid, 'hp_process_subheadline', true) ?: 'From booking to delivery — here\'s exactly what to expect.'); ?>
      </p>
    </div>

    <div class="home-how__steps">
      <?php foreach ($steps as $i => $step): ?>
        <div class="home-how__step js-reveal" style="transition-delay: <?php echo $i * 0.10; ?>s">
          <span class="home-how__step-num" aria-hidden="true"><?php echo esc_html($step['num']); ?></span>
          <h3><?php echo esc_html(get_post_meta($pid, $step['title_key'], true) ?: $step['title_default']); ?></h3>
          <p><?php echo esc_html(get_post_meta($pid, $step['body_key'], true) ?: $step['body_default']); ?></p>
          <?php if ($i < count($steps) - 1): ?>
            <div class="home-how__connector" aria-hidden="true"></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="home-how__cta js-reveal">
      <a class="btn" href="<?php echo esc_url($cta_url); ?>">
        <?php echo esc_html(get_post_meta($pid, 'hp_process_cta', true) ?: 'Book Your Shoot Today'); ?>
      </a>
    </div>

  </div>
</section>
```

- [ ] **Step 2: Add HowItWorks CSS in `assets/css/pages.css`**

```css
/* ─── HOW IT WORKS ───────────────────────────────────────────── */
.home-how {
  background: #ffffff;
  padding: 96px 0;
  position: relative;
  overflow: hidden;
}

.home-how__header {
  text-align: center;
  margin-bottom: 56px;
}

.home-how__sub {
  color: var(--muted-foreground);
  font-size: 17px;
  margin: 16px auto 0;
}

.home-how__steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

@media (max-width: 860px) {
  .home-how__steps { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 520px) {
  .home-how__steps { grid-template-columns: 1fr; }
}

.home-how__step {
  position: relative;
  padding: 28px;
  border-radius: 20px;
  background: #f9fbff;
  border: 1px solid rgba(15, 39, 68, 0.06);
}

.home-how__step-num {
  display: block;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 42px;
  line-height: 1;
  color: var(--accent);
  opacity: 0.30;
  margin-bottom: 16px;
}

.home-how__step h3 {
  font-size: 17px;
  color: var(--primary-strong);
  margin-bottom: 6px;
}

.home-how__step p {
  color: var(--muted-foreground);
  font-size: 14.5px;
  line-height: 1.6;
  margin: 0;
}

/* Horizontal connector line (desktop only) */
.home-how__connector {
  display: none;
}

@media (min-width: 861px) {
  .home-how__connector {
    display: block;
    position: absolute;
    top: 50%;
    right: -14px;
    width: 28px;
    height: 2px;
    background: rgba(201, 146, 42, 0.20);
    z-index: 1;
  }
}

.home-how__cta {
  text-align: center;
  margin-top: 40px;
}
```

- [ ] **Step 3: Verify, commit**
```bash
git add template-parts/home/how-it-works.php assets/css/pages.css
git commit -m "feat: redesign How It Works section — 4-step horizontal layout per Figma"
```

---

## Task 9: Homepage — Testimonials Section Redesign

**Files:**
- Modify: `template-parts/home/testimonials.php`
- Modify: `inc/homepage-meta.php` (add Before/After image pickers)
- Modify: `assets/css/pages.css`

Figma: light background, 6 testimonial cards in a 3-col grid, then a Before/After visual comparison section below (dark navy, 2 images side by side with labels). Before/After images are editable via WP Admin.

- [ ] **Step 1: Verify Before/After meta box in `inc/homepage-meta.php`**

Open `inc/homepage-meta.php` and confirm `slm_render_homepage_meta_box_before_after` exists and registers the `before_image`, `after_image`, `before_label`, `after_label` fields. If the function exists and the meta box is registered under `slm_homepage_before_after` — it's already working, skip to Step 2.

If it is missing, add this to the `add_meta_boxes` action in `inc/homepage-meta.php`:
```php
add_meta_box('slm_homepage_before_after', 'Homepage: Before/After Visuals', 'slm_render_homepage_meta_box_before_after', 'page', 'normal', 'high');
```

And ensure the `save_post` hook in that file reads:
```php
$nonce1 = (string) ($_POST['slm_homepage_meta_nonce'] ?? '');
if (wp_verify_nonce($nonce1, 'slm_homepage_meta_save')) {
  foreach (['before_image', 'after_image', 'before_label', 'after_label'] as $field) {
    if (isset($_POST[$field])) {
      update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
    }
  }
}
```

- [ ] **Step 2: Update `template-parts/home/testimonials.php`**

```php
<?php
if (!defined('ABSPATH')) exit;

// ── Testimonials data (pulled from inc/testimonials.php) ──────────
$testimonials = function_exists('slm_get_testimonials') ? slm_get_testimonials() : [];

// Fallback default testimonials if none saved
if (empty($testimonials)) {
  $testimonials = [
    ['name' => 'Sarah M.',   'role' => 'Real Estate Agent', 'location' => 'Jacksonville, FL', 'rating' => 5, 'text' => 'Showcase Listings Media completely changed the way I market my listings. The photos are stunning and my clients love the walkthrough videos.', 'source' => 'Google'],
    ['name' => 'James T.',   'role' => 'Broker',            'location' => 'St. Augustine, FL', 'rating' => 5, 'text' => 'Fast turnaround, incredible quality, and they truly understand what agents need. I recommend them to every agent on my team.', 'source' => 'Google'],
    ['name' => 'Brittney R.','role' => 'Small Business Owner','location' => 'Jacksonville, FL','rating' => 5, 'text' => 'They shot our brand content and it\'s been a game changer for our social media. Professional, creative, and easy to work with.', 'source' => 'Facebook'],
    ['name' => 'Michael K.', 'role' => 'Real Estate Agent', 'location' => 'Ponte Vedra, FL',  'rating' => 5, 'text' => 'The drone footage alone is worth it. My listings look incredible from above and buyers get a real sense of the property.', 'source' => 'Google'],
    ['name' => 'Ashley D.',  'role' => 'Team Lead',         'location' => 'Orange Park, FL',   'rating' => 5, 'text' => 'We\'ve used several photography companies and Showcase Listings Media is by far the best. Consistent quality, great communication, and always on time.', 'source' => 'Google'],
    ['name' => 'Carlos V.',  'role' => 'Real Estate Agent', 'location' => 'Fleming Island, FL','rating' => 5, 'text' => 'The social media reels they create get way more views than anything I\'ve posted before. They know exactly what works on Instagram and TikTok.', 'source' => 'Facebook'],
  ];
}

// ── Before/After images from admin meta ───────────────────────
$pid = get_option('page_on_front');
$before_id    = (int) get_post_meta($pid, 'before_image', true);
$after_id     = (int) get_post_meta($pid, 'after_image', true);
$before_label = get_post_meta($pid, 'before_label', true) ?: 'Before';
$after_label  = get_post_meta($pid, 'after_label', true)  ?: 'After';
$before_url   = $before_id ? wp_get_attachment_image_url($before_id, 'large') : '';
$after_url    = $after_id  ? wp_get_attachment_image_url($after_id,  'large') : '';

$headline = get_post_meta($pid, 'hp_proof_headline', true) ?: 'Real Results. Real Clients.';
$subheadline = get_post_meta($pid, 'hp_proof_subheadline', true) ?: "Don't take our word for it — here's what agents and businesses across North Florida are saying.";
?>

<!-- ── Testimonials ── -->
<section id="testimonials" class="home-testimonials" aria-labelledby="home-testimonials-title">
  <div class="container">

    <div class="home-testimonials__header js-reveal">
      <span class="section-eyebrow">Social Proof</span>
      <h2 id="home-testimonials-title"><?php echo esc_html($headline); ?></h2>
      <p class="home-testimonials__sub"><?php echo esc_html($subheadline); ?></p>
    </div>

    <div class="home-testimonials__grid">
      <?php foreach (array_slice($testimonials, 0, 6) as $i => $t): ?>
        <div class="home-testimonials__card js-reveal" style="transition-delay: <?php echo $i * 0.06; ?>s">
          <div class="home-testimonials__card-head">
            <div class="home-testimonials__avatar" aria-hidden="true">
              <?php echo esc_html(mb_substr($t['name'], 0, 1)); ?>
            </div>
            <div>
              <p class="home-testimonials__name"><?php echo esc_html($t['name']); ?></p>
              <p class="home-testimonials__meta"><?php echo esc_html($t['role']); ?> &bull; <?php echo esc_html($t['location']); ?></p>
            </div>
            <span class="home-testimonials__source"><?php echo esc_html($t['source'] ?? 'Google'); ?></span>
          </div>
          <div class="home-testimonials__stars" aria-label="<?php echo (int) ($t['rating'] ?? 5); ?> stars">
            <?php for ($s = 0; $s < 5; $s++): ?>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <?php endfor; ?>
          </div>
          <p class="home-testimonials__text"><?php echo esc_html($t['text']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<?php if ($before_url && $after_url): ?>
<!-- ── Before / After Comparison ── -->
<section class="home-ba" aria-label="Before and after comparison">
  <div class="container">
    <div class="home-ba__inner js-reveal">
      <div class="home-ba__col">
        <p class="home-ba__label"><?php echo esc_html($before_label); ?></p>
        <img src="<?php echo esc_url($before_url); ?>" alt="<?php echo esc_attr($before_label); ?> photo" loading="lazy" decoding="async">
      </div>
      <div class="home-ba__col">
        <p class="home-ba__label"><?php echo esc_html($after_label); ?></p>
        <img src="<?php echo esc_url($after_url); ?>" alt="<?php echo esc_attr($after_label); ?> photo" loading="lazy" decoding="async">
      </div>
    </div>
  </div>
</section>
<?php endif; ?>
```

- [ ] **Step 3: Add Testimonials + Before/After CSS in `assets/css/pages.css`**

```css
/* ─── TESTIMONIALS ───────────────────────────────────────────── */
.home-testimonials {
  background: var(--background);
  padding: 96px 0;
}

.home-testimonials__header {
  text-align: center;
  margin-bottom: 56px;
}

.home-testimonials__sub {
  color: var(--muted-foreground);
  font-size: 17px;
  max-width: 560px;
  margin: 16px auto 0;
}

.home-testimonials__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

@media (max-width: 980px) { .home-testimonials__grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .home-testimonials__grid { grid-template-columns: 1fr; } }

.home-testimonials__card {
  padding: 24px;
  border-radius: 20px;
  background: #ffffff;
  border: 1px solid rgba(15, 39, 68, 0.06);
  box-shadow: 0 2px 12px rgba(11, 25, 44, 0.03);
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.home-testimonials__card-head {
  display: flex;
  align-items: center;
  gap: 12px;
}

.home-testimonials__avatar {
  width: 40px; height: 40px;
  border-radius: 50%;
  background: var(--primary);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 14px;
  flex-shrink: 0;
}

.home-testimonials__name {
  font-family: var(--font-sans);
  font-weight: 700;
  font-size: 14px;
  color: var(--primary-strong);
  margin: 0;
}

.home-testimonials__meta {
  color: var(--muted-foreground);
  font-size: 12px;
  margin: 0;
}

.home-testimonials__source {
  margin-left: auto;
  color: var(--muted-foreground);
  font-size: 11px;
  font-weight: 600;
  flex-shrink: 0;
}

.home-testimonials__stars {
  display: flex;
  gap: 2px;
  color: var(--accent);
}

.home-testimonials__text {
  color: var(--foreground);
  font-size: 14px;
  line-height: 1.6;
  margin: 0;
}

/* ─── BEFORE / AFTER ─────────────────────────────────────────── */
.home-ba {
  background: var(--primary);
  padding: 80px 0;
}

.home-ba__inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

@media (max-width: 680px) {
  .home-ba__inner { grid-template-columns: 1fr; }
}

.home-ba__col img {
  width: 100%;
  aspect-ratio: 16/10;
  object-fit: cover;
  border-radius: 16px;
}

.home-ba__label {
  font-family: var(--font-sans);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--accent);
  margin: 0 0 12px;
}
```

- [ ] **Step 4: Verify, commit**
```bash
git add template-parts/home/testimonials.php assets/css/pages.css inc/homepage-meta.php
git commit -m "feat: redesign Testimonials section — 6-card grid + Before/After section per Figma"
```

---

## Task 10: Homepage — Final CTA Section Redesign

**Files:**
- Modify: `template-parts/home/cta.php`
- Modify: `assets/css/pages.css`

Figma: dark gradient background (`#0D1B2A → #132b4a → #0D1B2A`), gold glow orb top-right, dark blur bottom-left, headline with gold highlight on "stand out", 3 buttons: Book a Shoot (gold), Call (ghost), Send a Message (ghost).

- [ ] **Step 1: Update `template-parts/home/cta.php`**

```php
<?php
if (!defined('ABSPATH')) exit;
$pid = get_option('page_on_front');
$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
$contact_url = home_url('/contact/');

$headline = get_post_meta($pid, 'hp_finalcta_headline', true) ?: "Ready to <em>stand out</em>?";
$sub      = get_post_meta($pid, 'hp_finalcta_sub', true)      ?: "Whether you're an agent or a business, your content should work for you — not blend in.";
$btn1     = get_post_meta($pid, 'hp_finalcta_btn_primary', true) ?: 'Book a Shoot Today';
$btn2     = get_post_meta($pid, 'hp_finalcta_btn_call', true)    ?: 'Call (904) 294-5809';
$btn3     = get_post_meta($pid, 'hp_finalcta_btn_message', true) ?: 'Send a Message';
?>

<section id="cta" class="home-cta js-reveal" aria-labelledby="home-cta-title">
  <div class="home-cta__bg-orb home-cta__bg-orb--top" aria-hidden="true"></div>
  <div class="home-cta__bg-orb home-cta__bg-orb--bottom" aria-hidden="true"></div>

  <div class="container">
    <div class="home-cta__inner">
      <h2 id="home-cta-title">
        <?php
          // Allow a simple <em> tag in the headline for the gold highlight
          echo wp_kses($headline, ['em' => []]);
        ?>
      </h2>
      <p class="home-cta__sub"><?php echo esc_html($sub); ?></p>
      <div class="home-cta__actions">
        <a class="btn" href="<?php echo esc_url($cta_url); ?>">
          <?php echo esc_html($btn1); ?>
        </a>
        <a class="btn btn--ghost" href="tel:+19042945809">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.56a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <?php echo esc_html($btn2); ?>
        </a>
        <a class="btn btn--ghost" href="<?php echo esc_url($contact_url); ?>">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <?php echo esc_html($btn3); ?>
        </a>
      </div>
    </div>
  </div>
</section>
```

- [ ] **Step 2: Add Final CTA CSS in `assets/css/pages.css`**

```css
/* ─── FINAL CTA ─────────────────────────────────────────────── */
.home-cta {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #0D1B2A 0%, #132b4a 50%, #0D1B2A 100%);
  padding: 112px 0;
}

.home-cta__bg-orb {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
}

.home-cta__bg-orb--top {
  top: 0; right: 0;
  width: 500px; height: 500px;
  background: rgba(201, 146, 42, 0.10);
  filter: blur(120px);
}

.home-cta__bg-orb--bottom {
  bottom: 0; left: 0;
  width: 400px; height: 400px;
  background: rgba(26, 48, 80, 0.50);
  filter: blur(100px);
}

.home-cta__inner {
  position: relative;
  max-width: 720px;
  margin: 0 auto;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.home-cta__inner h2 {
  color: #ffffff;
  font-size: clamp(2rem, 4vw, 3.2rem);
  font-weight: 800;
  letter-spacing: -0.025em;
  line-height: 1.1;
  margin: 0;
}

.home-cta__inner h2 em {
  font-style: normal;
  color: var(--accent);
}

.home-cta__sub {
  color: rgba(255, 255, 255, 0.65);
  font-size: 17px;
  line-height: 1.65;
  max-width: 480px;
  margin: 0 0 24px;
}

.home-cta__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 12px;
}
```

- [ ] **Step 3: Verify, commit**
```bash
git add template-parts/home/cta.php assets/css/pages.css
git commit -m "feat: redesign Final CTA — dark gradient, gold orbs, 3-button layout per Figma"
```

---

## Task 11: Portfolio Page — Update Categories and Admin Panel

**Files:**
- Modify: `templates/page-portfolio.php`
- Modify: `assets/css/pages.css` (or `pages-public.css`)

Figma changes the portfolio categories from:
- `Real Estate Photography, Cinematic Video, Drone, Social Media / Reels, Business Branding`

To:
- `All, Photography, Videography, Drone, Social Media, Business`

Also updates the masonry card overlay style to match Figma.

- [ ] **Step 1: Update filter categories in `templates/page-portfolio.php`**

Find the `$default_categories` array and the filter buttons. Update:
```php
// ── Update default_categories array ──
$default_categories = [
  'Photography',   // 0
  'Photography',   // 1
  'Photography',   // 2
  'Photography',   // 3
  'Photography',   // 4
  'Photography',   // 5
  'Drone',         // 6
  'Drone',         // 7
  'Drone',         // 8
  'Drone',         // 9
  'Videography',   // 10
  'Videography',   // 11
  'Videography',   // 12
  'Videography',   // 13
  'Social Media',  // 14
  'Social Media',  // 15
  'Social Media',  // 16
  'Business',      // 17
  'Business',      // 18
  'Business',      // 19
];
```

Find the `var CATEGORIES` array in the inline JS and update:
```js
var CATEGORIES = [
  'Photography',
  'Videography',
  'Drone',
  'Social Media',
  'Business'
];
```

Find the filter bar HTML and update:
```html
<div class="port-filters js-reveal" role="group" aria-label="Filter portfolio by category" id="portFilters">
  <button class="port-filter port-filter--active" data-filter="All" type="button">All</button>
  <button class="port-filter" data-filter="Photography" type="button">Photography</button>
  <button class="port-filter" data-filter="Videography" type="button">Videography</button>
  <button class="port-filter" data-filter="Drone" type="button">Drone</button>
  <button class="port-filter" data-filter="Social Media" type="button">Social Media</button>
  <button class="port-filter" data-filter="Business" type="button">Business</button>
</div>
```

- [ ] **Step 2: Remove the plain-text password from the admin trigger JS**

The current portfolio has a hardcoded `ADMIN_PASS = 'showcase2024'` in plain JavaScript that is visible to all visitors. Instead, gate the admin trigger visibility using PHP (only render for logged-in admins):

Find the admin trigger section in `templates/page-portfolio.php` and wrap in PHP:
```php
<?php if (current_user_can('manage_options')): ?>
<div style="text-align:center;padding:12px 0 4px;">
  <button id="portAdminTrigger" type="button" style="background:none;border:none;color:#9ca3af;font-size:0.75rem;cursor:pointer;font-family:inherit;padding:4px 8px;">&#9998; Edit Portfolio</button>
</div>
<?php endif; ?>
```

Remove the password prompt from the JS:
```js
// Find this block and replace:
if (adminTrigger) {
  adminTrigger.addEventListener('click', function () {
    // REMOVE the password prompt — admin visibility is now PHP-gated
    renderAdminList();
    adminPanel.classList.add('is-open');
    adminPanel.setAttribute('aria-hidden', 'false');
  });
}
```

- [ ] **Step 3: Update portfolio card CSS in `assets/css/pages.css`**

Update `.port-card` overlay to match Figma gradient:
```css
.port-card__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(10, 21, 33, 0.88) 0%, rgba(10, 21, 33, 0.30) 50%, transparent 100%);
  opacity: 0;
  transition: opacity var(--duration-base) ease;
  display: flex;
  align-items: flex-end;
  padding: 20px;
  border-radius: inherit;
}

.port-card:hover .port-card__overlay { opacity: 1; }
```

- [ ] **Step 4: Verify, commit**
```bash
git add templates/page-portfolio.php assets/css/pages.css
git commit -m "feat: update portfolio categories, secure admin trigger with PHP capability check"
```

---

## Task 12: About Page — Add Story, Beliefs, Partners Sections

**Files:**
- Modify: `templates/page-about.php`
- Create: `inc/about-meta.php`
- Modify: `functions.php`

Figma About page has: Hero (dark navy), Our Story (light), Partners (2 partners from Modern Florida Home Staging), Beliefs, Approach steps, CTA.

- [ ] **Step 1: Create `inc/about-meta.php`** (admin media + text fields for About page)

```php
<?php
if (!defined('ABSPATH')) exit;

/**
 * About Page meta boxes — editable text and images via WP Admin.
 */

function slm_about_page_id(): int {
  $ids = get_posts([
    'post_type'      => 'page',
    'post_status'    => ['publish', 'draft'],
    'posts_per_page' => 1,
    'fields'         => 'ids',
    'no_found_rows'  => true,
    'meta_key'       => '_wp_page_template',
    'meta_value'     => 'templates/page-about.php',
  ]);
  return !empty($ids) ? (int) $ids[0] : 0;
}

add_action('add_meta_boxes_page', function (WP_Post $post): void {
  if (get_page_template_slug($post->ID) !== 'templates/page-about.php') return;
  add_meta_box('slm_about_hero',    'About — Hero',          'slm_render_about_hero_box',    'page', 'normal', 'high');
  add_meta_box('slm_about_story',   'About — Our Story',     'slm_render_about_story_box',   'page', 'normal', 'high');
  add_meta_box('slm_about_partner1','About — Partner 1',     'slm_render_about_partner1_box','page', 'normal', 'high');
  add_meta_box('slm_about_partner2','About — Partner 2',     'slm_render_about_partner2_box','page', 'normal', 'high');
});

function slm_about_text_row(int $pid, string $key, string $label, bool $textarea = false): void {
  $val = (string) get_post_meta($pid, $key, true);
  echo '<div style="margin-bottom:14px;">';
  echo '<label for="' . esc_attr($key) . '" style="display:block;font-weight:600;margin-bottom:4px;">' . esc_html($label) . '</label>';
  if ($textarea) {
    echo '<textarea id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" rows="4" style="width:100%;">' . esc_textarea($val) . '</textarea>';
  } else {
    echo '<input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" style="width:100%;" />';
  }
  echo '</div>';
}

function slm_about_image_row(int $pid, string $key, string $label): void {
  $val = (int) get_post_meta($pid, $key, true);
  echo '<div style="margin-bottom:14px;">';
  echo '<label style="display:block;font-weight:600;margin-bottom:4px;">' . esc_html($label) . '</label>';
  echo '<input type="hidden" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" />';
  if ($val) {
    echo wp_get_attachment_image($val, 'thumbnail', false, ['style' => 'width:80px;height:80px;object-fit:cover;border-radius:8px;display:block;margin-bottom:8px;']);
  }
  echo '<button type="button" class="button slm-img-pick" data-target="' . esc_attr($key) . '">Select Image</button>';
  if ($val) echo ' <button type="button" class="button slm-img-clear" data-target="' . esc_attr($key) . '">Remove</button>';
  echo '</div>';
}

function slm_render_about_hero_box(WP_Post $post): void {
  wp_nonce_field('slm_about_save', 'slm_about_nonce');
  slm_about_text_row($post->ID, 'about_hero_eyebrow',  'Eyebrow');
  slm_about_text_row($post->ID, 'about_hero_headline', 'Headline');
  slm_about_text_row($post->ID, 'about_hero_sub',      'Subheadline', true);
}

function slm_render_about_story_box(WP_Post $post): void {
  slm_about_text_row($post->ID, 'about_story_eyebrow',  'Eyebrow');
  slm_about_text_row($post->ID, 'about_story_headline', 'Headline');
  slm_about_text_row($post->ID, 'about_story_body_1',   'Body Paragraph 1', true);
  slm_about_text_row($post->ID, 'about_story_body_2',   'Body Paragraph 2', true);
}

function slm_render_about_partner_box(WP_Post $post, int $n): void {
  slm_about_text_row($post->ID, "about_partner_{$n}_name",    'Name');
  slm_about_text_row($post->ID, "about_partner_{$n}_role",    'Role/Title');
  slm_about_text_row($post->ID, "about_partner_{$n}_company", 'Company');
  slm_about_text_row($post->ID, "about_partner_{$n}_bio",     'Bio', true);
  slm_about_text_row($post->ID, "about_partner_{$n}_website", 'Website URL');
  slm_about_image_row($post->ID, "about_partner_{$n}_photo",  'Profile Photo');
}

function slm_render_about_partner1_box(WP_Post $post): void {
  wp_enqueue_media();
  slm_render_about_partner_box($post, 1);
}
function slm_render_about_partner2_box(WP_Post $post): void {
  slm_render_about_partner_box($post, 2);
}

add_action('save_post', function (int $post_id): void {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_page', $post_id)) return;
  $nonce = (string) ($_POST['slm_about_nonce'] ?? '');
  if (!wp_verify_nonce($nonce, 'slm_about_save')) return;

  $text_fields = [
    'about_hero_eyebrow', 'about_hero_headline', 'about_hero_sub',
    'about_story_eyebrow', 'about_story_headline', 'about_story_body_1', 'about_story_body_2',
    'about_partner_1_name', 'about_partner_1_role', 'about_partner_1_company',
    'about_partner_1_bio', 'about_partner_1_website',
    'about_partner_2_name', 'about_partner_2_role', 'about_partner_2_company',
    'about_partner_2_bio', 'about_partner_2_website',
  ];
  foreach ($text_fields as $f) {
    if (isset($_POST[$f])) {
      update_post_meta($post_id, $f, sanitize_textarea_field($_POST[$f]));
    }
  }
  foreach (['about_partner_1_photo', 'about_partner_2_photo'] as $f) {
    if (isset($_POST[$f])) {
      update_post_meta($post_id, $f, (int) $_POST[$f]);
    }
  }
});

// Enqueue WP media + image picker JS on About page edit screen
add_action('admin_enqueue_scripts', function (string $hook): void {
  if ($hook !== 'post.php' && $hook !== 'post-new.php') return;
  $post_id = (int) ($_GET['post'] ?? 0);
  if ($post_id && get_page_template_slug($post_id) !== 'templates/page-about.php') return;
  wp_enqueue_media();
  // Inline image picker script (reuse pattern from homepage meta)
  wp_add_inline_script('jquery', "
    jQuery(function(\$){
      \$(document).on('click', '.slm-img-pick', function(){
        var target = \$(this).data('target');
        var frame = wp.media({ title:'Select Image', button:{text:'Use Image'}, multiple:false });
        frame.on('select', function(){
          var att = frame.state().get('selection').first().toJSON();
          \$('#'+target).val(att.id);
        });
        frame.open();
      });
      \$(document).on('click', '.slm-img-clear', function(){
        \$('#'+\$(this).data('target')).val('');
      });
    });
  ");
});
```

- [ ] **Step 2: Add `require_once` to `functions.php`**

In `functions.php`, after the existing requires, add:
```php
require_once __DIR__ . '/inc/about-meta.php';
```

- [ ] **Step 3: Update `templates/page-about.php` with new sections**

The About page should have these sections (many already exist in the file — update/add as needed):

```php
<?php
/**
 * Template Name: About
 */
if (!defined('ABSPATH')) exit;
get_header();

$pid         = get_the_ID();
$cta_url     = is_user_logged_in()
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());

// Hero
$hero_eyebrow  = get_post_meta($pid, 'about_hero_eyebrow', true)  ?: 'About Us';
$hero_headline = get_post_meta($pid, 'about_hero_headline', true) ?: 'Real Estate & Brand Media in North Florida That Helps You Stand Out';
$hero_sub      = get_post_meta($pid, 'about_hero_sub', true)      ?: 'At Showcase Listings Media, we do more than capture photos — we create content that helps real estate agents and businesses across Jacksonville and North Florida stand out, attract attention, and grow.';

// Story
$story_headline = get_post_meta($pid, 'about_story_headline', true) ?: 'Built Different. On Purpose.';
$story_body_1   = get_post_meta($pid, 'about_story_body_1', true)   ?: 'Founded on the belief that every listing and every brand is different, our approach is intentional. We don\'t rely on cookie-cutter templates or rushed edits. Every shoot is designed to highlight what makes your property or business unique.';
$story_body_2   = get_post_meta($pid, 'about_story_body_2', true)   ?: 'We\'re a locally based team in Jacksonville, Florida — and we care deeply about the results our clients get. That means fast communication, on-time delivery, and content that actually performs.';

// Partners
$partners = [];
for ($n = 1; $n <= 2; $n++) {
  $name    = get_post_meta($pid, "about_partner_{$n}_name", true);
  $role    = get_post_meta($pid, "about_partner_{$n}_role", true);
  $company = get_post_meta($pid, "about_partner_{$n}_company", true);
  $bio     = get_post_meta($pid, "about_partner_{$n}_bio", true);
  $website = get_post_meta($pid, "about_partner_{$n}_website", true);
  $photo   = (int) get_post_meta($pid, "about_partner_{$n}_photo", true);

  if ($name) {
    $partners[] = compact('name', 'role', 'company', 'bio', 'website', 'photo');
  }
}

// Default partners if none set
if (empty($partners)) {
  $partners = [
    ['name' => 'Reesa Storely', 'role' => 'Managing Member & Certified Staging Expert', 'company' => 'Modern Florida Home Staging', 'bio' => 'Reesa is a Managing Member of Modern Florida Home Staging LLC and a certified staging expert. With years of experience in interior design and a refined eye for detail, she brings sophistication and elegance to every project — transforming spaces to captivate buyers and maximize property value.', 'website' => 'https://www.modernfloridahomestaging.com/', 'photo' => 0],
    ['name' => 'Danielle Ramos', 'role' => 'Managing Member & Social Media Manager', 'company' => 'Modern Florida Home Staging', 'bio' => 'Danielle Ramos is a Managing Member of Modern Florida Home Staging LLC, serving as the team\'s social media content creator and lead stager. She brings a fresh, modern perspective to every staging project.', 'website' => 'https://www.modernfloridahomestaging.com/', 'photo' => 0],
  ];
}

$beliefs = [
  ['label' => 'Stops people from scrolling'],
  ['label' => 'Creates a strong first impression'],
  ['label' => 'Positions you above your competition'],
];

$approach = [
  ['num' => '01', 'text' => 'Every project is tailored — no one-size-fits-all'],
  ['num' => '02', 'text' => 'Fast, reliable turnaround times'],
  ['num' => '03', 'text' => 'Clear communication from start to finish'],
  ['num' => '04', 'text' => 'A focus on both aesthetic and performance'],
];
?>

<main id="main-content">
  <?php slm_edit_page_button($pid); ?>

  <!-- Hero -->
  <section class="about-hero">
    <div class="about-hero__top-line" aria-hidden="true"></div>
    <div class="about-hero__orb" aria-hidden="true"></div>
    <div class="container">
      <div class="about-hero__content js-reveal">
        <span class="section-eyebrow"><?php echo esc_html($hero_eyebrow); ?></span>
        <h1><?php echo esc_html($hero_headline); ?></h1>
        <p><?php echo esc_html($hero_sub); ?></p>
      </div>
    </div>
  </section>

  <!-- Our Story -->
  <section class="about-story">
    <div class="container">
      <div class="about-story__inner js-reveal">
        <span class="section-eyebrow">Our Story</span>
        <h2><?php echo esc_html($story_headline); ?></h2>
        <p><?php echo esc_html($story_body_1); ?></p>
        <p><?php echo esc_html($story_body_2); ?></p>
      </div>
    </div>
  </section>

  <!-- What We Believe -->
  <section class="about-beliefs">
    <div class="container">
      <div class="about-beliefs__inner js-reveal">
        <h2>We believe great media:</h2>
        <ul class="about-beliefs__list">
          <?php foreach ($beliefs as $belief): ?>
            <li>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
              <?php echo esc_html($belief['label']); ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </section>

  <!-- Partners -->
  <?php if (!empty($partners)): ?>
  <section class="about-partners">
    <div class="container">
      <div class="about-partners__header js-reveal">
        <span class="section-eyebrow">Our Partners</span>
        <h2>Better Together</h2>
      </div>
      <div class="about-partners__grid">
        <?php foreach ($partners as $i => $partner): ?>
          <div class="about-partners__card js-reveal" style="transition-delay: <?php echo $i * 0.15; ?>s">
            <div class="about-partners__card-head">
              <?php if ($partner['photo']): ?>
                <?php echo wp_get_attachment_image($partner['photo'], 'thumbnail', false, ['class' => 'about-partners__avatar']); ?>
              <?php else: ?>
                <div class="about-partners__avatar-fallback" aria-hidden="true">
                  <?php echo esc_html(mb_substr($partner['name'], 0, 2)); ?>
                </div>
              <?php endif; ?>
              <div>
                <p class="about-partners__name"><?php echo esc_html($partner['name']); ?></p>
                <p class="about-partners__role"><?php echo esc_html($partner['role']); ?></p>
                <p class="about-partners__company"><?php echo esc_html($partner['company']); ?></p>
              </div>
            </div>
            <p class="about-partners__bio"><?php echo esc_html($partner['bio']); ?></p>
            <?php if ($partner['website']): ?>
              <a class="about-partners__link" href="<?php echo esc_url($partner['website']); ?>" target="_blank" rel="noopener noreferrer">
                Visit Website
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              </a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Our Approach -->
  <section class="about-approach">
    <div class="container">
      <div class="about-approach__header js-reveal">
        <span class="section-eyebrow">Our Approach</span>
        <h2>How We Work</h2>
      </div>
      <div class="about-approach__steps">
        <?php foreach ($approach as $i => $step): ?>
          <div class="about-approach__step js-reveal" style="transition-delay: <?php echo $i * 0.10; ?>s">
            <span class="about-approach__num" aria-hidden="true"><?php echo esc_html($step['num']); ?></span>
            <p><?php echo esc_html($step['text']); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="svc-final-cta js-reveal" aria-label="Book a service">
    <div class="container">
      <h2>Ready to Create Content That Stands Out?</h2>
      <p>Let's build something together.</p>
      <div class="svc-final-cta__btns">
        <a class="btn svc-final-cta__primary" href="<?php echo esc_url($cta_url); ?>">Book a Shoot</a>
        <a class="btn btn--ghost svc-final-cta__secondary" href="tel:+19042945809">Call (904) 294-5809</a>
        <a class="btn btn--ghost svc-final-cta__secondary" href="<?php echo esc_url(home_url('/contact/')); ?>">Send a Message</a>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
```

- [ ] **Step 4: Add About page CSS in `assets/css/pages.css`**

```css
/* ─── ABOUT PAGE ─────────────────────────────────────────────── */
.about-hero {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #0D1B2A 0%, #162947 100%);
  padding: 128px 0 80px;
}

.about-hero__top-line {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(201, 146, 42, 0.60), transparent);
}

.about-hero__orb {
  position: absolute;
  top: 20%; right: 0;
  width: 500px; height: 500px;
  background: rgba(201, 146, 42, 0.05);
  border-radius: 50%;
  filter: blur(100px);
  pointer-events: none;
}

.about-hero__content { max-width: 760px; }
.about-hero__content h1 { color: #ffffff; }
.about-hero__content p { color: rgba(255, 255, 255, 0.72); font-size: 17px; line-height: 1.65; margin: 0; }

.about-story {
  background: var(--background);
  padding: 80px 0;
}

.about-story__inner {
  max-width: 680px;
  margin: 0 auto;
  text-align: center;
}

.about-story__inner h2 { margin-bottom: 20px; }
.about-story__inner p { color: var(--muted-foreground); font-size: 16px; line-height: 1.7; margin-bottom: 16px; }

.about-beliefs {
  background: #ffffff;
  padding: 60px 0;
}

.about-beliefs__inner {
  max-width: 600px;
  margin: 0 auto;
}

.about-beliefs__inner h2 { margin-bottom: 24px; }

.about-beliefs__list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.about-beliefs__list li {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 16px;
  font-weight: 500;
  color: var(--foreground);
}

.about-beliefs__list svg { color: var(--accent); flex-shrink: 0; }

.about-partners {
  background: var(--background);
  padding: 80px 0;
}

.about-partners__header {
  text-align: center;
  margin-bottom: 48px;
}

.about-partners__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

@media (max-width: 680px) { .about-partners__grid { grid-template-columns: 1fr; } }

.about-partners__card {
  padding: 28px;
  border-radius: 20px;
  background: #ffffff;
  border: 1px solid rgba(15, 39, 68, 0.06);
  box-shadow: 0 2px 12px rgba(11, 25, 44, 0.04);
}

.about-partners__card-head {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  margin-bottom: 16px;
}

.about-partners__avatar {
  width: 56px; height: 56px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.about-partners__avatar-fallback {
  width: 56px; height: 56px;
  border-radius: 50%;
  background: var(--primary);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 16px;
  flex-shrink: 0;
}

.about-partners__name { font-weight: 700; font-size: 15px; color: var(--primary-strong); margin: 0 0 2px; }
.about-partners__role { font-size: 13px; color: var(--muted-foreground); margin: 0 0 2px; }
.about-partners__company { font-size: 13px; font-weight: 600; color: var(--accent); margin: 0; }
.about-partners__bio { color: var(--muted-foreground); font-size: 14.5px; line-height: 1.65; margin: 0 0 16px; }

.about-partners__link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--accent);
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: gap var(--duration-fast) ease;
}

.about-partners__link:hover { gap: 9px; }

.about-approach {
  background: #ffffff;
  padding: 80px 0;
}

.about-approach__header {
  text-align: center;
  margin-bottom: 48px;
}

.about-approach__steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  max-width: 900px;
  margin: 0 auto;
}

@media (max-width: 760px) { .about-approach__steps { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .about-approach__steps { grid-template-columns: 1fr; } }

.about-approach__step {
  padding: 24px;
  border-radius: 16px;
  background: var(--background);
  border: 1px solid rgba(15, 39, 68, 0.06);
}

.about-approach__num {
  display: block;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 32px;
  line-height: 1;
  color: var(--accent);
  opacity: 0.30;
  margin-bottom: 12px;
}

.about-approach__step p {
  font-size: 14.5px;
  font-weight: 500;
  color: var(--foreground);
  margin: 0;
  line-height: 1.5;
}
```

- [ ] **Step 5: Verify, commit**
```bash
git add inc/about-meta.php functions.php templates/page-about.php assets/css/pages.css
git commit -m "feat: rebuild About page with Story, Beliefs, Partners sections and WP admin meta boxes"
```

---

## Task 13: Contact Page — Service Selector + 2-Column Layout

**Files:**
- Modify: `templates/page-contact.php`
- Modify: `assets/css/pages.css`

Figma shows a 2-column layout: left has contact info cards (phone, email, location, response time), right has a form with name, email, phone, service dropdown, message.

- [ ] **Step 1: Rewrite `templates/page-contact.php`**

Full template — copy this as the complete file content:

```php
<?php
/**
 * Template Name: Contact
 */
if (!defined('ABSPATH')) exit;
get_header();

$pid = get_the_ID();
$contact_items = [
  [
    'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.56a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
    'label' => 'Phone',
    'value' => '(904) 294-5809',
    'href'  => 'tel:+19042945809',
  ],
  [
    'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
    'label' => 'Email',
    'value' => 'Showcaselistingsmedia@gmail.com',
    'href'  => 'mailto:Showcaselistingsmedia@gmail.com',
  ],
  [
    'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
    'label' => 'Service Area',
    'value' => 'Jacksonville & North Florida',
    'href'  => null,
  ],
  [
    'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    'label' => 'Response Time',
    'value' => 'Within 24 hours',
    'href'  => null,
  ],
];

$services_list = [
  'Real Estate Photography',
  'Real Estate Videography',
  'Drone Photography',
  'Drone Videography',
  'Virtual Tours',
  'Floor Plans',
  'Twilight Photography',
  'Zillow Showcase',
  'Social Media Packages',
  'Social Media Assistance',
  'Other / Not sure',
];
?>

<main id="main-content">
  <?php slm_edit_page_button($pid); ?>

  <!-- Hero -->
  <section class="contact-hero">
    <div class="contact-hero__top-line" aria-hidden="true"></div>
    <div class="container">
      <div class="js-reveal">
        <span class="section-eyebrow">Get In Touch</span>
        <h1>Let's Work Together</h1>
        <p>Ready to book a shoot, ask a question, or get a custom quote? We'd love to hear from you.</p>
      </div>
    </div>
  </section>

  <!-- Body -->
  <section class="contact-body">
    <div class="container">
      <div class="contact-body__grid">

        <!-- Left: Info -->
        <div class="js-reveal">
          <h2 style="margin-bottom:24px;">Reach Out Directly</h2>
          <div class="contact-info__cards">
            <?php foreach ($contact_items as $item): ?>
              <div class="contact-info__card">
                <div class="contact-info__card-icon"><?php echo $item['icon']; ?></div>
                <div>
                  <p class="contact-info__card-label"><?php echo esc_html($item['label']); ?></p>
                  <?php if ($item['href']): ?>
                    <a href="<?php echo esc_url($item['href']); ?>" class="contact-info__card-value"><?php echo esc_html($item['value']); ?></a>
                  <?php else: ?>
                    <p class="contact-info__card-value"><?php echo esc_html($item['value']); ?></p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Right: Form -->
        <div class="js-reveal" style="transition-delay:.15s">
          <h2 style="margin-bottom:24px;">Send Us a Message</h2>
          <form class="contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('slm_contact_submit', 'slm_contact_nonce'); ?>
            <input type="hidden" name="action" value="slm_contact_submit">

            <div class="contact-form__row">
              <div>
                <label for="cf-name">Your Name</label>
                <input class="contact-form__input" type="text" id="cf-name" name="contact_name" required placeholder="Jane Smith">
              </div>
              <div>
                <label for="cf-email">Email Address</label>
                <input class="contact-form__input" type="email" id="cf-email" name="contact_email" required placeholder="jane@example.com">
              </div>
            </div>

            <div>
              <label for="cf-phone">Phone Number</label>
              <input class="contact-form__input" type="tel" id="cf-phone" name="contact_phone" placeholder="(904) 555-0123">
            </div>

            <div>
              <label for="cf-service">Service Interested In</label>
              <select class="contact-form__select" id="cf-service" name="contact_service">
                <option value="">Select a service…</option>
                <?php foreach ($services_list as $svc): ?>
                  <option value="<?php echo esc_attr($svc); ?>"><?php echo esc_html($svc); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label for="cf-message">Message</label>
              <textarea class="contact-form__textarea" id="cf-message" name="contact_message" rows="5" placeholder="Tell us about your project…"></textarea>
            </div>

            <button class="btn" type="submit">Send Message</button>
          </form>
        </div>

      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
```

Also add the form handler to `functions.php` (or a new `inc/contact-form.php` required from `functions.php`):
```php
add_action('admin_post_nopriv_slm_contact_submit', 'slm_handle_contact_form');
add_action('admin_post_slm_contact_submit', 'slm_handle_contact_form');

function slm_handle_contact_form(): void {
  if (!wp_verify_nonce($_POST['slm_contact_nonce'] ?? '', 'slm_contact_submit')) {
    wp_die('Invalid request.');
  }
  $name    = sanitize_text_field($_POST['contact_name'] ?? '');
  $email   = sanitize_email($_POST['contact_email'] ?? '');
  $phone   = sanitize_text_field($_POST['contact_phone'] ?? '');
  $service = sanitize_text_field($_POST['contact_service'] ?? '');
  $message = sanitize_textarea_field($_POST['contact_message'] ?? '');

  $to      = get_option('admin_email');
  $subject = 'New Contact Form Submission — ' . get_bloginfo('name');
  $body    = "Name: $name\nEmail: $email\nPhone: $phone\nService: $service\n\nMessage:\n$message";
  $headers = ['Content-Type: text/plain; charset=UTF-8', "Reply-To: $name <$email>"];

  wp_mail($to, $subject, $body, $headers);
  wp_safe_redirect(add_query_arg('sent', '1', home_url('/contact/')));
  exit;
}
```

- [ ] **Step 2: Add Contact page CSS in `assets/css/pages.css`**

```css
/* ─── CONTACT PAGE ───────────────────────────────────────────── */
.contact-hero {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #0D1B2A 0%, #162947 100%);
  padding: 128px 0 64px;
  text-align: center;
}

.contact-hero__top-line {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(201, 146, 42, 0.60), transparent);
}

.contact-hero h1 { color: #ffffff; }
.contact-hero p  { color: rgba(255, 255, 255, 0.70); font-size: 17px; max-width: 520px; margin: 0 auto; }

.contact-body {
  background: #ffffff;
  padding: 80px 0;
}

.contact-body__grid {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 56px;
  align-items: start;
}

@media (max-width: 860px) {
  .contact-body__grid { grid-template-columns: 1fr; }
}

.contact-info__cards {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.contact-info__card {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 18px;
  border-radius: 14px;
  background: #f9fbff;
  border: 1px solid rgba(15, 39, 68, 0.06);
}

.contact-info__card-icon {
  width: 40px; height: 40px;
  border-radius: 12px;
  background: rgba(201, 146, 42, 0.10);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--accent);
  flex-shrink: 0;
}

.contact-info__card-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--muted-foreground);
  margin: 0 0 2px;
}

.contact-info__card-value {
  font-size: 15px;
  font-weight: 600;
  color: var(--primary-strong);
  margin: 0;
  text-decoration: none;
}

a.contact-info__card-value:hover { color: var(--accent); }

.contact-form__select,
.contact-form__input,
.contact-form__textarea {
  width: 100%;
  padding: 12px 16px;
  border-radius: 12px;
  border: 1px solid rgba(15, 39, 68, 0.13);
  background: var(--input-background);
  font-family: var(--font-sans);
  font-size: 15px;
  color: var(--foreground);
  outline: none;
  transition: border-color var(--duration-base) ease, box-shadow var(--duration-base) ease;
}

.contact-form__select:focus,
.contact-form__input:focus,
.contact-form__textarea:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(201, 146, 42, 0.12);
}
```

- [ ] **Step 3: Verify, commit**
```bash
git add templates/page-contact.php assets/css/pages.css
git commit -m "feat: update Contact page — 2-column layout, service dropdown, contact info cards per Figma"
```

---

## Task 14: Services Page — Grouped Layout (Photo / Video / Drone / Add-Ons)

**Files:**
- Modify: `templates/page-services.php`
- Modify: `assets/css/pages.css`

Figma groups services into: Photo, Video, Drone, Add-Ons — each with image, title, tagline, description, highlights, price, and "Learn More" link.

The existing `inc/services-meta.php` already handles the hero section. The service card images should come from WP media fields. For each service section, add image picker fields to the existing Services meta box.

The services page uses **named meta keys** for images. Use this exact field-name scheme so the meta box wiring and the template always agree:

| Meta key | Service |
|---|---|
| `svc_photo_re_image_id` | Real Estate Photography |
| `svc_photo_twilight_image_id` | Twilight Photography |
| `svc_video_re_image_id` | Cinematic Listing Videos |
| `svc_video_social_image_id` | Social Media Reels |
| `svc_drone_photo_image_id` | Drone Photography |
| `svc_drone_video_image_id` | Drone Videography |
| `svc_addon_virtual_image_id` | Virtual Tours |
| `svc_addon_floor_image_id` | Floor Plans |
| `svc_addon_zillow_image_id` | Zillow Showcase |

- [ ] **Step 1: Add image picker fields to the Services meta box in `inc/services-meta.php`**

In `slm_render_svc_core_meta_box`, call `wp_enqueue_media()` then add one picker row per image field using this helper pattern:
```php
function slm_svc_image_row(int $post_id, string $key, string $label): void {
  $val = (int) get_post_meta($post_id, $key, true);
  echo '<div style="margin-bottom:14px;">';
  echo '<label style="display:block;font-weight:600;margin-bottom:4px;">' . esc_html($label) . '</label>';
  echo '<input type="hidden" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" />';
  if ($val) echo wp_get_attachment_image($val, 'thumbnail', false, ['style' => 'width:72px;height:72px;object-fit:cover;border-radius:8px;margin-bottom:6px;display:block;']);
  echo '<button type="button" class="button slm-svc-img-pick" data-target="' . esc_attr($key) . '">Select Image</button>';
  if ($val) echo ' <button type="button" class="button slm-svc-img-clear" data-target="' . esc_attr($key) . '">Remove</button>';
  echo '</div>';
}
```

Then inside `slm_render_svc_core_meta_box`, add after the existing service text rows:
```php
wp_enqueue_media();
echo '<hr style="margin:20px 0;"><h4>Service Section Images</h4>';
slm_svc_image_row($post->ID, 'svc_photo_re_image_id',       'RE Photography — Section Image');
slm_svc_image_row($post->ID, 'svc_photo_twilight_image_id',  'Twilight Photography — Section Image');
slm_svc_image_row($post->ID, 'svc_video_re_image_id',        'Listing Videos — Section Image');
slm_svc_image_row($post->ID, 'svc_video_social_image_id',    'Social Media Reels — Section Image');
slm_svc_image_row($post->ID, 'svc_drone_photo_image_id',     'Drone Photography — Section Image');
slm_svc_image_row($post->ID, 'svc_drone_video_image_id',     'Drone Videography — Section Image');
slm_svc_image_row($post->ID, 'svc_addon_virtual_image_id',   'Virtual Tours — Section Image');
slm_svc_image_row($post->ID, 'svc_addon_floor_image_id',     'Floor Plans — Section Image');
slm_svc_image_row($post->ID, 'svc_addon_zillow_image_id',    'Zillow Showcase — Section Image');
```

Add the `wp.media` JS inline in `slm_render_svc_core_meta_box`:
```php
wp_add_inline_script('jquery', "
  jQuery(function(\$){
    \$(document).on('click', '.slm-svc-img-pick', function(){
      var target = \$(this).data('target');
      wp.media({ title:'Select Image', button:{text:'Use Image'}, multiple:false })
        .on('select', function(){ var att = this.state().get('selection').first().toJSON(); \$('#'+target).val(att.id); })
        .open();
    });
    \$(document).on('click', '.slm-svc-img-clear', function(){ \$('#'+\$(this).data('target')).val(''); });
  });
");
```

In the `save_post` hook for services (add to existing `slm_svc_save` nonce block):
```php
$img_keys = [
  'svc_photo_re_image_id','svc_photo_twilight_image_id',
  'svc_video_re_image_id','svc_video_social_image_id',
  'svc_drone_photo_image_id','svc_drone_video_image_id',
  'svc_addon_virtual_image_id','svc_addon_floor_image_id','svc_addon_zillow_image_id',
];
foreach ($img_keys as $k) {
  if (isset($_POST[$k])) update_post_meta($post_id, $k, (int) $_POST[$k]);
}
```

- [ ] **Step 2: Update `templates/page-services.php` with grouped layout**

Group services into 4 sections. Use the complete service definition array below. Each card renders its image from the named meta key (`svc_*_image_id`), falling back gracefully if no image is set.

```php
<?php
/**
 * Template Name: Services
 */
if (!defined('ABSPATH')) exit;
get_header();

$pid = get_the_ID();

// ── Hero meta (editable from existing Services meta boxes) ───────────
$hero_eyebrow  = get_post_meta($pid, 'svc_hero_eyebrow', true)    ?: 'Jacksonville & North Florida';
$hero_headline = get_post_meta($pid, 'svc_hero_headline', true)   ?: 'Professional Real Estate & Brand Media Services';
$hero_sub      = get_post_meta($pid, 'svc_hero_subheadline', true) ?: 'High-quality photo, video, and content built for agents and businesses that want to stand out and drive results.';

// ── Service groups ───────────────────────────────────────────────────
$groups = [
  [
    'eyebrow' => 'Photography',
    'title'   => 'Photo Services',
    'services' => [
      [
        'img_key'    => 'svc_photo_re_image_id',
        'title'      => 'Real Estate Photography',
        'tagline'    => 'MLS-ready photos that stop the scroll and attract buyers.',
        'desc'       => 'High-quality interior and exterior photography with HDR processing, professional lighting, and strategic angles. Delivered MLS-ready within 24 hours.',
        'price'      => 'Starting at $145',
        'highlights' => ['HDR interior & exterior', 'Blue-sky guarantee', '24-hr delivery', 'MLS-ready files'],
        'url'        => home_url('/services/real-estate-photography/'),
      ],
      [
        'img_key'    => 'svc_photo_twilight_image_id',
        'title'      => 'Twilight Photography',
        'tagline'    => 'Make your listing look like a magazine cover.',
        'desc'       => 'Dramatic dusk and golden-hour photography that captures homes in their most impressive light — warm glows, vibrant skies, and premium curb appeal.',
        'price'      => 'Starting at $85',
        'highlights' => ['Golden hour timing', 'Interior lights on', 'Dramatic sky HDR', 'Real or AI-converted'],
        'url'        => home_url('/services/twilight-photography/'),
      ],
    ],
  ],
  [
    'eyebrow' => 'Video',
    'title'   => 'Video Services',
    'services' => [
      [
        'img_key'    => 'svc_video_re_image_id',
        'title'      => 'Cinematic Listing Videos',
        'tagline'    => 'Bring listings to life with smooth, modern walkthroughs.',
        'desc'       => 'Professional listing videos crafted for both MLS and social media — giving your listing a compelling story that photos alone can\'t tell.',
        'price'      => 'Starting at $199',
        'highlights' => ['Gimbal-stabilized footage', 'Professional color grading', 'MLS & social formats', '48-hr delivery'],
        'url'        => home_url('/services/real-estate-videography/'),
      ],
      [
        'img_key'    => 'svc_video_social_image_id',
        'title'      => 'Social Media Reels & Content',
        'tagline'    => 'Short-form video that grows your presence and drives leads.',
        'desc'       => 'Custom Reels and Shorts designed to stop the scroll on Instagram, TikTok, and Facebook — perfect for agents building a recognizable brand.',
        'price'      => 'Starting at $299/mo',
        'highlights' => ['Branded Reels & Shorts', 'Monthly content calendar', 'Caption & strategy', 'Multi-platform formats'],
        'url'        => home_url('/services/social-media-packages/'),
      ],
    ],
  ],
  [
    'eyebrow' => 'Drone',
    'title'   => 'Aerial Services',
    'services' => [
      [
        'img_key'    => 'svc_drone_photo_image_id',
        'title'      => 'Drone Photography',
        'tagline'    => 'Aerial perspective that impresses buyers and wins listings.',
        'desc'       => 'FAA-certified aerial photography that captures the full scale of a property and its surroundings — especially effective for larger lots, waterfront, and luxury homes.',
        'price'      => 'Starting at $75 (add-on)',
        'highlights' => ['FAA-certified pilot', '4K aerial stills', 'Same-day scheduling', 'MLS-ready files'],
        'url'        => home_url('/services/drone-photography/'),
      ],
      [
        'img_key'    => 'svc_drone_video_image_id',
        'title'      => 'Drone Videography',
        'tagline'    => 'Cinematic aerial video that reveals the full property story.',
        'desc'       => 'Smooth 4K aerial footage ideal for luxury listings, acreage, and social media content. Delivered standalone or combined with ground-level packages.',
        'price'      => 'Starting at $125 (add-on)',
        'highlights' => ['4K aerial video', 'Cinematic color grade', 'Social & MLS formats', '48-hr delivery'],
        'url'        => home_url('/services/drone-videography/'),
      ],
    ],
  ],
  [
    'eyebrow' => 'Add-Ons',
    'title'   => 'Marketing Add-Ons',
    'services' => [
      [
        'img_key'    => 'svc_addon_virtual_image_id',
        'title'      => 'Virtual Tours',
        'tagline'    => 'Interactive 3D walkthroughs buyers can explore from anywhere.',
        'desc'       => 'Immersive virtual tours that let buyers explore every room from their device — reducing wasted showings and attracting serious buyers.',
        'price'      => 'Starting at $149',
        'highlights' => ['360° interactive tour', 'Embeddable on MLS & website', 'Mobile-friendly', 'Floor plan integration'],
        'url'        => home_url('/services/virtual-tours/'),
      ],
      [
        'img_key'    => 'svc_addon_floor_image_id',
        'title'      => 'Floor Plans',
        'tagline'    => 'Help buyers visualize the layout before they visit.',
        'desc'       => 'Accurate 2D floor plans that give buyers a clear sense of the home\'s layout — increasing listing engagement and buyer confidence.',
        'price'      => 'Starting at $59',
        'highlights' => ['2D floor plan', 'Room dimensions', 'Quick turnaround', 'MLS-ready format'],
        'url'        => home_url('/services/floor-plans/'),
      ],
      [
        'img_key'    => 'svc_addon_zillow_image_id',
        'title'      => 'Zillow Showcase',
        'tagline'    => 'Premium Zillow listing upgrade for maximum exposure.',
        'desc'       => 'Upgrade your Zillow listing with Showcase — premium placement, interactive media, and scroll-stopping presentation that outperforms standard listings.',
        'price'      => 'Starting at $29/listing',
        'highlights' => ['Zillow premium placement', 'Interactive media embed', 'Increased listing views', 'Easy setup'],
        'url'        => home_url('/services/zillow-showcase/'),
      ],
    ],
  ],
];

$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
?>

<main id="main-content">
  <?php slm_edit_page_button($pid); ?>

  <!-- Hero -->
  <section class="svc-hero">
    <div class="svc-hero__top-line" aria-hidden="true"></div>
    <div class="container">
      <div class="svc-hero__content js-reveal">
        <span class="section-eyebrow"><?php echo esc_html($hero_eyebrow); ?></span>
        <h1><?php echo esc_html($hero_headline); ?></h1>
        <p><?php echo esc_html($hero_sub); ?></p>
      </div>
    </div>
  </section>

  <!-- Service groups -->
  <?php foreach ($groups as $g_i => $group): ?>
    <section class="svc-section <?php echo ($g_i % 2 === 1) ? 'svc-section--alt' : ''; ?>">
      <div class="container">
        <div class="svc-section__header js-reveal">
          <span class="section-eyebrow"><?php echo esc_html($group['eyebrow']); ?></span>
          <h2><?php echo esc_html($group['title']); ?></h2>
        </div>
        <div class="svc-cards-grid">
          <?php foreach ($group['services'] as $s_i => $svc): ?>
            <div class="svc-card js-reveal" style="transition-delay: <?php echo $s_i * 0.12; ?>s">
              <?php
                $img_id = (int) get_post_meta($pid, $svc['img_key'], true);
                if ($img_id) {
                  echo wp_get_attachment_image($img_id, 'large', false, ['class' => 'svc-card__img']);
                }
              ?>
              <div class="svc-card__body">
                <h3><?php echo esc_html($svc['title']); ?></h3>
                <p class="svc-card__tagline"><?php echo esc_html($svc['tagline']); ?></p>
                <p><?php echo esc_html($svc['desc']); ?></p>
                <ul class="svc-card__highlights">
                  <?php foreach ($svc['highlights'] as $h): ?>
                    <li><?php echo esc_html($h); ?></li>
                  <?php endforeach; ?>
                </ul>
                <p class="svc-card__price"><?php echo esc_html($svc['price']); ?></p>
                <a class="btn btn--dark" href="<?php echo esc_url($svc['url']); ?>">Learn More</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endforeach; ?>

  <!-- Final CTA -->
  <section class="svc-final-cta js-reveal" aria-label="Book a service">
    <div class="container">
      <h2>Ready to Create Content That Gets Results?</h2>
      <p>Whether you're an agent or a business — let's build something that works for you.</p>
      <div class="svc-final-cta__btns">
        <a class="btn svc-final-cta__primary" href="<?php echo esc_url($cta_url); ?>">Book a Shoot</a>
        <a class="btn btn--ghost svc-final-cta__secondary" href="tel:+19042945809">Call (904) 294-5809</a>
        <a class="btn btn--ghost svc-final-cta__secondary" href="<?php echo esc_url(home_url('/contact/')); ?>">Send a Message</a>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
```

Also add to `assets/css/pages.css`:
```css
.svc-hero {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #0D1B2A 0%, #162947 100%);
  padding: 128px 0 80px;
}

.svc-hero__top-line {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(to right, transparent, rgba(201, 146, 42, 0.60), transparent);
}

.svc-hero__content h1 { color: #ffffff; }
.svc-hero__content p  { color: rgba(255, 255, 255, 0.72); font-size: 17px; margin: 16px 0 0; }

.svc-section--alt { background: var(--background); }

.svc-cards-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

@media (max-width: 760px) { .svc-cards-grid { grid-template-columns: 1fr; } }
```

- [ ] **Step 3: Add Services page card CSS in `assets/css/pages.css`**

```css
/* ─── SERVICES PAGE ──────────────────────────────────────────── */
.svc-card {
  display: grid;
  grid-template-columns: 1fr 1fr;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(15, 39, 68, 0.06);
  background: #ffffff;
  box-shadow: 0 2px 12px rgba(11, 25, 44, 0.04);
}

@media (max-width: 720px) { .svc-card { grid-template-columns: 1fr; } }

.svc-card__img {
  width: 100%;
  height: 100%;
  min-height: 240px;
  object-fit: cover;
}

.svc-card__body {
  padding: 32px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.svc-card__tagline { color: var(--accent); font-weight: 600; font-size: 15px; margin: 0; }

.svc-card__highlights {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.svc-card__highlights li {
  padding: 4px 12px;
  border-radius: 999px;
  background: rgba(201, 146, 42, 0.08);
  border: 1px solid rgba(201, 146, 42, 0.20);
  color: var(--accent);
  font-size: 13px;
  font-weight: 600;
}

.svc-card__price {
  font-size: 15px;
  font-weight: 700;
  color: var(--primary-strong);
  margin: 0;
}

.svc-section { padding: 64px 0; }
.svc-section + .svc-section { padding-top: 0; }
.svc-section__header { margin-bottom: 32px; }
```

- [ ] **Step 4: Verify, commit**
```bash
git add templates/page-services.php assets/css/pages.css inc/services-meta.php
git commit -m "feat: update Services page — grouped Photo/Video/Drone/Add-Ons layout with image pickers"
```

---

## Task 15: Admin Media Settings — Audit and Verify All Pages

**Files:** Review all `inc/*.php` meta box files

Ensure every public-facing page has WP Admin media pickers for its images. Audit checklist:

- [ ] **Homepage hero slider** — ACF fields `hp_slide_1_image` through `hp_slide_6_image` in `inc/acf-fields.php` ✓
- [ ] **Homepage before/after** — `before_image`, `after_image` in `inc/homepage-meta.php` ✓
- [ ] **Portfolio page images/videos** — managed via `inc/portfolio-gallery.php` Appearance → Portfolio Gallery ✓
- [ ] **Service page galleries** — managed via `inc/portfolio-gallery.php` meta box on each service page ✓
- [ ] **About page partner photos** — added in `inc/about-meta.php` (Task 12) ✓
- [ ] **Services page card images** — added image ID fields in `inc/services-meta.php` (Task 14) ✓
- [ ] **Contact page** — no images needed (form + contact info)

If any page is missing a media picker, add one following the same pattern as `inc/about-meta.php`: `wp_enqueue_media()` in `admin_enqueue_scripts`, `wp.media` frame in inline JS.

- [ ] **Step 1: Verify each page in WP Admin has its media pickers visible**

Log into `/wp-admin/`, navigate to each page's edit screen, confirm media picker buttons appear and function.

- [ ] **Step 2: Test media upload flow end-to-end**

Upload a test image on one page, save, verify it appears on the frontend.

- [ ] **Step 3: Final commit**
```bash
git add -A
git commit -m "feat: complete Figma design implementation — all pages, admin media pickers, portfolio admin panel"
```

---

## Quick Reference — Page → Template Map

| Public URL | Template File |
|---|---|
| `/` (homepage) | `front-page.php` + `template-parts/home/*.php` |
| `/portfolio/` | `templates/page-portfolio.php` |
| `/about/` | `templates/page-about.php` |
| `/contact/` | `templates/page-contact.php` |
| `/services/` | `templates/page-services.php` |
| `/services/real-estate-photography/` | `templates/page-service-re-photography.php` |
| `/services/drone-photography/` | `templates/page-service-drone-photography.php` |
| *(all other service sub-pages)* | `templates/page-service-*.php` |

## Quick Reference — Admin Media Locations

| Content | Admin Location |
|---|---|
| Homepage hero slides (1–6) | WP Admin → Pages → Homepage → ACF Hero Images meta box |
| Homepage before/after photos | WP Admin → Pages → Homepage → Before/After Visuals meta box |
| Portfolio images + videos | WP Admin → Appearance → Portfolio Gallery |
| About page partner photos | WP Admin → Pages → About → Partner 1 / Partner 2 meta boxes |
| Service page gallery images | WP Admin → Pages → [Service Page] → Portfolio Media meta box |
| Portfolio page images | WP Admin → Pages → Portfolio → Portfolio Media meta box (or Appearance → Portfolio Gallery) |
