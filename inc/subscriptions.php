<?php
if (!defined('ABSPATH')) exit;

const SLM_SUBSCRIPTIONS_SCHEMA_VERSION = '1.1.0';
const SLM_SQUARE_API_VERSION = '2025-10-16';

function slm_subscriptions_meta_keys(): array {
  return [
    'plan' => 'slm_subscription_plan',
    'provider' => 'slm_subscription_provider',
    'term_code' => 'slm_subscription_term_code',
    'square_option_id' => 'slm_square_subscription_option_id',
    'term_started_at' => 'slm_subscription_term_started_at',
    'term_ends_at' => 'slm_subscription_term_ends_at',
    'stripe_customer_id' => 'slm_stripe_customer_id',
    'stripe_subscription_id' => 'slm_stripe_subscription_id',
    'square_customer_id' => 'slm_square_customer_id',
    'square_subscription_id' => 'slm_square_subscription_id',
    'status' => 'slm_subscription_status',
    'current_period_end' => 'slm_subscription_current_period_end',
    'commitment_end' => 'slm_subscription_commitment_end',
    'last_credited_invoice_id' => 'slm_last_credited_invoice_id',
    'membership_review_flag' => 'slm_membership_review_flag',
    'membership_review_note' => 'slm_membership_review_note',
    'test_membership' => 'slm_test_membership',
    'aryeo_customer_user_id' => 'slm_aryeo_customer_user_id',
    'aryeo_affiliate_membership_id' => 'slm_aryeo_affiliate_membership_id',
  ];
}

function slm_subscriptions_user_exact_status(int $user_id): string {
  $keys = slm_subscriptions_meta_keys();
  return sanitize_key((string) get_user_meta($user_id, $keys['status'], true));
}

function slm_subscriptions_user_has_exact_active_membership(int $user_id): bool {
  return slm_subscriptions_user_exact_status($user_id) === 'active';
}

function slm_subscriptions_user_membership_state_issues(int $user_id): array {
  $keys = slm_subscriptions_meta_keys();
  $status = sanitize_key((string) get_user_meta($user_id, $keys['status'], true));
  $provider = sanitize_key((string) get_user_meta($user_id, $keys['provider'], true));
  $plan = sanitize_key((string) get_user_meta($user_id, $keys['plan'], true));
  $stripe_sub_id = trim((string) get_user_meta($user_id, $keys['stripe_subscription_id'], true));
  $square_sub_id = trim((string) get_user_meta($user_id, $keys['square_subscription_id'], true));
  $review_flag = sanitize_key((string) get_user_meta($user_id, $keys['membership_review_flag'], true));
  $review_note = trim((string) get_user_meta($user_id, $keys['membership_review_note'], true));

  $reasons = [];
  $messages = [];

  if ($stripe_sub_id !== '' && $square_sub_id !== '') {
    $reasons[] = 'multiple_subscriptions';
    $messages[] = 'Multiple provider subscription IDs are stored on this account.';
  }
  if ($status === 'active' && $plan === '') {
    $reasons[] = 'active_missing_plan';
    $messages[] = 'Membership status is active but no plan slug is stored.';
  }
  if ($status === 'active' && $stripe_sub_id === '' && $square_sub_id === '') {
    $reasons[] = 'active_missing_subscription_id';
    $messages[] = 'Membership status is active but no provider subscription ID is stored.';
  }
  if ($provider === 'square' && $status === 'active' && $square_sub_id === '') {
    $reasons[] = 'square_provider_missing_subscription_id';
    $messages[] = 'Square is marked as provider but Square subscription ID is missing.';
  }
  if ($provider === 'stripe' && $status === 'active' && $stripe_sub_id === '') {
    $reasons[] = 'stripe_provider_missing_subscription_id';
    $messages[] = 'Stripe is marked as provider but Stripe subscription ID is missing.';
  }
  if ($review_flag !== '') {
    $reasons[] = $review_flag;
    $messages[] = $review_note !== '' ? $review_note : 'Membership state has been flagged for admin review.';
  }

  $reasons = array_values(array_unique(array_filter(array_map('sanitize_key', $reasons))));
  $messages = array_values(array_unique(array_filter(array_map('strval', $messages))));

  return [
    'has_issue' => $reasons !== [],
    'has_duplicate_memberships' => in_array('multiple_subscriptions', $reasons, true),
    'reasons' => $reasons,
    'messages' => $messages,
    'status' => $status,
    'provider' => $provider,
    'plan_slug' => $plan,
    'square_subscription_id' => $square_sub_id,
    'stripe_subscription_id' => $stripe_sub_id,
  ];
}

function slm_subscriptions_flag_user_membership_review(int $user_id, string $reason, array $context = []): void {
  if ($user_id <= 0) return;
  $reason = sanitize_key($reason);
  if ($reason === '') return;
  $keys = slm_subscriptions_meta_keys();
  update_user_meta($user_id, $keys['membership_review_flag'], $reason);
  $note = 'Flagged for admin review: ' . $reason;
  if (!empty($context['message']) && is_string($context['message'])) {
    $note = trim((string) $context['message']);
  }
  update_user_meta($user_id, $keys['membership_review_note'], $note);
  slm_subscriptions_log('membership_review_flagged', [
    'user_id' => $user_id,
    'reason' => $reason,
    'context' => $context,
  ], 'error');
}

function slm_subscriptions_clear_user_membership_review(int $user_id): void {
  if ($user_id <= 0) return;
  $keys = slm_subscriptions_meta_keys();
  delete_user_meta($user_id, $keys['membership_review_flag']);
  delete_user_meta($user_id, $keys['membership_review_note']);
}

function slm_subscriptions_set_test_membership_flag(int $user_id, bool $enabled): void {
  $keys = slm_subscriptions_meta_keys();
  if ($enabled) {
    update_user_meta($user_id, $keys['test_membership'], '1');
    return;
  }
  delete_user_meta($user_id, $keys['test_membership']);
}

