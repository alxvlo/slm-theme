<?php
/**
 * ACF Local Field Groups — SLM Theme
 *
 * Registers all admin-editable fields using ACF's local JSON API.
 * Requires ACF Free (no Repeater fields needed — we use numbered individual fields).
 *
 * @package SLM_Theme
 */
if (!defined('ABSPATH')) exit;
if (!function_exists('acf_add_local_field_group')) return;

/* ─────────────────────────────────────────────────────────────────
   1. HOMEPAGE FIELDS
   Attached to: the page set as Front Page
────────────────────────────────────────────────────────────────── */
acf_add_local_field_group([
  'key'    => 'group_slm_homepage',
  'title'  => 'Homepage Settings',
  'fields' => [

    /* ── SECTION: Hero Slider ── */
    ['key' => 'field_slm_hero_tab',       'label' => 'Hero Slider',      'name' => '',                     'type' => 'tab'],
    ['key' => 'field_hp_slide_1_image',   'label' => 'Slide 1 Image',    'name' => 'hp_slide_1_image',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Recommended: 1920×1080 or wider landscape photo.'],
    ['key' => 'field_hp_slide_2_image',   'label' => 'Slide 2 Image',    'name' => 'hp_slide_2_image',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium'],
    ['key' => 'field_hp_slide_3_image',   'label' => 'Slide 3 Image',    'name' => 'hp_slide_3_image',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium'],
    ['key' => 'field_hp_slide_4_image',   'label' => 'Slide 4 Image',    'name' => 'hp_slide_4_image',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium'],
    ['key' => 'field_hp_slide_5_image',   'label' => 'Slide 5 Image',    'name' => 'hp_slide_5_image',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium'],
    ['key' => 'field_hp_slide_6_image',   'label' => 'Slide 6 Image',    'name' => 'hp_slide_6_image',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium'],

    /* ── SECTION: Solution ── */
    ['key' => 'field_slm_solution_tab',   'label' => 'Solution Section', 'name' => '',                     'type' => 'tab'],
    ['key' => 'field_hp_solution_image',  'label' => 'Solution Image',   'name' => 'hp_solution_image',    'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Right-column media image for the Solution section.'],

    /* ── SECTION: Service Card Images ── */
    ['key' => 'field_slm_svc_tab',        'label' => 'Service Card Images', 'name' => '',                  'type' => 'tab'],
    ['key' => 'field_hp_svc_1_img',       'label' => 'Service 1 Background Image', 'name' => 'hp_svc_1_img', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Real Estate Photography card background.'],
    ['key' => 'field_hp_svc_2_img',       'label' => 'Service 2 Background Image', 'name' => 'hp_svc_2_img', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium'],
    ['key' => 'field_hp_svc_3_img',       'label' => 'Service 3 Background Image', 'name' => 'hp_svc_3_img', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium'],
    ['key' => 'field_hp_svc_4_img',       'label' => 'Service 4 Background Image', 'name' => 'hp_svc_4_img', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium'],
    ['key' => 'field_hp_svc_5_img',       'label' => 'Service 5 Background Image', 'name' => 'hp_svc_5_img', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium'],
    ['key' => 'field_hp_svc_6_img',       'label' => 'Service 6 Background Image', 'name' => 'hp_svc_6_img', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium'],

    /* ── SECTION: Final CTA Background ── */
    ['key' => 'field_slm_cta_tab',        'label' => 'Final CTA',        'name' => '',                     'type' => 'tab'],
    ['key' => 'field_hp_cta_bg_image',    'label' => 'CTA Background Image', 'name' => 'hp_cta_bg_image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Full-width background for the bottom CTA section.'],

  ],
  'location' => [[
    ['param' => 'page_type', 'operator' => '==', 'value' => 'front_page'],
  ]],
  'menu_order' => 0,
  'position'   => 'normal',
  'style'      => 'default',
  'label_placement' => 'top',
]);

/* ─────────────────────────────────────────────────────────────────
   2. ABOUT PAGE FIELDS
   Attached to: page with slug "about"
────────────────────────────────────────────────────────────────── */
acf_add_local_field_group([
  'key'    => 'group_slm_about',
  'title'  => 'About Page Settings',
  'fields' => [
    ['key' => 'field_about_hero_tab',     'label' => 'Hero',             'name' => '',                     'type' => 'tab'],
    ['key' => 'field_about_hero_image',   'label' => 'Hero Banner Image','name' => 'about_hero_image',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Full-width hero banner. Recommended: 1920×700+.'],

    ['key' => 'field_about_team_tab',     'label' => 'Team Members',     'name' => '',                     'type' => 'tab'],
    /* Team member 1 */
    ['key' => 'field_about_tm1_photo',    'label' => 'Team Member 1 — Photo',  'name' => 'about_tm1_photo',  'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail'],
    ['key' => 'field_about_tm1_name',     'label' => 'Team Member 1 — Name',   'name' => 'about_tm1_name',   'type' => 'text'],
    ['key' => 'field_about_tm1_title',    'label' => 'Team Member 1 — Title',  'name' => 'about_tm1_title',  'type' => 'text'],
    ['key' => 'field_about_tm1_bio',      'label' => 'Team Member 1 — Bio',    'name' => 'about_tm1_bio',    'type' => 'textarea', 'rows' => 3],
    /* Team member 2 */
    ['key' => 'field_about_tm2_photo',    'label' => 'Team Member 2 — Photo',  'name' => 'about_tm2_photo',  'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail'],
    ['key' => 'field_about_tm2_name',     'label' => 'Team Member 2 — Name',   'name' => 'about_tm2_name',   'type' => 'text'],
    ['key' => 'field_about_tm2_title',    'label' => 'Team Member 2 — Title',  'name' => 'about_tm2_title',  'type' => 'text'],
    ['key' => 'field_about_tm2_bio',      'label' => 'Team Member 2 — Bio',    'name' => 'about_tm2_bio',    'type' => 'textarea', 'rows' => 3],
    /* Team member 3 */
    ['key' => 'field_about_tm3_photo',    'label' => 'Team Member 3 — Photo',  'name' => 'about_tm3_photo',  'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail'],
    ['key' => 'field_about_tm3_name',     'label' => 'Team Member 3 — Name',   'name' => 'about_tm3_name',   'type' => 'text'],
    ['key' => 'field_about_tm3_title',    'label' => 'Team Member 3 — Title',  'name' => 'about_tm3_title',  'type' => 'text'],
    ['key' => 'field_about_tm3_bio',      'label' => 'Team Member 3 — Bio',    'name' => 'about_tm3_bio',    'type' => 'textarea', 'rows' => 3],
    /* Team member 4 */
    ['key' => 'field_about_tm4_photo',    'label' => 'Team Member 4 — Photo',  'name' => 'about_tm4_photo',  'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail'],
    ['key' => 'field_about_tm4_name',     'label' => 'Team Member 4 — Name',   'name' => 'about_tm4_name',   'type' => 'text'],
    ['key' => 'field_about_tm4_title',    'label' => 'Team Member 4 — Title',  'name' => 'about_tm4_title',  'type' => 'text'],
    ['key' => 'field_about_tm4_bio',      'label' => 'Team Member 4 — Bio',    'name' => 'about_tm4_bio',    'type' => 'textarea', 'rows' => 3],
  ],
  'location' => [[
    ['param' => 'page', 'operator' => '==', 'value' => 'page'],
    ['param' => 'page_template', 'operator' => '==', 'value' => 'templates/page-about.php'],
  ]],
  'menu_order' => 1,
  'position'   => 'normal',
  'style'      => 'default',
  'label_placement' => 'top',
]);

/* ─────────────────────────────────────────────────────────────────
   3. SERVICES PAGE FIELDS
   Attached to: page using page-services.php template
────────────────────────────────────────────────────────────────── */
acf_add_local_field_group([
  'key'    => 'group_slm_services',
  'title'  => 'Services Page Settings',
  'fields' => [
    ['key' => 'field_svc_hero_image', 'label' => 'Hero Banner Image', 'name' => 'services_hero_image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Full-width services page hero image.'],
  ],
  'location' => [[
    ['param' => 'page_template', 'operator' => '==', 'value' => 'templates/page-services.php'],
  ]],
  'menu_order' => 2,
  'position'   => 'normal',
  'style'      => 'default',
  'label_placement' => 'top',
]);

/* ─────────────────────────────────────────────────────────────────
   4. SERVICE DETAIL PAGE FIELDS
   Attached to: all 8 individual service detail templates
────────────────────────────────────────────────────────────────── */
$_slm_service_templates = [
  'templates/page-service-re-photography.php',
  'templates/page-service-re-videography.php',
  'templates/page-service-drone-photography.php',
  'templates/page-service-drone-videography.php',
  'templates/page-service-floor-plans.php',
  'templates/page-service-twilight-photography.php',
  'templates/page-service-virtual-tours.php',
  'templates/page-service-zillow-showcase.php',
];

$_slm_svc_detail_location = [];
foreach ($_slm_service_templates as $tpl) {
  $_slm_svc_detail_location[] = [
    ['param' => 'page_template', 'operator' => '==', 'value' => $tpl],
  ];
}

acf_add_local_field_group([
  'key'    => 'group_slm_service_detail',
  'title'  => 'Service Detail Page Settings',
  'fields' => [
    ['key' => 'field_svcdet_hero_image',   'label' => 'Hero Image',       'name' => 'svcdet_hero_image',    'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Full-width hero photo for this service.'],
    ['key' => 'field_svcdet_gallery_tab',  'label' => 'Example Gallery',  'name' => '',                      'type' => 'tab'],
    ['key' => 'field_svcdet_gallery_1',    'label' => 'Gallery Image 1',  'name' => 'svcdet_gallery_1',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail'],
    ['key' => 'field_svcdet_gallery_2',    'label' => 'Gallery Image 2',  'name' => 'svcdet_gallery_2',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail'],
    ['key' => 'field_svcdet_gallery_3',    'label' => 'Gallery Image 3',  'name' => 'svcdet_gallery_3',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail'],
    ['key' => 'field_svcdet_gallery_4',    'label' => 'Gallery Image 4',  'name' => 'svcdet_gallery_4',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail'],
    ['key' => 'field_svcdet_gallery_5',    'label' => 'Gallery Image 5',  'name' => 'svcdet_gallery_5',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail'],
    ['key' => 'field_svcdet_gallery_6',    'label' => 'Gallery Image 6',  'name' => 'svcdet_gallery_6',     'type' => 'image', 'return_format' => 'id', 'preview_size' => 'thumbnail'],
  ],
  'location'   => $_slm_svc_detail_location,
  'menu_order' => 3,
  'position'   => 'normal',
  'style'      => 'default',
  'label_placement' => 'top',
]);
unset($_slm_service_templates, $_slm_svc_detail_location);

/* ─────────────────────────────────────────────────────────────────
   5. CONTACT PAGE FIELDS
────────────────────────────────────────────────────────────────── */
acf_add_local_field_group([
  'key'    => 'group_slm_contact',
  'title'  => 'Contact Page Settings',
  'fields' => [
    ['key' => 'field_contact_feature_image', 'label' => 'Feature / Background Image', 'name' => 'contact_feature_image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Left-column background image on the contact page.'],
  ],
  'location' => [[
    ['param' => 'page_template', 'operator' => '==', 'value' => 'templates/page-contact.php'],
  ]],
  'menu_order' => 4,
  'position'   => 'normal',
  'style'      => 'default',
  'label_placement' => 'top',
]);

/* ─────────────────────────────────────────────────────────────────
   6. LOGIN PAGE FIELDS
────────────────────────────────────────────────────────────────── */
acf_add_local_field_group([
  'key'    => 'group_slm_login',
  'title'  => 'Login Page Settings',
  'fields' => [
    ['key' => 'field_login_bg_image', 'label' => 'Background Image', 'name' => 'login_bg_image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Full-page background for the login/signup page.'],
  ],
  'location' => [[
    ['param' => 'page_template', 'operator' => '==', 'value' => 'templates/page-login.php'],
  ]],
  'menu_order' => 5,
  'position'   => 'normal',
  'style'      => 'default',
  'label_placement' => 'top',
]);

/* ─────────────────────────────────────────────────────────────────
   7. MEMBERSHIPS PAGE FIELDS
────────────────────────────────────────────────────────────────── */
acf_add_local_field_group([
  'key'    => 'group_slm_memberships',
  'title'  => 'Memberships Page Settings',
  'fields' => [
    ['key' => 'field_mem_hero_image', 'label' => 'Hero / Background Image', 'name' => 'memberships_hero_image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium', 'instructions' => 'Hero background image for the memberships page.'],
  ],
  'location' => [[
    ['param' => 'page_template', 'operator' => '==', 'value' => 'templates/page-memberships.php'],
  ]],
  'menu_order' => 6,
  'position'   => 'normal',
  'style'      => 'default',
  'label_placement' => 'top',
]);
