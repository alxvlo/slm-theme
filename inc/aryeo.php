<?php
if (!defined('ABSPATH')) exit;

// --- Options ---

function slm_aryeo_api_key(): string {
  $key = (string) get_option('slm_aryeo_api_key', '');
  return trim($key);
}

function slm_aryeo_default_order_form_id(): string {
  $id = (string) get_option('slm_aryeo_order_form_id', '');
  return slm_aryeo_normalize_order_form_id($id);
}

function slm_aryeo_webhook_secret(): string {
  $secret = (string) get_option('slm_aryeo_webhook_secret', '');
  return trim($secret);
}

function slm_aryeo_public_order_form_url(): string {
  $url = (string) get_option('slm_aryeo_order_form_url', '');
  $url = trim($url);
  if ($url === '') {
    return '';
  }

  return esc_url_raw($url);
}

function slm_customer_notifications_enabled(): bool {
  $raw = get_option('slm_customer_notifications_enabled', '0');
  if (is_bool($raw)) return $raw;
  $value = strtolower(trim((string) $raw));
  return !in_array($value, ['0', 'false', 'off', 'no', ''], true);
}

function slm_customer_submission_email_enabled(): bool {
  $raw = get_option('slm_customer_submission_email_enabled', '1');
  if (is_bool($raw)) return $raw;
  $value = strtolower(trim((string) $raw));
  return !in_array($value, ['0', 'false', 'off', 'no', ''], true);
}

function slm_customer_completion_email_enabled(): bool {
  $raw = get_option('slm_customer_completion_email_enabled', '1');
  if (is_bool($raw)) return $raw;
  $value = strtolower(trim((string) $raw));
  return !in_array($value, ['0', 'false', 'off', 'no', ''], true);
}

function slm_aryeo_normalize_order_form_id(string $value): string {
  $value = trim($value);
  if ($value === '') {
    return '';
  }

  // Allow full Aryeo order-form URLs in settings and extract the trailing ID.
  if (strpos($value, 'http://') === 0 || strpos($value, 'https://') === 0) {
    $parts = wp_parse_url($value);
    $path = (string) ($parts['path'] ?? '');
    if ($path !== '') {
      $segments = array_values(array_filter(explode('/', trim($path, '/')), 'strlen'));
      if (!empty($segments)) {
        $value = (string) end($segments);
      }
    }
  }

  $value = trim($value);

  // Normalize UUID-ish IDs if pasted with extra characters.
  if (preg_match('/([0-9a-z]{8,}-[0-9a-z-]{8,})/i', $value, $m)) {
    return strtolower((string) $m[1]);
  }

  return $value;
}

function slm_aryeo_is_configured(): bool {
  return slm_aryeo_api_key() !== '' && slm_aryeo_default_order_form_id() !== '';
}

function slm_aryeo_api_base(): string {
  return 'https://api.aryeo.com/v1';
}

function slm_aryeo_array_is_list(array $value): bool {
  if (function_exists('array_is_list')) {
    return array_is_list($value);
  }

  if ($value === []) {
    return true;
  }

  return array_keys($value) === range(0, count($value) - 1);
}

function slm_aryeo_extract_collection(array $response, string $legacy_key): array {
  $data = $response['data'] ?? null;
  if (is_array($data) && slm_aryeo_array_is_list($data)) {
    return $data;
  }
  if (is_array($data) && isset($data[$legacy_key]) && is_array($data[$legacy_key])) {
    return $data[$legacy_key];
  }

  $legacy = $response[$legacy_key] ?? null;
  if (is_array($legacy)) {
    return $legacy;
  }

  return [];
}

function slm_aryeo_extract_resource(array $response, string $legacy_key): array {
  $data = $response['data'] ?? null;
  if (is_array($data) && !slm_aryeo_array_is_list($data)) {
    return $data;
  }

  $legacy = $response[$legacy_key] ?? null;
  if (is_array($legacy) && !slm_aryeo_array_is_list($legacy)) {
    return $legacy;
  }

  return [];
}

function slm_aryeo_extract_order_customer_email(array $order): string {
  $candidates = [
    $order['customer']['email'] ?? '',
    $order['customer_email'] ?? '',
    $order['customerData']['email'] ?? '',
    $order['customer_data']['email'] ?? '',
    $order['activity']['resource']['attributes']['customer_email'] ?? '',
    $order['data']['attributes']['customer_email'] ?? '',
  ];

  foreach ($candidates as $candidate) {
    if (is_string($candidate)) {
      $candidate = trim($candidate);
      if ($candidate !== '') {
        return $candidate;
      }
    }
  }

  return '';
}

function slm_aryeo_get_order_form_public_url(string $order_form_id) {
  $manual_url = slm_aryeo_public_order_form_url();
  if ($manual_url !== '') {
    return $manual_url;
  }

  $order_form_id = trim($order_form_id);
  if ($order_form_id === '') {
    return new WP_Error('slm_aryeo_no_order_form', 'Default Aryeo Order Form ID is not configured.');
  }

  $cache_key = 'slm_aryeo_order_form_url_' . md5($order_form_id);
  $cached = get_transient($cache_key);
  if (is_string($cached) && $cached !== '') {
    return $cached;
  }

  $res = slm_aryeo_request('GET', '/order-forms', ['per_page' => 100, 'page' => 1], null);
  if (is_wp_error($res)) {
    return $res;
  }

  $forms = slm_aryeo_extract_collection($res, 'order_forms');
  foreach ($forms as $form) {
    if (!is_array($form)) {
      continue;
    }

    $id = strtolower(trim((string) ($form['id'] ?? '')));
    if ($id !== strtolower($order_form_id)) {
      continue;
    }

    $url = trim((string) ($form['url'] ?? $form['public_url'] ?? $form['share_url'] ?? ''));
    if ($url !== '') {
      set_transient($cache_key, $url, 300);
      return $url;
    }
  }

  return new WP_Error('slm_aryeo_order_form_not_found', 'Unable to find the configured order form.');
}

function slm_aryeo_split_display_name(string $display_name): array {
  $display_name = trim($display_name);
  if ($display_name === '') {
    return ['', ''];
  }

  $parts = preg_split('/\s+/', $display_name);
  if (!is_array($parts) || empty($parts)) {
    return [$display_name, ''];
  }

  $first = (string) array_shift($parts);
  $last = trim(implode(' ', $parts));
  return [$first, $last];
}

// --- HTTP client ---

function slm_aryeo_request(string $method, string $path, array $query = [], $json_body = null) {
  $api_key = slm_aryeo_api_key();
  if ($api_key === '') {
    return new WP_Error('slm_aryeo_no_key', 'Aryeo API key is not configured.');
  }

  $url = rtrim(slm_aryeo_api_base(), '/') . '/' . ltrim($path, '/');
  if (!empty($query)) {
    $url = add_query_arg($query, $url);
  }

  $args = [
    'method' => strtoupper($method),
    'timeout' => 20,
    'headers' => [
      'Authorization' => 'Bearer ' . $api_key,
      'Accept' => 'application/json',
    ],
  ];

  if ($json_body !== null) {
    $args['headers']['Content-Type'] = 'application/json';
    $args['body'] = wp_json_encode($json_body);
  }

  $res = wp_remote_request($url, $args);
  if (is_wp_error($res)) return $res;

  $code = (int) wp_remote_retrieve_response_code($res);
  $body = (string) wp_remote_retrieve_body($res);
  $data = null;
  if ($body !== '') {
    $data = json_decode($body, true);
  }

  if ($code < 200 || $code >= 300) {
    $msg = 'Aryeo API request failed.';
    if (is_array($data)) {
      $err = $data['error']['message']
        ?? $data['message']
        ?? $data['errors'][0]['detail']
        ?? $data['errors'][0]['title']
        ?? $data['error']
        ?? '';
      if (is_string($err) && $err !== '') $msg = $err;
    }
    return new WP_Error('slm_aryeo_http_' . $code, $msg, [
      'status' => $code,
      'url' => $url,
      'body' => $data,
    ]);
  }

  return is_array($data) ? $data : [];
}

// --- Ordering ---

function slm_aryeo_start_order_url(): string {
  $url = add_query_arg(['slm_aryeo_start_order' => '1'], home_url('/'));
  return wp_nonce_url($url, 'slm_aryeo_start_order');
}

