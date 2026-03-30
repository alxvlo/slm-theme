<?php
if (!defined('ABSPATH')) exit;

const SLM_MEMBER_CREDITS_SCHEMA_VERSION = '1.0.0';

function slm_member_credits_ledger_table(): string {
  global $wpdb;
  return $wpdb->prefix . 'slm_member_credit_ledger';
}

function slm_member_credits_flags_table(): string {
  global $wpdb;
  return $wpdb->prefix . 'slm_member_credit_flags';
}

function slm_member_credits_schema_install(): void {
  global $wpdb;
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  $charset = $wpdb->get_charset_collate();
  $ledger = slm_member_credits_ledger_table();
  $flags = slm_member_credits_flags_table();

  $sql_ledger = "CREATE TABLE {$ledger} (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL,
    subscription_provider varchar(20) NOT NULL DEFAULT '',
    subscription_external_id varchar(191) NOT NULL DEFAULT '',
    plan_slug varchar(100) NOT NULL DEFAULT '',
    term_code varchar(20) NOT NULL DEFAULT '',
    credit_key varchar(100) NOT NULL DEFAULT '',
    txn_type varchar(30) NOT NULL DEFAULT '',
    quantity_delta int NOT NULL DEFAULT 0,
    cycle_start_gmt datetime NULL,
    cycle_end_gmt datetime NULL,
    expires_at_gmt datetime NULL,
    source varchar(50) NOT NULL DEFAULT '',
    source_ref varchar(191) NULL,
    dedupe_key varchar(191) NOT NULL,
    meta_json longtext NULL,
    created_at_gmt datetime NOT NULL,
    created_by_user_id bigint(20) unsigned NULL,
    PRIMARY KEY (id),
    UNIQUE KEY dedupe_key (dedupe_key),
    KEY user_id (user_id),
    KEY subscription_external_id (subscription_external_id),
    KEY plan_slug (plan_slug),
    KEY credit_key (credit_key),
    KEY created_at_gmt (created_at_gmt)
  ) {$charset};";

  $sql_flags = "CREATE TABLE {$flags} (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL DEFAULT 0,
    order_ref varchar(191) NOT NULL DEFAULT '',
    flag_type varchar(50) NOT NULL DEFAULT '',
    status varchar(20) NOT NULL DEFAULT 'open',
    message text NULL,
    meta_json longtext NULL,
    created_at_gmt datetime NOT NULL,
    resolved_at_gmt datetime NULL,
    resolved_by_user_id bigint(20) unsigned NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY order_ref (order_ref),
    KEY flag_type (flag_type),
    KEY status (status),
    KEY created_at_gmt (created_at_gmt)
  ) {$charset};";

  dbDelta($sql_ledger);
  dbDelta($sql_flags);
  update_option('slm_member_credits_schema_version', SLM_MEMBER_CREDITS_SCHEMA_VERSION, false);
}

add_action('after_switch_theme', 'slm_member_credits_schema_install');
add_action('init', function () {
  if (wp_installing()) return;
  if ((string) get_option('slm_member_credits_schema_version', '') !== SLM_MEMBER_CREDITS_SCHEMA_VERSION) {
    slm_member_credits_schema_install();
  }
}, 5);

function slm_member_credits_auto_deduct_enabled(): bool {
  $raw = get_option('slm_member_credits_auto_deduct_enabled', '1');
  if (is_bool($raw)) return $raw;
  return !in_array(strtolower(trim((string) $raw)), ['0', 'false', 'off', 'no', ''], true);
}

function slm_member_credits_overage_allowed(): bool {
  $raw = get_option('slm_member_credits_overage_allowed', '1');
  if (is_bool($raw)) return $raw;
  return !in_array(strtolower(trim((string) $raw)), ['0', 'false', 'off', 'no', ''], true);
}

function slm_member_credits_supported_keys(): array {
  return [
    'edited_reel',
    'branded_insta_post',
    'talking_head_video',
    'horizontal_video',
    'social_media_post_plan',
    'strategic_media_analysis',
    'listing_shoot',
    'ai_video',
    'agent_intro_video',
    'staged_or_dusk',
  ];
}

function slm_member_credits_key_labels(): array {
  return [
    'edited_reel' => 'Edited Reel',
    'branded_insta_post' => 'Branded Insta Post',
    'talking_head_video' => 'Talking Head Video',
    'horizontal_video' => 'Horizontal Video / Tour',
    'social_media_post_plan' => 'Social Media Post Plan',
    'strategic_media_analysis' => 'Strategic Media Analysis',
    'listing_shoot' => 'Listing Shoot',
    'ai_video' => 'AI Video',
    'agent_intro_video' => 'Agent Intro Video',
    'staged_or_dusk' => 'Staged/Dusk Conversion',
  ];
}

