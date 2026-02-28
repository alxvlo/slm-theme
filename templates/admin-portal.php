<?php
/**
 * Template Name: Admin Portal
 */
if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
  get_header();
  get_template_part('template-parts/site/guest-dashboard');
  get_footer();
  return;
}

if (!slm_user_is_admin()) {
  wp_safe_redirect(slm_portal_url());
  exit;
}

$view = isset($_GET['view']) ? sanitize_key($_GET['view']) : 'dashboard';
$allowed_views = ['dashboard', 'all-jobs', 'order-detail', 'memberships', 'clients', 'notifications', 'account'];
if (!in_array($view, $allowed_views, true)) {
  $view = 'dashboard';
}

$admin_portal_url = slm_admin_portal_url();

$normalize_status = static function ($status): string {
  $s = is_string($status) ? strtolower(trim($status)) : '';
  if ($s === '') return 'pending';
  if (in_array($s, ['completed', 'complete', 'delivered'], true)) return 'completed';
  if (in_array($s, ['in-progress', 'in_progress', 'processing'], true)) return 'in-progress';
  if (in_array($s, ['scheduled', 'scheduled_for'], true)) return 'scheduled';
  return 'pending';
};

$status_class = static function (string $status): string {
  if ($status === 'completed') return 'is-completed';
  if ($status === 'in-progress') return 'is-progress';
  if ($status === 'scheduled') return 'is-scheduled';
  return 'is-pending';
};

$status_label = static function (string $status): string {
  return ucwords(str_replace('-', ' ', $status));
};

$orders_error = null;
$orders = [];
$orders_by_raw_id = [];
$orders_search = sanitize_text_field(wp_unslash((string) ($_GET['orders_q'] ?? '')));
$orders_status = sanitize_key((string) ($_GET['orders_status'] ?? 'all'));
$orders_page = max(1, (int) ($_GET['orders_page'] ?? 1));
$orders_per_page = 50;
$selected_order_id = sanitize_text_field(wp_unslash((string) ($_GET['order_id'] ?? '')));
$order_notice = sanitize_key((string) ($_GET['order_notice'] ?? ''));
$selected_order = null;
$allowed_status_filters = ['all', 'pending', 'scheduled', 'in-progress', 'completed', 'needs-payment'];
if (!in_array($orders_status, $allowed_status_filters, true)) {
  $orders_status = 'all';
}

$to_amount = static function ($value): float {
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
};

$format_money = static function (float $amount): string {
  return '$' . number_format($amount, 2);
};

$format_datetime = static function (string $date): string {
  if ($date === '') return '';
  $timestamp = strtotime($date);
  if (!$timestamp) return '';
  return date_i18n('M j, Y g:i a', $timestamp);
};

