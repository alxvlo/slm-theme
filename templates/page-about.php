<?php
/**
 * Template Name: About
 */
if (!defined('ABSPATH'))
  exit;

get_header();

$is_logged_in = is_user_logged_in();
$cta_url = $is_logged_in ? slm_dashboard_url() : add_query_arg('mode', 'signup', slm_login_url());
$cta_label = $is_logged_in ? 'Go to Dashboard' : 'Create Account to Order';

$pid = get_the_ID();
$config = function_exists('slm_get_editable_fields_for_template') ? slm_get_editable_fields_for_template('templates/page-about.php') : [];

$meta_get = function ($key) use ($pid, $config) {
  $v = get_post_meta($pid, $key, true);
  if ($v === '') {
    if ($config && isset($config[$key]))
      return $config[$key]['default'];
  }
  return $v;
};

$hero_title = $meta_get('slm_about_hero_title');
$hero_sub = $meta_get('slm_about_hero_sub');
$intro_h2 = $meta_get('slm_about_intro_h2');
$intro_p1 = $meta_get('slm_about_intro_p1');
$intro_p2 = $meta_get('slm_about_intro_p2');
$values_h2 = $meta_get('slm_about_values_h2');
$values_sub = $meta_get('slm_about_values_sub');
$outcomes_h2 = $meta_get('slm_about_outcomes_h2');
$outcomes_sub = $meta_get('slm_about_outcomes_sub');
$compare_h2 = $meta_get('slm_about_compare_h2');
$compare_sub = $meta_get('slm_about_compare_sub');
$owner_h2 = $meta_get('slm_about_owner_h2');
$owner_name = $meta_get('slm_about_owner_name');
$owner_role = $meta_get('slm_about_owner_role');
$owner_bio = $meta_get('slm_about_owner_bio');
$owner_photo_id = absint((string) $meta_get('slm_about_owner_photo_id'));
$owner_photo_url = $owner_photo_id > 0 ? wp_get_attachment_image_url($owner_photo_id, 'large') : '';
$cta_h2 = $meta_get('slm_about_cta_h2');
$cta_p = $meta_get('slm_about_cta_p');

$parse_list = function ($key) use ($meta_get) {
  $v = $meta_get($key);
  return array_filter(array_map('trim', explode("\n", $v)));
};

$values_raw = $parse_list('slm_about_values_list');
$core_values = [];
foreach ($values_raw as $l) {
  $parts = explode('|', $l, 2);
  if (count($parts) === 2) {
    $core_values[] = ['title' => trim($parts[0]), 'description' => trim($parts[1])];
  }
  else {
    $core_values[] = ['title' => trim($parts[0]), 'description' => ''];
  }
}
$outcomes = $parse_list('slm_about_outcomes_list');
$traditional = $parse_list('slm_about_traditional_list');
$showcase = $parse_list('slm_about_showcase_list');
$has_owner_content = trim((string) $owner_name) !== ''
  || trim((string) $owner_role) !== ''
  || trim((string) $owner_bio) !== ''
  || $owner_photo_url !== '';

$intro_h2_html = esc_html((string) $intro_h2);
$intro_h2_html = str_replace(
  ['Showcase-Worthy.', 'Showcase-Worthy'],
  ['<span class="slm-noWrap">Showcase-Worthy.</span>', '<span class="slm-noWrap">Showcase-Worthy</span>'],
  $intro_h2_html
);

$page_content = '';
if (have_posts()) {
  while (have_posts()) {
    the_post();
    $page_content = trim((string)get_the_content());
  }
}
?>

<main>
  <?php if (current_user_can('edit_page', $pid)): ?>
    <a href="<?php echo get_edit_post_link($pid); ?>" class="btn" style="position:fixed; bottom:24px; right:24px; z-index:1100; padding:12px 20px; font-size:14px; box-shadow:0 4px 20px rgba(0,0,0,0.25); border-radius:999px;">&#9998; Edit Page Text</a>
  <?php
