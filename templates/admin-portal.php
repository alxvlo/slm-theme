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

$orders = [
  [
    'id' => 'ORD-1234',
    'customer' => 'Sarah Johnson',
    'service' => 'Real Estate Photography',
    'address' => '123 Oak Street, Beverly Hills, CA',
    'status' => 'completed',
    'price' => '$450',
    'date' => 'Jan 18, 2026',
  ],
  [
    'id' => 'ORD-1235',
    'customer' => 'Michael Chen',
    'service' => 'Drone Photography',
    'address' => '456 Maple Drive, Santa Monica, CA',
    'status' => 'in-progress',
    'price' => '$350',
    'date' => 'Jan 20, 2026',
  ],
  [
    'id' => 'ORD-1236',
    'customer' => 'Emily Rodriguez',
    'service' => '3D Virtual Tour',
    'address' => '789 Pine Avenue, Malibu, CA',
    'status' => 'scheduled',
    'price' => '$550',
    'date' => 'Feb 12, 2026',
  ],
  [
    'id' => 'ORD-1237',
    'customer' => 'David Thompson',
    'service' => 'Real Estate Videography',
    'address' => '321 Sunset Blvd, Hollywood, CA',
    'status' => 'pending',
    'price' => '$650',
    'date' => 'Feb 13, 2026',
  ],
];

$status_class = static function (string $status): string {
  if ($status === 'completed') return 'is-completed';
  if ($status === 'in-progress') return 'is-progress';
  if ($status === 'scheduled') return 'is-scheduled';
  return 'is-pending';
};

$status_label = static function (string $status): string {
  return ucwords(str_replace('-', ' ', $status));
};

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
      <?php if ($view === 'dashboard'): ?>
        <section class="portal-section">
          <h1>Admin Dashboard</h1>
          <p class="sub">Manage customer orders and monitor business performance.</p>
        </section>

        <section class="portal-stats portal-stats--four">
          <article class="portal-card">
            <p>Total Orders</p>
            <strong>127</strong>
          </article>
          <article class="portal-card">
            <p>Active Customers</p>
            <strong>45</strong>
          </article>
          <article class="portal-card">
            <p>In Progress</p>
            <strong>12</strong>
          </article>
          <article class="portal-card">
            <p>Revenue (MTD)</p>
            <strong>$18.5K</strong>
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
              <?php foreach ($orders as $order): ?>
                <tr>
                  <td><?php echo esc_html($order['id']); ?></td>
                  <td><?php echo esc_html($order['customer']); ?></td>
                  <td><?php echo esc_html($order['service']); ?></td>
                  <td><?php echo esc_html($order['address']); ?></td>
                  <td><span class="status-pill <?php echo esc_attr($status_class($order['status'])); ?>"><?php echo esc_html($status_label($order['status'])); ?></span></td>
                  <td><?php echo esc_html($order['price']); ?></td>
                  <td><?php echo esc_html($order['date']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </section>
      <?php endif; ?>

      <?php if ($view === 'all-jobs'): ?>
        <section class="portal-section">
          <h1>All Customer Orders</h1>
          <p class="sub">Centralized operations view for all active and completed jobs.</p>
        </section>
        <section class="portal-tableCard">
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
              <?php foreach ($orders as $order): ?>
                <tr>
                  <td><?php echo esc_html($order['id']); ?></td>
                  <td><?php echo esc_html($order['customer']); ?></td>
                  <td><?php echo esc_html($order['service']); ?></td>
                  <td><?php echo esc_html($order['address']); ?></td>
                  <td><span class="status-pill <?php echo esc_attr($status_class($order['status'])); ?>"><?php echo esc_html($status_label($order['status'])); ?></span></td>
                  <td><?php echo esc_html($order['price']); ?></td>
                  <td><?php echo esc_html($order['date']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
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
