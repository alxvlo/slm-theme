<?php
/**
 * Template Name: Contact
 */
if (!defined('ABSPATH'))
  exit;

// SEO
add_filter('pre_get_document_title', function () {
  return 'Contact Us | Showcase Listings Media';
}, 99);
add_action('wp_head', function () {
  echo '<meta name="description" content="Get in touch with Showcase Listings Media — Jacksonville\'s real estate photography and video team serving agents and businesses across North Florida.">' . "\n";
}, 1);

get_header();

$is_logged_in  = is_user_logged_in();
$book_url      = $is_logged_in
  ? add_query_arg('view', 'place-order', slm_portal_url())
  : add_query_arg('mode', 'signup', slm_login_url());
$services_url  = esc_url(home_url('/services/'));
$contact_email = function_exists('slm_footer_setting')
  ? slm_footer_setting('slm_footer_email', 'Showcaselistingsmedia@gmail.com')
  : 'Showcaselistingsmedia@gmail.com';

$interest_options = [
  'real-estate-photography' => 'Real Estate Photography',
  'cinematic-video'         => 'Cinematic Video',
  'drone-photography'       => 'Drone Photography',
  'social-media-content'    => 'Social Media Content',
  'business-branding'       => 'Business Branding',
  'general-inquiry'         => 'General Inquiry',
];

$form = [
  'name'      => '',
  'email'     => '',
  'phone'     => '',
  'brokerage' => '',
  'interest'  => '',
  'message'   => '',
];

$notice_message = '';
$notice_type    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['slm_contact_submit'])) {
  $nonce = isset($_POST['slm_contact_nonce']) ? (string) $_POST['slm_contact_nonce'] : '';
  if (!wp_verify_nonce($nonce, 'slm_contact_form')) {
    $notice_type    = 'error';
    $notice_message = 'Security check failed. Please refresh and try again.';
  } else {
    $form['name']      = sanitize_text_field((string) ($_POST['name'] ?? ''));
    $form['email']     = sanitize_email((string) ($_POST['email'] ?? ''));
    $form['phone']     = sanitize_text_field((string) ($_POST['phone'] ?? ''));
    $form['brokerage'] = sanitize_text_field((string) ($_POST['brokerage'] ?? ''));
    $form['interest']  = sanitize_key((string) ($_POST['interest'] ?? ''));
    $form['message']   = sanitize_textarea_field((string) ($_POST['message'] ?? ''));

    $errors = [];
    if ($form['name'] === '')
      $errors[] = 'Please enter your name.';
    if ($form['email'] === '' || !is_email($form['email']))
      $errors[] = 'Please enter a valid email address.';
    if ($form['message'] === '')
      $errors[] = 'Please enter a message.';

    if (!empty($errors)) {
      $notice_type    = 'error';
      $notice_message = implode(' ', $errors);
    } else {
      $site_name      = wp_specialchars_decode((string) get_bloginfo('name'), ENT_QUOTES);
      $subject        = sprintf('[%s] New Contact Inquiry from %s', $site_name, $form['name']);
      $interest_label = $interest_options[$form['interest']] ?? 'Not specified';
      $lines          = [
        'Name: '      . $form['name'],
        'Email: '     . $form['email'],
        'Phone: '     . ($form['phone'] !== '' ? $form['phone'] : 'Not provided'),
        'Brokerage: ' . ($form['brokerage'] !== '' ? $form['brokerage'] : 'Not provided'),
        'Interest: '  . $interest_label,
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
        $notice_type    = 'success';
        $notice_message = 'Thanks for reaching out. We received your message and will follow up shortly.';
        $form = ['name' => '', 'email' => '', 'phone' => '', 'brokerage' => '', 'interest' => '', 'message' => ''];
      } else {
        $notice_type    = 'error';
        $notice_message = 'We could not send your message right now. Please try again shortly.';
      }
    }
  }
}
?>

