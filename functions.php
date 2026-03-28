<?php
if (!defined('ABSPATH'))
  exit;

require_once __DIR__ . '/inc/aryeo.php';
require_once __DIR__ . '/inc/subscriptions.php';
require_once __DIR__ . '/inc/member-credits.php';
require_once __DIR__ . '/inc/testimonials.php';
require_once __DIR__ . '/inc/portfolio-gallery.php';
require_once __DIR__ . '/inc/footer-customizer.php';
require_once __DIR__ . '/inc/page-editable-text.php';

/**
 * Hint compatible cache layers to bypass full-page cache for signed-in users.
 */
add_action('init', function () {
  if (is_user_logged_in() && !defined('DONOTCACHEPAGE')) {
    define('DONOTCACHEPAGE', true);
  }
}, 1);

function slm_has_auth_cookie(): bool
{
  $cookie_names = [];

  if (defined('LOGGED_IN_COOKIE') && is_string(LOGGED_IN_COOKIE) && LOGGED_IN_COOKIE !== '') {
    $cookie_names[] = LOGGED_IN_COOKIE;
  }
  if (defined('AUTH_COOKIE') && is_string(AUTH_COOKIE) && AUTH_COOKIE !== '') {
    $cookie_names[] = AUTH_COOKIE;
  }
  if (defined('SECURE_AUTH_COOKIE') && is_string(SECURE_AUTH_COOKIE) && SECURE_AUTH_COOKIE !== '') {
    $cookie_names[] = SECURE_AUTH_COOKIE;
  }

  foreach ($cookie_names as $cookie_name) {
    if (!empty($_COOKIE[$cookie_name])) {
      return true;
    }
  }

  return false;
}

add_action('init', function () {
  if (!(is_user_logged_in() || slm_has_auth_cookie())) {
    return;
  }

  if (!defined('DONOTCACHEPAGE')) {
    define('DONOTCACHEPAGE', true);
  }
  if (!defined('DONOTCACHEOBJECT')) {
    define('DONOTCACHEOBJECT', true);
  }
  if (!defined('DONOTCACHEDB')) {
    define('DONOTCACHEDB', true);
  }
  if (!defined('DONOTMINIFY')) {
    define('DONOTMINIFY', true);
  }
}, 1);

function slm_theme_ver()
{
  $t = wp_get_theme();
  return $t->get('Version') ?: '0.1.0';
}

/**
 * True when the given user can access the admin dashboard area.
 */
function slm_user_is_admin($user = null): bool
{
  if (!$user instanceof WP_User) {
    $user = wp_get_current_user();
  }
  return ($user instanceof WP_User) && user_can($user, 'manage_options');
}

/**
 * Hide the WordPress admin toolbar on all frontend pages for clients, while
 * keeping it visible for admins/staff.
 */
add_filter('show_admin_bar', function ($show) {
  if (is_admin()) {
    return $show;
  }

  if (!is_user_logged_in()) {
    return $show;
  }

  if (slm_user_is_admin()) {
    return $show;
  }
  return false;
}, 20);

/**
 * Resolve a published page URL by assigned page template.
 */
function slm_page_url_by_template(string $template_file, string $fallback_path): string
{
  static $cache = [];

  $cache_key = $template_file . '|' . $fallback_path;
  if (isset($cache[$cache_key])) {
    return $cache[$cache_key];
  }

  $candidates = [$template_file];
  if (strpos($template_file, '/') === false) {
    $candidates[] = 'templates/' . ltrim($template_file, '/');
  }

  foreach ($candidates as $candidate) {
    $page_ids = get_posts([
      'post_type' => 'page',
      'post_status' => 'publish',
      'posts_per_page' => 1,
      'fields' => 'ids',
      'no_found_rows' => true,
      'meta_key' => '_wp_page_template',
      'meta_value' => $candidate,
    ]);

    if (!empty($page_ids)) {
      $permalink = get_permalink((int) $page_ids[0]);
      if (is_string($permalink) && $permalink !== '') {
        $cache[$cache_key] = $permalink;
        return $permalink;
      }
    }
  }

  $cache[$cache_key] = home_url($fallback_path);
  return $cache[$cache_key];
}

function slm_admin_portal_url(): string
{
  return slm_page_url_by_template('admin-portal.php', '/admin-portal/');
}

function slm_portal_url(): string
{
  return slm_page_url_by_template('page-portal.php', '/portal/');
}

function slm_login_url(): string
{
  return slm_page_url_by_template('page-login.php', '/login/');
}

function slm_memberships_url(): string
{
  return slm_page_url_by_template('page-memberships.php', '/memberships/');
}

/**
 * Primary role-aware CTA used across shared templates.
 */
function slm_primary_cta_for_user($user = null): array
{
  if (!$user instanceof WP_User) {
    $user = wp_get_current_user();
  }

  if ($user instanceof WP_User && $user->ID > 0 && slm_user_is_admin($user)) {
    return [
      'url' => slm_admin_portal_url(),
      'label' => 'Admin Dashboard',
      'is_order' => false,
    ];
  }

  if ($user instanceof WP_User && $user->ID > 0) {
    return [
      'url' => add_query_arg('view', 'place-order', slm_portal_url()),
      'label' => 'Place Order',
      'is_order' => true,
    ];
  }

  return [
    'url' => add_query_arg('mode', 'signup', slm_login_url()),
    'label' => 'Create Account',
    'is_order' => false,
  ];
}

