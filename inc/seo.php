<?php
if (!defined('ABSPATH')) exit;

/**
 * SEO layer: title, meta description, Open Graph, Twitter Card, JSON-LD,
 * plus an editor meta box for per-page overrides.
 */

const SLM_SEO_DESCRIPTION_MAX = 160;

function slm_seo_resolve_post_id(): int
{
  if (is_singular() || is_page()) {
    $id = get_queried_object_id();
    if ($id > 0) return (int) $id;
  }
  if (is_front_page()) {
    $front = (int) get_option('page_on_front');
    if ($front > 0) return $front;
  }
  return 0;
}

function slm_seo_clean_text(string $text): string
{
  $text = wp_strip_all_tags($text);
  $text = preg_replace('/\s+/', ' ', $text);
  return trim((string) $text);
}

function slm_seo_truncate(string $text, int $limit): string
{
  $text = slm_seo_clean_text($text);
  if ($text === '') return '';
  if (function_exists('mb_strlen') && mb_strlen($text) <= $limit) {
    return $text;
  }
  if (!function_exists('mb_strlen') && strlen($text) <= $limit) {
    return $text;
  }

  $truncated = function_exists('mb_substr')
    ? mb_substr($text, 0, max(1, $limit - 1))
    : substr($text, 0, max(1, $limit - 1));

  $space_pos = function_exists('mb_strrpos')
    ? mb_strrpos($truncated, ' ')
    : strrpos($truncated, ' ');

  if ($space_pos !== false && $space_pos > 40) {
    $truncated = function_exists('mb_substr')
      ? mb_substr($truncated, 0, $space_pos)
      : substr($truncated, 0, $space_pos);
  }

  return rtrim($truncated, " ,.;:-") . '…';
}

function slm_seo_get_title(): string
{
  $site_name = (string) get_bloginfo('name');
  $tagline = (string) get_bloginfo('description');

  if (is_front_page() || is_home()) {
    $home_id = slm_seo_resolve_post_id();
    if ($home_id > 0) {
      $override = (string) get_post_meta($home_id, 'slm_meta_title', true);
      if ($override !== '') {
        return slm_seo_clean_text($override);
      }
    }
    return $tagline !== ''
      ? $site_name . ' — ' . $tagline
      : $site_name;
  }

  $post_id = slm_seo_resolve_post_id();
  if ($post_id > 0) {
    $override = (string) get_post_meta($post_id, 'slm_meta_title', true);
    if ($override !== '') {
      return slm_seo_clean_text($override) . ' | ' . $site_name;
    }
  }

  $page_title = trim((string) wp_title('', false));
  if ($page_title !== '') {
    return $page_title . ' | ' . $site_name;
  }

  return $site_name;
}

function slm_seo_get_description(): string
{
  $post_id = slm_seo_resolve_post_id();
  if ($post_id > 0) {
    $override = (string) get_post_meta($post_id, 'slm_meta_description', true);
    if ($override !== '') {
      return slm_seo_truncate($override, SLM_SEO_DESCRIPTION_MAX);
    }
  }

  if (is_front_page() || is_home()) {
    $tagline = (string) get_bloginfo('description');
    if ($tagline !== '') {
      return slm_seo_truncate($tagline, SLM_SEO_DESCRIPTION_MAX);
    }
  }

  if ($post_id > 0) {
    $excerpt = (string) get_post_field('post_excerpt', $post_id);
    if ($excerpt !== '') {
      return slm_seo_truncate($excerpt, SLM_SEO_DESCRIPTION_MAX);
    }
    $content = (string) get_post_field('post_content', $post_id);
    if ($content !== '') {
      return slm_seo_truncate($content, SLM_SEO_DESCRIPTION_MAX);
    }
  }

  $tagline = (string) get_bloginfo('description');
  return slm_seo_truncate($tagline, SLM_SEO_DESCRIPTION_MAX);
}

function slm_seo_get_og_image_url(): string
{
  $post_id = slm_seo_resolve_post_id();
  if ($post_id > 0) {
    $override = (string) get_post_meta($post_id, 'slm_og_image', true);
    if ($override !== '' && is_numeric($override)) {
      $src = wp_get_attachment_image_src((int) $override, 'full');
      if (is_array($src) && !empty($src[0])) {
        return (string) $src[0];
      }
    } elseif ($override !== '') {
      return $override;
    }

    if (has_post_thumbnail($post_id)) {
      $src = wp_get_attachment_image_src((int) get_post_thumbnail_id($post_id), 'full');
      if (is_array($src) && !empty($src[0])) {
        return (string) $src[0];
      }
    }
  }

  $site_icon_id = (int) get_option('site_icon');
  if ($site_icon_id > 0) {
    $src = wp_get_attachment_image_src($site_icon_id, 'full');
    if (is_array($src) && !empty($src[0])) {
      return (string) $src[0];
    }
  }

  $logo_id = (int) get_theme_mod('custom_logo');
  if ($logo_id > 0) {
    $src = wp_get_attachment_image_src($logo_id, 'full');
    if (is_array($src) && !empty($src[0])) {
      return (string) $src[0];
    }
  }

  return '';
}