<main>

  <?php $pid = get_the_ID(); slm_edit_page_button($pid); ?>

  <!-- ── Section 1: Hero ─────────────────────────────── -->
  <section class="ctc-hero js-reveal">
    <div class="container ctc-hero__inner">
      <h1 class="ctc-hero__h1">Let's Create Something Worth Seeing</h1>
      <p class="ctc-hero__sub">Whether you're booking a shoot, asking about services, or just getting started — we're here and ready to help.</p>
    </div>
  </section>

  <!-- ── Section 2: Contact Split ────────────────────── -->
  <section class="ctc-split js-reveal">
    <div class="container ctc-split__grid">

      <!-- Left: Contact Info -->
      <div class="ctc-info">
        <h2 class="ctc-info__h2">We'd love to hear from you.</h2>
        <p class="ctc-info__body">Whether you need listing media, pricing guidance, or support choosing the right package — our team is here to help you move faster with confidence.</p>

        <div class="ctc-info__block">
          <span class="ctc-info__icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M6 3h3l2 5-2 2a15 15 0 0 0 5 5l2-2 5 2v3a2 2 0 0 1-2.2 2A17 17 0 0 1 4 5.2 2 2 0 0 1 6 3Z" stroke="#C9922A" stroke-width="1.6" stroke-linejoin="round"/></svg>
          </span>
          <div>
            <span class="ctc-info__label">PHONE</span>
            <a class="ctc-info__value" href="tel:+19042945809">(904) 294-5809</a>
          </div>
        </div>

        <div class="ctc-info__block">
          <span class="ctc-info__icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4V6Z" stroke="#C9922A" stroke-width="1.6"/><path d="m4 7 8 6 8-6" stroke="#C9922A" stroke-width="1.6"/></svg>
          </span>
          <div>
            <span class="ctc-info__label">EMAIL</span>
            <a class="ctc-info__value" href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
          </div>
        </div>

        <div class="ctc-info__block">
          <span class="ctc-info__icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11Z" stroke="#C9922A" stroke-width="1.6"/><circle cx="12" cy="10" r="2.4" stroke="#C9922A" stroke-width="1.6"/></svg>
          </span>
          <div>
            <span class="ctc-info__label">MARKET</span>
            <span class="ctc-info__value">Serving North Florida agents and brands</span>
          </div>
        </div>

        <div class="ctc-response">
          <h3 class="ctc-response__title">Response Standard</h3>
          <p class="ctc-response__body">We operate with a service-first approach. Most inquiries receive a response within one business day.</p>
        </div>
      </div>

      <!-- Right: Contact Form Card -->
      <div class="ctc-form-card">
        <h3 class="ctc-form-card__title">Send Us a Message</h3>

        <?php if ($notice_message !== ''): ?>
          <p class="ctc-alert ctc-alert--<?php echo esc_attr($notice_type); ?>"><?php echo esc_html($notice_message); ?></p>
        <?php endif; ?>

        <form class="ctc-form" method="post" action="<?php echo esc_url(get_permalink()); ?>">
          <?php wp_nonce_field('slm_contact_form', 'slm_contact_nonce'); ?>

          <div class="ctc-field">
            <label class="ctc-label" for="ctc_name">Full Name</label>
            <input class="ctc-input" id="ctc_name" name="name" type="text" value="<?php echo esc_attr($form['name']); ?>" required>
          </div>

          <div class="ctc-field">
            <label class="ctc-label" for="ctc_email">Email</label>
            <input class="ctc-input" id="ctc_email" name="email" type="email" value="<?php echo esc_attr($form['email']); ?>" required>
          </div>

          <div class="ctc-field">
            <label class="ctc-label" for="ctc_phone">Phone <span class="ctc-optional">(optional)</span></label>
            <input class="ctc-input" id="ctc_phone" name="phone" type="tel" value="<?php echo esc_attr($form['phone']); ?>">
          </div>

          <div class="ctc-field">
            <label class="ctc-label" for="ctc_brokerage">Brokerage / Team <span class="ctc-optional">(optional)</span></label>
            <input class="ctc-input" id="ctc_brokerage" name="brokerage" type="text" value="<?php echo esc_attr($form['brokerage']); ?>">
          </div>

          <div class="ctc-field">
            <label class="ctc-label" for="ctc_interest">What do you need help with?</label>
            <select class="ctc-input" id="ctc_interest" name="interest">
              <option value="">Select an option</option>
              <?php foreach ($interest_options as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>"<?php selected($form['interest'], $key); ?>><?php echo esc_html($label); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="ctc-field">
            <label class="ctc-label" for="ctc_message">Message</label>
            <textarea class="ctc-input ctc-textarea" id="ctc_message" name="message" rows="5"><?php echo esc_textarea($form['message']); ?></textarea>
          </div>

          <button class="ctc-submit" type="submit" name="slm_contact_submit" value="1">Send Message</button>
        </form>
      </div>

    </div>
  </section>

  <!-- ── Section 3: Three Quick Options ──────────────── -->
  <section class="ctc-options js-reveal">
    <div class="container">
      <h2 class="ctc-options__h2">Prefer to reach out directly?</h2>
      <div class="ctc-options__grid">

        <div class="ctc-option-card js-reveal">
          <span class="ctc-option-card__icon" aria-hidden="true">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M6 3h3l2 5-2 2a15 15 0 0 0 5 5l2-2 5 2v3a2 2 0 0 1-2.2 2A17 17 0 0 1 4 5.2 2 2 0 0 1 6 3Z" stroke="#C9922A" stroke-width="1.6" stroke-linejoin="round"/></svg>
          </span>
          <h3 class="ctc-option-card__title">Call Us</h3>
          <p class="ctc-option-card__body">Speak directly with our team</p>
          <a class="ctc-option-card__link" href="tel:+19042945809">(904) 294-5809</a>
        </div>

        <div class="ctc-option-card js-reveal">
          <span class="ctc-option-card__icon" aria-hidden="true">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><path d="M4 6h16v12H4V6Z" stroke="#C9922A" stroke-width="1.6"/><path d="m4 7 8 6 8-6" stroke="#C9922A" stroke-width="1.6"/></svg>
          </span>
          <h3 class="ctc-option-card__title">Email Us</h3>
          <p class="ctc-option-card__body">We respond within one business day</p>
          <a class="ctc-option-card__link" href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
        </div>

        <div class="ctc-option-card js-reveal">
          <span class="ctc-option-card__icon" aria-hidden="true">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" stroke="#C9922A" stroke-width="1.6"/><path d="M16 2v4M8 2v4M3 10h18" stroke="#C9922A" stroke-width="1.6" stroke-linecap="round"/></svg>
          </span>
          <h3 class="ctc-option-card__title">Book a Shoot</h3>
          <p class="ctc-option-card__body">Ready to get started? Book directly online</p>
          <a class="ctc-option-card__btn" href="<?php echo esc_url($book_url); ?>">Book Now</a>
        </div>

      </div>
    </div>
  </section>

  <!-- ── Section 4: Final CTA ────────────────────────── -->
  <section class="svc-final-cta js-reveal" aria-label="Get started with Showcase Listings Media">
    <div class="container">
      <h2>Ready to Stand Out?</h2>
      <p>Whether you're an agent or a business — your content should work for you, not blend in.</p>
      <div class="svc-final-cta__btns">
        <a class="btn svc-final-cta__primary" href="<?php echo esc_url($book_url); ?>">Book a Shoot Today</a>
        <a class="btn svc-final-cta__secondary" href="<?php echo esc_url($services_url); ?>">View Our Services</a>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
