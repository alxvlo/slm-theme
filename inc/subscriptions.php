<?php
if (!defined('ABSPATH')) exit;

const SLM_SUBSCRIPTIONS_SCHEMA_VERSION = '1.0.0';

function slm_subscriptions_meta_keys(): array {
  return [
    'plan' => 'slm_subscription_plan',
    'stripe_customer_id' => 'slm_stripe_customer_id',
    'stripe_subscription_id' => 'slm_stripe_subscription_id',
    'status' => 'slm_subscription_status',
    'current_period_end' => 'slm_subscription_current_period_end',
    'commitment_end' => 'slm_subscription_commitment_end',
    'last_credited_invoice_id' => 'slm_last_credited_invoice_id',
    'aryeo_customer_user_id' => 'slm_aryeo_customer_user_id',
    'aryeo_affiliate_membership_id' => 'slm_aryeo_affiliate_membership_id',
  ];
}

function slm_subscriptions_default_plans(): array {
  return [
    'monthly-momentum' => ['label' => 'Monthly Momentum', 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 3, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'growth-engine' => ['label' => 'Growth Engine', 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 3, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'brand-authority' => ['label' => 'Brand Authority', 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 3, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'elite-presence' => ['label' => 'Elite Presence', 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 3, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'vip-presence' => ['label' => 'VIP Presence', 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 3, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'agent-starting' => ['label' => 'Starting', 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 12, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'agent-growing' => ['label' => 'Growing', 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 12, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'agent-established' => ['label' => 'Established', 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 12, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'agent-elite' => ['label' => 'Elite', 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 12, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'agent-top-tier' => ['label' => 'Top-Tier', 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 12, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
  ];
}

function slm_subscriptions_default_profiles(): array {
  return [
    'member-default' => [
      'label' => 'Member Default',
      'affiliate_id' => '',
      'credit_amount' => 0,
      'debit_on_revoke' => 1,
    ],
  ];
}

function slm_subscriptions_stripe_secret_key(): string {
  return trim((string) get_option('slm_stripe_secret_key', ''));
}

function slm_subscriptions_stripe_webhook_secret(): string {
  return trim((string) get_option('slm_stripe_webhook_secret', ''));
}

function slm_subscriptions_stripe_publishable_key(): string {
  return trim((string) get_option('slm_stripe_publishable_key', ''));
}

function slm_subscriptions_stripe_portal_config_id(): string {
  return trim((string) get_option('slm_stripe_portal_config_id', ''));
}

function slm_subscriptions_checkout_success_url(): string {
  $fallback = add_query_arg(['view' => 'account', 'subscription' => 'success'], slm_portal_url());
  $url = trim((string) get_option('slm_stripe_checkout_success_url', ''));
  return $url !== '' ? esc_url_raw($url) : $fallback;
}

function slm_subscriptions_checkout_cancel_url(): string {
  $fallback = add_query_arg(['view' => 'account', 'subscription' => 'cancelled'], slm_portal_url());
  $url = trim((string) get_option('slm_stripe_checkout_cancel_url', ''));
  return $url !== '' ? esc_url_raw($url) : $fallback;
}

function slm_subscriptions_affiliate_removal_path(): string {
  return trim((string) get_option('slm_aryeo_affiliate_removal_path', ''));
}

function slm_subscriptions_decode_json($value): array {
  if (is_array($value)) return $value;
  $raw = is_string($value) ? trim(wp_unslash($value)) : '';
  if ($raw === '') return [];
  $decoded = json_decode($raw, true);
  return is_array($decoded) ? $decoded : [];
}

function slm_subscriptions_sanitize_plans($value): array {
  $decoded = slm_subscriptions_decode_json($value);
  if ($decoded === []) return slm_subscriptions_default_plans();
  $clean = [];
  foreach ($decoded as $slug => $plan) {
    if (!is_array($plan)) continue;
    if (is_int($slug) && isset($plan['slug'])) $slug = (string) $plan['slug'];
    $slug = sanitize_key((string) $slug);
    if ($slug === '') continue;
    $interval = sanitize_key((string) ($plan['billing_interval'] ?? 'month'));
    if (!in_array($interval, ['day', 'week', 'month', 'year'], true)) $interval = 'month';
    $clean[$slug] = [
      'label' => sanitize_text_field((string) ($plan['label'] ?? $slug)),
      'stripe_price_id' => sanitize_text_field((string) ($plan['stripe_price_id'] ?? '')),
      'aryeo_benefit_profile' => sanitize_key((string) ($plan['aryeo_benefit_profile'] ?? 'member-default')),
      'minimum_term_months' => max(0, (int) ($plan['minimum_term_months'] ?? 0)),
      'billing_interval' => $interval,
      'credit_amount' => max(0, (int) ($plan['credit_amount'] ?? 0)),
      'active' => empty($plan['active']) ? 0 : 1,
    ];
  }
  return $clean === [] ? slm_subscriptions_default_plans() : $clean;
}

function slm_subscriptions_sanitize_profiles($value): array {
  $decoded = slm_subscriptions_decode_json($value);
  if ($decoded === []) return slm_subscriptions_default_profiles();
  $clean = [];
  foreach ($decoded as $slug => $profile) {
    if (!is_array($profile)) continue;
    if (is_int($slug) && isset($profile['slug'])) $slug = (string) $profile['slug'];
    $slug = sanitize_key((string) $slug);
    if ($slug === '') continue;
    $clean[$slug] = [
      'label' => sanitize_text_field((string) ($profile['label'] ?? $slug)),
      'affiliate_id' => sanitize_text_field((string) ($profile['affiliate_id'] ?? '')),
      'credit_amount' => max(0, (int) ($profile['credit_amount'] ?? 0)),
      'debit_on_revoke' => empty($profile['debit_on_revoke']) ? 0 : 1,
    ];
  }
  return $clean === [] ? slm_subscriptions_default_profiles() : $clean;
}

function slm_subscription_plans(): array {
  $defaults = slm_subscriptions_default_plans();
  $saved = get_option('slm_subscription_plans', []);
  if (!is_array($saved) || $saved === []) return $defaults;
  foreach ($saved as $slug => $plan) {
    if (!is_array($plan)) continue;
    $slug = sanitize_key((string) $slug);
    if ($slug === '') continue;
    $base = $defaults[$slug] ?? ['label' => $slug, 'stripe_price_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 0, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1];
    $defaults[$slug] = array_merge($base, $plan);
  }
  return $defaults;
}

function slm_subscription_benefit_profiles(): array {
  $defaults = slm_subscriptions_default_profiles();
  $saved = get_option('slm_subscription_benefit_profiles', []);
  if (!is_array($saved) || $saved === []) return $defaults;
  foreach ($saved as $slug => $profile) {
    if (!is_array($profile)) continue;
    $slug = sanitize_key((string) $slug);
    if ($slug === '') continue;
    $base = $defaults[$slug] ?? ['label' => $slug, 'affiliate_id' => '', 'credit_amount' => 0, 'debit_on_revoke' => 1];
    $defaults[$slug] = array_merge($base, $profile);
  }
  return $defaults;
}

function slm_subscriptions_plan(string $slug): ?array {
  $plans = slm_subscription_plans();
  $slug = sanitize_key($slug);
  return isset($plans[$slug]) && is_array($plans[$slug]) ? $plans[$slug] : null;
}

function slm_subscriptions_plan_slug_by_price_id(string $price_id): string {
  foreach (slm_subscription_plans() as $slug => $plan) {
    if (!is_array($plan)) continue;
    if (trim((string) ($plan['stripe_price_id'] ?? '')) === trim($price_id)) return (string) $slug;
  }
  return '';
}

function slm_subscriptions_profile_for_plan(string $plan_slug): array {
  $plan = slm_subscriptions_plan($plan_slug);
  $profile_key = sanitize_key((string) ($plan['aryeo_benefit_profile'] ?? 'member-default'));
  $profiles = slm_subscription_benefit_profiles();
  $profile = $profiles[$profile_key] ?? ($profiles['member-default'] ?? slm_subscriptions_default_profiles()['member-default']);
  if ((int) ($profile['credit_amount'] ?? 0) <= 0 && (int) ($plan['credit_amount'] ?? 0) > 0) {
    $profile['credit_amount'] = (int) $plan['credit_amount'];
  }
  return is_array($profile) ? $profile : slm_subscriptions_default_profiles()['member-default'];
}

function slm_subscriptions_is_active_status(string $status): bool {
  return in_array(strtolower(trim($status)), ['active', 'trialing'], true);
}

function slm_subscriptions_status_label(string $status): string {
  $status = strtolower(trim($status));
  if ($status === '') return 'Not Subscribed';
  $labels = ['active' => 'Active', 'trialing' => 'Trialing', 'past_due' => 'Past Due', 'unpaid' => 'Unpaid', 'canceled' => 'Canceled', 'non_member' => 'Not Active'];
  return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
}

function slm_subscriptions_can_accept_checkout(): bool {
  if (slm_subscriptions_stripe_secret_key() === '') return false;
  foreach (slm_subscription_plans() as $plan) {
    if (!is_array($plan)) continue;
    if (!empty($plan['active']) && trim((string) ($plan['stripe_price_id'] ?? '')) !== '') return true;
  }
  return false;
}

function slm_subscriptions_event_table(): string {
  global $wpdb;
  return $wpdb->prefix . 'slm_stripe_events';
}

function slm_subscriptions_install_schema(): void {
  global $wpdb;
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  $table = slm_subscriptions_event_table();
  $charset = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE {$table} (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    event_id varchar(191) NOT NULL,
    event_type varchar(191) NOT NULL,
    user_id bigint(20) unsigned NULL,
    status varchar(32) NOT NULL DEFAULT 'received',
    details longtext NULL,
    processed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY event_id (event_id),
    KEY status (status),
    KEY user_id (user_id)
  ) {$charset};";
  dbDelta($sql);
  update_option('slm_subscriptions_schema_version', SLM_SUBSCRIPTIONS_SCHEMA_VERSION);
}

add_action('after_switch_theme', 'slm_subscriptions_install_schema');
add_action('init', function () {
  if (wp_installing()) return;
  if ((string) get_option('slm_subscriptions_schema_version', '') !== SLM_SUBSCRIPTIONS_SCHEMA_VERSION) {
    slm_subscriptions_install_schema();
  }
}, 4);

function slm_subscriptions_log(string $event, array $context = [], string $level = 'info'): void {
  $logs = get_option('slm_subscription_sync_log', []);
  if (!is_array($logs)) $logs = [];
  $logs[] = [
    'timestamp' => current_time('mysql'),
    'level' => sanitize_key($level),
    'event' => sanitize_text_field($event),
    'context' => $context,
  ];
  if (count($logs) > 200) $logs = array_slice($logs, -200);
  update_option('slm_subscription_sync_log', $logs, false);
}

function slm_subscriptions_stripe_request(string $method, string $path, array $params = []) {
  $secret = slm_subscriptions_stripe_secret_key();
  if ($secret === '') return new WP_Error('slm_stripe_no_secret', 'Stripe secret key is not configured.');

  $method = strtoupper($method);
  $url = 'https://api.stripe.com/v1/' . ltrim($path, '/');
  $args = [
    'method' => $method,
    'timeout' => 25,
    'headers' => ['Authorization' => 'Bearer ' . $secret],
  ];
  if ($method === 'GET') {
    if (!empty($params)) $url = add_query_arg($params, $url);
  } elseif (!empty($params)) {
    $args['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
    $args['body'] = http_build_query($params, '', '&');
  }

  $res = wp_remote_request($url, $args);
  if (is_wp_error($res)) return $res;
  $code = (int) wp_remote_retrieve_response_code($res);
  $body = (string) wp_remote_retrieve_body($res);
  $data = $body !== '' ? json_decode($body, true) : [];
  if (!is_array($data)) $data = [];
  if ($code < 200 || $code >= 300) {
    $msg = (string) ($data['error']['message'] ?? $data['message'] ?? 'Stripe API request failed.');
    return new WP_Error('slm_stripe_http_' . $code, $msg, ['status' => $code, 'body' => $data]);
  }
  return $data;
}

function slm_subscriptions_verify_webhook_signature(string $payload, string $header, string $secret, int $tolerance = 300): bool {
  if ($payload === '' || $header === '' || $secret === '') return false;
  $timestamp = '';
  $signatures = [];
  foreach (array_map('trim', explode(',', $header)) as $part) {
    if (strpos($part, '=') === false) continue;
    [$k, $v] = explode('=', $part, 2);
    if ($k === 't') $timestamp = $v;
    if ($k === 'v1') $signatures[] = $v;
  }
  if ($timestamp === '' || empty($signatures) || !ctype_digit((string) $timestamp)) return false;
  $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
  $matched = false;
  foreach ($signatures as $sig) {
    if (hash_equals($expected, $sig)) {
      $matched = true;
      break;
    }
  }
  if (!$matched) return false;
  return abs(time() - (int) $timestamp) <= $tolerance;
}

function slm_subscriptions_find_user_by_meta(string $key, string $value): ?WP_User {
  $users = get_users(['number' => 1, 'count_total' => false, 'meta_key' => $key, 'meta_value' => $value]);
  return (!empty($users) && $users[0] instanceof WP_User) ? $users[0] : null;
}

function slm_subscriptions_resolve_user(array $object): ?WP_User {
  $meta_user_id = (int) ($object['metadata']['slm_user_id'] ?? 0);
  if ($meta_user_id > 0) {
    $u = get_user_by('id', $meta_user_id);
    if ($u instanceof WP_User) return $u;
  }
  $client_ref = (int) ($object['client_reference_id'] ?? 0);
  if ($client_ref > 0) {
    $u = get_user_by('id', $client_ref);
    if ($u instanceof WP_User) return $u;
  }
  $keys = slm_subscriptions_meta_keys();
  $customer_id = trim((string) ($object['customer'] ?? ''));
  if ($customer_id !== '') {
    $u = slm_subscriptions_find_user_by_meta($keys['stripe_customer_id'], $customer_id);
    if ($u instanceof WP_User) return $u;
  }
  $sub_id = trim((string) ($object['subscription'] ?? $object['id'] ?? ''));
  if (strpos($sub_id, 'sub_') === 0) {
    $u = slm_subscriptions_find_user_by_meta($keys['stripe_subscription_id'], $sub_id);
    if ($u instanceof WP_User) return $u;
  }
  $emails = [(string) ($object['customer_email'] ?? ''), (string) ($object['customer_details']['email'] ?? ''), (string) ($object['receipt_email'] ?? '')];
  foreach ($emails as $email) {
    $email = sanitize_email($email);
    if ($email === '') continue;
    $u = get_user_by('email', $email);
    if ($u instanceof WP_User) return $u;
  }
  return null;
}

function slm_subscriptions_plan_slug_from_event(array $object, ?array $subscription = null): string {
  $meta = sanitize_key((string) ($object['metadata']['slm_plan'] ?? ''));
  if ($meta !== '') return $meta;
  if ($subscription !== null) {
    $sub_meta = sanitize_key((string) ($subscription['metadata']['slm_plan'] ?? ''));
    if ($sub_meta !== '') return $sub_meta;
  }
  $prices = [];
  if (isset($object['lines']['data']) && is_array($object['lines']['data'])) {
    foreach ($object['lines']['data'] as $line) {
      if (!is_array($line)) continue;
      $p = (string) ($line['price']['id'] ?? $line['pricing']['price_details']['price'] ?? '');
      if ($p !== '') $prices[] = $p;
    }
  }
  if (isset($object['items']['data']) && is_array($object['items']['data'])) {
    foreach ($object['items']['data'] as $line) {
      if (!is_array($line)) continue;
      $p = (string) ($line['price']['id'] ?? '');
      if ($p !== '') $prices[] = $p;
    }
  }
  if ($subscription !== null && isset($subscription['items']['data']) && is_array($subscription['items']['data'])) {
    foreach ($subscription['items']['data'] as $line) {
      if (!is_array($line)) continue;
      $p = (string) ($line['price']['id'] ?? '');
      if ($p !== '') $prices[] = $p;
    }
  }
  foreach ($prices as $price_id) {
    $slug = slm_subscriptions_plan_slug_by_price_id($price_id);
    if ($slug !== '') return $slug;
  }
  return '';
}

function slm_subscriptions_period_end(array $sub): int {
  $raw = $sub['current_period_end'] ?? 0;
  if (is_numeric($raw)) return max(0, (int) $raw);
  if (is_string($raw) && $raw !== '') {
    $ts = strtotime($raw);
    return $ts ? $ts : 0;
  }
  return 0;
}

function slm_subscriptions_action_url(string $action, array $args = []): string {
  $action = sanitize_key($action);
  $url = add_query_arg(array_merge(['view' => 'account', 'action' => $action], $args), slm_portal_url());
  return wp_nonce_url($url, 'slm_subscription_' . $action);
}

function slm_subscriptions_start_url(string $plan_slug): string {
  return slm_subscriptions_action_url('start-membership', ['plan' => sanitize_key($plan_slug)]);
}

function slm_subscriptions_manage_billing_url(): string {
  return slm_subscriptions_action_url('manage-billing');
}

function slm_subscriptions_get_or_create_customer(WP_User $user) {
  $keys = slm_subscriptions_meta_keys();
  $existing = trim((string) get_user_meta($user->ID, $keys['stripe_customer_id'], true));
  if ($existing !== '') return $existing;
  $name = trim((string) ($user->first_name . ' ' . $user->last_name));
  if ($name === '') $name = (string) $user->display_name;
  $payload = ['email' => (string) $user->user_email, 'name' => $name, 'metadata[slm_user_id]' => (string) $user->ID];
  $phone = trim((string) get_user_meta($user->ID, 'phone', true));
  if ($phone !== '') $payload['phone'] = $phone;
  $res = slm_subscriptions_stripe_request('POST', '/customers', $payload);
  if (is_wp_error($res)) return $res;
  $customer_id = trim((string) ($res['id'] ?? ''));
  if ($customer_id === '') return new WP_Error('slm_stripe_missing_customer_id', 'Stripe customer ID missing.');
  update_user_meta($user->ID, $keys['stripe_customer_id'], $customer_id);
  return $customer_id;
}

function slm_subscriptions_checkout_for_plan(WP_User $user, string $plan_slug) {
  $plan_slug = sanitize_key($plan_slug);
  $plan = slm_subscriptions_plan($plan_slug);
  if (!$plan || empty($plan['active'])) return new WP_Error('slm_subscription_plan', 'Membership plan is not available.');
  $price_id = trim((string) ($plan['stripe_price_id'] ?? ''));
  if ($price_id === '') return new WP_Error('slm_subscription_missing_price', 'Stripe price ID is missing for this plan.');

  $customer_id = slm_subscriptions_get_or_create_customer($user);
  if (is_wp_error($customer_id)) return $customer_id;

  $params = [
    'mode' => 'subscription',
    'customer' => $customer_id,
    'client_reference_id' => (string) $user->ID,
    'success_url' => slm_subscriptions_checkout_success_url(),
    'cancel_url' => slm_subscriptions_checkout_cancel_url(),
    'allow_promotion_codes' => 'true',
    'line_items[0][price]' => $price_id,
    'line_items[0][quantity]' => '1',
    'metadata[slm_plan]' => $plan_slug,
    'metadata[slm_user_id]' => (string) $user->ID,
    'subscription_data[metadata][slm_plan]' => $plan_slug,
    'subscription_data[metadata][slm_user_id]' => (string) $user->ID,
    'subscription_data[metadata][slm_minimum_term_months]' => (string) max(0, (int) ($plan['minimum_term_months'] ?? 0)),
  ];
  $res = slm_subscriptions_stripe_request('POST', '/checkout/sessions', $params);
  if (is_wp_error($res)) return $res;
  $url = trim((string) ($res['url'] ?? ''));
  return $url !== '' ? $url : new WP_Error('slm_stripe_missing_checkout_url', 'Stripe checkout URL missing.');
}

function slm_subscriptions_billing_portal_for_user(WP_User $user) {
  $customer_id = slm_subscriptions_get_or_create_customer($user);
  if (is_wp_error($customer_id)) return $customer_id;
  $params = ['customer' => $customer_id, 'return_url' => add_query_arg('view', 'account', slm_portal_url())];
  $config = slm_subscriptions_stripe_portal_config_id();
  if ($config !== '') $params['configuration'] = $config;
  $res = slm_subscriptions_stripe_request('POST', '/billing_portal/sessions', $params);
  if (is_wp_error($res)) return $res;
  $url = trim((string) ($res['url'] ?? ''));
  return $url !== '' ? $url : new WP_Error('slm_stripe_missing_portal_url', 'Stripe billing portal URL missing.');
}

function slm_subscriptions_handle_portal_action(): void {
  $action = sanitize_key((string) ($_GET['action'] ?? ''));
  if (!in_array($action, ['start-membership', 'manage-billing'], true)) return;
  if (!is_user_logged_in()) {
    wp_safe_redirect(add_query_arg('mode', 'login', slm_login_url()));
    exit;
  }
  $nonce = (string) ($_GET['_wpnonce'] ?? '');
  if (!wp_verify_nonce($nonce, 'slm_subscription_' . $action)) wp_die('Invalid request.', 403);

  $user = wp_get_current_user();
  if (!$user instanceof WP_User || !$user->ID) wp_die('Not logged in.', 403);

  $target = null;
  if ($action === 'start-membership') {
    $target = slm_subscriptions_checkout_for_plan($user, sanitize_key((string) ($_GET['plan'] ?? '')));
  } elseif ($action === 'manage-billing') {
    $target = slm_subscriptions_billing_portal_for_user($user);
  }
  if (is_wp_error($target)) {
    slm_subscriptions_log('portal_action_error', ['action' => $action, 'user_id' => (int) $user->ID, 'error' => $target->get_error_message()], 'error');
    wp_die(esc_html($target->get_error_message()), 500);
  }
  wp_safe_redirect((string) $target);
  exit;
}
add_action('template_redirect', 'slm_subscriptions_handle_portal_action', 2);

function slm_subscriptions_aryeo_available(): bool {
  return function_exists('slm_aryeo_request') && function_exists('slm_aryeo_api_key') && trim((string) slm_aryeo_api_key()) !== '';
}

function slm_subscriptions_aryeo_collection(array $res, string $legacy_key): array {
  if (function_exists('slm_aryeo_extract_collection')) {
    return slm_aryeo_extract_collection($res, $legacy_key);
  }
  $data = $res['data'] ?? null;
  if (is_array($data) && array_values($data) === $data) return $data;
  $legacy = $res[$legacy_key] ?? null;
  return is_array($legacy) ? $legacy : [];
}

function slm_subscriptions_aryeo_resource(array $res, string $legacy_key): array {
  if (function_exists('slm_aryeo_extract_resource')) {
    return slm_aryeo_extract_resource($res, $legacy_key);
  }
  $data = $res['data'] ?? null;
  if (is_array($data) && array_values($data) !== $data) return $data;
  $legacy = $res[$legacy_key] ?? null;
  return is_array($legacy) ? $legacy : [];
}

function slm_subscriptions_aryeo_user_id(int $user_id) {
  $keys = slm_subscriptions_meta_keys();
  $cached = trim((string) get_user_meta($user_id, $keys['aryeo_customer_user_id'], true));
  if ($cached !== '') return $cached;
  if (!slm_subscriptions_aryeo_available()) return new WP_Error('slm_aryeo_not_configured', 'Aryeo is not configured.');

  $user = get_user_by('id', $user_id);
  if (!$user instanceof WP_User) return new WP_Error('slm_user_not_found', 'User not found.');
  $email = sanitize_email((string) $user->user_email);
  if ($email === '') return new WP_Error('slm_email_missing', 'User email missing.');

  $queries = [
    ['filter[email]' => $email, 'per_page' => 50, 'page' => 1],
    ['filter[search]' => $email, 'per_page' => 50, 'page' => 1],
  ];
  $found = null;
  foreach ($queries as $query) {
    $res = slm_aryeo_request('GET', '/customer-users', $query, null);
    if (is_wp_error($res)) continue;
    $users = slm_subscriptions_aryeo_collection($res, 'customer_users');
    foreach ($users as $item) {
      if (!is_array($item)) continue;
      $candidate = sanitize_email((string) ($item['email'] ?? $item['user']['email'] ?? ''));
      if ($candidate !== '' && strtolower($candidate) === strtolower($email)) {
        $found = $item;
        break 2;
      }
    }
  }

  if (!$found) {
    $first = trim((string) $user->first_name);
    $last = trim((string) $user->last_name);
    if ($first === '' || $last === '') {
      $parts = preg_split('/\s+/', trim((string) $user->display_name));
      if (is_array($parts) && !empty($parts)) {
        if ($first === '') $first = (string) array_shift($parts);
        if ($last === '') $last = trim(implode(' ', $parts));
      }
    }
    if ($first === '') $first = (string) $user->user_login;
    if ($last === '') $last = 'Client';

    $payload = ['first_name' => $first, 'last_name' => $last, 'email' => $email];
    $phone = trim((string) get_user_meta($user_id, 'phone', true));
    if ($phone !== '') $payload['phone'] = $phone;

    $created = slm_aryeo_request('POST', '/customer-users', [], $payload);
    if (is_wp_error($created)) return $created;
    $found = slm_subscriptions_aryeo_resource($created, 'customer_user');
  }

  $aryeo_user_id = trim((string) ($found['id'] ?? ''));
  if ($aryeo_user_id === '') return new WP_Error('slm_aryeo_user_missing', 'Unable to resolve Aryeo customer user.');
  update_user_meta($user_id, $keys['aryeo_customer_user_id'], $aryeo_user_id);
  return $aryeo_user_id;
}

function slm_subscriptions_aryeo_apply_profile(int $user_id, string $plan_slug, string $invoice_id = '') {
  if (!slm_subscriptions_aryeo_available()) return ['ok' => true, 'skipped' => 'aryeo_not_configured'];

  $profile = slm_subscriptions_profile_for_plan($plan_slug);
  $affiliate_id = trim((string) ($profile['affiliate_id'] ?? ''));
  $credit_amount = max(0, (int) ($profile['credit_amount'] ?? 0));
  $aryeo_user_id = slm_subscriptions_aryeo_user_id($user_id);
  if (is_wp_error($aryeo_user_id)) return $aryeo_user_id;

  $keys = slm_subscriptions_meta_keys();
  $actions = [];

  if ($affiliate_id !== '') {
    $membership = slm_aryeo_request('POST', '/customer-teams/affiliate-memberships', [], ['affiliate_id' => $affiliate_id, 'customer_user_id' => $aryeo_user_id]);
    if (is_wp_error($membership)) {
      $msg = strtolower($membership->get_error_message());
      if (strpos($msg, 'already') === false && strpos($msg, 'exists') === false) {
        return $membership;
      }
    } else {
      $resource = slm_subscriptions_aryeo_resource($membership, 'customer_team_membership');
      $membership_id = trim((string) ($resource['id'] ?? ''));
      if ($membership_id !== '') {
        update_user_meta($user_id, $keys['aryeo_affiliate_membership_id'], $membership_id);
      }
    }
    $actions[] = 'affiliate_applied';
  }

  if ($invoice_id !== '' && $credit_amount > 0) {
    $last = trim((string) get_user_meta($user_id, $keys['last_credited_invoice_id'], true));
    if ($last !== $invoice_id) {
      $tx = slm_aryeo_request(
        'POST',
        '/customer-users/' . rawurlencode($aryeo_user_id) . '/credit-transactions',
        [],
        ['type' => 'credit', 'amount' => $credit_amount, 'date' => gmdate('Y-m-d'), 'description' => 'Membership credit (' . $plan_slug . ')']
      );
      if (is_wp_error($tx)) return $tx;
      update_user_meta($user_id, $keys['last_credited_invoice_id'], $invoice_id);
      $actions[] = 'credit_applied';
    }
  }

  return ['ok' => true, 'actions' => $actions];
}

function slm_subscriptions_aryeo_revoke_profile(int $user_id, string $plan_slug, string $reason) {
  if (!slm_subscriptions_aryeo_available()) return ['ok' => true, 'skipped' => 'aryeo_not_configured'];

  $profile = slm_subscriptions_profile_for_plan($plan_slug);
  $credit_amount = max(0, (int) ($profile['credit_amount'] ?? 0));
  $debit_on_revoke = !empty($profile['debit_on_revoke']);
  $aryeo_user_id = slm_subscriptions_aryeo_user_id($user_id);
  if (is_wp_error($aryeo_user_id)) return $aryeo_user_id;

  $actions = [];
  if ($debit_on_revoke && $credit_amount > 0) {
    $ref = gmdate('Y-m') . ':' . sanitize_key($reason);
    $last = trim((string) get_user_meta($user_id, 'slm_subscription_last_revoke_ref', true));
    if ($last !== $ref) {
      $tx = slm_aryeo_request(
        'POST',
        '/customer-users/' . rawurlencode($aryeo_user_id) . '/credit-transactions',
        [],
        ['type' => 'debit', 'amount' => $credit_amount, 'date' => gmdate('Y-m-d'), 'description' => 'Membership deactivation debit (' . $plan_slug . ')']
      );
      if (!is_wp_error($tx)) {
        update_user_meta($user_id, 'slm_subscription_last_revoke_ref', $ref);
        $actions[] = 'credit_debited';
      }
    }
  }

  $path = slm_subscriptions_affiliate_removal_path();
  if ($path !== '') {
    $keys = slm_subscriptions_meta_keys();
    $membership_id = trim((string) get_user_meta($user_id, $keys['aryeo_affiliate_membership_id'], true));
    if ($membership_id !== '') {
      $delete_path = str_replace('{membership_id}', rawurlencode($membership_id), $path);
      $res = slm_aryeo_request('DELETE', $delete_path, [], null);
      if (!is_wp_error($res)) {
        delete_user_meta($user_id, $keys['aryeo_affiliate_membership_id']);
        $actions[] = 'affiliate_removed';
      }
    }
  }

  return ['ok' => true, 'actions' => $actions];
}

function slm_subscriptions_update_state(int $user_id, array $state): void {
  $keys = slm_subscriptions_meta_keys();
  $map = [
    'plan' => $keys['plan'],
    'stripe_customer_id' => $keys['stripe_customer_id'],
    'stripe_subscription_id' => $keys['stripe_subscription_id'],
    'status' => $keys['status'],
    'current_period_end' => $keys['current_period_end'],
    'commitment_end' => $keys['commitment_end'],
  ];
  foreach ($map as $field => $meta_key) {
    if (!array_key_exists($field, $state)) continue;
    $value = $state[$field];
    if (in_array($field, ['current_period_end', 'commitment_end'], true)) $value = max(0, (int) $value);
    elseif ($field === 'plan') $value = sanitize_key((string) $value);
    else $value = trim((string) $value);
    update_user_meta($user_id, $meta_key, $value);
  }
}

function slm_subscriptions_commitment_end(int $user_id, string $plan_slug, int $anchor = 0): int {
  $keys = slm_subscriptions_meta_keys();
  $existing = (int) get_user_meta($user_id, $keys['commitment_end'], true);
  if ($existing > time()) return $existing;
  $plan = slm_subscriptions_plan($plan_slug);
  $months = max(0, (int) ($plan['minimum_term_months'] ?? 0));
  if ($months <= 0) {
    update_user_meta($user_id, $keys['commitment_end'], 0);
    return 0;
  }
  if ($anchor <= 0) $anchor = time();
  $end = strtotime('+' . $months . ' months', $anchor);
  $end = $end ? (int) $end : 0;
  update_user_meta($user_id, $keys['commitment_end'], $end);
  return $end;
}

function slm_subscriptions_get_event_row(string $event_id): ?array {
  global $wpdb;
  $table = slm_subscriptions_event_table();
  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE event_id=%s LIMIT 1", $event_id), ARRAY_A);
  return is_array($row) ? $row : null;
}

function slm_subscriptions_insert_event_row(string $event_id, string $event_type): bool {
  global $wpdb;
  $table = slm_subscriptions_event_table();
  return $wpdb->insert($table, [
    'event_id' => $event_id,
    'event_type' => $event_type,
    'status' => 'received',
    'details' => wp_json_encode(['type' => $event_type]),
    'processed_at' => current_time('mysql', true),
  ], ['%s', '%s', '%s', '%s', '%s']) !== false;
}

function slm_subscriptions_update_event_row(string $event_id, string $status, int $user_id, array $details = []): void {
  global $wpdb;
  $table = slm_subscriptions_event_table();
  $wpdb->update($table, [
    'status' => $status,
    'user_id' => $user_id > 0 ? $user_id : null,
    'details' => wp_json_encode($details),
    'processed_at' => current_time('mysql', true),
  ], ['event_id' => $event_id], ['%s', '%d', '%s', '%s'], ['%s']);
}

function slm_subscriptions_retrieve_subscription(string $id) {
  $id = trim($id);
  if ($id === '') return new WP_Error('slm_subscription_missing_id', 'Subscription ID missing.');
  return slm_subscriptions_stripe_request('GET', '/subscriptions/' . rawurlencode($id), []);
}

function slm_subscriptions_enforce_commitment(array $subscription, int $commitment_end) {
  $sub_id = trim((string) ($subscription['id'] ?? ''));
  if ($sub_id === '' || $commitment_end <= time()) return true;
  if (empty($subscription['cancel_at_period_end'])) return true;
  return slm_subscriptions_stripe_request('POST', '/subscriptions/' . rawurlencode($sub_id), [
    'cancel_at_period_end' => 'false',
    'metadata[slm_commitment_policy]' => 'cancel_blocked_until_commitment_end',
  ]);
}

function slm_subscriptions_extract_subscription(array $object) {
  if ((string) ($object['object'] ?? '') === 'subscription') return $object;
  $sub_id = trim((string) ($object['subscription'] ?? ''));
  if ($sub_id === '') return new WP_Error('slm_subscription_missing', 'Subscription is missing from event object.');
  return slm_subscriptions_retrieve_subscription($sub_id);
}

function slm_subscriptions_handle_checkout_completed(array $object, ?WP_User $user): array {
  if ((string) ($object['mode'] ?? '') !== 'subscription') return ['ok' => true, 'status' => 'ignored', 'message' => 'Not a subscription checkout.'];
  if (!$user instanceof WP_User) $user = slm_subscriptions_resolve_user($object);
  if (!$user instanceof WP_User) return ['ok' => false, 'status' => 'error', 'message' => 'Unable to map checkout to user.'];

  $sub_id = trim((string) ($object['subscription'] ?? ''));
  $subscription = $sub_id !== '' ? slm_subscriptions_retrieve_subscription($sub_id) : null;
  if (is_wp_error($subscription)) return ['ok' => false, 'status' => 'error', 'message' => $subscription->get_error_message(), 'user_id' => (int) $user->ID];
  $sub_data = is_array($subscription) ? $subscription : [];
  $plan_slug = slm_subscriptions_plan_slug_from_event($object, $sub_data ?: null);
  $status = (string) ($sub_data['status'] ?? 'active');
  $commitment_end = slm_subscriptions_commitment_end((int) $user->ID, $plan_slug, (int) ($sub_data['start_date'] ?? time()));

  slm_subscriptions_update_state((int) $user->ID, [
    'plan' => $plan_slug,
    'stripe_customer_id' => (string) ($object['customer'] ?? ''),
    'stripe_subscription_id' => $sub_id,
    'status' => $status,
    'current_period_end' => slm_subscriptions_period_end($sub_data),
    'commitment_end' => $commitment_end,
  ]);

  if ($plan_slug !== '' && slm_subscriptions_is_active_status($status)) {
    $sync = slm_subscriptions_aryeo_apply_profile((int) $user->ID, $plan_slug, '');
    if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message(), 'user_id' => (int) $user->ID];
  }
  return ['ok' => true, 'status' => 'processed', 'user_id' => (int) $user->ID, 'message' => 'Checkout processed.'];
}

function slm_subscriptions_handle_invoice_paid(array $object, ?WP_User $user): array {
  if (!$user instanceof WP_User) $user = slm_subscriptions_resolve_user($object);
  if (!$user instanceof WP_User) return ['ok' => false, 'status' => 'error', 'message' => 'Unable to map invoice to user.'];
  $subscription = slm_subscriptions_extract_subscription($object);
  if (is_wp_error($subscription)) return ['ok' => false, 'status' => 'error', 'message' => $subscription->get_error_message(), 'user_id' => (int) $user->ID];

  $plan_slug = slm_subscriptions_plan_slug_from_event($object, $subscription);
  $status = (string) ($subscription['status'] ?? 'active');
  $commitment_end = slm_subscriptions_commitment_end((int) $user->ID, $plan_slug, (int) ($subscription['start_date'] ?? time()));
  slm_subscriptions_update_state((int) $user->ID, [
    'plan' => $plan_slug,
    'stripe_customer_id' => (string) ($object['customer'] ?? ''),
    'stripe_subscription_id' => (string) ($subscription['id'] ?? ''),
    'status' => $status,
    'current_period_end' => slm_subscriptions_period_end($subscription),
    'commitment_end' => $commitment_end,
  ]);

  if ($plan_slug !== '' && slm_subscriptions_is_active_status($status)) {
    $invoice_id = trim((string) ($object['id'] ?? ''));
    $sync = slm_subscriptions_aryeo_apply_profile((int) $user->ID, $plan_slug, $invoice_id);
    if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message(), 'user_id' => (int) $user->ID];
  }
  return ['ok' => true, 'status' => 'processed', 'user_id' => (int) $user->ID, 'message' => 'Invoice paid processed.'];
}

function slm_subscriptions_handle_invoice_failed(array $object, ?WP_User $user): array {
  if (!$user instanceof WP_User) $user = slm_subscriptions_resolve_user($object);
  if (!$user instanceof WP_User) return ['ok' => false, 'status' => 'error', 'message' => 'Unable to map failed invoice to user.'];
  $subscription = slm_subscriptions_extract_subscription($object);
  if (is_wp_error($subscription)) return ['ok' => false, 'status' => 'error', 'message' => $subscription->get_error_message(), 'user_id' => (int) $user->ID];

  $plan_slug = slm_subscriptions_plan_slug_from_event($object, $subscription);
  $status = (string) ($subscription['status'] ?? 'past_due');
  slm_subscriptions_update_state((int) $user->ID, [
    'plan' => $plan_slug,
    'stripe_customer_id' => (string) ($object['customer'] ?? ''),
    'stripe_subscription_id' => (string) ($subscription['id'] ?? ''),
    'status' => $status,
    'current_period_end' => slm_subscriptions_period_end($subscription),
  ]);

  if ($plan_slug !== '') {
    $sync = slm_subscriptions_aryeo_revoke_profile((int) $user->ID, $plan_slug, 'invoice_failed');
    if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message(), 'user_id' => (int) $user->ID];
  }
  return ['ok' => true, 'status' => 'processed', 'user_id' => (int) $user->ID, 'message' => 'Invoice failed processed.'];
}

function slm_subscriptions_handle_subscription_updated(array $object, ?WP_User $user): array {
  if (!$user instanceof WP_User) $user = slm_subscriptions_resolve_user($object);
  if (!$user instanceof WP_User) return ['ok' => false, 'status' => 'error', 'message' => 'Unable to map updated subscription to user.'];

  $plan_slug = slm_subscriptions_plan_slug_from_event($object, $object);
  $status = (string) ($object['status'] ?? '');
  $commitment_end = slm_subscriptions_commitment_end((int) $user->ID, $plan_slug, (int) ($object['start_date'] ?? time()));
  $enforced = slm_subscriptions_enforce_commitment($object, $commitment_end);
  if (is_wp_error($enforced)) {
    slm_subscriptions_log('commitment_enforcement_failed', ['user_id' => (int) $user->ID, 'subscription_id' => (string) ($object['id'] ?? ''), 'error' => $enforced->get_error_message()], 'error');
  }

  slm_subscriptions_update_state((int) $user->ID, [
    'plan' => $plan_slug,
    'stripe_customer_id' => (string) ($object['customer'] ?? ''),
    'stripe_subscription_id' => (string) ($object['id'] ?? ''),
    'status' => $status,
    'current_period_end' => slm_subscriptions_period_end($object),
    'commitment_end' => $commitment_end,
  ]);

  if ($plan_slug !== '') {
    if (slm_subscriptions_is_active_status($status)) {
      $sync = slm_subscriptions_aryeo_apply_profile((int) $user->ID, $plan_slug, '');
      if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message(), 'user_id' => (int) $user->ID];
    } else {
      $sync = slm_subscriptions_aryeo_revoke_profile((int) $user->ID, $plan_slug, 'subscription_updated');
      if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message(), 'user_id' => (int) $user->ID];
    }
  }
  return ['ok' => true, 'status' => 'processed', 'user_id' => (int) $user->ID, 'message' => 'Subscription updated processed.'];
}

function slm_subscriptions_handle_subscription_deleted(array $object, ?WP_User $user): array {
  if (!$user instanceof WP_User) $user = slm_subscriptions_resolve_user($object);
  if (!$user instanceof WP_User) return ['ok' => false, 'status' => 'error', 'message' => 'Unable to map deleted subscription to user.'];
  $plan_slug = slm_subscriptions_plan_slug_from_event($object, $object);

  slm_subscriptions_update_state((int) $user->ID, [
    'plan' => $plan_slug,
    'stripe_customer_id' => (string) ($object['customer'] ?? ''),
    'stripe_subscription_id' => (string) ($object['id'] ?? ''),
    'status' => 'canceled',
    'current_period_end' => 0,
  ]);

  if ($plan_slug !== '') {
    $sync = slm_subscriptions_aryeo_revoke_profile((int) $user->ID, $plan_slug, 'subscription_deleted');
    if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message(), 'user_id' => (int) $user->ID];
  }
  return ['ok' => true, 'status' => 'processed', 'user_id' => (int) $user->ID, 'message' => 'Subscription deleted processed.'];
}

function slm_subscriptions_process_event(array $event): array {
  $type = (string) ($event['type'] ?? '');
  $object = $event['data']['object'] ?? null;
  if (!is_array($object)) return ['ok' => false, 'status' => 'error', 'message' => 'Invalid payload object.'];
  $user = slm_subscriptions_resolve_user($object);

  switch ($type) {
    case 'checkout.session.completed':
      return slm_subscriptions_handle_checkout_completed($object, $user);
    case 'invoice.paid':
      return slm_subscriptions_handle_invoice_paid($object, $user);
    case 'invoice.payment_failed':
      return slm_subscriptions_handle_invoice_failed($object, $user);
    case 'customer.subscription.updated':
      return slm_subscriptions_handle_subscription_updated($object, $user);
    case 'customer.subscription.deleted':
      return slm_subscriptions_handle_subscription_deleted($object, $user);
    default:
      return ['ok' => true, 'status' => 'ignored', 'message' => 'Unhandled event type: ' . $type];
  }
}

function slm_subscriptions_webhook(WP_REST_Request $req) {
  $raw = (string) $req->get_body();
  if ($raw === '') return new WP_REST_Response(['ok' => false, 'error' => 'empty_payload'], 400);

  $secret = slm_subscriptions_stripe_webhook_secret();
  if ($secret === '') return new WP_REST_Response(['ok' => false, 'error' => 'webhook_secret_not_configured'], 500);

  $signature = (string) $req->get_header('stripe-signature');
  if (!slm_subscriptions_verify_webhook_signature($raw, $signature, $secret)) {
    return new WP_REST_Response(['ok' => false, 'error' => 'invalid_signature'], 401);
  }

  $payload = json_decode($raw, true);
  if (!is_array($payload)) return new WP_REST_Response(['ok' => false, 'error' => 'invalid_json'], 400);

  $event_id = trim((string) ($payload['id'] ?? ''));
  $event_type = trim((string) ($payload['type'] ?? ''));
  if ($event_id === '' || $event_type === '') return new WP_REST_Response(['ok' => false, 'error' => 'invalid_event'], 400);

  $row = slm_subscriptions_get_event_row($event_id);
  if ($row && in_array((string) ($row['status'] ?? ''), ['processed', 'ignored'], true)) {
    return new WP_REST_Response(['ok' => true, 'duplicate' => true], 200);
  }
  if (!$row) {
    slm_subscriptions_insert_event_row($event_id, $event_type);
  } else {
    slm_subscriptions_update_event_row($event_id, 'received', (int) ($row['user_id'] ?? 0), ['retry' => true]);
  }

  $result = slm_subscriptions_process_event($payload);
  $ok = !empty($result['ok']);
  $status = (string) ($result['status'] ?? ($ok ? 'processed' : 'error'));
  $user_id = (int) ($result['user_id'] ?? 0);
  slm_subscriptions_update_event_row($event_id, $status, $user_id, $result);
  slm_subscriptions_log('stripe_event_' . $status, ['event_id' => $event_id, 'event_type' => $event_type, 'user_id' => $user_id, 'message' => (string) ($result['message'] ?? '')], $ok ? 'info' : 'error');

  if (!$ok) return new WP_REST_Response(['ok' => false, 'error' => (string) ($result['message'] ?? 'processing_failed')], 500);
  return new WP_REST_Response(['ok' => true, 'status' => $status], 200);
}

add_action('rest_api_init', function () {
  register_rest_route('slm/v1', '/stripe/webhook', [
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => 'slm_subscriptions_webhook',
  ]);
});

function slm_subscriptions_reconcile_user(int $user_id): array {
  $keys = slm_subscriptions_meta_keys();
  $sub_id = trim((string) get_user_meta($user_id, $keys['stripe_subscription_id'], true));
  if ($sub_id === '') return ['ok' => true, 'status' => 'ignored', 'message' => 'No subscription ID.'];

  $sub = slm_subscriptions_retrieve_subscription($sub_id);
  if (is_wp_error($sub)) return ['ok' => false, 'status' => 'error', 'message' => $sub->get_error_message()];

  $status = (string) ($sub['status'] ?? '');
  $plan_slug = slm_subscriptions_plan_slug_from_event($sub, $sub);
  $commitment_end = slm_subscriptions_commitment_end($user_id, $plan_slug, (int) ($sub['start_date'] ?? time()));
  $enforced = slm_subscriptions_enforce_commitment($sub, $commitment_end);
  if (is_wp_error($enforced)) {
    slm_subscriptions_log('reconcile_commitment_failed', ['user_id' => $user_id, 'subscription_id' => $sub_id, 'error' => $enforced->get_error_message()], 'error');
  }

  slm_subscriptions_update_state($user_id, [
    'plan' => $plan_slug,
    'stripe_customer_id' => (string) ($sub['customer'] ?? ''),
    'stripe_subscription_id' => (string) ($sub['id'] ?? ''),
    'status' => $status,
    'current_period_end' => slm_subscriptions_period_end($sub),
    'commitment_end' => $commitment_end,
  ]);

  if ($plan_slug !== '') {
    if (slm_subscriptions_is_active_status($status)) {
      $sync = slm_subscriptions_aryeo_apply_profile($user_id, $plan_slug, '');
      if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message()];
    } else {
      $sync = slm_subscriptions_aryeo_revoke_profile($user_id, $plan_slug, 'reconcile');
      if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message()];
    }
  }

  return ['ok' => true, 'status' => 'processed', 'message' => 'Reconciled.'];
}

function slm_subscriptions_daily_reconcile(): void {
  $keys = slm_subscriptions_meta_keys();
  $batch = 100;
  $offset = 0;
  do {
    $users = get_users([
      'number' => $batch,
      'offset' => $offset,
      'meta_key' => $keys['stripe_subscription_id'],
      'meta_compare' => 'EXISTS',
      'fields' => ['ID'],
      'orderby' => 'ID',
      'order' => 'ASC',
    ]);
    if (!is_array($users) || empty($users)) break;
    foreach ($users as $u) {
      $uid = (int) ($u->ID ?? 0);
      if ($uid <= 0) continue;
      $result = slm_subscriptions_reconcile_user($uid);
      slm_subscriptions_log('daily_reconcile', ['user_id' => $uid, 'result' => $result], !empty($result['ok']) ? 'info' : 'error');
    }
    $offset += $batch;
  } while (count($users) === $batch);
}

add_action('init', function () {
  if (!wp_next_scheduled('slm_subscriptions_daily_reconcile')) {
    wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'slm_subscriptions_daily_reconcile');
  }
}, 8);
add_action('slm_subscriptions_daily_reconcile', 'slm_subscriptions_daily_reconcile');

function slm_get_user_subscription_summary(int $user_id): array {
  $keys = slm_subscriptions_meta_keys();
  $plan_slug = sanitize_key((string) get_user_meta($user_id, $keys['plan'], true));
  $status = sanitize_key((string) get_user_meta($user_id, $keys['status'], true));
  $period_end = (int) get_user_meta($user_id, $keys['current_period_end'], true);
  $commitment_end = (int) get_user_meta($user_id, $keys['commitment_end'], true);
  $sub_id = trim((string) get_user_meta($user_id, $keys['stripe_subscription_id'], true));
  $plan = slm_subscriptions_plan($plan_slug);
  $plan_label = $plan ? (string) ($plan['label'] ?? $plan_slug) : 'No active plan';

  return [
    'plan_slug' => $plan_slug,
    'plan_label' => $plan_label,
    'status' => $status !== '' ? $status : 'non_member',
    'status_label' => slm_subscriptions_status_label($status !== '' ? $status : 'non_member'),
    'is_active' => slm_subscriptions_is_active_status($status),
    'subscription_id' => $sub_id,
    'current_period_end' => $period_end,
    'current_period_end_label' => $period_end > 0 ? wp_date('M j, Y', $period_end) : 'N/A',
    'commitment_end' => $commitment_end,
    'commitment_end_label' => $commitment_end > 0 ? wp_date('M j, Y', $commitment_end) : 'N/A',
    'within_commitment' => $commitment_end > time(),
    'can_manage_billing' => slm_subscriptions_stripe_secret_key() !== '' && $sub_id !== '',
    'manage_billing_url' => slm_subscriptions_manage_billing_url(),
  ];
}

function slm_subscriptions_active_plans_for_cta(): array {
  $plans = [];
  foreach (slm_subscription_plans() as $slug => $plan) {
    if (!is_array($plan)) continue;
    if (empty($plan['active'])) continue;
    if (trim((string) ($plan['stripe_price_id'] ?? '')) === '') continue;
    $plans[$slug] = $plan;
  }
  return $plans;
}

add_action('admin_menu', function () {
  add_options_page('SLM Subscriptions', 'SLM Subscriptions', 'manage_options', 'slm-subscriptions', 'slm_subscriptions_render_settings_page');
});

add_action('admin_init', function () {
  register_setting('slm_subscriptions', 'slm_stripe_secret_key', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_stripe_publishable_key', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_stripe_webhook_secret', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_stripe_portal_config_id', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_stripe_checkout_success_url', ['sanitize_callback' => function ($v) { return esc_url_raw((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_stripe_checkout_cancel_url', ['sanitize_callback' => function ($v) { return esc_url_raw((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_aryeo_affiliate_removal_path', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_subscription_plans', ['sanitize_callback' => 'slm_subscriptions_sanitize_plans']);
  register_setting('slm_subscriptions', 'slm_subscription_benefit_profiles', ['sanitize_callback' => 'slm_subscriptions_sanitize_profiles']);

  add_settings_section('slm_subscriptions_main', 'Stripe + Aryeo Subscription Settings', '__return_false', 'slm_subscriptions');

  add_settings_field('slm_stripe_secret_key', 'Stripe Secret Key', function () {
    echo '<input type="password" name="slm_stripe_secret_key" value="' . esc_attr(slm_subscriptions_stripe_secret_key()) . '" class="regular-text" autocomplete="off" />';
    echo '<p class="description">Used for server-side Stripe API requests.</p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_stripe_webhook_secret', 'Stripe Webhook Secret', function () {
    echo '<input type="text" name="slm_stripe_webhook_secret" value="' . esc_attr(slm_subscriptions_stripe_webhook_secret()) . '" class="regular-text code" />';
    echo '<p class="description">Required for webhook signature verification.</p>';
    echo '<p class="description">Webhook URL: <code>' . esc_html(rest_url('slm/v1/stripe/webhook')) . '</code></p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_stripe_publishable_key', 'Stripe Publishable Key', function () {
    echo '<input type="text" name="slm_stripe_publishable_key" value="' . esc_attr(slm_subscriptions_stripe_publishable_key()) . '" class="regular-text code" />';
    echo '<p class="description">Optional for future client-side Stripe elements.</p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_stripe_portal_config_id', 'Billing Portal Config ID', function () {
    echo '<input type="text" name="slm_stripe_portal_config_id" value="' . esc_attr(slm_subscriptions_stripe_portal_config_id()) . '" class="regular-text code" placeholder="bpc_..." />';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_stripe_checkout_success_url', 'Checkout Success URL', function () {
    echo '<input type="url" name="slm_stripe_checkout_success_url" value="' . esc_attr(slm_subscriptions_checkout_success_url()) . '" class="regular-text code" />';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_stripe_checkout_cancel_url', 'Checkout Cancel URL', function () {
    echo '<input type="url" name="slm_stripe_checkout_cancel_url" value="' . esc_attr(slm_subscriptions_checkout_cancel_url()) . '" class="regular-text code" />';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_subscription_plans', 'Plan Mapping (JSON)', function () {
    $json = wp_json_encode(slm_subscription_plans(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo '<textarea name="slm_subscription_plans" class="large-text code" rows="16">' . esc_textarea((string) $json) . '</textarea>';
    echo '<p class="description">Map plan slug to Stripe price, commitment months, billing interval, credit amount, and Aryeo profile key.</p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_subscription_benefit_profiles', 'Aryeo Benefit Profiles (JSON)', function () {
    $json = wp_json_encode(slm_subscription_benefit_profiles(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo '<textarea name="slm_subscription_benefit_profiles" class="large-text code" rows="10">' . esc_textarea((string) $json) . '</textarea>';
    echo '<p class="description">Each profile supports <code>affiliate_id</code>, <code>credit_amount</code>, and <code>debit_on_revoke</code>.</p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_aryeo_affiliate_removal_path', 'Aryeo Affiliate Removal Endpoint (optional)', function () {
    echo '<input type="text" name="slm_aryeo_affiliate_removal_path" value="' . esc_attr(slm_subscriptions_affiliate_removal_path()) . '" class="regular-text code" placeholder="/customer-team-members/{membership_id}" />';
    echo '<p class="description">Optional DELETE path template for removing affiliate memberships.</p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');
});

function slm_subscriptions_render_settings_page(): void {
  if (!current_user_can('manage_options')) return;

  $test = null;
  if (isset($_POST['slm_subscriptions_test']) && check_admin_referer('slm_subscriptions_test')) {
    $test = slm_subscriptions_stripe_request('GET', '/prices', ['limit' => 1]);
  }

  echo '<div class="wrap">';
  echo '<h1>SLM Subscriptions</h1>';
  echo '<p>Stripe Billing + Aryeo sync for memberships.</p>';
  settings_errors('slm_subscriptions');

  if (is_wp_error($test)) {
    echo '<div class="notice notice-error"><p>' . esc_html($test->get_error_message()) . '</p></div>';
  } elseif (is_array($test) && $test !== []) {
    $count = is_array($test['data'] ?? null) ? count((array) $test['data']) : 0;
    echo '<div class="notice notice-success"><p>Stripe connection OK. Retrieved ' . esc_html((string) $count) . ' price record(s).</p></div>';
  }

  echo '<form method="post" action="options.php">';
  settings_fields('slm_subscriptions');
  do_settings_sections('slm_subscriptions');
  submit_button('Save Subscription Settings');
  echo '</form>';

  echo '<hr />';
  echo '<form method="post">';
  wp_nonce_field('slm_subscriptions_test');
  submit_button('Test Stripe API Connection', 'secondary', 'slm_subscriptions_test');
  echo '</form>';

  $logs = get_option('slm_subscription_sync_log', []);
  if (!is_array($logs)) $logs = [];
  $logs = array_reverse(array_slice($logs, -20));
  echo '<hr />';
  echo '<h2>Recent Sync Log</h2>';
  if (empty($logs)) {
    echo '<p>No sync events yet.</p>';
  } else {
    echo '<table class="widefat striped"><thead><tr><th>Time</th><th>Level</th><th>Event</th><th>Context</th></tr></thead><tbody>';
    foreach ($logs as $entry) {
      echo '<tr>';
      echo '<td>' . esc_html((string) ($entry['timestamp'] ?? '')) . '</td>';
      echo '<td>' . esc_html(strtoupper((string) ($entry['level'] ?? 'info'))) . '</td>';
      echo '<td>' . esc_html((string) ($entry['event'] ?? '')) . '</td>';
      echo '<td><code style="white-space:pre-wrap;display:block;">' . esc_html(wp_json_encode($entry['context'] ?? [])) . '</code></td>';
      echo '</tr>';
    }
    echo '</tbody></table>';
  }
  echo '</div>';
}