function slm_aryeo_create_order_form_session_for_user(WP_User $user) {
  $order_form_id = slm_aryeo_default_order_form_id();
  if ($order_form_id === '') {
    return new WP_Error('slm_aryeo_no_order_form', 'Default Aryeo Order Form ID is not configured.');
  }

  $first_name = (string) $user->first_name;
  $last_name = (string) $user->last_name;
  $display_name = trim((string) $user->display_name);
  if ($first_name === '' || $last_name === '') {
    [$display_first, $display_last] = slm_aryeo_split_display_name($display_name);
    if ($first_name === '') {
      $first_name = $display_first;
    }
    if ($last_name === '') {
      $last_name = $display_last;
    }
  }
  if ($first_name === '') {
    $first_name = (string) $user->user_login;
  }
  if ($last_name === '') {
    $last_name = 'Client';
  }
  $phone = (string) get_user_meta($user->ID, 'phone', true);
  $success_url = add_query_arg(['view' => 'my-orders'], slm_portal_url());

  $payload = [
    'order_form_id' => $order_form_id,
    'success_url' => $success_url,
    'customer_data' => [
      'email' => (string) $user->user_email,
      'first_name' => $first_name,
      'last_name' => $last_name,
    ],
  ];

  if ($phone !== '') {
    $payload['customer_data']['phone_number'] = $phone;
  }

  $res = slm_aryeo_request('POST', '/order-form-sessions', [], $payload);
  if (is_wp_error($res)) {
    // Backward compatibility fallback for older Aryeo payload shape.
    $legacy_payload = ['order_form_session' => $payload];
    $res = slm_aryeo_request('POST', '/order-form-sessions', [], $legacy_payload);
  }
  if (is_wp_error($res)) {
    // Fallback: open the public order form URL directly so customers can still place orders.
    $public_url = slm_aryeo_get_order_form_public_url($order_form_id);
    if (!is_wp_error($public_url)) {
      return $public_url;
    }
    return $res;
  }

  $session = slm_aryeo_extract_resource($res, 'order_form_session');
  $url = $session['url'] ?? '';
  if (!is_string($url) || $url === '') {
    $url = $res['order_form_session']['url'] ?? '';
  }

  if (!is_string($url) || $url === '') {
    return new WP_Error('slm_aryeo_missing_url', 'Aryeo did not return an order session URL.');
  }

  return $url;
}

function slm_aryeo_handle_start_order_request(): void {
  $order_portal_url = add_query_arg('view', 'place-order', slm_portal_url());
  if (!is_user_logged_in()) {
    wp_safe_redirect(add_query_arg([
      'mode' => 'login',
      'redirect_to' => $order_portal_url,
    ], slm_login_url()));
    exit;
  }

  $nonce = (string) ($_GET['_wpnonce'] ?? '');
  if ($nonce !== '' && !wp_verify_nonce($nonce, 'slm_aryeo_start_order')) {
    wp_safe_redirect(add_query_arg(['view' => 'place-order', 'error' => 'invalid_request'], slm_portal_url()));
    exit;
  }

  $user = wp_get_current_user();
  if (!$user instanceof WP_User || !$user->ID) {
    wp_safe_redirect(add_query_arg([
      'mode' => 'login',
      'redirect_to' => $order_portal_url,
    ], slm_login_url()));
    exit;
  }

  $session_url = slm_aryeo_create_order_form_session_for_user($user);
  if (is_wp_error($session_url)) {
    wp_safe_redirect(add_query_arg(['view' => 'place-order', 'error' => 'session_failed'], slm_portal_url()));
    exit;
  }

  $session_url = is_string($session_url) ? $session_url : '';
  if ($session_url === '' || !wp_http_validate_url($session_url)) {
    wp_safe_redirect(add_query_arg(['view' => 'place-order', 'error' => 'invalid_url'], slm_portal_url()));
    exit;
  }

  // Aryeo sessions are hosted externally; wp_safe_redirect() can fall back to /wp-admin/.
  wp_redirect($session_url);
  exit;
}

add_action('template_redirect', function () {
  if ((string) ($_GET['slm_aryeo_start_order'] ?? '') !== '1') {
    return;
  }

  slm_aryeo_handle_start_order_request();
}, 1);

add_action('wp_ajax_slm_aryeo_start_order', function () {
  // Backward compatibility for links that still hit admin-ajax.
  slm_aryeo_handle_start_order_request();
});

add_action('admin_init', function () {
  // Compatibility: some host/security layers collapse admin-ajax order links to /wp-admin/.
  $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
  $is_admin_root = (bool) preg_match('#/wp-admin/?$#', $uri);
  if (!$is_admin_root || !is_user_logged_in()) {
    return;
  }

  $ref = wp_get_referer();
  if (!is_string($ref) || $ref === '') {
    return;
  }

  if (strpos($ref, 'slm_aryeo_start_order=1') === false && strpos($ref, 'view=place-order') === false) {
    return;
  }

  wp_safe_redirect(slm_aryeo_start_order_url());
  exit;
}, 1);

// --- Orders (tracking) ---

function slm_aryeo_filter_orders_for_email(array $orders, string $email): array {
  $email = strtolower(trim($email));
  if ($email === '') {
    return [];
  }

  $filtered = [];
  foreach ($orders as $order) {
    if (!is_array($order)) {
      continue;
    }

    $order_email = strtolower(slm_aryeo_extract_order_customer_email($order));
    if ($order_email !== '' && $order_email === $email) {
      $filtered[] = $order;
    }
  }

  return $filtered;
}

function slm_aryeo_positive_int($value): int {
  if (is_int($value)) {
    return $value > 0 ? $value : 0;
  }
  if (is_string($value) && $value !== '' && ctype_digit($value)) {
    return (int) $value;
  }
  if (is_numeric($value)) {
    $int_value = (int) $value;
    return $int_value > 0 ? $int_value : 0;
  }
  return 0;
}

function slm_aryeo_orders_response_has_next_page(array $response, array $batch, int $per_page, int $page, int $max_pages): bool {
  $links = $response['links'] ?? null;
  if (is_array($links) && array_key_exists('next', $links)) {
    $next = $links['next'];
    if (is_string($next)) {
      return trim($next) !== '';
    }
    if ($next !== null) {
      return (bool) $next;
    }
    return false;
  }

  $meta = $response['meta'] ?? [];
  $pagination = is_array($meta) ? ($meta['pagination'] ?? []) : [];
  if (!is_array($pagination)) {
    $pagination = [];
  }

  $top_pagination = $response['pagination'] ?? [];
  if (!is_array($top_pagination)) {
    $top_pagination = [];
  }

  $total_pages_candidates = [
    $meta['total_pages'] ?? null,
    $meta['last_page'] ?? null,
    $meta['page_count'] ?? null,
    $pagination['total_pages'] ?? null,
    $pagination['last_page'] ?? null,
    $pagination['pages'] ?? null,
    $top_pagination['total_pages'] ?? null,
    $top_pagination['last_page'] ?? null,
    $top_pagination['pages'] ?? null,
  ];

  foreach ($total_pages_candidates as $candidate) {
    $total_pages = slm_aryeo_positive_int($candidate);
    if ($total_pages > 0) {
      return $page < $total_pages;
    }
  }

  return count($batch) >= $per_page && $page < $max_pages;
}

function slm_aryeo_get_orders_collection(array $base_query = [], int $max_orders = 1000, int $per_page = 100) {
  $max_orders = max(1, min($max_orders, 5000));
  $per_page = max(1, min($per_page, 100));
  $max_pages = max(1, min((int) ceil($max_orders / $per_page), 50));

  $orders = [];
  $seen_first_ids = [];
  for ($page = 1; $page <= $max_pages; $page++) {
    $query = array_merge([
      'include' => 'items,listing,customer,appointments',
    ], $base_query);
    $query['per_page'] = $per_page;
    $query['page'] = $page;

    $res = slm_aryeo_request('GET', '/orders', $query, null);
    if (is_wp_error($res)) {
      return $res;
    }

    $batch = slm_aryeo_extract_collection($res, 'orders');
    if (empty($batch)) {
      break;
    }

    $first = $batch[0] ?? null;
    $first_id = '';
    if (is_array($first)) {
      $first_id = (string) ($first['id'] ?? '');
    }
    if ($first_id !== '') {
      if (isset($seen_first_ids[$first_id])) {
        break;
      }
      $seen_first_ids[$first_id] = true;
    }

    foreach ($batch as $order) {
      if (!is_array($order)) {
        continue;
      }
      $orders[] = $order;
      if (count($orders) >= $max_orders) {
        break 2;
      }
    }

    if (!slm_aryeo_orders_response_has_next_page($res, $batch, $per_page, $page, $max_pages)) {
      break;
    }
  }

  return $orders;
}