function slm_member_credits_default_service_mapping(): array {
  return [
    'by_service_name' => [
      'Edited Reel' => [['credit_key' => 'edited_reel', 'qty' => 1]],
      'Talking Head Video' => [['credit_key' => 'talking_head_video', 'qty' => 1]],
      'Horizontal Video' => [['credit_key' => 'horizontal_video', 'qty' => 1]],
      'Horizontal Video/Tour' => [['credit_key' => 'horizontal_video', 'qty' => 1]],
      'Branded Insta Post' => [['credit_key' => 'branded_insta_post', 'qty' => 1]],
      'Branded Instagram Post' => [['credit_key' => 'branded_insta_post', 'qty' => 1]],
      'Social Media Post Plan' => [['credit_key' => 'social_media_post_plan', 'qty' => 1]],
      'Strategic Media Analysis' => [['credit_key' => 'strategic_media_analysis', 'qty' => 1]],
      'Listing Shoot' => [['credit_key' => 'listing_shoot', 'qty' => 1]],
      'AI Video' => [['credit_key' => 'ai_video', 'qty' => 1]],
      'Agent Intro Video' => [['credit_key' => 'agent_intro_video', 'qty' => 1]],
      'Staged Photo' => [['credit_key' => 'staged_or_dusk', 'qty' => 1]],
      'Dusk Conversion' => [['credit_key' => 'staged_or_dusk', 'qty' => 1]],
    ],
    'by_service_id' => new stdClass(),
  ];
}

function slm_member_credits_decode_json($value): array {
  if (is_array($value)) return $value;
  $raw = is_string($value) ? trim(wp_unslash($value)) : '';
  if ($raw === '') return [];
  $decoded = json_decode($raw, true);
  return is_array($decoded) ? $decoded : [];
}

function slm_member_credits_sanitize_service_mapping($value): array {
  $decoded = slm_member_credits_decode_json($value);
  if ($decoded === []) return slm_member_credits_default_service_mapping();
  $clean = ['by_service_name' => [], 'by_service_id' => []];
  $allowed_keys = array_fill_keys(slm_member_credits_supported_keys(), true);

  foreach (['by_service_name', 'by_service_id'] as $bucket) {
    $entries = $decoded[$bucket] ?? [];
    if (!is_array($entries)) continue;
    foreach ($entries as $key => $rules) {
      $map_key = trim((string) $key);
      if ($map_key === '' || !is_array($rules)) continue;
      $sanitized_rules = [];
      foreach ($rules as $rule) {
        if (!is_array($rule)) continue;
        $credit_key = sanitize_key((string) ($rule['credit_key'] ?? ''));
        if ($credit_key === '' || !isset($allowed_keys[$credit_key])) continue;
        $qty = max(1, (int) ($rule['qty'] ?? 1));
        $sanitized_rules[] = ['credit_key' => $credit_key, 'qty' => $qty];
      }
      if ($sanitized_rules !== []) $clean[$bucket][$map_key] = $sanitized_rules;
    }
  }

  if ($clean['by_service_name'] === [] && $clean['by_service_id'] === []) {
    return slm_member_credits_default_service_mapping();
  }
  return $clean;
}

function slm_member_credits_service_mapping(): array {
  $saved = get_option('slm_credit_service_mapping_json', []);
  if (!is_array($saved) || $saved === []) return slm_member_credits_default_service_mapping();
  return $saved;
}

function slm_member_credits_time_to_gmt_string(int $timestamp): string {
  return gmdate('Y-m-d H:i:s', max(0, $timestamp));
}

function slm_member_credits_term_label(string $term_code): string {
  $term_code = sanitize_key($term_code);
  $labels = [
    'm2m' => 'Month-to-Month',
    '6m' => '6-Month Agreement',
    '12m' => '12-Month Agreement',
  ];
  return $labels[$term_code] ?? strtoupper($term_code);
}

function slm_member_credits_user_subscription_context(int $user_id): array {
  $keys = function_exists('slm_subscriptions_meta_keys') ? slm_subscriptions_meta_keys() : [];
  $plan_slug = sanitize_key((string) get_user_meta($user_id, (string) ($keys['plan'] ?? 'slm_subscription_plan'), true));
  $provider = sanitize_key((string) get_user_meta($user_id, (string) ($keys['provider'] ?? 'slm_subscription_provider'), true));
  $status = sanitize_key((string) get_user_meta($user_id, (string) ($keys['status'] ?? 'slm_subscription_status'), true));
  $term_code = sanitize_key((string) get_user_meta($user_id, (string) ($keys['term_code'] ?? 'slm_subscription_term_code'), true));
  $term_started_at = (int) get_user_meta($user_id, (string) ($keys['term_started_at'] ?? 'slm_subscription_term_started_at'), true);
  $term_ends_at = (int) get_user_meta($user_id, (string) ($keys['term_ends_at'] ?? 'slm_subscription_term_ends_at'), true);
  $commitment_end = (int) get_user_meta($user_id, (string) ($keys['commitment_end'] ?? 'slm_subscription_commitment_end'), true);
  $stripe_sub_id = trim((string) get_user_meta($user_id, (string) ($keys['stripe_subscription_id'] ?? 'slm_stripe_subscription_id'), true));
  $square_sub_id = trim((string) get_user_meta($user_id, (string) ($keys['square_subscription_id'] ?? 'slm_square_subscription_id'), true));
  $external_id = $provider === 'square' ? $square_sub_id : $stripe_sub_id;
  if ($external_id === '') $external_id = $square_sub_id !== '' ? $square_sub_id : $stripe_sub_id;
  $plan = function_exists('slm_subscriptions_plan') ? slm_subscriptions_plan($plan_slug) : null;
  return [
    'user_id' => $user_id,
    'plan_slug' => $plan_slug,
    'plan' => is_array($plan) ? $plan : null,
    'provider' => $provider,
    'status' => $status,
    'term_code' => $term_code,
    'term_label' => slm_member_credits_term_label($term_code),
    'term_started_at' => $term_started_at,
    'term_ends_at' => $term_ends_at,
    'commitment_end' => $commitment_end,
    'subscription_external_id' => $external_id,
    'is_active' => function_exists('slm_subscriptions_is_active_status') ? slm_subscriptions_is_active_status($status) : in_array($status, ['active', 'trialing'], true),
  ];
}

