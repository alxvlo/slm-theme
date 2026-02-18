<?php
/**
 * Template Name: Contact
 */
if (!defined('ABSPATH')) exit;

get_header();

$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in ? slm_dashboard_url() : add_query_arg('mode', 'signup', slm_login_url());
$cta_label = $is_logged_in ? 'Go to Dashboard' : 'Create Account to Order';
$contact_email = function_exists('slm_footer_setting') ? slm_footer_setting('slm_footer_email', 'Showcaselistingsmedia@gmail.com') : 'Showcaselistingsmedia@gmail.com';

$interest_options = [
  'listing-packages' => 'Listing Packages',
  'social-packages' => 'Social Packages',
  'monthly-memberships' => 'Monthly Memberships',
  'listings-agent-memberships' => 'Listings-Agent Memberships',
  'addons' => 'Add-Ons',
  'general-inquiry' => 'General Inquiry',
];

$form = [
  'name' => '',
  'email' => '',
  'phone' => '',
  'brokerage' => '',
  'interest' => '',
  'message' => '',
];

$notice_message = '';
$notice_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slm_contact_submit'])) {
  $nonce = isset($_POST['slm_contact_nonce']) ? (string) $_POST['slm_contact_nonce'] : '';
  if (!wp_verify_nonce($nonce, 'slm_contact_form')) {
    $notice_type = 'error';
    $notice_message = 'Security check failed. Please refresh and try again.';
  } else {
    $form['name'] = sanitize_text_field((string) ($_POST['name'] ?? ''));
    $form['email'] = sanitize_email((string) ($_POST['email'] ?? ''));
    $form['phone'] = sanitize_text_field((string) ($_POST['phone'] ?? ''));
    $form['brokerage'] = sanitize_text_field((string) ($_POST['brokerage'] ?? ''));
    $form['interest'] = sanitize_key((string) ($_POST['interest'] ?? ''));
    $form['message'] = sanitize_textarea_field((string) ($_POST['message'] ?? ''));

    $errors = [];
    if ($form['name'] === '') {
      $errors[] = 'Please enter your name.';
    }
    if ($form['email'] === '' || !is_email($form['email'])) {
      $errors[] = 'Please enter a valid email address.';
    }
    if ($form['message'] === '') {
      $errors[] = 'Please enter a message.';
    }

    if (!empty($errors)) {
      $notice_type = 'error';
      $notice_message = implode(' ', $errors);
    } else {
      $site_name = wp_specialchars_decode((string) get_bloginfo('name'), ENT_QUOTES);
      $subject = sprintf('[%s] New Contact Inquiry from %s', $site_name, $form['name']);

      $interest_label = $interest_options[$form['interest']] ?? 'Not specified';
      $lines = [
        'Name: ' . $form['name'],
        'Email: ' . $form['email'],
        'Phone: ' . ($form['phone'] !== '' ? $form['phone'] : 'Not provided'),
        'Brokerage: ' . ($form['brokerage'] !== '' ? $form['brokerage'] : 'Not provided'),
        'Interest: ' . $interest_label,
        '',
        'Message:',
        $form['message'],
      ];

      $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . wp_strip_all_tags($form['name']) . ' <' . $form['email'] . '>',
      ];

      $sent = wp_mail((string) get_option('admin_email'), $subject, implode("\n", $lines), $headers);
      if ($sent) {
        $notice_type = 'success';
        $notice_message = 'Thanks for reaching out. We received your message and will follow up shortly.';
        $form = [
          'name' => '',
          'email' => '',
          'phone' => '',
          'brokerage' => '',
          'interest' => '',
          'message' => '',
        ];
      } else {
        $notice_type = 'error';
        $notice_message = 'We could not send your message right now. Please try again shortly.';
      }
    }
  }
}
?>

<main>
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>Contact Showcase Listings Media</h1>
      <p class="page-hero__sub">Strategic support, responsive service, and marketing built around better outcomes.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section">
    <div class="container contact-wrap">
      <div class="contact-grid">
        <div class="contact-left">
          <h2>Let us build your competitive advantage</h2>
          <p>Whether you need listing media, pricing guidance, or support choosing the right package, our team is here to help you move faster with confidence.</p>

          <div class="contact-item">
            <div class="contact-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none"><path d="M6 3h3l2 5-2 2a15 15 0 0 0 5 5l2-2 5 2v3a2 2 0 0 1-2.2 2A17 17 0 0 1 4 5.2 2 2 0 0 1 6 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </div>
            <div>
              <strong>Phone</strong>
              <span><a href="tel:+19042945809">(904)-294-5809</a></span>
            </div>
          </div>

          <div class="contact-item">
            <div class="contact-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4V6Z" stroke="currentColor" stroke-width="1.6"/><path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="1.6"/></svg>
            </div>
            <div>
              <strong>Email</strong>
              <span><a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a></span>
            </div>
          </div>

          <div class="contact-item">
            <div class="contact-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none"><path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11Z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="10" r="2.4" stroke="currentColor" stroke-width="1.6"/></svg>
            </div>
            <div>
              <strong>Market</strong>
              <span>Serving North Florida agents and broker teams</span>
            </div>
          </div>

          <div class="contact-note card">
            <h3>Response standard</h3>
            <p>We operate with a service-first approach. Most inquiries receive a response within one business day.</p>
          </div>
        </div>

        <div class="card contact-card">
          <h3>Send us a message</h3>

          <?php if ($notice_message !== ''): ?>
            <p class="contact-alert contact-alert--<?php echo esc_attr($notice_type); ?>"><?php echo esc_html($notice_message); ?></p>
          <?php endif; ?>

          <form class="contact-form" method="post" action="<?php echo esc_url(get_permalink()); ?>">
            <?php wp_nonce_field('slm_contact_form', 'slm_contact_nonce'); ?>

            <div class="field">
              <label for="slm_name">Full Name</label>
              <input id="slm_name" name="name" type="text" value="<?php echo esc_attr($form['name']); ?>" required />
            </div>

            <div class="field">
              <label for="slm_email">Email</label>
              <input id="slm_email" name="email" type="email" value="<?php echo esc_attr($form['email']); ?>" required />
            </div>

            <div class="field">
              <label for="slm_phone">Phone (optional)</label>
              <input id="slm_phone" name="phone" type="text" value="<?php echo esc_attr($form['phone']); ?>" />
            </div>

            <div class="field">
              <label for="slm_brokerage">Brokerage / Team (optional)</label>
              <input id="slm_brokerage" name="brokerage" type="text" value="<?php echo esc_attr($form['brokerage']); ?>" />
            </div>

            <div class="field">
              <label for="slm_interest">What do you need help with?</label>
              <select id="slm_interest" name="interest">
                <option value="">Select an option</option>
                <?php foreach ($interest_options as $key => $label): ?>
                  <option value="<?php echo esc_attr($key); ?>"<?php selected($form['interest'], $key); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field">
              <label for="slm_message">Message</label>
              <textarea id="slm_message" name="message" required><?php echo esc_textarea($form['message']); ?></textarea>
            </div>

            <button class="btn" type="submit" name="slm_contact_submit" value="1">Send Message</button>
          </form>
        </div>
      </div>

      <div class="center" style="margin-top:56px;">
        <p class="sub" style="margin-bottom:18px;">Ready to start your next listing campaign?</p>
        <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