function slm_aryeo_get_orders_for_email(string $email) {
  $email = trim($email);
  if ($email === '') return [];

  $cache_key = 'slm_aryeo_orders_' . md5(strtolower($email));
  $cached = get_transient($cache_key);
  if (is_array($cached)) return $cached;

  $query = [
    'filter[search]' => $email,
  ];

  $orders = slm_aryeo_get_orders_collection($query, 1000, 100);
  if (is_wp_error($orders)) return $orders;
  $orders = slm_aryeo_filter_orders_for_email($orders, $email);

  set_transient($cache_key, $orders, 60);
  return $orders;
}

function slm_aryeo_get_all_orders(int $max_orders = 2000) {
  $max_orders = max(1, min($max_orders, 5000));

  $cache_key = 'slm_aryeo_all_orders';
  $cached = get_transient($cache_key);
  if (is_array($cached)) {
    return array_slice($cached, 0, $max_orders);
  }

  $fetch_limit = max($max_orders, 2000);
  $orders = slm_aryeo_get_orders_collection([], $fetch_limit, 100);
  if (is_wp_error($orders)) {
    return $orders;
  }

  set_transient($cache_key, $orders, 60);
  return array_slice($orders, 0, $max_orders);
}

function slm_aryeo_get_recent_orders(int $limit = 50) {
  $limit = max(1, min($limit, 100));

  return slm_aryeo_get_orders_collection([], $limit, $limit);
}

function slm_aryeo_get_order_by_id(string $order_id) {
  $order_id = trim($order_id);
  if ($order_id === '') {
    return new WP_Error('slm_aryeo_no_order_id', 'Order ID is required.');
  }

  $res = slm_aryeo_request('GET', '/orders/' . rawurlencode($order_id), [
    'include' => 'items,listing,customer,appointments',
  ], null);
  if (is_wp_error($res)) {
    return $res;
  }

  $order = slm_aryeo_extract_resource($res, 'order');
  if (!empty($order)) {
    return $order;
  }

  $orders = slm_aryeo_extract_collection($res, 'orders');
  if (!empty($orders) && is_array($orders[0])) {
    return $orders[0];
  }

  return new WP_Error('slm_aryeo_order_not_found', 'Order not found in Aryeo response.');
}

function slm_aryeo_clear_order_cache_for_email(string $email): void {
  $email = trim($email);
  if ($email === '') return;
  delete_transient('slm_aryeo_orders_' . md5(strtolower($email)));
  delete_transient('slm_aryeo_all_orders');
}

function slm_ops_notifications_enabled(): bool {
  $raw = get_option('slm_ops_notifications_enabled', '1');
  if (is_bool($raw)) {
    return $raw;
  }

  $value = strtolower(trim((string) $raw));
  return !in_array($value, ['0', 'false', 'off', 'no', ''], true);
}

function slm_ops_notification_email(): string {
  $email = sanitize_email((string) get_option('slm_ops_notification_email', ''));
  if ($email === '') {
    $email = sanitize_email((string) get_option('admin_email', ''));
  }
  return $email;
}

function slm_ops_get_notification_log(int $limit = 100): array {
  $limit = max(1, min($limit, 500));
  $log = get_option('slm_ops_notification_log', []);
  if (!is_array($log)) {
    return [];
  }

  $entries = [];
  foreach ($log as $row) {
    if (!is_array($row)) {
      continue;
    }
    $entries[] = $row;
  }

  return array_slice($entries, 0, $limit);
}

function slm_ops_clear_notification_log(): void {
  update_option('slm_ops_notification_log', [], false);
}

function slm_ops_append_notification_log(array $entry): void {
  $log = get_option('slm_ops_notification_log', []);
  if (!is_array($log)) {
    $log = [];
  }

  $normalized = [
    'id' => (string) ($entry['id'] ?? wp_generate_uuid4()),
    'timestamp_gmt' => (string) ($entry['timestamp_gmt'] ?? gmdate('c')),
    'timestamp_local' => (string) ($entry['timestamp_local'] ?? current_time('mysql')),
    'type' => (string) ($entry['type'] ?? 'notification'),
    'event' => (string) ($entry['event'] ?? ''),
    'status' => (string) ($entry['status'] ?? 'unknown'),
    'subject' => (string) ($entry['subject'] ?? ''),
    'recipient' => (string) ($entry['recipient'] ?? ''),
    'order_id' => (string) ($entry['order_id'] ?? ''),
    'order_number' => (string) ($entry['order_number'] ?? ''),
    'details' => (string) ($entry['details'] ?? ''),
  ];

  array_unshift($log, $normalized);
  $log = array_slice($log, 0, 500);
  update_option('slm_ops_notification_log', $log, false);
}

function slm_notifications_send_email(string $to, string $subject, array $lines, array $meta = []): bool {
  $type = (string) ($meta['type'] ?? 'notification');
  $event = (string) ($meta['event'] ?? '');
  $order_id = (string) ($meta['order_id'] ?? '');
  $order_number = (string) ($meta['order_number'] ?? '');
  $enabled = array_key_exists('enabled', $meta) ? !empty($meta['enabled']) : true;
  $disabled_details = (string) ($meta['disabled_details'] ?? 'Notifications are disabled.');
  $recipient = sanitize_email((string) $to);

  if (!$enabled) {
    slm_ops_append_notification_log([
      'type' => $type,
      'event' => $event,
      'status' => 'skipped-disabled',
      'subject' => $subject,
      'recipient' => $recipient,
      'order_id' => $order_id,
      'order_number' => $order_number,
      'details' => $disabled_details,
    ]);
    return false;
  }

  if ($recipient === '' || !is_email($recipient)) {
    slm_ops_append_notification_log([
      'type' => $type,
      'event' => $event,
      'status' => 'skipped-invalid-recipient',
      'subject' => $subject,
      'recipient' => (string) $to,
      'order_id' => $order_id,
      'order_number' => $order_number,
      'details' => 'Recipient email is invalid.',
    ]);
    return false;
  }

  $body = implode("\n", array_values(array_filter(array_map('strval', $lines), static function (string $line): bool {
    return trim($line) !== '';
  })));
  if ($body === '') {
    slm_ops_append_notification_log([
      'type' => $type,
      'event' => $event,
      'status' => 'skipped-empty-body',
      'subject' => $subject,
      'recipient' => $recipient,
      'order_id' => $order_id,
      'order_number' => $order_number,
      'details' => 'Email body was empty.',
    ]);
    return false;
  }

  $headers = ['Content-Type: text/plain; charset=UTF-8'];
  $reply_to = sanitize_email((string) ($meta['reply_to'] ?? ''));
  if ($reply_to !== '') {
    $headers[] = 'Reply-To: ' . $reply_to;
  }
  $sent = (bool) wp_mail($recipient, $subject, $body, $headers);

  slm_ops_append_notification_log([
    'type' => $type,
    'event' => $event,
    'status' => $sent ? 'sent' : 'failed-send',
    'subject' => $subject,
    'recipient' => $recipient,
    'order_id' => $order_id,
    'order_number' => $order_number,
    'details' => $sent ? 'Notification sent.' : 'wp_mail returned false.',
  ]);

  return $sent;
}

function slm_ops_send_notification_email(string $subject, array $lines, array $meta = []): bool {
  return slm_notifications_send_email(
    slm_ops_notification_email(),
    $subject,
    $lines,
    array_merge($meta, [
      'enabled' => slm_ops_notifications_enabled(),
      'disabled_details' => 'Ops notifications are disabled.',
    ])
  );
}