function slm_member_credits_insert_ledger_txn(array $txn) {
  global $wpdb;
  $table = slm_member_credits_ledger_table();
  $dedupe_key = trim((string) ($txn['dedupe_key'] ?? ''));
  if ($dedupe_key === '') return new WP_Error('slm_member_credits_missing_dedupe', 'Credit ledger transaction dedupe key is required.');

  $row = [
    'user_id' => max(0, (int) ($txn['user_id'] ?? 0)),
    'subscription_provider' => sanitize_key((string) ($txn['subscription_provider'] ?? '')),
    'subscription_external_id' => trim((string) ($txn['subscription_external_id'] ?? '')),
    'plan_slug' => sanitize_key((string) ($txn['plan_slug'] ?? '')),
    'term_code' => sanitize_key((string) ($txn['term_code'] ?? '')),
    'credit_key' => sanitize_key((string) ($txn['credit_key'] ?? '')),
    'txn_type' => sanitize_key((string) ($txn['txn_type'] ?? '')),
    'quantity_delta' => (int) ($txn['quantity_delta'] ?? 0),
    'cycle_start_gmt' => $txn['cycle_start_gmt'] ?? null,
    'cycle_end_gmt' => $txn['cycle_end_gmt'] ?? null,
    'expires_at_gmt' => $txn['expires_at_gmt'] ?? null,
    'source' => sanitize_key((string) ($txn['source'] ?? '')),
    'source_ref' => trim((string) ($txn['source_ref'] ?? '')),
    'dedupe_key' => $dedupe_key,
    'meta_json' => isset($txn['meta']) ? wp_json_encode($txn['meta']) : null,
    'created_at_gmt' => current_time('mysql', true),
    'created_by_user_id' => isset($txn['created_by_user_id']) ? max(0, (int) $txn['created_by_user_id']) : null,
  ];
  if ($row['user_id'] <= 0 || $row['credit_key'] === '' || $row['txn_type'] === '') {
    return new WP_Error('slm_member_credits_invalid_txn', 'Credit ledger transaction is missing required fields.');
  }

  $result = $wpdb->insert($table, $row, ['%d','%s','%s','%s','%s','%s','%s','%d','%s','%s','%s','%s','%s','%s','%s','%s','%d']);
  if ($result === false) {
    if (strpos((string) $wpdb->last_error, 'dedupe_key') !== false) {
      return ['ok' => true, 'duplicate' => true];
    }
    return new WP_Error('slm_member_credits_insert_failed', 'Unable to write credit ledger transaction.', ['db_error' => $wpdb->last_error]);
  }
  return ['ok' => true, 'duplicate' => false, 'id' => (int) $wpdb->insert_id];
}

function slm_member_credits_open_flag(int $user_id, string $flag_type, string $order_ref, string $message, array $meta = []) {
  global $wpdb;
  $table = slm_member_credits_flags_table();
  $flag_type = sanitize_key($flag_type);
  if ($flag_type === '') return new WP_Error('slm_member_credits_flag_type', 'Flag type is required.');
  $existing_id = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT id FROM {$table} WHERE user_id = %d AND order_ref = %s AND flag_type = %s AND status = %s AND message = %s ORDER BY id DESC LIMIT 1",
    max(0, $user_id),
    trim((string) $order_ref),
    $flag_type,
    'open',
    sanitize_textarea_field($message)
  ));
  if ($existing_id > 0) return ['ok' => true, 'duplicate' => true, 'id' => $existing_id];
  $result = $wpdb->insert($table, [
    'user_id' => max(0, $user_id),
    'order_ref' => trim((string) $order_ref),
    'flag_type' => $flag_type,
    'status' => 'open',
    'message' => sanitize_textarea_field($message),
    'meta_json' => wp_json_encode($meta),
    'created_at_gmt' => current_time('mysql', true),
    'resolved_at_gmt' => null,
    'resolved_by_user_id' => null,
  ], ['%d','%s','%s','%s','%s','%s','%s','%s','%d']);
  if ($result === false) {
    return new WP_Error('slm_member_credits_flag_insert_failed', 'Unable to create member credits flag.', ['db_error' => $wpdb->last_error]);
  }
  return ['ok' => true, 'id' => (int) $wpdb->insert_id];
}

function slm_member_credits_resolve_flag(int $flag_id, int $resolved_by_user_id): bool {
  global $wpdb;
  $table = slm_member_credits_flags_table();
  $updated = $wpdb->update($table, [
    'status' => 'resolved',
    'resolved_at_gmt' => current_time('mysql', true),
    'resolved_by_user_id' => max(0, $resolved_by_user_id),
  ], ['id' => max(0, $flag_id)], ['%s','%s','%d'], ['%d']);
  return $updated !== false;
}

