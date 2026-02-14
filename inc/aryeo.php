<?php
if (!defined('ABSPATH')) exit;

// --- Options ---

function slm_aryeo_api_key(): string {
  $key = (string) get_option('slm_aryeo_api_key', '');
  return trim($key);
}

function slm_aryeo_default_order_form_id(): string {
  $id = (string) get_option('slm_aryeo_order_form_id', '');
  return trim($id);
}

function slm_aryeo_webhook_secret(): string {
  $secret = (string) get_option('slm_aryeo_webhook_secret', '');
  return trim($secret);
}

function slm_aryeo_is_configured(): bool {
  return slm_aryeo_api_key() !== '' && slm_aryeo_default_order_form_id() !== '';
}

function slm_aryeo_api_base(): string {
  return 'https://api.aryeo.com/v1';
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
      $err = $data['error']['message'] ?? $data['message'] ?? '';
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
  return wp_nonce_url(admin_url('admin-ajax.php?action=slm_aryeo_start_order'), 'slm_aryeo_start_order');
}

function slm_aryeo_create_order_form_session_for_user(WP_User $user) {
  $order_form_id = slm_aryeo_default_order_form_id();
  if ($order_form_id === '') {
    return new WP_Error('slm_aryeo_no_order_form', 'Default Aryeo Order Form ID is not configured.');
  }

  $first_name = (string) $user->first_name;
  $last_name = (string) $user->last_name;
  $phone = (string) get_user_meta($user->ID, 'phone', true);
  $success_url = add_query_arg(['view' => 'my-orders'], slm_portal_url());

  $payload = [
    'order_form_session' => [
      'order_form_id' => $order_form_id,
      'success_url' => $success_url,
      'customer_data' => [
        'email' => (string) $user->user_email,
        'first_name' => $first_name !== '' ? $first_name : (string) $user->display_name,
        'last_name' => $last_name,
      ],
    ],
  ];

  if ($phone !== '') {
    $payload['order_form_session']['customer_data']['phone_number'] = $phone;
  }

  $res = slm_aryeo_request('POST', '/order-form-sessions', [], $payload);
  if (is_wp_error($res)) return $res;

  $url = $res['order_form_session']['url'] ?? '';
  if (!is_string($url) || $url === '') {
    return new WP_Error('slm_aryeo_missing_url', 'Aryeo did not return an order session URL.');
  }

  return $url;
}

add_action('wp_ajax_slm_aryeo_start_order', function () {
  if (!is_user_logged_in()) {
    wp_safe_redirect(add_query_arg('mode', 'login', slm_login_url()));
    exit;
  }

  if (!wp_verify_nonce((string) ($_GET['_wpnonce'] ?? ''), 'slm_aryeo_start_order')) {
    wp_die('Invalid request.', 403);
  }

  $user = wp_get_current_user();
  if (!$user instanceof WP_User || !$user->ID) {
    wp_die('Not logged in.', 403);
  }

  $session_url = slm_aryeo_create_order_form_session_for_user($user);
  if (is_wp_error($session_url)) {
    wp_die(esc_html($session_url->get_error_message()), 500);
  }

  wp_safe_redirect($session_url);
  exit;
});

// --- Orders (tracking) ---

function slm_aryeo_get_orders_for_email(string $email) {
  $email = trim($email);
  if ($email === '') return [];

  $cache_key = 'slm_aryeo_orders_' . md5(strtolower($email));
  $cached = get_transient($cache_key);
  if (is_array($cached)) return $cached;

  $query = [
    'include' => 'items,listing,customer,appointments',
    'filter[search]' => $email,
    'page[limit]' => 25,
  ];

  $res = slm_aryeo_request('GET', '/orders', $query, null);
  if (is_wp_error($res)) return $res;

  $orders = $res['orders'] ?? [];
  if (!is_array($orders)) $orders = [];

  set_transient($cache_key, $orders, 60);
  return $orders;
}

function slm_aryeo_clear_order_cache_for_email(string $email): void {
  $email = trim($email);
  if ($email === '') return;
  delete_transient('slm_aryeo_orders_' . md5(strtolower($email)));
}

// --- Webhook (optional but recommended) ---

