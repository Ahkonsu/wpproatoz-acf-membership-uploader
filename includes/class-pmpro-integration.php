<?php
/**
 * Paid Memberships Pro Integration for WPProAtoZ ACF Membership Uploader
 */

if (!defined('ABSPATH')) exit;

// ====================== PMPro ACCOUNT PAGE INTEGRATION ======================
add_action('pmpro_after_account', 'pe_tracker_force_on_account_page', 99);
add_action('pmpro_account_after_content', 'pe_tracker_force_on_account_page', 99);

function pe_tracker_force_on_account_page() {
    if (!function_exists('pmpro_hasMembershipLevel') || !pmpro_hasMembershipLevel() || !is_user_logged_in()) {
        return;
    }

    $entry_id = pe_get_or_create_user_entry();
    if (!$entry_id) {
        return;
    }

    $image_key = get_option('iv_image_field_key', '');
    $video_key = get_option('iv_video_field_key', '');

    $fields = array_filter([$image_key, $video_key]);

    if (empty($fields)) {
        echo '<div class="pe-tracker-section" style="padding:20px; background:#fff0f0; border:2px solid #dc3232; margin:20px 0;">';
        echo '<h3>⚠️ Configuration Needed</h3>';
        echo '<p>Please configure your ACF Image and Video field keys in <strong>Membership Uploader → Settings</strong>.</p>';
        echo '</div>';
        return;
    }

    echo '<div class="pe-tracker-full-section">';
    echo '<h2>📸 My PE Tracker</h2>';
    echo '<p style="font-size:1.1em;">Upload or update images and video of your pet. Limits are based on your current membership tier.</p>';

    // Render the uploader form
    echo do_shortcode('[pe_tracker_uploader]');

    echo '</div>';
}

// Optional: Add a direct link in PMPro account navigation
add_filter('pmpro_account_nav_items', 'pe_add_tracker_nav_link');

function pe_add_tracker_nav_link($items) {
    // Insert after "Account" or at the end
    $items['pe-tracker'] = [
        'title' => 'My PE Tracker',
        'url'   => '#', // We use the forced section above instead
        'icon'  => 'dashicons-format-image'
    ];
    return $items;
}