function slm_member_credits_balance_snapshot(int $user_id, ?int $as_of = null): array {
  global $wpdb;
  $table = slm_member_credits_ledger_table();
  $as_of = $as_of ?: time();
  $as_of_sql = gmdate('Y-m-d H:i:s', $as_of);
  $wpdb->last_error = '';
  $rows = $wpdb->get_results($wpdb->prepare(
    "SELECT credit_key, SUM(quantity_delta) AS qty
     FROM {$table}
     WHERE user_id = %d
       AND (expires_at_gmt IS NULL OR expires_at_gmt = '0000-00-00 00:00:00' OR expires_at_gmt > %s)
     GROUP BY credit_key",
    $user_id,
    $as_of_sql
  ), ARRAY_A);
  if (!is_array($rows)) $rows = [];

  $by_key = array_fill_keys(slm_member_credits_supported_keys(), 0);
  foreach ($rows as $row) {
    if (!is_array($row)) continue;
    $credit_key = sanitize_key((string) ($row['credit_key'] ?? ''));
    if ($credit_key === '') continue;
    $by_key[$credit_key] = (int) round((float) ($row['qty'] ?? 0));
  }

  $labels = slm_member_credits_key_labels();
  $balances = [];
  foreach ($by_key as $credit_key => $qty) {
    $balances[] = [
      'credit_key' => $credit_key,
      'label' => (string) ($labels[$credit_key] ?? $credit_key),
      'qty' => (int) $qty,
      'is_negative' => $qty < 0,
    ];
  }
  usort($balances, static function (array $a, array $b): int {
    if ((int) ($a['qty'] ?? 0) === (int) ($b['qty'] ?? 0)) return strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
    return ((int) ($b['qty'] ?? 0)) <=> ((int) ($a['qty'] ?? 0));
  });

  return [
    'user_id' => $user_id,
    'as_of' => $as_of,
    'balances' => $balances,
    'by_key' => $by_key,
    'db_error' => (string) $wpdb->last_error,
  ];
}

function slm_member_credits_recent_ledger(int $user_id, int $limit = 50): array {
  global $wpdb;
  $table = slm_member_credits_ledger_table();
  $limit = max(1, min(200, $limit));
  $rows = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$table} WHERE user_id = %d ORDER BY id DESC LIMIT %d",
    $user_id,
    $limit
  ), ARRAY_A);
  return is_array($rows) ? $rows : [];
}

function slm_member_credits_open_flags(int $user_id, int $limit = 100): array {
  global $wpdb;
  $table = slm_member_credits_flags_table();
  $limit = max(1, min(200, $limit));
  $rows = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$table} WHERE user_id = %d AND status = %s ORDER BY id DESC LIMIT %d",
    $user_id,
    'open',
    $limit
  ), ARRAY_A);
  return is_array($rows) ? $rows : [];
}

function slm_member_credits_open_flag_count(int $user_id): int {
  global $wpdb;
  $table = slm_member_credits_flags_table();
  $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND status = %s", $user_id, 'open'));
  return max(0, (int) $count);
}

function slm_member_credits_plan_entitlements(array $plan): array {
  $raw = $plan['entitlements'] ?? [];
  if (!is_array($raw)) return [];
  $allowed = array_fill_keys(slm_member_credits_supported_keys(), true);
  $clean = [];
  foreach ($raw as $credit_key => $qty) {
    $credit_key = sanitize_key((string) $credit_key);
    if ($credit_key === '' || !isset($allowed[$credit_key])) continue;
    $clean[$credit_key] = max(0, (int) $qty);
  }
  return array_filter($clean, static function ($qty) { return (int) $qty > 0; });
}

function slm_member_credits_term_option(array $plan, string $term_code): array {
  $term_code = sanitize_key($term_code);
  $options = $plan['square_term_options'] ?? [];
  if (is_array($options) && isset($options[$term_code]) && is_array($options[$term_code])) {
    return $options[$term_code];
  }
  return [];
}

function slm_member_credits_rollover_policy(array $plan): string {
  $policy = sanitize_key((string) ($plan['rollover_policy'] ?? 'none'));
  if (!in_array($policy, ['none', 'social_bonus_only', 'all_until_term_end'], true)) $policy = 'none';
  return $policy;
}

function slm_member_credits_cycle_window_from_square_subscription(array $subscription, string $billing_interval = 'month'): array {
  $cycle_end_ts = function_exists('slm_subscriptions_square_period_end') ? (int) slm_subscriptions_square_period_end($subscription) : 0;
  if ($cycle_end_ts <= 0) $cycle_end_ts = strtotime((string) ($subscription['charged_through_date'] ?? '')) ?: 0;
  if ($cycle_end_ts <= 0) $cycle_end_ts = time();
  $interval = sanitize_key($billing_interval);
  if (!in_array($interval, ['day', 'week', 'month', 'year'], true)) $interval = 'month';
  $cycle_start_ts = strtotime('-1 ' . $interval, $cycle_end_ts);
  if (!$cycle_start_ts) $cycle_start_ts = max(0, $cycle_end_ts - MONTH_IN_SECONDS);
  return [
    'cycle_start_ts' => (int) $cycle_start_ts,
    'cycle_end_ts' => (int) $cycle_end_ts,
    'cycle_start_gmt' => slm_member_credits_time_to_gmt_string((int) $cycle_start_ts),
    'cycle_end_gmt' => slm_member_credits_time_to_gmt_string((int) $cycle_end_ts),
  ];
}

