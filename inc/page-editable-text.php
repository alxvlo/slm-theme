<?php
if (!defined('ABSPATH'))
  exit;

function slm_get_editable_fields_for_template(string $template): array
{
  if ($template === 'templates/page-about.php') {
    return [
      'slm_about_hero_title' => ['label' => 'Hero Title', 'type' => 'text', 'default' => 'About Showcase Listings Media'],
      'slm_about_hero_sub' => ['label' => 'Hero Subtitle', 'type' => 'text', 'default' => 'Break the standard. Showcase the difference.'],
      'slm_about_intro_h2' => ['label' => 'Intro H2', 'type' => 'text', 'default' => 'Where Listings Become Showcase-Worthy'],
      'slm_about_intro_p1' => ['label' => 'Intro Paragraph 1', 'type' => 'textarea', 'default' => 'Before launching Showcase Listings Media, our owner worked alongside agents through tight deadlines, nonstop pressure, and the constant need to stay visible online while still doing the job. The same pattern appeared repeatedly: marketing mattered, but most offers were priced per add-on, per minute, or per revision.'],
      'slm_about_intro_p2' => ['label' => 'Intro Paragraph 2', 'type' => 'textarea', 'default' => 'Showcase Listings Media was built to fix that. We focus on practical systems, polished execution, and consistent results so clients can maintain strong marketing without overextending their budget, while still getting a luxury feel at a practical cost.'],
      'slm_about_values_h2' => ['label' => 'Values Section Title', 'type' => 'text', 'default' => 'Core Values'],
      'slm_about_values_sub' => ['label' => 'Values Section Subtitle', 'type' => 'textarea', 'default' => 'We focus on practical, repeatable marketing systems, clear pricing, and flexible execution that supports how agents actually work.'],
      'slm_about_values_list' => ['label' => 'Core Values (Format: Title | Description, one per line)', 'type' => 'textarea', 'default' => implode("\n", [
        'Client Partnership | We operate as partners, not vendors. Your success, growth, and visibility are our priority.',
        'Practical Consistency | We build repeatable workflows so marketing remains active even when schedules are busy.',
        'Clear, Honest Pricing | No nickel-and-diming. Expectations, scope, and support stay transparent.',
        'Luxury-Level Presentation | We deliver polished visuals and strategy without unnecessary complexity.',
        'Service-First Execution | Responsive communication and dependable delivery remain non-negotiable.',
        'Long-Term Momentum | We prioritize sustainable progress over one-off transactions.',
      ])],
      'slm_about_outcomes_h2' => ['label' => 'Outcomes Title', 'type' => 'text', 'default' => 'What Clients Gain'],
      'slm_about_outcomes_sub' => ['label' => 'Outcomes Subtitle', 'type' => 'textarea', 'default' => 'Our services are structured around business outcomes, not just deliverables.'],
      'slm_about_outcomes_list' => ['label' => 'Outcomes List (One per line)', 'type' => 'textarea', 'default' => implode("\n", [
        'Winning more listing presentations',
        'Elevating brand perception in your market',
        'Attracting higher-value opportunities',
        'Saving time through streamlined workflows',
        'Consistent marketing without overextending your budget',
        'Showing up confidently and consistently in your marketing',
        'Building scalable systems for long-term growth',
      ])],
      'slm_about_compare_h2' => ['label' => 'Compare Title', 'type' => 'text', 'default' => 'Why Agents Switch'],
      'slm_about_compare_sub' => ['label' => 'Compare Subtitle', 'type' => 'textarea', 'default' => 'Most agents do not switch media partners on a whim. They switch when they want better systems, better support, and better outcomes.'],
      'slm_about_traditional_list' => ['label' => 'Traditional List (One per line)', 'type' => 'textarea', 'default' => implode("\n", [
        'Per-shoot transactional services',
        'One-size-fits-all package structures',
        'Limited strategic support',
        'Luxury-style pricing without practical flexibility',
        'Production volume over long-term partnership',
      ])],
      'slm_about_showcase_list' => ['label' => 'Showcase List (One per line)', 'type' => 'textarea', 'default' => implode("\n", [
        'Designed for consistency across every client while adapting to each brand',
        'Flexible options aligned to business goals',
        'Strategy, education, and execution in one partnership',
        'Budget-responsible delivery with a polished luxury feel',
        'Long-term client success as the priority',
      ])],
      'slm_about_owner_h2' => ['label' => 'Owner Section Title', 'type' => 'text', 'default' => 'Meet the Owner'],
      'slm_about_owner_name' => ['label' => 'Owner Name', 'type' => 'text', 'default' => ''],
      'slm_about_owner_role' => ['label' => 'Owner Role', 'type' => 'text', 'default' => ''],
      'slm_about_owner_bio' => ['label' => 'Owner Bio', 'type' => 'textarea', 'default' => ''],
      'slm_about_owner_photo_id' => ['label' => 'Owner Photo', 'type' => 'image', 'default' => ''],
      'slm_about_cta_h2' => ['label' => 'CTA Title', 'type' => 'text', 'default' => 'Ready to Build Marketing Momentum?'],
      'slm_about_cta_p' => ['label' => 'CTA Text', 'type' => 'textarea', 'default' => 'Create your account and start ordering media designed to help you win listings, improve perception, and scale with confidence, professionally and sustainably. Your success is our success.'],
    ];
  }

  if ($template === 'templates/page-contact.php') {
    return [
      'slm_contact_hero_title' => ['label' => 'Hero Title', 'type' => 'text', 'default' => 'Contact Showcase Listings Media'],
      'slm_contact_hero_sub' => ['label' => 'Hero Subtitle', 'type' => 'text', 'default' => 'Strategic support, responsive service, and marketing built around better outcomes.'],
      'slm_contact_left_h2' => ['label' => 'Contact Info Title', 'type' => 'text', 'default' => 'Let us build your competitive advantage'],
      'slm_contact_left_p' => ['label' => 'Contact Info Text', 'type' => 'textarea', 'default' => 'Whether you need listing media, pricing guidance, or support choosing the right package, our team is here to help you move faster with confidence.'],
      'slm_contact_form_title' => ['label' => 'Form Box Title', 'type' => 'text', 'default' => 'Send us a message'],
      'slm_contact_bottom_text' => ['label' => 'Bottom CTA Text', 'type' => 'text', 'default' => 'Ready to start your next listing campaign?'],
    ];
  }

  if ($template === 'templates/page-portfolio.php') {
    return [
      'slm_portfolio_title' => ['label' => 'Portfolio Hero Title', 'type' => 'text', 'default' => 'Our Portfolio'],
      'slm_portfolio_sub' => ['label' => 'Portfolio Hero Subtitle', 'type' => 'textarea', 'default' => 'Featured listing media and campaign examples built to help agents stand out.'],
    ];
  }

  return [];
}

