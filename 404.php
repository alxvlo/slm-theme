<?php get_header(); ?>

<main id="main-content" class="container" style="padding-top:60px; padding-bottom:80px; text-align:center;">
  <h1 style="font-size:48px; margin-bottom:12px;">404</h1>
  <p style="font-size:18px; color:var(--muted-foreground); margin-bottom:28px;">The page you're looking for doesn't exist or has been moved.</p>

  <div style="max-width:420px; margin:0 auto 32px;">
    <?php get_search_form(); ?>
  </div>

  <a class="btn" href="<?php echo esc_url(home_url('/')); ?>">Back to Home</a>
</main>

<?php get_footer(); ?>
