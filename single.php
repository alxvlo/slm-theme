<?php
if (!defined('ABSPATH')) exit;

get_header();

$placeholder = get_template_directory_uri() . '/assets/img/placeholder.jpg';
?>

<main>
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <?php
      $img = get_the_post_thumbnail_url(get_the_ID(), 'large');
      if (!$img) $img = $placeholder;
      $cats = get_the_category();
      $cat_label = !empty($cats) ? $cats[0]->name : 'Article';
    ?>

    <section class="page-hero page-hero--image" style="background-image:url('<?php echo esc_url($img); ?>');">
      <div class="page-hero__overlay"></div>
      <div class="container page-hero__content">
        <p class="kicker" style="color:#fff; opacity:.9; margin:0 0 8px;"><?php echo esc_html($cat_label); ?></p>
        <h1><?php the_title(); ?></h1>
        <p class="page-hero__sub"><?php echo esc_html(get_the_date('F j, Y')); ?></p>
      </div>
      <svg class="page-hero__curve" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true">
        <path fill="#ffffff" d="M0,96L120,80C240,64,480,32,720,32C960,32,1200,64,1320,80L1440,96L1440,120L0,120Z"></path>
      </svg>
    </section>

    <section class="page-section">
      <div class="container post-wrap">
        <article class="post-content">
          <?php the_content(); ?>
        </article>

        <div class="service-finalCta" style="margin-top:30px;">
          <h2>Need Listing Media Support?</h2>
          <p>Apply these insights with media and systems designed to improve listing performance and brand positioning.</p>
          <div class="service-finalCta__actions">
            <?php if (is_user_logged_in()): ?>
              <a class="btn btn--accent" href="<?php echo esc_url(slm_dashboard_url()); ?>">Go to Dashboard</a>
            <?php else: ?>
              <a class="btn btn--accent" href="<?php echo esc_url(add_query_arg('mode', 'signup', slm_login_url())); ?>">Create Account to Order</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  <?php endwhile; endif; ?>
</main>

<?php get_footer(); ?>