function slm_page_editable_template_supported(string $template): bool
{
  return in_array($template, ['templates/page-about.php', 'templates/page-contact.php', 'templates/page-portfolio.php'], true);
}

function slm_page_editable_fields_include_image(string $template): bool
{
  $fields = slm_get_editable_fields_for_template($template);
  foreach ($fields as $config) {
    if ((string) ($config['type'] ?? 'text') === 'image') {
      return true;
    }
  }
  return false;
}

add_action('add_meta_boxes', function () {
  global $post;
  if (!$post) {
    return;
  }

  $template = (string) get_post_meta($post->ID, '_wp_page_template', true);
  if (slm_page_editable_template_supported($template)) {
    add_meta_box('slm_page_text_meta_box', 'Editable Page Text', 'slm_render_page_text_meta_box', 'page', 'normal', 'high');
  }
});

function slm_render_page_text_meta_box($post)
{
  wp_nonce_field('slm_page_text_save', 'slm_page_text_nonce');
  $template = (string) get_post_meta($post->ID, '_wp_page_template', true);
  $fields = slm_get_editable_fields_for_template($template);
  $has_image_fields = false;

  foreach ($fields as $key => $config) {
    $type = (string) ($config['type'] ?? 'text');
    $val = get_post_meta($post->ID, $key, true);
    if ($val === '') {
      $val = $config['default'];
    }

    echo '<p><strong>' . esc_html((string) $config['label']) . '</strong></p>';
    if ($type === 'textarea') {
      echo '<textarea name="' . esc_attr($key) . '" style="width:100%; height:90px; font-family:monospace;">' . esc_textarea((string) $val) . '</textarea>';
      continue;
    }

    if ($type === 'image') {
      $has_image_fields = true;
      $image_id = absint((string) $val);
      $image_url = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'medium') : '';

      echo '<div class="slm-imageField" data-target="' . esc_attr($key) . '">';
      echo '  <input type="hidden" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr((string) $image_id) . '" />';
      echo '  <div id="' . esc_attr($key . '_preview') . '" style="width:220px; min-height:126px; border:1px solid rgba(0,0,0,.14); border-radius:10px; display:grid; place-items:center; background:#f8fbff; overflow:hidden;">';
      if ($image_url) {
        echo '<img src="' . esc_url($image_url) . '" alt="" style="width:100%; height:100%; object-fit:cover; display:block;" />';
      } else {
        echo '<span style="font-size:12px; color:#60708a;">No image selected</span>';
      }
      echo '  </div>';
      echo '  <p style="margin:8px 0 0; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">';
      echo '    <button type="button" class="button slm-imageField__select" data-target="' . esc_attr($key) . '">Select Image</button>';
      echo '    <button type="button" class="button-link-delete slm-imageField__clear" data-target="' . esc_attr($key) . '"' . ($image_id > 0 ? '' : ' style="display:none;"') . '>Remove</button>';
      echo '  </p>';
      echo '</div>';
      continue;
    }

    echo '<input type="text" name="' . esc_attr($key) . '" value="' . esc_attr((string) $val) . '" style="width:100%;" />';
  }

  if (!$has_image_fields) {
    return;
  }

  wp_enqueue_media();
  static $slm_page_text_image_script_printed = false;
  if ($slm_page_text_image_script_printed) {
    return;
  }
  $slm_page_text_image_script_printed = true;
  ?>
  <script>
    (function ($) {
      function bindImageField($root) {
        var target = $root.data('target');
        if (!target) return;
        var $input = $('#' + target);
        var $preview = $('#' + target + '_preview');
        var $select = $root.find('.slm-imageField__select');
        var $clear = $root.find('.slm-imageField__clear');
        if (!$input.length || !$preview.length || !$select.length || !$clear.length) return;

        var frame = null;
        $select.on('click', function (e) {
          e.preventDefault();
          if (!frame) {
            frame = wp.media({
              title: 'Select Image',
              button: { text: 'Use this image' },
              library: { type: 'image' },
              multiple: false
            });

            frame.on('select', function () {
              var attachment = frame.state().get('selection').first();
              if (!attachment) return;
              var attrs = attachment.toJSON ? attachment.toJSON() : attachment.attributes || {};
              var id = Number(attrs.id || 0);
              var sizes = attrs.sizes || {};
              var chosen = (sizes.medium && sizes.medium.url) || (sizes.large && sizes.large.url) || attrs.url || '';
              $input.val(id > 0 ? String(id) : '');
              if (chosen) {
                $preview.html('<img src="' + chosen + '" alt="" style="width:100%; height:100%; object-fit:cover; display:block;" />');
              } else {
                $preview.html('<span style="font-size:12px; color:#60708a;">No image selected</span>');
              }
              $clear.show();
            });
          }
          frame.open();
        });

        $clear.on('click', function (e) {
          e.preventDefault();
          $input.val('');
          $preview.html('<span style="font-size:12px; color:#60708a;">No image selected</span>');
          $clear.hide();
        });
      }

      $(function () {
        $('.slm-imageField').each(function () {
          bindImageField($(this));
        });
      });
    })(jQuery);
  </script>
  <?php
}

add_action('save_post_page', function ($post_id) {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
    return;
  if (!current_user_can('edit_page', $post_id))
    return;
  if (!isset($_POST['slm_page_text_nonce']) || !wp_verify_nonce($_POST['slm_page_text_nonce'], 'slm_page_text_save'))
    return;

  $template = (string) get_post_meta($post_id, '_wp_page_template', true);
  $fields = slm_get_editable_fields_for_template($template);

  foreach ($fields as $key => $config) {
    if (!isset($_POST[$key])) {
      continue;
    }

    $type = (string) ($config['type'] ?? 'text');
    $val = wp_unslash($_POST[$key]);
    if ($type === 'textarea') {
      update_post_meta($post_id, $key, sanitize_textarea_field($val));
    } elseif ($type === 'image') {
      update_post_meta($post_id, $key, absint((string) $val));
    } else {
      update_post_meta($post_id, $key, sanitize_text_field($val));
    }
  }
});