function slm_ops_number_from_value($value): float {
  if (is_int($value)) {
    return ((float) $value) / 100.0;
  }
  if (is_float($value)) {
    $is_whole = abs($value - floor($value)) < 0.00001;
    return $is_whole ? ($value / 100.0) : $value;
  }
  if (is_string($value)) {
    $value = trim($value);
    if ($value === '') {
      return 0.0;
    }

    $normalized = preg_replace('/[^0-9.\-]/', '', $value);
    if (!is_string($normalized) || $normalized === '' || $normalized === '-' || $normalized === '.') {
      return 0.0;
    }

    if (strpos($normalized, '.') === false) {
      return ((float) $normalized) / 100.0;
    }

    return (float) $normalized;
  }
  if (is_numeric($value)) {
    return ((float) $value) / 100.0;
  }
  return 0.0;
}

function slm_aryeo_order_status_normalized($status): string {
  $s = is_string($status) ? strtolower(trim($status)) : '';
  if ($s === '') return 'pending';
  if (in_array($s, ['completed', 'complete', 'delivered'], true)) return 'completed';
  if (in_array($s, ['in-progress', 'in_progress', 'processing'], true)) return 'in-progress';
  if (in_array($s, ['scheduled', 'scheduled_for'], true)) return 'scheduled';
  return 'pending';
}

function slm_aryeo_order_payment_summary(array $order): array {
  $payment = $order['payment'] ?? [];
  if (!is_array($payment)) {
    $payment = [];
  }

  $total_amount = slm_ops_number_from_value($order['total'] ?? $order['total_amount'] ?? $order['grand_total'] ?? $order['amount'] ?? 0);
  $paid_amount = slm_ops_number_from_value(
    $order['amount_paid']
      ?? $order['paid_amount']
      ?? $payment['amount_paid']
      ?? $payment['paid_amount']
      ?? 0
  );
  $due_amount = slm_ops_number_from_value(
    $order['amount_due']
      ?? $order['balance_due']
      ?? $payment['amount_due']
      ?? $payment['balance_due']
      ?? max($total_amount - $paid_amount, 0)
  );
  if ($due_amount <= 0 && $total_amount > 0) {
    $due_amount = max($total_amount - $paid_amount, 0);
  }

  $payment_status = 'unknown';
  if ($total_amount <= 0 && $paid_amount <= 0 && $due_amount <= 0) {
    $payment_status = 'not-set';
  } elseif ($due_amount <= 0.009 && $total_amount > 0) {
    $payment_status = 'paid';
  } elseif ($paid_amount > 0) {
    $payment_status = 'partial';
  } else {
    $payment_status = 'due';
  }

  $payment_url = trim((string) (
    $order['payment_url']
      ?? $payment['url']
      ?? $payment['payment_url']
      ?? $order['invoice_url']
      ?? ''
  ));

  return [
    'total_amount' => $total_amount,
    'paid_amount' => $paid_amount,
    'due_amount' => $due_amount,
    'payment_status' => $payment_status,
    'payment_url' => $payment_url,
  ];
}

function slm_aryeo_order_client_actions(array $normalized_order): array {
  $due_amount = (float) ($normalized_order['due_amount'] ?? 0);
  $payment_url = trim((string) ($normalized_order['payment_url'] ?? ''));
  $delivery_url = trim((string) ($normalized_order['delivery_url'] ?? ''));

  $client_can_pay = $due_amount > 0.009 && $payment_url !== '';
  $client_can_view_delivery = $due_amount <= 0.009 && $delivery_url !== '';
  $delivery_locked_for_payment = $due_amount > 0.009 && $delivery_url !== '';

  return [
    'client_can_pay' => $client_can_pay,
    'client_can_view_delivery' => $client_can_view_delivery,
    'delivery_locked_for_payment' => $delivery_locked_for_payment,
  ];
}

function slm_aryeo_normalize_order(array $order): array {
  $order_id = (string) ($order['id'] ?? '');
  $order_number = (string) ($order['order_number'] ?? $order['number'] ?? $order_id);

  $customer_name = '';
  $customer_email = '';
  $customer_phone = '';
  $customer = $order['customer'] ?? [];
  if (is_array($customer)) {
    $customer_name = (string) ($customer['full_name'] ?? $customer['name'] ?? '');
    if ($customer_name === '') {
      $first = trim((string) ($customer['first_name'] ?? ''));
      $last = trim((string) ($customer['last_name'] ?? ''));
      $customer_name = trim($first . ' ' . $last);
    }
    $customer_email = (string) ($customer['email'] ?? '');
    $customer_phone = (string) ($customer['phone'] ?? $customer['phone_number'] ?? '');
  }
  if ($customer_email === '') {
    $customer_email = slm_aryeo_extract_order_customer_email($order);
  }
  if ($customer_phone === '') {
    $customer_phone = (string) ($order['customer_phone'] ?? $order['phone'] ?? '');
  }
  if ($customer_name === '') {
    $customer_name = 'Customer';
  }

  $item_labels = [];
  $service_name = '';
  $items = $order['items'] ?? [];
  if (is_array($items) && !empty($items)) {
    foreach ($items as $item) {
      if (!is_array($item)) continue;
      $product = $item['product'] ?? [];
      if (!is_array($product)) {
        $product = [];
      }
      $label = trim((string) ($item['name'] ?? $item['title'] ?? $product['name'] ?? $product['title'] ?? ''));
      if ($label === '') continue;
      $item_labels[] = $label;
    }
  }
  if (!empty($item_labels)) {
    $service_name = $item_labels[0];
  }
  if ($service_name === '') {
    $service_name = 'Order';
  }

  $listing = $order['listing'] ?? [];
  if (!is_array($listing)) {
    $listing = [];
  }
  $address_raw = $listing['address'] ?? $order['address'] ?? '';
  if (is_array($address_raw)) {
    $address_raw = (string) ($address_raw['full'] ?? $address_raw['formatted'] ?? $address_raw['street_address'] ?? '');
  }
  $address = trim((string) $address_raw);

  $status = slm_aryeo_order_status_normalized($order['status'] ?? $order['order_status'] ?? '');
  $created_at = (string) ($order['created_at'] ?? '');
  $updated_at = (string) ($order['updated_at'] ?? '');

  $delivery_at = (string) ($order['delivery_date'] ?? $order['delivered_at'] ?? $order['completed_at'] ?? '');
  $delivery_url = trim((string) (
    $order['delivery_url']
      ?? $order['gallery_url']
      ?? $order['listing_website_url']
      ?? $order['url']
      ?? ''
  ));

  $appointment_at = '';
  $appointments = $order['appointments'] ?? [];
  if (is_array($appointments) && !empty($appointments)) {
    $first_appointment = $appointments[0] ?? [];
    if (is_array($first_appointment)) {
      $appointment_at = (string) ($first_appointment['start_at'] ?? $first_appointment['starts_at'] ?? $first_appointment['scheduled_for'] ?? '');
    }
  }

  $payment = slm_aryeo_order_payment_summary($order);
  $normalized = [
    'raw_id' => $order_id,
    'id' => $order_number,
    'customer' => $customer_name,
    'customer_email' => trim($customer_email),
    'customer_phone' => trim($customer_phone),
    'service' => $service_name,
    'items' => $item_labels,
    'address' => $address,
    'status' => $status,
    'price' => (string) ($order['total'] ?? $order['total_amount'] ?? $order['grand_total'] ?? $order['amount'] ?? ''),
    'total_amount' => (float) ($payment['total_amount'] ?? 0),
    'paid_amount' => (float) ($payment['paid_amount'] ?? 0),
    'due_amount' => (float) ($payment['due_amount'] ?? 0),
    'payment_status' => (string) ($payment['payment_status'] ?? 'unknown'),
    'payment_url' => (string) ($payment['payment_url'] ?? ''),
    'delivery_at' => $delivery_at,
    'delivery_url' => $delivery_url,
    'appointment_at' => $appointment_at,
    'date' => (string) ($created_at !== '' ? $created_at : $updated_at),
    'created_at' => $created_at,
    'updated_at' => $updated_at,
    'raw' => $order,
  ];

  return array_merge($normalized, slm_aryeo_order_client_actions($normalized));
}

