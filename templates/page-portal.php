<?php
/**
 * Template Name: Portal
 */
if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
  $requested_view = isset($_GET['view']) ? sanitize_key((string) $_GET['view']) : 'dashboard';
  $protected_views = ['place-order', 'my-orders', 'membership-shop', 'account'];
  if (in_array($requested_view, $protected_views, true)) {
    $redirect_target = add_query_arg('view', $requested_view, slm_portal_url());
    wp_safe_redirect(add_query_arg([
      'mode' => 'login',
      'redirect_to' => $redirect_target,
    ], slm_login_url()));
    exit;
  }
}

if (!is_user_logged_in()) {
  get_header();
  get_template_part('template-parts/site/guest-dashboard');
  get_footer();
  return;
}

if (slm_user_is_admin()) {
  wp_safe_redirect(slm_admin_portal_url());
  exit;
}

$view = isset($_GET['view']) ? sanitize_key($_GET['view']) : 'dashboard';
$allowed_views = ['dashboard', 'place-order', 'my-orders', 'membership-shop', 'account'];
if (!in_array($view, $allowed_views, true)) {
  $view = 'dashboard';
}
$portal_error = isset($_GET['error']) ? sanitize_key($_GET['error']) : '';
$change_request_notice = isset($_GET['change_request']) ? sanitize_key((string) $_GET['change_request']) : '';
$change_request_error = isset($_GET['change_request_error']) ? sanitize_key((string) $_GET['change_request_error']) : '';

$portal_url = slm_portal_url();
$portal_membership_shop_url = add_query_arg('view', 'membership-shop', $portal_url);
$user = wp_get_current_user();
$name = $user instanceof WP_User ? $user->display_name : 'there';
$user_email = $user instanceof WP_User ? (string) $user->user_email : '';
$subscription_summary = null;
if ($user instanceof WP_User && function_exists('slm_get_user_subscription_summary')) {
  $subscription_summary = slm_get_user_subscription_summary((int) $user->ID);
}
$credit_snapshot = null;
if ($user instanceof WP_User && is_array($subscription_summary) && !empty($subscription_summary['is_active']) && function_exists('slm_member_credits_balance_snapshot')) {
  $credit_snapshot = slm_member_credits_balance_snapshot((int) $user->ID);
}
$portal_member_exact_active = is_array($subscription_summary) && strtolower((string) ($subscription_summary['status'] ?? '')) === 'active';
$portal_member_state_issues = is_array($subscription_summary) && is_array($subscription_summary['state_issues'] ?? null) ? (array) $subscription_summary['state_issues'] : ['has_issue' => false, 'messages' => []];
$portal_member_has_state_issue = !empty($portal_member_state_issues['has_issue']);
$portal_member_state_issue_messages = is_array($portal_member_state_issues['messages'] ?? null) ? (array) $portal_member_state_issues['messages'] : [];
$subscription_notice = isset($_GET['subscription']) ? sanitize_key((string) $_GET['subscription']) : '';
$billing_notice = isset($_GET['billing']) ? sanitize_key((string) $_GET['billing']) : '';

$portal_social_memberships = [
  ['slug' => 'monthly-momentum', 'name' => 'Monthly Momentum', 'features' => ['45 minute session', '4 edited reels', '1 talking head video']],
  ['slug' => 'growth-engine', 'name' => 'Growth Engine', 'popular' => true, 'features' => ['1.5 hour session', '10 edited reels', '1 talking head video', '1 horizontal video', '5 branded Instagram posts']],
  ['slug' => 'brand-authority', 'name' => 'Brand Authority', 'features' => ['2 hour session', '15 edited reels', '2 talking head videos', '1 horizontal video', '8 branded Instagram posts']],
  ['slug' => 'elite-presence', 'name' => 'Elite Presence', 'features' => ['Half-day session', '25 edited reels', '2 talking head videos', '2 horizontal videos', '10 branded Instagram posts', 'Social media post plan']],
  ['slug' => 'vip-presence', 'name' => 'VIP Presence', 'features' => ['Full-day content shoot', '30 edited reels', '3 talking head videos', '3 horizontal videos', '15 branded Instagram posts', 'Social media post plan', 'Caption Suggestions', 'Strategic media analysis']],
];
$portal_agent_memberships = [
  ['slug' => 'agent-starting', 'name' => 'Starting', 'features' => ['1 listing shoot', '1 AI video for 1 listing', '1 staged photo or 1 dusk conversion']],
  ['slug' => 'agent-growing', 'name' => 'Growing', 'popular' => true, 'features' => ['3 listing shoots', '1 AI video for 1 listing', '1 agent intro video', '2 staged or dusk conversions', '3 branded Instagram posts']],
  ['slug' => 'agent-established', 'name' => 'Established', 'features' => ['5 listing shoots', '2 AI videos for listings', '2 agent intro videos', '1 horizontal video/tour', '5 branded Instagram posts']],
  ['slug' => 'agent-elite', 'name' => 'Elite', 'features' => ['9 listing shoots', '5 AI videos', '4 agent intro videos', '2 horizontal videos/tours', '10 branded Instagram posts', '4 staged or dusk conversions']],
  ['slug' => 'agent-top-tier', 'name' => 'Top-Tier', 'features' => ['15 listing shoots', '7 AI videos', '6 agent intro videos', '4 horizontal videos/tours', '15 branded Instagram posts', '8 staged or dusk conversions']],
];
$portal_membership_features_by_slug = [];
foreach (array_merge($portal_social_memberships, $portal_agent_memberships) as $membership_pkg) {
  if (!is_array($membership_pkg)) continue;
  $pkg_slug = sanitize_key((string) ($membership_pkg['slug'] ?? ''));
  if ($pkg_slug === '') continue;
  $portal_membership_features_by_slug[$pkg_slug] = is_array($membership_pkg['features'] ?? null) ? array_values((array) $membership_pkg['features']) : [];
}
$portal_social_term_options = [
  ['code' => 'm2m', 'label' => 'Month-to-Month'],
  ['code' => '6m', 'label' => '6-Month Agreement'],
  ['code' => '12m', 'label' => '12-Month Agreement'],
];
$portal_checkout_provider = function_exists('slm_subscriptions_checkout_provider') ? slm_subscriptions_checkout_provider() : 'square';
$portal_uses_square_checkout = $portal_checkout_provider === 'square';
$portal_subscriptions_enabled = function_exists('slm_subscriptions_can_accept_checkout') && slm_subscriptions_can_accept_checkout();
$current_member_plan_slug = is_array($subscription_summary) ? sanitize_key((string) ($subscription_summary['plan_slug'] ?? '')) : '';
$current_member_term_code = is_array($subscription_summary) ? sanitize_key((string) ($subscription_summary['term_code'] ?? '')) : '';
$membership_shop_card_cta = static function (array $pkg, string $default_term_code = '') use ($portal_subscriptions_enabled, $portal_member_exact_active, $portal_member_has_state_issue): array {
  $slug = sanitize_key((string) ($pkg['slug'] ?? ''));
  $term_code = sanitize_key($default_term_code);
  if ($portal_member_exact_active || $portal_member_has_state_issue || !$portal_subscriptions_enabled || $slug === '' || !function_exists('slm_subscriptions_start_url')) {
    return ['url' => '', 'enabled' => false];
  }
  return ['url' => slm_subscriptions_start_url($slug, $term_code), 'enabled' => true];
};
$membership_shop_plan_availability = static function (string $plan_slug) use ($portal_uses_square_checkout): array {
  $plan_slug = sanitize_key($plan_slug);
  $expected_terms = strpos($plan_slug, 'agent-') === 0 ? ['12m'] : ['m2m', '6m', '12m'];
  $fallback = [
    'plan_slug' => $plan_slug,
    'expected_terms' => $expected_terms,
    'available_terms' => $expected_terms,
    'missing_terms' => [],
    'has_any_checkout_term' => true,
    'is_checkout_ready' => true,
    'reason' => '',
  ];
  if ($plan_slug === '') {
    $fallback['available_terms'] = [];
    $fallback['missing_terms'] = $expected_terms;
    $fallback['has_any_checkout_term'] = false;
    $fallback['is_checkout_ready'] = false;
    $fallback['reason'] = 'missing_plan';
    return $fallback;
  }
  if (!$portal_uses_square_checkout || !function_exists('slm_subscriptions_square_plan_term_availability')) {
    return $fallback;
  }
  $data = slm_subscriptions_square_plan_term_availability($plan_slug);
  if (!is_array($data)) return $fallback;
  $data['expected_terms'] = is_array($data['expected_terms'] ?? null) ? array_values((array) $data['expected_terms']) : $fallback['expected_terms'];
  $data['available_terms'] = is_array($data['available_terms'] ?? null) ? array_values((array) $data['available_terms']) : [];
  $data['missing_terms'] = is_array($data['missing_terms'] ?? null) ? array_values((array) $data['missing_terms']) : [];
  $data['has_any_checkout_term'] = !empty($data['has_any_checkout_term']);
  $data['is_checkout_ready'] = !empty($data['is_checkout_ready']);
  $data['reason'] = sanitize_key((string) ($data['reason'] ?? ''));
  return array_merge($fallback, $data);
};
$portal_membership_rank = [];
foreach ($portal_social_memberships as $idx => $pkg) {
  $slug = sanitize_key((string) ($pkg['slug'] ?? ''));
  if ($slug !== '') $portal_membership_rank[$slug] = ['rank' => (int) $idx, 'family' => 'social'];
}
foreach ($portal_agent_memberships as $idx => $pkg) {
  $slug = sanitize_key((string) ($pkg['slug'] ?? ''));
  if ($slug !== '') $portal_membership_rank[$slug] = ['rank' => (int) $idx, 'family' => 'agent'];
}
$membership_change_request_button_label = static function (string $desired_plan_slug) use ($current_member_plan_slug, $portal_membership_rank): string {
  $desired_plan_slug = sanitize_key($desired_plan_slug);
  if ($desired_plan_slug === '') return 'Request Plan Change';
  if ($desired_plan_slug === $current_member_plan_slug) return 'Current Plan';
  $current = $portal_membership_rank[$current_member_plan_slug] ?? null;
  $desired = $portal_membership_rank[$desired_plan_slug] ?? null;
  if (!is_array($current) || !is_array($desired) || (string) ($current['family'] ?? '') !== (string) ($desired['family'] ?? '')) {
    return 'Request Plan Change';
  }
  return ((int) ($desired['rank'] ?? 0) > (int) ($current['rank'] ?? 0)) ? 'Request Upgrade' : 'Request Downgrade';
};
$membership_change_request_prefill_plan = sanitize_key((string) ($_GET['request_plan'] ?? ($current_member_plan_slug !== '' ? $current_member_plan_slug : '')));
$membership_change_request_prefill_term = sanitize_key((string) ($_GET['request_term'] ?? ($current_member_term_code !== '' ? $current_member_term_code : 'm2m')));
$membership_change_request_plan_choices = function_exists('slm_subscriptions_membership_change_request_plan_choices')
  ? (array) slm_subscriptions_membership_change_request_plan_choices()
  : [];