function slm_member_credits_expiry_for_grant(string $rollover_policy, bool $is_bonus, array $user_ctx, int $cycle_end_ts): ?string {
  $term_end_ts = (int) ($user_ctx['term_ends_at'] ?? 0);
  if ($term_end_ts <= 0) $term_end_ts = (int) ($user_ctx['commitment_end'] ?? 0);
  if ($rollover_policy === 'all_until_term_end') {
    return $term_end_ts > 0 ? slm_member_credits_time_to_gmt_string($term_end_ts) : null;
  }
  if ($rollover_policy === 'social_bonus_only' && $is_bonus) {
    return $term_end_ts > 0 ? slm_member_credits_time_to_gmt_string($term_end_ts) : null;
  }
  return slm_member_credits_time_to_gmt_string($cycle_end_ts);
}

function slm_member_credits_grant_cycle_for_square_invoice(int $user_id, array $subscription, string $invoice_id = '', string $source = 'square_invoice'): array {
  if (!function_exists('slm_subscriptions_plan')) {
    return ['ok' => false, 'status' => 'error', 'message' => 'Subscriptions module unavailable.'];
  }
  $user_ctx = slm_member_credits_user_subscription_context($user_id);
  $plan_slug = sanitize_key((string) ($user_ctx['plan_slug'] ?? ''));
  if ($plan_slug === '') return ['ok' => false, 'status' => 'error', 'message' => 'No active plan slug found on user.', 'user_id' => $user_id];
  $plan = slm_subscriptions_plan($plan_slug);
  if (!is_array($plan)) return ['ok' => false, 'status' => 'error', 'message' => 'Unknown membership plan.', 'user_id' => $user_id];
  $entitlements = slm_member_credits_plan_entitlements($plan);
  if ($entitlements === []) return ['ok' => true, 'status' => 'ignored', 'message' => 'No entitlements configured.', 'user_id' => $user_id];

  $term_code = sanitize_key((string) ($user_ctx['term_code'] ?? ''));
  $term_option = slm_member_credits_term_option($plan, $term_code);
  $billing_interval = sanitize_key((string) ($term_option['billing_interval'] ?? ($plan['billing_interval'] ?? 'month')));
  $window = slm_member_credits_cycle_window_from_square_subscription($subscription, $billing_interval ?: 'month');
  $rollover_policy = slm_member_credits_rollover_policy($plan);
  $source_ref = trim($invoice_id) !== '' ? trim($invoice_id) : ('sub:' . trim((string) ($subscription['id'] ?? '')));

  $granted = [];
  foreach ($entitlements as $credit_key => $qty) {
    $insert = slm_member_credits_insert_ledger_txn([
      'user_id' => $user_id,
      'subscription_provider' => 'square',
      'subscription_external_id' => (string) ($subscription['id'] ?? ''),
      'plan_slug' => $plan_slug,
      'term_code' => $term_code,
      'credit_key' => $credit_key,
      'txn_type' => 'grant',
      'quantity_delta' => (int) $qty,
      'cycle_start_gmt' => $window['cycle_start_gmt'],
      'cycle_end_gmt' => $window['cycle_end_gmt'],
      'expires_at_gmt' => slm_member_credits_expiry_for_grant($rollover_policy, false, $user_ctx, (int) $window['cycle_end_ts']),
      'source' => $source,
      'source_ref' => $source_ref,
      'dedupe_key' => 'grant:' . md5(implode('|', [(string) ($subscription['id'] ?? ''), $plan_slug, $term_code, $credit_key, $window['cycle_start_gmt']])),
      'meta' => ['invoice_id' => $invoice_id, 'subscription_id' => (string) ($subscription['id'] ?? '')],
    ]);
    if (is_wp_error($insert)) return ['ok' => false, 'status' => 'error', 'message' => $insert->get_error_message(), 'user_id' => $user_id];
    $granted[] = ['credit_key' => $credit_key, 'qty' => (int) $qty, 'duplicate' => !empty($insert['duplicate'])];
  }

  $bonus_qty = 0;
  if ($term_code === '12m') {
    $bonus_qty = max(0, (int) ($term_option['bonus_listing_shoot_per_cycle'] ?? 1));
  }
  if ($bonus_qty > 0) {
    $insert = slm_member_credits_insert_ledger_txn([
      'user_id' => $user_id,
      'subscription_provider' => 'square',
      'subscription_external_id' => (string) ($subscription['id'] ?? ''),
      'plan_slug' => $plan_slug,
      'term_code' => $term_code,
      'credit_key' => 'listing_shoot',
      'txn_type' => 'bonus_grant',
      'quantity_delta' => $bonus_qty,
      'cycle_start_gmt' => $window['cycle_start_gmt'],
      'cycle_end_gmt' => $window['cycle_end_gmt'],
      'expires_at_gmt' => slm_member_credits_expiry_for_grant($rollover_policy, true, $user_ctx, (int) $window['cycle_end_ts']),
      'source' => $source,
      'source_ref' => $source_ref,
      'dedupe_key' => 'bonus:' . md5(implode('|', [(string) ($subscription['id'] ?? ''), $plan_slug, $term_code, 'listing_shoot', $window['cycle_start_gmt']])),
      'meta' => ['invoice_id' => $invoice_id, 'bonus' => 'listing_shoot'],
    ]);
    if (is_wp_error($insert)) return ['ok' => false, 'status' => 'error', 'message' => $insert->get_error_message(), 'user_id' => $user_id];
    $granted[] = ['credit_key' => 'listing_shoot', 'qty' => $bonus_qty, 'bonus' => true, 'duplicate' => !empty($insert['duplicate'])];
  }

  if (function_exists('slm_subscriptions_log')) {
    slm_subscriptions_log('member_credits_grant_cycle', [
      'user_id' => $user_id,
      'plan_slug' => $plan_slug,
      'term_code' => $term_code,
      'invoice_id' => $invoice_id,
      'source' => $source,
      'cycle_start' => $window['cycle_start_gmt'],
      'cycle_end' => $window['cycle_end_gmt'],
      'granted' => $granted,
    ], 'info');
  }
  return ['ok' => true, 'status' => 'processed', 'user_id' => $user_id, 'plan_slug' => $plan_slug, 'term_code' => $term_code, 'granted' => $granted];
}

