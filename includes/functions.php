<?php
/**
 * Core Functions for WPProAtoZ ACF Membership Uploader
 */

if (!defined('ABSPATH')) exit;

// ====================== ONE ENTRY PER USER ======================
function pe_get_or_create_user_entry() {
    if (!is_user_logged_in()) return false;

    $user_id = get_current_user_id();
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

    // Create new entry
    return wp_insert_post([
        'post_title'   => 'My PE Tracker - ' . date('F Y'),
        'post_type'    => $cpt_slug,
        'post_status'  => 'draft',
        'post_author'  => $user_id,
    ]);
}

// ====================== DYNAMIC TIER LIMITS ======================
function pe_apply_tier_limits($field) {
    if (!function_exists('pmpro_getMembershipLevelForUser')) {
        return $field;
    }

    $level = pmpro_getMembershipLevelForUser();
    if (!$level) return $field;

    $tier_limits = get_option('iv_tier_limits', []);

    // Fallback defaults
    if (empty($tier_limits)) {
        $tier_limits = [
            1 => ['max_images' => 5, 'max_video_mb' => 30],
            2 => ['max_images' => 10, 'max_video_mb' => 100],
        ];
    }

    $limits = $tier_limits[$level->id] ?? ['max_images' => 5, 'max_video_mb' => 30];

    $image_key = get_option('iv_image_field_key', '');
    $video_key = get_option('iv_video_field_key', '');

    if ($field['key'] === $image_key && isset($field['max'])) {
        $field['max'] = $limits['max_images'];
        $field['instructions'] = "Maximum {$limits['max_images']} images allowed per your tier.";
    }

    if ($field['key'] === $video_key) {
        $field['instructions'] = "Maximum video size: {$limits['max_video_mb']} MB per your tier. (Optional)";
    }

    return $field;
}
add_filter('acf/prepare_field', 'pe_apply_tier_limits', 20);

// ====================== UTILITY FUNCTIONS ======================
function iv_get_max_image_size() {
    return get_option('iv_max_image_size_mb', 1) * 1024 * 1024;
}

function iv_get_max_video_size() {
    return get_option('iv_max_video_size_mb', 30) * 1024 * 1024;
}