function slm_aryeo_normalize_orders(array $orders): array {
  $normalized = [];
  foreach ($orders as $order) {
    if (!is_array($order)) continue;
    $normalized[] = slm_aryeo_normalize_order($order);
  }
  return $normalized;
}

function slm_customer_order_email_enabled(string $type): bool {
  if (!slm_customer_notifications_enabled()) return false;
  $type = sanitize_key($type);
  if ($type === 'submission') return slm_customer_submission_email_enabled();
  if ($type === 'completion') return slm_customer_completion_email_enabled();
  return false;
}

function slm_customer_order_email_recipient(array $normalized_order): string {
  return sanitize_email((string) ($normalized_order['customer_email'] ?? ''));
}

function slm_aryeo_customer_email_ttl_seconds(string $template): int {
  $template = sanitize_key($template);
  if ($template === 'completion') return 30 * DAY_IN_SECONDS;
  return 14 * DAY_IN_SECONDS;
}

function slm_aryeo_customer_email_primary_dedupe_key(string $template, array $normalized_order): string {
  $template = sanitize_key($template);
  $order_id = trim((string) ($normalized_order['raw_id'] ?? $normalized_order['id'] ?? ''));
  $order_num = trim((string) ($normalized_order['id'] ?? ''));
  $email = strtolower(trim((string) slm_customer_order_email_recipient($normalized_order)));
  return 'slm_customer_mail_primary_' . md5(implode('|', [$template, $order_id, $order_num, $email]));
}

function slm_aryeo_customer_email_dedupe_key(string $template, array $normalized_order, array $context = []): string {
  $template = sanitize_key($template);
  $trigger = sanitize_key((string) ($context['trigger'] ?? 'webhook'));
  $order_id = trim((string) ($normalized_order['raw_id'] ?? $normalized_order['id'] ?? ''));
  $order_num = trim((string) ($normalized_order['id'] ?? ''));
  $email = strtolower(trim((string) slm_customer_order_email_recipient($normalized_order)));
  $event_id = trim((string) ($context['event_id'] ?? ''));
  $fingerprint = $event_id;
  if ($fingerprint === '') {
    $fingerprint = trim((string) ($context['event_fingerprint'] ?? ''));
  }
  if ($fingerprint === '') {
    $fingerprint = trim((string) ($normalized_order['delivery_at'] ?? $normalized_order['updated_at'] ?? ''));
  }
  return 'slm_customer_mail_' . md5(implode('|', [$template, $trigger, $order_id, $order_num, $email, $fingerprint]));
}

function slm_aryeo_customer_email_should_send(string $template, array $normalized_order, array $context = []): array {
  $primary_key = slm_aryeo_customer_email_primary_dedupe_key($template, $normalized_order);
  $event_key = slm_aryeo_customer_email_dedupe_key($template, $normalized_order, $context);
  if (get_transient($primary_key)) {
    return ['ok' => false, 'reason' => 'duplicate', 'primary_key' => $primary_key, 'event_key' => $event_key];
  }
  if (get_transient($event_key)) {
    return ['ok' => false, 'reason' => 'duplicate', 'primary_key' => $primary_key, 'event_key' => $event_key];
  }
  return ['ok' => true, 'primary_key' => $primary_key, 'event_key' => $event_key];
}

function slm_aryeo_customer_email_mark_sent(string $template, array $normalized_order, array $context = []): void {
  $ttl = slm_aryeo_customer_email_ttl_seconds($template);
  $primary_key = slm_aryeo_customer_email_primary_dedupe_key($template, $normalized_order);
  $event_key = slm_aryeo_customer_email_dedupe_key($template, $normalized_order, $context);
  set_transient($primary_key, 1, $ttl);
  set_transient($event_key, 1, $ttl);
}

function slm_aryeo_customer_email_log_duplicate(string $template, array $normalized_order, array $context = []): void {
  $type = $template === 'completion' ? 'customer-completion' : 'customer-submission';
  slm_ops_append_notification_log([
    'type' => $type,
    'event' => (string) ($context['event_name'] ?? 'duplicate'),
    'status' => 'skipped-duplicate',
    'subject' => (string) ($context['subject'] ?? ''),
    'recipient' => slm_customer_order_email_recipient($normalized_order),
    'order_id' => (string) ($normalized_order['raw_id'] ?? ''),
    'order_number' => (string) ($normalized_order['id'] ?? ''),
    'details' => 'Customer email skipped because a matching message was already sent recently.',
  ]);
}

function slm_aryeo_customer_email_support_contact(): string {
  $email = sanitize_email((string) slm_ops_notification_email());
  if ($email !== '') return $email;
  return sanitize_email((string) get_option('admin_email', ''));
}

function slm_aryeo_customer_order_email_subject(string $template, array $normalized_order): string {
  $site_name = wp_specialchars_decode((string) get_bloginfo('name'), ENT_QUOTES);
  if ($site_name === '') $site_name = 'Showcase Listings Media';
  $order_label = trim((string) ($normalized_order['id'] ?? $normalized_order['raw_id'] ?? ''));
  if ($order_label === '') $order_label = 'your order';
  if ($template === 'completion') {
    return '[' . $site_name . '] Your order is complete: ' . $order_label;
  }
  return '[' . $site_name . '] Order received: ' . $order_label;
}

function slm_aryeo_customer_order_submission_lines(array $normalized_order): array {
  $due = (float) ($normalized_order['due_amount'] ?? 0);
  $total = (float) ($normalized_order['total_amount'] ?? 0);
  $payment_url = trim((string) ($normalized_order['payment_url'] ?? ''));
  $support = slm_aryeo_customer_email_support_contact();
  $lines = [
    'Thanks for your submission. We received your job request.',
    'Order #: ' . ((string) ($normalized_order['id'] ?? $normalized_order['raw_id'] ?? 'n/a')),
    'Service: ' . ((string) ($normalized_order['service'] ?? 'n/a')),
    'Address: ' . ((string) ($normalized_order['address'] ?? 'n/a')),
    'Status: ' . ucwords(str_replace('-', ' ', (string) ($normalized_order['status'] ?? 'pending'))),
    'Total: $' . number_format($total, 2),
  ];
  if ($due > 0.009) {
    $lines[] = 'Balance Due: $' . number_format($due, 2);
    if ($payment_url !== '') {
      $lines[] = 'Pay Online: ' . $payment_url;
    }
  } else {
    $lines[] = 'Payment Status: Paid or no balance due.';
  }
  $lines[] = '';
  $lines[] = 'Preferred scheduling can be requested in the ordering flow, but it is not guaranteed.';
  $lines[] = 'We will confirm scheduling details by phone.';
  if ($support !== '') {
    $lines[] = 'Questions? Reply to this email or contact: ' . $support;
  }
  return $lines;
}

function slm_aryeo_customer_order_completion_lines(array $normalized_order): array {
  $due = (float) ($normalized_order['due_amount'] ?? 0);
  $delivery_url = trim((string) ($normalized_order['delivery_url'] ?? ''));
  $payment_url = trim((string) ($normalized_order['payment_url'] ?? ''));
  $support = slm_aryeo_customer_email_support_contact();
  $lines = [
    'Your order update is complete.',
    'Order #: ' . ((string) ($normalized_order['id'] ?? $normalized_order['raw_id'] ?? 'n/a')),
    'Service: ' . ((string) ($normalized_order['service'] ?? 'n/a')),
    'Address: ' . ((string) ($normalized_order['address'] ?? 'n/a')),
  ];

  if ($due <= 0.009) {
    $lines[] = 'Payment Status: Paid / no balance due.';
    if ($delivery_url !== '') {
      $lines[] = 'Delivery Link: ' . $delivery_url;
    } else {
      $lines[] = 'Delivery link will be shared shortly.';
    }
  } else {
    $lines[] = 'Balance Due: $' . number_format($due, 2);
    $lines[] = 'Complete payment before delivery access is granted.';
    if ($payment_url !== '') {
      $lines[] = 'Pay Online: ' . $payment_url;
    }
  }

  if ($support !== '') {
    $lines[] = '';
    $lines[] = 'Questions? Contact: ' . $support;
  }
  return $lines;
}

