<?php
/**
 * Validation & Deletion Handler
 */

if (!defined('ABSPATH')) exit;

add_filter('acf/pre_save_post', 'iv_validate_submission', 10, 2);

function iv_validate_submission($post_id, $acf_values) {
    if (get_post_type($post_id) !== get_option('iv_cpt_slug', 'pe_tracker_entry')) {
        return $post_id;
    }

    // reCAPTCHA + Honeypot (keep existing code)
    $recaptcha_type = get_option('iv_recaptcha_type', 'none');
    // ... your recaptcha code here ...

    return $post_id;
}

// ====================== HANDLE DELETIONS + BYPASS REQUIRED FIELDS ======================
add_action('acf/save_post', 'iv_handle_deletions_after_save', 20);

function iv_handle_deletions_after_save($post_id) {
    if (get_post_type($post_id) !== get_option('iv_cpt_slug', 'pe_tracker_entry')) {
        return;
    }

    $image_field_key = get_option('iv_image_field_key', '');
    $video_field_key = get_option('iv_video_field_key', '');

    // === DELETE IMAGES ===
    if (isset($_POST['iv_delete_images']) && $_POST['iv_delete_images'] == '1' && !empty($image_field_key)) {
        update_field($image_field_key, [], $post_id);
    }

    // === DELETE VIDEO ===
    if (isset($_POST['iv_delete_video']) && $_POST['iv_delete_video'] == '1' && !empty($video_field_key)) {
        $video_id = get_field($video_field_key, $post_id, false);
        if ($video_id) {
            wp_delete_attachment($video_id, true);
        }
        update_field($video_field_key, '', $post_id);
    }
}

// Temporarily disable required validation when deleting
add_filter('acf/validate_value', 'iv_bypass_required_when_deleting', 5, 4);

function iv_bypass_required_when_deleting($valid, $value, $field, $input) {
    if (isset($_POST['iv_delete_video']) && $_POST['iv_delete_video'] == '1') {
        if ($field['key'] === get_option('iv_video_field_key', '')) {
            return true; // Bypass required check
        }
    }
    if (isset($_POST['iv_delete_images']) && $_POST['iv_delete_images'] == '1') {
        if ($field['key'] === get_option('iv_image_field_key', '')) {
            return true; // Bypass required check
        }
    }
    return $valid;
}