/**
 * Canonical service detail pages by key.
 */
function slm_service_pages(): array
{
  return [
    're-photography' => [
      'template' => 'templates/page-service-re-photography.php',
      'fallback' => '/service-real-estate-photography/',
      'label' => 'Real Estate Photography',
    ],
    're-videography' => [
      'template' => 'templates/page-service-re-videography.php',
      'fallback' => '/service-real-estate-videography/',
      'label' => 'Real Estate Videography',
    ],
    'drone-photography' => [
      'template' => 'templates/page-service-drone-photography.php',
      'fallback' => '/service-drone-photography/',
      'label' => 'Drone Photography',
    ],
    'virtual-tours' => [
      'template' => 'templates/page-service-virtual-tours.php',
      'fallback' => '/service-virtual-tours/',
      'label' => 'Virtual Tours',
    ],
    'floor-plans' => [
      'template' => 'templates/page-service-floor-plans.php',
      'fallback' => '/service-floor-plans/',
      'label' => 'Floor Plans',
    ],
    'drone-videography' => [
      'template' => 'templates/page-service-drone-videography.php',
      'fallback' => '/service-drone-videography/',
      'label' => 'Drone Videography',
    ],
    'twilight-photography' => [
      'template' => 'templates/page-service-twilight-photography.php',
      'fallback' => '/service-twilight-photography/',
      'label' => 'Twilight Photography',
    ],
    'social-media-packages' => [
      'template' => 'templates/page-social-media-packages.php',
      'fallback' => '/social-media-packages/',
      'label' => 'Social Media Packages',
    ],
    'social-media-assistance' => [
      'template' => 'templates/page-social-media-assistance.php',
      'fallback' => '/social-media-assistance/',
      'label' => 'Social Media Assistance',
    ],
  ];
}

function slm_service_page_url(string $service_key): string
{
  $services = slm_service_pages();
  if (!isset($services[$service_key])) {
    return home_url('/services/');
  }

  $service = $services[$service_key];
  return slm_page_url_by_template((string) $service['template'], (string) $service['fallback']);
}

function slm_service_page_urls(): array
{
  $services = slm_service_pages();
  $urls = [];
  foreach ($services as $key => $service) {
    $urls[$key] = [
      'label' => (string) $service['label'],
      'url' => slm_service_page_url((string) $key),
    ];
  }
  return $urls;
}

function slm_primary_nav_fallback(): void
{
  $service_links = slm_service_page_urls();
  echo '<ul class="nav__menu" id="site-primary-menu">';
  echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
  echo '<li class="menu-item-has-children"><a href="' . esc_url(home_url('/services/')) . '">Services</a>';
  echo '<ul class="sub-menu">';
  foreach ($service_links as $service) {
    echo '<li><a href="' . esc_url((string) $service['url']) . '">' . esc_html((string) $service['label']) . '</a></li>';
  }
  echo '</ul></li>';
  echo '<li><a href="' . esc_url(slm_memberships_url()) . '">Memberships</a></li>';
  echo '<li><a href="' . esc_url(slm_page_url_by_template('templates/page-portfolio.php', '/portfolio/')) . '">Portfolio</a></li>';
  echo '<li><a href="' . esc_url(home_url('/contact/')) . '">Contact</a></li>';
  echo '</ul>';
}

/**
 * Prevent cache bleed on auth-sensitive pages/routes.
 */
add_action('send_headers', function () {
  if (is_admin()) {
    return;
  }

  if (!headers_sent()) {
    header('Vary: Cookie', false);
  }

  $is_auth_template = is_page_template('templates/page-login.php')
    || is_page_template('templates/page-portal.php')
    || is_page_template('templates/admin-portal.php');

  $is_auth_query = isset($_GET['mode'])
    || isset($_GET['auth'])
    || isset($_GET['slm_aryeo_start_order'])
    || isset($_GET['slm_subscription_action']);

  $has_auth_cookie = slm_has_auth_cookie();

  if (!(is_user_logged_in() || $has_auth_cookie || $is_auth_template || $is_auth_query)) {
    return;
  }

  if (!defined('DONOTCACHEPAGE')) {
    define('DONOTCACHEPAGE', true);
  }

  nocache_headers();
  if (!headers_sent()) {
    header('Cache-Control: private, no-cache, no-store, must-revalidate, max-age=0');
  }
}, 2);

/**
 * Role-aware dashboard target.
 */
function slm_dashboard_url($user = null): string
{
  return slm_user_is_admin($user) ? slm_admin_portal_url() : slm_portal_url();
}

/**
 * File-based cache busting. Pass a theme-relative path like: /assets/css/base.css
 */
function slm_asset_ver(string $rel_path): string
{
  $abs = get_template_directory() . $rel_path;
  if (is_file($abs))
    return (string) filemtime($abs);
  return slm_theme_ver();
}

/**
 * Canonical Privacy Policy copy rendered by the dedicated privacy template.
 */
