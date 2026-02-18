<?php
if (!defined('ABSPATH')) exit;

$login_url = add_query_arg('mode', 'login', slm_login_url());
$signup_url = add_query_arg('mode', 'signup', slm_login_url());
?>

<main>
  <section class="home-hero" style="background-image:url('https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=2000&q=80');">
    <div class="home-hero__overlay"></div>

    <div class="container home-hero__content">
      <h1>Premium Real Estate Media for Faster, Better Sales</h1>
      <p>Photography, video, drone, and virtual tours designed to help every listing stand out from the first impression.</p>

      <div class="home-hero__actions">
        <a class="btn btn--accent" href="<?php echo esc_url($signup_url); ?>">Create Account</a>
        <a class="btn btn--ghostLight" href="<?php echo esc_url(home_url('/services/')); ?>">Browse Services</a>
      </div>
    </div>
  </section>

  <section class="home-section">
    <div class="container">
      <h2 class="center">How It Works</h2>
      <p class="center sub">A simple workflow for new clients and returning teams.</p>

      <div class="services-grid">
        <article class="service-card">
          <div class="service-icon">1</div>
          <h3>Create Your Account</h3>
          <p>Sign up and tell us about your business preferences.</p>
        </article>

        <article class="service-card">
          <div class="service-icon">2</div>
          <h3>Place Your Order</h3>
          <p>Choose services, property details, and scheduling windows.</p>
        </article>

        <article class="service-card">
          <div class="service-icon">3</div>
          <h3>Track Progress</h3>
          <p>Use your dashboard to monitor status and delivery timelines.</p>
        </article>

        <article class="service-card">
          <div class="service-icon">4</div>
          <h3>Download Deliverables</h3>
          <p>Get finished assets from your portal, ready for MLS and marketing.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="home-cta">
    <div class="container">
      <div class="cta-box">
        <h2>Guest Access Starts Here</h2>
        <p>New users can register and immediately access the correct dashboard based on account role.</p>
        <a class="btn" href="<?php echo esc_url($login_url); ?>">Go to Login</a>
      </div>
    </div>
  </section>
</main>