function slm_customer_order_email_result(string $template, array $normalized_order, array $context = []): array {
  $template = sanitize_key($template);
  if (!in_array($template, ['submission', 'completion'], true)) {
    return ['ok' => false, 'status' => 'invalid-type', 'message' => 'Invalid customer email type.'];
  }

  $order_id = (string) ($normalized_order['raw_id'] ?? '');
  $order_number = (string) ($normalized_order['id'] ?? '');
  $event_name = (string) ($context['event_name'] ?? ($template === 'completion' ? 'order.completed' : 'order.created'));
  $type_label = $template === 'completion' ? 'customer-completion' : 'customer-submission';
  $subject = slm_aryeo_customer_order_email_subject($template, $normalized_order);
  $recipient = slm_customer_order_email_recipient($normalized_order);

  if ($template === 'completion') {
    $ready = ((string) ($normalized_order['status'] ?? '') === 'completed') || trim((string) ($normalized_order['delivery_url'] ?? '')) !== '';
    if (!$ready) {
      slm_ops_append_notification_log([
        'type' => $type_label,
        'event' => $event_name,
        'status' => 'skipped-not-ready',
        'subject' => $subject,
        'recipient' => $recipient,
        'order_id' => $order_id,
        'order_number' => $order_number,
        'details' => 'Order was not ready for a completion email.',
      ]);
      return ['ok' => false, 'status' => 'not-ready', 'message' => 'Order is not ready for a completion email.'];
    }
  }

  if (!slm_customer_order_email_enabled($template)) {
    slm_notifications_send_email($recipient, $subject, ['Customer notifications are disabled.'], [
      'enabled' => false,
      'disabled_details' => 'Customer notifications are disabled for this email type.',
      'type' => $type_label,
      'event' => $event_name,
      'order_id' => $order_id,
      'order_number' => $order_number,
    ]);
    return ['ok' => false, 'status' => 'disabled', 'message' => 'Customer notifications are disabled.'];
  }

  $dedupe = slm_aryeo_customer_email_should_send($template, $normalized_order, $context);
  if (empty($dedupe['ok'])) {
    slm_aryeo_customer_email_log_duplicate($template, $normalized_order, array_merge($context, ['subject' => $subject]));
    return ['ok' => false, 'status' => 'duplicate', 'message' => 'A matching customer email was already sent recently.'];
  }

  $lines = $template === 'completion'
    ? slm_aryeo_customer_order_completion_lines($normalized_order)
    : slm_aryeo_customer_order_submission_lines($normalized_order);

  $reply_to = slm_aryeo_customer_email_support_contact();
  $sent = slm_notifications_send_email($recipient, $subject, $lines, [
    'type' => $type_label,
    'event' => $event_name,
    'order_id' => $order_id,
    'order_number' => $order_number,
    'reply_to' => $reply_to,
    'enabled' => true,
  ]);
  if (!$sent) {
    return ['ok' => false, 'status' => ($recipient === '' ? 'missing-email' : 'failed'), 'message' => 'Customer email could not be sent.'];
  }

  slm_aryeo_customer_email_mark_sent($template, $normalized_order, $context);
  return ['ok' => true, 'status' => 'sent', 'message' => 'Customer email sent.'];
}

function slm_customer_order_submission_email_result(array $normalized_order, array $context = []): array {
  $context = array_merge(['trigger' => 'webhook'], $context);
  return slm_customer_order_email_result('submission', $normalized_order, $context);
}

function slm_customer_order_completion_email_result(array $normalized_order, string $trigger = 'webhook', array $context = []): array {
  $context = array_merge($context, ['trigger' => sanitize_key($trigger) ?: 'webhook']);
  return slm_customer_order_email_result('completion', $normalized_order, $context);
}

function slm_customer_order_submission_email(array $normalized_order): bool {
  $res = slm_customer_order_submission_email_result($normalized_order, ['trigger' => 'manual']);
  return !empty($res['ok']);
}

function slm_customer_order_completion_email(array $normalized_order, string $trigger = 'webhook'): bool {
  $res = slm_customer_order_completion_email_result($normalized_order, $trigger);
  return !empty($res['ok']);
}

function slm_aryeo_webhook_event_id(array $payload): string {
  $event_candidates = [
    $payload['id'] ?? null,
    $payload['event_id'] ?? null,
    (is_array($payload['event'] ?? null) ? ($payload['event']['id'] ?? null) : null),
    (is_array($payload['meta'] ?? null) ? ($payload['meta']['id'] ?? null) : null),
  ];
  foreach ($event_candidates as $candidate) {
    if (is_string($candidate) && trim($candidate) !== '') {
      return trim($candidate);
    }
  }
  return '';
}

function slm_aryeo_detect_webhook_event_name(array $payload): string {
  $event = '';
  $candidates = [
    $payload['event_type'] ?? null,
    $payload['type'] ?? null,
    $payload['event'] ?? null,
    $payload['action'] ?? null,
    $payload['topic'] ?? null,
  ];

  foreach ($candidates as $candidate) {
    if (is_array($candidate)) {
      $candidate = $candidate['type'] ?? $candidate['name'] ?? '';
    }
    if (is_string($candidate) && trim($candidate) !== '') {
      $event = strtolower(trim($candidate));
      break;
    }
  }

  if ($event !== '') {
    return $event;
  }

  $resource = [];
  if (isset($payload['data']) && is_array($payload['data'])) {
    $resource = $payload['data'];
  } elseif (isset($payload['order']) && is_array($payload['order'])) {
    $resource = $payload['order'];
  } else {
    $resource = $payload;
  }

  $status = strtolower(trim((string) ($resource['status'] ?? $resource['order_status'] ?? '')));
  if (in_array($status, ['completed', 'complete', 'delivered'], true)) {
    return 'order.delivered';
  }

  if ($status !== '') {
    return 'order.updated';
  }

  return 'order.event';
}

function slm_aryeo_extract_webhook_order_payload(array $payload): array {
  if (isset($payload['data']) && is_array($payload['data'])) {
    return $payload['data'];
  }
  if (isset($payload['order']) && is_array($payload['order'])) {
    return $payload['order'];
  }
  if (isset($payload['resource']) && is_array($payload['resource'])) {
    return $payload['resource'];
  }
  return $payload;
}

function slm_aryeo_webhook_dedupe_key(array $payload, string $raw_body): string {
  $event_id = slm_aryeo_webhook_event_id($payload);
  if ($event_id !== '') {
    return 'slm_aryeo_event_mail_' . md5($event_id);
  }

  return 'slm_aryeo_event_mail_' . md5($raw_body);
}

function slm_aryeo_customer_webhook_seen_key(array $normalized_order): string {
  $order_id = trim((string) ($normalized_order['raw_id'] ?? ''));
  $order_number = trim((string) ($normalized_order['id'] ?? ''));
  $recipient = strtolower(trim((string) slm_customer_order_email_recipient($normalized_order)));
  return 'slm_aryeo_seen_order_' . md5(implode('|', [$order_id, $order_number, $recipient]));
}

function slm_aryeo_customer_webhook_is_first_seen(array $normalized_order): bool {
  $key = slm_aryeo_customer_webhook_seen_key($normalized_order);
  if (get_transient($key)) {
    return false;
  }
  set_transient($key, 1, 120 * DAY_IN_SECONDS);
  return true;
}

function slm_aryeo_webhook_event_is_creation_like(string $event_name): bool {
  $event_name = strtolower(trim($event_name));
  if ($event_name === '') {
    return false;
  }
  return strpos($event_name, 'create') !== false
    || strpos($event_name, 'new') !== false
    || $event_name === 'order.created';
}

function slm_aryeo_webhook_event_is_completion_like(string $event_name, array $normalized_order): bool {
  $event_name = strtolower(trim($event_name));
  if (
    strpos($event_name, 'deliver') !== false
    || strpos($event_name, 'complete') !== false
    || $event_name === 'order.delivered'
    || $event_name === 'order.completed'
  ) {
    return true;
  }

  return ((string) ($normalized_order['status'] ?? '')) === 'completed';
}