function slm_member_credits_reconcile_user(int $user_id): array {
  if (!function_exists('slm_subscriptions_square_retrieve_subscription')) {
    return ['ok' => false, 'status' => 'error', 'message' => 'Subscriptions module unavailable.'];
  }
  $ctx = slm_member_credits_user_subscription_context($user_id);
  if (($ctx['provider'] ?? '') !== 'square') return ['ok' => true, 'status' => 'ignored', 'message' => 'Not a Square member.'];
  if (empty($ctx['is_active'])) return ['ok' => true, 'status' => 'ignored', 'message' => 'Member not active.'];
  $sub_id = trim((string) ($ctx['subscription_external_id'] ?? ''));
  if ($sub_id === '') return ['ok' => true, 'status' => 'ignored', 'message' => 'No Square subscription ID.'];
  $sub = slm_subscriptions_square_retrieve_subscription($sub_id);
  if (is_wp_error($sub) || !is_array($sub)) {
    return ['ok' => false, 'status' => 'error', 'message' => is_wp_error($sub) ? $sub->get_error_message() : 'Invalid subscription payload.'];
  }
  return slm_member_credits_grant_cycle_for_square_invoice($user_id, $sub, '', 'reconcile');
}

function slm_member_credits_aryeo_order_items_for_mapping(array $normalized_order): array {
  $raw = $normalized_order['raw'] ?? [];
  if (!is_array($raw)) $raw = [];
  $items = $raw['items'] ?? [];
  if (!is_array($items)) $items = [];
  $result = [];
  foreach ($items as $item) {
    if (!is_array($item)) continue;
    $product = $item['product'] ?? [];
    if (!is_array($product)) $product = [];
    $name = trim((string) ($item['name'] ?? $item['title'] ?? $product['name'] ?? $product['title'] ?? ''));
    $service_id = trim((string) ($item['id'] ?? $item['service_id'] ?? $product['id'] ?? ''));
    $qty = max(1, (int) ($item['quantity'] ?? $item['qty'] ?? 1));
    if ($name === '' && $service_id === '') continue;
    $result[] = ['name' => $name, 'service_id' => $service_id, 'qty' => $qty, 'raw' => $item];
  }
  if ($result === []) {
    $fallback = trim((string) ($normalized_order['service'] ?? ''));
    if ($fallback !== '') {
      $result[] = ['name' => $fallback, 'service_id' => '', 'qty' => 1, 'raw' => []];
    }
  }
  return $result;
}

function slm_member_credits_service_mapping_match(array $order_item, array $mapping): array {
  $service_id = trim((string) ($order_item['service_id'] ?? ''));
  $name = trim((string) ($order_item['name'] ?? ''));
  $by_id = is_array($mapping['by_service_id'] ?? null) ? $mapping['by_service_id'] : [];
  $by_name = is_array($mapping['by_service_name'] ?? null) ? $mapping['by_service_name'] : [];
  if ($service_id !== '' && isset($by_id[$service_id]) && is_array($by_id[$service_id])) return $by_id[$service_id];
  if ($name !== '') {
    foreach ($by_name as $map_name => $rules) {
      if (!is_array($rules)) continue;
      if (strcasecmp(trim((string) $map_name), $name) === 0) return $rules;
    }
  }
  return [];
}