function slm_privacy_policy_content_html(): string
{
  return trim(<<<'HTML'
<h2>1. Overview</h2>
<p>This Privacy Policy explains how Showcase Listings Media ("Company") collects, uses, stores, and shares information when you visit our website, contact us, create an account, or book services.</p>
<p>By using this website or submitting information through our forms, portal, or booking flows, you consent to the practices described here.</p>

<h2>2. Information We Collect</h2>
<p>We may collect the following categories of information:</p>
<ul>
  <li>Contact information such as name, email address, phone number, brokerage, and company details.</li>
  <li>Account information submitted when you register for a client portal or log in to an existing account.</li>
  <li>Order and property details related to real estate media bookings, including listing address, scheduling preferences, service selections, and delivery notes.</li>
  <li>Communications you send to us through forms, email, text, or support requests.</li>
  <li>Technical and usage data such as browser type, device information, pages visited, referral data, and log information used for security and performance.</li>
  <li>Payment-related details required to complete transactions through third-party processors. We do not store full payment card numbers on this website.</li>
</ul>

<h2>3. How We Use Information</h2>
<p>We use collected information to operate and improve our business, including to:</p>
<ul>
  <li>respond to inquiries and provide customer support;</li>
  <li>schedule, manage, and deliver photography, video, drone, and related media services;</li>
  <li>create and manage portal accounts and service history;</li>
  <li>process payments, invoices, subscriptions, and related operational records;</li>
  <li>send confirmations, service updates, order notifications, and important account communications;</li>
  <li>monitor website security, prevent abuse, and troubleshoot technical issues;</li>
  <li>improve our website, workflows, and customer experience;</li>
  <li>comply with legal, tax, accounting, and recordkeeping obligations.</li>
</ul>

<h2>4. Sharing and Service Providers</h2>
<p>We may share information only as needed to operate our services and business. This may include trusted providers that support:</p>
<ul>
  <li>website hosting and infrastructure;</li>
  <li>email and communication delivery;</li>
  <li>order, scheduling, or media workflow tools;</li>
  <li>payment and billing processing;</li>
  <li>customer account, portal, or subscription management;</li>
  <li>analytics, security, and fraud prevention.</li>
</ul>
<p>We do not sell personal information. We may also disclose information when necessary to protect our rights, enforce our policies, respond to lawful requests, or comply with legal obligations.</p>

<h2>5. Cookies and Similar Technologies</h2>
<p>This website may use cookies and similar technologies to support essential functionality, remember session preferences, maintain secure logins, and understand site usage.</p>
<ul>
  <li>Essential cookies help the site function properly, including authentication and portal access.</li>
  <li>Preference or performance cookies may help improve usability and reliability.</li>
  <li>Third-party tools may set their own cookies when embedded services or integrations are used.</li>
</ul>
<p>You can manage cookies through your browser settings, but disabling certain cookies may affect website functionality.</p>

<h2>6. Marketing and Communications</h2>
<p>We may send service-related emails or messages such as booking confirmations, delivery notifications, billing notices, and account updates.</p>
<p>If we send optional promotional or marketing communications, you may opt out using the unsubscribe method included in those communications or by contacting us directly.</p>

<h2>7. Data Retention</h2>
<p>We retain information only for as long as reasonably necessary to provide services, maintain business records, resolve disputes, enforce agreements, and satisfy legal, tax, or accounting obligations.</p>
<p>Retention periods may vary depending on the type of information, the services provided, and whether an account, transaction, or support issue remains active.</p>

<h2>8. Data Security</h2>
<p>We use reasonable administrative, technical, and organizational safeguards designed to protect personal information from unauthorized access, misuse, alteration, or disclosure.</p>
<p>No method of transmission or storage is completely secure, so we cannot guarantee absolute security.</p>

<h2>9. Your Choices</h2>
<p>You may contact us to request updates to your account or contact information, ask questions about your data, or request deletion where applicable and legally permitted.</p>
<ul>
  <li>You may choose not to submit certain information, though that may limit our ability to provide services.</li>
  <li>You may disable cookies in your browser, subject to the functionality limits noted above.</li>
  <li>You may opt out of non-essential marketing communications.</li>
</ul>

<h2>10. Third-Party Links and Platforms</h2>
<p>This website may link to third-party services, galleries, payment tools, or external platforms. We are not responsible for the privacy practices, content, or security of third-party websites or services.</p>
<p>You should review the privacy policies of those third parties before submitting information to them.</p>

<h2>11. Children's Privacy</h2>
<p>This website and our services are not directed to children under 13, and we do not knowingly collect personal information from children under 13 through this site.</p>

<h2>12. Updates and Contact</h2>
<p>We may update this Privacy Policy from time to time by posting a revised version on this page. Continued use of the site after updates means the revised policy will apply going forward.</p>
<p>If you have questions about this Privacy Policy or how your information is handled, please contact us using the information provided on our Contact page.</p>
HTML
  );
}

/**
 * Canonical Terms of Service copy rendered by the dedicated terms template.
 */
