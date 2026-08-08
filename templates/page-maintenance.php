<?php
if (!defined('ABSPATH')) exit;

$site_name = (string) get_bloginfo('name');
$logo_id = (int) get_theme_mod('custom_logo');
$logo_url = '';
if ($logo_id > 0) {
  $src = wp_get_attachment_image_src($logo_id, 'full');
  if (is_array($src) && !empty($src[0])) {
    $logo_url = (string) $src[0];
  }
}
if ($logo_url === '') {
  $fallback_logo = get_template_directory() . '/assets/img/logo-icon.png';
  if (file_exists($fallback_logo)) {
    $logo_url = get_template_directory_uri() . '/assets/img/logo-icon.png';
  }
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title><?php echo esc_html($site_name); ?> &mdash; We&rsquo;ll be right back</title>
  <style>
    :root {
      --slm-bg: #0D1B2A;
      --slm-bg-2: #15314f;
      --slm-gold: #C9922A;
      --slm-text: #ffffff;
      --slm-muted: rgba(255, 255, 255, 0.72);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; height: 100%; }
    body {
      font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background: radial-gradient(120% 80% at 50% 0%, var(--slm-bg-2) 0%, var(--slm-bg) 60%);
      color: var(--slm-text);
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 32px 18px;
      -webkit-font-smoothing: antialiased;
    }
    .slm-maint__card {
      width: min(560px, 100%);
      text-align: center;
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 18px;
      padding: 44px 28px;
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
      backdrop-filter: blur(6px);
    }
    .slm-maint__logo {
      width: 72px;
      height: 72px;
      object-fit: contain;
      margin: 0 auto 18px;
      display: block;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.06);
      padding: 8px;
    }
    .slm-maint__badge {
      display: inline-block;
      font-size: 0.72rem;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--slm-gold);
      border: 1px solid rgba(201, 146, 42, 0.45);
      padding: 5px 12px;
      border-radius: 999px;
      margin-bottom: 18px;
    }
    .slm-maint__title {
      font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
      font-size: clamp(1.5rem, 4vw, 2.1rem);
      font-weight: 700;
      letter-spacing: -0.01em;
      margin: 0 0 12px;
    }
    .slm-maint__msg {
      color: #000000;
      line-height: 1.55;
      margin: 0 0 22px;
      font-size: 1rem;
    }
    .slm-maint__brand {
      font-size: 0.85rem;
      color: var(--slm-muted);
      letter-spacing: 0.04em;
    }
    .slm-maint__brand strong {
      color: var(--slm-gold);
      font-weight: 700;
    }
    .slm-maint__divider {
      width: 56px;
      height: 2px;
      background: var(--slm-gold);
      border-radius: 2px;
      margin: 22px auto;
    }
  </style>
  <?php wp_head(); ?>
</head>
<body>
  <main class="slm-maint__card" role="main">
    <?php if ($logo_url !== ''): ?>
      <img src="<?php echo esc_url($logo_url); ?>" alt="" class="slm-maint__logo">
    <?php endif; ?>
    <span class="slm-maint__badge">Coming Soon</span>
    <h1 class="slm-maint__title">We&rsquo;re currently updating our site.</h1>
    <p class="slm-maint__msg">Check back soon &mdash; great things are coming.</p>
    <div class="slm-maint__divider" aria-hidden="true"></div>
    <p class="slm-maint__brand"><strong><?php echo esc_html($site_name); ?></strong></p>
  </main>
  <?php wp_footer(); ?>
</body>
</html>