$membership_change_request_selected_plan_label = (string) ($membership_change_request_plan_choices[$membership_change_request_prefill_plan] ?? '');
$membership_change_request_selected_term_label = function_exists('slm_subscriptions_term_label')
  ? slm_subscriptions_term_label($membership_change_request_prefill_term)
  : strtoupper($membership_change_request_prefill_term ?: 'm2m');
$render_membership_change_request_form = static function (string $context_view = 'account') use ($portal_url, $subscription_summary, $membership_change_request_prefill_plan, $membership_change_request_prefill_term, $membership_change_request_plan_choices) {
  $plan_choices = $membership_change_request_plan_choices;
  if (!is_array($plan_choices) || $plan_choices === []) return;
  $current_plan = is_array($subscription_summary) ? sanitize_key((string) ($subscription_summary['plan_slug'] ?? '')) : '';
  $current_term = is_array($subscription_summary) ? sanitize_key((string) ($subscription_summary['term_code'] ?? '')) : '';
  $selected_plan = $membership_change_request_prefill_plan !== '' ? $membership_change_request_prefill_plan : ($current_plan !== '' ? $current_plan : (string) array_key_first($plan_choices));
  if (!isset($plan_choices[$selected_plan])) $selected_plan = (string) array_key_first($plan_choices);
  $selected_term = $membership_change_request_prefill_term !== '' ? $membership_change_request_prefill_term : ($current_term !== '' ? $current_term : (function_exists('slm_subscriptions_default_term_for_plan') ? slm_subscriptions_default_term_for_plan($selected_plan) : 'm2m'));
  if (function_exists('slm_subscriptions_square_expected_term_codes_for_plan')) {
    $allowed_terms = slm_subscriptions_square_expected_term_codes_for_plan($selected_plan);
    if (is_array($allowed_terms) && $allowed_terms !== [] && !in_array($selected_term, $allowed_terms, true)) {
      $selected_term = function_exists('slm_subscriptions_default_term_for_plan') ? slm_subscriptions_default_term_for_plan($selected_plan) : 'm2m';
    }
  }
  ?>
  <section id="membership-change-request-form" class="portal-card" style="margin-top:16px;">
    <div class="membership-change-request__header">
      <div>
        <h2 style="margin:0;">Request Plan Change</h2>
        <p class="sub" style="margin:6px 0 0;">Need to upgrade, downgrade, or switch agreement terms? Submit a request and Brittney will review and update your membership in Square/Aryeo.</p>
      </div>
      <div class="membership-change-request__pill">Manual review by Brittney</div>
    </div>
    <form method="post" class="membership-change-request-form">
      <?php wp_nonce_field('slm_portal_request_plan_change', 'slm_portal_request_plan_change_nonce'); ?>
      <input type="hidden" name="slm_portal_membership_form_action" value="request_plan_change" />
      <input type="hidden" name="slm_change_request_return_view" value="<?php echo esc_attr($context_view); ?>" />
      <div class="membership-change-request__grid">
        <div class="membership-change-request__field">
          <label for="slm_change_request_plan_<?php echo esc_attr($context_view); ?>">Desired Plan</label>
          <select id="slm_change_request_plan_<?php echo esc_attr($context_view); ?>" name="slm_change_request_plan">
            <?php foreach ($plan_choices as $slug => $label): ?>
              <option value="<?php echo esc_attr((string) $slug); ?>"<?php selected($selected_plan, (string) $slug); ?>><?php echo esc_html((string) $label); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="membership-change-request__field">
          <label for="slm_change_request_term_<?php echo esc_attr($context_view); ?>">Desired Agreement Term</label>
          <select id="slm_change_request_term_<?php echo esc_attr($context_view); ?>" name="slm_change_request_term">
            <option value="m2m"<?php selected($selected_term, 'm2m'); ?>>Month-to-Month</option>
            <option value="6m"<?php selected($selected_term, '6m'); ?>>6-Month Agreement</option>
            <option value="12m"<?php selected($selected_term, '12m'); ?>>12-Month Agreement</option>
          </select>
        </div>
      </div>
      <div class="membership-change-request__helper">
        <strong>Before you submit</strong>
        <p class="sub" style="margin:6px 0 0;">Agent plans are 12-month only. If you pick an incompatible plan/term combo, the request will be rejected and you can resubmit.</p>
      </div>
      <div class="membership-change-request__field membership-change-request__field--full">
        <label for="slm_change_request_notes_<?php echo esc_attr($context_view); ?>">Notes (optional)</label>
        <textarea id="slm_change_request_notes_<?php echo esc_attr($context_view); ?>" name="slm_change_request_notes" rows="4" placeholder="Anything Brittney should know about timing, plan goals, or questions?"></textarea>
      </div>
      <div class="membership-change-request__actions">
        <button class="btn" type="submit">Submit Change Request</button>
        <a class="btn btn--secondary" href="<?php echo esc_url(add_query_arg('view', 'account', $portal_url)); ?>">View Account</a>
      </div>
    </form>
  </section>
  <?php
};

$recent_orders = [];
$aryeo_orders = null;
if (function_exists('slm_aryeo_is_configured') && slm_aryeo_is_configured() && $user_email !== '') {
  $aryeo_orders = slm_aryeo_get_orders_for_email($user_email);
  if (is_array($aryeo_orders)) {
    if (function_exists('slm_aryeo_normalize_orders')) {
      $aryeo_orders = slm_aryeo_normalize_orders($aryeo_orders);
    }
    $recent_orders = array_slice($aryeo_orders, 0, 10);
  }
}

$status_class = static function (string $status): string {
  if ($status === 'completed') return 'is-completed';
  if ($status === 'in-progress') return 'is-progress';
  if ($status === 'scheduled') return 'is-scheduled';
  return 'is-pending';
};

$status_label = static function (string $status): string {
  return ucwords(str_replace('-', ' ', $status));
};

$display_status = static function ($status): string {
  $s = is_string($status) ? $status : '';
  if ($s === '') return 'pending';
  $s = strtolower(trim($s));
  if (in_array($s, ['completed', 'complete', 'delivered'], true)) return 'completed';
  if (in_array($s, ['in-progress', 'in_progress', 'processing'], true)) return 'in-progress';
  if (in_array($s, ['scheduled', 'scheduled_for'], true)) return 'scheduled';
  return $s;
};

$payment_label = static function (array $order): string {
  $payment_status = (string) ($order['payment_status'] ?? 'unknown');
  $due_amount = (float) ($order['due_amount'] ?? 0);
  if ($payment_status === 'paid') {
    return 'Paid';
  }
  if ($payment_status === 'partial') {
    return 'Partial (' . '$' . number_format($due_amount, 2) . ' due)';
  }
  if ($payment_status === 'due') {
    return 'Due ' . '$' . number_format($due_amount, 2);
  }
  if ($payment_status === 'not-set') {
    return 'Not set';
  }
  return 'Unknown';
};

$order_counts = [
  'total' => is_array($aryeo_orders) ? count($aryeo_orders) : 0,
  'in_progress' => 0,
  'completed' => 0,
  'scheduled' => 0,
  'pending' => 0,
];