function slm_terms_of_service_content_html(): string
{
  return trim(<<<'HTML'
<h2>1. Booking &amp; Acceptance</h2>
<p>By scheduling a service with Showcase Listings Media ("Company"), the client agrees to all terms outlined below.</p>
<p>The person scheduling the appointment is responsible for communicating these terms to the homeowner, occupants, and any third parties.</p>

<h2>2. Deliverables</h2>
<p>The Company provides media services including but not limited to photography, videography, drone, and marketing media.</p>
<p>Final deliverables include only the edited media files delivered via online gallery.</p>
<p>RAW files are not included and are not delivered under any circumstance.</p>
<p>The Company maintains creative discretion in:</p>
<ul>
  <li>composition</li>
  <li>editing style</li>
  <li>retouching level</li>
  <li>music selection (video)</li>
  <li>shot selection</li>
</ul>
<p>Requests for specific shots must be made prior to the shoot and are not guaranteed.</p>

<h2>3. Turnaround Times</h2>
<p>We strive to meet the following delivery timelines:</p>
<ul>
  <li>Photography: 24 hours</li>
  <li>Video/Reels: 48 hours</li>
</ul>
<p>Turnaround times begin after the shoot is completed.</p>
<p>Turnaround may be delayed due to:</p>
<ul>
  <li>major revisions requested</li>
  <li>incomplete property readiness</li>
  <li>weather return visits (exteriors/drone)</li>
  <li>technical issues beyond control</li>
</ul>
<p>While we strive to always meet turnaround times, they are not guaranteed and do not constitute grounds for refunds.</p>

<h2>4. Property Readiness</h2>
<p>The property must be fully ready at the scheduled time.</p>
<p>The photographer will not:</p>
<ul>
  <li>move large furniture</li>
  <li>clean</li>
  <li>stage</li>
  <li>remove clutter</li>
  <li>wait for cleaners/contractors</li>
</ul>
<p>If the property is not ready, the Company may:</p>
<ul>
  <li>shoot as-is OR</li>
  <li>reschedule at client request (rescheduling policy applies)</li>
</ul>

<h2>5. Rescheduling &amp; Cancellation</h2>
<p>Appointments may be rescheduled with at least 24 hours notice.</p>
<p>Day-of cancellations or reschedules incur a $75 fee.</p>
<p>No-shows or inability to access the property are treated as day-of cancellations.</p>

<h2>6. Weather Policy &amp; Drone Restrictions</h2>
<p>Drone operations are regulated and cannot be performed in:</p>
<ul>
  <li>rain</li>
  <li>unsafe wind</li>
  <li>restricted flight conditions</li>
</ul>
<p>If weather affects the shoot:</p>
<p><strong>Light rain / sprinkle</strong></p>
<ul>
  <li>Interior photos may proceed</li>
  <li>Exterior &amp; drone rescheduled</li>
  <li>When drone return visit occurs, exterior photos will also be re-shot</li>
</ul>
<p><strong>Heavy rain / storming</strong></p>
<ul>
  <li>Entire shoot rescheduled</li>
</ul>
<p><strong>Drone return visits</strong></p>
<ul>
  <li>Only drone and exterior coverage will be completed unless otherwise requested.</li>
</ul>
<p>Blue sky replacement may be used at the Company's discretion.</p>

<h2>7. Access &amp; Safety</h2>
<p>Client is responsible for:</p>
<ul>
  <li>property access</li>
  <li>pets secured</li>
  <li>occupants notified</li>
  <li>safe environment</li>
</ul>
<p>The Company reserves the right to leave a location if conditions are unsafe. This is treated as a completed appointment.</p>

<h2>8. Revisions</h2>
<p>One reasonable revision round is included for videos and photos (minor adjustments).</p>
<p>Examples:</p>
<ul>
  <li>brightness</li>
  <li>color tone</li>
  <li>clip swap if available</li>
</ul>
<p>Not included:</p>
<ul>
  <li>full re-edits</li>
  <li>different music styles after approval</li>
  <li>reshooting due to property condition</li>
</ul>
<p>Additional revisions may incur editing fees.</p>

<h2>9. Payment</h2>
<p>Payment terms are due per invoice agreement.</p>
<p>Media usage rights are granted after payment is received.</p>
<p>Failure to pay may result in:</p>
<ul>
  <li>removal of access</li>
  <li>late fees</li>
  <li>collection action</li>
</ul>

<h2>10. Usage License</h2>
<p>Client receives a marketing license for real estate promotion of the specific listing.</p>
<p>Media may not be:</p>
<ul>
  <li>sold</li>
  <li>transferred to another listing</li>
  <li>used by builders/third parties without permission</li>
</ul>
<p>The Company retains copyright and may use media for portfolio, marketing, and advertising.</p>

<h2>11. Liability</h2>
<p>The Company is not responsible for:</p>
<ul>
  <li>listing performance</li>
  <li>MLS rejection</li>
  <li>weather appearance changes</li>
  <li>staging outcomes</li>
  <li>homeowner dissatisfaction</li>
</ul>
<p>Maximum liability is limited to the cost of the service performed.</p>

<h2>12. Agreement</h2>
<p>Booking a service constitutes agreement to all terms listed above.</p>
HTML
  );
}

/**
 * Canonical fallback legal copy used only when the legal pages are empty.
 */