function slm_aryeo_send_customer_webhook_emails(array $payload, string $raw_body): void {
  $order = slm_aryeo_extract_webhook_order_payload($payload);
  if (empty($order) || !is_array($order)) {
    return;
  }

  $normalized = slm_aryeo_normalize_order($order);
  $recipient = slm_customer_order_email_recipient($normalized);
  if ($recipient === '') {
    return;
  }

  $event_name = slm_aryeo_detect_webhook_event_name($payload);
  $event_id = slm_aryeo_webhook_event_id($payload);
  $context = [
    'trigger' => 'webhook',
    'event_name' => $event_name,
    'event_id' => $event_id,
    'event_fingerprint' => $raw_body !== '' ? md5($raw_body) : '',
  ];

  $is_completion_event = slm_aryeo_webhook_event_is_completion_like($event_name, $normalized);
  $is_creation_event = slm_aryeo_webhook_event_is_creation_like($event_name);
  $is_first_seen = slm_aryeo_customer_webhook_is_first_seen($normalized);

  if ($is_creation_event || ($is_first_seen && !$is_completion_event)) {
    slm_customer_order_submission_email_result($normalized, $context);
  }

  if ($is_completion_event) {
    slm_customer_order_completion_email_result($normalized, 'webhook', $context);
  }
}

function slm_aryeo_send_webhook_notification(array $payload, string $raw_body): void {
  if (!slm_ops_notifications_enabled()) {
    return;
  }

  $dedupe_key = slm_aryeo_webhook_dedupe_key($payload, $raw_body);
  if (get_transient($dedupe_key)) {
    return;
  }

  $event_name = slm_aryeo_detect_webhook_event_name($payload);
  $order = slm_aryeo_extract_webhook_order_payload($payload);
  $listing = $order['listing'] ?? [];
  if (!is_array($listing)) {
    $listing = [];
  }
  $payment = $order['payment'] ?? [];
  if (!is_array($payment)) {
    $payment = [];
  }

  $order_id = (string) ($order['id'] ?? '');
  $order_number = (string) ($order['order_number'] ?? $order['number'] ?? $order_id);
  $status = (string) ($order['status'] ?? $order['order_status'] ?? '');
  $address_raw = $listing['address'] ?? $order['address'] ?? '';
  if (is_array($address_raw)) {
    $address_raw = (string) ($address_raw['full'] ?? $address_raw['formatted'] ?? $address_raw['street_address'] ?? '');
  }
  $address = trim((string) $address_raw);
  $email = slm_aryeo_extract_order_customer_email($order);

  $total = slm_ops_number_from_value(
    $order['total']
      ?? $order['total_amount']
      ?? $order['grand_total']
      ?? $order['amount']
      ?? 0
  );
  $paid = slm_ops_number_from_value(
    $order['amount_paid']
      ?? $order['paid_amount']
      ?? $payment['amount_paid']
      ?? $payment['paid_amount']
      ?? 0
  );
  $due = slm_ops_number_from_value(
    $order['amount_due']
      ?? $order['balance_due']
      ?? $payment['amount_due']
      ?? $payment['balance_due']
      ?? max($total - $paid, 0)
  );
  if ($due <= 0 && $total > 0) {
    $due = max($total - $paid, 0);
  }

  $subject = '[SLM Ops] Order Updated';
  if (strpos($event_name, 'created') !== false) {
    $subject = '[SLM Ops] New Order Created';
  } elseif (strpos($event_name, 'delivery') !== false || strpos($event_name, 'delivered') !== false || in_array(strtolower(trim($status)), ['completed', 'complete', 'delivered'], true)) {
    $subject = '[SLM Ops] Order Delivered';
  } elseif (strpos($event_name, 'payment') !== false || strpos($event_name, 'invoice') !== false) {
    $subject = '[SLM Ops] Order Payment Update';
  }

  if ($order_number !== '') {
    $subject .= ': ' . $order_number;
  } elseif ($order_id !== '') {
    $subject .= ': ' . $order_id;
  }

  $lines = [
    'Aryeo webhook notification',
    'Time: ' . current_time('mysql'),
    'Event: ' . ($event_name !== '' ? $event_name : 'order.event'),
    'Order #: ' . ($order_number !== '' ? $order_number : 'n/a'),
    'Order ID: ' . ($order_id !== '' ? $order_id : 'n/a'),
    'Status: ' . ($status !== '' ? $status : 'n/a'),
    'Customer Email: ' . ($email !== '' ? $email : 'n/a'),
    'Address: ' . ($address !== '' ? $address : 'n/a'),
    'Total: $' . number_format($total, 2),
    'Paid: $' . number_format($paid, 2),
    'Due: $' . number_format($due, 2),
  ];

  if (slm_ops_send_notification_email($subject, $lines, [
    'type' => 'aryeo-webhook',
    'event' => $event_name,
    'order_id' => $order_id,
    'order_number' => $order_number,
  ])) {
    set_transient($dedupe_key, 1, 10 * MINUTE_IN_SECONDS);
  }
}

add_action('user_register', function (int $user_id): void {
  if (!slm_ops_notifications_enabled()) {
    return;
  }

  $user = get_userdata($user_id);
  if (!$user instanceof WP_User) {
    return;
  }

  $phone = trim((string) get_user_meta($user_id, 'phone', true));
  $brokerage = trim((string) get_user_meta($user_id, 'brokerage', true));
  $affiliate_id = trim((string) get_user_meta($user_id, 'affiliate_id', true));

  $name = trim((string) $user->display_name);
  if ($name === '') {
    $name = trim((string) $user->user_login);
  }

  $subject = '[SLM Ops] New Account Created: ' . $name;
  $lines = [
    'New account registration',
    'Time: ' . current_time('mysql'),
    'User ID: ' . (string) $user_id,
    'Name: ' . $name,
    'Email: ' . (string) $user->user_email,
    'Phone: ' . ($phone !== '' ? $phone : 'n/a'),
    'Brokerage: ' . ($brokerage !== '' ? $brokerage : 'n/a'),
    'Affiliate ID: ' . ($affiliate_id !== '' ? $affiliate_id : 'n/a'),
    'Roles: ' . implode(', ', array_map('strval', (array) $user->roles)),
  ];
  slm_ops_send_notification_email($subject, $lines, [
    'type' => 'account-created',
    'event' => 'user_register',
  ]);
});

// --- Webhook (optional but recommended) ---

function slm_aryeo_verify_webhook_signature(string $raw_body, string $signature, string $secret): bool {
  if ($secret === '' || $signature === '') return false;

  $signature = trim($signature);
  $sha256_hex = hash_hmac('sha256', $raw_body, $secret);
  if (hash_equals($sha256_hex, $signature)) {
    return true;
  }

  // Backward compatibility for older implementations using base64(raw sha256).
  $sha256_b64 = base64_encode(hash_hmac('sha256', $raw_body, $secret, true));
  return hash_equals($sha256_b64, $signature);
}

add_action('rest_api_init', function () {
  register_rest_route('slm/v1', '/aryeo/webhook', [
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => function (WP_REST_Request $req) {
      $secret = slm_aryeo_webhook_secret();
      $signature = (string) $req->get_header('signature');
      if ($signature === '') {
        $signature = (string) $req->get_header('aryeo-signature');
      }
      $raw = (string) $req->get_body();

      if ($secret !== '' && !slm_aryeo_verify_webhook_signature($raw, $signature, $secret)) {
        return new WP_REST_Response(['ok' => false, 'error' => 'invalid_signature'], 401);
      }

      $payload = json_decode($raw, true);
      if (!is_array($payload)) {
        return new WP_REST_Response(['ok' => false, 'error' => 'invalid_json'], 400);
      }

      $email = slm_aryeo_extract_order_customer_email($payload);
      if ($email === '' && isset($payload['data']) && is_array($payload['data'])) {
        $email = slm_aryeo_extract_order_customer_email($payload['data']);
      }
      delete_transient('slm_aryeo_all_orders');
      if ($email !== '') {
        slm_aryeo_clear_order_cache_for_email($email);
      }

      slm_aryeo_send_webhook_notification($payload, $raw);
      slm_aryeo_send_customer_webhook_emails($payload, $raw);
      if (function_exists('slm_member_credits_handle_aryeo_webhook')) {
        slm_member_credits_handle_aryeo_webhook($payload, $raw);
      }

      return new WP_REST_Response(['ok' => true], 200);
    },
  ]);
});

