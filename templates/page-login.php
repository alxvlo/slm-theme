<?php
/**
 * Template Name: Login
 */
if (!defined('ABSPATH')) exit;
// Preserve intended destination (portal membership shop, place-order, etc.) through login/signup.
$requested_redirect = isset($_REQUEST['redirect_to']) ? (string) wp_unslash($_REQUEST['redirect_to']) : '';
$validated_redirect = '';
if ($requested_redirect !== '') {
  $candidate = wp_validate_redirect($requested_redirect, '');
  if (is_string($candidate) && $candidate !== '') {
    $validated_redirect = $candidate;
  }
}
$post_auth_redirect = $validated_redirect !== '' ? $validated_redirect : slm_dashboard_url();

if (is_user_logged_in()) {
  wp_safe_redirect($post_auth_redirect);
  exit;
}

$mode = isset($_GET['mode']) ? sanitize_key($_GET['mode']) : 'portal';
$allowed_modes = ['portal', 'login', 'signup'];
if (!in_array($mode, $allowed_modes, true)) {
  $mode = 'portal';
}

$users_can_register = (bool) get_option('users_can_register');
$login_page_url = slm_login_url();
$auth_link_args = [];
if ($validated_redirect !== '') {
  $auth_link_args['redirect_to'] = $validated_redirect;
}
$login_mode_url = add_query_arg(array_merge(['mode' => 'login'], $auth_link_args), $login_page_url);
$signup_mode_url = add_query_arg(array_merge(['mode' => 'signup'], $auth_link_args), $login_page_url);
$portal_mode_url = remove_query_arg(['mode', 'auth'], $login_page_url);
if ($validated_redirect !== '') {
  $portal_mode_url = add_query_arg('redirect_to', $validated_redirect, $portal_mode_url);
}
$auth = isset($_GET['auth']) ? sanitize_key($_GET['auth']) : '';
$signup_error = '';

$signup_values = [
  'first_name' => '',
  'last_name' => '',
  'email' => '',
  'phone' => '',
  'brokerage' => '',
  'affiliate_id' => '',
];

if (
  $_SERVER['REQUEST_METHOD'] === 'POST'
  && isset($_POST['slm_action'])
  && sanitize_key(wp_unslash($_POST['slm_action'])) === 'register'
) {
  $mode = 'signup';

  if (!$users_can_register) {
    $signup_error = 'Account registration is currently disabled. In WordPress Admin, enable Settings > General > Membership: Anyone can register.';
  } elseif (
    !isset($_POST['slm_register_nonce'])
    || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['slm_register_nonce'])), 'slm_front_register')
  ) {
    $signup_error = 'Your session expired. Please refresh and try again.';
  } else {
    $first_name = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
    $last_name = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $brokerage = sanitize_text_field(wp_unslash($_POST['brokerage'] ?? ''));
    $affiliate_id = sanitize_text_field(wp_unslash($_POST['affiliate_id'] ?? ''));
    $password = (string) wp_unslash($_POST['password'] ?? '');
    $confirm_password = (string) wp_unslash($_POST['confirm_password'] ?? '');

    $signup_values = [
      'first_name' => $first_name,
      'last_name' => $last_name,
      'email' => $email,
      'phone' => $phone,
      'brokerage' => $brokerage,
      'affiliate_id' => $affiliate_id,
    ];

    $errors = new WP_Error();

    if ($first_name === '') {
      $errors->add('first_name', 'First name is required.');
    }
    if ($last_name === '') {
      $errors->add('last_name', 'Last name is required.');
    }
    if ($email === '' || !is_email($email)) {
      $errors->add('email', 'A valid email address is required.');
    } elseif (email_exists($email)) {
      $errors->add('email_exists', 'An account with this email already exists.');
    }
    if ($password === '') {
      $errors->add('password', 'Password is required.');
    } elseif (strlen($password) < 8) {
      $errors->add('password_len', 'Password must be at least 8 characters.');
    }
    if ($confirm_password === '') {
      $errors->add('confirm_password', 'Please confirm your password.');
    } elseif ($password !== $confirm_password) {
      $errors->add('password_match', 'Passwords do not match.');
    }

    if (empty($errors->get_error_codes())) {
      $username_seed = sanitize_user((string) strtok($email, '@'), true);
      if ($username_seed === '') {
        $username_seed = 'client';
      }

      $username = $username_seed;
      $suffix = 1;
      while (username_exists($username)) {
        $suffix++;
        $username = $username_seed . $suffix;
      }

      $user_id = wp_insert_user([
        'user_login' => $username,
        'user_pass' => $password,
        'user_email' => $email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => trim($first_name . ' ' . $last_name),
        'role' => get_option('default_role', 'subscriber'),
      ]);

      if (is_wp_error($user_id)) {
        $signup_error = $user_id->get_error_message();
      } else {
        if ($phone !== '') {
          update_user_meta($user_id, 'phone', $phone);
        }
        if ($brokerage !== '') {
          update_user_meta($user_id, 'brokerage', $brokerage);
        }
        if ($affiliate_id !== '') {
          update_user_meta($user_id, 'affiliate_id', $affiliate_id);
        }

        $new_user = get_user_by('id', $user_id);
        if ($new_user instanceof WP_User) {
          wp_set_current_user($user_id);
          wp_set_auth_cookie($user_id, true);
          do_action('wp_login', $new_user->user_login, $new_user);
          wp_safe_redirect($post_auth_redirect);
          exit;
        }

        $signup_error = 'Account created, but automatic sign-in failed. Please sign in manually.';
      }
    } else {
      $messages = $errors->get_error_messages();
      $signup_error = (string) ($messages[0] ?? 'Unable to create account. Please review the form and try again.');
    }
  }
}

