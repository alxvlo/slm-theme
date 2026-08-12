<?php
/**
 * Template Name: Memberships
 */
if (!defined('ABSPATH')) exit;

slm_page_seo(
  'Real Estate Media Memberships | Showcase Listings Media',
  'Monthly listing media memberships for North Florida agents and businesses — predictable pricing and priority scheduling.'
);

get_header();

$is_logged_in = is_user_logged_in();
$is_admin = $is_logged_in && slm_user_is_admin();
$portal_membership_url = add_query_arg('view', 'membership-shop', slm_portal_url());
$portal_membership_auth_url = add_query_arg([
  'redirect_to' => $portal_membership_url,
], slm_login_url());
$admin_dashboard_url = slm_admin_portal_url();
// Booking CTA — must resolve through the canonical helper. The old local
// fallback reached slm_aryeo_start_order_url(), which cannot serve a guest.
$membership_order_cta_url = slm_book_url();

$membership_catalog = function_exists('slm_subscriptions_membership_catalog') ? slm_subscriptions_membership_catalog() : ['social' => [], 'agent' => []];
$monthly_memberships = (array) ($membership_catalog['social'] ?? []);
$agent_memberships = (array) ($membership_catalog['agent'] ?? []);

$membership_cta = static function (array $pkg) use ($is_logged_in, $is_admin, $portal_membership_url, $portal_membership_auth_url, $admin_dashboard_url): array {
  if ($is_admin) {
    return [
      'url' => $admin_dashboard_url,
      'label' => 'Admin Dashboard',
    ];
  }

  if ($is_logged_in) {
    return [
      'url' => $portal_membership_url,
      'label' => 'Open Membership Shop',
    ];
  }

  return [
    'url' => $portal_membership_auth_url,
    'label' => 'Log In / Create Account',
  ];
};
?>

<main id="main-content">
  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1>Break the standard. Showcase the difference.</h1>
      <p class="page-hero__sub">Browse content and listing shoot memberships designed for consistent media output, strategic support, and scalable growth.</p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section page-section--secondary" id="monthly-content-memberships">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Content Memberships (agents &amp; businesses)</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Recurring plans for creators, teams, and brands that need consistent monthly production. Membership checkout is available only after sign-in in the client portal.</p>
      <div class="pkg-ladderWrap">
        <?php if (function_exists('slm_subscriptions_ladder_rail_head')) slm_subscriptions_ladder_rail_head(); ?>
        <div class="pkg-ladder" id="ladder-content" data-ladder>
          <?php foreach ($monthly_memberships as $pkg_index => $pkg): ?>
            <?php $cta = $membership_cta($pkg); ?>
            <div class="pkg-card pkg-card--ladder<?php echo !empty($pkg['popular']) ? ' pkg-card--popular' : ''; ?>">
              <?php if (!empty($pkg['popular'])): ?>
                <div class="pkg-badge">Most Popular</div>
              <?php endif; ?>
              <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
              <?php slm_subscriptions_ladder_rank((int) $pkg_index); ?>
              <p class="pkg-ladder__price">
                <span class="pkg-ladder__amount"><?php echo esc_html(slm_subscriptions_format_price((int) ($pkg['price'] ?? 0))); ?></span>
                <span class="pkg-ladder__per">/ mo</span>
              </p>
              <p class="sub" style="margin:10px 0 12px; font-size:.9rem;">Agreement options available in portal: Month-to-Month, 6-Month, or 12-Month.</p>
              <ul class="pkg-features">
                <?php foreach ($pkg['features'] as $feature): ?>
                  <li>
                    <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span><?php echo esc_html($feature); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
              <a class="btn btn--accent pkg-cta" href="<?php echo esc_url((string) $cta['url']); ?>"><?php echo esc_html((string) $cta['label']); ?></a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <p class="center sub" style="margin-top:22px;">Want us to post it too? <a href="<?php echo esc_url(slm_social_media_management_url()); ?>">See Social Media Management &rarr;</a></p>
      <div class="center sub" style="margin-top:20px; max-width:920px; margin-left:auto; margin-right:auto;">
        <p style="margin:0 0 8px;">Month-to-month social memberships require a minimum 3-month commitment.</p>
        <p style="margin:0 0 8px;">6-month agreement: $100 off the first two months (averaged discount option in Square may display as approximately $33.33/mo across 6 months).</p>
        <p style="margin:0;">12-month agreement includes 1 complimentary listing shoot per month while the agreement remains active.</p>
      </div>
    </div>
  </section>

  <section class="page-section" id="listings-agent-memberships">
    <div class="container">
      <h2 class="center" style="margin-top:0;">Listing Shoot Memberships (agents)</h2>
      <p class="center sub" style="margin-bottom:34px; max-width:820px;">Tiered options for agents combining listings coverage, AI edits, and social support workflows.</p>
      <div class="pkg-ladderWrap">
        <?php if (function_exists('slm_subscriptions_ladder_rail_head')) slm_subscriptions_ladder_rail_head(); ?>
        <div class="pkg-ladder" id="ladder-agent" data-ladder>
          <?php foreach ($agent_memberships as $pkg_index => $pkg): ?>
            <?php $cta = $membership_cta($pkg); ?>
            <div class="pkg-card pkg-card--ladder<?php echo !empty($pkg['popular']) ? ' pkg-card--popular' : ''; ?>">
              <?php if (!empty($pkg['popular'])): ?>
                <div class="pkg-badge">Most Popular</div>
              <?php endif; ?>
              <h3 class="pkg-title"><?php echo esc_html($pkg['name']); ?></h3>
              <?php slm_subscriptions_ladder_rank((int) $pkg_index); ?>
              <p class="pkg-ladder__price">
                <span class="pkg-ladder__amount"><?php echo esc_html(slm_subscriptions_format_price((int) ($pkg['price'] ?? 0))); ?></span>
                <span class="pkg-ladder__per">/ mo</span>
              </p>
              <p class="sub" style="margin:8px 0 12px; font-size:.9rem;">Agreement Term: <strong>12-Month</strong></p>
              <ul class="pkg-features">
                <?php foreach ($pkg['features'] as $feature): ?>
                  <li>
                    <svg class="pkg-check" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span><?php echo esc_html($feature); ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
              <a class="btn btn--accent pkg-cta" href="<?php echo esc_url((string) $cta['url']); ?>"><?php echo esc_html((string) $cta['label']); ?></a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <p class="center sub" style="margin-top:20px;">Listing shoot memberships run on a 12-month agreement. Unused items roll over and expire 12 months from signing. Members receive priority in all editing, plus monthly perks such as open house exposure or giveaways.</p>
    </div>
  </section>

  <section class="service-section" id="membership-application">
    <div class="container">
      <div class="service-finalCta">
        <h2>Need Help Choosing a Membership?</h2>
        <p class="sub">Use the plan cards above to sign in and open the client portal membership shop for checkout. If you need one-time work or a custom recommendation, review services first.</p>
        <div class="service-finalCta__actions">
          <a class="btn btn--accent" href="<?php echo esc_url($membership_order_cta_url); ?>">View Services</a>
        </div>
        <p class="sub memberships-applyCard__note"><a href="<?php echo esc_url($portal_membership_auth_url); ?>">Already a client? Open the membership shop and billing tools in your portal.</a></p>
      </div>
    </div>
  </section>
</main>

<?php get_footer();