function slm_legal_default_content(): array
{
  $last_updated = '<p><em>Last updated: ' . esc_html(date('F j, Y')) . '</em></p>';

  $privacy_content = slm_privacy_policy_content_html();

  $terms_content = slm_terms_of_service_content_html();

  return [
    'privacy' => $privacy_content,
    'terms' => $terms_content,
  ];
}

/**
 * Ensure legal pages exist (and use the correct page templates).
 *
 * This is safe to call multiple times; it only creates pages when missing.
 */
function slm_ensure_legal_pages(): void
{
  $ensure_page = static function (string $title, string $slug, string $template, string $content_html): int {
    $existing = get_page_by_path($slug);
    if (!$existing) {
      $query = new WP_Query([
        'post_type' => 'page',
        'title' => $title,
        'post_status' => 'any',
        'posts_per_page' => 1,
        'no_found_rows' => true,
      ]);
      $existing = $query->have_posts() ? $query->posts[0] : null;
      wp_reset_postdata();
    }
    if ($existing) {
      $update = ['ID' => $existing->ID];
      $should_update = false;
      $current_content = trim((string) $existing->post_content);

      if ($existing->post_status !== 'publish') {
        $update['post_status'] = 'publish';
        $should_update = true;
      }

      $should_seed_content = ($current_content === '');
      if (!$should_seed_content && $slug === 'terms-of-service') {
        $looks_like_legacy_default = strpos($current_content, '<h2>Acceptance</h2>') !== false;
        $already_has_new_default = strpos($current_content, '<h2>Booking and Acceptance</h2>') !== false;
        if ($looks_like_legacy_default && !$already_has_new_default) {
          $should_seed_content = true;
        }
      }

      if ($should_seed_content) {
        $update['post_content'] = $content_html;
        $should_update = true;
      }
      if ($should_update) {
        wp_update_post($update);
      }

      update_post_meta($existing->ID, '_wp_page_template', $template);
      return (int) $existing->ID;
    }

    $id = wp_insert_post([
      'post_title' => $title,
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => $slug,
      'post_content' => $content_html,
    ]);
    if ($id && !is_wp_error($id)) {
      update_post_meta((int) $id, '_wp_page_template', $template);
      return (int) $id;
    }
    return 0;
  };

  $defaults = slm_legal_default_content();
  $ensure_page('Privacy Policy', 'privacy-policy', 'templates/page-privacy-policy.php', $defaults['privacy']);
  $ensure_page('Terms of Service', 'terms-of-service', 'templates/page-terms-of-service.php', $defaults['terms']);
}

/**
 * Keep legal pages available even when the theme was activated before this logic existed.
 * Guarded by a transient to avoid DB queries on every request.
 */
add_action('init', function () {
  if (wp_installing())
    return;

  if (get_transient('slm_legal_pages_exist'))
    return;

  $privacy = get_page_by_path('privacy-policy');
  $terms = get_page_by_path('terms-of-service');
  if (!$privacy || !$terms) {
    slm_ensure_legal_pages();
  }

  set_transient('slm_legal_pages_exist', '1', DAY_IN_SECONDS);
}, 5);

add_action('init', function () {
  if (wp_installing()) {
    return;
  }

  if (get_transient('slm_memberships_page_exists')) {
    return;
  }

  $memberships = get_page_by_path('memberships') ?: get_page_by_title('Memberships');
  if (!$memberships) {
    $memberships_id = wp_insert_post([
      'post_title' => 'Memberships',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => 'memberships',
    ]);
    if ($memberships_id && !is_wp_error($memberships_id)) {
      update_post_meta((int) $memberships_id, '_wp_page_template', 'templates/page-memberships.php');
    }
  } else {
    update_post_meta((int) $memberships->ID, '_wp_page_template', 'templates/page-memberships.php');
  }

  set_transient('slm_memberships_page_exists', '1', DAY_IN_SECONDS);
}, 6);

add_action('init', function () {
  if (wp_installing()) {
    return;
  }

  if (get_transient('slm_portfolio_page_exists')) {
    return;
  }

  $portfolio = get_page_by_path('our-portfolio') ?: get_page_by_path('portfolio-gallery');
  if (!$portfolio) {
    $portfolio_id = wp_insert_post([
      'post_title' => 'Portfolio',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => 'our-portfolio',
    ]);
    if ($portfolio_id && !is_wp_error($portfolio_id)) {
      update_post_meta((int) $portfolio_id, '_wp_page_template', 'templates/page-portfolio.php');
    }
  } else {
    update_post_meta((int) $portfolio->ID, '_wp_page_template', 'templates/page-portfolio.php');
  }

  set_transient('slm_portfolio_page_exists', '1', DAY_IN_SECONDS);
}, 7);

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', [
    'search-form',
    'comment-form',
    'comment-list',
    'gallery',
    'caption',
    'style',
    'script'
  ]);

  add_theme_support('custom-logo', [
    'height' => 48,
    'width' => 48,
    'flex-height' => true,
    'flex-width' => true,
  ]);

  register_nav_menus([
    'primary' => __('Primary Menu', 'slm'),
    'footer' => __('Footer Menu', 'slm'),
  ]);
});

