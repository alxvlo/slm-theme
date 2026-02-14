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
$allowed_views = ['dashboard', 'all-jobs', 'account'];
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
if (function_exists('slm_aryeo_is_configured') && slm_aryeo_is_configured() && function_exists('slm_aryeo_get_recent_orders')) {
  $raw_orders = slm_aryeo_get_recent_orders(100);
  if (is_wp_error($raw_orders)) {
    $orders_error = $raw_orders->get_error_message();
  } elseif (is_array($raw_orders)) {
    foreach ($raw_orders as $order) {
      $order_id = (string) ($order['id'] ?? '');
      $order_number = (string) ($order['order_number'] ?? $order_id);

      $customer_name = '';
      $customer = $order['customer'] ?? [];
      if (is_array($customer)) {
        $customer_name = (string) ($customer['full_name'] ?? '');
        if ($customer_name === '') {
          $first = trim((string) ($customer['first_name'] ?? ''));
          $last = trim((string) ($customer['last_name'] ?? ''));
          $customer_name = trim($first . ' ' . $last);
        }
        if ($customer_name === '') {
          $customer_name = (string) ($customer['email'] ?? '');
        }
      }
      if ($customer_name === '') $customer_name = 'Customer';

      $service_name = '';
      $items = $order['items'] ?? [];
      if (is_array($items) && !empty($items)) {
        $first_item = $items[0] ?? [];
        if (is_array($first_item)) {
          $service_name = (string) ($first_item['name'] ?? $first_item['product']['name'] ?? '');
        }
      }
      if ($service_name === '') $service_name = 'Order';

      $orders[] = [
        'id' => $order_number,
        'customer' => $customer_name,
        'service' => $service_name,
        'address' => (string) ($order['listing']['address'] ?? $order['address'] ?? ''),
        'status' => $normalize_status($order['status'] ?? ''),
        'price' => (string) ($order['total'] ?? $order['total_amount'] ?? ''),
        'date' => (string) ($order['created_at'] ?? $order['updated_at'] ?? ''),
      ];
    }
  }
}

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

  $price_raw = (string) ($order['price'] ?? '');
  $price_val = (float) preg_replace('/[^0-9.]/', '', $price_raw);
  $stats['revenue'] += $price_val;
}

$stats['active_customers'] = count($customers);
$revenue_label = '$' . number_format($stats['revenue'], 0);
$recent_orders = array_slice($orders, 0, 10);

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
      <a class="<?php echo $view === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'dashboard', $admin_portal_url)); ?>">Dashboard</a>
      <a class="<?php echo $view === 'all-jobs' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'all-jobs', $admin_portal_url)); ?>">All Orders</a>
      <a class="<?php echo $view === 'account' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'account', $admin_portal_url)); ?>">Account</a>
    </nav>

    <div class="portal-sidebar__footer">
      <a class="btn btn--secondary" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
    </div>
  </aside>

  <main class="portal-main">
    <div class="portal-wrap">
      <section class="portal-toolbar" aria-label="Admin Quick Actions">
        <a class="portal-toolbar__pill <?php echo $view === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'dashboard', $admin_portal_url)); ?>">Overview</a>
        <a class="portal-toolbar__pill <?php echo $view === 'all-jobs' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'all-jobs', $admin_portal_url)); ?>">Order Queue</a>
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
                      <td><?php echo esc_html($order['id']); ?></td>
                      <td><?php echo esc_html($order['customer']); ?></td>
                      <td><?php echo esc_html($order['service']); ?></td>
                      <td><?php echo esc_html($order['address']); ?></td>
                      <td><span class="status-pill <?php echo esc_attr($status_class($order['status'])); ?>"><?php echo esc_html($status_label($order['status'])); ?></span></td>
                      <td><?php echo esc_html($order['price']); ?></td>
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
        <section class="portal-tableCard">
          <div class="table-scroll">
            <table class="table" aria-label="All Customer Orders">
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
                  <tr><td colspan="7">Unable to load orders: <?php echo esc_html($orders_error); ?></td></tr>
                <?php elseif (empty($orders)): ?>
                  <tr><td colspan="7">No customer orders available yet.</td></tr>
                <?php else: ?>
                  <?php foreach ($orders as $order): ?>
                    <?php $date_label = $order['date'] !== '' ? date_i18n('M j, Y', strtotime($order['date'])) : ''; ?>
                    <tr>
                      <td><?php echo esc_html($order['id']); ?></td>
                      <td><?php echo esc_html($order['customer']); ?></td>
                      <td><?php echo esc_html($order['service']); ?></td>
                      <td><?php echo esc_html($order['address']); ?></td>
                      <td><span class="status-pill <?php echo esc_attr($status_class($order['status'])); ?>"><?php echo esc_html($status_label($order['status'])); ?></span></td>
                      <td><?php echo esc_html($order['price']); ?></td>
                      <td><?php echo esc_html($date_label); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
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