function slm_seo_get_canonical_url(): string
{
  if (is_front_page() || is_home()) {
    return home_url('/');
  }
  $post_id = slm_seo_resolve_post_id();
  if ($post_id > 0) {
    $permalink = get_permalink($post_id);
    if (is_string($permalink) && $permalink !== '') {
      return $permalink;
    }
  }
  $request = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
  return home_url($request);
}

function slm_seo_get_og_type(): string
{
  if (is_singular('post')) return 'article';
  return 'website';
}

/**
 * Override <title> through the title-tag filter so we don't double-output.
 */
add_filter('pre_get_document_title', function ($title) {
  $built = slm_seo_get_title();
  return $built !== '' ? $built : $title;
}, 20);

/**
 * Emit description, Open Graph, Twitter Card, and JSON-LD.
 */
add_action('wp_head', 'slm_output_seo_tags', 1);

function slm_output_seo_tags(): void
{
  if (is_admin()) return;

  $description = slm_seo_get_description();
  $title = slm_seo_get_title();
  $canonical = slm_seo_get_canonical_url();
  $og_image = slm_seo_get_og_image_url();
  $og_type = slm_seo_get_og_type();
  $site_name = (string) get_bloginfo('name');

  echo "\n<!-- SLM SEO -->\n";

  if ($description !== '') {
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
  }

  echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";

  echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";
  echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
  if ($description !== '') {
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
  }
  echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
  if ($site_name !== '') {
    echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
  }
  if ($og_image !== '') {
    echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
  }

  echo '<meta name="twitter:card" content="' . esc_attr($og_image !== '' ? 'summary_large_image' : 'summary') . '">' . "\n";
  echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
  if ($description !== '') {
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
  }
  if ($og_image !== '') {
    echo '<meta name="twitter:image" content="' . esc_url($og_image) . '">' . "\n";
  }

  if (is_front_page()) {
    $same_as = array_values(array_filter(array_map('trim', [
      (string) get_theme_mod('slm_social_instagram', ''),
      (string) get_theme_mod('slm_social_facebook', ''),
      (string) get_theme_mod('slm_social_youtube', ''),
      (string) get_theme_mod('slm_social_linkedin', ''),
    ])));

    $schema = [
      '@context' => 'https://schema.org',
      '@type' => 'LocalBusiness',
      'name' => $site_name,
      'url' => home_url('/'),
      'description' => $description,
      'address' => [
        '@type' => 'PostalAddress',
        'addressRegion' => 'FL',
        'addressCountry' => 'US',
      ],
    ];
    if ($og_image !== '') {
      $schema['image'] = $og_image;
    }
    $footer_phone = (string) get_theme_mod('slm_footer_phone', '');
    if ($footer_phone !== '') {
      $schema['telephone'] = $footer_phone;
    }
    $footer_email = (string) get_theme_mod('slm_footer_email', '');
    if ($footer_email !== '') {
      $schema['email'] = $footer_email;
    }
    if (!empty($same_as)) {
      $schema['sameAs'] = $same_as;
    }

    $json = wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (is_string($json) && $json !== '') {
      echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
    }
  }

  echo "<!-- /SLM SEO -->\n";
}

/**
 * Editor meta box: SEO Settings on pages and posts.
 */
add_action('add_meta_boxes', function (): void {
  foreach (['page', 'post'] as $screen) {
    add_meta_box(
      'slm_seo_settings',
      __('SEO Settings', 'slm'),
      'slm_render_seo_meta_box',
      $screen,
      'normal',
      'default'
    );
  }
});