if (is_array($aryeo_orders)) {
  foreach ($aryeo_orders as $order) {
    $normalized = $display_status($order['status'] ?? $order['order_status'] ?? '');
    if ($normalized === 'in-progress') {
      $order_counts['in_progress']++;
      continue;
    }
    if ($normalized === 'completed') {
      $order_counts['completed']++;
      continue;
    }
    if ($normalized === 'scheduled') {
      $order_counts['scheduled']++;
      continue;
    }
    $order_counts['pending']++;
  }
}

get_header();
?>

<div class="portal-shell">
  <aside class="portal-sidebar">
    <div class="portal-brand">
      <span class="portal-brand__logo">CP</span>
      <div>
        <strong>Client Portal</strong>
        <small>Customer Workspace</small>
      </div>
    </div>

    <nav class="portal-nav" aria-label="Customer Portal">
      <a class="<?php echo $view === 'dashboard' ? 'is-active' : ''; ?>"<?php echo $view === 'dashboard' ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('view', 'dashboard', $portal_url)); ?>">Dashboard</a>
      <a class="<?php echo $view === 'place-order' ? 'is-active' : ''; ?>"<?php echo $view === 'place-order' ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('view', 'place-order', $portal_url)); ?>">Submit New Job</a>
      <a class="<?php echo $view === 'my-orders' ? 'is-active' : ''; ?>"<?php echo $view === 'my-orders' ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('view', 'my-orders', $portal_url)); ?>">Previous Jobs</a>
      <a class="<?php echo $view === 'membership-shop' ? 'is-active' : ''; ?>"<?php echo $view === 'membership-shop' ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url($portal_membership_shop_url); ?>">Memberships</a>
      <a class="<?php echo $view === 'account' ? 'is-active' : ''; ?>"<?php echo $view === 'account' ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('view', 'account', $portal_url)); ?>">Account</a>
    </nav>

    <div class="portal-sidebar__footer">
      <a class="btn btn--secondary" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
    </div>
  </aside>

  <main class="portal-main">
    <div class="portal-wrap">
      <section class="portal-toolbar" aria-label="Quick Actions">
        <a class="portal-toolbar__pill <?php echo $view === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'dashboard', $portal_url)); ?>">Overview</a>
        <a class="portal-toolbar__pill <?php echo $view === 'place-order' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'place-order', $portal_url)); ?>">Submit New Job</a>
        <a class="portal-toolbar__pill <?php echo $view === 'my-orders' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'my-orders', $portal_url)); ?>">Previous Jobs</a>
        <a class="portal-toolbar__pill <?php echo $view === 'membership-shop' ? 'is-active' : ''; ?>" href="<?php echo esc_url($portal_membership_shop_url); ?>">Memberships</a>
        <a class="portal-toolbar__pill <?php echo $view === 'account' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'account', $portal_url)); ?>">Profile</a>
      </section>

      <?php if ($portal_error !== ''): ?>
        <section class="portal-section" style="padding-bottom:0;">
          <p class="portal-feedback portal-feedback--err" style="margin:0;">
            <?php
              $error_messages = [
                'invalid_request' => 'Your request could not be verified. Please try again.',
                'session_failed' => 'We could not start your order session. Please try again or contact support.',
                'invalid_url' => 'The ordering service returned an invalid link. Please try again.',
                'subscription_unavailable' => 'That membership plan or agreement term is not available for checkout yet. Please choose another option or contact support.',
                'already_active_use_change_request' => 'You already have an active membership. To change plans, request a change.',
                'multiple_memberships_detected' => 'Multiple memberships detected - please contact support.',
                'checkout_failed' => 'Checkout could not be completed. Please try again or contact support.',
                'billing_failed' => 'Billing action could not be completed. Please try again or contact support.',
                'commitment_active' => 'Your membership is still within the minimum commitment period and cannot be canceled yet.',
              ];
              echo esc_html($error_messages[$portal_error] ?? 'Something went wrong. Please try again.');
            ?>
          </p>
        </section>
      <?php endif; ?>

      <?php if ($change_request_notice !== ''): ?>
        <section class="portal-section" style="padding-bottom:0;">
          <?php
            $change_request_messages = [
              'submitted' => ['class' => 'portal-feedback', 'text' => 'Your plan change request was submitted. Brittney will review it and update you soon.'],
              'error' => ['class' => 'portal-feedback portal-feedback--err', 'text' => 'Your change request could not be submitted. Please check your selection and try again.'],
            ];
            $change_notice = $change_request_messages[$change_request_notice] ?? ['class' => 'portal-feedback', 'text' => 'Membership request updated.'];
          ?>
          <p class="<?php echo esc_attr((string) $change_notice['class']); ?>" style="margin:0;">
            <?php echo esc_html((string) $change_notice['text']); ?>
            <?php if ($change_request_notice === 'error' && $change_request_error !== ''): ?>
              <span style="display:block; margin-top:4px; opacity:.9;">Code: <?php echo esc_html($change_request_error); ?></span>
            <?php endif; ?>
          </p>
        </section>
      <?php endif; ?>

      <?php if ($view === 'dashboard'): ?>
        <section class="portal-section">
          <h1>Welcome back, <?php echo esc_html($name); ?></h1>
          <p class="sub">Here is a clear snapshot of current orders, delivery progress, and next actions.</p>
        </section>

        <section class="portal-stats portal-stats--four">
          <article class="portal-card">
            <p>Total Orders</p>
            <strong><?php echo esc_html((string) $order_counts['total']); ?></strong>
          </article>
          <article class="portal-card">
            <p>In Progress</p>
            <strong><?php echo esc_html((string) $order_counts['in_progress']); ?></strong>
          </article>
          <article class="portal-card">
            <p>Completed</p>
            <strong><?php echo esc_html((string) $order_counts['completed']); ?></strong>
          </article>
          <article class="portal-card">
            <p>Scheduled</p>
            <strong><?php echo esc_html((string) $order_counts['scheduled']); ?></strong>
          </article>
        </section>

        <section class="portal-actions">
          <a class="portal-action portal-action--primary" href="<?php echo esc_url(add_query_arg('view', 'place-order', $portal_url)); ?>">
            <h2>Submit New Job</h2>
            <p>Start a new media request using the Aryeo-hosted order flow.</p>
          </a>
          <a class="portal-action" href="<?php echo esc_url(add_query_arg('view', 'my-orders', $portal_url)); ?>">
            <h2>Previous Jobs</h2>
            <p>Track current jobs and review completed work.</p>
          </a>
          <?php if (!is_array($subscription_summary) || empty($subscription_summary['is_active'])): ?>
            <a class="portal-action" href="<?php echo esc_url($portal_membership_shop_url); ?>">
              <h2>Subscribe Now</h2>
              <p>Choose a membership plan and agreement term to unlock monthly credits and member pricing.</p>
            </a>
          <?php else: ?>
            <a class="portal-action" href="<?php echo esc_url($portal_membership_shop_url); ?>">
              <h2>Membership & Credits</h2>
              <p>Review your current membership, compare plans, and manage renewal options.</p>
            </a>
          <?php endif; ?>
        </section>

        <?php if (is_array($subscription_summary) && !empty($subscription_summary['is_active'])): ?>
          <section class="portal-tableCard">
            <div class="portal-tableCard__head">
              <h2>Membership Status</h2>
              <a href="<?php echo esc_url($portal_membership_shop_url); ?>">Open Shop</a>
            </div>
            <div style="padding:0 16px 16px;">
              <p style="margin:0 0 8px;"><strong><?php echo esc_html((string) ($subscription_summary['plan_label'] ?? 'Membership')); ?></strong> (<?php echo esc_html((string) ($subscription_summary['term_label'] ?? 'N/A')); ?>)</p>
              <p style="margin:0 0 8px;">Status: <?php echo esc_html((string) ($subscription_summary['status_label'] ?? 'Unknown')); ?></p>
              <p style="margin:0;">Next Billing: <?php echo esc_html((string) ($subscription_summary['current_period_end_label'] ?? 'N/A')); ?></p>
            </div>
          </section>
        <?php endif; ?>

        <?php if (is_array($credit_snapshot) && !empty($credit_snapshot['balances'])): ?>
          <section class="portal-tableCard">
            <div class="portal-tableCard__head">
              <h2>Credit Balances</h2>
              <a href="<?php echo esc_url($portal_membership_shop_url); ?>">View Membership</a>
            </div>
            <div class="table-scroll">
              <table class="table" aria-label="Membership Credit Balances">
                <thead>
                  <tr><th>Credit</th><th>Remaining</th></tr>
                </thead>
                <tbody>
                  <?php foreach ((array) $credit_snapshot['balances'] as $balance): ?>
                    <?php if (!is_array($balance)) continue; ?>
                    <tr>
                      <td><?php echo esc_html((string) ($balance['label'] ?? 'Credit')); ?></td>
                      <td><?php echo esc_html((string) (int) ($balance['qty'] ?? 0)); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </section>
        <?php endif; ?>

        <section class="portal-tableCard">
          <div class="portal-tableCard__head">
            <h2>Recent Orders</h2>
            <a href="<?php echo esc_url(add_query_arg('view', 'my-orders', $portal_url)); ?>">View All</a>
          </div>
          <div class="table-scroll">
            <table class="table" aria-label="Recent Orders">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Service</th>
                  <th>Address</th>
                  <th>Status</th>
                  <th>Price</th>
                  <th>Payment</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (is_wp_error($aryeo_orders)): ?>
                  <tr><td colspan="8">Unable to load orders: <?php echo esc_html($aryeo_orders->get_error_message()); ?></td></tr>
                <?php elseif (empty($recent_orders)): ?>
                  <tr><td colspan="8">No orders yet. When you submit a job, it will show up here.</td></tr>
                <?php else: ?>
                  <?php foreach ($recent_orders as $order): ?>
                    <?php
                      $order_number = (string) ($order['id'] ?? $order['raw_id'] ?? '');
                      $status_raw = $display_status($order['status'] ?? '');
                      $service_name = (string) ($order['service'] ?? 'Order');
                      $address = (string) ($order['address'] ?? '');
                      $total = (string) ($order['price'] ?? '');
                      $created = (string) ($order['date'] ?? $order['created_at'] ?? $order['updated_at'] ?? '');
                      $created_label = $created !== '' ? date_i18n('M j, Y', strtotime($created)) : '';
                      $can_pay = !empty($order['client_can_pay']);
                      $can_view_delivery = !empty($order['client_can_view_delivery']);
                      $delivery_locked = !empty($order['delivery_locked_for_payment']);
                      $payment_url = (string) ($order['payment_url'] ?? '');
                      $delivery_url = (string) ($order['delivery_url'] ?? '');
                    ?>
                    <tr>
                      <td><?php echo esc_html($order_number); ?></td>
                      <td><?php echo esc_html($service_name); ?></td>
                      <td><?php echo esc_html($address); ?></td>
                      <td><span class="status-pill <?php echo esc_attr($status_class($status_raw)); ?>"><?php echo esc_html($status_label($status_raw)); ?></span></td>
                      <td><?php echo esc_html($total !== '' ? $total : ''); ?></td>
                      <td><?php echo esc_html($payment_label($order)); ?></td>
                      <td><?php echo esc_html($created_label); ?></td>
                      <td>
                        <?php if ($can_pay && $payment_url !== ''): ?>
                          <a class="btn btn--secondary" href="<?php echo esc_url($payment_url); ?>" target="_blank" rel="noopener">Pay Now</a>
                        <?php elseif ($can_view_delivery && $delivery_url !== ''): ?>
                          <a class="btn btn--secondary" href="<?php echo esc_url($delivery_url); ?>" target="_blank" rel="noopener">View Delivery</a>
                        <?php elseif ($delivery_locked): ?>
                          <span class="muted">Complete payment to access delivery</span>
                        <?php else: ?>
                          <span class="muted">n/a</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($view === 'place-order'): ?>
        <section class="portal-section">
          <h1>Submit New Job</h1>
          <p class="sub">Start a new job request in our Aryeo-hosted order form. Preferred date/time is requested there, is not guaranteed, and will be confirmed by phone.</p>
        </section>

        <?php if (function_exists('slm_aryeo_is_configured') && slm_aryeo_is_configured()): ?>
          <section class="portal-actions" style="grid-template-columns: 1fr;">
            <a class="portal-action portal-action--primary" href="<?php echo esc_url(slm_aryeo_start_order_url()); ?>">
              <h2>Open Aryeo Order Form</h2>
              <p>Continue to the hosted ordering flow to submit your job details and scheduling request.</p>
            </a>
            <a class="portal-action" href="<?php echo esc_url(home_url('/services/')); ?>">
              <h2>Review Service Menu</h2>
              <p>Compare available services before you start checkout.</p>
            </a>
          </section>
        <?php else: ?>
          <section class="portal-actions" style="grid-template-columns: 1fr;">
            <div class="portal-card">
              <h2 style="margin:0 0 8px;">Ordering Not Configured Yet</h2>
              <p style="margin:0;">An admin needs to add the Aryeo API key and default order form ID in <strong>Settings &gt; Aryeo Integration</strong>.</p>
            </div>
          </section>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($view === 'my-orders'): ?>
        <section class="portal-section">
          <h1>Previous Jobs</h1>
          <p class="sub">Track active jobs, payment status, and delivered media in one place.</p>
        </section>
        <section class="portal-tableCard">
          <div class="table-scroll">
            <table class="table" aria-label="My Orders">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Service</th>
                  <th>Address</th>
                  <th>Status</th>
                  <th>Price</th>
                  <th>Payment</th>
                  <th>Delivery</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (is_wp_error($aryeo_orders)): ?>
                  <tr><td colspan="8">Unable to load orders: <?php echo esc_html($aryeo_orders->get_error_message()); ?></td></tr>
                <?php elseif (empty($aryeo_orders) || !is_array($aryeo_orders)): ?>
                  <tr><td colspan="8">No jobs yet. Use "Submit New Job" to get started.</td></tr>
                <?php else: ?>
                  <?php foreach ($aryeo_orders as $order): ?>
                    <?php
                      $order_number = (string) ($order['id'] ?? $order['raw_id'] ?? '');
                      $status_raw = $display_status($order['status'] ?? '');
                      $service_name = (string) ($order['service'] ?? 'Order');
                      $address = (string) ($order['address'] ?? '');
                      $total = (string) ($order['price'] ?? '');
                      $delivery = (string) ($order['delivery_at'] ?? $order['updated_at'] ?? '');
                      $delivery_label = $delivery !== '' ? date_i18n('M j, Y', strtotime($delivery)) : '';
                      $can_pay = !empty($order['client_can_pay']);
                      $can_view_delivery = !empty($order['client_can_view_delivery']);
                      $delivery_locked = !empty($order['delivery_locked_for_payment']);
                      $payment_url = (string) ($order['payment_url'] ?? '');
                      $delivery_url = (string) ($order['delivery_url'] ?? '');
                    ?>
                    <tr>
                      <td><?php echo esc_html($order_number); ?></td>
                      <td><?php echo esc_html($service_name); ?></td>
                      <td><?php echo esc_html($address); ?></td>
                      <td><span class="status-pill <?php echo esc_attr($status_class($status_raw)); ?>"><?php echo esc_html($status_label($status_raw)); ?></span></td>
                      <td><?php echo esc_html($total !== '' ? $total : ''); ?></td>
                      <td><?php echo esc_html($payment_label($order)); ?></td>
                      <td><?php echo esc_html($delivery_label); ?></td>
                      <td>
                        <?php if ($can_pay && $payment_url !== ''): ?>
                          <a class="btn btn--secondary" href="<?php echo esc_url($payment_url); ?>" target="_blank" rel="noopener">Pay Now</a>
                        <?php elseif ($can_view_delivery && $delivery_url !== ''): ?>
                          <a class="btn btn--secondary" href="<?php echo esc_url($delivery_url); ?>" target="_blank" rel="noopener">View Delivery</a>
                        <?php elseif ($delivery_locked): ?>
                          <span class="muted">Complete payment to access delivery</span>
                        <?php else: ?>
                          <span class="muted">n/a</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($view === 'membership-shop'): ?>
        <section class="portal-section">
          <h1>Memberships</h1>
          <p class="sub">Choose a membership plan inside your portal. Select a plan and agreement term, then continue to Square checkout.</p>
        </section>

        <?php if (is_array($subscription_summary)): ?>
          <section class="portal-tableCard">
            <div class="portal-tableCard__head">
              <h2>Current Membership</h2>
              <a href="<?php echo esc_url(add_query_arg('view', 'account', $portal_url)); ?>">Account Details</a>
            </div>
            <div style="padding:0 16px 16px;">
              <p style="margin:0 0 8px;"><strong><?php echo esc_html((string) ($subscription_summary['plan_label'] ?? 'No active plan')); ?></strong><?php if (!empty($subscription_summary['term_label'])): ?> (<?php echo esc_html((string) $subscription_summary['term_label']); ?>)<?php endif; ?></p>
              <p style="margin:0 0 8px;">Status: <?php echo esc_html((string) ($subscription_summary['status_label'] ?? 'Not Subscribed')); ?></p>
              <p style="margin:0 0 8px;">Next Billing: <?php echo esc_html((string) ($subscription_summary['current_period_end_label'] ?? 'N/A')); ?></p>
              <p style="margin:0;">Commitment End: <?php echo esc_html((string) ($subscription_summary['commitment_end_label'] ?? 'N/A')); ?></p>
            </div>
          </section>
        <?php endif; ?>

        <?php if ($portal_member_has_state_issue): ?>
          <section class="portal-section" style="padding-top:0;">
            <p class="portal-feedback portal-feedback--err" style="margin:0;">
              Multiple memberships detected - please contact support.
              <?php if ($portal_member_state_issue_messages !== []): ?>
                <span style="display:block; margin-top:4px;"><?php echo esc_html((string) $portal_member_state_issue_messages[0]); ?></span>
              <?php endif; ?>
            </p>
          </section>
        <?php endif; ?>

        <?php if ($portal_member_exact_active): ?>
          <section class="portal-section" style="padding-top:0;">
            <p class="portal-feedback" style="margin:0;">You already have an active membership. To change plans, request a change.</p>
          </section>
        <?php endif; ?>

        <?php if (!$portal_subscriptions_enabled): ?>
          <section class="portal-tableCard">
            <div style="padding:16px;">
              <h2 style="margin:0 0 8px;">Membership Checkout Not Ready</h2>
              <p style="margin:0;">An admin needs to finish Square membership plan and term option mapping in <strong>Settings &gt; SLM Subscriptions</strong>.</p>
            </div>
          </section>
        <?php else: ?>
          <section class="portal-tableCard">
            <div class="portal-tableCard__head">
              <h2>Choose Agreement Term</h2>
              <span class="muted">Applies to Social Memberships</span>
            </div>
            <div style="padding:0 16px 16px;">
              <div class="membership-shop__termChips" role="radiogroup" aria-label="Social membership agreement term">
                <?php foreach ($portal_social_term_options as $idx => $term_option): ?>
                  <button
                    type="button"
                    class="membership-shop__termChip<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                    data-term-chip="<?php echo esc_attr((string) $term_option['code']); ?>"
                    role="radio"
                    aria-checked="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
                  >
                    <?php echo esc_html((string) $term_option['label']); ?>
                  </button>
                <?php endforeach; ?>
              </div>
              <p class="sub" style="margin:10px 0 0;">Month-to-month social memberships still require a 3-month minimum commitment. 12-month agreements include 1 complimentary listing shoot per month.</p>
            </div>
          </section>

          <section class="membership-shop">
            <div class="membership-shop__main">
              <section class="portal-tableCard">
                <div class="portal-tableCard__head">
                  <h2>Social Memberships</h2>
                  <span class="muted">Term selected above</span>
                </div>
                <div style="padding:0 16px 16px;">
                  <div class="pkg-grid">
                    <?php foreach ($portal_social_memberships as $pkg): ?>
                      <?php
                        $plan_slug = sanitize_key((string) ($pkg['slug'] ?? ''));
                        $cta = $membership_shop_card_cta($pkg, 'm2m');
                        $availability = $membership_shop_plan_availability($plan_slug);
                        $available_terms = array_values(array_unique(array_map('sanitize_key', (array) ($availability['available_terms'] ?? []))));
                        $missing_terms = array_values(array_unique(array_map('sanitize_key', (array) ($availability['missing_terms'] ?? []))));
                        $has_any_checkout_term = !empty($availability['has_any_checkout_term']);
                        $button_enabled = !empty($cta['enabled']) && $has_any_checkout_term;
                        $is_current = $current_member_plan_slug === $plan_slug && !empty($subscription_summary['is_active']);
                      ?>
                      <div class="pkg-card<?php echo !empty($pkg['popular']) ? ' pkg-card--popular' : ''; ?><?php echo $is_current ? ' membership-shop__card--current' : ''; ?>">
                        <?php if (!empty($pkg['popular'])): ?><div class="pkg-badge">Most Popular</div><?php endif; ?>
                        <?php if ($is_current): ?><div class="membership-shop__statusTag">Current Plan</div><?php endif; ?>
                        <h3 class="pkg-title"><?php echo esc_html((string) ($pkg['name'] ?? '')); ?></h3>
                        <ul class="pkg-features">
                          <?php foreach ((array) ($pkg['features'] ?? []) as $feature): ?>
                            <li>
                              <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                              <span><?php echo esc_html((string) $feature); ?></span>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                        <p class="sub membership-shop__termPreview" data-term-preview="<?php echo esc_attr((string) ($pkg['slug'] ?? '')); ?>" style="margin:10px 0 12px; font-size:.9rem;">Agreement: Month-to-Month</p>
                        <?php if ($portal_member_exact_active): ?>
                          <?php if ($is_current): ?>
                            <button type="button" class="btn btn--secondary" disabled aria-disabled="true">Current Plan</button>
                          <?php else: ?>
                            <a class="btn btn--secondary" href="<?php echo esc_url(add_query_arg(['view' => 'membership-shop', 'request_plan' => $plan_slug], $portal_url)); ?>#membership-change-request-form"><?php echo esc_html($membership_change_request_button_label($plan_slug)); ?></a>
                          <?php endif; ?>
                        <?php else: ?>
                          <button
                            type="button"
                            class="btn btn--secondary membership-shop__select"
                            data-plan-name="<?php echo esc_attr((string) ($pkg['name'] ?? '')); ?>"
                            data-plan-slug="<?php echo esc_attr($plan_slug); ?>"
                            data-plan-family="social"
                            data-term-code="m2m"
                            data-checkout-url="<?php echo esc_attr((string) ($cta['url'] ?? '')); ?>"
                            data-available-terms="<?php echo esc_attr(implode(',', $available_terms)); ?>"
                            data-unavailable-terms="<?php echo esc_attr(implode(',', $missing_terms)); ?>"
                            data-has-checkout-term="<?php echo $has_any_checkout_term ? '1' : '0'; ?>"
                            data-is-current="<?php echo $is_current ? '1' : '0'; ?>"
                            <?php echo !$button_enabled ? 'disabled aria-disabled="true"' : ''; ?>
                          >
                            Select Plan
                          </button>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </section>

              <section class="portal-tableCard">
                <div class="portal-tableCard__head">
                  <h2>Listings-Agent Memberships</h2>
                  <span class="muted">Fixed 12-Month Agreement</span>
                </div>
                <div style="padding:0 16px 16px;">
                  <div class="pkg-grid">
                    <?php foreach ($portal_agent_memberships as $pkg): ?>
                      <?php
                        $plan_slug = sanitize_key((string) ($pkg['slug'] ?? ''));
                        $cta = $membership_shop_card_cta($pkg, '12m');
                        $availability = $membership_shop_plan_availability($plan_slug);
                        $available_terms = array_values(array_unique(array_map('sanitize_key', (array) ($availability['available_terms'] ?? []))));
                        $missing_terms = array_values(array_unique(array_map('sanitize_key', (array) ($availability['missing_terms'] ?? []))));
                        $has_any_checkout_term = !empty($availability['has_any_checkout_term']);
                        $button_enabled = !empty($cta['enabled']) && $has_any_checkout_term;
                        $is_current = $current_member_plan_slug === $plan_slug && !empty($subscription_summary['is_active']);
                      ?>
                      <div class="pkg-card<?php echo !empty($pkg['popular']) ? ' pkg-card--popular' : ''; ?><?php echo $is_current ? ' membership-shop__card--current' : ''; ?>">
                        <?php if (!empty($pkg['popular'])): ?><div class="pkg-badge">Most Popular</div><?php endif; ?>
                        <?php if ($is_current): ?><div class="membership-shop__statusTag">Current Plan</div><?php endif; ?>
                        <h3 class="pkg-title"><?php echo esc_html((string) ($pkg['name'] ?? '')); ?></h3>
                        <ul class="pkg-features">
                          <?php foreach ((array) ($pkg['features'] ?? []) as $feature): ?>
                            <li>
                              <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                              <span><?php echo esc_html((string) $feature); ?></span>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                        <p class="sub" style="margin:10px 0 12px; font-size:.9rem;">Agreement: 12-Month</p>
                        <?php if ($portal_member_exact_active): ?>
                          <?php if ($is_current): ?>
                            <button type="button" class="btn btn--secondary" disabled aria-disabled="true">Current Plan</button>
                          <?php else: ?>
                            <a class="btn btn--secondary" href="<?php echo esc_url(add_query_arg(['view' => 'membership-shop', 'request_plan' => $plan_slug, 'request_term' => '12m'], $portal_url)); ?>#membership-change-request-form"><?php echo esc_html($membership_change_request_button_label($plan_slug)); ?></a>
                          <?php endif; ?>
                        <?php else: ?>
                          <button
                            type="button"
                            class="btn btn--secondary membership-shop__select"
                            data-plan-name="<?php echo esc_attr((string) ($pkg['name'] ?? '')); ?>"
                            data-plan-slug="<?php echo esc_attr($plan_slug); ?>"
                            data-plan-family="agent"
                            data-term-code="12m"
                            data-checkout-url="<?php echo esc_attr((string) ($cta['url'] ?? '')); ?>"
                            data-available-terms="<?php echo esc_attr(implode(',', $available_terms)); ?>"
                            data-unavailable-terms="<?php echo esc_attr(implode(',', $missing_terms)); ?>"
                            data-has-checkout-term="<?php echo $has_any_checkout_term ? '1' : '0'; ?>"
                            data-is-current="<?php echo $is_current ? '1' : '0'; ?>"
                            <?php echo !$button_enabled ? 'disabled aria-disabled="true"' : ''; ?>
                          >
                            Select Plan
                          </button>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </section>
            </div>

            <aside class="membership-shop__summary portal-card" aria-live="polite">
              <?php if ($portal_member_exact_active): ?>
                <?php
                  $change_request_plan_label = $membership_change_request_selected_plan_label !== '' ? $membership_change_request_selected_plan_label : 'Select a plan card';
                  $change_request_term_label = $membership_change_request_selected_plan_label !== '' ? $membership_change_request_selected_term_label : '-';
                  $change_request_action_label = $membership_change_request_button_label($membership_change_request_prefill_plan);
                  $same_plan_request = $membership_change_request_prefill_plan !== '' && $membership_change_request_prefill_plan === $current_member_plan_slug;
                ?>
                <h2 style="margin-top:0;">Change Request</h2>
                <p class="sub" style="margin-top:0;">Pick a plan card to prefill the request form, then submit your request for Brittney to review.</p>
                <div class="membership-shop__summaryRows membership-shop__summaryRows--change">
                  <div><span>Current</span><strong><?php echo esc_html((string) ($subscription_summary['plan_label'] ?? 'No membership')); ?></strong></div>
                  <div><span>Requested</span><strong><?php echo esc_html($change_request_plan_label); ?></strong></div>
                  <div><span>Term</span><strong><?php echo esc_html($change_request_term_label); ?></strong></div>
                  <div><span>Action</span><strong><?php echo esc_html($same_plan_request ? 'Change term / submit note' : $change_request_action_label); ?></strong></div>
                </div>
                <div class="membership-shop__summaryCallout">
                  <strong>Next step</strong>
                  <p class="sub" style="margin:6px 0 0;">Use any plan card to prefill the request form below. We are not starting a new checkout while your membership is active.</p>
                </div>
                <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                  <a class="btn" href="#membership-change-request-form">Open Request Form</a>
                  <a class="btn btn--secondary" href="<?php echo esc_url(add_query_arg('view', 'account', $portal_url)); ?>">View Account</a>
                  <?php if (is_array($subscription_summary) && !empty($subscription_summary['can_manage_billing']) && !empty($subscription_summary['manage_billing_url'])): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url((string) ($subscription_summary['manage_billing_url'] ?? '')); ?>"><?php echo esc_html((string) ($subscription_summary['manage_billing_label'] ?? 'Manage Billing')); ?></a>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <h2 style="margin-top:0;">Checkout Summary</h2>
                <p class="sub" style="margin-top:0;">Select a plan to continue to Square checkout.</p>
                <div class="membership-shop__summaryRows">
                  <div><span>Plan</span><strong id="membership-shop-selected-plan">None selected</strong></div>
                  <div><span>Term</span><strong id="membership-shop-selected-term">-</strong></div>
                  <div><span>Billing</span><strong id="membership-shop-selected-price">Shown in Square checkout</strong></div>
                </div>
                <p id="membership-shop-term-note" class="sub" style="margin:12px 0 0;">Select a social or agent plan to preview the agreement note before checkout.</p>
                <?php if (!$portal_member_has_state_issue): ?>
                  <div style="margin-top:16px;">
                    <a id="membership-shop-continue" class="btn btn--accent is-disabled" aria-disabled="true" href="#" onclick="return false;">Continue to Checkout</a>
                  </div>
                <?php else: ?>
                  <div style="margin-top:16px;">
                    <a id="membership-shop-continue" class="btn btn--accent is-disabled" aria-disabled="true" href="#" onclick="return false;">Checkout Disabled</a>
                  </div>
                <?php endif; ?>
                <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap;">
                  <a class="btn btn--secondary" href="<?php echo esc_url(add_query_arg('view', 'account', $portal_url)); ?>">View Account</a>
                  <?php if (is_array($subscription_summary) && !empty($subscription_summary['can_manage_billing']) && !empty($subscription_summary['manage_billing_url'])): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url((string) ($subscription_summary['manage_billing_url'] ?? '')); ?>"><?php echo esc_html((string) ($subscription_summary['manage_billing_label'] ?? 'Manage Billing')); ?></a>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </aside>
          </section>
          <?php if ($portal_member_exact_active): ?>
            <?php $render_membership_change_request_form('membership-shop'); ?>
          <?php endif; ?>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($view === 'account'): ?>
        <section class="portal-section">
          <h1>Account</h1>
          <p class="sub">Keep profile and notification settings up to date.</p>
        </section>
        <?php if ($subscription_notice === 'success'): ?>
          <section class="portal-section" style="padding-top:0;">
            <p class="sub" style="margin:0; color:#176b35;">Membership checkout completed. Your subscription status will update shortly.</p>
          </section>
        <?php elseif ($subscription_notice === 'cancelled'): ?>
          <section class="portal-section" style="padding-top:0;">
            <p class="sub" style="margin:0;">Membership checkout was canceled. You can restart anytime.</p>
          </section>
        <?php endif; ?>
        <?php if ($billing_notice === 'billing_cancel_scheduled'): ?>
          <section class="portal-section" style="padding-top:0;">
            <p class="sub" style="margin:0; color:#176b35;">Membership cancellation was submitted. Your account status will refresh after the billing provider confirms the change.</p>
          </section>
        <?php endif; ?>
        <?php if ($portal_member_has_state_issue): ?>
          <section class="portal-section" style="padding-top:0;">
            <p class="portal-feedback portal-feedback--err" style="margin:0;">Multiple memberships detected - please contact support and do not start another checkout.</p>
          </section>
        <?php endif; ?>
        <section class="portal-account">
          <?php if (is_array($subscription_summary)): ?>
            <?php
              $is_subscription_active = !empty($subscription_summary['is_active']);
              $is_exact_active = !empty($subscription_summary['is_exact_active']);
              $can_manage_billing = !empty($subscription_summary['can_manage_billing']);
              $manage_billing_url = (string) ($subscription_summary['manage_billing_url'] ?? '');
              $manage_billing_label = (string) ($subscription_summary['manage_billing_label'] ?? 'Manage Billing');
              $membership_catalog_url = $portal_membership_shop_url;
              $entitlements = is_array($subscription_summary['entitlements'] ?? null) ? (array) $subscription_summary['entitlements'] : [];
              $is_test_membership = !empty($subscription_summary['is_test_membership']);
              $plan_feature_highlights = is_array($portal_membership_features_by_slug[$current_member_plan_slug] ?? null) ? (array) $portal_membership_features_by_slug[$current_member_plan_slug] : [];
              $entitlement_keys = [];
              foreach ($entitlements as $entitlement_row) {
                if (!is_array($entitlement_row)) continue;
                $entitlement_key = sanitize_key((string) ($entitlement_row['key'] ?? ''));
                if ($entitlement_key !== '') $entitlement_keys[$entitlement_key] = true;
              }
              $credit_balances_filtered = [];
              if (is_array($credit_snapshot) && !empty($credit_snapshot['balances'])) {
                foreach ((array) $credit_snapshot['balances'] as $balance_row) {
                  if (!is_array($balance_row)) continue;
                  $credit_key = sanitize_key((string) ($balance_row['credit_key'] ?? ''));
                  $qty = (int) ($balance_row['qty'] ?? 0);
                  $is_plan_credit = $credit_key !== '' && isset($entitlement_keys[$credit_key]);
                  if ($is_plan_credit || $qty !== 0) {
                    $credit_balances_filtered[] = $balance_row;
                  }
                }
              }
            ?>
            <article class="portal-card" id="my-membership-card">
              <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                <div>
                  <h2 style="margin:0;">My Membership<?php if ($is_test_membership): ?> <span style="display:inline-block;margin-left:8px;padding:2px 8px;border-radius:999px;background:#fff0d8;color:#8a4b00;font-size:.75rem;font-weight:700;vertical-align:middle;">TEST</span><?php endif; ?></h2>
                  <p class="sub" style="margin:6px 0 0;">Plan includes, billing status, and membership actions.</p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                  <?php if ($can_manage_billing && $manage_billing_url !== ''): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url($manage_billing_url); ?>"><?php echo esc_html($manage_billing_label); ?></a>
                  <?php endif; ?>
                  <a class="btn btn--secondary" href="<?php echo esc_url(add_query_arg(['view' => 'account'], $portal_url)); ?>#membership-change-request-form">Request Plan Change</a>
                  <a class="btn btn--secondary" href="<?php echo esc_url($membership_catalog_url); ?>">Compare Plans</a>
                  <?php if (current_user_can('manage_options') && function_exists('slm_subscriptions_recovery_admin_url')): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url(slm_subscriptions_recovery_admin_url(['user_id' => (int) $user->ID])); ?>">Sync Status</a>
                  <?php endif; ?>
                </div>
              </div>

              <div class="account-grid" style="margin-top:14px;">
                <div>
                  <label>Current Plan</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['plan_label'] ?? 'No active plan')); ?>" readonly>
                </div>
                <div>
                  <label>Status</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['status_label'] ?? 'Not Subscribed')); ?>" readonly>
                </div>
                <div>
                  <label>Agreement Term</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['term_label'] ?? 'N/A')); ?>" readonly>
                </div>
                <div>
                  <label>Next Billing Date</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['current_period_end_label'] ?? 'N/A')); ?>" readonly>
                </div>
              </div>

              <?php if (!$is_subscription_active): ?>
                <p style="margin:14px 0 0;">Membership is inactive. Member-only pricing and credits are disabled until billing is active.</p>
              <?php elseif ($is_exact_active): ?>
                <p style="margin:14px 0 0;">Your membership is active. Included benefits reset monthly based on your billing cycle and plan settings.</p>
              <?php endif; ?>

              <?php if (!empty($subscription_summary['rollover_policy_label']) || !empty($subscription_summary['rollover_policy_description'])): ?>
                <div style="margin-top:14px; padding:12px; border:1px solid #dfe7f4; border-radius:12px; background:#f8fbff;">
                  <strong><?php echo esc_html((string) ($subscription_summary['rollover_policy_label'] ?? 'Rollover Policy')); ?></strong>
                  <p class="sub" style="margin:6px 0 0;"><?php echo esc_html((string) ($subscription_summary['rollover_policy_description'] ?? 'Included benefits generally reset monthly.')); ?></p>
                </div>
              <?php endif; ?>

              <div style="margin-top:16px;">
                <h3 style="margin:0 0 8px;">Plan Highlights</h3>
                <?php if ($plan_feature_highlights === []): ?>
                  <p class="sub" style="margin:0;">Plan highlights are not configured for this membership yet.</p>
                <?php else: ?>
                  <ul class="portal-plan-highlights">
                    <?php foreach ($plan_feature_highlights as $feature): ?>
                      <li>
                        <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span><?php echo esc_html((string) $feature); ?></span>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>

              <?php if ($entitlements !== []): ?>
                <div style="margin-top:16px;">
                  <h3 style="margin:0 0 8px;">Included Credits Per Cycle</h3>
                  <div class="table-scroll">
                    <table class="table" aria-label="Membership Included Credit Entitlements">
                      <thead><tr><th>Credit</th><th>Included</th></tr></thead>
                      <tbody>
                        <?php foreach ($entitlements as $entitlement): ?>
                          <?php if (!is_array($entitlement)) continue; ?>
                          <tr>
                            <td><?php echo esc_html((string) ($entitlement['label'] ?? $entitlement['key'] ?? 'Credit')); ?></td>
                            <td><?php echo esc_html((string) (int) ($entitlement['qty'] ?? 0)); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              <?php endif; ?>

              <?php if ($credit_balances_filtered !== []): ?>
                <div style="margin-top:16px;">
                  <h3 style="margin:0 0 8px;">Current Credit Balances</h3>
                  <p class="sub" style="margin:0 0 8px;">Remaining credits this billing cycle (plus any rollover/bonus balances still available).</p>
                  <div class="table-scroll">
                    <table class="table" aria-label="Membership Credits Snapshot">
                      <thead><tr><th>Credit</th><th>Remaining</th></tr></thead>
                      <tbody>
                        <?php foreach ($credit_balances_filtered as $balance): ?>
                          <?php if (!is_array($balance)) continue; ?>
                          <tr>
                            <td><?php echo esc_html((string) ($balance['label'] ?? 'Credit')); ?></td>
                            <td><?php echo esc_html((string) (int) ($balance['qty'] ?? 0)); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              <?php elseif (is_array($credit_snapshot) && !empty($credit_snapshot['balances'])): ?>
                <div style="margin-top:16px;">
                  <h3 style="margin:0 0 8px;">Current Credit Balances</h3>
                  <p class="sub" style="margin:0;">No current plan credit balances are available yet.</p>
                </div>
              <?php endif; ?>
            </article>

            <?php $render_membership_change_request_form('account'); ?>
          <?php endif; ?>

          <article class="portal-card">
            <h2>Profile</h2>
            <form id="slm-profile-form" method="post">
              <?php wp_nonce_field('slm_save_profile', 'slm_profile_nonce'); ?>
              <div id="slm-profile-feedback" class="portal-feedback" hidden></div>
              <div class="account-grid">
                <div>
                  <label for="slm_profile_name">Full Name</label>
                  <input id="slm_profile_name" type="text" name="display_name" value="<?php echo esc_attr($name); ?>">
                </div>
                <div>
                  <label for="slm_profile_email">Email</label>
                  <input id="slm_profile_email" type="email" name="user_email" value="<?php echo esc_attr($user->user_email ?? ''); ?>">
                </div>
              </div>
              <div style="margin-top:14px;"><button class="btn" type="submit">Save Profile</button></div>
            </form>
          </article>
          <?php if (is_array($subscription_summary)): ?>
            <?php
              $is_subscription_active = !empty($subscription_summary['is_active']);
              $can_manage_billing = !empty($subscription_summary['can_manage_billing']);
              $manage_billing_url = (string) ($subscription_summary['manage_billing_url'] ?? '');
              $manage_billing_label = (string) ($subscription_summary['manage_billing_label'] ?? 'Manage Billing');
              $membership_catalog_url = $portal_membership_shop_url;
            ?>
            <article class="portal-card">
              <h2>Membership Details</h2>
              <div class="account-grid">
                <div>
                  <label>Plan</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['plan_label'] ?? 'No active plan')); ?>" readonly>
                </div>
                <div>
                  <label>Status</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['status_label'] ?? 'Not Subscribed')); ?>" readonly>
                </div>
                <div>
                  <label>Term</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['term_label'] ?? 'N/A')); ?>" readonly>
                </div>
                <div>
                  <label>Next Billing Date</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['current_period_end_label'] ?? 'N/A')); ?>" readonly>
                </div>
                <div>
                  <label>Commitment End</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['commitment_end_label'] ?? 'N/A')); ?>" readonly>
                </div>
                <div>
                  <label>Agreement End</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['term_ends_at_label'] ?? 'N/A')); ?>" readonly>
                </div>
              </div>
              <?php if (!$is_subscription_active): ?>
                <p style="margin:14px 0 0;">Membership is inactive. Member-only pricing and credits are disabled until billing is active.</p>
              <?php endif; ?>
              <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
                <?php if ($can_manage_billing && $manage_billing_url !== ''): ?>
                  <a class="btn btn--secondary" href="<?php echo esc_url($manage_billing_url); ?>"><?php echo esc_html($manage_billing_label); ?></a>
                <?php endif; ?>
                <a class="btn btn--secondary" href="<?php echo esc_url($membership_catalog_url); ?>">Open Membership Shop</a>
              </div>
            </article>
          <?php endif; ?>
          <article class="portal-card">
            <h2>Security</h2>
            <p>Update your password from WordPress account settings.</p>
            <a class="btn btn--secondary" href="<?php echo esc_url(admin_url('profile.php')); ?>">Open Profile Settings</a>
          </article>
        </section>
      <?php endif; ?>
    </div>
  </main>
