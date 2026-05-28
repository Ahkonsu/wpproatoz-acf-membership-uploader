<?php
/**
 * Validation & Deletion Handler for PE Tracker
 */

if (!defined('ABSPATH')) exit;

// ====================== MAIN VALIDATION ======================
add_filter('acf/pre_save_post', 'iv_validate_submission', 10, 2);

function iv_validate_submission($post_id, $acf_values) {
    if (get_post_type($post_id) !== get_option('iv_cpt_slug', 'pe_tracker_entry')) {
        return $post_id;
    }

    $image_key = get_option('iv_image_field_key', '');
    $video_key = get_option('iv_video_field_key', '');

    $has_images = !empty($_FILES[$image_key]['name'][0] ?? '') || !empty(get_field($image_key, $post_id));
    $has_video  = !empty($_FILES[$video_key]['name'] ?? '') || !empty(get_field($video_key, $post_id));

    // Check for delete requests
    if (isset($_POST['iv_delete_images']) && $_POST['iv_delete_images'] == '1') {
        $has_images = false;
    }
    if (isset($_POST['iv_delete_video']) && $_POST['iv_delete_video'] == '1') {
        $has_video = false;
    }

    // Require AT LEAST one media type
    if (!$has_images && !$has_video) {
        wp_die('<p style="color:red;">Error: You must upload either images or a video (or both) for your PE Tracker.</p>
                <p><a href="javascript:history.back()">? Go Back</a></p>');
    }

    return $post_id;
}

// ====================== HANDLE DELETIONS ======================
add_action('acf/save_post', 'iv_handle_deletions_after_save', 20);

function iv_handle_deletions_after_save($post_id) {
    if (get_post_type($post_id) !== get_option('iv_cpt_slug', 'pe_tracker_entry')) {
        return;
    }

    $image_key = get_option('iv_image_field_key', '');
    $video_key = get_option('iv_video_field_key', '');

    // Delete Images
    if (isset($_POST['iv_delete_images']) && $_POST['iv_delete_images'] == '1' && !empty($image_key)) {
        update_field($image_key, [], $post_id);
    }

    // Delete Video
    if (isset($_POST['iv_delete_video']) && $_POST['iv_delete_video'] == '1' && !empty($video_key)) {
        $video_id = get_field($video_key, $post_id, false);
        if ($video_id) {
            wp_delete_attachment($video_id, true);
        }
        update_field($video_key, '', $post_id);
    }
}

// Bypass ACF Required during deletion
add_filter('acf/validate_value', 'iv_bypass_required_when_deleting', 5, 4);

function iv_bypass_required_when_deleting($valid, $value, $field, $input) {
    $image_key = get_option('iv_image_field_key', '');
    $video_key = get_option('iv_video_field_key', '');

    if (isset($_POST['iv_delete_images']) && $_POST['iv_delete_images'] == '1') {
        if ($field['key'] === $image_key) {
            return true;
        }
    }
    if (isset($_POST['iv_delete_video']) && $_POST['iv_delete_video'] == '1') {
        if ($field['key'] === $video_key) {
            return true;
        }
    }
    return $valid;
}