add_action('wp_enqueue_scripts', function () {
  $uri = get_template_directory_uri();

  $fonts_url = 'https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap';
  wp_enqueue_style('slm-fonts', $fonts_url, [], null);

  // Base layers (always) - load in a predictable order.
  $base = '/assets/css/base.css';
  $components = '/assets/css/components.css';
  $nav = '/assets/css/nav.css';
  $pages = '/assets/css/pages.css';

  wp_enqueue_style('slm-base', $uri . $base, ['slm-fonts'], slm_asset_ver($base));
  wp_enqueue_style('slm-components', $uri . $components, ['slm-base'], slm_asset_ver($components));
  wp_enqueue_style('slm-nav', $uri . $nav, ['slm-components'], slm_asset_ver($nav));
  wp_enqueue_style('slm-pages', $uri . $pages, ['slm-nav'], slm_asset_ver($pages));

  $main = '/assets/js/main.js';
  wp_enqueue_script('slm-main', $uri . $main, [], slm_asset_ver($main), true);

  // GSAP + ScrollTrigger
  wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', [], null, true);
  wp_enqueue_script('scrolltrigger', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js', ['gsap'], null, true);

  // Custom Animations
  $custom_anim = '/assets/js/custom-animations.js';
  wp_enqueue_script('slm-custom-animations', $uri . $custom_anim, ['gsap', 'scrolltrigger'], slm_asset_ver($custom_anim), true);
});

/**
 * Localize AJAX URL for front-end scripts.
 */
add_action('wp_enqueue_scripts', function () {
  wp_localize_script('slm-main', 'slmAjax', [
    'url' => admin_url('admin-ajax.php'),
  ]);
}, 20);

/**
 * AJAX handler: save user profile from the portal Account view.
 */
add_action('wp_ajax_slm_save_profile', function () {
  if (!is_user_logged_in()) {
    wp_send_json_error(['message' => 'Not logged in.'], 403);
  }
  if (!check_ajax_referer('slm_save_profile', 'slm_profile_nonce', false)) {
    wp_send_json_error(['message' => 'Session expired. Please refresh the page.'], 403);
  }
  $user = wp_get_current_user();
  $display_name = sanitize_text_field(wp_unslash($_POST['display_name'] ?? ''));
  $email = sanitize_email(wp_unslash($_POST['user_email'] ?? ''));

  if ($display_name === '') {
    wp_send_json_error(['message' => 'Name cannot be empty.']);
  }
  if ($email === '' || !is_email($email)) {
    wp_send_json_error(['message' => 'Please enter a valid email address.']);
  }
  if ($email !== $user->user_email && email_exists($email)) {
    wp_send_json_error(['message' => 'That email is already in use by another account.']);
  }

  $parts = explode(' ', $display_name, 2);
  $result = wp_update_user([
    'ID' => $user->ID,
    'display_name' => $display_name,
    'first_name' => $parts[0],
    'last_name' => $parts[1] ?? '',
    'user_email' => $email,
  ]);

  if (is_wp_error($result)) {
    wp_send_json_error(['message' => $result->get_error_message()]);
  }
  wp_send_json_success(['message' => 'Profile saved successfully.']);
});

/**
 * Force role-based redirect after successful login, regardless of requested URL.
 */
add_filter('login_redirect', function ($redirect_to, $requested_redirect_to, $user) {
  if ($user instanceof WP_User) {
    return slm_dashboard_url($user);
  }
  return $redirect_to;
}, 10, 3);

/**
 * Keep failed front-end login attempts on the themed login page.
 */
add_action('wp_login_failed', function () {
  $referrer = wp_get_referer();
  if (!$referrer)
    return;

  $login_url = slm_login_url();
  if (strpos($referrer, $login_url) === false)
    return;

  $target = add_query_arg(['auth' => 'failed', 'mode' => 'login'], remove_query_arg(['auth', 'mode'], $referrer));
  wp_safe_redirect($target);
  exit;
});

/**
 * Redirect legacy service URLs to canonical service detail pages.
 * Enabled by default and can be disabled via filter if needed.
 */
add_action('template_redirect', function () {
  if (!apply_filters('slm_enable_service_legacy_redirects', true))
    return;

  if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST))
    return;

  $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
  $path = strtolower(trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/'));
  if ($path === '')
    return;

  $legacy_map = [
    'service-re-photography' => 're-photography',
    'service-re-videography' => 're-videography',
    'service-drone-photography' => 'drone-photography',
    'service-virtual-tours' => 'virtual-tours',
    'service-floor-plans' => 'floor-plans',
    'service-drone-videography' => 'drone-videography',
    'service-twilight-photography' => 'twilight-photography',
    'services/real-estate-photography' => 're-photography',
    'services/real-estate-videography' => 're-videography',
    'services/drone-photography' => 'drone-photography',
    'services/virtual-tours' => 'virtual-tours',
    'services/floor-plans' => 'floor-plans',
    'services/drone-videography' => 'drone-videography',
    'services/twilight-photography' => 'twilight-photography',
  ];

  if (!isset($legacy_map[$path]))
    return;

  $target = slm_service_page_url((string) $legacy_map[$path]);
  $requested_url = home_url('/' . $path . '/');
  if (untrailingslashit($target) === untrailingslashit($requested_url))
    return;

  wp_safe_redirect($target, 301);
  exit;
}, 1);