</div>

<?php if (in_array($view, ['membership-shop', 'account'], true)): ?>
<style>
  .membership-change-request__header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:12px;
  }
  .membership-change-request__pill{
    border:1px solid #d7e2f3;
    background:#f6f9ff;
    color:#173963;
    border-radius:999px;
    padding:6px 10px;
    font-size:.82rem;
    font-weight:700;
    white-space:nowrap;
  }
  .membership-change-request-form label{
    display:block;
    margin:0 0 6px;
    font-weight:600;
    color:#16314f;
  }
  .membership-change-request__grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:14px;
  }
  .membership-change-request__field select,
  .membership-change-request__field textarea{
    width:100%;
    max-width:none;
  }
  .membership-change-request__field textarea{
    min-height:110px;
    resize:vertical;
  }
  .membership-change-request__field--full{
    margin-top:12px;
  }
  .membership-change-request__helper{
    margin-top:12px;
    border:1px solid #e6edf8;
    border-radius:12px;
    background:#f8fbff;
    padding:12px 14px;
  }
  .membership-change-request__actions{
    margin-top:14px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
  }
  .membership-shop__summaryRows strong{
    font-size:1.05rem;
    line-height:1.2;
    text-align:right;
    max-width:68%;
    word-break:break-word;
  }
  .membership-shop__summaryRows--change strong{
    font-size:.98rem;
    max-width:62%;
  }
  .membership-shop__summaryCallout{
    margin-top:12px;
    border:1px solid #e6edf8;
    border-radius:12px;
    background:#f8fbff;
    padding:12px 14px;
  }
  .portal-plan-highlights{
    margin:0;
    padding:0;
    list-style:none;
    display:grid;
    gap:10px;
  }
  .portal-plan-highlights li{
    display:flex;
    align-items:flex-start;
    gap:8px;
    color:#2f3f55;
  }
  .portal-plan-highlights .pkg-check{
    width:18px;
    height:18px;
    color:#163e72;
    flex:0 0 18px;
    margin-top:1px;
  }
  @media (max-width: 900px){
    .membership-change-request__grid{
      grid-template-columns:1fr;
    }
    .membership-shop__summaryRows > div{
      align-items:flex-start;
    }
    .membership-shop__summaryRows strong{
      max-width:58%;
      font-size:.98rem;
    }
  }