function slm_subscriptions_default_plan_config_map(): array {
  return [
    'monthly-momentum' => [
      'rollover_policy' => 'social_bonus_only',
      'entitlements' => ['edited_reel' => 4, 'talking_head_video' => 1],
      'square_term_options' => [
        'm2m' => ['label' => 'Month-to-Month', 'checkout_option_id' => '', 'commitment_months' => 3, 'billing_interval' => 'month', 'billing_periods' => 0, 'ui_badge' => ''],
        '6m' => ['label' => '6-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 6, 'billing_interval' => 'month', 'billing_periods' => 6, 'discount_note' => '$100 off first two months (avg $33.33/mo)'],
        '12m' => ['label' => '12-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 12, 'billing_interval' => 'month', 'billing_periods' => 12, 'bonus_listing_shoot_per_cycle' => 1],
      ],
    ],
    'growth-engine' => [
      'rollover_policy' => 'social_bonus_only',
      'entitlements' => ['edited_reel' => 10, 'talking_head_video' => 1, 'horizontal_video' => 1, 'branded_insta_post' => 5],
      'square_term_options' => [
        'm2m' => ['label' => 'Month-to-Month', 'checkout_option_id' => '', 'commitment_months' => 3, 'billing_interval' => 'month', 'billing_periods' => 0],
        '6m' => ['label' => '6-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 6, 'billing_interval' => 'month', 'billing_periods' => 6, 'discount_note' => '$100 off first two months (avg $33.33/mo)'],
        '12m' => ['label' => '12-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 12, 'billing_interval' => 'month', 'billing_periods' => 12, 'bonus_listing_shoot_per_cycle' => 1],
      ],
    ],
    'brand-authority' => [
      'rollover_policy' => 'social_bonus_only',
      'entitlements' => ['edited_reel' => 15, 'talking_head_video' => 2, 'horizontal_video' => 1, 'branded_insta_post' => 8],
      'square_term_options' => [
        'm2m' => ['label' => 'Month-to-Month', 'checkout_option_id' => '', 'commitment_months' => 3, 'billing_interval' => 'month', 'billing_periods' => 0],
        '6m' => ['label' => '6-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 6, 'billing_interval' => 'month', 'billing_periods' => 6, 'discount_note' => '$100 off first two months (avg $33.33/mo)'],
        '12m' => ['label' => '12-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 12, 'billing_interval' => 'month', 'billing_periods' => 12, 'bonus_listing_shoot_per_cycle' => 1],
      ],
    ],
    'elite-presence' => [
      'rollover_policy' => 'social_bonus_only',
      'entitlements' => ['edited_reel' => 25, 'talking_head_video' => 2, 'horizontal_video' => 2, 'branded_insta_post' => 10, 'social_media_post_plan' => 1],
      'square_term_options' => [
        'm2m' => ['label' => 'Month-to-Month', 'checkout_option_id' => '', 'commitment_months' => 3, 'billing_interval' => 'month', 'billing_periods' => 0],
        '6m' => ['label' => '6-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 6, 'billing_interval' => 'month', 'billing_periods' => 6, 'discount_note' => '$100 off first two months (avg $33.33/mo)'],
        '12m' => ['label' => '12-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 12, 'billing_interval' => 'month', 'billing_periods' => 12, 'bonus_listing_shoot_per_cycle' => 1],
      ],
    ],
    'vip-presence' => [
      'rollover_policy' => 'social_bonus_only',
      'entitlements' => ['edited_reel' => 30, 'talking_head_video' => 3, 'horizontal_video' => 3, 'branded_insta_post' => 15, 'social_media_post_plan' => 1, 'strategic_media_analysis' => 1],
      'square_term_options' => [
        'm2m' => ['label' => 'Month-to-Month', 'checkout_option_id' => '', 'commitment_months' => 3, 'billing_interval' => 'month', 'billing_periods' => 0],
        '6m' => ['label' => '6-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 6, 'billing_interval' => 'month', 'billing_periods' => 6, 'discount_note' => '$100 off first two months (avg $33.33/mo)'],
        '12m' => ['label' => '12-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 12, 'billing_interval' => 'month', 'billing_periods' => 12, 'bonus_listing_shoot_per_cycle' => 1],
      ],
    ],
    'agent-starting' => [
      'rollover_policy' => 'all_until_term_end',
      'entitlements' => ['listing_shoot' => 1, 'ai_video' => 1, 'staged_or_dusk' => 1],
      'square_term_options' => ['12m' => ['label' => '12-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 12, 'billing_interval' => 'month', 'billing_periods' => 12, 'bonus_listing_shoot_per_cycle' => 1]],
    ],
    'agent-growing' => [
      'rollover_policy' => 'all_until_term_end',
      'entitlements' => ['listing_shoot' => 3, 'ai_video' => 1, 'agent_intro_video' => 1, 'staged_or_dusk' => 2, 'branded_insta_post' => 3],
      'square_term_options' => ['12m' => ['label' => '12-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 12, 'billing_interval' => 'month', 'billing_periods' => 12, 'bonus_listing_shoot_per_cycle' => 1]],
    ],
    'agent-established' => [
      'rollover_policy' => 'all_until_term_end',
      'entitlements' => ['listing_shoot' => 5, 'ai_video' => 2, 'agent_intro_video' => 2, 'horizontal_video' => 1, 'branded_insta_post' => 5],
      'square_term_options' => ['12m' => ['label' => '12-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 12, 'billing_interval' => 'month', 'billing_periods' => 12, 'bonus_listing_shoot_per_cycle' => 1]],
    ],
    'agent-elite' => [
      'rollover_policy' => 'all_until_term_end',
      'entitlements' => ['listing_shoot' => 9, 'ai_video' => 5, 'agent_intro_video' => 4, 'horizontal_video' => 2, 'branded_insta_post' => 10, 'staged_or_dusk' => 4],
      'square_term_options' => ['12m' => ['label' => '12-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 12, 'billing_interval' => 'month', 'billing_periods' => 12, 'bonus_listing_shoot_per_cycle' => 1]],
    ],
    'agent-top-tier' => [
      'rollover_policy' => 'all_until_term_end',
      'entitlements' => ['listing_shoot' => 15, 'ai_video' => 7, 'agent_intro_video' => 6, 'horizontal_video' => 4, 'branded_insta_post' => 15, 'staged_or_dusk' => 8],
      'square_term_options' => ['12m' => ['label' => '12-Month Agreement', 'checkout_option_id' => '', 'commitment_months' => 12, 'billing_interval' => 'month', 'billing_periods' => 12, 'bonus_listing_shoot_per_cycle' => 1]],
    ],
  ];
}

function slm_subscriptions_default_plans(): array {
  $plans = [
    'monthly-momentum' => ['label' => 'Monthly Momentum', 'stripe_price_id' => '', 'square_subscription_plan_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 3, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'growth-engine' => ['label' => 'Growth Engine', 'stripe_price_id' => '', 'square_subscription_plan_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 3, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'brand-authority' => ['label' => 'Brand Authority', 'stripe_price_id' => '', 'square_subscription_plan_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 3, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'elite-presence' => ['label' => 'Elite Presence', 'stripe_price_id' => '', 'square_subscription_plan_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 3, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'vip-presence' => ['label' => 'VIP Presence', 'stripe_price_id' => '', 'square_subscription_plan_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 3, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'agent-starting' => ['label' => 'Starting', 'stripe_price_id' => '', 'square_subscription_plan_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 12, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'agent-growing' => ['label' => 'Growing', 'stripe_price_id' => '', 'square_subscription_plan_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 12, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'agent-established' => ['label' => 'Established', 'stripe_price_id' => '', 'square_subscription_plan_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 12, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'agent-elite' => ['label' => 'Elite', 'stripe_price_id' => '', 'square_subscription_plan_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 12, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
    'agent-top-tier' => ['label' => 'Top-Tier', 'stripe_price_id' => '', 'square_subscription_plan_id' => '', 'aryeo_benefit_profile' => 'member-default', 'minimum_term_months' => 12, 'billing_interval' => 'month', 'credit_amount' => 0, 'active' => 1],
  ];
  $config_map = slm_subscriptions_default_plan_config_map();
  foreach ($plans as $slug => $plan) {
    $config = is_array($config_map[$slug] ?? null) ? $config_map[$slug] : [];
    $plans[$slug]['square_term_options'] = is_array($config['square_term_options'] ?? null) ? $config['square_term_options'] : [];
    $plans[$slug]['entitlements'] = is_array($config['entitlements'] ?? null) ? $config['entitlements'] : [];
    $plans[$slug]['rollover_policy'] = (string) ($config['rollover_policy'] ?? 'none');
  }
  return $plans;
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

function slm_subscriptions_provider_mode(): string {
  $mode = sanitize_key((string) get_option('slm_subscriptions_provider_mode', 'dual'));
  return in_array($mode, ['stripe', 'square', 'dual'], true) ? $mode : 'dual';
}

function slm_subscriptions_checkout_provider(): string {
  $provider = sanitize_key((string) get_option('slm_subscriptions_checkout_provider', 'square'));
  if (!in_array($provider, ['stripe', 'square'], true)) $provider = 'square';
  $mode = slm_subscriptions_provider_mode();
  if ($mode === 'stripe') return 'stripe';
  if ($mode === 'square') return 'square';
  return $provider;
}

function slm_subscriptions_square_environment(): string {
  $env = sanitize_key((string) get_option('slm_square_environment', 'sandbox'));
  return in_array($env, ['sandbox', 'production'], true) ? $env : 'sandbox';
}

function slm_subscriptions_square_access_token(): string {
  return trim((string) get_option('slm_square_access_token', ''));
}

function slm_subscriptions_square_webhook_signature_key(): string {
  return trim((string) get_option('slm_square_webhook_signature_key', ''));
}

function slm_subscriptions_square_location_id(): string {
  return trim((string) get_option('slm_square_location_id', ''));
}

function slm_subscriptions_square_application_id(): string {
  return trim((string) get_option('slm_square_application_id', ''));
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

function slm_subscriptions_sanitize_rollover_policy($value): string {
  $policy = sanitize_key((string) $value);
  return in_array($policy, ['none', 'social_bonus_only', 'all_until_term_end'], true) ? $policy : 'none';
}

function slm_subscriptions_sanitize_entitlements($value): array {
  $allowed = function_exists('slm_member_credits_supported_keys') ? array_fill_keys(slm_member_credits_supported_keys(), true) : [];
  if (!is_array($value)) return [];
  $clean = [];
  foreach ($value as $key => $qty) {
    $credit_key = sanitize_key((string) $key);
    if ($credit_key === '') continue;
    if ($allowed && !isset($allowed[$credit_key])) continue;
    $clean[$credit_key] = max(0, (int) $qty);
  }
  return $clean;
}

function slm_subscriptions_sanitize_square_term_options($value): array {
  if (!is_array($value)) return [];
  $clean = [];
  foreach ($value as $term_code => $option) {
    if (!is_array($option)) continue;
    $term_code = sanitize_key((string) $term_code);
    if ($term_code === '') continue;
    $interval = sanitize_key((string) ($option['billing_interval'] ?? 'month'));
    if (!in_array($interval, ['day', 'week', 'month', 'year'], true)) $interval = 'month';
    $clean[$term_code] = [
      'label' => sanitize_text_field((string) ($option['label'] ?? strtoupper($term_code))),
      'checkout_option_id' => sanitize_text_field((string) ($option['checkout_option_id'] ?? '')),
      'commitment_months' => max(0, (int) ($option['commitment_months'] ?? 0)),
      'billing_interval' => $interval,
      'billing_periods' => max(0, (int) ($option['billing_periods'] ?? 0)),
      'bonus_listing_shoot_per_cycle' => max(0, (int) ($option['bonus_listing_shoot_per_cycle'] ?? 0)),
      'discount_note' => sanitize_text_field((string) ($option['discount_note'] ?? '')),
      'ui_badge' => sanitize_text_field((string) ($option['ui_badge'] ?? '')),
    ];
  }
  return $clean;
}

function slm_subscriptions_normalize_plan_record(array $plan, string $slug = ''): array {
  $defaults = slm_subscriptions_default_plans();
  $base = ($slug !== '' && isset($defaults[$slug]) && is_array($defaults[$slug])) ? $defaults[$slug] : [
    'label' => $slug !== '' ? $slug : 'Plan',
    'stripe_price_id' => '',
    'square_subscription_plan_id' => '',
    'square_term_options' => [],
    'entitlements' => [],
    'rollover_policy' => 'none',
    'aryeo_benefit_profile' => 'member-default',
    'minimum_term_months' => 0,
    'billing_interval' => 'month',
    'credit_amount' => 0,
    'active' => 1,
  ];
  $interval = sanitize_key((string) ($plan['billing_interval'] ?? $base['billing_interval'] ?? 'month'));
  if (!in_array($interval, ['day', 'week', 'month', 'year'], true)) $interval = 'month';
  $normalized = [
    'label' => sanitize_text_field((string) ($plan['label'] ?? $base['label'] ?? ($slug !== '' ? $slug : 'Plan'))),
    'stripe_price_id' => sanitize_text_field((string) ($plan['stripe_price_id'] ?? $base['stripe_price_id'] ?? '')),
    'square_subscription_plan_id' => sanitize_text_field((string) ($plan['square_subscription_plan_id'] ?? $base['square_subscription_plan_id'] ?? '')),
    'square_term_options' => slm_subscriptions_sanitize_square_term_options($plan['square_term_options'] ?? ($base['square_term_options'] ?? [])),
    'entitlements' => slm_subscriptions_sanitize_entitlements($plan['entitlements'] ?? ($base['entitlements'] ?? [])),
    'rollover_policy' => slm_subscriptions_sanitize_rollover_policy($plan['rollover_policy'] ?? ($base['rollover_policy'] ?? 'none')),
    'aryeo_benefit_profile' => sanitize_key((string) ($plan['aryeo_benefit_profile'] ?? $base['aryeo_benefit_profile'] ?? 'member-default')),
    'minimum_term_months' => max(0, (int) ($plan['minimum_term_months'] ?? $base['minimum_term_months'] ?? 0)),
    'billing_interval' => $interval,
    'credit_amount' => max(0, (int) ($plan['credit_amount'] ?? $base['credit_amount'] ?? 0)),
    'active' => empty($plan['active']) ? 0 : 1,
  ];
  if ($normalized['square_term_options'] === [] && !empty($base['square_term_options']) && is_array($base['square_term_options'])) {
    $normalized['square_term_options'] = slm_subscriptions_sanitize_square_term_options($base['square_term_options']);
  }
  if ($normalized['entitlements'] === [] && !empty($base['entitlements']) && is_array($base['entitlements'])) {
    $normalized['entitlements'] = slm_subscriptions_sanitize_entitlements($base['entitlements']);
  }
  return $normalized;
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
    $clean[$slug] = slm_subscriptions_normalize_plan_record($plan, $slug);
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
    if (!is_array($plan)) continue;
    $defaults[$slug] = slm_subscriptions_normalize_plan_record($plan, $slug);
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

function slm_subscriptions_plan_slug_by_square_plan_id(string $plan_id): string {
  foreach (slm_subscription_plans() as $slug => $plan) {
    if (!is_array($plan)) continue;
    if (trim((string) ($plan['square_subscription_plan_id'] ?? '')) === trim($plan_id)) return (string) $slug;
  }
  return '';
}

function slm_subscriptions_plan_slug_by_square_option_id(string $option_id): string {
  $option_id = trim($option_id);
  if ($option_id === '') return '';
  foreach (slm_subscription_plans() as $slug => $plan) {
    if (!is_array($plan)) continue;
    if (trim((string) ($plan['square_subscription_plan_id'] ?? '')) === $option_id) return (string) $slug;
    $term_options = $plan['square_term_options'] ?? [];
    if (!is_array($term_options)) continue;
    foreach ($term_options as $term_code => $term_option) {
      if (!is_array($term_option)) continue;
      if (trim((string) ($term_option['checkout_option_id'] ?? '')) === $option_id) return (string) $slug;
    }
  }
  return '';
}

function slm_subscriptions_plan_term_options(string $plan_slug): array {
  $plan = slm_subscriptions_plan($plan_slug);
  $options = is_array($plan) ? ($plan['square_term_options'] ?? []) : [];
  return is_array($options) ? $options : [];
}

function slm_subscriptions_plan_term_option(string $plan_slug, string $term_code): array {
  $term_code = sanitize_key($term_code);
  $options = slm_subscriptions_plan_term_options($plan_slug);
  return (isset($options[$term_code]) && is_array($options[$term_code])) ? $options[$term_code] : [];
}

function slm_subscriptions_square_term_options_have_checkout_ids(array $plan): bool {
  $term_options = $plan['square_term_options'] ?? [];
  if (!is_array($term_options) || $term_options === []) return false;
  foreach ($term_options as $term_option) {
    if (!is_array($term_option)) continue;
    if (trim((string) ($term_option['checkout_option_id'] ?? '')) !== '') return true;
  }
  return false;
}

function slm_subscriptions_square_expected_term_codes_for_plan(string $plan_slug): array {
  $plan_slug = sanitize_key($plan_slug);
  if ($plan_slug === '') return [];
  if (strpos($plan_slug, 'agent-') === 0) return ['12m'];
  return ['m2m', '6m', '12m'];
}

function slm_subscriptions_square_plan_term_availability(string $plan_slug): array {
  $plan_slug = sanitize_key($plan_slug);
  $expected_terms = slm_subscriptions_square_expected_term_codes_for_plan($plan_slug);
  $result = [
    'plan_slug' => $plan_slug,
    'expected_terms' => $expected_terms,
    'available_terms' => [],
    'missing_terms' => $expected_terms,
    'has_any_checkout_term' => false,
    'is_checkout_ready' => false,
    'reason' => $plan_slug === '' ? 'missing_plan' : '',
  ];
  if ($plan_slug === '') return $result;

  $plan = slm_subscriptions_plan($plan_slug);
  if (!$plan || empty($plan['active'])) {
    $result['reason'] = 'inactive_or_missing';
    return $result;
  }

  $plan_id = trim((string) ($plan['square_subscription_plan_id'] ?? ''));
  if ($plan_id === '') {
    $result['reason'] = 'missing_plan_id';
    return $result;
  }

  $available_terms = [];
  $missing_terms = [];
  foreach ($expected_terms as $term_code) {
    $term_code = sanitize_key((string) $term_code);
    if ($term_code === '') continue;
    $term_option = slm_subscriptions_plan_term_option($plan_slug, $term_code);
    $checkout_option_id = trim((string) ($term_option['checkout_option_id'] ?? ''));
    if ($checkout_option_id !== '') {
      $available_terms[] = $term_code;
      continue;
    }
    $missing_terms[] = $term_code;
  }

  $result['available_terms'] = array_values(array_unique($available_terms));
  $result['missing_terms'] = array_values(array_unique($missing_terms));
  $result['has_any_checkout_term'] = $result['available_terms'] !== [];
  $result['is_checkout_ready'] = $result['has_any_checkout_term'];
  if (!$result['is_checkout_ready']) {
    $result['reason'] = 'missing_term_options';
  }
  return $result;
}

function slm_subscriptions_square_checkout_option_for_term(string $plan_slug, string $term_code): string {
  $term_option = slm_subscriptions_plan_term_option($plan_slug, $term_code);
  return trim((string) ($term_option['checkout_option_id'] ?? ''));
}

function slm_subscriptions_default_term_for_plan(string $plan_slug): string {
  $plan_slug = sanitize_key($plan_slug);
  if (strpos($plan_slug, 'agent-') === 0) return '12m';
  return 'm2m';
}

function slm_subscriptions_term_label(string $term_code): string {
  $term_code = sanitize_key($term_code);
  $labels = [
    'm2m' => 'Month-to-Month',
    '6m' => '6-Month Agreement',
    '12m' => '12-Month Agreement',
  ];
  return $labels[$term_code] ?? strtoupper($term_code);
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
  $labels = [
    'active' => 'Active',
    'trialing' => 'Trialing',
    'past_due' => 'Past Due',
    'unpaid' => 'Unpaid',
    'canceled' => 'Canceled',
    'cancelled' => 'Canceled',
    'pending' => 'Pending',
    'paused' => 'Paused',
    'deactivated' => 'Deactivated',
    'non_member' => 'Not Active',
  ];
  return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
}

function slm_subscriptions_can_accept_checkout(): bool {
  $provider = slm_subscriptions_checkout_provider();
  if ($provider === 'stripe' && slm_subscriptions_stripe_secret_key() === '') return false;
  if ($provider === 'square' && (slm_subscriptions_square_access_token() === '' || slm_subscriptions_square_location_id() === '')) return false;
  foreach (slm_subscription_plans() as $slug => $plan) {
    if (!is_array($plan)) continue;
    if (empty($plan['active'])) continue;
    if ($provider === 'square') {
      $availability = slm_subscriptions_square_plan_term_availability((string) $slug);
      if (!empty($availability['is_checkout_ready'])) return true;
      continue;
    }
    if ($provider === 'stripe' && trim((string) ($plan['stripe_price_id'] ?? '')) !== '') return true;
  }
  return false;
}

function slm_subscriptions_event_table(): string {
  global $wpdb;
  return $wpdb->prefix . 'slm_stripe_events';
}

function slm_subscriptions_change_request_table(): string {
  global $wpdb;
  return $wpdb->prefix . 'slm_membership_change_requests';
}

function slm_subscriptions_install_schema(): void {
  global $wpdb;
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  $table = slm_subscriptions_event_table();
  $requests_table = slm_subscriptions_change_request_table();
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

  $requests_sql = "CREATE TABLE {$requests_table} (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL,
    current_plan_slug varchar(64) NOT NULL DEFAULT '',
    current_term_code varchar(16) NOT NULL DEFAULT '',
    current_status varchar(32) NOT NULL DEFAULT '',
    desired_plan_slug varchar(64) NOT NULL DEFAULT '',
    desired_term_code varchar(16) NOT NULL DEFAULT '',
    request_notes longtext NULL,
    status varchar(32) NOT NULL DEFAULT 'new',
    admin_notes longtext NULL,
    square_subscription_id varchar(191) NOT NULL DEFAULT '',
    details longtext NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY status (status),
    KEY desired_plan_slug (desired_plan_slug),
    KEY created_at (created_at)
  ) {$charset};";
  dbDelta($requests_sql);
  update_option('slm_subscriptions_schema_version', SLM_SUBSCRIPTIONS_SCHEMA_VERSION);
}

add_action('after_switch_theme', 'slm_subscriptions_install_schema');

/**
 * Schedule a daily cron to clean up old Stripe event rows (90+ days).
 */
add_action('after_switch_theme', function () {
  if (!wp_next_scheduled('slm_stripe_events_cleanup')) {
    wp_schedule_event(time(), 'daily', 'slm_stripe_events_cleanup');
  }
});

add_action('slm_stripe_events_cleanup', function () {
  global $wpdb;
  $table = slm_subscriptions_event_table();
  $wpdb->query(
    $wpdb->prepare("DELETE FROM {$table} WHERE processed_at < %s", gmdate('Y-m-d H:i:s', strtotime('-90 days')))
  );
});

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

function slm_subscriptions_change_request_statuses(): array {
  return [
    'new' => 'New',
    'seen' => 'Seen',
    'in_progress' => 'In Progress',
    'done' => 'Done',
    'rejected' => 'Rejected',
  ];
}

function slm_subscriptions_change_request_normalize_status(string $status): string {
  $status = sanitize_key($status);
  $allowed = slm_subscriptions_change_request_statuses();
  return isset($allowed[$status]) ? $status : 'new';
}

function slm_subscriptions_insert_change_request(array $data) {
  global $wpdb;
  $table = slm_subscriptions_change_request_table();
  $user_id = max(0, (int) ($data['user_id'] ?? 0));
  if ($user_id <= 0) return new WP_Error('slm_change_request_missing_user', 'User ID is required.');

  $row = [
    'user_id' => $user_id,
    'current_plan_slug' => sanitize_key((string) ($data['current_plan_slug'] ?? '')),
    'current_term_code' => sanitize_key((string) ($data['current_term_code'] ?? '')),
    'current_status' => sanitize_key((string) ($data['current_status'] ?? '')),
    'desired_plan_slug' => sanitize_key((string) ($data['desired_plan_slug'] ?? '')),
    'desired_term_code' => sanitize_key((string) ($data['desired_term_code'] ?? '')),
    'request_notes' => trim((string) ($data['request_notes'] ?? '')),
    'status' => slm_subscriptions_change_request_normalize_status((string) ($data['status'] ?? 'new')),
    'admin_notes' => trim((string) ($data['admin_notes'] ?? '')),
    'square_subscription_id' => trim((string) ($data['square_subscription_id'] ?? '')),
    'details' => wp_json_encode(is_array($data['details'] ?? null) ? (array) $data['details'] : []),
    'created_at' => current_time('mysql', true),
    'updated_at' => current_time('mysql', true),
  ];
  $ok = $wpdb->insert($table, $row, ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);
  if ($ok === false) return new WP_Error('slm_change_request_insert_failed', 'Unable to save membership change request.');
  return (int) $wpdb->insert_id;
}

function slm_subscriptions_get_change_request(int $request_id): ?array {
  global $wpdb;
  $request_id = max(0, $request_id);
  if ($request_id <= 0) return null;
  $table = slm_subscriptions_change_request_table();
  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d LIMIT 1", $request_id), ARRAY_A);
  if (!is_array($row)) return null;
  $row['id'] = (int) ($row['id'] ?? 0);
  $row['user_id'] = (int) ($row['user_id'] ?? 0);
  $row['details_decoded'] = slm_subscriptions_decode_json((string) ($row['details'] ?? ''));
  return $row;
}

function slm_subscriptions_update_change_request(int $request_id, array $fields): bool {
  global $wpdb;
  $request_id = max(0, $request_id);
  if ($request_id <= 0) return false;
  $table = slm_subscriptions_change_request_table();
  $updates = [];
  $formats = [];

  $map = [
    'status' => ['status', '%s', function ($v) { return slm_subscriptions_change_request_normalize_status((string) $v); }],
    'admin_notes' => ['admin_notes', '%s', function ($v) { return trim((string) $v); }],
    'square_subscription_id' => ['square_subscription_id', '%s', function ($v) { return trim((string) $v); }],
  ];
  foreach ($map as $field => $def) {
    if (!array_key_exists($field, $fields)) continue;
    [$col, $fmt, $sanitize] = $def;
    $updates[$col] = $sanitize($fields[$field]);
    $formats[] = $fmt;
  }
  if (array_key_exists('details', $fields)) {
    $updates['details'] = wp_json_encode(is_array($fields['details']) ? $fields['details'] : []);
    $formats[] = '%s';
  }
  if ($updates === []) return false;
  $updates['updated_at'] = current_time('mysql', true);
  $formats[] = '%s';
  return $wpdb->update($table, $updates, ['id' => $request_id], $formats, ['%d']) !== false;
}

function slm_subscriptions_list_change_requests(array $args = []): array {
  global $wpdb;
  $table = slm_subscriptions_change_request_table();
  $limit = max(1, min(200, (int) ($args['limit'] ?? 50)));
  $offset = max(0, (int) ($args['offset'] ?? 0));
  $status = sanitize_key((string) ($args['status'] ?? ''));
  $search = trim((string) ($args['search'] ?? ''));

  $where = ['1=1'];
  $params = [];
  if ($status !== '' && $status !== 'all') {
    $where[] = 'status = %s';
    $params[] = slm_subscriptions_change_request_normalize_status($status);
  }
  if ($search !== '') {
    $like = '%' . $wpdb->esc_like($search) . '%';
    $where[] = '(CAST(id AS CHAR) LIKE %s OR current_plan_slug LIKE %s OR desired_plan_slug LIKE %s OR request_notes LIKE %s OR admin_notes LIKE %s)';
    array_push($params, $like, $like, $like, $like, $like);
  }
  $where_sql = implode(' AND ', $where);
  $sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d";
  $rows = $wpdb->get_results($wpdb->prepare($sql, array_merge($params, [$limit, $offset])), ARRAY_A);
  if (!is_array($rows)) $rows = [];
  foreach ($rows as &$row) {
    $row['id'] = (int) ($row['id'] ?? 0);
    $row['user_id'] = (int) ($row['user_id'] ?? 0);
  }
  unset($row);
  return $rows;
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

function slm_subscriptions_square_base_url(): string {
  return slm_subscriptions_square_environment() === 'production'
    ? 'https://connect.squareup.com'
    : 'https://connect.squareupsandbox.com';
}

function slm_subscriptions_square_request(string $method, string $path, array $query = [], ?array $body = null) {
  $token = slm_subscriptions_square_access_token();
  if ($token === '') return new WP_Error('slm_square_no_token', 'Square access token is not configured.');

  $method = strtoupper($method);
  $url = slm_subscriptions_square_base_url() . '/' . ltrim($path, '/');
  if (!empty($query)) $url = add_query_arg($query, $url);

  $args = [
    'method' => $method,
    'timeout' => 25,
    'headers' => [
      'Authorization' => 'Bearer ' . $token,
      'Square-Version' => SLM_SQUARE_API_VERSION,
      'Accept' => 'application/json',
    ],
  ];
  if ($body !== null) {
    $args['headers']['Content-Type'] = 'application/json';
    $args['body'] = wp_json_encode($body);
  }

  $res = wp_remote_request($url, $args);
  if (is_wp_error($res)) return $res;
  $code = (int) wp_remote_retrieve_response_code($res);
  $raw = (string) wp_remote_retrieve_body($res);
  $data = $raw !== '' ? json_decode($raw, true) : [];
  if (!is_array($data)) $data = [];

  if ($code < 200 || $code >= 300) {
    $msg = 'Square API request failed.';
    if (!empty($data['errors']) && is_array($data['errors'])) {
      $first = $data['errors'][0] ?? [];
      if (is_array($first)) {
        $msg = (string) ($first['detail'] ?? $first['category'] ?? $msg);
      }
    }
    return new WP_Error('slm_square_http_' . $code, $msg, ['status' => $code, 'body' => $data]);
  }
  return $data;
}

function slm_subscriptions_square_extract_resource(array $res, string $key): array {
  if (isset($res[$key]) && is_array($res[$key])) return $res[$key];
  if (isset($res['data'][$key]) && is_array($res['data'][$key])) return $res['data'][$key];
  if (isset($res['data']) && is_array($res['data']) && array_values($res['data']) !== $res['data']) return $res['data'];
  return [];
}

function slm_subscriptions_square_retrieve_subscription(string $id) {
  $id = trim($id);
  if ($id === '') return new WP_Error('slm_square_missing_subscription_id', 'Square subscription ID missing.');
  $res = slm_subscriptions_square_request('GET', '/v2/subscriptions/' . rawurlencode($id));
  if (is_wp_error($res)) return $res;
  return slm_subscriptions_square_extract_resource($res, 'subscription');
}

function slm_subscriptions_square_cancel_subscription(string $id) {
  $id = trim($id);
  if ($id === '') return new WP_Error('slm_square_missing_subscription_id', 'Square subscription ID missing.');
  $res = slm_subscriptions_square_request('POST', '/v2/subscriptions/' . rawurlencode($id) . '/cancel');
  if (is_wp_error($res)) return $res;
  return slm_subscriptions_square_extract_resource($res, 'subscription');
}

function slm_subscriptions_square_retrieve_customer(string $id) {
  $id = trim($id);
  if ($id === '') return new WP_Error('slm_square_missing_customer_id', 'Square customer ID missing.');
  $res = slm_subscriptions_square_request('GET', '/v2/customers/' . rawurlencode($id));
  if (is_wp_error($res)) return $res;
  return slm_subscriptions_square_extract_resource($res, 'customer');
}

function slm_subscriptions_square_retrieve_invoice(string $id) {
  $id = trim($id);
  if ($id === '') return new WP_Error('slm_square_missing_invoice_id', 'Square invoice ID missing.');
  $res = slm_subscriptions_square_request('GET', '/v2/invoices/' . rawurlencode($id));
  if (is_wp_error($res)) return $res;
  return slm_subscriptions_square_extract_resource($res, 'invoice');
}

function slm_subscriptions_square_retrieve_catalog_object(string $id) {
  $id = trim($id);
  if ($id === '') return new WP_Error('slm_square_missing_catalog_object_id', 'Square catalog object ID missing.');
  $res = slm_subscriptions_square_request('GET', '/v2/catalog/object/' . rawurlencode($id));
  if (is_wp_error($res)) return $res;
  $obj = slm_subscriptions_square_extract_resource($res, 'object');
  return $obj !== [] ? $obj : (is_array($res['object'] ?? null) ? $res['object'] : []);
}

function slm_subscriptions_square_normalize_money($money): array {
  if (!is_array($money)) return [];
  $amount = isset($money['amount']) && is_numeric($money['amount']) ? (int) $money['amount'] : 0;
  $currency = strtoupper(trim((string) ($money['currency'] ?? '')));
  if ($amount <= 0 || $currency === '') return [];
  return ['amount' => $amount, 'currency' => $currency];
}

function slm_subscriptions_square_money_from_catalog_object_price(array $catalog_object): array {
  $candidates = [
    $catalog_object['item_variation_data']['price_money'] ?? null,
    $catalog_object['variation_data']['price_money'] ?? null,
    $catalog_object['item_data']['price_money'] ?? null,
  ];
  foreach ($candidates as $money) {
    $normalized = slm_subscriptions_square_normalize_money($money);
    if ($normalized !== []) return $normalized;
  }

  $nested_variations = $catalog_object['item_data']['variations'] ?? [];
  if (is_array($nested_variations)) {
    foreach ($nested_variations as $variation) {
      if (!is_array($variation)) continue;
      $normalized = slm_subscriptions_square_normalize_money($variation['item_variation_data']['price_money'] ?? null);
      if ($normalized !== []) return $normalized;
    }
  }
  return [];
}

function slm_subscriptions_square_retrieve_catalog_object_with_related(string $id) {
  $id = trim($id);
  if ($id === '') return new WP_Error('slm_square_missing_catalog_object_id', 'Square catalog object ID missing.');
  return slm_subscriptions_square_request('GET', '/v2/catalog/object/' . rawurlencode($id), ['include_related_objects' => 'true']);
}

function slm_subscriptions_square_money_from_relative_subscription_variation(array $catalog_object): array {
  $variation_data = $catalog_object['subscription_plan_variation_data'] ?? [];
  if (!is_array($variation_data)) return [];

  $plan_id = trim((string) ($variation_data['subscription_plan_id'] ?? ''));
  if ($plan_id === '') return [];

  $plan_bundle = slm_subscriptions_square_retrieve_catalog_object_with_related($plan_id);
  if (is_wp_error($plan_bundle) || !is_array($plan_bundle)) return [];

  $plan_object = slm_subscriptions_square_extract_resource($plan_bundle, 'object');
  if ($plan_object === [] && is_array($plan_bundle['object'] ?? null)) {
    $plan_object = (array) $plan_bundle['object'];
  }
  $plan_data = is_array($plan_object['subscription_plan_data'] ?? null) ? (array) $plan_object['subscription_plan_data'] : [];
  $eligible_item_ids = is_array($plan_data['eligible_item_ids'] ?? null) ? (array) $plan_data['eligible_item_ids'] : [];

  // Square may return related catalog objects alongside the plan when include_related_objects=true.
  $related_objects = is_array($plan_bundle['related_objects'] ?? null) ? (array) $plan_bundle['related_objects'] : [];
  $related_by_id = [];
  foreach ($related_objects as $related) {
    if (!is_array($related)) continue;
    $related_id = trim((string) ($related['id'] ?? ''));
    if ($related_id === '') continue;
    $related_by_id[$related_id] = $related;
  }

  foreach ($eligible_item_ids as $eligible_id) {
    $eligible_id = trim((string) $eligible_id);
    if ($eligible_id === '') continue;

    if (isset($related_by_id[$eligible_id]) && is_array($related_by_id[$eligible_id])) {
      $money = slm_subscriptions_square_money_from_catalog_object_price((array) $related_by_id[$eligible_id]);
      if ($money !== []) return $money;
    }

    $eligible_object = slm_subscriptions_square_retrieve_catalog_object($eligible_id);
    if (!is_wp_error($eligible_object) && is_array($eligible_object)) {
      $money = slm_subscriptions_square_money_from_catalog_object_price($eligible_object);
      if ($money !== []) return $money;
    }

    $eligible_bundle = slm_subscriptions_square_retrieve_catalog_object_with_related($eligible_id);
    if (is_wp_error($eligible_bundle) || !is_array($eligible_bundle)) continue;

    $eligible_main = slm_subscriptions_square_extract_resource($eligible_bundle, 'object');
    if ($eligible_main === [] && is_array($eligible_bundle['object'] ?? null)) {
      $eligible_main = (array) $eligible_bundle['object'];
    }
    if ($eligible_main !== []) {
      $money = slm_subscriptions_square_money_from_catalog_object_price($eligible_main);
      if ($money !== []) return $money;
    }

    $eligible_related_objects = is_array($eligible_bundle['related_objects'] ?? null) ? (array) $eligible_bundle['related_objects'] : [];
    foreach ($eligible_related_objects as $related) {
      if (!is_array($related)) continue;
      $money = slm_subscriptions_square_money_from_catalog_object_price((array) $related);
      if ($money !== []) return $money;
    }
  }

  return [];
}

function slm_subscriptions_square_money_from_variation(array $catalog_object): array {
  $data = $catalog_object['subscription_plan_variation_data'] ?? [];
  if (!is_array($data)) $data = [];
  $phases = $data['phases'] ?? [];
  if (!is_array($phases)) $phases = [];
  $has_relative_pricing = false;
  foreach ($phases as $phase) {
    if (!is_array($phase)) continue;
    $pricing_type = strtoupper(trim((string) ($phase['pricing']['type'] ?? '')));
    if ($pricing_type === 'RELATIVE') $has_relative_pricing = true;
    $candidates = [
      $phase['pricing']['price_money'] ?? null,
      $phase['pricing']['price'] ?? null,
      $phase['recurring_price_money'] ?? null,
    ];
    foreach ($candidates as $money) {
      $normalized = slm_subscriptions_square_normalize_money($money);
      if ($normalized !== []) return $normalized;
    }
  }
  if ($has_relative_pricing) {
    $relative_money = slm_subscriptions_square_money_from_relative_subscription_variation($catalog_object);
    if ($relative_money !== []) return $relative_money;
  }
  return [];
}

function slm_subscriptions_square_status_to_internal(string $status): string {
  $status = strtoupper(trim($status));
  if ($status === '') return '';
  $map = [
    'ACTIVE' => 'active',
    'PENDING' => 'pending',
    'PAUSED' => 'paused',
    'CANCELED' => 'canceled',
    'DEACTIVATED' => 'deactivated',
  ];
  return $map[$status] ?? strtolower($status);
}

function slm_subscriptions_square_period_end(array $subscription): int {
  $candidates = [
    $subscription['charged_through_date'] ?? '',
    $subscription['canceled_date'] ?? '',
    $subscription['monthly_billing_anchor_date'] ?? '',
    $subscription['start_date'] ?? '',
  ];
  foreach ($candidates as $raw) {
    if (is_numeric($raw)) return max(0, (int) $raw);
    if (!is_string($raw) || trim($raw) === '') continue;
    $ts = strtotime($raw);
    if ($ts) return (int) $ts;
  }
  return 0;
}

function slm_subscriptions_square_webhook_urls(): array {
  $urls = [rest_url('slm/v1/square/webhook')];
  $urls[] = untrailingslashit((string) $urls[0]);
  $urls[] = trailingslashit((string) $urls[0]);
  $scheme = is_ssl() ? 'https' : 'http';
  $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
  $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
  if ($host !== '' && $uri !== '') {
    $urls[] = $scheme . '://' . $host . $uri;
    $urls[] = untrailingslashit($scheme . '://' . $host . $uri);
    $urls[] = trailingslashit($scheme . '://' . $host . $uri);
    if (stripos($host, 'localhost') === false) {
      $urls[] = 'https://' . $host . $uri;
      $urls[] = untrailingslashit('https://' . $host . $uri);
      $urls[] = trailingslashit('https://' . $host . $uri);
    }
  }
  return array_values(array_unique(array_filter(array_map('trim', $urls))));
}

function slm_subscriptions_square_verify_webhook_signature(string $raw_body, string $header_signature, string $signature_key): bool {
  if ($raw_body === '' || $header_signature === '' || $signature_key === '') return false;
  foreach (slm_subscriptions_square_webhook_urls() as $url) {
    $expected = base64_encode(hash_hmac('sha256', $url . $raw_body, $signature_key, true));
    if (hash_equals($expected, trim($header_signature))) return true;
  }
  return false;
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

function slm_subscriptions_start_url(string $plan_slug, string $term_code = ''): string {
  $args = ['plan' => sanitize_key($plan_slug)];
  $term_code = sanitize_key($term_code);
  if ($term_code !== '') $args['term'] = $term_code;
  return slm_subscriptions_action_url('start-membership', $args);
}

function slm_subscriptions_manage_billing_url(): string {
  return slm_subscriptions_action_url('manage-billing');
}

function slm_subscriptions_cancel_membership_url(): string {
  return slm_subscriptions_action_url('cancel-membership');
}

function slm_subscriptions_user_provider(int $user_id): string {
  $keys = slm_subscriptions_meta_keys();
  $provider = sanitize_key((string) get_user_meta($user_id, $keys['provider'], true));
  if (in_array($provider, ['stripe', 'square'], true)) return $provider;
  $square_sub_id = trim((string) get_user_meta($user_id, $keys['square_subscription_id'], true));
  if ($square_sub_id !== '') return 'square';
  $stripe_sub_id = trim((string) get_user_meta($user_id, $keys['stripe_subscription_id'], true));
  if ($stripe_sub_id !== '') return 'stripe';
  return slm_subscriptions_checkout_provider();
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

function slm_subscriptions_square_checkout_context(string $plan_slug, string $term_code = '') {
  $plan_slug = sanitize_key($plan_slug);
  $plan = slm_subscriptions_plan($plan_slug);
  if (!$plan || empty($plan['active'])) return new WP_Error('slm_subscription_plan', 'Membership plan is not available.');
  $plan_id = trim((string) ($plan['square_subscription_plan_id'] ?? ''));
  if ($plan_id === '') return new WP_Error('slm_square_missing_plan', 'Square subscription plan ID is missing for this plan.');
  $availability = slm_subscriptions_square_plan_term_availability($plan_slug);

  $term_code = sanitize_key($term_code);
  if ($term_code === '') $term_code = slm_subscriptions_default_term_for_plan($plan_slug);
  $expected_terms = is_array($availability['expected_terms'] ?? null) ? (array) $availability['expected_terms'] : slm_subscriptions_square_expected_term_codes_for_plan($plan_slug);
  if ($expected_terms !== [] && !in_array($term_code, $expected_terms, true)) {
    return new WP_Error('slm_square_invalid_term', 'The selected membership term is not available for this plan.');
  }

  $term_option = slm_subscriptions_plan_term_option($plan_slug, $term_code);
  if ($term_option === []) {
    return new WP_Error('slm_square_missing_term_option', 'Square term option configuration is missing for the selected membership term.');
  }
  $checkout_option_id = trim((string) ($term_option['checkout_option_id'] ?? ''));
  if ($checkout_option_id === '') {
    return new WP_Error('slm_square_missing_term_option', 'Square checkout option ID is missing for the selected membership term.');
  }

  return [
    'plan_slug' => $plan_slug,
    'plan' => $plan,
    'term_code' => $term_code,
    'term_option' => $term_option,
    'square_plan_id' => $plan_id,
    'checkout_option_id' => $checkout_option_id,
  ];
}

function slm_subscriptions_square_store_pending_checkout_selection(int $user_id, array $data): void {
  set_transient('slm_square_pending_checkout_' . $user_id, [
    'plan_slug' => sanitize_key((string) ($data['plan_slug'] ?? '')),
    'term_code' => sanitize_key((string) ($data['term_code'] ?? '')),
    'square_option_id' => trim((string) ($data['square_option_id'] ?? '')),
    'stored_at' => time(),
  ], HOUR_IN_SECONDS * 6);
}

function slm_subscriptions_square_pending_checkout_selection(int $user_id): array {
  $raw = get_transient('slm_square_pending_checkout_' . $user_id);
  return is_array($raw) ? $raw : [];
}

function slm_subscriptions_stripe_checkout_for_plan(WP_User $user, string $plan_slug, string $term_code = '') {
  unset($term_code);
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

function slm_subscriptions_square_checkout_for_plan(WP_User $user, string $plan_slug, string $term_code = '') {
  $plan_slug = sanitize_key($plan_slug);
  $checkout_context = slm_subscriptions_square_checkout_context($plan_slug, $term_code);
  if (is_wp_error($checkout_context)) return $checkout_context;
  $plan = (array) ($checkout_context['plan'] ?? []);
  $selected_term_code = sanitize_key((string) ($checkout_context['term_code'] ?? ''));
  $square_plan_id = trim((string) ($checkout_context['square_plan_id'] ?? ''));
  $checkout_option_id = trim((string) ($checkout_context['checkout_option_id'] ?? ''));

  if (slm_subscriptions_square_location_id() === '') return new WP_Error('slm_square_missing_location', 'Square location ID is not configured.');
  if (slm_subscriptions_square_access_token() === '') return new WP_Error('slm_square_missing_token', 'Square access token is not configured.');

  $catalog_object = slm_subscriptions_square_retrieve_catalog_object($checkout_option_id);
  if (is_wp_error($catalog_object)) return $catalog_object;
  $money = slm_subscriptions_square_money_from_variation(is_array($catalog_object) ? $catalog_object : []);
  if (!is_array($money) || (int) ($money['amount'] ?? 0) <= 0 || trim((string) ($money['currency'] ?? '')) === '') {
    return new WP_Error('slm_square_missing_price_money', 'Unable to read recurring price from the Square plan variation.');
  }

  $name = trim((string) ($plan['label'] ?? $plan_slug));
  if ($name === '') $name = 'Membership';
  if ($selected_term_code !== '') {
    $name .= ' (' . slm_subscriptions_term_label($selected_term_code) . ')';
  }
  $body = [
    'idempotency_key' => wp_generate_uuid4(),
    'quick_pay' => [
      'name' => $name . ' Membership',
      'price_money' => [
        'amount' => (int) $money['amount'],
        'currency' => (string) $money['currency'],
      ],
      'location_id' => slm_subscriptions_square_location_id(),
    ],
    'checkout_options' => [
      'subscription_plan_id' => $checkout_option_id,
      'redirect_url' => slm_subscriptions_checkout_success_url(),
    ],
  ];
  $res = slm_subscriptions_square_request('POST', '/v2/online-checkout/payment-links', [], $body);
  if (is_wp_error($res)) return $res;
  $payment_link = is_array($res['payment_link'] ?? null) ? $res['payment_link'] : [];
  $url = trim((string) ($payment_link['url'] ?? ''));
  if ($url !== '') {
    slm_subscriptions_square_store_pending_checkout_selection((int) $user->ID, [
      'plan_slug' => $plan_slug,
      'term_code' => $selected_term_code,
      'square_option_id' => $checkout_option_id,
    ]);
  }
  return $url !== '' ? $url : new WP_Error('slm_square_missing_checkout_url', 'Square payment link URL missing.');
}

function slm_subscriptions_checkout_for_plan(WP_User $user, string $plan_slug, string $term_code = '') {
  $provider = slm_subscriptions_checkout_provider();
  if ($provider === 'square') return slm_subscriptions_square_checkout_for_plan($user, $plan_slug, $term_code);
  return slm_subscriptions_stripe_checkout_for_plan($user, $plan_slug, $term_code);
}

function slm_subscriptions_stripe_billing_portal_for_user(WP_User $user) {
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

function slm_subscriptions_square_cancel_membership_for_user(WP_User $user) {
  $keys = slm_subscriptions_meta_keys();
  $sub_id = trim((string) get_user_meta($user->ID, $keys['square_subscription_id'], true));
  if ($sub_id === '') return new WP_Error('slm_square_missing_subscription', 'No Square subscription is linked to this account.');

  $commitment_end = (int) get_user_meta($user->ID, $keys['commitment_end'], true);
  if ($commitment_end > time()) {
    return new WP_Error('slm_subscription_commitment_active', 'Membership cannot be canceled until the commitment ends.');
  }

  $res = slm_subscriptions_square_cancel_subscription($sub_id);
  if (is_wp_error($res)) return $res;
  if (is_array($res) && !empty($res)) {
    slm_subscriptions_update_state((int) $user->ID, [
      'provider' => 'square',
      'square_customer_id' => (string) ($res['customer_id'] ?? ''),
      'square_subscription_id' => (string) ($res['id'] ?? $sub_id),
      'status' => slm_subscriptions_square_status_to_internal((string) ($res['status'] ?? '')),
      'current_period_end' => slm_subscriptions_square_period_end($res),
    ]);
  }
  return add_query_arg(['view' => 'account', 'billing' => 'billing_cancel_scheduled'], slm_portal_url());
}

function slm_subscriptions_billing_portal_for_user(WP_User $user) {
  return slm_subscriptions_billing_action_for_user($user);
}

function slm_subscriptions_billing_action_for_user(WP_User $user) {
  $provider = slm_subscriptions_user_provider((int) $user->ID);
  if ($provider === 'square') return slm_subscriptions_square_cancel_membership_for_user($user);
  return slm_subscriptions_stripe_billing_portal_for_user($user);
}

function slm_subscriptions_portal_start_membership_guard(WP_User $user, string $requested_plan = '', string $requested_term = '') {
  $user_id = (int) $user->ID;
  if ($user_id <= 0) return new WP_Error('invalid_user', 'User account is invalid.');

  $issues = slm_subscriptions_user_membership_state_issues($user_id);
  if (!empty($issues['has_issue'])) {
    slm_subscriptions_flag_user_membership_review($user_id, 'multiple_subscriptions', [
      'message' => 'Multiple memberships detected or membership state is inconsistent. Review before allowing checkout.',
      'issues' => $issues,
      'requested_plan' => sanitize_key($requested_plan),
      'requested_term' => sanitize_key($requested_term),
    ]);
    return new WP_Error('slm_multiple_memberships_detected', 'Multiple memberships detected - please contact support.');
  }

  if (slm_subscriptions_user_exact_status($user_id) === 'active') {
    slm_subscriptions_log('membership_checkout_blocked_active', [
      'user_id' => $user_id,
      'requested_plan' => sanitize_key($requested_plan),
      'requested_term' => sanitize_key($requested_term),
    ], 'info');
    return new WP_Error('slm_already_active_use_change_request', 'You already have an active membership. To change plans, request a change.');
  }

  return true;
}

function slm_subscriptions_portal_start_membership_error_code(WP_Error $error): string {
  $code = sanitize_key((string) $error->get_error_code());
  $config_codes = [
    'slm_subscription_plan',
    'slm_subscription_missing_price',
    'slm_square_missing_plan',
    'slm_square_invalid_term',
    'slm_square_missing_term_option',
    'slm_square_missing_location',
    'slm_square_missing_token',
    'slm_square_missing_price_money',
    'slm_square_missing_catalog_object_id',
    'slm_square_no_token',
    'slm_stripe_no_secret',
  ];
  if ($code === 'slm_already_active_use_change_request') return 'already_active_use_change_request';
  if ($code === 'slm_multiple_memberships_detected') return 'multiple_memberships_detected';
  return in_array($code, $config_codes, true) ? 'subscription_unavailable' : 'checkout_failed';
}

function slm_subscriptions_portal_plan_change_return_url(string $fallback_view = 'account'): string {
  $view = sanitize_key((string) ($_POST['slm_change_request_return_view'] ?? ''));
  if (!in_array($view, ['account', 'membership-shop'], true)) $view = sanitize_key($fallback_view);
  if (!in_array($view, ['account', 'membership-shop'], true)) $view = 'account';
  return add_query_arg('view', $view, slm_portal_url());
}

function slm_subscriptions_membership_change_request_plan_choices(): array {
  $choices = [];
  foreach (slm_subscription_plans() as $slug => $plan) {
    if (!is_array($plan)) continue;
    if (empty($plan['active'])) continue;
    $slug = sanitize_key((string) $slug);
    if ($slug === '') continue;
    $choices[$slug] = sanitize_text_field((string) ($plan['label'] ?? $slug));
  }
  return $choices;
}

function slm_subscriptions_validate_change_request_target(string $desired_plan, string $desired_term) {
  $desired_plan = sanitize_key($desired_plan);
  $desired_term = sanitize_key($desired_term);
  if ($desired_plan === '') return new WP_Error('slm_change_request_missing_plan', 'Choose a desired plan.');
  $plan = slm_subscriptions_plan($desired_plan);
  if (!$plan || empty($plan['active'])) return new WP_Error('slm_change_request_invalid_plan', 'The selected plan is not available.');
  $expected_terms = slm_subscriptions_square_expected_term_codes_for_plan($desired_plan);
  if ($desired_term === '') $desired_term = slm_subscriptions_default_term_for_plan($desired_plan);
  if ($expected_terms !== [] && !in_array($desired_term, $expected_terms, true)) {
    return new WP_Error('slm_change_request_invalid_term', 'The selected agreement term is not valid for that plan.');
  }
  return [
    'plan_slug' => $desired_plan,
    'term_code' => $desired_term,
    'plan' => $plan,
  ];
}

function slm_subscriptions_handle_portal_change_request_submit(): void {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
  $form_action = sanitize_key((string) ($_POST['slm_portal_membership_form_action'] ?? ''));
  if ($form_action !== 'request_plan_change') return;

  $return_url = slm_subscriptions_portal_plan_change_return_url('account');
  if (!is_user_logged_in()) {
    wp_safe_redirect(add_query_arg([
      'mode' => 'login',
      'redirect_to' => $return_url,
    ], slm_login_url()));
    exit;
  }

  $nonce = (string) ($_POST['slm_portal_request_plan_change_nonce'] ?? '');
  if (!wp_verify_nonce($nonce, 'slm_portal_request_plan_change')) {
    wp_safe_redirect(add_query_arg(['view' => 'account', 'error' => 'invalid_request'], slm_portal_url()));
    exit;
  }

  $user = wp_get_current_user();
  if (!$user instanceof WP_User || !$user->ID) {
    wp_safe_redirect(add_query_arg(['view' => 'account', 'error' => 'invalid_request'], slm_portal_url()));
    exit;
  }

  $validated = slm_subscriptions_validate_change_request_target(
    (string) ($_POST['slm_change_request_plan'] ?? ''),
    (string) ($_POST['slm_change_request_term'] ?? '')
  );
  if (is_wp_error($validated)) {
    slm_subscriptions_log('membership_change_request_error', [
      'user_id' => (int) $user->ID,
      'error' => $validated->get_error_message(),
      'code' => $validated->get_error_code(),
    ], 'error');
    $error_url = add_query_arg([
      'change_request' => 'error',
      'change_request_error' => sanitize_key((string) $validated->get_error_code()),
    ], $return_url);
    wp_safe_redirect($error_url);
    exit;
  }

  $desired_plan = sanitize_key((string) ($validated['plan_slug'] ?? ''));
  $desired_term = sanitize_key((string) ($validated['term_code'] ?? ''));
  $summary = slm_get_user_subscription_summary((int) $user->ID);
  $notes = trim((string) wp_unslash($_POST['slm_change_request_notes'] ?? ''));
  if (strlen($notes) > 2000) $notes = substr($notes, 0, 2000);
  $keys = slm_subscriptions_meta_keys();

  $inserted = slm_subscriptions_insert_change_request([
    'user_id' => (int) $user->ID,
    'current_plan_slug' => sanitize_key((string) ($summary['plan_slug'] ?? '')),
    'current_term_code' => sanitize_key((string) ($summary['term_code'] ?? '')),
    'current_status' => sanitize_key((string) ($summary['status'] ?? '')),
    'desired_plan_slug' => $desired_plan,
    'desired_term_code' => $desired_term,
    'request_notes' => $notes,
    'status' => 'new',
    'square_subscription_id' => trim((string) get_user_meta((int) $user->ID, $keys['square_subscription_id'], true)),
    'details' => [
      'provider' => slm_subscriptions_user_provider((int) $user->ID),
      'user_email' => (string) $user->user_email,
      'square_customer_id' => trim((string) get_user_meta((int) $user->ID, $keys['square_customer_id'], true)),
      'is_test_membership' => trim((string) get_user_meta((int) $user->ID, $keys['test_membership'], true)) === '1',
    ],
  ]);
  if (is_wp_error($inserted)) {
    slm_subscriptions_log('membership_change_request_error', [
      'user_id' => (int) $user->ID,
      'error' => $inserted->get_error_message(),
      'code' => $inserted->get_error_code(),
    ], 'error');
    $error_url = add_query_arg([
      'change_request' => 'error',
      'change_request_error' => sanitize_key((string) $inserted->get_error_code()),
    ], $return_url);
    wp_safe_redirect($error_url);
    exit;
  }

  slm_subscriptions_log('membership_change_request_created', [
    'request_id' => (int) $inserted,
    'user_id' => (int) $user->ID,
    'current_plan' => sanitize_key((string) ($summary['plan_slug'] ?? '')),
    'current_term' => sanitize_key((string) ($summary['term_code'] ?? '')),
    'desired_plan' => $desired_plan,
    'desired_term' => $desired_term,
  ], 'info');

  $success_url = add_query_arg([
    'change_request' => 'submitted',
    'change_request_id' => (int) $inserted,
  ], $return_url);
  wp_safe_redirect($success_url);
  exit;
}
add_action('template_redirect', 'slm_subscriptions_handle_portal_change_request_submit', 1);

function slm_subscriptions_handle_portal_action(): void {
  $action = sanitize_key((string) ($_GET['action'] ?? ''));
  if (!in_array($action, ['start-membership', 'manage-billing', 'cancel-membership'], true)) return;
  $guest_redirect_view = $action === 'start-membership' ? 'membership-shop' : 'account';
  $guest_redirect_url = add_query_arg('view', $guest_redirect_view, slm_portal_url());
  if (!is_user_logged_in()) {
    wp_safe_redirect(add_query_arg([
      'mode' => 'login',
      'redirect_to' => $guest_redirect_url,
    ], slm_login_url()));
    exit;
  }
  $nonce = (string) ($_GET['_wpnonce'] ?? '');
  if (!wp_verify_nonce($nonce, 'slm_subscription_' . $action)) {
    wp_safe_redirect(add_query_arg(['view' => 'account', 'error' => 'invalid_request'], slm_portal_url()));
    exit;
  }

  $user = wp_get_current_user();
  if (!$user instanceof WP_User || !$user->ID) {
    wp_safe_redirect(add_query_arg([
      'mode' => 'login',
      'redirect_to' => $guest_redirect_url,
    ], slm_login_url()));
    exit;
  }

  $requested_plan = sanitize_key((string) ($_GET['plan'] ?? ''));
  $requested_term = sanitize_key((string) ($_GET['term'] ?? ''));
  $target = null;
  if ($action === 'start-membership') {
    $guard = slm_subscriptions_portal_start_membership_guard($user, $requested_plan, $requested_term);
    if (is_wp_error($guard)) {
      $target = $guard;
    } else {
      $target = slm_subscriptions_checkout_for_plan($user, $requested_plan, $requested_term);
    }
  } elseif ($action === 'manage-billing') {
    $target = slm_subscriptions_billing_action_for_user($user);
  } elseif ($action === 'cancel-membership') {
    $target = slm_subscriptions_square_cancel_membership_for_user($user);
  }
  if (is_wp_error($target)) {
    $provider_for_log = $action === 'start-membership' ? slm_subscriptions_checkout_provider() : slm_subscriptions_user_provider((int) $user->ID);
    $wp_error_code = sanitize_key((string) $target->get_error_code());
    $wp_error_message = (string) $target->get_error_message();
    slm_subscriptions_log('portal_action_error', [
      'action' => $action,
      'user_id' => (int) $user->ID,
      'provider' => $provider_for_log,
      'plan' => $requested_plan,
      'term' => $requested_term,
      'wp_error_code' => $wp_error_code,
      'wp_error_message' => $wp_error_message,
      'error' => $wp_error_message,
    ], 'error');
    $error_code = 'checkout_failed';
    if ($wp_error_code === 'slm_subscription_commitment_active') {
      $error_code = 'commitment_active';
    } elseif ($action === 'start-membership') {
      $error_code = slm_subscriptions_portal_start_membership_error_code($target);
    } elseif (in_array($action, ['manage-billing', 'cancel-membership'], true)) {
      $error_code = 'billing_failed';
    }
    $redirect_view = $action === 'start-membership' ? 'membership-shop' : 'account';
    wp_safe_redirect(add_query_arg(['view' => $redirect_view, 'error' => $error_code], slm_portal_url()));
    exit;
  }
  $target_url = trim((string) $target);
  $site_host = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
  $target_host = strtolower((string) wp_parse_url($target_url, PHP_URL_HOST));

  // Provider checkout/portal URLs are external (Square/Stripe) and wp_safe_redirect()
  // falls back to /wp-admin/ for external hosts unless explicitly allowed.
  if ($target_host !== '' && $site_host !== '' && $target_host !== $site_host) {
    wp_redirect($target_url);
    exit;
  }

  wp_safe_redirect($target_url);
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
  if (!array_key_exists('provider', $state)) {
    if (trim((string) ($state['square_subscription_id'] ?? $state['square_customer_id'] ?? '')) !== '') {
      $state['provider'] = 'square';
    } elseif (trim((string) ($state['stripe_subscription_id'] ?? $state['stripe_customer_id'] ?? '')) !== '') {
      $state['provider'] = 'stripe';
    }
  }
  $map = [
    'plan' => $keys['plan'],
    'provider' => $keys['provider'],
    'term_code' => $keys['term_code'],
    'square_option_id' => $keys['square_option_id'],
    'term_started_at' => $keys['term_started_at'],
    'term_ends_at' => $keys['term_ends_at'],
    'stripe_customer_id' => $keys['stripe_customer_id'],
    'stripe_subscription_id' => $keys['stripe_subscription_id'],
    'square_customer_id' => $keys['square_customer_id'],
    'square_subscription_id' => $keys['square_subscription_id'],
    'status' => $keys['status'],
    'current_period_end' => $keys['current_period_end'],
    'commitment_end' => $keys['commitment_end'],
  ];
  foreach ($map as $field => $meta_key) {
    if (!array_key_exists($field, $state)) continue;
    $value = $state[$field];
    if (in_array($field, ['current_period_end', 'commitment_end', 'term_started_at', 'term_ends_at'], true)) $value = max(0, (int) $value);
    elseif (in_array($field, ['plan', 'provider'], true)) $value = sanitize_key((string) $value);
    elseif (in_array($field, ['term_code'], true)) $value = sanitize_key((string) $value);
    else $value = trim((string) $value);
    update_user_meta($user_id, $meta_key, $value);
  }
}

function slm_subscriptions_commitment_end(int $user_id, string $plan_slug, int $anchor = 0, ?int $months_override = null): int {
  $keys = slm_subscriptions_meta_keys();
  $existing = (int) get_user_meta($user_id, $keys['commitment_end'], true);
  if ($existing > time()) return $existing;
  if ($months_override !== null) {
    $months = max(0, (int) $months_override);
  } else {
    $plan = slm_subscriptions_plan($plan_slug);
    $months = max(0, (int) ($plan['minimum_term_months'] ?? 0));
  }
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

function slm_subscriptions_term_end_from_months(int $anchor_ts, int $months): int {
  $anchor_ts = max(0, $anchor_ts);
  $months = max(0, $months);
  if ($anchor_ts <= 0 || $months <= 0) return 0;
  $end = strtotime('+' . $months . ' months', $anchor_ts);
  return $end ? (int) $end : 0;
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

function slm_subscriptions_square_subscription_plan_id(array $subscription): string {
  $candidates = [
    $subscription['plan_id'] ?? '',
    $subscription['subscription_plan_id'] ?? '',
    $subscription['plan']['id'] ?? '',
    $subscription['subscription_plan']['id'] ?? '',
  ];
  foreach ($candidates as $candidate) {
    $id = trim((string) $candidate);
    if ($id !== '') return $id;
  }
  return '';
}

function slm_subscriptions_square_subscription_option_id(array $subscription): string {
  $candidates = [
    $subscription['plan_variation_id'] ?? '',
    $subscription['subscription_plan_variation_id'] ?? '',
    $subscription['plan_option_id'] ?? '',
    $subscription['subscription_plan_variation']['id'] ?? '',
  ];
  foreach ($candidates as $candidate) {
    $id = trim((string) $candidate);
    if ($id !== '') return $id;
  }
  return '';
}

function slm_subscriptions_square_term_code_from_option_id(string $plan_slug, string $option_id): string {
  $plan_slug = sanitize_key($plan_slug);
  $option_id = trim($option_id);
  if ($plan_slug === '' || $option_id === '') return '';
  $term_options = slm_subscriptions_plan_term_options($plan_slug);
  foreach ($term_options as $term_code => $term_option) {
    if (!is_array($term_option)) continue;
    if (trim((string) ($term_option['checkout_option_id'] ?? '')) === $option_id) return (string) $term_code;
  }
  return '';
}

function slm_subscriptions_square_plan_slug_from_subscription(array $subscription): string {
  $meta_slug = sanitize_key((string) ($subscription['metadata']['slm_plan'] ?? ''));
  if ($meta_slug !== '') return $meta_slug;
  $plan_id = slm_subscriptions_square_subscription_plan_id($subscription);
  if ($plan_id !== '') {
    $slug = slm_subscriptions_plan_slug_by_square_plan_id($plan_id);
    if ($slug !== '') return $slug;
  }
  $option_id = slm_subscriptions_square_subscription_option_id($subscription);
  if ($option_id !== '') {
    $slug = slm_subscriptions_plan_slug_by_square_option_id($option_id);
    if ($slug !== '') return $slug;
  }
  return '';
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

function slm_subscriptions_square_event_type(array $event): string {
  return strtolower(trim((string) ($event['type'] ?? '')));
}

function slm_subscriptions_square_event_id(array $event): string {
  $id = trim((string) ($event['event_id'] ?? $event['id'] ?? ''));
  return $id;
}

function slm_subscriptions_square_event_resource(array $event, string $resource_key): array {
  $data = $event['data'] ?? null;
  if (!is_array($data)) $data = [];
  $object = $data['object'] ?? null;
  if (is_array($object)) {
    if (isset($object[$resource_key]) && is_array($object[$resource_key])) return $object[$resource_key];
    if (($object['type'] ?? '') === $resource_key && isset($object['id'])) return $object;
  }
  if (isset($data[$resource_key]) && is_array($data[$resource_key])) return $data[$resource_key];
  return [];
}

function slm_subscriptions_square_event_resource_id(array $event, string $resource_key): string {
  $res = slm_subscriptions_square_event_resource($event, $resource_key);
  if ($res !== []) {
    $id = trim((string) ($res['id'] ?? ''));
    if ($id !== '') return $id;
  }
  $data = $event['data'] ?? null;
  if (!is_array($data)) $data = [];
  $object = $data['object'] ?? null;
  $candidates = [
    $data['id'] ?? '',
    $object['id'] ?? '',
    $data[$resource_key . '_id'] ?? '',
  ];
  foreach ($candidates as $candidate) {
    $id = trim((string) $candidate);
    if ($id !== '') return $id;
  }
  return '';
}

function slm_subscriptions_square_customer_email(array $customer): string {
  $email = sanitize_email((string) ($customer['email_address'] ?? $customer['email'] ?? ''));
  return $email;
}

function slm_subscriptions_square_find_user_by_customer_id(string $customer_id): ?WP_User {
  $customer_id = trim($customer_id);
  if ($customer_id === '') return null;
  $keys = slm_subscriptions_meta_keys();
  $u = slm_subscriptions_find_user_by_meta($keys['square_customer_id'], $customer_id);
  if ($u instanceof WP_User) return $u;

  $customer = slm_subscriptions_square_retrieve_customer($customer_id);
  if (is_wp_error($customer) || !is_array($customer)) return null;
  $email = slm_subscriptions_square_customer_email($customer);
  if ($email === '') return null;
  $u = get_user_by('email', $email);
  if ($u instanceof WP_User) {
    update_user_meta($u->ID, $keys['square_customer_id'], $customer_id);
    return $u;
  }
  return null;
}

function slm_subscriptions_square_customer_id_from_invoice(array $invoice): string {
  $candidates = [
    $invoice['customer_id'] ?? '',
    $invoice['recipient']['customer_id'] ?? '',
    $invoice['primary_recipient']['customer_id'] ?? '',
  ];
  foreach ($candidates as $candidate) {
    $id = trim((string) $candidate);
    if ($id !== '') return $id;
  }
  $requests = $invoice['payment_requests'] ?? [];
  if (is_array($requests)) {
    foreach ($requests as $request) {
      if (!is_array($request)) continue;
      $id = trim((string) ($request['customer_id'] ?? ''));
      if ($id !== '') return $id;
    }
  }
  return '';
}

function slm_subscriptions_square_subscription_id_from_invoice(array $invoice): string {
  $candidates = [
    $invoice['subscription_id'] ?? '',
    $invoice['subscription_details']['subscription_id'] ?? '',
  ];
  foreach ($candidates as $candidate) {
    $id = trim((string) $candidate);
    if ($id !== '') return $id;
  }
  $requests = $invoice['payment_requests'] ?? [];
  if (is_array($requests)) {
    foreach ($requests as $request) {
      if (!is_array($request)) continue;
      $id = trim((string) ($request['subscription_id'] ?? ''));
      if ($id !== '') return $id;
    }
  }
  return '';
}

function slm_subscriptions_square_resolve_user_from_subscription(array $subscription): ?WP_User {
  $keys = slm_subscriptions_meta_keys();
  $sub_id = trim((string) ($subscription['id'] ?? ''));
  if ($sub_id !== '') {
    $u = slm_subscriptions_find_user_by_meta($keys['square_subscription_id'], $sub_id);
    if ($u instanceof WP_User) return $u;
  }
  $customer_id = trim((string) ($subscription['customer_id'] ?? ''));
  if ($customer_id !== '') {
    return slm_subscriptions_square_find_user_by_customer_id($customer_id);
  }
  return null;
}

function slm_subscriptions_square_resolve_user_from_invoice(array $invoice): ?WP_User {
  $sub_id = slm_subscriptions_square_subscription_id_from_invoice($invoice);
  if ($sub_id !== '') {
    $keys = slm_subscriptions_meta_keys();
    $u = slm_subscriptions_find_user_by_meta($keys['square_subscription_id'], $sub_id);
    if ($u instanceof WP_User) return $u;
  }
  $customer_id = slm_subscriptions_square_customer_id_from_invoice($invoice);
  if ($customer_id !== '') return slm_subscriptions_square_find_user_by_customer_id($customer_id);
  return null;
}

function slm_subscriptions_square_resolve_user_from_event(array $event): ?WP_User {
  $type = slm_subscriptions_square_event_type($event);
  if (strpos($type, 'subscription.') === 0) {
    $sub = slm_subscriptions_square_event_resource($event, 'subscription');
    if ($sub !== []) {
      $u = slm_subscriptions_square_resolve_user_from_subscription($sub);
      if ($u instanceof WP_User) return $u;
    }
    $sub_id = slm_subscriptions_square_event_resource_id($event, 'subscription');
    if ($sub_id !== '') {
      $sub = slm_subscriptions_square_retrieve_subscription($sub_id);
      if (is_array($sub)) return slm_subscriptions_square_resolve_user_from_subscription($sub);
    }
    return null;
  }
  if (strpos($type, 'invoice.') === 0) {
    $invoice = slm_subscriptions_square_event_resource($event, 'invoice');
    if ($invoice !== []) {
      $u = slm_subscriptions_square_resolve_user_from_invoice($invoice);
      if ($u instanceof WP_User) return $u;
    }
    $invoice_id = slm_subscriptions_square_event_resource_id($event, 'invoice');
    if ($invoice_id !== '') {
      $invoice = slm_subscriptions_square_retrieve_invoice($invoice_id);
      if (is_array($invoice)) return slm_subscriptions_square_resolve_user_from_invoice($invoice);
    }
  }
  return null;
}

function slm_subscriptions_square_subscription_anchor_ts(array $subscription): int {
  $candidates = [
    $subscription['start_date'] ?? '',
    $subscription['created_at'] ?? '',
  ];
  foreach ($candidates as $raw) {
    if (!is_string($raw) || trim($raw) === '') continue;
    $ts = strtotime($raw);
    if ($ts) return (int) $ts;
  }
  return time();
}

function slm_subscriptions_square_sync_subscription_state(int $user_id, array $subscription, string $invoice_id = ''): array {
  $keys = slm_subscriptions_meta_keys();
  $existing_square_sub_id = trim((string) get_user_meta($user_id, $keys['square_subscription_id'], true));
  $existing_status = slm_subscriptions_user_exact_status($user_id);
  $plan_slug = slm_subscriptions_square_plan_slug_from_subscription($subscription);
  $status = slm_subscriptions_square_status_to_internal((string) ($subscription['status'] ?? ''));
  $anchor_ts = slm_subscriptions_square_subscription_anchor_ts($subscription);
  $square_plan_id = slm_subscriptions_square_subscription_plan_id($subscription);
  $square_option_id = slm_subscriptions_square_subscription_option_id($subscription);
  $term_code = '';
  if ($plan_slug !== '') {
    $term_code = slm_subscriptions_square_term_code_from_option_id($plan_slug, $square_option_id);
  }
  if ($term_code === '') {
    $pending = slm_subscriptions_square_pending_checkout_selection($user_id);
    if ($plan_slug !== '' && $plan_slug === sanitize_key((string) ($pending['plan_slug'] ?? ''))) {
      $term_code = sanitize_key((string) ($pending['term_code'] ?? ''));
      if ($square_option_id === '') {
        $square_option_id = trim((string) ($pending['square_option_id'] ?? ''));
      }
    }
  }
  if ($term_code === '' && $plan_slug !== '') {
    $term_code = slm_subscriptions_default_term_for_plan($plan_slug);
  }
  $term_option = $plan_slug !== '' ? slm_subscriptions_plan_term_option($plan_slug, $term_code) : [];
  $commitment_months = max(0, (int) ($term_option['commitment_months'] ?? 0));
  if ($commitment_months <= 0 && $plan_slug !== '') {
    $plan_for_commitment = slm_subscriptions_plan($plan_slug);
    $commitment_months = max(0, (int) ($plan_for_commitment['minimum_term_months'] ?? 0));
  }
  $commitment_end = slm_subscriptions_commitment_end($user_id, $plan_slug, $anchor_ts, $commitment_months);
  $term_started_at = $anchor_ts;
  $term_ends_at = slm_subscriptions_term_end_from_months($term_started_at, $commitment_months);
  if ($term_code === 'm2m' && max(0, (int) ($term_option['billing_periods'] ?? 0)) === 0) {
    $term_ends_at = 0;
  }

  $new_square_sub_id = (string) ($subscription['id'] ?? '');
  if (
    $existing_status === 'active' &&
    $existing_square_sub_id !== '' &&
    trim($new_square_sub_id) !== '' &&
    trim($new_square_sub_id) !== $existing_square_sub_id
  ) {
    slm_subscriptions_flag_user_membership_review($user_id, 'multiple_subscriptions', [
      'message' => 'A different Square subscription ID was received for a user already marked active. Manual review required.',
      'existing_square_subscription_id' => $existing_square_sub_id,
      'incoming_square_subscription_id' => trim($new_square_sub_id),
      'invoice_id' => $invoice_id,
    ]);
    slm_subscriptions_log('square_duplicate_subscription_detected', [
      'user_id' => $user_id,
      'existing_square_subscription_id' => $existing_square_sub_id,
      'incoming_square_subscription_id' => trim($new_square_sub_id),
      'status' => $status,
    ], 'error');
  }

  slm_subscriptions_update_state($user_id, [
    'provider' => 'square',
    'plan' => $plan_slug,
    'term_code' => $term_code,
    'square_option_id' => $square_option_id,
    'term_started_at' => $term_started_at,
    'term_ends_at' => $term_ends_at,
    'square_customer_id' => (string) ($subscription['customer_id'] ?? ''),
    'square_subscription_id' => $new_square_sub_id,
    'status' => $status,
    'current_period_end' => slm_subscriptions_square_period_end($subscription),
    'commitment_end' => $commitment_end,
  ]);
  if ($term_code !== '') {
    delete_transient('slm_square_pending_checkout_' . $user_id);
  }
  if (trim($new_square_sub_id) !== '') {
    slm_subscriptions_set_test_membership_flag($user_id, false);
  }
  $issues_after = slm_subscriptions_user_membership_state_issues($user_id);
  $review_flag_reason = sanitize_key((string) get_user_meta($user_id, $keys['membership_review_flag'], true));
  $issue_reasons = is_array($issues_after['reasons'] ?? null) ? (array) $issues_after['reasons'] : [];
  if ($review_flag_reason !== '') {
    $issue_reasons = array_values(array_diff(array_map('sanitize_key', $issue_reasons), [$review_flag_reason]));
  }
  if ($issue_reasons === []) {
    slm_subscriptions_clear_user_membership_review($user_id);
  }

  if ($plan_slug !== '') {
    if (slm_subscriptions_is_active_status($status)) {
      $sync = slm_subscriptions_aryeo_apply_profile($user_id, $plan_slug, $invoice_id);
      if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message(), 'user_id' => $user_id];
      if ($invoice_id !== '' && function_exists('slm_member_credits_grant_cycle_for_square_invoice')) {
        $grant = slm_member_credits_grant_cycle_for_square_invoice($user_id, $subscription, $invoice_id, 'square_invoice');
        if (is_array($grant) && empty($grant['ok'])) {
          return ['ok' => false, 'status' => 'error', 'message' => (string) ($grant['message'] ?? 'Credit grant failed.'), 'user_id' => $user_id];
        }
      }
    } else {
      $sync = slm_subscriptions_aryeo_revoke_profile($user_id, $plan_slug, 'square_subscription');
      if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message(), 'user_id' => $user_id];
    }
  }

  return ['ok' => true, 'status' => 'processed', 'user_id' => $user_id, 'plan_slug' => $plan_slug, 'term_code' => $term_code, 'square_plan_id' => $square_plan_id, 'square_option_id' => $square_option_id, 'message' => 'Square subscription synced.'];
}

function slm_subscriptions_square_handle_subscription_event(array $event): array {
  $sub = slm_subscriptions_square_event_resource($event, 'subscription');
  if ($sub === []) {
    $sub_id = slm_subscriptions_square_event_resource_id($event, 'subscription');
    if ($sub_id === '') return ['ok' => false, 'status' => 'error', 'message' => 'Missing subscription in Square event.'];
    $sub = slm_subscriptions_square_retrieve_subscription($sub_id);
    if (is_wp_error($sub)) return ['ok' => false, 'status' => 'error', 'message' => $sub->get_error_message()];
  }
  if (!is_array($sub) || $sub === []) return ['ok' => false, 'status' => 'error', 'message' => 'Invalid Square subscription payload.'];
  $user = slm_subscriptions_square_resolve_user_from_subscription($sub);
  if (!$user instanceof WP_User) return ['ok' => false, 'status' => 'error', 'message' => 'Unable to map Square subscription to user.'];
  return slm_subscriptions_square_sync_subscription_state((int) $user->ID, $sub, '');
}

function slm_subscriptions_square_handle_invoice_payment_made(array $event): array {
  $invoice = slm_subscriptions_square_event_resource($event, 'invoice');
  if ($invoice === []) {
    $invoice_id = slm_subscriptions_square_event_resource_id($event, 'invoice');
    if ($invoice_id === '') return ['ok' => false, 'status' => 'error', 'message' => 'Missing invoice in Square event.'];
    $invoice = slm_subscriptions_square_retrieve_invoice($invoice_id);
    if (is_wp_error($invoice)) return ['ok' => false, 'status' => 'error', 'message' => $invoice->get_error_message()];
  }
  if (!is_array($invoice) || $invoice === []) return ['ok' => false, 'status' => 'error', 'message' => 'Invalid Square invoice payload.'];
  $user = slm_subscriptions_square_resolve_user_from_invoice($invoice);
  if (!$user instanceof WP_User) return ['ok' => false, 'status' => 'error', 'message' => 'Unable to map Square invoice to user.'];

  $sub_id = slm_subscriptions_square_subscription_id_from_invoice($invoice);
  if ($sub_id === '') return ['ok' => false, 'status' => 'error', 'message' => 'Square invoice is not linked to a subscription.', 'user_id' => (int) $user->ID];
  $sub = slm_subscriptions_square_retrieve_subscription($sub_id);
  if (is_wp_error($sub)) return ['ok' => false, 'status' => 'error', 'message' => $sub->get_error_message(), 'user_id' => (int) $user->ID];
  if (!is_array($sub) || $sub === []) return ['ok' => false, 'status' => 'error', 'message' => 'Invalid Square subscription payload.', 'user_id' => (int) $user->ID];

  $invoice_id = trim((string) ($invoice['id'] ?? ''));
  return slm_subscriptions_square_sync_subscription_state((int) $user->ID, $sub, $invoice_id);
}

function slm_subscriptions_square_handle_invoice_failed(array $event): array {
  $invoice = slm_subscriptions_square_event_resource($event, 'invoice');
  if ($invoice === []) {
    $invoice_id = slm_subscriptions_square_event_resource_id($event, 'invoice');
    if ($invoice_id === '') return ['ok' => false, 'status' => 'error', 'message' => 'Missing invoice in Square event.'];
    $invoice = slm_subscriptions_square_retrieve_invoice($invoice_id);
    if (is_wp_error($invoice)) return ['ok' => false, 'status' => 'error', 'message' => $invoice->get_error_message()];
  }
  if (!is_array($invoice) || $invoice === []) return ['ok' => false, 'status' => 'error', 'message' => 'Invalid Square invoice payload.'];
  $user = slm_subscriptions_square_resolve_user_from_invoice($invoice);
  if (!$user instanceof WP_User) return ['ok' => false, 'status' => 'error', 'message' => 'Unable to map Square failed invoice to user.'];

  $sub_id = slm_subscriptions_square_subscription_id_from_invoice($invoice);
  if ($sub_id === '') return ['ok' => false, 'status' => 'error', 'message' => 'Square invoice is not linked to a subscription.', 'user_id' => (int) $user->ID];
  $sub = slm_subscriptions_square_retrieve_subscription($sub_id);
  if (is_wp_error($sub)) return ['ok' => false, 'status' => 'error', 'message' => $sub->get_error_message(), 'user_id' => (int) $user->ID];
  if (!is_array($sub)) return ['ok' => false, 'status' => 'error', 'message' => 'Invalid Square subscription payload.', 'user_id' => (int) $user->ID];

  $status = slm_subscriptions_square_status_to_internal((string) ($sub['status'] ?? ''));
  if ($status === '' || $status === 'active') $status = 'past_due';
  $plan_slug = slm_subscriptions_square_plan_slug_from_subscription($sub);

  slm_subscriptions_update_state((int) $user->ID, [
    'provider' => 'square',
    'plan' => $plan_slug,
    'square_customer_id' => (string) ($sub['customer_id'] ?? ''),
    'square_subscription_id' => (string) ($sub['id'] ?? $sub_id),
    'status' => $status,
    'current_period_end' => slm_subscriptions_square_period_end($sub),
  ]);

  if ($plan_slug !== '') {
    $sync = slm_subscriptions_aryeo_revoke_profile((int) $user->ID, $plan_slug, 'invoice_failed');
    if (is_wp_error($sync)) return ['ok' => false, 'status' => 'error', 'message' => $sync->get_error_message(), 'user_id' => (int) $user->ID];
  }
  return ['ok' => true, 'status' => 'processed', 'user_id' => (int) $user->ID, 'message' => 'Square invoice failure processed.'];
}

function slm_subscriptions_square_process_event(array $event): array {
  $type = slm_subscriptions_square_event_type($event);
  switch ($type) {
    case 'subscription.created':
    case 'subscription.updated':
      return slm_subscriptions_square_handle_subscription_event($event);
    case 'invoice.payment_made':
      return slm_subscriptions_square_handle_invoice_payment_made($event);
    case 'invoice.scheduled_charge_failed':
      return slm_subscriptions_square_handle_invoice_failed($event);
    default:
      return ['ok' => true, 'status' => 'ignored', 'message' => 'Unhandled Square event type: ' . $type];
  }
}

function slm_subscriptions_square_webhook(WP_REST_Request $req) {
  $raw = (string) $req->get_body();
  if ($raw === '') return new WP_REST_Response(['ok' => false, 'error' => 'empty_payload'], 400);

  $secret = slm_subscriptions_square_webhook_signature_key();
  if ($secret === '') return new WP_REST_Response(['ok' => false, 'error' => 'webhook_signature_key_not_configured'], 500);

  $signature = (string) $req->get_header('x-square-hmacsha256-signature');
  if (!slm_subscriptions_square_verify_webhook_signature($raw, $signature, $secret)) {
    slm_subscriptions_log('square_event_rejected_signature', [
      'path' => (string) $req->get_route(),
      'has_signature' => $signature !== '',
      'remote_addr' => isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '',
    ], 'error');
    return new WP_REST_Response(['ok' => false, 'error' => 'invalid_signature'], 401);
  }

  $payload = json_decode($raw, true);
  if (!is_array($payload)) return new WP_REST_Response(['ok' => false, 'error' => 'invalid_json'], 400);
  $event_id = slm_subscriptions_square_event_id($payload);
  $event_type = slm_subscriptions_square_event_type($payload);
  if ($event_id === '' || $event_type === '') return new WP_REST_Response(['ok' => false, 'error' => 'invalid_event'], 400);
  slm_subscriptions_log('square_event_received', ['event_id' => $event_id, 'event_type' => $event_type], 'info');

  $row = slm_subscriptions_get_event_row($event_id);
  if ($row && in_array((string) ($row['status'] ?? ''), ['processed', 'ignored'], true)) {
    return new WP_REST_Response(['ok' => true, 'duplicate' => true], 200);
  }
  if (!$row) {
    slm_subscriptions_insert_event_row($event_id, $event_type);
  } else {
    slm_subscriptions_update_event_row($event_id, 'received', (int) ($row['user_id'] ?? 0), ['provider' => 'square', 'retry' => true]);
  }

  $result = slm_subscriptions_square_process_event($payload);
  $ok = !empty($result['ok']);
  $status = (string) ($result['status'] ?? ($ok ? 'processed' : 'error'));
  $user_id = (int) ($result['user_id'] ?? 0);
  $result['provider'] = 'square';
  slm_subscriptions_update_event_row($event_id, $status, $user_id, $result);
  slm_subscriptions_log('square_event_' . $status, ['event_id' => $event_id, 'event_type' => $event_type, 'user_id' => $user_id, 'message' => (string) ($result['message'] ?? '')], $ok ? 'info' : 'error');

  if (!$ok) return new WP_REST_Response(['ok' => false, 'error' => (string) ($result['message'] ?? 'processing_failed')], 500);
  return new WP_REST_Response(['ok' => true, 'status' => $status], 200);
}

add_action('rest_api_init', function () {
  register_rest_route('slm/v1', '/square/webhook', [
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => 'slm_subscriptions_square_webhook',
  ]);
});

function slm_subscriptions_reconcile_user(int $user_id): array {
  $keys = slm_subscriptions_meta_keys();
  $provider = slm_subscriptions_user_provider($user_id);
  $has_square = trim((string) get_user_meta($user_id, $keys['square_subscription_id'], true)) !== '';
  if ($provider === 'square' && $has_square) {
    return ['ok' => true, 'status' => 'ignored', 'message' => 'Square is active provider for this user.'];
  }
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

function slm_subscriptions_square_reconcile_user(int $user_id): array {
  $keys = slm_subscriptions_meta_keys();
  $provider = slm_subscriptions_user_provider($user_id);
  $has_stripe = trim((string) get_user_meta($user_id, $keys['stripe_subscription_id'], true)) !== '';
  if ($provider === 'stripe' && $has_stripe) {
    return ['ok' => true, 'status' => 'ignored', 'message' => 'Stripe is active provider for this user.'];
  }
  $sub_id = trim((string) get_user_meta($user_id, $keys['square_subscription_id'], true));
  if ($sub_id === '') return ['ok' => true, 'status' => 'ignored', 'message' => 'No Square subscription ID.'];

  $sub = slm_subscriptions_square_retrieve_subscription($sub_id);
  if (is_wp_error($sub)) return ['ok' => false, 'status' => 'error', 'message' => $sub->get_error_message()];
  if (!is_array($sub) || $sub === []) return ['ok' => false, 'status' => 'error', 'message' => 'Square subscription payload was empty.'];

  $sync_result = slm_subscriptions_square_sync_subscription_state($user_id, $sub, '');
  if (empty($sync_result['ok'])) return $sync_result;
  if (function_exists('slm_member_credits_reconcile_user')) {
    $credits_result = slm_member_credits_reconcile_user($user_id);
    if (is_array($credits_result) && empty($credits_result['ok'])) {
      slm_subscriptions_log('member_credits_reconcile_failed', ['user_id' => $user_id, 'result' => $credits_result], 'error');
    }
  }
  return ['ok' => true, 'status' => 'processed', 'message' => 'Square reconciled.'];
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

  $offset = 0;
  do {
    $users = get_users([
      'number' => $batch,
      'offset' => $offset,
      'meta_key' => $keys['square_subscription_id'],
      'meta_compare' => 'EXISTS',
      'fields' => ['ID'],
      'orderby' => 'ID',
      'order' => 'ASC',
    ]);
    if (!is_array($users) || empty($users)) break;
    foreach ($users as $u) {
      $uid = (int) ($u->ID ?? 0);
      if ($uid <= 0) continue;
      $result = slm_subscriptions_square_reconcile_user($uid);
      slm_subscriptions_log('daily_reconcile_square', ['user_id' => $uid, 'result' => $result], !empty($result['ok']) ? 'info' : 'error');
    }
    $offset += $batch;
  } while (count($users) === $batch);
}

add_filter('cron_schedules', function (array $schedules): array {
  if (!isset($schedules['slm_every_15_minutes'])) {
    $schedules['slm_every_15_minutes'] = [
      'interval' => 15 * MINUTE_IN_SECONDS,
      'display' => 'Every 15 Minutes (SLM)',
    ];
  }
  return $schedules;
});

add_action('init', function () {
  if (!wp_next_scheduled('slm_subscriptions_daily_reconcile')) {
    wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'slm_subscriptions_daily_reconcile');
  }
  if (!wp_next_scheduled('slm_subscriptions_reconcile_tick')) {
    wp_schedule_event(time() + (5 * MINUTE_IN_SECONDS), 'slm_every_15_minutes', 'slm_subscriptions_reconcile_tick');
  }
}, 8);
add_action('slm_subscriptions_daily_reconcile', 'slm_subscriptions_daily_reconcile');
add_action('slm_subscriptions_reconcile_tick', 'slm_subscriptions_daily_reconcile');

function slm_subscriptions_rollover_policy_label(string $policy): string {
  $policy = sanitize_key($policy);
  $labels = [
    'none' => 'No Rollover',
    'social_bonus_only' => 'Bonus Listing Shoots Roll Over Until Term End',
    'all_until_term_end' => 'All Included Credits Roll Over Until Term End',
  ];
  return $labels[$policy] ?? 'No Rollover';
}

function slm_subscriptions_rollover_policy_description(string $policy): string {
  $policy = sanitize_key($policy);
  switch ($policy) {
    case 'social_bonus_only':
      return 'Monthly plan inclusions reset each cycle. Bonus listing-shoot credits (when included by the agreement) can roll over until the agreement term ends.';
    case 'all_until_term_end':
      return 'Included credits generally reset monthly, but unused credits may roll over while the agreement term remains active.';
    case 'none':
    default:
      return 'Included membership benefits typically reset each billing cycle unless otherwise noted.';
  }
}

function slm_subscriptions_entitlement_rows_for_plan(string $plan_slug): array {
  $plan = slm_subscriptions_plan($plan_slug);
  if (!$plan || !is_array($plan)) return [];
  $entitlements = is_array($plan['entitlements'] ?? null) ? (array) $plan['entitlements'] : [];
  if ($entitlements === []) return [];
  $labels = function_exists('slm_member_credits_key_labels') ? (array) slm_member_credits_key_labels() : [];
  $rows = [];
  foreach ($entitlements as $key => $qty) {
    $key = sanitize_key((string) $key);
    if ($key === '') continue;
    $rows[] = [
      'key' => $key,
      'label' => (string) ($labels[$key] ?? ucwords(str_replace('_', ' ', $key))),
      'qty' => max(0, (int) $qty),
    ];
  }
  return $rows;
}

function slm_subscriptions_plan_feature_highlights(string $plan_slug): array {
  $plan_slug = sanitize_key($plan_slug);
  $map = [
    'monthly-momentum' => ['45 minute session', '4 edited reels', '1 talking head video'],
    'growth-engine' => ['1.5 hour session', '10 edited reels', '1 talking head video', '1 horizontal video', '5 branded Instagram posts'],
    'brand-authority' => ['2 hour session', '15 edited reels', '2 talking head videos', '1 horizontal video', '8 branded Instagram posts'],
    'elite-presence' => ['Half-day session', '25 edited reels', '2 talking head videos', '2 horizontal videos', '10 branded Instagram posts', 'Social media post plan'],
    'vip-presence' => ['Full-day content shoot', '30 edited reels', '3 talking head videos', '3 horizontal videos', '15 branded Instagram posts', 'Social media post plan', 'Caption Suggestions', 'Strategic media analysis'],
    'agent-starting' => ['1 listing shoot', '1 AI video for 1 listing', '1 staged photo or 1 dusk conversion'],
    'agent-growing' => ['3 listing shoots', '1 AI video for 1 listing', '1 agent intro video', '2 staged or dusk conversions', '3 branded Instagram posts'],
    'agent-established' => ['5 listing shoots', '2 AI videos for listings', '2 agent intro videos', '1 horizontal video/tour', '5 branded Instagram posts'],
    'agent-elite' => ['9 listing shoots', '5 AI videos', '4 agent intro videos', '2 horizontal videos/tours', '10 branded Instagram posts', '4 staged or dusk conversions'],
    'agent-top-tier' => ['15 listing shoots', '7 AI videos', '6 agent intro videos', '4 horizontal videos/tours', '15 branded Instagram posts', '8 staged or dusk conversions'],
  ];
  $features = $map[$plan_slug] ?? [];
  if (!is_array($features)) return [];
  return array_values(array_filter(array_map('strval', $features), static function ($v) { return trim((string) $v) !== ''; }));
}

function slm_get_user_subscription_summary(int $user_id): array {
  $keys = slm_subscriptions_meta_keys();
  $plan_slug = sanitize_key((string) get_user_meta($user_id, $keys['plan'], true));
  $status = sanitize_key((string) get_user_meta($user_id, $keys['status'], true));
  $period_end = (int) get_user_meta($user_id, $keys['current_period_end'], true);
  $commitment_end = (int) get_user_meta($user_id, $keys['commitment_end'], true);
  $term_code = sanitize_key((string) get_user_meta($user_id, $keys['term_code'], true));
  $term_ends_at = (int) get_user_meta($user_id, $keys['term_ends_at'], true);
  $provider = slm_subscriptions_user_provider($user_id);
  $stripe_sub_id = trim((string) get_user_meta($user_id, $keys['stripe_subscription_id'], true));
  $square_sub_id = trim((string) get_user_meta($user_id, $keys['square_subscription_id'], true));
  $sub_id = $provider === 'square' ? $square_sub_id : $stripe_sub_id;
  if ($sub_id === '') $sub_id = $stripe_sub_id !== '' ? $stripe_sub_id : $square_sub_id;
  $plan = slm_subscriptions_plan($plan_slug);
  $plan_label = $plan ? (string) ($plan['label'] ?? $plan_slug) : 'No active plan';
  $plan_entitlements = $plan_slug !== '' ? slm_subscriptions_entitlement_rows_for_plan($plan_slug) : [];
  $plan_features = $plan_slug !== '' ? slm_subscriptions_plan_feature_highlights($plan_slug) : [];
  $rollover_policy = sanitize_key((string) ($plan['rollover_policy'] ?? 'none'));
  $is_test_membership = trim((string) get_user_meta($user_id, $keys['test_membership'], true)) === '1';
  $state_issues = slm_subscriptions_user_membership_state_issues($user_id);
  $can_manage_billing = false;
  $manage_billing_label = 'Manage Billing';
  if ($provider === 'square') {
    $can_manage_billing = slm_subscriptions_square_access_token() !== '' && $square_sub_id !== '';
    $manage_billing_label = 'Cancel Membership';
  } else {
    $can_manage_billing = slm_subscriptions_stripe_secret_key() !== '' && $stripe_sub_id !== '';
  }

  return [
    'plan_slug' => $plan_slug,
    'plan_label' => $plan_label,
    'provider' => $provider,
    'term_code' => $term_code,
    'term_label' => $term_code !== '' ? slm_subscriptions_term_label($term_code) : 'N/A',
    'status' => $status !== '' ? $status : 'non_member',
    'status_label' => slm_subscriptions_status_label($status !== '' ? $status : 'non_member'),
    'is_active' => slm_subscriptions_is_active_status($status),
    'is_exact_active' => strtolower((string) $status) === 'active',
    'is_test_membership' => $is_test_membership,
    'subscription_id' => $sub_id,
    'current_period_end' => $period_end,
    'current_period_end_label' => $period_end > 0 ? wp_date('M j, Y', $period_end) : 'N/A',
    'commitment_end' => $commitment_end,
    'commitment_end_label' => $commitment_end > 0 ? wp_date('M j, Y', $commitment_end) : 'N/A',
    'term_ends_at' => $term_ends_at,
    'term_ends_at_label' => $term_ends_at > 0 ? wp_date('M j, Y', $term_ends_at) : 'N/A',
    'within_commitment' => $commitment_end > time(),
    'entitlements' => $plan_entitlements,
    'plan_features' => $plan_features,
    'rollover_policy' => $rollover_policy,
    'rollover_policy_label' => slm_subscriptions_rollover_policy_label($rollover_policy),
    'rollover_policy_description' => slm_subscriptions_rollover_policy_description($rollover_policy),
    'state_issues' => $state_issues,
    'has_membership_state_issue' => !empty($state_issues['has_issue']),
    'can_manage_billing' => $can_manage_billing,
    'manage_billing_url' => slm_subscriptions_manage_billing_url(),
    'manage_billing_label' => $manage_billing_label,
  ];
}

function slm_subscriptions_active_plans_for_cta(): array {
  $plans = [];
  $provider = slm_subscriptions_checkout_provider();
  foreach (slm_subscription_plans() as $slug => $plan) {
    if (!is_array($plan)) continue;
    if (empty($plan['active'])) continue;
    if ($provider === 'square') {
      if (trim((string) ($plan['square_subscription_plan_id'] ?? '')) === '') continue;
      // Keep plans visible during migration even if term option IDs are not populated yet.
      // Checkout validation will return a clear error if a selected term is missing a checkout option ID.
    } else {
      if (trim((string) ($plan['stripe_price_id'] ?? '')) === '') continue;
    }
    $plans[$slug] = $plan;
  }
  return $plans;
}

function slm_subscriptions_square_plan_mapping_validation_summary(): array {
  $items = [];
  $active_count = 0;
  $ready_count = 0;
  $inactive_skipped_count = 0;

  foreach (slm_subscription_plans() as $slug => $plan) {
    if (!is_array($plan)) continue;
    $slug = sanitize_key((string) $slug);
    if ($slug === '') continue;

    $label = sanitize_text_field((string) ($plan['label'] ?? $slug));
    $is_active = !empty($plan['active']);
    $missing = [];
    $available_terms = [];
    $missing_terms = [];

    if (!$is_active) {
      $inactive_skipped_count++;
      $items[] = [
        'slug' => $slug,
        'label' => $label,
        'active' => false,
        'status' => 'inactive',
        'missing' => [],
        'available_terms' => [],
        'missing_terms' => [],
      ];
      continue;
    }

    $active_count++;
    if (trim((string) ($plan['square_subscription_plan_id'] ?? '')) === '') {
      $missing[] = 'square_subscription_plan_id';
    }

    $availability = slm_subscriptions_square_plan_term_availability($slug);
    $available_terms = is_array($availability['available_terms'] ?? null) ? array_values((array) $availability['available_terms']) : [];
    $missing_terms = is_array($availability['missing_terms'] ?? null) ? array_values((array) $availability['missing_terms']) : [];
    foreach ($missing_terms as $term_code) {
      $term_code = sanitize_key((string) $term_code);
      if ($term_code === '') continue;
      $missing[] = 'square_term_options.' . $term_code . '.checkout_option_id';
    }

    $has_any_checkout_term = !empty($availability['is_checkout_ready']);
    $is_fully_ready = $missing === [];
    if ($is_fully_ready) $ready_count++;

    $items[] = [
      'slug' => $slug,
      'label' => $label,
      'active' => true,
      'status' => $is_fully_ready ? 'ready' : ($has_any_checkout_term ? 'partial' : 'needs_config'),
      'missing' => array_values(array_unique($missing)),
      'available_terms' => array_values(array_unique(array_map('sanitize_key', $available_terms))),
      'missing_terms' => array_values(array_unique(array_map('sanitize_key', $missing_terms))),
    ];
  }

  return [
    'active_count' => $active_count,
    'ready_count' => $ready_count,
    'inactive_skipped_count' => $inactive_skipped_count,
    'items' => $items,
  ];
}

function slm_subscriptions_render_square_plan_mapping_validation_summary(): void {
  $summary = slm_subscriptions_square_plan_mapping_validation_summary();
  $items = is_array($summary['items'] ?? null) ? (array) $summary['items'] : [];
  $active_count = (int) ($summary['active_count'] ?? 0);
  $ready_count = (int) ($summary['ready_count'] ?? 0);
  $inactive_skipped_count = (int) ($summary['inactive_skipped_count'] ?? 0);

  echo '<div style="margin-top:12px; padding:12px; border:1px solid #dcdcde; background:#fff;">';
  echo '<p style="margin:0 0 6px;"><strong>Square Mapping Validation</strong></p>';
  echo '<p class="description" style="margin:0 0 10px;">Active plans fully mapped for Square checkout: <strong>' . esc_html((string) $ready_count) . '</strong> / <strong>' . esc_html((string) $active_count) . '</strong>. Inactive plans skipped: <strong>' . esc_html((string) $inactive_skipped_count) . '</strong>.</p>';

  if ($items === []) {
    echo '<p class="description" style="margin:0;">No plans found.</p>';
    echo '</div>';
    return;
  }

  echo '<ul style="margin:0; padding-left:18px;">';
  foreach ($items as $item) {
    if (!is_array($item)) continue;
    $label = (string) ($item['label'] ?? ($item['slug'] ?? 'Plan'));
    $slug = (string) ($item['slug'] ?? '');
    $status = (string) ($item['status'] ?? '');
    $missing = is_array($item['missing'] ?? null) ? (array) $item['missing'] : [];
    $available_terms = is_array($item['available_terms'] ?? null) ? (array) $item['available_terms'] : [];
    $status_label = 'Needs Configuration';
    if ($status === 'ready') $status_label = 'Ready';
    if ($status === 'partial') $status_label = 'Partially Configured';
    if ($status === 'inactive') $status_label = 'Skipped (Inactive)';

    $line = esc_html($label);
    if ($slug !== '') $line .= ' (' . esc_html($slug) . ')';
    $line .= ': ' . esc_html($status_label);

    if ($available_terms !== []) {
      $line .= ' | Available terms: ' . esc_html(implode(', ', array_map('strtoupper', array_map('sanitize_key', $available_terms))));
    }
    if ($missing !== []) {
      $line .= ' | Missing: ' . esc_html(implode(', ', array_map('strval', $missing)));
    }
    echo '<li style="margin:0 0 4px;">' . $line . '</li>';
  }
  echo '</ul>';
  echo '</div>';
}

add_action('admin_menu', function () {
  add_options_page('SLM Subscriptions', 'SLM Subscriptions', 'manage_options', 'slm-subscriptions', 'slm_subscriptions_render_settings_page');
});

add_action('admin_init', function () {
  register_setting('slm_subscriptions', 'slm_subscriptions_provider_mode', ['sanitize_callback' => function ($v) {
    $v = sanitize_key((string) $v);
    return in_array($v, ['stripe', 'square', 'dual'], true) ? $v : 'dual';
  }]);
  register_setting('slm_subscriptions', 'slm_subscriptions_checkout_provider', ['sanitize_callback' => function ($v) {
    $v = sanitize_key((string) $v);
    return in_array($v, ['stripe', 'square'], true) ? $v : 'square';
  }]);
  register_setting('slm_subscriptions', 'slm_stripe_secret_key', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_stripe_publishable_key', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_stripe_webhook_secret', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_stripe_portal_config_id', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_stripe_checkout_success_url', ['sanitize_callback' => function ($v) { return esc_url_raw((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_stripe_checkout_cancel_url', ['sanitize_callback' => function ($v) { return esc_url_raw((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_square_environment', ['sanitize_callback' => function ($v) {
    $v = sanitize_key((string) $v);
    return in_array($v, ['sandbox', 'production'], true) ? $v : 'sandbox';
  }]);
  register_setting('slm_subscriptions', 'slm_square_access_token', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_square_webhook_signature_key', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_square_location_id', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_square_application_id', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_member_credits_auto_deduct_enabled', ['sanitize_callback' => function ($v) { return empty($v) ? '0' : '1'; }]);
  register_setting('slm_subscriptions', 'slm_member_credits_overage_allowed', ['sanitize_callback' => function ($v) { return empty($v) ? '0' : '1'; }]);
  register_setting('slm_subscriptions', 'slm_credit_service_mapping_json', ['sanitize_callback' => function ($v) {
    if (function_exists('slm_member_credits_sanitize_service_mapping')) return slm_member_credits_sanitize_service_mapping($v);
    return [];
  }]);
  register_setting('slm_subscriptions', 'slm_aryeo_affiliate_removal_path', ['sanitize_callback' => function ($v) { return trim((string) $v); }]);
  register_setting('slm_subscriptions', 'slm_subscription_plans', ['sanitize_callback' => 'slm_subscriptions_sanitize_plans']);
  register_setting('slm_subscriptions', 'slm_subscription_benefit_profiles', ['sanitize_callback' => 'slm_subscriptions_sanitize_profiles']);

  add_settings_section('slm_subscriptions_main', 'Subscription Provider + Aryeo Settings', '__return_false', 'slm_subscriptions');

  add_settings_field('slm_subscriptions_provider_mode', 'Provider Mode', function () {
    $val = slm_subscriptions_provider_mode();
    echo '<select name="slm_subscriptions_provider_mode">';
    foreach (['dual' => 'Dual (transition)', 'square' => 'Square only', 'stripe' => 'Stripe only'] as $k => $label) {
      echo '<option value="' . esc_attr($k) . '"' . selected($val, $k, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Controls which provider(s) remain active during migration.</p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_subscriptions_checkout_provider', 'Checkout Provider (New Signups)', function () {
    $val = slm_subscriptions_checkout_provider();
    echo '<select name="slm_subscriptions_checkout_provider">';
    foreach (['square' => 'Square', 'stripe' => 'Stripe'] as $k => $label) {
      echo '<option value="' . esc_attr($k) . '"' . selected($val, $k, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Used by the membership CTA and portal start-membership flow.</p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

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

  add_settings_field('slm_square_environment', 'Square Environment', function () {
    $val = slm_subscriptions_square_environment();
    echo '<select name="slm_square_environment">';
    foreach (['sandbox' => 'Sandbox', 'production' => 'Production'] as $k => $label) {
      echo '<option value="' . esc_attr($k) . '"' . selected($val, $k, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Use sandbox while configuring the dummy account and webhook flow.</p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_square_access_token', 'Square Access Token', function () {
    echo '<input type="password" name="slm_square_access_token" value="' . esc_attr(slm_subscriptions_square_access_token()) . '" class="regular-text" autocomplete="off" />';
    echo '<p class="description">Used for server-side Square API requests.</p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_square_webhook_signature_key', 'Square Webhook Signature Key', function () {
    echo '<input type="text" name="slm_square_webhook_signature_key" value="' . esc_attr(slm_subscriptions_square_webhook_signature_key()) . '" class="regular-text code" />';
    echo '<p class="description">Required for Square webhook signature verification.</p>';
    echo '<p class="description">Webhook URL: <code>' . esc_html(rest_url('slm/v1/square/webhook')) . '</code></p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_square_location_id', 'Square Location ID', function () {
    echo '<input type="text" name="slm_square_location_id" value="' . esc_attr(slm_subscriptions_square_location_id()) . '" class="regular-text code" placeholder="L..." />';
    echo '<p class="description">Required for hosted payment link subscription checkout.</p>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_square_application_id', 'Square Application ID (optional)', function () {
    echo '<input type="text" name="slm_square_application_id" value="' . esc_attr(slm_subscriptions_square_application_id()) . '" class="regular-text code" />';
    echo '<p class="description">Not required for hosted links, but useful for future Web Payments SDK work.</p>';
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
    echo '<p class="description">Map each plan slug to Stripe/Square billing IDs and membership rules. For Square, <code>square_subscription_plan_id</code> is the tier plan ID and <code>square_term_options.*.checkout_option_id</code> stores the term-specific checkout option ID.</p>';
    slm_subscriptions_render_square_plan_mapping_validation_summary();
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_member_credits_auto_deduct_enabled', 'Auto Deduct Member Credits', function () {
    $enabled = function_exists('slm_member_credits_auto_deduct_enabled') ? slm_member_credits_auto_deduct_enabled() : true;
    echo '<input type="hidden" name="slm_member_credits_auto_deduct_enabled" value="0" />';
    echo '<label><input type="checkbox" name="slm_member_credits_auto_deduct_enabled" value="1" ' . checked($enabled, true, false) . ' /> Deduct credits automatically when Aryeo orders are completed/delivered</label>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_member_credits_overage_allowed', 'Allow Credit Overage', function () {
    $enabled = function_exists('slm_member_credits_overage_allowed') ? slm_member_credits_overage_allowed() : true;
    echo '<input type="hidden" name="slm_member_credits_overage_allowed" value="0" />';
    echo '<label><input type="checkbox" name="slm_member_credits_overage_allowed" value="1" ' . checked($enabled, true, false) . ' /> Allow usage overages and flag them for admin follow-up</label>';
  }, 'slm_subscriptions', 'slm_subscriptions_main');

  add_settings_field('slm_credit_service_mapping_json', 'Aryeo Service -> Credit Mapping (JSON)', function () {
    $mapping = function_exists('slm_member_credits_service_mapping') ? slm_member_credits_service_mapping() : [];
    if (!is_array($mapping) || $mapping === []) {
      $mapping = function_exists('slm_member_credits_default_service_mapping') ? slm_member_credits_default_service_mapping() : ['by_service_name' => [], 'by_service_id' => []];
    }
    $json = wp_json_encode($mapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo '<textarea name="slm_credit_service_mapping_json" class="large-text code" rows="14">' . esc_textarea((string) $json) . '</textarea>';
    echo '<p class="description">Used for automatic credit deduction on Aryeo completion/delivery webhooks. Match by service ID when possible; service name matching is case-insensitive exact text.</p>';
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
  $square_test = null;
  if (isset($_POST['slm_subscriptions_test']) && check_admin_referer('slm_subscriptions_test')) {
    $test = slm_subscriptions_stripe_request('GET', '/prices', ['limit' => 1]);
  }
  if (isset($_POST['slm_subscriptions_test_square']) && check_admin_referer('slm_subscriptions_test_square')) {
    $square_test = slm_subscriptions_square_request('GET', '/v2/locations');
  }

  echo '<div class="wrap">';
  echo '<h1>SLM Subscriptions</h1>';
  echo '<p>Stripe/Square subscription billing + Aryeo sync for memberships.</p>';
  settings_errors('slm_subscriptions');

  if (is_wp_error($test)) {
    echo '<div class="notice notice-error"><p>' . esc_html($test->get_error_message()) . '</p></div>';
  } elseif (is_array($test) && $test !== []) {
    $count = is_array($test['data'] ?? null) ? count((array) $test['data']) : 0;
    echo '<div class="notice notice-success"><p>Stripe connection OK. Retrieved ' . esc_html((string) $count) . ' price record(s).</p></div>';
  }
  if (is_wp_error($square_test)) {
    echo '<div class="notice notice-error"><p>' . esc_html($square_test->get_error_message()) . '</p></div>';
  } elseif (is_array($square_test) && $square_test !== []) {
    $locations = is_array($square_test['locations'] ?? null) ? (array) $square_test['locations'] : [];
    echo '<div class="notice notice-success"><p>Square connection OK. Retrieved ' . esc_html((string) count($locations)) . ' location record(s).</p></div>';
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

  echo '<form method="post" style="margin-top:8px;">';
  wp_nonce_field('slm_subscriptions_test_square');
  submit_button('Test Square API Connection', 'secondary', 'slm_subscriptions_test_square');
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

function slm_subscriptions_change_requests_admin_url(array $args = []): string {
  return add_query_arg($args, admin_url('admin.php?page=slm-membership-ops'));
}

function slm_subscriptions_recovery_admin_url(array $args = []): string {
  return add_query_arg($args, admin_url('admin.php?page=slm-membership-recovery'));
}

function slm_subscriptions_member_check_admin_url(array $args = []): string {
  return add_query_arg($args, admin_url('admin.php?page=slm-membership-check'));
}

function slm_subscriptions_test_membership_admin_url(array $args = []): string {
  return add_query_arg($args, admin_url('admin.php?page=slm-membership-test'));
}

function slm_subscriptions_manual_square_sync_for_user(int $user_id, string $subscription_id, string $source = 'admin_recovery'): array {
  $user_id = max(0, $user_id);
  $subscription_id = trim($subscription_id);
  if ($user_id <= 0) return ['ok' => false, 'message' => 'User ID is required.'];
  if ($subscription_id === '') return ['ok' => false, 'message' => 'Square subscription ID is required.'];
  $sub = slm_subscriptions_square_retrieve_subscription($subscription_id);
  if (is_wp_error($sub)) {
    slm_subscriptions_log('manual_square_sync_failed', [
      'user_id' => $user_id,
      'subscription_id' => $subscription_id,
      'source' => $source,
      'error' => $sub->get_error_message(),
    ], 'error');
    return ['ok' => false, 'message' => $sub->get_error_message()];
  }
  if (!is_array($sub) || $sub === []) {
    slm_subscriptions_log('manual_square_sync_failed', [
      'user_id' => $user_id,
      'subscription_id' => $subscription_id,
      'source' => $source,
      'error' => 'Empty subscription payload.',
    ], 'error');
    return ['ok' => false, 'message' => 'Square subscription payload was empty.'];
  }
  $result = slm_subscriptions_square_sync_subscription_state($user_id, $sub, '');
  slm_subscriptions_log('manual_square_sync', [
    'user_id' => $user_id,
    'subscription_id' => $subscription_id,
    'source' => $source,
    'result' => $result,
  ], !empty($result['ok']) ? 'info' : 'error');
  return is_array($result) ? $result : ['ok' => false, 'message' => 'Unexpected sync response.'];
}

function slm_subscriptions_flush_membership_runtime_cache(int $user_id): void {
  $user_id = max(0, $user_id);
  if ($user_id <= 0) return;
  delete_transient('slm_square_pending_checkout_' . $user_id);
  if (function_exists('clean_user_cache')) clean_user_cache($user_id);
  if (function_exists('wp_cache_delete')) {
    wp_cache_delete($user_id, 'user_meta');
    wp_cache_delete($user_id, 'users');
  }
}

function slm_subscriptions_clear_test_membership_state_meta(int $user_id): void {
  $user_id = max(0, $user_id);
  if ($user_id <= 0) return;
  $keys = slm_subscriptions_meta_keys();

  slm_subscriptions_update_state($user_id, [
    'provider' => '',
    'plan' => '',
    'term_code' => '',
    'square_option_id' => '',
    'term_started_at' => 0,
    'term_ends_at' => 0,
    'stripe_customer_id' => '',
    'stripe_subscription_id' => '',
    'square_customer_id' => '',
    'square_subscription_id' => '',
    'status' => 'non_member',
    'current_period_end' => 0,
    'commitment_end' => 0,
  ]);

  delete_user_meta($user_id, $keys['last_credited_invoice_id']);
  delete_user_meta($user_id, $keys['membership_review_flag']);
  delete_user_meta($user_id, $keys['membership_review_note']);
  delete_user_meta($user_id, $keys['test_membership']);
  delete_user_meta($user_id, $keys['aryeo_affiliate_membership_id']);
}

function slm_subscriptions_clear_test_membership_seed_credits(int $user_id): array {
  if (!function_exists('slm_member_credits_ledger_table')) {
    return ['ok' => true, 'status' => 'ignored', 'message' => 'Member credits module unavailable.'];
  }
  global $wpdb;
  $table = slm_member_credits_ledger_table();
  $deleted = $wpdb->query($wpdb->prepare(
    "DELETE FROM {$table} WHERE user_id = %d AND source = %s",
    max(0, $user_id),
    'test_membership_seed'
  ));
  if ($deleted === false) {
    return ['ok' => false, 'message' => 'Unable to clear prior test credit rows.'];
  }
  return ['ok' => true, 'deleted' => (int) $deleted];
}

function slm_subscriptions_clear_all_member_credit_data_for_user(int $user_id): array {
  if (!function_exists('slm_member_credits_ledger_table')) {
    return ['ok' => true, 'status' => 'ignored', 'message' => 'Member credits module unavailable.'];
  }
  global $wpdb;
  $user_id = max(0, $user_id);
  if ($user_id <= 0) return ['ok' => false, 'message' => 'Invalid user ID.'];

  $ledger_table = slm_member_credits_ledger_table();
  $ledger_deleted = $wpdb->query($wpdb->prepare("DELETE FROM {$ledger_table} WHERE user_id = %d", $user_id));
  if ($ledger_deleted === false) {
    return ['ok' => false, 'message' => 'Unable to clear member credit ledger rows.'];
  }

  $flags_deleted = 0;
  if (function_exists('slm_member_credits_flags_table')) {
    $flags_table = slm_member_credits_flags_table();
    $flags_deleted = $wpdb->query($wpdb->prepare("DELETE FROM {$flags_table} WHERE user_id = %d", $user_id));
    if ($flags_deleted === false) {
      return ['ok' => false, 'message' => 'Credit ledger rows were cleared, but credit flags could not be cleared.'];
    }
  }

  return ['ok' => true, 'ledger_deleted' => (int) $ledger_deleted, 'flags_deleted' => (int) $flags_deleted];
}

function slm_subscriptions_seed_test_membership_credits(int $user_id, string $plan_slug, string $term_code, int $cycle_start_ts = 0): array {
  if (
    !function_exists('slm_member_credits_insert_ledger_txn') ||
    !function_exists('slm_member_credits_plan_entitlements') ||
    !function_exists('slm_member_credits_term_option') ||
    !function_exists('slm_member_credits_rollover_policy') ||
    !function_exists('slm_member_credits_time_to_gmt_string') ||
    !function_exists('slm_member_credits_expiry_for_grant') ||
    !function_exists('slm_member_credits_user_subscription_context')
  ) {
    return ['ok' => true, 'status' => 'ignored', 'message' => 'Member credits module unavailable.'];
  }

  $plan_slug = sanitize_key($plan_slug);
  $term_code = sanitize_key($term_code);
  $plan = slm_subscriptions_plan($plan_slug);
  if (!is_array($plan)) return ['ok' => false, 'message' => 'Invalid plan for credit seeding.'];

  $entitlements = slm_member_credits_plan_entitlements($plan);
  if ($entitlements === []) return ['ok' => true, 'status' => 'ignored', 'message' => 'No plan entitlements configured.'];

  $clear = slm_subscriptions_clear_test_membership_seed_credits($user_id);
  if (empty($clear['ok'])) return $clear;

  $term_option = slm_member_credits_term_option($plan, $term_code);
  $billing_interval = sanitize_key((string) ($term_option['billing_interval'] ?? ($plan['billing_interval'] ?? 'month')));
  if (!in_array($billing_interval, ['day', 'week', 'month', 'year'], true)) $billing_interval = 'month';
  $cycle_start_ts = $cycle_start_ts > 0 ? $cycle_start_ts : time();
  $cycle_end_ts = strtotime('+1 ' . $billing_interval, $cycle_start_ts);
  if (!$cycle_end_ts) $cycle_end_ts = $cycle_start_ts + MONTH_IN_SECONDS;
  $window = [
    'cycle_start_ts' => (int) $cycle_start_ts,
    'cycle_end_ts' => (int) $cycle_end_ts,
    'cycle_start_gmt' => slm_member_credits_time_to_gmt_string((int) $cycle_start_ts),
    'cycle_end_gmt' => slm_member_credits_time_to_gmt_string((int) $cycle_end_ts),
  ];
  $user_ctx = slm_member_credits_user_subscription_context($user_id);
  $rollover_policy = slm_member_credits_rollover_policy($plan);
  $seed_ref = 'test-membership:' . gmdate('YmdHis', (int) $cycle_start_ts);
  $subscription_external_id = 'test-membership:' . $user_id;
  $created_by = get_current_user_id();
  // Keep test grants visible for UI QA; the remove/reset test actions clear these rows.
  $test_seed_expiry = null;
  $granted = [];

  foreach ($entitlements as $credit_key => $qty) {
    $insert = slm_member_credits_insert_ledger_txn([
      'user_id' => $user_id,
      'subscription_provider' => 'square',
      'subscription_external_id' => $subscription_external_id,
      'plan_slug' => $plan_slug,
      'term_code' => $term_code,
      'credit_key' => $credit_key,
      'txn_type' => 'grant',
      'quantity_delta' => (int) $qty,
      'cycle_start_gmt' => $window['cycle_start_gmt'],
      'cycle_end_gmt' => $window['cycle_end_gmt'],
      'expires_at_gmt' => $test_seed_expiry,
      'source' => 'test_membership_seed',
      'source_ref' => $seed_ref,
      'dedupe_key' => 'test_seed:' . md5(implode('|', [$user_id, $plan_slug, $term_code, $credit_key, $window['cycle_start_gmt']])),
      'meta' => [
        'test_membership' => true,
        'intended_expires_at_gmt' => slm_member_credits_expiry_for_grant($rollover_policy, false, $user_ctx, (int) $window['cycle_end_ts']),
      ],
      'created_by_user_id' => $created_by > 0 ? $created_by : null,
    ]);
    if (is_wp_error($insert)) return ['ok' => false, 'message' => $insert->get_error_message()];
    $granted[] = ['credit_key' => $credit_key, 'qty' => (int) $qty];
  }

  if ($term_code === '12m') {
    $bonus_qty = max(0, (int) ($term_option['bonus_listing_shoot_per_cycle'] ?? 1));
    if ($bonus_qty > 0) {
      $insert = slm_member_credits_insert_ledger_txn([
        'user_id' => $user_id,
        'subscription_provider' => 'square',
        'subscription_external_id' => $subscription_external_id,
        'plan_slug' => $plan_slug,
        'term_code' => $term_code,
        'credit_key' => 'listing_shoot',
        'txn_type' => 'bonus_grant',
        'quantity_delta' => $bonus_qty,
        'cycle_start_gmt' => $window['cycle_start_gmt'],
        'cycle_end_gmt' => $window['cycle_end_gmt'],
        'expires_at_gmt' => $test_seed_expiry,
        'source' => 'test_membership_seed',
        'source_ref' => $seed_ref,
        'dedupe_key' => 'test_bonus:' . md5(implode('|', [$user_id, $plan_slug, $term_code, 'listing_shoot', $window['cycle_start_gmt']])),
        'meta' => [
          'test_membership' => true,
          'bonus' => true,
          'intended_expires_at_gmt' => slm_member_credits_expiry_for_grant($rollover_policy, true, $user_ctx, (int) $window['cycle_end_ts']),
        ],
        'created_by_user_id' => $created_by > 0 ? $created_by : null,
      ]);
      if (is_wp_error($insert)) return ['ok' => false, 'message' => $insert->get_error_message()];
      $granted[] = ['credit_key' => 'listing_shoot', 'qty' => $bonus_qty, 'bonus' => true];
    }
  }

  return ['ok' => true, 'status' => 'processed', 'granted' => $granted, 'seed_ref' => $seed_ref];
}

function slm_subscriptions_apply_test_membership(int $user_id, string $plan_slug, string $term_code, string $status, int $next_billing_ts = 0): array {
  $user_id = max(0, $user_id);
  if ($user_id <= 0) return ['ok' => false, 'message' => 'Select a user.'];
  $keys = slm_subscriptions_meta_keys();

  $plan_slug = sanitize_key($plan_slug);
  $plan = slm_subscriptions_plan($plan_slug);
  if (!$plan || !is_array($plan)) return ['ok' => false, 'message' => 'Invalid plan slug.'];

  $expected_terms = slm_subscriptions_square_expected_term_codes_for_plan($plan_slug);
  $term_code = sanitize_key($term_code);
  if ($term_code === '') $term_code = slm_subscriptions_default_term_for_plan($plan_slug);
  if ($expected_terms !== [] && !in_array($term_code, $expected_terms, true)) {
    return ['ok' => false, 'message' => 'Invalid term for selected plan.'];
  }

  $status = sanitize_key($status);
  if (!in_array($status, ['active', 'inactive', 'canceled', 'past_due'], true)) {
    $status = 'inactive';
  }
  if ($status === 'inactive') $status = 'non_member';

  $term_option = slm_subscriptions_plan_term_option($plan_slug, $term_code);
  $anchor_ts = time();
  $commitment_months = max(0, (int) ($term_option['commitment_months'] ?? ($plan['minimum_term_months'] ?? 0)));
  $term_ends_at = ($term_code === 'm2m' && max(0, (int) ($term_option['billing_periods'] ?? 0)) === 0)
    ? 0
    : slm_subscriptions_term_end_from_months($anchor_ts, $commitment_months);
  $commitment_end = $commitment_months > 0 ? slm_subscriptions_term_end_from_months($anchor_ts, $commitment_months) : 0;
  $current_period_end = $next_billing_ts > 0 ? $next_billing_ts : ($status === 'active' ? strtotime('+1 month', $anchor_ts) : 0);
  // Test memberships should be deterministic for UI QA. Clear prior member credit data
  // before reseeding so old grants/debits don't net balances back to zero.
  $credit_reset_result = slm_subscriptions_clear_all_member_credit_data_for_user($user_id);

  slm_subscriptions_update_state($user_id, [
    'provider' => 'square',
    'plan' => $plan_slug,
    'term_code' => $term_code,
    'stripe_customer_id' => '',
    'stripe_subscription_id' => '',
    'square_customer_id' => '',
    'square_subscription_id' => '',
    'square_option_id' => trim((string) ($term_option['checkout_option_id'] ?? '')),
    'term_started_at' => $anchor_ts,
    'term_ends_at' => $term_ends_at ?: 0,
    'status' => $status,
    'current_period_end' => $current_period_end ?: 0,
    'commitment_end' => $commitment_end ?: 0,
  ]);
  delete_user_meta($user_id, $keys['last_credited_invoice_id']);
  delete_user_meta($user_id, $keys['aryeo_affiliate_membership_id']);
  slm_subscriptions_set_test_membership_flag($user_id, true);
  slm_subscriptions_clear_user_membership_review($user_id);
  $credit_seed_result = $status === 'active'
    ? slm_subscriptions_seed_test_membership_credits($user_id, $plan_slug, $term_code, $anchor_ts)
    : slm_subscriptions_clear_test_membership_seed_credits($user_id);
  $credit_snapshot_after_seed = null;
  $recent_credit_ledger_after_seed = null;
  $expected_seed_qty = 0;
  $seeded_visible_qty = 0;
  if ($status === 'active' && function_exists('slm_member_credits_balance_snapshot')) {
    $credit_snapshot_after_seed = slm_member_credits_balance_snapshot($user_id);
    if (function_exists('slm_member_credits_recent_ledger')) {
      $recent_credit_ledger_after_seed = slm_member_credits_recent_ledger($user_id, 20);
    }
    $plan_entitlements_for_check = function_exists('slm_member_credits_plan_entitlements') ? slm_member_credits_plan_entitlements($plan) : [];
    if (is_array($plan_entitlements_for_check)) {
      foreach ($plan_entitlements_for_check as $credit_key => $qty) {
        $qty = max(0, (int) $qty);
        $expected_seed_qty += $qty;
        $seeded_visible_qty += (int) (($credit_snapshot_after_seed['by_key'][$credit_key] ?? 0));
      }
    }
  }
  slm_subscriptions_log('test_membership_set', [
    'user_id' => $user_id,
    'plan' => $plan_slug,
    'term' => $term_code,
    'status' => $status,
    'next_billing' => $current_period_end ?: 0,
    'credit_reset' => $credit_reset_result,
    'credit_seed' => $credit_seed_result,
    'credit_snapshot_after_seed' => $credit_snapshot_after_seed,
    'recent_credit_ledger_after_seed' => $recent_credit_ledger_after_seed,
  ], 'info');
  slm_subscriptions_flush_membership_runtime_cache($user_id);

  $debug = [
    'credit_reset' => $credit_reset_result,
    'credit_seed' => $credit_seed_result,
    'credit_snapshot_after_seed' => $credit_snapshot_after_seed,
    'recent_credit_ledger_after_seed' => $recent_credit_ledger_after_seed,
    'expected_seed_qty' => $expected_seed_qty,
    'seeded_visible_qty' => $seeded_visible_qty,
  ];

  if (is_array($credit_reset_result) && empty($credit_reset_result['ok'])) {
    return ['ok' => false, 'message' => 'Test membership applied, but previous member credit data could not be cleared.', 'debug' => $debug];
  }
  if (is_array($credit_seed_result) && empty($credit_seed_result['ok'])) {
    return ['ok' => false, 'message' => 'Test membership applied, but credit balances could not be seeded.', 'debug' => $debug];
  }
  if ($status === 'active' && $expected_seed_qty > 0 && $seeded_visible_qty <= 0) {
    return ['ok' => false, 'message' => 'Test membership applied, but no credit balances were visible after seeding. Check member credit ledger tables and DB permissions.', 'debug' => $debug];
  }
  return ['ok' => true, 'message' => 'Test membership applied and credits were seeded from the plan.', 'debug' => $debug];
}

function slm_subscriptions_clear_test_membership(int $user_id): array {
  $user_id = max(0, $user_id);
  if ($user_id <= 0) return ['ok' => false, 'message' => 'Select a user.'];
  $seed_clear = slm_subscriptions_clear_test_membership_seed_credits($user_id);
  slm_subscriptions_set_test_membership_flag($user_id, false);
  slm_subscriptions_flush_membership_runtime_cache($user_id);
  slm_subscriptions_log('test_membership_flag_cleared', ['user_id' => $user_id, 'credit_seed_clear' => $seed_clear], 'info');
  if (is_array($seed_clear) && empty($seed_clear['ok'])) {
    return ['ok' => true, 'message' => 'Test membership flag cleared (credit seed rows could not be removed).'];
  }
  return ['ok' => true, 'message' => 'Test membership flag cleared and test credit seed rows removed.'];
}

function slm_subscriptions_remove_test_membership(int $user_id): array {
  $user_id = max(0, $user_id);
  if ($user_id <= 0) return ['ok' => false, 'message' => 'Select a user.'];

  $keys = slm_subscriptions_meta_keys();
  $is_test = !empty(get_user_meta($user_id, $keys['test_membership'], true));
  if (!$is_test) {
    return ['ok' => false, 'message' => 'This user is not marked as a TEST membership.'];
  }

  slm_subscriptions_clear_test_membership_state_meta($user_id);
  slm_subscriptions_clear_user_membership_review($user_id);
  $credit_clear = slm_subscriptions_clear_all_member_credit_data_for_user($user_id);
  slm_subscriptions_flush_membership_runtime_cache($user_id);

  slm_subscriptions_log('test_membership_removed', [
    'user_id' => $user_id,
    'credit_clear' => $credit_clear,
  ], 'info');

  if (is_array($credit_clear) && empty($credit_clear['ok'])) {
    return ['ok' => false, 'message' => 'Test membership meta was reset, but member credit data could not be fully cleared.'];
  }
  return ['ok' => true, 'message' => 'Test membership removed, membership meta cleared, and member credit data reset.'];
}

function slm_subscriptions_admin_ops_nav(string $active_slug): void {
  $tabs = [
    'slm-membership-ops' => ['label' => 'Change Requests', 'url' => slm_subscriptions_change_requests_admin_url()],
    'slm-membership-check' => ['label' => 'Member Check', 'url' => slm_subscriptions_member_check_admin_url()],
    'slm-membership-recovery' => ['label' => 'Membership Recovery', 'url' => slm_subscriptions_recovery_admin_url()],
    'slm-membership-test' => ['label' => 'Set Test Membership', 'url' => slm_subscriptions_test_membership_admin_url()],
  ];
  echo '<h2 class="nav-tab-wrapper" style="margin-bottom:16px;">';
  foreach ($tabs as $slug => $tab) {
    echo '<a class="nav-tab ' . ($slug === $active_slug ? 'nav-tab-active' : '') . '" href="' . esc_url((string) $tab['url']) . '">' . esc_html((string) $tab['label']) . '</a>';
  }
  echo '</h2>';
}

function slm_subscriptions_admin_ops_notice(array $notice): void {
  $type = sanitize_key((string) ($notice['type'] ?? 'info'));
  $msg = (string) ($notice['message'] ?? '');
  if ($msg === '') return;
  $class = 'notice-info';
  if ($type === 'success') $class = 'notice-success';
  if ($type === 'error') $class = 'notice-error';
  echo '<div class="notice ' . esc_attr($class) . '"><p>' . esc_html($msg) . '</p></div>';
}

function slm_subscriptions_admin_user_membership_snapshot_row(int $user_id): string {
  $summary = slm_get_user_subscription_summary($user_id);
  if (!is_array($summary)) return 'No membership summary available.';
  return implode(' | ', [
    'Plan: ' . (string) ($summary['plan_label'] ?? 'N/A'),
    'Status: ' . (string) ($summary['status_label'] ?? 'N/A'),
    'Term: ' . (string) ($summary['term_label'] ?? 'N/A'),
    'Next Billing: ' . (string) ($summary['current_period_end_label'] ?? 'N/A'),
  ]);
}

function slm_subscriptions_render_change_requests_admin_page(): void {
  if (!current_user_can('manage_options')) return;
  $notice = [];

  if (isset($_POST['slm_change_request_admin_action']) && sanitize_key((string) $_POST['slm_change_request_admin_action']) === 'update_request') {
    $request_id = max(0, (int) ($_POST['request_id'] ?? 0));
    $nonce = (string) ($_POST['slm_change_request_update_nonce'] ?? '');
    if ($request_id <= 0 || !wp_verify_nonce($nonce, 'slm_change_request_update_' . $request_id)) {
      $notice = ['type' => 'error', 'message' => 'Invalid request update submission.'];
    } else {
      $ok = slm_subscriptions_update_change_request($request_id, [
        'status' => (string) ($_POST['request_status'] ?? ''),
        'admin_notes' => (string) wp_unslash($_POST['admin_notes'] ?? ''),
        'square_subscription_id' => (string) ($_POST['square_subscription_id'] ?? ''),
      ]);
      $notice = $ok
        ? ['type' => 'success', 'message' => 'Change request updated.']
        : ['type' => 'error', 'message' => 'Unable to update change request.'];
      if ($ok) {
        slm_subscriptions_log('membership_change_request_updated', [
          'request_id' => $request_id,
          'status' => sanitize_key((string) ($_POST['request_status'] ?? '')),
        ], 'info');
      }
    }
  }

  $status_filter = sanitize_key((string) ($_GET['status'] ?? ''));
  if ($status_filter === '') $status_filter = 'all';
  $search = trim((string) ($_GET['s'] ?? ''));
  $request_id = max(0, (int) ($_GET['request_id'] ?? 0));
  $detail = $request_id > 0 ? slm_subscriptions_get_change_request($request_id) : null;

  echo '<div class="wrap">';
  echo '<h1>Membership Change Requests</h1>';
  slm_subscriptions_admin_ops_nav('slm-membership-ops');
  if ($notice !== []) slm_subscriptions_admin_ops_notice($notice);

  if (is_array($detail)) {
    $user = get_user_by('id', (int) ($detail['user_id'] ?? 0));
    $user_email = $user instanceof WP_User ? (string) $user->user_email : '';
    $user_name = $user instanceof WP_User ? (string) $user->display_name : '';
    $keys = slm_subscriptions_meta_keys();
    $square_customer_id = $user instanceof WP_User ? trim((string) get_user_meta((int) $user->ID, $keys['square_customer_id'], true)) : '';
    echo '<p><a href="' . esc_url(slm_subscriptions_change_requests_admin_url()) . '">&larr; Back to all requests</a></p>';
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th>Request ID</th><td>#' . esc_html((string) (int) $detail['id']) . '</td></tr>';
    echo '<tr><th>User</th><td>' . esc_html($user_name !== '' ? $user_name : 'User #' . (int) ($detail['user_id'] ?? 0));
    if ($user_email !== '') echo ' &lt;' . esc_html($user_email) . '&gt;';
    echo '</td></tr>';
    echo '<tr><th>Current</th><td>' . esc_html((string) ($detail['current_plan_slug'] ?? '')) . ' / ' . esc_html(strtoupper((string) ($detail['current_term_code'] ?? ''))) . ' (' . esc_html((string) ($detail['current_status'] ?? '')) . ')</td></tr>';
    echo '<tr><th>Desired</th><td>' . esc_html((string) ($detail['desired_plan_slug'] ?? '')) . ' / ' . esc_html(strtoupper((string) ($detail['desired_term_code'] ?? ''))) . '</td></tr>';
    echo '<tr><th>User Notes</th><td><pre style="white-space:pre-wrap;margin:0;">' . esc_html((string) ($detail['request_notes'] ?? '')) . '</pre></td></tr>';
    echo '<tr><th>Square Customer ID</th><td>' . esc_html($square_customer_id !== '' ? $square_customer_id : 'Not stored');
    if ($square_customer_id !== '') {
      echo ' <a href="' . esc_url('https://app.squareup.com/dashboard/customers/directory') . '" target="_blank" rel="noopener">(Open Square Customers)</a>';
    }
    echo '</td></tr>';
    echo '<tr><th>Created</th><td>' . esc_html((string) ($detail['created_at'] ?? '')) . '</td></tr>';
    echo '<tr><th>Updated</th><td>' . esc_html((string) ($detail['updated_at'] ?? '')) . '</td></tr>';
    echo '</tbody></table>';

    echo '<form method="post" style="margin-top:16px;">';
    wp_nonce_field('slm_change_request_update_' . (int) $detail['id'], 'slm_change_request_update_nonce');
    echo '<input type="hidden" name="slm_change_request_admin_action" value="update_request" />';
    echo '<input type="hidden" name="request_id" value="' . esc_attr((string) (int) $detail['id']) . '" />';
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th><label for="request_status">Status</label></th><td><select id="request_status" name="request_status">';
    foreach (slm_subscriptions_change_request_statuses() as $status_key => $status_label) {
      echo '<option value="' . esc_attr($status_key) . '"' . selected((string) ($detail['status'] ?? 'new'), $status_key, false) . '>' . esc_html($status_label) . '</option>';
    }
    echo '</select></td></tr>';
    echo '<tr><th><label for="square_subscription_id">Square Subscription ID</label></th><td><input type="text" id="square_subscription_id" name="square_subscription_id" class="regular-text code" value="' . esc_attr((string) ($detail['square_subscription_id'] ?? '')) . '" /></td></tr>';
    echo '<tr><th><label for="admin_notes">Admin Notes</label></th><td><textarea id="admin_notes" name="admin_notes" class="large-text code" rows="6">' . esc_textarea((string) ($detail['admin_notes'] ?? '')) . '</textarea></td></tr>';
    echo '</tbody></table>';
    submit_button('Update Request');
    echo '</form>';

    if ($user instanceof WP_User) {
      echo '<h2>User Membership Snapshot</h2>';
      echo '<p>' . esc_html(slm_subscriptions_admin_user_membership_snapshot_row((int) $user->ID)) . '</p>';
      echo '<p><a class="button" href="' . esc_url(slm_subscriptions_recovery_admin_url(['user_id' => (int) $user->ID])) . '">Open Recovery Tool for This User</a></p>';
    }

    echo '</div>';
    return;
  }

  echo '<form method="get" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;margin-bottom:12px;">';
  echo '<input type="hidden" name="page" value="slm-membership-ops" />';
  echo '<div><label for="slm_change_request_status_filter">Status</label><br><select id="slm_change_request_status_filter" name="status">';
  echo '<option value="all"' . selected($status_filter, 'all', false) . '>All</option>';
  foreach (slm_subscriptions_change_request_statuses() as $status_key => $status_label) {
    echo '<option value="' . esc_attr($status_key) . '"' . selected($status_filter, $status_key, false) . '>' . esc_html($status_label) . '</option>';
  }
  echo '</select></div>';
  echo '<div><label for="slm_change_request_search">Search</label><br><input type="search" id="slm_change_request_search" name="s" value="' . esc_attr($search) . '" placeholder="User email, plan, notes..." /></div>';
  submit_button('Filter', 'secondary', '', false);
  echo '</form>';

  $rows = slm_subscriptions_list_change_requests([
    'status' => $status_filter,
    'search' => $search,
    'limit' => 200,
    'offset' => 0,
  ]);
  if ($search !== '' && $rows !== []) {
    $search_lc = strtolower($search);
    $rows = array_values(array_filter($rows, function ($row) use ($search_lc) {
      if (!is_array($row)) return false;
      $haystack = [
        (string) ($row['current_plan_slug'] ?? ''),
        (string) ($row['desired_plan_slug'] ?? ''),
        (string) ($row['request_notes'] ?? ''),
        (string) ($row['admin_notes'] ?? ''),
        (string) ($row['square_subscription_id'] ?? ''),
        (string) ($row['id'] ?? ''),
      ];
      $uid = (int) ($row['user_id'] ?? 0);
      if ($uid > 0) {
        $u = get_user_by('id', $uid);
        if ($u instanceof WP_User) {
          $haystack[] = (string) $u->user_email;
          $haystack[] = (string) $u->display_name;
          $haystack[] = (string) $u->user_login;
        }
      }
      foreach ($haystack as $part) {
        if (strpos(strtolower((string) $part), $search_lc) !== false) return true;
      }
      return false;
    }));
  }
  echo '<table class="widefat striped"><thead><tr><th>ID</th><th>User</th><th>Current</th><th>Desired</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
  if ($rows === []) {
    echo '<tr><td colspan="7">No change requests found.</td></tr>';
  } else {
    foreach ($rows as $row) {
      if (!is_array($row)) continue;
      $uid = (int) ($row['user_id'] ?? 0);
      $user = $uid > 0 ? get_user_by('id', $uid) : null;
      $user_label = $uid > 0 ? ('User #' . $uid) : 'Unknown User';
      if ($user instanceof WP_User) {
        $user_label = $user->display_name . ' <' . $user->user_email . '>';
      }
      echo '<tr>';
      echo '<td>#' . esc_html((string) (int) ($row['id'] ?? 0)) . '</td>';
      echo '<td>' . esc_html($user_label) . '</td>';
      echo '<td>' . esc_html((string) ($row['current_plan_slug'] ?? '')) . ' / ' . esc_html(strtoupper((string) ($row['current_term_code'] ?? ''))) . '</td>';
      echo '<td>' . esc_html((string) ($row['desired_plan_slug'] ?? '')) . ' / ' . esc_html(strtoupper((string) ($row['desired_term_code'] ?? ''))) . '</td>';
      $status_key = slm_subscriptions_change_request_normalize_status((string) ($row['status'] ?? 'new'));
      echo '<td>' . esc_html((string) (slm_subscriptions_change_request_statuses()[$status_key] ?? $status_key)) . '</td>';
      echo '<td>' . esc_html((string) ($row['created_at'] ?? '')) . '</td>';
      echo '<td><a class="button button-small" href="' . esc_url(slm_subscriptions_change_requests_admin_url(['request_id' => (int) ($row['id'] ?? 0)])) . '">Open</a></td>';
      echo '</tr>';
    }
  }
  echo '</tbody></table>';
  echo '</div>';
}

function slm_subscriptions_render_recovery_admin_page(): void {
  if (!current_user_can('manage_options')) return;
  $notice = [];
  $found_user = null;

  if (isset($_POST['slm_membership_recovery_action'])) {
    $action = sanitize_key((string) $_POST['slm_membership_recovery_action']);
    $nonce = (string) ($_POST['slm_membership_recovery_nonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'slm_membership_recovery')) {
      $notice = ['type' => 'error', 'message' => 'Invalid recovery action.'];
    } else {
      $user_id = max(0, (int) ($_POST['user_id'] ?? 0));
      if ($action === 'sync_square') {
        $keys = slm_subscriptions_meta_keys();
        $subscription_id = trim((string) ($_POST['square_subscription_id'] ?? ''));
        if ($subscription_id === '' && $user_id > 0) {
          $subscription_id = trim((string) get_user_meta($user_id, $keys['square_subscription_id'], true));
        }
        $result = slm_subscriptions_manual_square_sync_for_user($user_id, $subscription_id, 'admin_recovery_page');
        $notice = [
          'type' => !empty($result['ok']) ? 'success' : 'error',
          'message' => (string) ($result['message'] ?? (!empty($result['ok']) ? 'Square sync completed.' : 'Square sync failed.')),
        ];
      }
    }
  }

  $search_email = sanitize_email((string) ($_REQUEST['search_email'] ?? ''));
  $user_id_from_request = max(0, (int) ($_REQUEST['user_id'] ?? 0));
  if ($user_id_from_request > 0) {
    $u = get_user_by('id', $user_id_from_request);
    if ($u instanceof WP_User) $found_user = $u;
  } elseif ($search_email !== '') {
    $u = get_user_by('email', $search_email);
    if ($u instanceof WP_User) $found_user = $u;
    if (!$u instanceof WP_User) $notice = ['type' => 'error', 'message' => 'No WordPress user found for that email.'];
  }

  echo '<div class="wrap">';
  echo '<h1>Membership Recovery</h1>';
  slm_subscriptions_admin_ops_nav('slm-membership-recovery');
  echo '<p>Use this tool if Square payment succeeded but the WordPress membership did not update. Search the member, paste the Square subscription ID, and run a manual sync from Square.</p>';
  if ($notice !== []) slm_subscriptions_admin_ops_notice($notice);

  echo '<form method="get" style="margin:12px 0 18px;">';
  echo '<input type="hidden" name="page" value="slm-membership-recovery" />';
  echo '<label for="slm_recovery_search_email"><strong>Search user by email</strong></label><br />';
  echo '<input type="email" class="regular-text" id="slm_recovery_search_email" name="search_email" value="' . esc_attr($search_email) . '" placeholder="member@example.com" /> ';
  submit_button('Find User', 'secondary', '', false);
  echo '</form>';

  if ($found_user instanceof WP_User) {
    $uid = (int) $found_user->ID;
    $keys = slm_subscriptions_meta_keys();
    $square_sub_id = trim((string) get_user_meta($uid, $keys['square_subscription_id'], true));
    $square_customer_id = trim((string) get_user_meta($uid, $keys['square_customer_id'], true));
    $issues = slm_subscriptions_user_membership_state_issues($uid);

    echo '<h2>Member</h2>';
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th>User</th><td>' . esc_html($found_user->display_name) . ' &lt;' . esc_html((string) $found_user->user_email) . '&gt; (ID ' . esc_html((string) $uid) . ')</td></tr>';
    echo '<tr><th>Membership Snapshot</th><td>' . esc_html(slm_subscriptions_admin_user_membership_snapshot_row($uid)) . '</td></tr>';
    echo '<tr><th>Square Customer ID</th><td>' . esc_html($square_customer_id !== '' ? $square_customer_id : 'Not stored') . '</td></tr>';
    echo '<tr><th>Stored Square Subscription ID</th><td>' . esc_html($square_sub_id !== '' ? $square_sub_id : 'Not stored') . '</td></tr>';
    echo '<tr><th>State Issues</th><td>' . (!empty($issues['has_issue']) ? esc_html(implode(' | ', (array) ($issues['messages'] ?? []))) : 'None detected') . '</td></tr>';
    echo '</tbody></table>';

    echo '<form method="post" style="margin-top:12px;">';
    wp_nonce_field('slm_membership_recovery', 'slm_membership_recovery_nonce');
    echo '<input type="hidden" name="slm_membership_recovery_action" value="sync_square" />';
    echo '<input type="hidden" name="user_id" value="' . esc_attr((string) $uid) . '" />';
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th><label for="recovery_square_subscription_id">Square Subscription ID</label></th><td><input type="text" id="recovery_square_subscription_id" name="square_subscription_id" class="regular-text code" value="' . esc_attr($square_sub_id) . '" placeholder="subsq_..." /><p class="description">Paste a subscription ID from Square. Leave blank to use the stored ID above.</p></td></tr>';
    echo '</tbody></table>';
    submit_button('Sync from Square');
    echo '</form>';
  }

  echo '</div>';
}

function slm_subscriptions_render_member_check_admin_page(): void {
  if (!current_user_can('manage_options')) return;
  $notice = [];
  $found_user = null;

  $search_email = sanitize_email((string) ($_REQUEST['search_email'] ?? ''));
  $user_id_from_request = max(0, (int) ($_REQUEST['user_id'] ?? 0));

  if ($user_id_from_request > 0) {
    $u = get_user_by('id', $user_id_from_request);
    if ($u instanceof WP_User) {
      $found_user = $u;
    } else {
      $notice = ['type' => 'error', 'message' => 'No WordPress user found for that user ID.'];
    }
  } elseif ($search_email !== '') {
    $u = get_user_by('email', $search_email);
    if ($u instanceof WP_User) {
      $found_user = $u;
    } else {
      $notice = ['type' => 'error', 'message' => 'No WordPress user found for that email.'];
    }
  }

  echo '<div class="wrap">';
  echo '<h1>Member Check</h1>';
  slm_subscriptions_admin_ops_nav('slm-membership-check');
  echo '<p>Search a member and review the current WordPress membership state, Square IDs, plan entitlements, and current credit balances.</p>';
  if ($notice !== []) slm_subscriptions_admin_ops_notice($notice);

  echo '<form method="get" style="margin:12px 0 18px; display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap;">';
  echo '<input type="hidden" name="page" value="slm-membership-check" />';
  echo '<div><label for="slm_member_check_search_email"><strong>Search by email</strong></label><br />';
  echo '<input type="email" class="regular-text" id="slm_member_check_search_email" name="search_email" value="' . esc_attr($search_email) . '" placeholder="member@example.com" /></div>';
  echo '<div><label for="slm_member_check_user_id"><strong>or User ID</strong></label><br />';
  echo '<input type="number" min="1" step="1" class="small-text" id="slm_member_check_user_id" name="user_id" value="' . esc_attr($user_id_from_request > 0 ? (string) $user_id_from_request : '') . '" /></div>';
  submit_button('Check Member', 'secondary', '', false);
  echo '</form>';

  if ($found_user instanceof WP_User) {
    $uid = (int) $found_user->ID;
    $summary = slm_get_user_subscription_summary($uid);
    $keys = slm_subscriptions_meta_keys();
    $state_issues = slm_subscriptions_user_membership_state_issues($uid);
    $square_sub_id = trim((string) get_user_meta($uid, $keys['square_subscription_id'], true));
    $square_customer_id = trim((string) get_user_meta($uid, $keys['square_customer_id'], true));
    $provider = sanitize_key((string) get_user_meta($uid, $keys['provider'], true));
    $status = sanitize_key((string) get_user_meta($uid, $keys['status'], true));
    $plan_slug = sanitize_key((string) get_user_meta($uid, $keys['plan'], true));
    $term_code = sanitize_key((string) get_user_meta($uid, $keys['term_code'], true));
    $is_test = !empty(get_user_meta($uid, $keys['test_membership'], true));
    $credit_snapshot = function_exists('slm_member_credits_balance_snapshot') ? slm_member_credits_balance_snapshot($uid) : null;
    $entitlements = is_array($summary) && is_array($summary['entitlements'] ?? null) ? (array) $summary['entitlements'] : [];
    $plan_features = is_array($summary) && is_array($summary['plan_features'] ?? null) ? (array) $summary['plan_features'] : [];

    echo '<h2>Member Snapshot</h2>';
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th>User</th><td>' . esc_html($found_user->display_name) . ' &lt;' . esc_html((string) $found_user->user_email) . '&gt; (ID ' . esc_html((string) $uid) . ')</td></tr>';
    echo '<tr><th>Membership Snapshot</th><td>' . esc_html(slm_subscriptions_admin_user_membership_snapshot_row($uid)) . ($is_test ? ' <span style="display:inline-block;margin-left:8px;padding:2px 8px;border-radius:999px;background:#fff0d8;color:#8a4b00;font-weight:700;">TEST</span>' : '') . '</td></tr>';
    echo '<tr><th>Provider</th><td>' . esc_html($provider !== '' ? $provider : 'Not set') . '</td></tr>';
    echo '<tr><th>Status (raw)</th><td>' . esc_html($status !== '' ? $status : 'Not set') . '</td></tr>';
    echo '<tr><th>Plan / Term (raw)</th><td>' . esc_html(($plan_slug !== '' ? $plan_slug : 'Not set') . ' / ' . ($term_code !== '' ? $term_code : 'Not set')) . '</td></tr>';
    echo '<tr><th>Square Customer ID</th><td>' . esc_html($square_customer_id !== '' ? $square_customer_id : 'Not stored') . '</td></tr>';
    echo '<tr><th>Square Subscription ID</th><td>' . esc_html($square_sub_id !== '' ? $square_sub_id : 'Not stored') . '</td></tr>';
    echo '<tr><th>State Issues</th><td>' . (!empty($state_issues['has_issue']) ? esc_html(implode(' | ', (array) ($state_issues['messages'] ?? []))) : 'None detected') . '</td></tr>';
    echo '</tbody></table>';

    echo '<p style="margin:12px 0 0; display:flex; gap:8px; flex-wrap:wrap;">';
    echo '<a class="button" href="' . esc_url(slm_subscriptions_recovery_admin_url(['user_id' => $uid])) . '">Open Recovery Tool</a>';
    echo '<a class="button" href="' . esc_url(slm_subscriptions_test_membership_admin_url(['user_id' => $uid])) . '">Open Set Test Membership</a>';
    echo '</p>';

    echo '<h2 style="margin-top:20px;">Plan Highlights</h2>';
    if ($plan_features === []) {
      echo '<p>Plan highlights (sessions/services) are not configured for this plan.</p>';
    } else {
      echo '<ul style="margin:0 0 0 18px;">';
      foreach ($plan_features as $feature) {
        $feature = trim((string) $feature);
        if ($feature === '') continue;
        echo '<li>' . esc_html($feature) . '</li>';
      }
      echo '</ul>';
    }

    echo '<h2 style="margin-top:20px;">Included Credits Per Cycle</h2>';
    if ($entitlements === []) {
      echo '<p>No entitlements found for the current plan.</p>';
    } else {
      echo '<table class="widefat striped" style="max-width:700px;"><thead><tr><th>Benefit</th><th>Qty</th></tr></thead><tbody>';
      foreach ($entitlements as $entitlement) {
        if (!is_array($entitlement)) continue;
        echo '<tr>';
        echo '<td>' . esc_html((string) ($entitlement['label'] ?? $entitlement['key'] ?? 'Benefit')) . '</td>';
        echo '<td>' . esc_html((string) (int) ($entitlement['qty'] ?? 0)) . '</td>';
        echo '</tr>';
      }
      echo '</tbody></table>';
    }

    echo '<h2 style="margin-top:20px;">Credit Balances</h2>';
    if (!is_array($credit_snapshot) || empty($credit_snapshot['balances'])) {
      echo '<p>No credit snapshot available.</p>';
    } else {
      echo '<table class="widefat striped" style="max-width:700px;"><thead><tr><th>Credit</th><th>Remaining</th></tr></thead><tbody>';
      foreach ((array) $credit_snapshot['balances'] as $balance) {
        if (!is_array($balance)) continue;
        echo '<tr>';
        echo '<td>' . esc_html((string) ($balance['label'] ?? 'Credit')) . '</td>';
        echo '<td>' . esc_html((string) (int) ($balance['qty'] ?? 0)) . '</td>';
        echo '</tr>';
      }
      echo '</tbody></table>';
    }
  }

  echo '</div>';
}

function slm_subscriptions_render_test_membership_admin_page(): void {
  if (!current_user_can('manage_options')) return;
  $notice = [];
  $debug_payload = null;
  $selected_user_id = max(0, (int) ($_REQUEST['user_id'] ?? 0));

  if (isset($_POST['slm_test_membership_action'])) {
    $action = sanitize_key((string) $_POST['slm_test_membership_action']);
    $nonce = (string) ($_POST['slm_test_membership_nonce'] ?? '');
    if (!wp_verify_nonce($nonce, 'slm_test_membership_admin')) {
      $notice = ['type' => 'error', 'message' => 'Invalid test membership action.'];
    } else {
      $selected_user_id = max(0, (int) ($_POST['user_id'] ?? 0));
      if ($action === 'apply') {
        $next_billing_raw = trim((string) ($_POST['next_billing_date'] ?? ''));
        $next_billing_ts = 0;
        if ($next_billing_raw !== '') {
          $parsed = strtotime($next_billing_raw . ' 00:00:00');
          if ($parsed) $next_billing_ts = (int) $parsed;
        }
        $result = slm_subscriptions_apply_test_membership(
          $selected_user_id,
          (string) ($_POST['plan_slug'] ?? ''),
          (string) ($_POST['term_code'] ?? ''),
          (string) ($_POST['membership_status'] ?? ''),
          $next_billing_ts
        );
        $debug_payload = is_array($result['debug'] ?? null) ? $result['debug'] : null;
        $notice = ['type' => !empty($result['ok']) ? 'success' : 'error', 'message' => (string) ($result['message'] ?? 'Completed.')];
      } elseif ($action === 'clear_test_flag') {
        $result = slm_subscriptions_clear_test_membership($selected_user_id);
        $debug_payload = is_array($result['debug'] ?? null) ? $result['debug'] : null;
        $notice = ['type' => !empty($result['ok']) ? 'success' : 'error', 'message' => (string) ($result['message'] ?? 'Completed.')];
      } elseif ($action === 'remove_test_membership') {
        $result = slm_subscriptions_remove_test_membership($selected_user_id);
        $debug_payload = is_array($result['debug'] ?? null) ? $result['debug'] : null;
        $notice = ['type' => !empty($result['ok']) ? 'success' : 'error', 'message' => (string) ($result['message'] ?? 'Completed.')];
      }
    }
  }

  $selected_user = $selected_user_id > 0 ? get_user_by('id', $selected_user_id) : null;
  $plan_choices = slm_subscriptions_membership_change_request_plan_choices();
  $selected_plan = sanitize_key((string) ($_REQUEST['plan_slug'] ?? key($plan_choices)));
  if (!isset($plan_choices[$selected_plan])) $selected_plan = (string) key($plan_choices);
  $selected_term = sanitize_key((string) ($_REQUEST['term_code'] ?? slm_subscriptions_default_term_for_plan($selected_plan)));
  $allowed_terms = slm_subscriptions_square_expected_term_codes_for_plan($selected_plan);
  if ($allowed_terms !== [] && !in_array($selected_term, $allowed_terms, true)) $selected_term = slm_subscriptions_default_term_for_plan($selected_plan);

  echo '<div class="wrap">';
  echo '<h1>Set Test Membership</h1>';
  slm_subscriptions_admin_ops_nav('slm-membership-test');
  echo '<p><strong>Admin-only testing tool.</strong> This writes WordPress membership meta directly for UI previews without charging a card. It sets a TEST flag so the portal can display a test badge.</p>';
  if ($notice !== []) slm_subscriptions_admin_ops_notice($notice);
  if (is_array($debug_payload) && $debug_payload !== []) {
    echo '<details style="margin:8px 0 16px;"><summary><strong>Last Test Membership Debug</strong></summary>';
    echo '<pre style="background:#fff;border:1px solid #ccd0d4;padding:10px;max-height:320px;overflow:auto;">' . esc_html(wp_json_encode($debug_payload, JSON_PRETTY_PRINT)) . '</pre>';
    echo '</details>';
  }

  echo '<form method="post">';
  wp_nonce_field('slm_test_membership_admin', 'slm_test_membership_nonce');
  echo '<table class="form-table" role="presentation"><tbody>';
  echo '<tr><th><label for="slm_test_user_id">User</label></th><td>';
  wp_dropdown_users([
    'name' => 'user_id',
    'id' => 'slm_test_user_id',
    'selected' => $selected_user_id,
    'show_option_none' => 'Select a user',
    'option_none_value' => '0',
  ]);
  echo '</td></tr>';
  echo '<tr><th><label for="slm_test_plan_slug">Plan</label></th><td><select id="slm_test_plan_slug" name="plan_slug">';
  foreach ($plan_choices as $slug => $label) {
    echo '<option value="' . esc_attr($slug) . '"' . selected($selected_plan, $slug, false) . '>' . esc_html($label . ' (' . $slug . ')') . '</option>';
  }
  echo '</select></td></tr>';
  echo '<tr><th><label for="slm_test_term_code">Term</label></th><td><select id="slm_test_term_code" name="term_code">';
  foreach (['m2m', '6m', '12m'] as $term_code) {
    echo '<option value="' . esc_attr($term_code) . '"' . selected($selected_term, $term_code, false) . '>' . esc_html(slm_subscriptions_term_label($term_code)) . '</option>';
  }
  echo '</select><p class="description">Agent plans are 12-month only. Invalid combinations will be rejected.</p></td></tr>';
  echo '<tr><th><label for="slm_test_membership_status">Status</label></th><td><select id="slm_test_membership_status" name="membership_status">';
  foreach (['active' => 'Active', 'inactive' => 'Inactive', 'past_due' => 'Past Due', 'canceled' => 'Canceled'] as $status_key => $status_label) {
    echo '<option value="' . esc_attr($status_key) . '">' . esc_html($status_label) . '</option>';
  }
  echo '</select></td></tr>';
  echo '<tr><th><label for="slm_test_next_billing_date">Next Billing Date (optional)</label></th><td><input type="date" id="slm_test_next_billing_date" name="next_billing_date" /></td></tr>';
  echo '</tbody></table>';
  echo '<input type="hidden" name="slm_test_membership_action" value="apply" />';
  submit_button('Apply Test Membership');
  echo '</form>';

  echo '<form method="post" style="margin-top:8px;">';
  wp_nonce_field('slm_test_membership_admin', 'slm_test_membership_nonce');
  echo '<input type="hidden" name="user_id" value="' . esc_attr((string) $selected_user_id) . '" />';
  echo '<input type="hidden" name="slm_test_membership_action" value="clear_test_flag" />';
  submit_button('Clear TEST Badge (Keep Membership Meta)', 'secondary');
  echo '</form>';

  echo '<form method="post" style="margin-top:8px;">';
  wp_nonce_field('slm_test_membership_admin', 'slm_test_membership_nonce');
  echo '<input type="hidden" name="user_id" value="' . esc_attr((string) $selected_user_id) . '" />';
  echo '<input type="hidden" name="slm_test_membership_action" value="remove_test_membership" />';
  submit_button('Remove Test Membership (Reset to Non-Member)', 'delete');
  echo '</form>';

  if ($selected_user instanceof WP_User) {
    echo '<h2>Selected User Snapshot</h2>';
    echo '<p>' . esc_html($selected_user->display_name . ' <' . $selected_user->user_email . '>') . '</p>';
    echo '<p>' . esc_html(slm_subscriptions_admin_user_membership_snapshot_row((int) $selected_user->ID)) . '</p>';
  }

  echo '</div>';
}

add_action('admin_menu', function () {
  add_menu_page(
    'SLM Membership Ops',
    'Membership Ops',
    'manage_options',
    'slm-membership-ops',
    'slm_subscriptions_render_change_requests_admin_page',
    'dashicons-id-alt',
    58
  );
  add_submenu_page('slm-membership-ops', 'Membership Change Requests', 'Change Requests', 'manage_options', 'slm-membership-ops', 'slm_subscriptions_render_change_requests_admin_page');
  add_submenu_page('slm-membership-ops', 'Member Check', 'Member Check', 'manage_options', 'slm-membership-check', 'slm_subscriptions_render_member_check_admin_page');
  add_submenu_page('slm-membership-ops', 'Membership Recovery', 'Recovery', 'manage_options', 'slm-membership-recovery', 'slm_subscriptions_render_recovery_admin_page');
  add_submenu_page('slm-membership-ops', 'Set Test Membership', 'Set Test Membership', 'manage_options', 'slm-membership-test', 'slm_subscriptions_render_test_membership_admin_page');
});