$map_order = static function (array $order) use ($normalize_status, $to_amount): array {
  if (function_exists('slm_aryeo_normalize_order')) {
    return slm_aryeo_normalize_order($order);
  }

  $order_id = (string) ($order['id'] ?? '');
  $order_number = (string) ($order['order_number'] ?? $order['number'] ?? $order_id);
  $listing = $order['listing'] ?? [];
  if (!is_array($listing)) {
    $listing = [];
  }
  $payment = $order['payment'] ?? [];
  if (!is_array($payment)) {
    $payment = [];
  }

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
    $customer_email = (string) ($order['customer_email'] ?? '');
  }
  if ($customer_phone === '') {
    $customer_phone = (string) ($order['customer_phone'] ?? $order['phone'] ?? '');
  }
  if ($customer_name === '') $customer_name = 'Customer';

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
      $label = (string) ($item['name'] ?? $item['title'] ?? $product['name'] ?? $product['title'] ?? '');
      $label = trim($label);
      if ($label === '') continue;
      $item_labels[] = $label;
    }
  }
  if (!empty($item_labels)) {
    $service_name = $item_labels[0];
  }
  if ($service_name === '') $service_name = 'Order';

  $address_raw = $listing['address'] ?? $order['address'] ?? '';
  if (is_array($address_raw)) {
    $address_raw = (string) ($address_raw['full'] ?? $address_raw['formatted'] ?? $address_raw['street_address'] ?? '');
  }
  $address = trim((string) $address_raw);

  $status = $normalize_status($order['status'] ?? $order['order_status'] ?? '');
  $created_at = (string) ($order['created_at'] ?? '');
  $updated_at = (string) ($order['updated_at'] ?? '');

  $total_amount = $to_amount($order['total'] ?? $order['total_amount'] ?? $order['grand_total'] ?? $order['amount'] ?? 0);
  $paid_amount = $to_amount(
    $order['amount_paid']
      ?? $order['paid_amount']
      ?? $payment['amount_paid']
      ?? $payment['paid_amount']
      ?? 0
  );
  $due_amount = $to_amount(
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

  $delivery_at = (string) ($order['delivery_date'] ?? $order['delivered_at'] ?? $order['completed_at'] ?? '');
  $delivery_url = trim((string) (
    $order['delivery_url']
      ?? $order['gallery_url']
      ?? $order['listing_website_url']
      ?? $order['url']
      ?? ''
  ));
  $payment_url = trim((string) (
    $order['payment_url']
      ?? $payment['url']
      ?? $payment['payment_url']
      ?? $order['invoice_url']
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

  return [
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
    'total_amount' => $total_amount,
    'paid_amount' => $paid_amount,
    'due_amount' => $due_amount,
    'payment_status' => $payment_status,
    'payment_url' => $payment_url,
    'delivery_at' => $delivery_at,
    'delivery_url' => $delivery_url,
    'appointment_at' => $appointment_at,
    'date' => (string) ($created_at !== '' ? $created_at : $updated_at),
    'created_at' => $created_at,
    'updated_at' => $updated_at,
    'raw' => $order,
  ];
};

if (function_exists('slm_aryeo_is_configured') && slm_aryeo_is_configured() && function_exists('slm_aryeo_get_recent_orders')) {
  if (function_exists('slm_aryeo_get_all_orders')) {
    $raw_orders = slm_aryeo_get_all_orders(5000);
  } else {
    $raw_orders = slm_aryeo_get_recent_orders(100);
  }
  if (is_wp_error($raw_orders)) {
    $orders_error = $raw_orders->get_error_message();
  } elseif (is_array($raw_orders)) {
    foreach ($raw_orders as $order) {
      if (!is_array($order)) {
        continue;
      }
      $mapped = $map_order($order);
      $orders[] = $mapped;
      if ($mapped['raw_id'] !== '') {
        $orders_by_raw_id[$mapped['raw_id']] = $mapped;
      }
    }
  }
}

if ($selected_order_id !== '' && isset($orders_by_raw_id[$selected_order_id])) {
  $selected_order = $orders_by_raw_id[$selected_order_id];
}

if ($selected_order === null && $selected_order_id !== '' && function_exists('slm_aryeo_get_order_by_id')) {
  $raw_selected_order = slm_aryeo_get_order_by_id($selected_order_id);
  if (is_wp_error($raw_selected_order)) {
    if ($orders_error === null) {
      $orders_error = $raw_selected_order->get_error_message();
    }
  } elseif (is_array($raw_selected_order)) {
    $selected_order = $map_order($raw_selected_order);
    if ($selected_order['raw_id'] !== '' && !isset($orders_by_raw_id[$selected_order['raw_id']])) {
      $orders_by_raw_id[$selected_order['raw_id']] = $selected_order;
      $orders[] = $selected_order;
    }
  }
}

if ($view === 'order-detail' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slm_order_action'])) {
  $action = sanitize_key((string) wp_unslash($_POST['slm_order_action']));
  $nonce = sanitize_text_field((string) wp_unslash($_POST['slm_order_nonce'] ?? ''));
  $target_order_id = sanitize_text_field(wp_unslash((string) ($_POST['order_id'] ?? $selected_order_id)));
  $notice = 'completion-email-failed';

  if ($action === 'send-completion-email' && current_user_can('manage_options') && $nonce !== '' && wp_verify_nonce($nonce, 'slm_order_action')) {
    $order_for_action = null;
    if ($target_order_id !== '' && $selected_order !== null && (string) ($selected_order['raw_id'] ?? '') === $target_order_id) {
      $order_for_action = $selected_order;
    } elseif ($target_order_id !== '' && isset($orders_by_raw_id[$target_order_id])) {
      $order_for_action = $orders_by_raw_id[$target_order_id];
    } elseif ($target_order_id !== '' && function_exists('slm_aryeo_get_order_by_id')) {
      $raw_action_order = slm_aryeo_get_order_by_id($target_order_id);
      if (is_array($raw_action_order)) {
        $order_for_action = $map_order($raw_action_order);
      }
    }

    if (!is_array($order_for_action)) {
      $notice = 'completion-email-failed';
    } elseif (trim((string) ($order_for_action['customer_email'] ?? '')) === '') {
      $notice = 'completion-email-missing-email';
    } else {
      $ready = ((string) ($order_for_action['status'] ?? '') === 'completed') || trim((string) ($order_for_action['delivery_url'] ?? '')) !== '';
      if (!$ready) {
        $notice = 'completion-email-not-ready';
      } elseif (function_exists('slm_customer_order_completion_email_result')) {
        $result = slm_customer_order_completion_email_result(
          $order_for_action,
          'admin_manual',
          ['event_name' => 'admin.manual_completion_email']
        );
        if (!empty($result['ok'])) {
          $notice = 'completion-email-sent';
        } else {
          $status = (string) ($result['status'] ?? '');
          if ($status === 'duplicate') {
            $notice = 'completion-email-duplicate';
          } elseif ($status === 'missing-email') {
            $notice = 'completion-email-missing-email';
          } elseif ($status === 'not-ready') {
            $notice = 'completion-email-not-ready';
          } else {
            $notice = 'completion-email-failed';
          }
        }
      }
    }
  }

  $redirect_args = [
    'view' => 'order-detail',
    'order_notice' => $notice,
  ];
  if ($target_order_id !== '') {
    $redirect_args['order_id'] = $target_order_id;
  } elseif ($selected_order_id !== '') {
    $redirect_args['order_id'] = $selected_order_id;
  }
  wp_safe_redirect(add_query_arg($redirect_args, $admin_portal_url));
  exit;
}

if ($view === 'memberships' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slm_memberships_action'])) {
  $action = sanitize_key((string) wp_unslash($_POST['slm_memberships_action']));
  $nonce = sanitize_text_field((string) wp_unslash($_POST['slm_memberships_nonce'] ?? ''));
  $target_user_id = max(0, (int) ($_POST['member_user_id'] ?? $_GET['member_user_id'] ?? 0));
  $memberships_q_redirect = sanitize_text_field(wp_unslash((string) ($_GET['memberships_q'] ?? '')));
  $notice = 'membership-action-failed';

  if ($nonce !== '' && wp_verify_nonce($nonce, 'slm_memberships_action') && current_user_can('manage_options')) {
    if ($action === 'adjust-credit' && function_exists('slm_member_credits_admin_adjust')) {
      $credit_key = sanitize_key((string) wp_unslash($_POST['credit_key'] ?? ''));
      $quantity_delta = (int) wp_unslash($_POST['quantity_delta'] ?? 0);
      $note = sanitize_text_field((string) wp_unslash($_POST['adjust_note'] ?? ''));
      $result = slm_member_credits_admin_adjust($target_user_id, $credit_key, $quantity_delta, $note, get_current_user_id());
      $notice = is_wp_error($result) ? 'credit-adjust-failed' : 'credit-adjusted';
    } elseif ($action === 'resolve-credit-flag' && function_exists('slm_member_credits_resolve_flag')) {
      $flag_id = max(0, (int) ($_POST['flag_id'] ?? 0));
      $notice = slm_member_credits_resolve_flag($flag_id, get_current_user_id()) ? 'flag-resolved' : 'flag-resolve-failed';
    } elseif ($action === 'sync-member-credits' && function_exists('slm_member_credits_reconcile_user')) {
      $result = slm_member_credits_reconcile_user($target_user_id);
      $notice = (is_array($result) && !empty($result['ok'])) ? 'credits-synced' : 'credits-sync-failed';
    } elseif ($action === 'cancel-membership' && $target_user_id > 0 && function_exists('slm_subscriptions_square_cancel_membership_for_user')) {
      $target_user = get_userdata($target_user_id);
      if ($target_user instanceof WP_User) {
        $result = slm_subscriptions_square_cancel_membership_for_user($target_user);
        if (is_wp_error($result)) {
          $notice = $result->get_error_code() === 'slm_subscription_commitment_active' ? 'membership-cancel-blocked' : 'membership-cancel-failed';
        } else {
          $notice = 'membership-cancel-scheduled';
        }
      } else {
        $notice = 'membership-cancel-failed';
      }
    }
  }

  $redirect_args = ['view' => 'memberships', 'membership_notice' => $notice];
  if ($target_user_id > 0) $redirect_args['member_user_id'] = (string) $target_user_id;
  if ($memberships_q_redirect !== '') $redirect_args['memberships_q'] = $memberships_q_redirect;
  wp_safe_redirect(add_query_arg($redirect_args, $admin_portal_url));
  exit;
}

usort($orders, static function (array $a, array $b): int {
  $a_time = strtotime((string) ($a['date'] ?? '')) ?: 0;
  $b_time = strtotime((string) ($b['date'] ?? '')) ?: 0;
  if ($a_time === $b_time) {
    return strcmp((string) ($b['id'] ?? ''), (string) ($a['id'] ?? ''));
  }
  return $b_time <=> $a_time;
});

$stats = [
  'total_orders' => count($orders),
  'active_orders' => 0,
  'completed' => 0,
  'active_customers' => 0,
  'revenue' => 0.0,
];

$customers = [];
foreach ($orders as $order) {
  $status = (string) ($order['status'] ?? '');
  if ($status === 'completed') {
    $stats['completed']++;
  } else {
    $stats['active_orders']++;
  }

  $customer = trim((string) ($order['customer'] ?? ''));
  if ($customer !== '') $customers[$customer] = true;

  $stats['revenue'] += (float) ($order['total_amount'] ?? 0);
}

$stats['active_customers'] = count($customers);
$revenue_label = '$' . number_format($stats['revenue'], 2);
$recent_orders = array_slice($orders, 0, 10);

$filtered_orders = $orders;
if ($orders_search !== '') {
  $needle = strtolower($orders_search);
  $filtered_orders = array_values(array_filter($filtered_orders, static function (array $order) use ($needle): bool {
    $haystack = strtolower(implode(' ', [
      (string) ($order['id'] ?? ''),
      (string) ($order['customer'] ?? ''),
      (string) ($order['service'] ?? ''),
      (string) ($order['address'] ?? ''),
      (string) ($order['status'] ?? ''),
      (string) ($order['price'] ?? ''),
      (string) ($order['date'] ?? ''),
    ]));
    return strpos($haystack, $needle) !== false;
  }));
}

if ($orders_status !== 'all') {
  $filtered_orders = array_values(array_filter($filtered_orders, static function (array $order) use ($orders_status): bool {
    if ($orders_status === 'needs-payment') {
      return (float) ($order['due_amount'] ?? 0) > 0.009;
    }
    return (string) ($order['status'] ?? '') === $orders_status;
  }));
}

$total_filtered_orders = count($filtered_orders);
$total_order_pages = max(1, (int) ceil($total_filtered_orders / $orders_per_page));
$orders_page = min($orders_page, $total_order_pages);
$orders_offset = ($orders_page - 1) * $orders_per_page;
$paged_orders = array_slice($filtered_orders, $orders_offset, $orders_per_page);
$showing_from = $total_filtered_orders > 0 ? $orders_offset + 1 : 0;
$showing_to = min($orders_offset + count($paged_orders), $total_filtered_orders);

$all_jobs_base_args = ['view' => 'all-jobs'];
if ($orders_search !== '') {
  $all_jobs_base_args['orders_q'] = $orders_search;
}
if ($orders_status !== 'all') {
  $all_jobs_base_args['orders_status'] = $orders_status;
}
$all_jobs_base_url = add_query_arg($all_jobs_base_args, $admin_portal_url);
$all_jobs_page_url = static function (int $page) use ($all_jobs_base_url): string {
  return add_query_arg('orders_page', (string) max(1, $page), $all_jobs_base_url);
};
$all_jobs_reset_url = add_query_arg('view', 'all-jobs', $admin_portal_url);

$notifications_notice = sanitize_key((string) ($_GET['notifications_notice'] ?? ''));
$notifications_q = sanitize_text_field(wp_unslash((string) ($_GET['notifications_q'] ?? '')));
$notifications_status = sanitize_key((string) ($_GET['notifications_status'] ?? 'all'));
$notifications_type = sanitize_key((string) ($_GET['notifications_type'] ?? 'all'));
$notifications_page = max(1, (int) ($_GET['notifications_page'] ?? 1));
$notifications_per_page = 25;

$allowed_notification_statuses = ['all', 'sent', 'failed-send', 'skipped-disabled', 'skipped-invalid-recipient', 'skipped-empty-body', 'skipped-duplicate', 'skipped-not-ready'];
if (!in_array($notifications_status, $allowed_notification_statuses, true)) {
  $notifications_status = 'all';
}

$allowed_notification_types = ['all', 'aryeo-webhook', 'account-created', 'manual-test', 'customer-submission', 'customer-completion', 'notification'];
if (!in_array($notifications_type, $allowed_notification_types, true)) {
  $notifications_type = 'all';
}

if ($view === 'notifications' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slm_notifications_action'])) {
  $action = sanitize_key((string) wp_unslash($_POST['slm_notifications_action']));
  $nonce = sanitize_text_field((string) wp_unslash($_POST['slm_notifications_nonce'] ?? ''));
  if ($nonce !== '' && wp_verify_nonce($nonce, 'slm_notifications_action')) {
    $notice = 'noop';
    if ($action === 'clear-log' && function_exists('slm_ops_clear_notification_log')) {
      slm_ops_clear_notification_log();
      $notice = 'cleared';
    }
    if ($action === 'send-test' && function_exists('slm_ops_send_notification_email')) {
      $sent = slm_ops_send_notification_email(
        '[SLM Ops] Test Notification',
        [
          'Manual test notification from Admin Portal.',
          'Time: ' . current_time('mysql'),
          'Triggered by: ' . (string) wp_get_current_user()->user_login,
        ],
        [
          'type' => 'manual-test',
          'event' => 'manual_test',
        ]
      );
      $notice = $sent ? 'test-sent' : 'test-failed';
    }

    $redirect_args = [
      'view' => 'notifications',
      'notifications_notice' => $notice,
    ];
    if ($notifications_q !== '') {
      $redirect_args['notifications_q'] = $notifications_q;
    }
    if ($notifications_status !== 'all') {
      $redirect_args['notifications_status'] = $notifications_status;
    }
    if ($notifications_type !== 'all') {
      $redirect_args['notifications_type'] = $notifications_type;
    }

    wp_safe_redirect(add_query_arg($redirect_args, $admin_portal_url));
    exit;
  }
}

$notification_rows = [];
if (function_exists('slm_ops_get_notification_log')) {
  $notification_rows = slm_ops_get_notification_log(500);
}
if (!is_array($notification_rows)) {
  $notification_rows = [];
}

$filtered_notifications = $notification_rows;
if ($notifications_q !== '') {
  $needle = strtolower($notifications_q);
  $filtered_notifications = array_values(array_filter($filtered_notifications, static function (array $row) use ($needle): bool {
    $haystack = strtolower(implode(' ', [
      (string) ($row['timestamp_local'] ?? ''),
      (string) ($row['type'] ?? ''),
      (string) ($row['event'] ?? ''),
      (string) ($row['status'] ?? ''),
      (string) ($row['subject'] ?? ''),
      (string) ($row['recipient'] ?? ''),
      (string) ($row['order_id'] ?? ''),
      (string) ($row['order_number'] ?? ''),
      (string) ($row['details'] ?? ''),
    ]));
    return strpos($haystack, $needle) !== false;
  }));
}

if ($notifications_status !== 'all') {
  $filtered_notifications = array_values(array_filter($filtered_notifications, static function (array $row) use ($notifications_status): bool {
    return (string) ($row['status'] ?? '') === $notifications_status;
  }));
}

if ($notifications_type !== 'all') {
  $filtered_notifications = array_values(array_filter($filtered_notifications, static function (array $row) use ($notifications_type): bool {
    return (string) ($row['type'] ?? '') === $notifications_type;
  }));
}

$total_notifications = count($filtered_notifications);
$total_notification_pages = max(1, (int) ceil($total_notifications / $notifications_per_page));
$notifications_page = min($notifications_page, $total_notification_pages);
$notifications_offset = ($notifications_page - 1) * $notifications_per_page;
$paged_notifications = array_slice($filtered_notifications, $notifications_offset, $notifications_per_page);
$notifications_showing_from = $total_notifications > 0 ? $notifications_offset + 1 : 0;
$notifications_showing_to = min($notifications_offset + count($paged_notifications), $total_notifications);

$notifications_base_args = ['view' => 'notifications'];
if ($notifications_q !== '') {
  $notifications_base_args['notifications_q'] = $notifications_q;
}
if ($notifications_status !== 'all') {
  $notifications_base_args['notifications_status'] = $notifications_status;
}
if ($notifications_type !== 'all') {
  $notifications_base_args['notifications_type'] = $notifications_type;
}
$notifications_base_url = add_query_arg($notifications_base_args, $admin_portal_url);
$notifications_page_url = static function (int $page) use ($notifications_base_url): string {
  return add_query_arg('notifications_page', (string) max(1, $page), $notifications_base_url);
};
$notifications_reset_url = add_query_arg('view', 'notifications', $admin_portal_url);

$clients_q = sanitize_text_field(wp_unslash((string) ($_GET['clients_q'] ?? '')));
$clients_page = max(1, (int) ($_GET['clients_page'] ?? 1));
$clients_per_page = 50;
$clients_rows = [];
$all_wp_users = get_users([
  'number' => -1,
  'orderby' => 'registered',
  'order' => 'DESC',
]);
if (is_array($all_wp_users)) {
  foreach ($all_wp_users as $client_user) {
    if (!$client_user instanceof WP_User) {
      continue;
    }

    $user_id = (int) $client_user->ID;
    $phone = trim((string) get_user_meta($user_id, 'phone', true));
    $brokerage = trim((string) get_user_meta($user_id, 'brokerage', true));
    $display_name = trim((string) $client_user->display_name);
    if ($display_name === '') {
      $display_name = trim((string) $client_user->user_login);
    }
    $email = (string) $client_user->user_email;
    $roles = array_values(array_map('strval', (array) $client_user->roles));
    $is_admin_user = in_array('administrator', $roles, true);

    if ($clients_q !== '') {
      $needle = strtolower($clients_q);
      $haystack = strtolower(implode(' ', [
        $display_name,
        (string) $client_user->user_login,
        $email,
        $phone,
        $brokerage,
        implode(' ', $roles),
      ]));
      if (strpos($haystack, $needle) === false) {
        continue;
      }
    }

    $clients_rows[] = [
      'user_id' => $user_id,
      'name' => $display_name,
      'email' => $email,
      'phone' => $phone,
      'brokerage' => $brokerage,
      'registered' => (string) $client_user->user_registered,
      'roles' => $roles,
      'is_admin' => $is_admin_user,
      'edit_url' => add_query_arg('user_id', (string) $user_id, admin_url('user-edit.php')),
      'portal_url' => slm_portal_url(),
    ];
  }
}

usort($clients_rows, static function (array $a, array $b): int {
  if (!empty($a['is_admin']) !== !empty($b['is_admin'])) {
    return !empty($a['is_admin']) ? 1 : -1;
  }
  $a_time = strtotime((string) ($a['registered'] ?? '')) ?: 0;
  $b_time = strtotime((string) ($b['registered'] ?? '')) ?: 0;
  if ($a_time === $b_time) {
    return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
  }
  return $b_time <=> $a_time;
});

$total_clients = count($clients_rows);
$total_client_pages = max(1, (int) ceil($total_clients / $clients_per_page));
$clients_page = min($clients_page, $total_client_pages);
$clients_offset = ($clients_page - 1) * $clients_per_page;
$paged_clients = array_slice($clients_rows, $clients_offset, $clients_per_page);
$clients_showing_from = $total_clients > 0 ? $clients_offset + 1 : 0;
$clients_showing_to = min($clients_offset + count($paged_clients), $total_clients);

$clients_base_args = ['view' => 'clients'];
if ($clients_q !== '') {
  $clients_base_args['clients_q'] = $clients_q;
}
$clients_base_url = add_query_arg($clients_base_args, $admin_portal_url);
$clients_page_url = static function (int $page) use ($clients_base_url): string {
  return add_query_arg('clients_page', (string) max(1, $page), $clients_base_url);
};
$clients_reset_url = add_query_arg('view', 'clients', $admin_portal_url);

$memberships_q = sanitize_text_field(wp_unslash((string) ($_GET['memberships_q'] ?? '')));
$memberships_page = max(1, (int) ($_GET['memberships_page'] ?? 1));
$memberships_per_page = 50;
$selected_member_user_id = max(0, (int) ($_GET['member_user_id'] ?? 0));
$membership_notice = sanitize_key((string) ($_GET['membership_notice'] ?? ''));
$memberships_rows = function_exists('slm_member_credits_membership_rows') ? slm_member_credits_membership_rows(['search' => $memberships_q, 'include_inactive' => false]) : [];
if (!is_array($memberships_rows)) $memberships_rows = [];
$total_memberships = count($memberships_rows);
$total_membership_pages = max(1, (int) ceil($total_memberships / $memberships_per_page));
$memberships_page = min($memberships_page, $total_membership_pages);
$memberships_offset = ($memberships_page - 1) * $memberships_per_page;
$paged_memberships = array_slice($memberships_rows, $memberships_offset, $memberships_per_page);
$memberships_showing_from = $total_memberships > 0 ? $memberships_offset + 1 : 0;
$memberships_showing_to = min($memberships_offset + count($paged_memberships), $total_memberships);
$memberships_base_args = ['view' => 'memberships'];
if ($memberships_q !== '') $memberships_base_args['memberships_q'] = $memberships_q;
if ($selected_member_user_id > 0) $memberships_base_args['member_user_id'] = (string) $selected_member_user_id;
$memberships_base_url = add_query_arg($memberships_base_args, $admin_portal_url);
$memberships_page_url = static function (int $page) use ($memberships_base_url): string {
  return add_query_arg('memberships_page', (string) max(1, $page), $memberships_base_url);
};
$memberships_reset_url = add_query_arg('view', 'memberships', $admin_portal_url);
$selected_member_detail = ($selected_member_user_id > 0 && function_exists('slm_member_credits_member_detail')) ? slm_member_credits_member_detail($selected_member_user_id) : [];

get_header();
?>

<div class="portal-shell">
  <aside class="portal-sidebar">
    <div class="portal-brand">
      <span class="portal-brand__logo">AD</span>
      <div>
        <strong>Admin Portal</strong>
        <small>Operations Workspace</small>
      </div>
    </div>

    <nav class="portal-nav" aria-label="Admin Portal">
      <a class="<?php echo $view === 'dashboard' ? 'is-active' : ''; ?>"<?php echo $view === 'dashboard' ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('view', 'dashboard', $admin_portal_url)); ?>">Dashboard</a>
      <a class="<?php echo in_array($view, ['all-jobs', 'order-detail'], true) ? 'is-active' : ''; ?>"<?php echo in_array($view, ['all-jobs', 'order-detail'], true) ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('view', 'all-jobs', $admin_portal_url)); ?>">All Orders</a>
      <a class="<?php echo $view === 'memberships' ? 'is-active' : ''; ?>"<?php echo $view === 'memberships' ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('view', 'memberships', $admin_portal_url)); ?>">Memberships</a>
      <a class="<?php echo $view === 'clients' ? 'is-active' : ''; ?>"<?php echo $view === 'clients' ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('view', 'clients', $admin_portal_url)); ?>">Clients</a>
      <a class="<?php echo $view === 'notifications' ? 'is-active' : ''; ?>"<?php echo $view === 'notifications' ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('view', 'notifications', $admin_portal_url)); ?>">Notifications</a>
      <a class="<?php echo $view === 'account' ? 'is-active' : ''; ?>"<?php echo $view === 'account' ? ' aria-current="page"' : ''; ?> href="<?php echo esc_url(add_query_arg('view', 'account', $admin_portal_url)); ?>">Account</a>
    </nav>

    <div class="portal-sidebar__footer">
      <a class="btn btn--secondary" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
    </div>
  </aside>

  <main class="portal-main">
    <div class="portal-wrap">
      <section class="portal-toolbar" aria-label="Admin Quick Actions">
        <a class="portal-toolbar__pill <?php echo $view === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'dashboard', $admin_portal_url)); ?>">Overview</a>
        <a class="portal-toolbar__pill <?php echo in_array($view, ['all-jobs', 'order-detail'], true) ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'all-jobs', $admin_portal_url)); ?>">Order Queue</a>
        <a class="portal-toolbar__pill <?php echo $view === 'memberships' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'memberships', $admin_portal_url)); ?>">Memberships</a>
        <a class="portal-toolbar__pill <?php echo $view === 'clients' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'clients', $admin_portal_url)); ?>">Clients</a>
        <a class="portal-toolbar__pill <?php echo $view === 'notifications' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'notifications', $admin_portal_url)); ?>">Notifications</a>
        <a class="portal-toolbar__pill <?php echo $view === 'account' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'account', $admin_portal_url)); ?>">Admin Profile</a>
      </section>

      <?php if ($view === 'dashboard'): ?>
        <section class="portal-section">
          <h1>Admin Dashboard</h1>
          <p class="sub">Manage customer orders and monitor operations with a cleaner snapshot of active work.</p>
        </section>

        <section class="portal-stats portal-stats--four">
          <article class="portal-card">
            <p>Total Orders</p>
            <strong><?php echo esc_html((string) $stats['total_orders']); ?></strong>
          </article>
          <article class="portal-card">
            <p>Active Customers</p>
            <strong><?php echo esc_html((string) $stats['active_customers']); ?></strong>
          </article>
          <article class="portal-card">
            <p>Active Orders</p>
            <strong><?php echo esc_html((string) $stats['active_orders']); ?></strong>
          </article>
          <article class="portal-card">
            <p>Revenue</p>
            <strong><?php echo esc_html($revenue_label); ?></strong>
          </article>
        </section>

        <section class="portal-actions">
          <a class="portal-action portal-action--primary" href="<?php echo esc_url(add_query_arg('view', 'all-jobs', $admin_portal_url)); ?>">
            <h2>View All Orders</h2>
            <p>Review statuses, service types, and customer details.</p>
          </a>
          <a class="portal-action" href="<?php echo esc_url(add_query_arg('view', 'account', $admin_portal_url)); ?>">
            <h2>Manage Account</h2>
            <p>Update admin profile and operational settings.</p>
          </a>
        </section>

        <section class="portal-tableCard">
          <div class="portal-tableCard__head">
            <h2>Recent Customer Orders</h2>
            <a href="<?php echo esc_url(add_query_arg('view', 'all-jobs', $admin_portal_url)); ?>">View All</a>
          </div>
          <div class="table-scroll">
            <table class="table" aria-label="Recent Customer Orders">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Service</th>
                  <th>Address</th>
                  <th>Status</th>
                  <th>Price</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($orders_error !== null): ?>
                  <tr><td colspan="7">Unable to load recent orders: <?php echo esc_html($orders_error); ?></td></tr>
                <?php elseif (empty($recent_orders)): ?>
                  <tr><td colspan="7">No recent customer orders available yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($recent_orders as $order): ?>
                    <?php $date_label = $order['date'] !== '' ? date_i18n('M j, Y', strtotime($order['date'])) : ''; ?>
                    <tr>
                      <td>
                        <?php if (($order['raw_id'] ?? '') !== ''): ?>
                          <a href="<?php echo esc_url(add_query_arg(['view' => 'order-detail', 'order_id' => $order['raw_id']], $admin_portal_url)); ?>"><?php echo esc_html($order['id']); ?></a>
                        <?php else: ?>
                          <?php echo esc_html($order['id']); ?>
                        <?php endif; ?>
                      </td>
                      <td><?php echo esc_html($order['customer']); ?></td>
                      <td><?php echo esc_html($order['service']); ?></td>
                      <td><?php echo esc_html($order['address']); ?></td>
                      <td><span class="status-pill <?php echo esc_attr($status_class($order['status'])); ?>"><?php echo esc_html($status_label($order['status'])); ?></span></td>
                      <td><?php echo esc_html($format_money((float) ($order['total_amount'] ?? 0))); ?></td>
                      <td><?php echo esc_html($date_label); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($view === 'all-jobs'): ?>
        <section class="portal-section">
          <h1>All Customer Orders</h1>
          <p class="sub">Centralized operations view for all active and completed jobs.</p>
        </section>
        <section class="portal-tableCard" data-server-filtered="1">
          <div class="portal-tableCard__head">
            <h2>Order Queue</h2>
            <a href="<?php echo esc_url($all_jobs_reset_url); ?>">Reset Filters</a>
          </div>

          <form class="table-controls table-controls--server" method="get" action="<?php echo esc_url($admin_portal_url); ?>">
            <input type="hidden" name="view" value="all-jobs">

            <div class="table-control">
              <label for="orders_q">Search</label>
              <input id="orders_q" name="orders_q" type="search" value="<?php echo esc_attr($orders_search); ?>" placeholder="Search order, customer, service, address">
            </div>

            <div class="table-control">
              <label for="orders_status">Status</label>
              <select id="orders_status" name="orders_status">
                <option value="all" <?php selected($orders_status, 'all'); ?>>All statuses</option>
                <option value="completed" <?php selected($orders_status, 'completed'); ?>>Completed</option>
                <option value="in-progress" <?php selected($orders_status, 'in-progress'); ?>>In Progress</option>
                <option value="scheduled" <?php selected($orders_status, 'scheduled'); ?>>Scheduled</option>
                <option value="pending" <?php selected($orders_status, 'pending'); ?>>Pending</option>
                <option value="needs-payment" <?php selected($orders_status, 'needs-payment'); ?>>Needs Payment</option>
              </select>
            </div>

            <div class="table-control table-control--action">
              <label for="orders_apply">Apply</label>
              <button id="orders_apply" type="submit" class="table-reset btn btn--secondary">Apply Filters</button>
            </div>

            <div class="table-control table-control--action">
              <label>Reset</label>
              <a class="table-reset btn btn--secondary" href="<?php echo esc_url($all_jobs_reset_url); ?>">Reset</a>
            </div>
          </form>

          <div class="table-scroll">
            <table class="table" aria-label="All Customer Orders">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Service</th>
                  <th>Address</th>
                  <th>Status</th>
                  <th>Total</th>
                  <th>Payment</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($orders_error !== null): ?>
                  <tr><td colspan="9">Unable to load orders: <?php echo esc_html($orders_error); ?></td></tr>
                <?php elseif (empty($orders)): ?>
                  <tr><td colspan="9">No customer orders available yet.</td></tr>
                <?php elseif (empty($paged_orders)): ?>
                  <tr><td colspan="9">No matching orders for current filters.</td></tr>
                <?php else: ?>
                  <?php foreach ($paged_orders as $order): ?>
                    <?php $date_label = $order['date'] !== '' ? date_i18n('M j, Y', strtotime($order['date'])) : ''; ?>
                    <?php
                      $payment_label = 'n/a';
                      if (($order['payment_status'] ?? '') === 'paid') {
                        $payment_label = 'Paid';
                      } elseif (($order['payment_status'] ?? '') === 'partial') {
                        $payment_label = 'Partial (' . $format_money((float) ($order['due_amount'] ?? 0)) . ' due)';
                      } elseif (($order['payment_status'] ?? '') === 'due') {
                        $payment_label = 'Due ' . $format_money((float) ($order['due_amount'] ?? 0));
                      } elseif (($order['payment_status'] ?? '') === 'not-set') {
                        $payment_label = 'Not set';
                      }
                    ?>
                    <tr>
                      <td>
                        <?php if (($order['raw_id'] ?? '') !== ''): ?>
                          <a href="<?php echo esc_url(add_query_arg(['view' => 'order-detail', 'order_id' => $order['raw_id']], $admin_portal_url)); ?>"><?php echo esc_html($order['id']); ?></a>
                        <?php else: ?>
                          <?php echo esc_html($order['id']); ?>
                        <?php endif; ?>
                      </td>
                      <td><?php echo esc_html($order['customer']); ?></td>
                      <td><?php echo esc_html($order['service']); ?></td>
                      <td><?php echo esc_html($order['address']); ?></td>
                      <td><span class="status-pill <?php echo esc_attr($status_class($order['status'])); ?>"><?php echo esc_html($status_label($order['status'])); ?></span></td>
                      <td><?php echo esc_html($format_money((float) ($order['total_amount'] ?? 0))); ?></td>
                      <td><?php echo esc_html($payment_label); ?></td>
                      <td><?php echo esc_html($date_label); ?></td>
                      <td>
                        <?php if (($order['raw_id'] ?? '') !== ''): ?>
                          <a class="btn btn--secondary" href="<?php echo esc_url(add_query_arg(['view' => 'order-detail', 'order_id' => $order['raw_id']], $admin_portal_url)); ?>">View</a>
                          <?php if ((float) ($order['due_amount'] ?? 0) > 0.009 && (string) ($order['payment_url'] ?? '') !== ''): ?>
                            <a class="btn btn--secondary" href="<?php echo esc_url((string) $order['payment_url']); ?>" target="_blank" rel="noopener">Pay</a>
                          <?php endif; ?>
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

          <?php if ($orders_error === null && !empty($orders)): ?>
            <div class="portal-pagination">
              <p class="portal-pagination__summary">
                Showing <?php echo esc_html((string) $showing_from); ?>-<?php echo esc_html((string) $showing_to); ?>
                of <?php echo esc_html((string) $total_filtered_orders); ?> order<?php echo $total_filtered_orders === 1 ? '' : 's'; ?>
              </p>
              <?php if ($total_order_pages > 1): ?>
                <div class="portal-pagination__actions">
                  <?php if ($orders_page > 1): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url($all_jobs_page_url($orders_page - 1)); ?>">Previous</a>
                  <?php else: ?>
                    <span class="btn btn--secondary is-disabled" aria-disabled="true">Previous</span>
                  <?php endif; ?>

                  <span class="portal-pagination__page">Page <?php echo esc_html((string) $orders_page); ?> of <?php echo esc_html((string) $total_order_pages); ?></span>

                  <?php if ($orders_page < $total_order_pages): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url($all_jobs_page_url($orders_page + 1)); ?>">Next</a>
                  <?php else: ?>
                    <span class="btn btn--secondary is-disabled" aria-disabled="true">Next</span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </section>
      <?php endif; ?>

      <?php if ($view === 'memberships'): ?>
        <section class="portal-section">
          <h1>Memberships</h1>
          <p class="sub">Monitor active members, credit balances, overages, and Square membership actions.</p>
        </section>

        <?php if ($membership_notice !== ''): ?>
          <section class="portal-tableCard">
            <div style="padding:12px 16px;">
              <?php
                $membership_notice_map = [
                  'credit-adjusted' => ['ok', 'Credit adjustment saved.'],
                  'credit-adjust-failed' => ['warn', 'Credit adjustment failed. Check the credit key and amount.'],
                  'flag-resolved' => ['ok', 'Member credit flag marked as resolved.'],
                  'flag-resolve-failed' => ['warn', 'Could not resolve the selected flag.'],
                  'credits-synced' => ['ok', 'Member credit entitlement sync was run.'],
                  'credits-sync-failed' => ['warn', 'Member credit sync failed. Check the subscription mapping and logs.'],
                  'membership-cancel-scheduled' => ['ok', 'Square membership cancellation was scheduled.'],
                  'membership-cancel-blocked' => ['warn', 'Membership cancellation is blocked until the commitment period ends.'],
                  'membership-cancel-failed' => ['warn', 'Membership cancellation failed. Check Square configuration and logs.'],
                  'membership-action-failed' => ['warn', 'Membership action failed. Please try again.'],
                ];
                $notice_meta = $membership_notice_map[$membership_notice] ?? ['warn', 'Membership action completed with an unknown result.'];
              ?>
              <p class="portal-notice portal-notice--<?php echo esc_attr($notice_meta[0]); ?>"><?php echo esc_html($notice_meta[1]); ?></p>
            </div>
          </section>
        <?php endif; ?>

        <section class="portal-tableCard" data-server-filtered="1">
          <div class="portal-tableCard__head">
            <h2>Member Accounts</h2>
            <a href="<?php echo esc_url($memberships_reset_url); ?>">Reset</a>
          </div>

          <form class="table-controls table-controls--server" method="get" action="<?php echo esc_url($admin_portal_url); ?>">
            <input type="hidden" name="view" value="memberships">
            <div class="table-control">
              <label for="memberships_q">Search</label>
              <input id="memberships_q" name="memberships_q" type="search" value="<?php echo esc_attr($memberships_q); ?>" placeholder="Search member, email, tier, term">
            </div>
            <div class="table-control table-control--action">
              <label for="memberships_apply">Apply</label>
              <button id="memberships_apply" type="submit" class="table-reset btn btn--secondary">Apply</button>
            </div>
            <div class="table-control table-control--action">
              <label>Reset</label>
              <a class="table-reset btn btn--secondary" href="<?php echo esc_url($memberships_reset_url); ?>">Reset</a>
            </div>
          </form>

          <div class="table-scroll">
            <table class="table" aria-label="Memberships">
              <thead>
                <tr>
                  <th>Client</th>
                  <th>Plan</th>
                  <th>Term</th>
                  <th>Provider</th>
                  <th>Status</th>
                  <th>Commitment End</th>
                  <th>Flags</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($memberships_rows)): ?>
                  <tr><td colspan="8">No memberships found yet.</td></tr>
                <?php elseif (empty($paged_memberships)): ?>
                  <tr><td colspan="8">No matching memberships for this search.</td></tr>
                <?php else: ?>
                  <?php foreach ($paged_memberships as $member_row): ?>
                    <?php
                      $commitment_label = 'n/a';
                      if ((int) ($member_row['commitment_end'] ?? 0) > 0) {
                        $commitment_label = date_i18n('M j, Y', (int) $member_row['commitment_end']);
                      }
                    ?>
                    <tr>
                      <td>
                        <strong><?php echo esc_html((string) ($member_row['name'] ?? '')); ?></strong><br>
                        <span class="muted"><?php echo esc_html((string) ($member_row['email'] ?? '')); ?></span>
                      </td>
                      <td><?php echo esc_html((string) ($member_row['plan_label'] ?? 'n/a')); ?></td>
                      <td><?php echo esc_html((string) (($member_row['term_label'] ?? '') !== '' ? $member_row['term_label'] : 'n/a')); ?></td>
                      <td><?php echo esc_html(strtoupper((string) ($member_row['provider'] ?? ''))); ?></td>
                      <td><?php echo esc_html((string) ($member_row['status_label'] ?? 'Unknown')); ?></td>
                      <td><?php echo esc_html($commitment_label); ?></td>
                      <td><?php echo esc_html((string) (int) ($member_row['open_flag_count'] ?? 0)); ?></td>
                      <td>
                        <a class="btn btn--secondary" href="<?php echo esc_url(add_query_arg(['view' => 'memberships', 'member_user_id' => (string) ($member_row['user_id'] ?? 0)], $admin_portal_url)); ?>">View</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if (!empty($memberships_rows)): ?>
            <div class="portal-pagination">
              <p class="portal-pagination__summary">
                Showing <?php echo esc_html((string) $memberships_showing_from); ?>-<?php echo esc_html((string) $memberships_showing_to); ?>
                of <?php echo esc_html((string) $total_memberships); ?> membership record<?php echo $total_memberships === 1 ? '' : 's'; ?>
              </p>
              <?php if ($total_membership_pages > 1): ?>
                <div class="portal-pagination__actions">
                  <?php if ($memberships_page > 1): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url($memberships_page_url($memberships_page - 1)); ?>">Previous</a>
                  <?php else: ?>
                    <span class="btn btn--secondary is-disabled" aria-disabled="true">Previous</span>
                  <?php endif; ?>
                  <span class="portal-pagination__page">Page <?php echo esc_html((string) $memberships_page); ?> of <?php echo esc_html((string) $total_membership_pages); ?></span>
                  <?php if ($memberships_page < $total_membership_pages): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url($memberships_page_url($memberships_page + 1)); ?>">Next</a>
                  <?php else: ?>
                    <span class="btn btn--secondary is-disabled" aria-disabled="true">Next</span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </section>

        <?php if (!empty($selected_member_detail) && is_array($selected_member_detail)): ?>
          <?php
            $member_user = $selected_member_detail['user'] ?? null;
            $member_sub = is_array($selected_member_detail['subscription'] ?? null) ? $selected_member_detail['subscription'] : [];
            $member_balances = is_array($selected_member_detail['balances'] ?? null) ? $selected_member_detail['balances'] : ['balances' => []];
            $member_ledger = is_array($selected_member_detail['ledger'] ?? null) ? $selected_member_detail['ledger'] : [];
            $member_flags = is_array($selected_member_detail['flags'] ?? null) ? $selected_member_detail['flags'] : [];
            $member_user_id = ($member_user instanceof WP_User) ? (int) $member_user->ID : $selected_member_user_id;
            $member_plan_arr = is_array($member_sub['plan'] ?? null) ? $member_sub['plan'] : [];
            $member_plan_label = (string) ($member_plan_arr['label'] ?? ($member_sub['plan_slug'] ?? 'n/a'));
          ?>
          <section class="portal-tableCard">
            <div class="portal-tableCard__head">
              <h2>Member Detail<?php if ($member_user instanceof WP_User): ?>: <?php echo esc_html((string) $member_user->display_name); ?><?php endif; ?></h2>
              <a href="<?php echo esc_url(add_query_arg('view', 'memberships', $admin_portal_url)); ?>">Back to Memberships</a>
            </div>
            <div class="order-detail" style="padding:16px;">
              <div class="order-detail__summary">
                <article class="portal-card"><p>Plan</p><strong><?php echo esc_html($member_plan_label); ?></strong></article>
                <article class="portal-card"><p>Term</p><strong><?php echo esc_html((string) (($member_sub['term_label'] ?? '') !== '' ? $member_sub['term_label'] : 'n/a')); ?></strong></article>
                <article class="portal-card"><p>Status</p><strong><?php echo esc_html(function_exists('slm_subscriptions_status_label') ? slm_subscriptions_status_label((string) ($member_sub['status'] ?? '')) : (string) ($member_sub['status'] ?? '')); ?></strong></article>
                <article class="portal-card"><p>Provider</p><strong><?php echo esc_html(strtoupper((string) ($member_sub['provider'] ?? ''))); ?></strong></article>
              </div>

              <div class="order-detail__grid">
                <article class="portal-card">
                  <h2>Credit Balances</h2>
                  <div class="table-scroll">
                    <table class="table" aria-label="Member Credit Balances">
                      <thead><tr><th>Credit</th><th>Remaining</th></tr></thead>
                      <tbody>
                        <?php foreach ((array) ($member_balances['balances'] ?? []) as $balance): ?>
                          <?php if (!is_array($balance)) continue; ?>
                          <tr>
                            <td><?php echo esc_html((string) ($balance['label'] ?? 'Credit')); ?></td>
                            <td><?php echo esc_html((string) (int) ($balance['qty'] ?? 0)); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </article>

                <article class="portal-card">
                  <h2>Adjust Credits</h2>
                  <form method="post" action="<?php echo esc_url(add_query_arg(['view' => 'memberships', 'member_user_id' => (string) $member_user_id], $admin_portal_url)); ?>">
                    <?php wp_nonce_field('slm_memberships_action', 'slm_memberships_nonce'); ?>
                    <input type="hidden" name="slm_memberships_action" value="adjust-credit">
                    <input type="hidden" name="member_user_id" value="<?php echo esc_attr((string) $member_user_id); ?>">
                    <p><label for="credit_key"><strong>Credit</strong></label><br>
                    <select id="credit_key" name="credit_key" style="width:100%;">
                      <?php foreach ((function_exists('slm_member_credits_key_labels') ? slm_member_credits_key_labels() : []) as $credit_key => $credit_label): ?>
                        <option value="<?php echo esc_attr((string) $credit_key); ?>"><?php echo esc_html((string) $credit_label); ?></option>
                      <?php endforeach; ?>
                    </select></p>
                    <p><label for="quantity_delta"><strong>Quantity Delta</strong></label><br>
                    <input id="quantity_delta" type="number" name="quantity_delta" value="1" step="1"></p>
                    <p><label for="adjust_note"><strong>Note</strong></label><br>
                    <input id="adjust_note" type="text" name="adjust_note" value="" style="width:100%;"></p>
                    <button type="submit" class="btn btn--secondary">Save Adjustment</button>
                  </form>
                </article>

                <article class="portal-card">
                  <h2>Actions</h2>
                  <form method="post" action="<?php echo esc_url(add_query_arg(['view' => 'memberships', 'member_user_id' => (string) $member_user_id], $admin_portal_url)); ?>" style="margin-bottom:10px;">
                    <?php wp_nonce_field('slm_memberships_action', 'slm_memberships_nonce'); ?>
                    <input type="hidden" name="slm_memberships_action" value="sync-member-credits">
                    <input type="hidden" name="member_user_id" value="<?php echo esc_attr((string) $member_user_id); ?>">
                    <button type="submit" class="btn btn--secondary">Re-run Entitlement Sync</button>
                  </form>
                  <?php if ((string) ($member_sub['provider'] ?? '') === 'square'): ?>
                    <?php $member_commitment_active = ((int) ($member_sub['commitment_end'] ?? 0) > time()); ?>
                    <form method="post" action="<?php echo esc_url(add_query_arg(['view' => 'memberships', 'member_user_id' => (string) $member_user_id], $admin_portal_url)); ?>" onsubmit="return confirm('Schedule Square cancellation for this member if eligible?');">
                      <?php wp_nonce_field('slm_memberships_action', 'slm_memberships_nonce'); ?>
                      <input type="hidden" name="slm_memberships_action" value="cancel-membership">
                      <input type="hidden" name="member_user_id" value="<?php echo esc_attr((string) $member_user_id); ?>">
                      <button type="submit" class="btn btn--secondary"<?php echo $member_commitment_active ? ' disabled aria-disabled="true"' : ''; ?>>Schedule Cancel (Square)</button>
                    </form>
                    <?php if ($member_commitment_active): ?>
                      <p class="sub" style="margin:8px 0 0;">Commitment active until <?php echo esc_html(date_i18n('M j, Y', (int) $member_sub['commitment_end'])); ?>.</p>
                    <?php endif; ?>
                  <?php else: ?>
                    <p>Square cancellation action is only available for Square memberships.</p>
                  <?php endif; ?>
                </article>

                <article class="portal-card">
                  <h2>Open Flags</h2>
                  <?php if (empty($member_flags)): ?>
                    <p>No open overage or unmatched-service flags.</p>
                  <?php else: ?>
                    <ul class="order-detail__items">
                      <?php foreach ($member_flags as $flag_row): ?>
                        <?php if (!is_array($flag_row)) continue; ?>
                        <li>
                          <strong><?php echo esc_html((string) ($flag_row['flag_type'] ?? 'flag')); ?></strong>: <?php echo esc_html((string) ($flag_row['message'] ?? '')); ?>
                          <form method="post" action="<?php echo esc_url(add_query_arg(['view' => 'memberships', 'member_user_id' => (string) $member_user_id], $admin_portal_url)); ?>" style="display:inline-block; margin-left:8px;">
                            <?php wp_nonce_field('slm_memberships_action', 'slm_memberships_nonce'); ?>
                            <input type="hidden" name="slm_memberships_action" value="resolve-credit-flag">
                            <input type="hidden" name="member_user_id" value="<?php echo esc_attr((string) $member_user_id); ?>">
                            <input type="hidden" name="flag_id" value="<?php echo esc_attr((string) (int) ($flag_row['id'] ?? 0)); ?>">
                            <button type="submit" class="btn btn--secondary">Resolve</button>
                          </form>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </article>
              </div>

              <article class="portal-card">
                <h2>Recent Credit Ledger</h2>
                <div class="table-scroll">
                  <table class="table" aria-label="Member Credit Ledger">
                    <thead>
                      <tr><th>Time (GMT)</th><th>Type</th><th>Credit</th><th>Qty</th><th>Source</th><th>Ref</th></tr>
                    </thead>
                    <tbody>
                      <?php if (empty($member_ledger)): ?>
                        <tr><td colspan="6">No credit ledger transactions yet.</td></tr>
                      <?php else: ?>
                        <?php foreach ($member_ledger as $ledger_row): ?>
                          <?php if (!is_array($ledger_row)) continue; ?>
                          <tr>
                            <td><?php echo esc_html((string) ($ledger_row['created_at_gmt'] ?? '')); ?></td>
                            <td><?php echo esc_html((string) ($ledger_row['txn_type'] ?? '')); ?></td>
                            <td><?php echo esc_html((string) ($ledger_row['credit_key'] ?? '')); ?></td>
                            <td><?php echo esc_html((string) (int) ($ledger_row['quantity_delta'] ?? 0)); ?></td>
                            <td><?php echo esc_html((string) ($ledger_row['source'] ?? '')); ?></td>
                            <td><?php echo esc_html((string) ($ledger_row['source_ref'] ?? '')); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </article>
            </div>
          </section>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($view === 'clients'): ?>
        <section class="portal-section">
          <h1>Clients</h1>
          <p class="sub">Manage WordPress client accounts and jump to the standard user editor when changes are needed.</p>
        </section>

        <section class="portal-tableCard" data-server-filtered="1">
          <div class="portal-tableCard__head">
            <h2>Client Accounts</h2>
            <a href="<?php echo esc_url($clients_reset_url); ?>">Reset Search</a>
          </div>

          <form class="table-controls table-controls--server" method="get" action="<?php echo esc_url($admin_portal_url); ?>">
            <input type="hidden" name="view" value="clients">

            <div class="table-control">
              <label for="clients_q">Search</label>
              <input id="clients_q" name="clients_q" type="search" value="<?php echo esc_attr($clients_q); ?>" placeholder="Search name, email, phone, brokerage">
            </div>

            <div class="table-control table-control--action">
              <label for="clients_apply">Apply</label>
              <button id="clients_apply" type="submit" class="table-reset btn btn--secondary">Apply</button>
            </div>

            <div class="table-control table-control--action">
              <label>Reset</label>
              <a class="table-reset btn btn--secondary" href="<?php echo esc_url($clients_reset_url); ?>">Reset</a>
            </div>
          </form>

          <div class="table-scroll">
            <table class="table" aria-label="Client Accounts">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Brokerage</th>
                  <th>Registered</th>
                  <th>Roles</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($clients_rows)): ?>
                  <tr><td colspan="7">No users found yet.</td></tr>
                <?php elseif (empty($paged_clients)): ?>
                  <tr><td colspan="7">No matching client accounts for this search.</td></tr>
                <?php else: ?>
                  <?php foreach ($paged_clients as $client_row): ?>
                    <?php
                      $registered_label = '';
                      if ((string) ($client_row['registered'] ?? '') !== '') {
                        $timestamp = strtotime((string) $client_row['registered']);
                        if ($timestamp) {
                          $registered_label = date_i18n('M j, Y', $timestamp);
                        }
                      }
                      $roles = array_values(array_map('strval', (array) ($client_row['roles'] ?? [])));
                      $role_label = !empty($roles) ? implode(', ', $roles) : 'n/a';
                      if (!empty($client_row['is_admin'])) {
                        $role_label .= ' (admin)';
                      }
                    ?>
                    <tr>
                      <td><?php echo esc_html((string) ($client_row['name'] ?? '')); ?></td>
                      <td><?php echo esc_html((string) ($client_row['email'] ?? '')); ?></td>
                      <td><?php echo esc_html((string) (($client_row['phone'] ?? '') !== '' ? $client_row['phone'] : 'n/a')); ?></td>
                      <td><?php echo esc_html((string) (($client_row['brokerage'] ?? '') !== '' ? $client_row['brokerage'] : 'n/a')); ?></td>
                      <td><?php echo esc_html($registered_label !== '' ? $registered_label : 'n/a'); ?></td>
                      <td><?php echo esc_html($role_label); ?></td>
                      <td>
                        <a class="btn btn--secondary" href="<?php echo esc_url((string) ($client_row['edit_url'] ?? '')); ?>">Edit User</a>
                        <a class="btn btn--secondary" href="<?php echo esc_url((string) ($client_row['portal_url'] ?? '')); ?>" target="_blank" rel="noopener">Open Portal</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if (!empty($clients_rows)): ?>
            <div class="portal-pagination">
              <p class="portal-pagination__summary">
                Showing <?php echo esc_html((string) $clients_showing_from); ?>-<?php echo esc_html((string) $clients_showing_to); ?>
                of <?php echo esc_html((string) $total_clients); ?> user<?php echo $total_clients === 1 ? '' : 's'; ?>
              </p>
              <?php if ($total_client_pages > 1): ?>
                <div class="portal-pagination__actions">
                  <?php if ($clients_page > 1): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url($clients_page_url($clients_page - 1)); ?>">Previous</a>
                  <?php else: ?>
                    <span class="btn btn--secondary is-disabled" aria-disabled="true">Previous</span>
                  <?php endif; ?>

                  <span class="portal-pagination__page">Page <?php echo esc_html((string) $clients_page); ?> of <?php echo esc_html((string) $total_client_pages); ?></span>

                  <?php if ($clients_page < $total_client_pages): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url($clients_page_url($clients_page + 1)); ?>">Next</a>
                  <?php else: ?>
                    <span class="btn btn--secondary is-disabled" aria-disabled="true">Next</span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </section>
      <?php endif; ?>

      <?php if ($view === 'order-detail'): ?>
        <section class="portal-section">
          <h1>Order Detail</h1>
          <p class="sub">Track delivery and payment status for a specific order.</p>
        </section>

        <?php if ($order_notice !== ''): ?>
          <section class="portal-tableCard">
            <div style="padding: 12px 16px;">
              <?php if ($order_notice === 'completion-email-sent'): ?>
                <p class="portal-notice portal-notice--ok">Completion email sent to the customer.</p>
              <?php elseif ($order_notice === 'completion-email-duplicate'): ?>
                <p class="portal-notice portal-notice--warn">A completion email was already sent recently for this order.</p>
              <?php elseif ($order_notice === 'completion-email-missing-email'): ?>
                <p class="portal-notice portal-notice--warn">Cannot send completion email because the order is missing a customer email.</p>
              <?php elseif ($order_notice === 'completion-email-not-ready'): ?>
                <p class="portal-notice portal-notice--warn">Order is not ready for a completion email yet.</p>
              <?php elseif ($order_notice === 'completion-email-failed'): ?>
                <p class="portal-notice portal-notice--warn">Completion email could not be sent. Check mail configuration and order details.</p>
              <?php endif; ?>
            </div>
          </section>
        <?php endif; ?>

        <?php if ($selected_order === null): ?>
          <section class="portal-tableCard">
            <div class="portal-tableCard__head">
              <h2>Order Not Found</h2>
              <a href="<?php echo esc_url(add_query_arg('view', 'all-jobs', $admin_portal_url)); ?>">Back to Orders</a>
            </div>
            <div style="padding: 0 16px 16px;">
              <p style="margin: 0;">We could not find that order. Try opening it again from the All Orders list.</p>
            </div>
          </section>
        <?php else: ?>
          <?php
            $timeline = [];
            $completion_email_recipient = trim((string) ($selected_order['customer_email'] ?? ''));
            $completion_email_ready = ((string) ($selected_order['status'] ?? '') === 'completed') || trim((string) ($selected_order['delivery_url'] ?? '')) !== '';
            if ((string) ($selected_order['created_at'] ?? '') !== '') {
              $timeline[] = ['label' => 'Order Created', 'date' => (string) $selected_order['created_at']];
            }
            if ((string) ($selected_order['appointment_at'] ?? '') !== '') {
              $timeline[] = ['label' => 'Appointment Scheduled', 'date' => (string) $selected_order['appointment_at']];
            }
            if ((string) ($selected_order['delivery_at'] ?? '') !== '') {
              $timeline[] = ['label' => 'Delivered', 'date' => (string) $selected_order['delivery_at']];
            }
            if ((string) ($selected_order['updated_at'] ?? '') !== '') {
              $timeline[] = ['label' => 'Last Updated', 'date' => (string) $selected_order['updated_at']];
            }
          ?>

          <section class="portal-tableCard">
            <div class="portal-tableCard__head">
              <h2>Order #<?php echo esc_html((string) $selected_order['id']); ?></h2>
              <a href="<?php echo esc_url(add_query_arg('view', 'all-jobs', $admin_portal_url)); ?>">Back to Orders</a>
            </div>
            <div class="order-detail">
              <div class="order-detail__summary">
                <article class="portal-card">
                  <p>Status</p>
                  <strong><span class="status-pill <?php echo esc_attr($status_class((string) $selected_order['status'])); ?>"><?php echo esc_html($status_label((string) $selected_order['status'])); ?></span></strong>
                </article>
                <article class="portal-card">
                  <p>Total</p>
                  <strong><?php echo esc_html($format_money((float) ($selected_order['total_amount'] ?? 0))); ?></strong>
                </article>
                <article class="portal-card">
                  <p>Paid</p>
                  <strong><?php echo esc_html($format_money((float) ($selected_order['paid_amount'] ?? 0))); ?></strong>
                </article>
                <article class="portal-card">
                  <p>Due</p>
                  <strong><?php echo esc_html($format_money((float) ($selected_order['due_amount'] ?? 0))); ?></strong>
                </article>
              </div>

              <div class="order-detail__grid">
                <article class="portal-card">
                  <h2>Customer</h2>
                  <p><strong><?php echo esc_html((string) $selected_order['customer']); ?></strong></p>
                  <p>Email: <?php echo esc_html((string) ($selected_order['customer_email'] ?: 'n/a')); ?></p>
                  <p>Phone: <?php echo esc_html((string) ($selected_order['customer_phone'] ?: 'n/a')); ?></p>
                </article>

                <article class="portal-card">
                  <h2>Listing & Service</h2>
                  <p>Address: <?php echo esc_html((string) ($selected_order['address'] ?: 'n/a')); ?></p>
                  <p>Primary Service: <?php echo esc_html((string) $selected_order['service']); ?></p>
                  <?php if (!empty($selected_order['items']) && is_array($selected_order['items'])): ?>
                    <p style="margin-bottom: 6px;">Items:</p>
                    <ul class="order-detail__items">
                      <?php foreach ($selected_order['items'] as $item_label): ?>
                        <li><?php echo esc_html((string) $item_label); ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </article>

                <article class="portal-card">
                  <h2>Delivery</h2>
                  <p>Delivered: <?php echo esc_html($format_datetime((string) ($selected_order['delivery_at'] ?? '')) ?: 'n/a'); ?></p>
                  <p>Appointment: <?php echo esc_html($format_datetime((string) ($selected_order['appointment_at'] ?? '')) ?: 'n/a'); ?></p>
                  <?php if ((string) ($selected_order['delivery_url'] ?? '') !== ''): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url((string) $selected_order['delivery_url']); ?>" target="_blank" rel="noopener">Open Delivery Link</a>
                  <?php endif; ?>
                </article>

                <article class="portal-card">
                  <h2>Payment</h2>
                  <p>Total: <?php echo esc_html($format_money((float) ($selected_order['total_amount'] ?? 0))); ?></p>
                  <p>Paid: <?php echo esc_html($format_money((float) ($selected_order['paid_amount'] ?? 0))); ?></p>
                  <p>Due: <?php echo esc_html($format_money((float) ($selected_order['due_amount'] ?? 0))); ?></p>
                  <p>Payment Status: <?php echo esc_html(ucwords(str_replace('-', ' ', (string) ($selected_order['payment_status'] ?? 'unknown')))); ?></p>
                  <?php if ((float) ($selected_order['due_amount'] ?? 0) > 0.009 && (string) ($selected_order['payment_url'] ?? '') !== ''): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url((string) $selected_order['payment_url']); ?>" target="_blank" rel="noopener">Open Payment Link</a>
                  <?php endif; ?>
                </article>

                <article class="portal-card">
                  <h2>Customer Notifications</h2>
                  <p>Customer Email: <?php echo esc_html($completion_email_recipient !== '' ? $completion_email_recipient : 'n/a'); ?></p>
                  <?php if ($completion_email_recipient === ''): ?>
                    <p>Completion email unavailable until a customer email is present on the order.</p>
                  <?php elseif (!$completion_email_ready): ?>
                    <p>Order is not ready for completion email yet. Delivery link or completed status is required.</p>
                  <?php else: ?>
                    <p>Send the customer a completion update email from WordPress (delivery link is only included when fully paid).</p>
                    <form method="post" action="<?php echo esc_url(add_query_arg(['view' => 'order-detail', 'order_id' => (string) ($selected_order['raw_id'] ?? '')], $admin_portal_url)); ?>">
                      <?php wp_nonce_field('slm_order_action', 'slm_order_nonce'); ?>
                      <input type="hidden" name="slm_order_action" value="send-completion-email">
                      <input type="hidden" name="order_id" value="<?php echo esc_attr((string) ($selected_order['raw_id'] ?? '')); ?>">
                      <button type="submit" class="btn btn--secondary">Send Completion Email</button>
                    </form>
                  <?php endif; ?>
                </article>
              </div>

              <article class="portal-card">
                <h2>Timeline</h2>
                <?php if (empty($timeline)): ?>
                  <p>No timeline entries available yet.</p>
                <?php else: ?>
                  <ul class="order-timeline">
                    <?php foreach ($timeline as $event): ?>
                      <li>
                        <span><?php echo esc_html((string) ($event['label'] ?? 'Update')); ?></span>
                        <strong><?php echo esc_html($format_datetime((string) ($event['date'] ?? '')) ?: 'n/a'); ?></strong>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </article>
            </div>
          </section>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($view === 'notifications'): ?>
        <section class="portal-section">
          <h1>Notifications</h1>
          <p class="sub">Monitor account/order notification events and delivery status.</p>
        </section>

        <?php if ($notifications_notice === 'cleared'): ?>
          <section class="portal-tableCard">
            <div style="padding: 12px 16px;">
              <p class="portal-notice portal-notice--ok">Notification log cleared.</p>
            </div>
          </section>
        <?php elseif ($notifications_notice === 'test-sent'): ?>
          <section class="portal-tableCard">
            <div style="padding: 12px 16px;">
              <p class="portal-notice portal-notice--ok">Test notification sent.</p>
            </div>
          </section>
        <?php elseif ($notifications_notice === 'test-failed'): ?>
          <section class="portal-tableCard">
            <div style="padding: 12px 16px;">
              <p class="portal-notice portal-notice--warn">Test notification failed. Check recipient email and mail delivery setup.</p>
            </div>
          </section>
        <?php endif; ?>

        <section class="portal-tableCard" data-server-filtered="1">
          <div class="portal-tableCard__head">
            <h2>Notification Log</h2>
            <a href="<?php echo esc_url($notifications_reset_url); ?>">Reset Filters</a>
          </div>

          <div class="notifications-actions">
            <form method="post" action="<?php echo esc_url(add_query_arg('view', 'notifications', $admin_portal_url)); ?>">
              <?php wp_nonce_field('slm_notifications_action', 'slm_notifications_nonce'); ?>
              <input type="hidden" name="slm_notifications_action" value="send-test">
              <button type="submit" class="btn btn--secondary">Send Test Email</button>
            </form>

            <form method="post" action="<?php echo esc_url(add_query_arg('view', 'notifications', $admin_portal_url)); ?>" onsubmit="return confirm('Clear notification log?');">
              <?php wp_nonce_field('slm_notifications_action', 'slm_notifications_nonce'); ?>
              <input type="hidden" name="slm_notifications_action" value="clear-log">
              <button type="submit" class="btn btn--secondary">Clear Log</button>
            </form>
          </div>

          <form class="table-controls table-controls--server" method="get" action="<?php echo esc_url($admin_portal_url); ?>">
            <input type="hidden" name="view" value="notifications">

            <div class="table-control">
              <label for="notifications_q">Search</label>
              <input id="notifications_q" name="notifications_q" type="search" value="<?php echo esc_attr($notifications_q); ?>" placeholder="Search subject, event, order, recipient">
            </div>

            <div class="table-control">
              <label for="notifications_status">Status</label>
              <select id="notifications_status" name="notifications_status">
                <option value="all" <?php selected($notifications_status, 'all'); ?>>All statuses</option>
                <option value="sent" <?php selected($notifications_status, 'sent'); ?>>Sent</option>
                <option value="failed-send" <?php selected($notifications_status, 'failed-send'); ?>>Failed</option>
                <option value="skipped-disabled" <?php selected($notifications_status, 'skipped-disabled'); ?>>Skipped (Disabled)</option>
                <option value="skipped-invalid-recipient" <?php selected($notifications_status, 'skipped-invalid-recipient'); ?>>Skipped (Bad Recipient)</option>
                <option value="skipped-empty-body" <?php selected($notifications_status, 'skipped-empty-body'); ?>>Skipped (Empty Body)</option>
                <option value="skipped-duplicate" <?php selected($notifications_status, 'skipped-duplicate'); ?>>Skipped (Duplicate)</option>
                <option value="skipped-not-ready" <?php selected($notifications_status, 'skipped-not-ready'); ?>>Skipped (Not Ready)</option>
              </select>
            </div>

            <div class="table-control">
              <label for="notifications_type">Type</label>
              <select id="notifications_type" name="notifications_type">
                <option value="all" <?php selected($notifications_type, 'all'); ?>>All types</option>
                <option value="aryeo-webhook" <?php selected($notifications_type, 'aryeo-webhook'); ?>>Aryeo Webhook</option>
                <option value="account-created" <?php selected($notifications_type, 'account-created'); ?>>Account Created</option>
                <option value="manual-test" <?php selected($notifications_type, 'manual-test'); ?>>Manual Test</option>
                <option value="customer-submission" <?php selected($notifications_type, 'customer-submission'); ?>>Customer Submission</option>
                <option value="customer-completion" <?php selected($notifications_type, 'customer-completion'); ?>>Customer Completion</option>
              </select>
            </div>

            <div class="table-control table-control--action">
              <label for="notifications_apply">Apply</label>
              <button id="notifications_apply" type="submit" class="table-reset btn btn--secondary">Apply Filters</button>
            </div>
          </form>

          <div class="table-scroll">
            <table class="table" aria-label="Notification Log">
              <thead>
                <tr>
                  <th>Time</th>
                  <th>Type</th>
                  <th>Event</th>
                  <th>Subject</th>
                  <th>Recipient</th>
                  <th>Status</th>
                  <th>Order</th>
                  <th>Details</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($notification_rows)): ?>
                  <tr><td colspan="8">No notifications logged yet.</td></tr>
                <?php elseif (empty($paged_notifications)): ?>
                  <tr><td colspan="8">No matching notifications for current filters.</td></tr>
                <?php else: ?>
                  <?php foreach ($paged_notifications as $row): ?>
                    <?php
                      $row_time = (string) ($row['timestamp_local'] ?? '');
                      $row_type = (string) ($row['type'] ?? '');
                      $row_event = (string) ($row['event'] ?? '');
                      $row_status = (string) ($row['status'] ?? '');
                      $row_subject = (string) ($row['subject'] ?? '');
                      $row_recipient = (string) ($row['recipient'] ?? '');
                      $row_order_id = (string) ($row['order_id'] ?? '');
                      $row_order_number = (string) ($row['order_number'] ?? '');
                      $row_details = (string) ($row['details'] ?? '');
                      $status_slug = sanitize_html_class('is-' . str_replace('_', '-', strtolower($row_status)));
                    ?>
                    <tr>
                      <td><?php echo esc_html($row_time !== '' ? $row_time : 'n/a'); ?></td>
                      <td><?php echo esc_html($row_type !== '' ? $row_type : 'n/a'); ?></td>
                      <td><?php echo esc_html($row_event !== '' ? $row_event : 'n/a'); ?></td>
                      <td><?php echo esc_html($row_subject !== '' ? $row_subject : 'n/a'); ?></td>
                      <td><?php echo esc_html($row_recipient !== '' ? $row_recipient : 'n/a'); ?></td>
                      <td><span class="notif-pill <?php echo esc_attr($status_slug); ?>"><?php echo esc_html($row_status !== '' ? $row_status : 'unknown'); ?></span></td>
                      <td>
                        <?php if ($row_order_id !== ''): ?>
                          <a href="<?php echo esc_url(add_query_arg(['view' => 'order-detail', 'order_id' => $row_order_id], $admin_portal_url)); ?>">
                            <?php echo esc_html($row_order_number !== '' ? $row_order_number : $row_order_id); ?>
                          </a>
                        <?php else: ?>
                          <span class="muted">n/a</span>
                        <?php endif; ?>
                      </td>
                      <td><?php echo esc_html($row_details !== '' ? $row_details : ''); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if (!empty($notification_rows)): ?>
            <div class="portal-pagination">
              <p class="portal-pagination__summary">
                Showing <?php echo esc_html((string) $notifications_showing_from); ?>-<?php echo esc_html((string) $notifications_showing_to); ?>
                of <?php echo esc_html((string) $total_notifications); ?> notification<?php echo $total_notifications === 1 ? '' : 's'; ?>
              </p>
              <?php if ($total_notification_pages > 1): ?>
                <div class="portal-pagination__actions">
                  <?php if ($notifications_page > 1): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url($notifications_page_url($notifications_page - 1)); ?>">Previous</a>
                  <?php else: ?>
                    <span class="btn btn--secondary is-disabled" aria-disabled="true">Previous</span>
                  <?php endif; ?>

                  <span class="portal-pagination__page">Page <?php echo esc_html((string) $notifications_page); ?> of <?php echo esc_html((string) $total_notification_pages); ?></span>

                  <?php if ($notifications_page < $total_notification_pages): ?>
                    <a class="btn btn--secondary" href="<?php echo esc_url($notifications_page_url($notifications_page + 1)); ?>">Next</a>
                  <?php else: ?>
                    <span class="btn btn--secondary is-disabled" aria-disabled="true">Next</span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </section>
      <?php endif; ?>

      <?php if ($view === 'account'): ?>
        <section class="portal-section">
          <h1>Admin Account</h1>
          <p class="sub">Access profile and security settings for your admin account.</p>
        </section>
        <section class="portal-account">
          <article class="portal-card">
            <h2>Administrator Profile</h2>
            <p>Use WordPress profile settings to update password, contact details, and preferences.</p>
            <a class="btn" href="<?php echo esc_url(admin_url('profile.php')); ?>">Open Admin Profile</a>
          </article>
        </section>
      <?php endif; ?>
    </div>
  </main>
</div>

<?php get_footer(); ?>
