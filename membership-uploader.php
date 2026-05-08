<?php
/**
 * Plugin Name: WPProAtoZ ACF Membership Uploader
 * Description: Tiered frontend uploader for membership sites (PE Tracker). One entry per user + PMPro integration. Built on ACF Pro.
 * Author: WPProAtoZ
 * Author URI: https://wpproatoz.com
 * Version: 2.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: wpproatoz-acf-membership-uploader
 * Update URI: https://github.com/Ahkonsu/wpproatoz-acf-membership-uploader/releases
 * GitHub Plugin URI: https://github.com/Ahkonsu/wpproatoz-acf-membership-uploader
 * GitHub Branch: main
 * Requires Plugins: advanced-custom-fields-pro, paid-memberships-pro
 */

require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/Ahkonsu/wpproatoz-acf-membership-uploader/',
    __FILE__,
    'wpproatoz-acf-membership-uploader'
);
$myUpdateChecker->setBranch('main');

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'iv_add_settings_link');
function iv_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=iv-settings') . '">' . __('Settings', 'wpproatoz-acf-membership-uploader') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// === ONE ENTRY PER USER HELPER ===
function pe_get_or_create_user_entry() {
    if (!is_user_logged_in()) {
        return false;
    }

    $user_id  = get_current_user_id();
    $cpt_slug = get_option('iv_cpt_slug', 'pe_tracker_entry');

    $existing = get_posts([
        'post_type'      => $cpt_slug,
        'author'         => $user_id,
        'posts_per_page' => 1,
        'post_status'    => ['publish', 'draft'],
        'orderby'        => 'ID',
        'order'          => 'ASC'
    ]);

    if (!empty($existing)) {
        return $existing[0]->ID;
    }

    // Create new draft
    $post_id = wp_insert_post([
        'post_title'   => 'My PE Tracker - ' . date('F Y'),
        'post_type'    => $cpt_slug,
        'post_status'  => 'draft',
        'post_author'  => $user_id,
    ]);

    return $post_id;
}

// === TIER LIMITS (PMPro) ===
function pe_apply_tier_limits($field) {
    if (!function_exists('pmpro_getMembershipLevelForUser')) {
        return $field;
    }

    $level = pmpro_getMembershipLevelForUser();
    if (!$level) {
        return $field;
    }

    // Map PMPro Level IDs → Limits (edit these!)
    $tier_limits = [
        1 => ['max_images' => 5,  'max_video_mb' => 30],   // Basic
        2 => ['max_images' => 10, 'max_video_mb' => 100],  // Premium
        // Add more levels as needed
    ];

    $limits = $tier_limits[$level->id] ?? ['max_images' => 5, 'max_video_mb' => 30];

    $image_field_key = get_option('iv_image_field_key', '');
    $video_field_key = get_option('iv_video_field_key', '');

    if ($field['key'] === $image_field_key && isset($field['max'])) {
        $field['max'] = $limits['max_images'];
        $field['instructions'] = "Max {$limits['max_images']} images per your tier.";
    }

    if ($field['key'] === $video_field_key) {
        $field['instructions'] = "Max video size: {$limits['max_video_mb']} MB per your tier.";
    }

    return $field;
}
add_filter('acf/prepare_field', 'pe_apply_tier_limits', 20);

// === SCRIPTS & STYLES ===
function iv_scripts() {
    wp_enqueue_style(
        'iv-style',
        plugin_dir_url(__FILE__) . 'css/iv-style.css',
        array(),
        '2.0.0'
    );

    if (is_page() && (has_shortcode(get_post()->post_content, 'pe_tracker_uploader') || has_shortcode(get_post()->post_content, 'image_video_submission'))) {
        // ... (keep your existing recaptcha + JS enqueue logic)
        wp_enqueue_script('iv-custom-js', plugin_dir_url(__FILE__) . 'iv-custom.js', array('jquery'), '2.0.0', true);
        // Localize script (keep existing + add new if needed)
    }
}
add_action('wp_enqueue_scripts', 'iv_scripts');

// ... (keep TGM, acf_form_head, rich edit disable, etc. from original)

// === UPDATED SHORTCODE ===
add_shortcode('pe_tracker_uploader', 'iv_display_submission_form');
function iv_display_submission_form() {
    if (!class_exists('ACF')) {
        return '<p>Error: Advanced Custom Fields Pro is required.</p>';
    }

    $cpt_slug         = get_option('iv_cpt_slug', 'pe_tracker_entry');
    $upload_mode      = get_option('iv_upload_mode', 'both');
    $image_field_key  = get_option('iv_image_field_key', '');
    $video_field_key  = get_option('iv_video_field_key', '');

    $fields = [];
    if (($upload_mode === 'images' || $upload_mode === 'both') && !empty($image_field_key)) {
        $fields[] = $image_field_key;
    }
    if (($upload_mode === 'videos' || $upload_mode === 'both') && !empty($video_field_key)) {
        $fields[] = $video_field_key;
    }

    if (empty($fields)) {
        return '<p><strong>DEBUG:</strong> No ACF fields configured. Check settings.</p>';
    }

    // Force logged-in only for membership use
    if (!is_user_logged_in()) {
        return '<p>Please log in to access your PE Tracker.</p>';
    }

    $entry_id = pe_get_or_create_user_entry();

    ob_start();

    if (isset($_GET['updated']) && $_GET['updated'] === 'true') {
        echo '<p class="iv-success-message">✅ Tracker updated successfully!</p>';
    }

    // Keep your recaptcha/honeypot logic here (same as original)

    acf_form([
        'post_id'         => $entry_id,
        'post_title'      => false,
        'post_content'    => false,
        'fields'          => $fields,
        'submit_value'    => __('Save My Tracker Updates', 'wpproatoz-acf-membership-uploader'),
        'return'          => add_query_arg('updated', 'true', get_permalink()),
        'form_attributes' => ['enctype' => 'multipart/form-data'],
        'html_before_fields' => $honeypot_html ?? '',
        'html_after_fields'  => $recaptcha_html ?? '',
        'uploader'        => 'basic'
    ]);

    return ob_get_clean();
}

// Keep your original validation filter (iv_validate_submission) — it still works great

// === PMPro Account Integration (add this section) ===
add_action('pmpro_account_after_membership', 'pe_tracker_add_account_section');
function pe_tracker_add_account_section() {
    if (!pmpro_hasMembershipLevel() || !is_user_logged_in()) {
        return;
    }

    echo '<div class="pmpro_account-section pe-tracker-section">';
    echo '<h2>My PE Tracker</h2>';
    echo '<p>Manage your images and video below. Limits are based on your membership tier.</p>';

    echo do_shortcode('[pe_tracker_uploader]');

    echo '</div>';
}

// === ADMIN MENU & SETTINGS (keep most of original, update titles) ===
add_action('admin_menu', 'iv_add_admin_menu');
function iv_add_admin_menu() {
    add_menu_page(
        'ACF Membership Uploader',
        'Membership Uploader',
        'manage_options',
        'iv-settings',
        'iv_settings_page',
        'dashicons-format-image',
        30
    );
    // ... keep the rest of your menu and settings page function
}

// Update plugin text domain references where needed (search/replace old domain)

// Keep the rest of your manage submissions, styles, etc.

?>
