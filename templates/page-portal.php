<?php
/**
 * Template Name: Portal
 */
if (!defined('ABSPATH')) exit;

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
$allowed_views = ['dashboard', 'place-order', 'my-orders', 'account'];
if (!in_array($view, $allowed_views, true)) {
  $view = 'dashboard';
}

$portal_url = slm_portal_url();
$user = wp_get_current_user();
$name = $user instanceof WP_User ? $user->display_name : 'there';
$user_email = $user instanceof WP_User ? (string) $user->user_email : '';
$subscription_summary = null;
if ($user instanceof WP_User && function_exists('slm_get_user_subscription_summary')) {
  $subscription_summary = slm_get_user_subscription_summary((int) $user->ID);
}
$subscription_notice = isset($_GET['subscription']) ? sanitize_key((string) $_GET['subscription']) : '';

$recent_orders = [];
$aryeo_orders = null;
if (function_exists('slm_aryeo_is_configured') && slm_aryeo_is_configured() && $user_email !== '') {
  $aryeo_orders = slm_aryeo_get_orders_for_email($user_email);
  if (is_array($aryeo_orders)) {
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
      <a class="<?php echo $view === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'dashboard', $portal_url)); ?>">Dashboard</a>
      <a class="<?php echo $view === 'place-order' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'place-order', $portal_url)); ?>">Place Order</a>
      <a class="<?php echo $view === 'my-orders' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'my-orders', $portal_url)); ?>">My Orders</a>
      <a class="<?php echo $view === 'account' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'account', $portal_url)); ?>">Account</a>
    </nav>

    <div class="portal-sidebar__footer">
      <a class="btn btn--secondary" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
    </div>
  </aside>

  <main class="portal-main">
    <div class="portal-wrap">
      <section class="portal-toolbar" aria-label="Quick Actions">
        <a class="portal-toolbar__pill <?php echo $view === 'dashboard' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'dashboard', $portal_url)); ?>">Overview</a>
        <a class="portal-toolbar__pill <?php echo $view === 'place-order' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'place-order', $portal_url)); ?>">Start Order</a>
        <a class="portal-toolbar__pill <?php echo $view === 'my-orders' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'my-orders', $portal_url)); ?>">Track Orders</a>
        <a class="portal-toolbar__pill <?php echo $view === 'account' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('view', 'account', $portal_url)); ?>">Profile</a>
      </section>

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
            <h2>Place New Order</h2>
            <p>Order professional media services for your listing.</p>
          </a>
          <a class="portal-action" href="<?php echo esc_url(add_query_arg('view', 'my-orders', $portal_url)); ?>">
            <h2>View My Orders</h2>
            <p>Track current jobs and review completed work.</p>
          </a>
        </section>

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
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (is_wp_error($aryeo_orders)): ?>
                  <tr><td colspan="6">Unable to load orders: <?php echo esc_html($aryeo_orders->get_error_message()); ?></td></tr>
                <?php elseif (empty($recent_orders)): ?>
                  <tr><td colspan="6">No orders yet. When you place an order, it will show up here.</td></tr>
                <?php else: ?>
                  <?php foreach ($recent_orders as $order): ?>
                    <?php
                      $order_id = (string) ($order['id'] ?? '');
                      $order_number = (string) ($order['order_number'] ?? $order['number'] ?? $order_id);
                      $status_raw = $display_status($order['status'] ?? $order['order_status'] ?? '');
                      $service_name = '';
                      $items = $order['items'] ?? [];
                      if (is_array($items) && !empty($items)) {
                        $first = $items[0] ?? [];
                        if (is_array($first)) {
                          $service_name = (string) ($first['name'] ?? $first['title'] ?? $first['product']['name'] ?? $first['product']['title'] ?? '');
                        }
                      }
                      if ($service_name === '') $service_name = 'Order';
                      $address_raw = $order['listing']['address'] ?? $order['address'] ?? '';
                      if (is_array($address_raw)) {
                        $address_raw = (string) ($address_raw['full'] ?? $address_raw['formatted'] ?? $address_raw['street_address'] ?? '');
                      }
                      $address = (string) $address_raw;
                      $total = (string) ($order['total'] ?? $order['total_amount'] ?? $order['grand_total'] ?? $order['amount'] ?? '');
                      $created = (string) ($order['created_at'] ?? $order['updated_at'] ?? '');
                      $created_label = $created !== '' ? date_i18n('M j, Y', strtotime($created)) : '';
                    ?>
                    <tr>
                      <td><?php echo esc_html($order_number); ?></td>
                      <td><?php echo esc_html($service_name); ?></td>
                      <td><?php echo esc_html($address); ?></td>
                      <td><span class="status-pill <?php echo esc_attr($status_class($status_raw)); ?>"><?php echo esc_html($status_label($status_raw)); ?></span></td>
                      <td><?php echo esc_html($total !== '' ? $total : ''); ?></td>
                      <td><?php echo esc_html($created_label); ?></td>
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
          <h1>Place New Order</h1>
          <p class="sub">Start a new order. Checkout and scheduling are handled in Aryeo.</p>
        </section>

        <?php if (function_exists('slm_aryeo_is_configured') && slm_aryeo_is_configured()): ?>
          <section class="portal-actions" style="grid-template-columns: 1fr;">
            <a class="portal-action portal-action--primary" href="<?php echo esc_url(slm_aryeo_start_order_url()); ?>">
              <h2>Start New Order</h2>
              <p>Continue to our ordering flow powered by Aryeo.</p>
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
          <h1>My Orders</h1>
          <p class="sub">Track active orders and delivered media in one place.</p>
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
                  <th>Delivery</th>
                </tr>
              </thead>
              <tbody>
                <?php if (is_wp_error($aryeo_orders)): ?>
                  <tr><td colspan="6">Unable to load orders: <?php echo esc_html($aryeo_orders->get_error_message()); ?></td></tr>
                <?php elseif (empty($aryeo_orders) || !is_array($aryeo_orders)): ?>
                  <tr><td colspan="6">No orders yet. Use "Place Order" to get started.</td></tr>
                <?php else: ?>
                  <?php foreach ($aryeo_orders as $order): ?>
                    <?php
                      $order_id = (string) ($order['id'] ?? '');
                      $order_number = (string) ($order['order_number'] ?? $order['number'] ?? $order_id);
                      $status_raw = $display_status($order['status'] ?? $order['order_status'] ?? '');
                      $service_name = '';
                      $items = $order['items'] ?? [];
                      if (is_array($items) && !empty($items)) {
                        $first = $items[0] ?? [];
                        if (is_array($first)) {
                          $service_name = (string) ($first['name'] ?? $first['title'] ?? $first['product']['name'] ?? $first['product']['title'] ?? '');
                        }
                      }
                      if ($service_name === '') $service_name = 'Order';
                      $address_raw = $order['listing']['address'] ?? $order['address'] ?? '';
                      if (is_array($address_raw)) {
                        $address_raw = (string) ($address_raw['full'] ?? $address_raw['formatted'] ?? $address_raw['street_address'] ?? '');
                      }
                      $address = (string) $address_raw;
                      $total = (string) ($order['total'] ?? $order['total_amount'] ?? $order['grand_total'] ?? $order['amount'] ?? '');
                      $delivery = (string) ($order['delivery_date'] ?? $order['delivered_at'] ?? $order['completed_at'] ?? $order['updated_at'] ?? '');
                      $delivery_label = $delivery !== '' ? date_i18n('M j, Y', strtotime($delivery)) : '';
                    ?>
                    <tr>
                      <td><?php echo esc_html($order_number); ?></td>
                      <td><?php echo esc_html($service_name); ?></td>
                      <td><?php echo esc_html($address); ?></td>
                      <td><span class="status-pill <?php echo esc_attr($status_class($status_raw)); ?>"><?php echo esc_html($status_label($status_raw)); ?></span></td>
                      <td><?php echo esc_html($total !== '' ? $total : ''); ?></td>
                      <td><?php echo esc_html($delivery_label); ?></td>
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
        <section class="portal-account">
          <article class="portal-card">
            <h2>Profile</h2>
            <div class="account-grid">
              <div>
                <label>Full Name</label>
                <input type="text" value="<?php echo esc_attr($name); ?>" readonly>
              </div>
              <div>
                <label>Email</label>
                <input type="email" value="<?php echo esc_attr($user->user_email ?? ''); ?>" readonly>
              </div>
            </div>
          </article>
          <?php if (is_array($subscription_summary)): ?>
            <?php
              $is_subscription_active = !empty($subscription_summary['is_active']);
              $can_manage_billing = !empty($subscription_summary['can_manage_billing']);
              $manage_billing_url = (string) ($subscription_summary['manage_billing_url'] ?? '');
              $membership_catalog_url = home_url('/services/#monthly-content-memberships');
            ?>
            <article class="portal-card">
              <h2>Membership</h2>
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
                  <label>Next Billing Date</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['current_period_end_label'] ?? 'N/A')); ?>" readonly>
                </div>
                <div>
                  <label>Commitment End</label>
                  <input type="text" value="<?php echo esc_attr((string) ($subscription_summary['commitment_end_label'] ?? 'N/A')); ?>" readonly>
                </div>
              </div>
              <?php if (!$is_subscription_active): ?>
                <p style="margin:14px 0 0;">Membership is inactive. Member-only pricing and credits are disabled until billing is active.</p>
              <?php endif; ?>
              <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
                <?php if ($can_manage_billing && $manage_billing_url !== ''): ?>
                  <a class="btn btn--secondary" href="<?php echo esc_url($manage_billing_url); ?>">Manage Billing</a>
                <?php endif; ?>
                <a class="btn btn--secondary" href="<?php echo esc_url($membership_catalog_url); ?>">Select Membership Plan</a>
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

<?php get_footer(); ?>