function slm_aryeo_verify_webhook_signature(string $raw_body, string $signature, string $secret): bool {
  if ($secret === '' || $signature === '') return false;
  $calc = base64_encode(hash_hmac('sha256', $raw_body, $secret, true));
  return hash_equals($calc, $signature);
}

add_action('rest_api_init', function () {
  register_rest_route('slm/v1', '/aryeo/webhook', [
    'methods' => 'POST',
    'permission_callback' => '__return_true',
    'callback' => function (WP_REST_Request $req) {
      $secret = slm_aryeo_webhook_secret();
      $signature = (string) $req->get_header('aryeo-signature');
      $raw = (string) $req->get_body();

      if ($secret !== '' && !slm_aryeo_verify_webhook_signature($raw, $signature, $secret)) {
        return new WP_REST_Response(['ok' => false, 'error' => 'invalid_signature'], 401);
      }

      $payload = json_decode($raw, true);
      if (!is_array($payload)) {
        return new WP_REST_Response(['ok' => false, 'error' => 'invalid_json'], 400);
      }

      // Minimal behavior: clear the current user's cache if we can find an email; otherwise, just return OK.
      $email = $payload['activity']['resource']['attributes']['customer_email'] ?? '';
      if (is_string($email) && $email !== '') {
        slm_aryeo_clear_order_cache_for_email($email);
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
  register_setting('slm_aryeo', 'slm_aryeo_order_form_id');
  register_setting('slm_aryeo', 'slm_aryeo_webhook_secret');

  add_settings_section('slm_aryeo_main', 'Configuration', '__return_false', 'slm_aryeo');

  add_settings_field('slm_aryeo_api_key', 'API Key', function () {
    $val = esc_attr(slm_aryeo_api_key());
    echo '<input type="password" name="slm_aryeo_api_key" value="' . $val . '" class="regular-text" autocomplete="off" />';
    echo '<p class="description">Company API key from Aryeo (stored server-side). Do not put this in JavaScript.</p>';
  }, 'slm_aryeo', 'slm_aryeo_main');

  add_settings_field('slm_aryeo_order_form_id', 'Default Order Form ID', function () {
    $val = esc_attr(slm_aryeo_default_order_form_id());
    echo '<input type="text" name="slm_aryeo_order_form_id" value="' . $val . '" class="regular-text" placeholder="uuid" />';
    echo '<p class="description">Used when a customer clicks "Start Order".</p>';
  }, 'slm_aryeo', 'slm_aryeo_main');

  add_settings_field('slm_aryeo_webhook_secret', 'Webhook Secret (Optional)', function () {
    $val = esc_attr(slm_aryeo_webhook_secret());
    echo '<input type="text" name="slm_aryeo_webhook_secret" value="' . $val . '" class="regular-text" />';
    echo '<p class="description">If set, we validate the <code>Aryeo-Signature</code> header on incoming webhooks.</p>';
    echo '<p class="description">Webhook URL: <code>' . esc_html(rest_url('slm/v1/aryeo/webhook')) . '</code></p>';
  }, 'slm_aryeo', 'slm_aryeo_main');
});

function slm_aryeo_render_settings_page(): void {
  if (!current_user_can('manage_options')) return;

  $test_result = null;
  if (isset($_POST['slm_aryeo_test']) && check_admin_referer('slm_aryeo_test')) {
    $test_result = slm_aryeo_request('GET', '/order-forms', ['page[limit]' => 5], null);
  }

  echo '<div class="wrap">';
  echo '<h1>Aryeo Integration</h1>';

  if (is_wp_error($test_result)) {
    echo '<div class="notice notice-error"><p>' . esc_html($test_result->get_error_message()) . '</p></div>';
  } elseif (is_array($test_result)) {
    $count = is_array($test_result['order_forms'] ?? null) ? count($test_result['order_forms']) : 0;
    echo '<div class="notice notice-success"><p>Connection OK. Found ' . esc_html((string) $count) . ' order forms (showing up to 5).</p></div>';
    if ($count > 0) {
      echo '<ul>';
      foreach ($test_result['order_forms'] as $of) {
        $id = $of['id'] ?? '';
        $name = $of['name'] ?? '';
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