/**
 * Local/dev convenience: ensure WP uses a static front page when the theme is activated.
 * Only runs if no front page is currently set.
 */
add_action('after_switch_theme', function () {
  // Always ensure legal pages exist, even if a front page is already set.
  slm_ensure_legal_pages();
  update_option('page_for_posts', 0);

  $memberships = get_page_by_path('memberships') ?: get_page_by_title('Memberships');
  if (!$memberships) {
    $memberships_id = wp_insert_post([
      'post_title' => 'Memberships',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => 'memberships',
    ]);
    if ($memberships_id && !is_wp_error($memberships_id)) {
      update_post_meta((int) $memberships_id, '_wp_page_template', 'templates/page-memberships.php');
    }
  } else {
    update_post_meta((int) $memberships->ID, '_wp_page_template', 'templates/page-memberships.php');
  }

  // Keep an editable company-values page available when blog is disabled.
  $about = get_page_by_path('about') ?: get_page_by_title('About');
  if (!$about) {
    $about_id = wp_insert_post([
      'post_title' => 'About',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => 'about',
    ]);
    if ($about_id && !is_wp_error($about_id)) {
      update_post_meta((int) $about_id, '_wp_page_template', 'templates/page-about.php');
    }
  } else {
    update_post_meta((int) $about->ID, '_wp_page_template', 'templates/page-about.php');
  }

  // Portfolio gallery page (slug MUST differ from the 'portfolio' post type archive slug).
  $portfolio = get_page_by_path('our-portfolio') ?: get_page_by_path('portfolio-gallery');
  if (!$portfolio) {
    $portfolio_id = wp_insert_post([
      'post_title' => 'Portfolio',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => 'our-portfolio',
    ]);
    if ($portfolio_id && !is_wp_error($portfolio_id)) {
      update_post_meta((int) $portfolio_id, '_wp_page_template', 'templates/page-portfolio.php');
    }
  } else {
    update_post_meta((int) $portfolio->ID, '_wp_page_template', 'templates/page-portfolio.php');
  }

  if (get_option('page_on_front'))
    return;

  // Home page
  $home = get_page_by_path('home') ?: get_page_by_title('Home');
  if (!$home) {
    $home_id = wp_insert_post([
      'post_title' => 'Home',
      'post_status' => 'publish',
      'post_type' => 'page',
      'post_name' => 'home',
    ]);
  } else {
    $home_id = $home->ID;
  }

  update_option('show_on_front', 'page');
  update_option('page_on_front', (int) $home_id);
});

function slm_import_theme_image_attachment(int $post_id, string $relative_path, string $title = ''): int
{
  $relative_path = ltrim($relative_path, '/');
  if ($relative_path === '') {
    return 0;
  }

  $existing = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'meta_key' => '_slm_theme_seed_source',
    'meta_value' => $relative_path,
    'no_found_rows' => true,
  ]);
  if (!empty($existing)) {
    return (int) $existing[0];
  }

  $source_path = trailingslashit(get_template_directory()) . $relative_path;
  if (!is_file($source_path) || !is_readable($source_path)) {
    return 0;
  }

  $uploads = wp_upload_dir();
  if (!empty($uploads['error'])) {
    return 0;
  }

  $filename = wp_unique_filename($uploads['path'], wp_basename($source_path));
  $target_path = trailingslashit($uploads['path']) . $filename;
  if (!@copy($source_path, $target_path)) {
    return 0;
  }

  require_once ABSPATH . 'wp-admin/includes/image.php';
  require_once ABSPATH . 'wp-admin/includes/file.php';
  require_once ABSPATH . 'wp-admin/includes/media.php';

  $filetype = wp_check_filetype($filename, null);
  $attach_id = wp_insert_attachment([
    'post_mime_type' => (string) ($filetype['type'] ?? ''),
    'post_title' => $title !== '' ? $title : preg_replace('/\.[^.]+$/', '', $filename),
    'post_content' => '',
    'post_status' => 'inherit',
  ], $target_path, $post_id);

  if (!$attach_id || is_wp_error($attach_id)) {
    return 0;
  }

  $attach_data = wp_generate_attachment_metadata((int) $attach_id, $target_path);
  if (is_array($attach_data)) {
    wp_update_attachment_metadata((int) $attach_id, $attach_data);
  }

  update_post_meta((int) $attach_id, '_slm_theme_seed_source', $relative_path);
  return (int) $attach_id;
}