function slm_render_seo_meta_box(WP_Post $post): void
{
  wp_nonce_field('slm_seo_meta_save', 'slm_seo_meta_nonce');
  wp_enqueue_media();

  $meta_title = (string) get_post_meta($post->ID, 'slm_meta_title', true);
  $meta_description = (string) get_post_meta($post->ID, 'slm_meta_description', true);
  $og_image_id = (string) get_post_meta($post->ID, 'slm_og_image', true);

  $og_image_preview = '';
  if ($og_image_id !== '' && is_numeric($og_image_id)) {
    $src = wp_get_attachment_image_src((int) $og_image_id, 'medium');
    if (is_array($src) && !empty($src[0])) {
      $og_image_preview = (string) $src[0];
    }
  }
  ?>
  <style>
    .slm-seo-field { margin-bottom: 16px; }
    .slm-seo-field label { display: block; font-weight: 600; margin-bottom: 5px; }
    .slm-seo-helper { color: #6b7280; font-size: 12px; margin: 4px 0 0; }
    .slm-seo-counter { font-size: 12px; color: #6b7280; }
    .slm-seo-preview {
      margin-top: 8px;
      max-width: 280px;
      max-height: 160px;
      background: #f3f4f6;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .slm-seo-preview img { display: block; max-width: 100%; height: auto; }
    .slm-seo-preview--empty { padding: 22px; color: #6b7280; font-size: 12px; }
  </style>

  <div class="slm-seo-field">
    <label for="slm_meta_title">Meta Title</label>
    <input
      type="text"
      id="slm_meta_title"
      name="slm_meta_title"
      value="<?php echo esc_attr($meta_title); ?>"
      class="widefat"
      maxlength="70"
      placeholder="<?php echo esc_attr(get_the_title($post)); ?>"
    >
    <p class="slm-seo-helper">Optional override. Leave blank to use the page title.</p>
  </div>

  <div class="slm-seo-field">
    <label for="slm_meta_description">
      Meta Description
      <span class="slm-seo-counter" id="slm-seo-desc-counter">(0 / <?php echo esc_html((string) SLM_SEO_DESCRIPTION_MAX); ?>)</span>
    </label>
    <textarea
      id="slm_meta_description"
      name="slm_meta_description"
      class="widefat"
      rows="3"
      maxlength="<?php echo esc_attr((string) SLM_SEO_DESCRIPTION_MAX); ?>"
      placeholder="One or two sentences. Aim for ~155 characters."
    ><?php echo esc_textarea($meta_description); ?></textarea>
    <p class="slm-seo-helper">Used for search results and social previews. Falls back to the excerpt or site tagline.</p>
  </div>

  <div class="slm-seo-field">
    <label>Open Graph Image (social share)</label>
    <input type="hidden" id="slm_og_image" name="slm_og_image" value="<?php echo esc_attr($og_image_id); ?>">
    <div class="slm-seo-preview" id="slm-og-image-preview">
      <?php if ($og_image_preview !== ''): ?>
        <img src="<?php echo esc_url($og_image_preview); ?>" alt="">
      <?php else: ?>
        <span class="slm-seo-preview--empty">No image selected</span>
      <?php endif; ?>
    </div>
    <p style="margin-top:8px;">
      <button type="button" class="button" id="slm-og-image-select">Select Image</button>
      <button type="button" class="button" id="slm-og-image-remove">Remove</button>
    </p>
    <p class="slm-seo-helper">Recommended size 1200×630. Falls back to featured image, then site icon.</p>
  </div>

  <script>
  (function () {
    var $ = window.jQuery;
    if (!$) return;

    var counter = document.getElementById('slm-seo-desc-counter');
    var textarea = document.getElementById('slm_meta_description');
    function updateCounter() {
      if (!counter || !textarea) return;
      counter.textContent = '(' + textarea.value.length + ' / <?php echo esc_js((string) SLM_SEO_DESCRIPTION_MAX); ?>)';
    }
    if (textarea) {
      textarea.addEventListener('input', updateCounter);
      updateCounter();
    }

    var frame;
    var input = document.getElementById('slm_og_image');
    var preview = document.getElementById('slm-og-image-preview');

    $('#slm-og-image-select').on('click', function (e) {
      e.preventDefault();
      if (frame) { frame.open(); return; }
      frame = wp.media({
        title: 'Select OG Image',
        button: { text: 'Use this image' },
        library: { type: 'image' },
        multiple: false
      });
      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        if (input) input.value = attachment.id;
        if (preview) {
          preview.innerHTML = '<img src="' + attachment.url + '" alt="">';
        }
      });
      frame.open();
    });

    $('#slm-og-image-remove').on('click', function (e) {
      e.preventDefault();
      if (input) input.value = '';
      if (preview) preview.innerHTML = '<span class="slm-seo-preview--empty">No image selected</span>';
    });
  })();
  </script>
  <?php
}

add_action('save_post', function (int $post_id): void {
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  $nonce = (string) ($_POST['slm_seo_meta_nonce'] ?? '');
  if ($nonce === '' || !wp_verify_nonce($nonce, 'slm_seo_meta_save')) return;
  if (!current_user_can('edit_post', $post_id)) return;

  if (isset($_POST['slm_meta_title'])) {
    update_post_meta($post_id, 'slm_meta_title', sanitize_text_field(wp_unslash((string) $_POST['slm_meta_title'])));
  }
  if (isset($_POST['slm_meta_description'])) {
    update_post_meta($post_id, 'slm_meta_description', sanitize_textarea_field(wp_unslash((string) $_POST['slm_meta_description'])));
  }
  if (isset($_POST['slm_og_image'])) {
    $raw = trim((string) wp_unslash($_POST['slm_og_image']));
    if ($raw === '') {
      delete_post_meta($post_id, 'slm_og_image');
    } elseif (is_numeric($raw)) {
      update_post_meta($post_id, 'slm_og_image', (string) (int) $raw);
    } else {
      update_post_meta($post_id, 'slm_og_image', esc_url_raw($raw));
    }
  }
});