</style>
<?php endif; ?>

<?php if ($view === 'membership-shop'): ?>
<style>
  .membership-shop{
    display:grid;
    grid-template-columns:minmax(0, 1fr) 340px;
    gap:18px;
    align-items:start;
  }
  .membership-shop__main{
    display:grid;
    gap:18px;
  }
  .membership-shop__summary{
    position:sticky;
    top:18px;
  }
  .membership-shop__termChips{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
  }
  .membership-shop__termChip{
    border:1px solid #c9d4e8;
    background:#f6f9ff;
    color:#173963;
    padding:8px 12px;
    border-radius:999px;
    font-weight:600;
    cursor:pointer;
  }
  .membership-shop__termChip.is-active{
    background:#163e72;
    border-color:#163e72;
    color:#fff;
    box-shadow:0 6px 18px rgba(22,62,114,.18);
  }
  .membership-shop__statusTag{
    display:inline-block;
    margin:-4px 0 8px;
    padding:4px 10px;
    border-radius:999px;
    background:#e9f7ee;
    color:#176b35;
    font-size:.8rem;
    font-weight:700;
  }
  .membership-shop__card--current{
    border:1px solid #bfe1ca;
    box-shadow:0 8px 20px rgba(23,107,53,.08);
  }
  .membership-shop__select.is-selected{
    background:#163e72;
    color:#fff;
    border-color:#163e72;
  }
  .membership-shop__select.is-unavailable{
    opacity:.55;
    cursor:not-allowed;
  }
  .membership-shop__summaryRows{
    display:grid;
    gap:10px;
    margin-top:8px;
  }
  .membership-shop__summaryRows > div{
    display:flex;
    justify-content:space-between;
    gap:12px;
    border-bottom:1px solid #edf1f7;
    padding-bottom:8px;
  }
  .membership-shop__summaryRows span{
    color:#607087;
    font-size:.9rem;
  }
  .membership-shop__summary .btn.is-disabled{
    opacity:.55;
    pointer-events:none;
  }
  @media (max-width: 1100px){
    .membership-shop{
      grid-template-columns:1fr;
    }
    .membership-shop__summary{
      position:static;
    }
  }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var shopRoot = document.querySelector('.membership-shop');
  if (!shopRoot) return;

  var selectedSocialTerm = <?php echo wp_json_encode(in_array($current_member_term_code, ['m2m', '6m', '12m'], true) ? $current_member_term_code : 'm2m'); ?>;
  var termChipButtons = Array.prototype.slice.call(document.querySelectorAll('[data-term-chip]'));
  var planButtons = Array.prototype.slice.call(document.querySelectorAll('.membership-shop__select'));
  var continueLink = document.getElementById('membership-shop-continue');
  var selectedPlanEl = document.getElementById('membership-shop-selected-plan');
  var selectedTermEl = document.getElementById('membership-shop-selected-term');
  var selectedPriceEl = document.getElementById('membership-shop-selected-price');
  var termNoteEl = document.getElementById('membership-shop-term-note');
  var defaultSummaryNote = 'Select a social or agent plan to preview the agreement note before checkout.';

  function termLabel(code) {
    if (code === 'm2m') return 'Month-to-Month';
    if (code === '6m') return '6-Month Agreement';
    if (code === '12m') return '12-Month Agreement';
    return code || '-';
  }

  function termNote(family, code) {
    if (family === 'agent') {
      return 'Agent memberships use a 12-month agreement and include 1 complimentary listing shoot per month with rollover while the term remains active.';
    }
    if (code === '6m') {
      return '6-month agreement pricing applies the corrected offer: $100 off the first two months.';
    }
    if (code === '12m') {
      return '12-month social agreements include 1 complimentary listing shoot per month while active. Bonus listing-shoot credits roll over until the term ends.';
    }
    return 'Month-to-month social memberships still require a 3-month minimum commitment.';
  }

  function parseTermList(value) {
    if (!value) return [];
    return value.split(',').map(function (v) { return (v || '').trim(); }).filter(Boolean);
  }

  function buttonAvailableTerms(button) {
    return parseTermList(button.getAttribute('data-available-terms') || '');
  }

  function effectiveTermForButton(button) {
    var family = button.getAttribute('data-plan-family') || 'social';
    return family === 'social' ? selectedSocialTerm : (button.getAttribute('data-term-code') || '12m');
  }

  function buttonSupportsTerm(button, termCode) {
    var availableTerms = buttonAvailableTerms(button);
    if (!availableTerms.length) return false;
    return availableTerms.indexOf(termCode) !== -1;
  }

  function buttonHasAnyCheckoutTerm(button) {
    return button.getAttribute('data-has-checkout-term') === '1';
  }

  function setContinueState(url) {
    if (!continueLink) return;
    continueLink.href = url || '#';
    continueLink.classList.toggle('is-disabled', !url);
    continueLink.setAttribute('aria-disabled', url ? 'false' : 'true');
    if (!url) {
      continueLink.onclick = function () { return false; };
    } else {
      continueLink.onclick = null;
    }
  }

  function clearSelectedPlan(message, selectedTermCode) {
    planButtons.forEach(function (btn) { btn.classList.remove('is-selected'); });
    if (selectedPlanEl) selectedPlanEl.textContent = 'None selected';
    if (selectedTermEl) selectedTermEl.textContent = selectedTermCode ? termLabel(selectedTermCode) : '-';
    if (selectedPriceEl) selectedPriceEl.textContent = 'Price shown in Square checkout';
    if (termNoteEl) termNoteEl.textContent = message || defaultSummaryNote;
    setContinueState('');
  }

  function buildCheckoutUrl(rawUrl, termCode) {
    if (!rawUrl) return '';
    try {
      var url = new URL(rawUrl, window.location.origin);
      if (termCode) url.searchParams.set('term', termCode);
      return url.toString();
    } catch (e) {
      return rawUrl;
    }
  }

  function refreshSocialTermPreviews() {
    var previews = document.querySelectorAll('[data-term-preview]');
    previews.forEach(function (node) {
      node.textContent = 'Agreement: ' + termLabel(selectedSocialTerm);
    });
  }

  function setButtonInteractiveState(button, enabled, reason) {
    button.disabled = !enabled;
    button.setAttribute('aria-disabled', enabled ? 'false' : 'true');
    button.classList.toggle('is-unavailable', !enabled && reason === 'term_unavailable');
    button.setAttribute('data-disabled-reason', enabled ? '' : reason);
  }

  function refreshPlanButtonAvailability(options) {
    var opts = options || {};
    var invalidatedSelection = false;
    planButtons.forEach(function (button) {
      var baseEnabled = !!(button.getAttribute('data-checkout-url') || '') && buttonHasAnyCheckoutTerm(button);
      var effectiveTerm = effectiveTermForButton(button);
      var termSupported = buttonSupportsTerm(button, effectiveTerm);
      var enabled = baseEnabled && termSupported;
      var reason = '';
      if (!baseEnabled) {
        reason = 'no_checkout';
      } else if (!termSupported) {
        reason = 'term_unavailable';
      }
      setButtonInteractiveState(button, enabled, reason);
      if (!enabled && button.classList.contains('is-selected')) {
        invalidatedSelection = true;
      }
    });
    if (invalidatedSelection) {
      clearSelectedPlan('The selected agreement term is not available for that plan. Choose another term or plan.', selectedSocialTerm);
    }
  }

  function setTermChipActive(code) {
    selectedSocialTerm = code || 'm2m';
    termChipButtons.forEach(function (btn) {
      var active = btn.getAttribute('data-term-chip') === selectedSocialTerm;
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-checked', active ? 'true' : 'false');
    });
    refreshSocialTermPreviews();
    refreshPlanButtonAvailability();
  }

  function setSelectedPlan(button) {
    planButtons.forEach(function (btn) { btn.classList.remove('is-selected'); });
    if (!(button instanceof HTMLElement) || button.disabled) return;
    var family = button.getAttribute('data-plan-family') || 'social';
    var effectiveTermCode = family === 'social' ? selectedSocialTerm : (button.getAttribute('data-term-code') || '12m');
    if (!buttonSupportsTerm(button, effectiveTermCode)) {
      clearSelectedPlan('That plan is not available for the selected agreement term. Choose another option.', family === 'social' ? effectiveTermCode : '12m');
      return;
    }
    button.classList.add('is-selected');
    var baseUrl = button.getAttribute('data-checkout-url') || '';
    var checkoutUrl = family === 'social' ? buildCheckoutUrl(baseUrl, effectiveTermCode) : baseUrl;
    var planName = button.getAttribute('data-plan-name') || 'Membership';

    if (selectedPlanEl) selectedPlanEl.textContent = planName;
    if (selectedTermEl) selectedTermEl.textContent = termLabel(effectiveTermCode);
    if (selectedPriceEl) selectedPriceEl.textContent = 'Shown in Square checkout';
    if (termNoteEl) termNoteEl.textContent = termNote(family, effectiveTermCode);
    setContinueState(checkoutUrl || '');
  }

  termChipButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      setTermChipActive(btn.getAttribute('data-term-chip') || 'm2m');
      var selectedBtn = document.querySelector('.membership-shop__select.is-selected');
      if (selectedBtn && !selectedBtn.disabled) setSelectedPlan(selectedBtn);
    });
  });

  planButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (btn.disabled) return;
      setSelectedPlan(btn);
    });
  });

  clearSelectedPlan(defaultSummaryNote, '');
  setTermChipActive(selectedSocialTerm);
  var currentBtn = document.querySelector('.membership-shop__select[data-is-current="1"]');
  if (currentBtn && !currentBtn.disabled) {
    setSelectedPlan(currentBtn);
  }
});
</script>
<?php endif; ?>

<?php get_footer(); ?>