function slm_member_credits_deduct_for_aryeo_completed_order(array $normalized_order, array $payload = [], string $raw_body = ''): array {
  if (!slm_member_credits_auto_deduct_enabled()) return ['ok' => false, 'status' => 'disabled', 'message' => 'Auto credit deduction is disabled.'];
  $email = sanitize_email((string) ($normalized_order['customer_email'] ?? ''));
  $order_ref = trim((string) ($normalized_order['raw_id'] ?? $normalized_order['id'] ?? ''));
  if ($email === '') {
    slm_member_credits_open_flag(0, 'no_active_membership', $order_ref, 'Completed Aryeo order is missing customer email.', []);
    return ['ok' => false, 'status' => 'missing-email', 'message' => 'Missing customer email.'];
  }
  $user = get_user_by('email', $email);
  if (!$user instanceof WP_User) {
    slm_member_credits_open_flag(0, 'no_active_membership', $order_ref, 'Completed Aryeo order could not be mapped to a WordPress user.', ['customer_email' => $email]);
    return ['ok' => false, 'status' => 'user-not-found', 'message' => 'No user found for order email.'];
  }
  $ctx = slm_member_credits_user_subscription_context((int) $user->ID);
  if (empty($ctx['is_active']) || trim((string) ($ctx['plan_slug'] ?? '')) === '') {
    slm_member_credits_open_flag((int) $user->ID, 'no_active_membership', $order_ref, 'Completed Aryeo order has no active membership for automatic credit deduction.', ['customer_email' => $email]);
    return ['ok' => false, 'status' => 'no-active-membership', 'message' => 'No active membership.', 'user_id' => (int) $user->ID];
  }

  $mapping = slm_member_credits_service_mapping();
  $order_items = slm_member_credits_aryeo_order_items_for_mapping($normalized_order);
  $requirements = [];
  $unmatched = [];
  foreach ($order_items as $item) {
    $rules = slm_member_credits_service_mapping_match($item, $mapping);
    if ($rules === []) {
      $unmatched[] = $item;
      continue;
    }
    foreach ($rules as $rule) {
      if (!is_array($rule)) continue;
      $credit_key = sanitize_key((string) ($rule['credit_key'] ?? ''));
      if ($credit_key === '') continue;
      $qty = max(1, (int) ($rule['qty'] ?? 1)) * max(1, (int) ($item['qty'] ?? 1));
      $requirements[$credit_key] = ($requirements[$credit_key] ?? 0) + $qty;
    }
  }

  $event_id = function_exists('slm_aryeo_webhook_event_id') ? (string) slm_aryeo_webhook_event_id($payload) : '';
  $fingerprint = $event_id !== '' ? $event_id : md5($raw_body !== '' ? $raw_body : wp_json_encode($normalized_order['raw'] ?? []));
  $source_ref = $order_ref !== '' ? $order_ref : ('aryeo:' . $fingerprint);

  foreach ($unmatched as $item) {
    $label = trim((string) ($item['name'] ?? ''));
    slm_member_credits_open_flag((int) $user->ID, 'unmatched_service', $source_ref, 'Unmatched Aryeo service for credit deduction: ' . ($label !== '' ? $label : 'unknown'), [
      'service_name' => $label,
      'service_id' => (string) ($item['service_id'] ?? ''),
      'event_id' => $event_id,
    ]);
  }
  if ($requirements === []) {
    return ['ok' => true, 'status' => 'ignored', 'message' => 'No mapped credits found for order.', 'user_id' => (int) $user->ID];
  }

  $before = slm_member_credits_balance_snapshot((int) $user->ID);
  $before_by_key = is_array($before['by_key'] ?? null) ? $before['by_key'] : [];
  $usage_rows = [];
  foreach ($requirements as $credit_key => $qty) {
    $insert = slm_member_credits_insert_ledger_txn([
      'user_id' => (int) $user->ID,
      'subscription_provider' => (string) ($ctx['provider'] ?? ''),
      'subscription_external_id' => (string) ($ctx['subscription_external_id'] ?? ''),
      'plan_slug' => (string) ($ctx['plan_slug'] ?? ''),
      'term_code' => (string) ($ctx['term_code'] ?? ''),
      'credit_key' => $credit_key,
      'txn_type' => 'usage',
      'quantity_delta' => 0 - (int) $qty,
      'source' => 'aryeo_order_completed',
      'source_ref' => $source_ref,
      'dedupe_key' => 'usage:' . md5(implode('|', [(string) $user->ID, $source_ref, $fingerprint, $credit_key, (string) $qty])),
      'meta' => [
        'order_id' => (string) ($normalized_order['raw_id'] ?? ''),
        'order_number' => (string) ($normalized_order['id'] ?? ''),
        'service' => (string) ($normalized_order['service'] ?? ''),
        'event_id' => $event_id,
      ],
    ]);
    if (is_wp_error($insert)) return ['ok' => false, 'status' => 'error', 'message' => $insert->get_error_message(), 'user_id' => (int) $user->ID];
    $usage_rows[] = ['credit_key' => $credit_key, 'qty' => (int) $qty, 'duplicate' => !empty($insert['duplicate'])];
  }

  $after = slm_member_credits_balance_snapshot((int) $user->ID);
  $after_by_key = is_array($after['by_key'] ?? null) ? $after['by_key'] : [];
  $overages = [];
  foreach ($requirements as $credit_key => $qty) {
    $before_qty = (int) ($before_by_key[$credit_key] ?? 0);
    $after_qty = (int) ($after_by_key[$credit_key] ?? 0);
    if ($after_qty < 0 && ($before_qty >= 0 || slm_member_credits_overage_allowed())) {
      $overages[$credit_key] = $after_qty;
      slm_member_credits_open_flag((int) $user->ID, 'overage', $source_ref, 'Membership credit overage after completed/delivered order.', [
        'credit_key' => $credit_key,
        'balance_after' => $after_qty,
        'required_qty' => (int) $qty,
        'event_id' => $event_id,
      ]);
    }
  }

  return ['ok' => true, 'status' => 'processed', 'user_id' => (int) $user->ID, 'order_ref' => $source_ref, 'requirements' => $requirements, 'unmatched' => count($unmatched), 'overages' => $overages, 'usage_rows' => $usage_rows];
}