endif; ?>

  <section class="page-hero page-hero--solid">
    <div class="container page-hero__content">
      <h1><?php echo esc_html($hero_title); ?></h1>
      <p class="page-hero__sub"><?php echo esc_html($hero_sub); ?></p>
    </div>
    <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
      <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
    </svg>
  </section>

  <section class="page-section page-section--secondary">
    <div class="container about-wrap">
      <div class="about-intro card">
        <h2><?php echo wp_kses($intro_h2_html, ['span' => ['class' => []]]); ?></h2>
        <?php if ($intro_p1): ?><p><?php echo esc_html($intro_p1); ?></p><?php
endif; ?>
        <?php if ($intro_p2): ?><p><?php echo esc_html($intro_p2); ?></p><?php
endif; ?>
      </div>
    </div>
  </section>

  <section class="page-section">
    <div class="container about-wrap">
      <h2 class="center"><?php echo esc_html($values_h2); ?></h2>
      <p class="center sub"><?php echo esc_html($values_sub); ?></p>

      <div class="about-values-grid">
        <?php foreach ($core_values as $value): ?>
          <article class="about-valueCard">
            <h3><?php echo esc_html($value['title']); ?></h3>
            <p><?php echo esc_html($value['description']); ?></p>
          </article>
        <?php
endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section page-section--secondary">
    <div class="container about-wrap">
      <h2 class="center"><?php echo esc_html($outcomes_h2); ?></h2>
      <p class="center sub"><?php echo esc_html($outcomes_sub); ?></p>

      <div class="about-outcomes-grid">
        <?php foreach ($outcomes as $item): ?>
          <article class="about-outcomeCard">
            <p><?php echo esc_html($item); ?></p>
          </article>
        <?php
endforeach; ?>
      </div>
    </div>
  </section>

  <section class="page-section">
    <div class="container about-wrap">
      <h2 class="center"><?php echo esc_html($compare_h2); ?></h2>
      <p class="center sub"><?php echo esc_html($compare_sub); ?></p>

      <div class="about-compare">
        <article class="about-compareCard">
          <h3>Traditional Media Providers</h3>
          <ul>
            <?php foreach ($traditional as $item): ?>
              <li><?php echo esc_html($item); ?></li>
            <?php
endforeach; ?>
          </ul>
        </article>

        <article class="about-compareCard about-compareCard--accent">
          <h3>Showcase Listings Media</h3>
          <ul>
            <?php foreach ($showcase as $item): ?>
              <li><?php echo esc_html($item); ?></li>
            <?php
endforeach; ?>
          </ul>
        </article>
      </div>
    </div>
  </section>

  <?php if ($page_content !== ''): ?>
    <section class="page-section page-section--secondary">
      <div class="container prose">
        <?php echo wp_kses_post(apply_filters('the_content', $page_content)); ?>
      </div>
    </section>
  <?php
endif; ?>

  <?php if ($has_owner_content): ?>
    <section class="page-section page-section--secondary">
      <div class="container about-wrap">
        <article class="about-owner card">
          <?php if ($owner_photo_url !== ''): ?>
            <div class="about-owner__media">
              <img src="<?php echo esc_url($owner_photo_url); ?>" alt="<?php echo esc_attr($owner_name !== '' ? $owner_name : 'Owner photo'); ?>" loading="lazy" decoding="async">
            </div>
          <?php endif; ?>
          <div class="about-owner__body">
            <?php if (trim((string) $owner_h2) !== ''): ?>
              <h2><?php echo esc_html($owner_h2); ?></h2>
            <?php endif; ?>
            <?php if (trim((string) $owner_name) !== ''): ?>
              <h3><?php echo esc_html($owner_name); ?></h3>
            <?php endif; ?>
            <?php if (trim((string) $owner_role) !== ''): ?>
              <p class="about-owner__role"><?php echo esc_html($owner_role); ?></p>
            <?php endif; ?>
            <?php if (trim((string) $owner_bio) !== ''): ?>
              <p><?php echo esc_html($owner_bio); ?></p>
            <?php endif; ?>
          </div>
        </article>
      </div>
    </section>
  <?php endif; ?>

  <section class="page-section">
    <div class="container about-wrap">
      <div class="about-cta">
        <h2><?php echo esc_html($cta_h2); ?></h2>
        <p><?php echo esc_html($cta_p); ?></p>
        <a class="btn btn--accent" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a>
      </div>
    </div>
  </section>
</main>

<?php get_footer(); ?>