// --- Admin settings ---

add_action('admin_menu', function () {
  add_options_page(
    'Aryeo Integration',
    'Aryeo Integration',
    'manage_options',
    'slm-aryeo',
    'slm_aryeo_render_settings_page'
  );
});

add_action('admin_init', function () {
  register_setting('slm_aryeo', 'slm_aryeo_api_key');
  register_setting('slm_aryeo', 'slm_aryeo_order_form_id', [
    'sanitize_callback' => function ($value) {
      return slm_aryeo_normalize_order_form_id((string) $value);
    },
  ]);
  register_setting('slm_aryeo', 'slm_aryeo_order_form_url', [
    'sanitize_callback' => function ($value) {
      return esc_url_raw((string) $value);
    },
  ]);
  register_setting('slm_aryeo', 'slm_aryeo_webhook_secret');
  register_setting('slm_aryeo', 'slm_ops_notifications_enabled', [
    'sanitize_callback' => function ($value) {
      return empty($value) ? '0' : '1';
    },
  ]);
  register_setting('slm_aryeo', 'slm_ops_notification_email', [
    'sanitize_callback' => function ($value) {
      return sanitize_email((string) $value);
    },
  ]);
  register_setting('slm_aryeo', 'slm_customer_notifications_enabled', [
    'sanitize_callback' => function ($value) {
      return empty($value) ? '0' : '1';
    },
  ]);
  register_setting('slm_aryeo', 'slm_customer_submission_email_enabled', [
    'sanitize_callback' => function ($value) {
      return empty($value) ? '0' : '1';
    },
  ]);
  register_setting('slm_aryeo', 'slm_customer_completion_email_enabled', [
    'sanitize_callback' => function ($value) {
      return empty($value) ? '0' : '1';
    },
  ]);

  add_settings_section('slm_aryeo_main', 'Configuration', '__return_false', 'slm_aryeo');

  add_settings_field('slm_aryeo_api_key', 'API Key', function () {
    $val = esc_attr(slm_aryeo_api_key());
    echo '<input type="password" name="slm_aryeo_api_key" value="' . $val . '" class="regular-text" autocomplete="off" />';
    echo '<p class="description">Company API key from Aryeo (stored server-side). Do not put this in JavaScript.</p>';
  }, 'slm_aryeo', 'slm_aryeo_main');

  add_settings_field('slm_aryeo_order_form_id', 'Default Order Form ID', function () {
    $val = esc_attr(slm_aryeo_default_order_form_id());
    echo '<input type="text" name="slm_aryeo_order_form_id" value="' . $val . '" class="regular-text" placeholder="uuid" />';
    echo '<p class="description">Used when a customer clicks "Start Order". You can paste either the UUID or full order-form URL.</p>';
  }, 'slm_aryeo', 'slm_aryeo_main');

  add_settings_field('slm_aryeo_order_form_url', 'Public Order Form URL (Fallback)', function () {
    $val = esc_attr(slm_aryeo_public_order_form_url());
    echo '<input type="url" name="slm_aryeo_order_form_url" value="' . $val . '" class="regular-text code" placeholder="https://your-company.aryeo.com/order-forms/..." />';
    echo '<p class="description">Optional. If Aryeo session creation fails, users are redirected to this URL.</p>';
  }, 'slm_aryeo', 'slm_aryeo_main');

  add_settings_field('slm_aryeo_webhook_secret', 'Webhook Secret (Optional)', function () {
    $val = esc_attr(slm_aryeo_webhook_secret());
    echo '<input type="text" name="slm_aryeo_webhook_secret" value="' . $val . '" class="regular-text" />';
    echo '<p class="description">If set, we validate the <code>Signature</code> header on incoming webhooks.</p>';
    echo '<p class="description">Webhook URL: <code>' . esc_html(rest_url('slm/v1/aryeo/webhook')) . '</code></p>';
  }, 'slm_aryeo', 'slm_aryeo_main');

  add_settings_field('slm_ops_notifications_enabled', 'Ops Email Notifications', function () {
    $enabled = slm_ops_notifications_enabled();
    echo '<input type="hidden" name="slm_ops_notifications_enabled" value="0" />';
    echo '<label><input type="checkbox" name="slm_ops_notifications_enabled" value="1" ' . checked($enabled, true, false) . ' /> Enable account + order event emails</label>';
  }, 'slm_aryeo', 'slm_aryeo_main');

  add_settings_field('slm_ops_notification_email', 'Ops Notification Email', function () {
    $val = esc_attr(slm_ops_notification_email());
    echo '<input type="email" name="slm_ops_notification_email" value="' . $val . '" class="regular-text" />';
    echo '<p class="description">Where operational notifications are sent. Defaults to WordPress admin email if blank.</p>';
  }, 'slm_aryeo', 'slm_aryeo_main');

  add_settings_field('slm_customer_notifications_enabled', 'Customer Email Notifications', function () {
    $enabled = slm_customer_notifications_enabled();
    echo '<input type="hidden" name="slm_customer_notifications_enabled" value="0" />';
    echo '<label><input type="checkbox" name="slm_customer_notifications_enabled" value="1" ' . checked($enabled, true, false) . ' /> Enable customer order emails from WordPress (submission + completion)</label>';
    echo '<p class="description">Leave this off until you confirm Aryeo is not already sending duplicate customer emails.</p>';
  }, 'slm_aryeo', 'slm_aryeo_main');

  add_settings_field('slm_customer_submission_email_enabled', 'Customer Submission Email', function () {
    $enabled = slm_customer_submission_email_enabled();
    echo '<input type="hidden" name="slm_customer_submission_email_enabled" value="0" />';
    echo '<label><input type="checkbox" name="slm_customer_submission_email_enabled" value="1" ' . checked($enabled, true, false) . ' /> Send customer confirmation when a job is submitted</label>';
    echo '<p class="description">Requires the master customer email toggle above.</p>';
  }, 'slm_aryeo', 'slm_aryeo_main');

  add_settings_field('slm_customer_completion_email_enabled', 'Customer Completion Email', function () {
    $enabled = slm_customer_completion_email_enabled();
    echo '<input type="hidden" name="slm_customer_completion_email_enabled" value="0" />';
    echo '<label><input type="checkbox" name="slm_customer_completion_email_enabled" value="1" ' . checked($enabled, true, false) . ' /> Send customer completion / delivery-ready email</label>';
    echo '<p class="description">Requires the master customer email toggle above.</p>';
  }, 'slm_aryeo', 'slm_aryeo_main');
});

function slm_aryeo_render_settings_page(): void {
  if (!current_user_can('manage_options')) return;

  $test_result = null;
  if (isset($_POST['slm_aryeo_test']) && check_admin_referer('slm_aryeo_test')) {
    $test_result = slm_aryeo_request('GET', '/order-forms', ['per_page' => 5, 'page' => 1], null);
  }

  echo '<div class="wrap">';
  echo '<h1>Aryeo Integration</h1>';

  if (is_wp_error($test_result)) {
    echo '<div class="notice notice-error"><p>' . esc_html($test_result->get_error_message()) . '</p></div>';
  } elseif (is_array($test_result)) {
    $order_forms = slm_aryeo_extract_collection($test_result, 'order_forms');
    $count = count($order_forms);
    echo '<div class="notice notice-success"><p>Connection OK. Found ' . esc_html((string) $count) . ' order forms (showing up to 5).</p></div>';
    if ($count > 0) {
      echo '<ul>';
      foreach ($order_forms as $of) {
        $id = $of['id'] ?? '';
        $name = $of['title'] ?? $of['name'] ?? '';
        echo '<li><code>' . esc_html((string) $id) . '</code> ' . esc_html((string) $name) . '</li>';
      }
      echo '</ul>';
    }
  }

  echo '<form method="post" action="options.php">';
  settings_fields('slm_aryeo');
  do_settings_sections('slm_aryeo');
  submit_button('Save Settings');
  echo '</form>';

  echo '<hr />';
  echo '<form method="post">';
  wp_nonce_field('slm_aryeo_test');
  submit_button('Test Connection (List Order Forms)', 'secondary', 'slm_aryeo_test');
  echo '</form>';

  echo '</div>';
}