$logo_src = get_template_directory_uri() . '/assets/img/logo-icon.png';
$logo_abs = get_template_directory() . '/assets/img/logo-icon.png';
$has_logo = file_exists($logo_abs);
$side_image_url = get_template_directory_uri() . '/assets/media/photos/05-1-front-exterior-1-3.jpg';

get_header();
?>

<main class="auth-page auth-page--split">
  <section class="auth-portalLayout">
    <div class="auth-portalLayout__panel">
      <div class="auth-card auth-card--portal">
        <div class="auth-portalBrand">
          <?php if ($has_logo): ?>
            <img src="<?php echo esc_url($logo_src); ?>" alt="" width="56" height="56" decoding="async" loading="eager">
          <?php else: ?>
            <span class="auth-portalBrand__fallback">SLM</span>
          <?php endif; ?>
          <strong><?php echo esc_html(get_bloginfo('name')); ?></strong>
        </div>

        <?php if ($mode === 'portal'): ?>
          <div class="auth-header">
            <h1>Client Portal</h1>
            <p>Sign in if you already have an account, or create one before placing an order.</p>
          </div>

          <div class="auth-optionList">
            <a class="auth-option" href="<?php echo esc_url($login_mode_url); ?>">
              <span class="auth-option__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M10 17 15 12 10 7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M15 12H3" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                  <path d="M12 3h4a5 5 0 0 1 5 5v8a5 5 0 0 1-5 5h-4" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                </svg>
              </span>
              <span>
                <strong>I have an account</strong>
                <small>Log in with your email and password.</small>
              </span>
            </a>

            <a class="auth-option" href="<?php echo esc_url($signup_mode_url); ?>">
              <span class="auth-option__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M16 20a4 4 0 0 0-8 0" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                  <circle cx="12" cy="9" r="3" stroke="currentColor" stroke-width="1.9"/>
                  <path d="M19 8v6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                  <path d="M16 11h6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                </svg>
              </span>
              <span>
                <strong>I do not have an account</strong>
                <small>Create your account to place an order.</small>
              </span>
            </a>
          </div>
        <?php endif; ?>

        <?php if ($mode === 'login'): ?>
          <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Sign in and we will route you to the correct dashboard for your role.</p>
          </div>

          <?php if ($auth === 'failed'): ?>
            <p class="auth-alert">Login failed. Please check your username/email and password.</p>
          <?php endif; ?>

          <form class="auth-form" method="post" action="<?php echo esc_url(wp_login_url()); ?>">
            <div class="auth-field">
              <label for="slm_log">Email or Username</label>
              <input id="slm_log" type="text" name="log" required autocomplete="username" placeholder="you@example.com">
            </div>

            <div class="auth-field">
              <label for="slm_pwd">Password</label>
              <input id="slm_pwd" type="password" name="pwd" required autocomplete="current-password" placeholder="Enter your password">
            </div>

            <div class="auth-row">
              <label class="auth-check">
                <input type="checkbox" name="rememberme" value="forever">
                <span>Remember me</span>
              </label>
              <a href="<?php echo esc_url(wp_lostpassword_url($login_page_url)); ?>">Forgot password?</a>
            </div>

            <input type="hidden" name="redirect_to" value="<?php echo esc_url($post_auth_redirect); ?>">
            <button class="btn auth-submit" type="submit">Sign In</button>
          </form>

          <p class="auth-switch">
            Do not have an account?
            <a href="<?php echo esc_url($signup_mode_url); ?>">Create Account</a>
          </p>
          <p class="auth-backLink"><a href="<?php echo esc_url($portal_mode_url); ?>">Back to Portal Home</a></p>
        <?php endif; ?>

        <?php if ($mode === 'signup'): ?>
          <div class="auth-header">
            <h1>Create Account</h1>
            <p>Set up your account to access ordering and your client portal.</p>
          </div>

          <?php if ($signup_error !== ''): ?>
            <p class="auth-alert"><?php echo esc_html($signup_error); ?></p>
          <?php endif; ?>

          <?php if ($users_can_register): ?>
            <form class="auth-form" method="post" action="<?php echo esc_url($signup_mode_url); ?>" novalidate>
              <?php wp_nonce_field('slm_front_register', 'slm_register_nonce'); ?>
              <input type="hidden" name="slm_action" value="register">
              <input type="hidden" name="redirect_to" value="<?php echo esc_url($post_auth_redirect); ?>">

              <div class="auth-grid">
                <div class="auth-field">
                  <label for="slm_first_name">First name</label>
                  <input id="slm_first_name" type="text" name="first_name" required value="<?php echo esc_attr($signup_values['first_name']); ?>" autocomplete="given-name">
                </div>
                <div class="auth-field">
                  <label for="slm_last_name">Last name</label>
                  <input id="slm_last_name" type="text" name="last_name" required value="<?php echo esc_attr($signup_values['last_name']); ?>" autocomplete="family-name">
                </div>
              </div>

              <div class="auth-grid">
                <div class="auth-field">
                  <label for="slm_email">Email</label>
                  <input id="slm_email" type="email" name="email" required value="<?php echo esc_attr($signup_values['email']); ?>" autocomplete="email">
                </div>
                <div class="auth-field">
                  <label for="slm_phone">Phone number</label>
                  <input id="slm_phone" type="text" name="phone" value="<?php echo esc_attr($signup_values['phone']); ?>" autocomplete="tel">
                </div>
              </div>

              <div class="auth-grid">
                <div class="auth-field">
                  <label for="slm_brokerage">Brokerage, team, or office name</label>
                  <input id="slm_brokerage" type="text" name="brokerage" value="<?php echo esc_attr($signup_values['brokerage']); ?>">
                </div>
                <div class="auth-field">
                  <label for="slm_affiliate_id">Affiliate ID <span class="auth-field__optional">(optional)</span></label>
                  <input id="slm_affiliate_id" type="text" name="affiliate_id" value="<?php echo esc_attr($signup_values['affiliate_id']); ?>">
                </div>
              </div>

              <div class="auth-grid">
                <div class="auth-field">
                  <label for="slm_password">Password</label>
                  <input id="slm_password" type="password" name="password" required autocomplete="new-password">
                </div>
                <div class="auth-field">
                  <label for="slm_confirm_password">Confirm password</label>
                  <input id="slm_confirm_password" type="password" name="confirm_password" required autocomplete="new-password">
                </div>
              </div>

              <button class="btn auth-submit" type="submit">Create Account</button>
            </form>
          <?php else: ?>
            <p class="auth-signupHelp">Registration is disabled. In WordPress Admin, enable <strong>Settings > General > Membership: Anyone can register</strong>.</p>
          <?php endif; ?>

          <p class="auth-switch">
            Already have an account?
            <a href="<?php echo esc_url($login_mode_url); ?>">Sign In</a>
          </p>
          <p class="auth-backLink"><a href="<?php echo esc_url($portal_mode_url); ?>">Back to Portal Home</a></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="auth-portalLayout__media" style="background-image: url('<?php echo esc_url($side_image_url); ?>');" aria-hidden="true"></div>
  </section>
</main>

<?php get_footer(); ?>