function slm_member_credits_handle_aryeo_webhook(array $payload, string $raw_body = ''): void {
  if (!slm_member_credits_auto_deduct_enabled()) return;
  if (!function_exists('slm_aryeo_extract_webhook_order_payload') || !function_exists('slm_aryeo_normalize_order') || !function_exists('slm_aryeo_detect_webhook_event_name') || !function_exists('slm_aryeo_webhook_event_is_completion_like')) return;
  $order = slm_aryeo_extract_webhook_order_payload($payload);
  if (!is_array($order) || $order === []) return;
  $normalized = slm_aryeo_normalize_order($order);
  $event_name = (string) slm_aryeo_detect_webhook_event_name($payload);
  if (!slm_aryeo_webhook_event_is_completion_like($event_name, $normalized)) return;
  $result = slm_member_credits_deduct_for_aryeo_completed_order($normalized, $payload, $raw_body);
  if (function_exists('slm_subscriptions_log')) {
    slm_subscriptions_log('member_credits_aryeo_webhook', ['event_name' => $event_name, 'order_id' => (string) ($normalized['raw_id'] ?? ''), 'result' => $result], !empty($result['ok']) ? 'info' : 'error');
  }
}

function slm_member_credits_membership_rows(array $args = []): array {
  $search = strtolower(trim((string) ($args['search'] ?? '')));
  $include_inactive = !empty($args['include_inactive']);
  $meta_keys = function_exists('slm_subscriptions_meta_keys') ? slm_subscriptions_meta_keys() : [];
  $plan_meta_key = (string) ($meta_keys['plan'] ?? 'slm_subscription_plan');
  $users = get_users(['number' => 500, 'meta_key' => $plan_meta_key, 'meta_compare' => 'EXISTS', 'orderby' => 'registered', 'order' => 'DESC']);
  if (!is_array($users)) return [];
  $rows = [];
  foreach ($users as $user) {
    if (!$user instanceof WP_User) continue;
    $ctx = slm_member_credits_user_subscription_context((int) $user->ID);
    if (!$include_inactive && empty($ctx['is_active'])) continue;
    $plan = is_array($ctx['plan'] ?? null) ? $ctx['plan'] : null;
    $plan_label = is_array($plan) ? (string) ($plan['label'] ?? ($ctx['plan_slug'] ?? '')) : (string) ($ctx['plan_slug'] ?? '');
    $name = trim((string) $user->display_name);
    if ($name === '') $name = (string) $user->user_login;
    $email = (string) $user->user_email;
    if ($search !== '') {
      $hay = strtolower(implode(' ', [$name, $email, (string) ($ctx['plan_slug'] ?? ''), $plan_label, (string) ($ctx['term_code'] ?? '')]));
      if (strpos($hay, $search) === false) continue;
    }
    $rows[] = [
      'user_id' => (int) $user->ID,
      'name' => $name,
      'email' => $email,
      'provider' => (string) ($ctx['provider'] ?? ''),
      'status' => (string) ($ctx['status'] ?? ''),
      'status_label' => function_exists('slm_subscriptions_status_label') ? slm_subscriptions_status_label((string) ($ctx['status'] ?? '')) : (string) ($ctx['status'] ?? ''),
      'plan_slug' => (string) ($ctx['plan_slug'] ?? ''),
      'plan_label' => $plan_label,
      'term_code' => (string) ($ctx['term_code'] ?? ''),
      'term_label' => (string) ($ctx['term_label'] ?? ''),
      'commitment_end' => (int) ($ctx['commitment_end'] ?? 0),
      'term_ends_at' => (int) ($ctx['term_ends_at'] ?? 0),
      'subscription_external_id' => (string) ($ctx['subscription_external_id'] ?? ''),
      'open_flag_count' => slm_member_credits_open_flag_count((int) $user->ID),
      'balance_snapshot' => slm_member_credits_balance_snapshot((int) $user->ID),
    ];
  }
  return $rows;
}

function slm_member_credits_member_detail(int $user_id): array {
  $user = get_userdata($user_id);
  if (!$user instanceof WP_User) return [];
  return [
    'user' => $user,
    'subscription' => slm_member_credits_user_subscription_context($user_id),
    'balances' => slm_member_credits_balance_snapshot($user_id),
    'ledger' => slm_member_credits_recent_ledger($user_id, 100),
    'flags' => slm_member_credits_open_flags($user_id, 100),
  ];
}

function slm_member_credits_admin_adjust(int $user_id, string $credit_key, int $quantity_delta, string $note = '', int $admin_user_id = 0) {
  $credit_key = sanitize_key($credit_key);
  if ($user_id <= 0 || $credit_key === '' || $quantity_delta === 0) return new WP_Error('slm_member_credits_adjust_invalid', 'Invalid credit adjustment request.');
  $allowed = array_fill_keys(slm_member_credits_supported_keys(), true);
  if (!isset($allowed[$credit_key])) return new WP_Error('slm_member_credits_adjust_key', 'Unsupported credit key.');
  $ctx = slm_member_credits_user_subscription_context($user_id);
  return slm_member_credits_insert_ledger_txn([
    'user_id' => $user_id,
    'subscription_provider' => (string) ($ctx['provider'] ?? ''),
    'subscription_external_id' => (string) ($ctx['subscription_external_id'] ?? ''),
    'plan_slug' => (string) ($ctx['plan_slug'] ?? ''),
    'term_code' => (string) ($ctx['term_code'] ?? ''),
    'credit_key' => $credit_key,
    'txn_type' => 'admin_adjust',
    'quantity_delta' => $quantity_delta,
    'source' => 'admin',
    'source_ref' => 'admin:' . $admin_user_id,
    'dedupe_key' => 'admin_adjust:' . wp_generate_uuid4(),
    'meta' => ['note' => sanitize_text_field($note)],
    'created_by_user_id' => max(0, $admin_user_id),
  ]);
}