function slm_seed_portfolio_content(): void
{
  if (wp_installing()) {
    return;
  }

  $seed_version = '2026-02-launch';
  if (get_option('slm_portfolio_seed_version') === $seed_version) {
    return;
  }

  $existing = get_posts([
    'post_type' => 'portfolio',
    'post_status' => ['publish', 'future', 'draft', 'pending', 'private'],
    'posts_per_page' => 1,
    'fields' => 'ids',
    'no_found_rows' => true,
  ]);

  if (!empty($existing)) {
    update_option('slm_portfolio_seed_version', $seed_version, false);
    return;
  }

  $items = [
    [
      'title' => 'Riverfront Listing Showcase',
      'excerpt' => 'Launch-ready listing media with polished interiors, clean lighting, and sales-focused framing.',
      'content' => 'A full listing package with interior highlights, exterior curb-appeal coverage, and edit consistency for MLS and social use.',
      'featured' => 'assets/media/photos/01-0-front-exterior.jpg',
      'gallery' => [
        'assets/media/photos/02-12-living-room-5-of-6.jpg',
        'assets/media/photos/03-17-dining-room-1-of-4.jpg',
        'assets/media/photos/14-35-primary-bedroom-4-of-4.jpg',
      ],
    ],
    [
      'title' => 'Aerial + Exterior Property Campaign',
      'excerpt' => 'Drone-first campaign assets showing lot scale, orientation, and neighborhood context.',
      'content' => 'Built for listings where lot positioning and surrounding geography need to be clear to buyers at first glance.',
      'featured' => 'assets/media/drone-photos/03-1-aerial-front-exterior-1.jpg',
      'gallery' => [
        'assets/media/drone-photos/04-2-aerial-front-exterior-2.jpg',
        'assets/media/drone-photos/05-3-aerial-overview.jpg',
        'assets/media/drone-photos/07-4-aerial-rear-view-1.jpg',
      ],
    ],
    [
      'title' => 'Lifestyle + Virtual Staging Series',
      'excerpt' => 'Before-and-after style visual direction designed for high-engagement listing promotion.',
      'content' => 'Combines clean base photography with staged alternates for stronger perceived value and audience retention.',
      'featured' => 'assets/media/staged/01-13-living-room-5-of-6-virtually-staged.jpeg',
      'gallery' => [
        'assets/media/staged/04-26-breakfast-bar-virtually-staged.jpeg',
        'assets/media/staged/05-32-primary-bedroom-1-of-4-virtually-staged.jpeg',
        'assets/media/staged/08-46-lanai-virtually-staged.jpeg',
      ],
    ],
  ];

  $gallery_meta_key = function_exists('slm_portfolio_gallery_meta_key')
    ? slm_portfolio_gallery_meta_key()
    : 'slm_portfolio_gallery_ids';

  $created = 0;
  foreach ($items as $item) {
    $post_id = wp_insert_post([
      'post_type' => 'portfolio',
      'post_status' => 'publish',
      'post_title' => (string) $item['title'],
      'post_excerpt' => (string) $item['excerpt'],
      'post_content' => (string) $item['content'],
    ]);

    if (!$post_id || is_wp_error($post_id)) {
      continue;
    }

    $featured_id = slm_import_theme_image_attachment((int) $post_id, (string) $item['featured'], (string) $item['title']);
    if ($featured_id > 0) {
      set_post_thumbnail((int) $post_id, $featured_id);
    }

    $gallery_ids = [];
    foreach ((array) ($item['gallery'] ?? []) as $idx => $path) {
      $title = (string) $item['title'] . ' Gallery ' . ((int) $idx + 1);
      $image_id = slm_import_theme_image_attachment((int) $post_id, (string) $path, $title);
      if ($image_id > 0 && $image_id !== $featured_id) {
        $gallery_ids[] = $image_id;
      }
    }

    if (!empty($gallery_ids)) {
      update_post_meta((int) $post_id, $gallery_meta_key, implode(',', $gallery_ids));
    }

    $created++;
  }

  if ($created > 0) {
    update_option('slm_portfolio_seed_version', $seed_version, false);
  }
}

add_action('init', function () {
  register_post_type('portfolio', [
    'labels' => [
      'name' => 'Portfolio',
      'singular_name' => 'Portfolio Item',
      'add_new_item' => 'Add New Portfolio Item',
      'edit_item' => 'Edit Portfolio Item',
    ],
    'public' => true,
    'has_archive' => true,
    'rewrite' => ['slug' => 'portfolio'],
    'menu_icon' => 'dashicons-format-gallery',
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
    'show_in_rest' => true,
  ]);
});

add_action('init', 'slm_seed_portfolio_content', 20);

add_action('add_meta_boxes', function () {
  add_meta_box(
    'slm_portfolio_results_meta',
    'Portfolio Results Data',
    'slm_render_portfolio_results_meta_box',
    'portfolio',
    'normal',
    'default'
  );
});

function slm_render_portfolio_results_meta_box($post) {
  wp_nonce_field('slm_portfolio_results_save', 'slm_portfolio_results_nonce');
  $val = get_post_meta($post->ID, 'slm_portfolio_results', true);
  echo '<p><label for="slm_portfolio_results">Results or Proof Metric (e.g. "Sold in 3 days", "Generated 10 referrals"):</label></p>';
  echo '<input type="text" id="slm_portfolio_results" name="slm_portfolio_results" value="' . esc_attr($val) . '" style="width:100%;" />';
}

add_action('save_post_portfolio', function ($post_id) {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;
  if (!isset($_POST['slm_portfolio_results_nonce']) || !wp_verify_nonce($_POST['slm_portfolio_results_nonce'], 'slm_portfolio_results_save')) return;

  if (isset($_POST['slm_portfolio_results'])) {
    update_post_meta($post_id, 'slm_portfolio_results', sanitize_text_field($_POST['slm_portfolio_results']));
  }
});
