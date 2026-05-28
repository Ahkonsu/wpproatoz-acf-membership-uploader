<?php
/**
 * Frontend Functionality for WPProAtoZ ACF Membership Uploader
 */
if (!defined('ABSPATH')) exit;

// ====================== FRONTEND SCRIPTS & STYLES ======================
function iv_scripts() {
    wp_enqueue_style('iv-style', IV_PLUGIN_URL . 'assets/css/iv-style.css', [], IV_PLUGIN_VERSION);

    if (is_page() && (has_shortcode(get_post()->post_content ?? '', 'pe_tracker_uploader') || has_shortcode(get_post()->post_content ?? '', 'image_video_submission'))) {
        $recaptcha_type = get_option('iv_recaptcha_type', 'none');
        $recaptcha_site_key = get_option('iv_recaptcha_site_key', '');

        if ($recaptcha_type !== 'none' && !empty($recaptcha_site_key)) {
            $url = ($recaptcha_type === 'v3')
                ? 'https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_site_key)
                : 'https://www.google.com/recaptcha/api.js';
            wp_enqueue_script('google-recaptcha', $url, [], null, true);
        }

        wp_enqueue_script('iv-custom-js', IV_PLUGIN_URL . 'assets/js/iv-custom.js', ['jquery'], IV_PLUGIN_VERSION, true);
        wp_localize_script('iv-custom-js', 'ivSettings', [
            'maxImageSize' => iv_get_max_image_size(),
            'maxVideoSize' => iv_get_max_video_size(),
            'imageMaxMB' => get_option('iv_max_image_size_mb', 1),
            'videoMaxMB' => get_option('iv_max_video_size_mb', 30),
        ]);

        // NEW: Load WordPress Media scripts for frontend library access
        wp_enqueue_media();
    }
}
add_action('wp_enqueue_scripts', 'iv_scripts');

// ====================== ACF FORM HEAD ======================
function iv_acf_form_head() {
    if (function_exists('acf_form_head') && is_page() && has_shortcode(get_post()->post_content ?? '', 'pe_tracker_uploader')) {
        acf_form_head();
    }
}
add_action('wp_head', 'iv_acf_form_head');

// Disable rich editing on frontend
add_filter('user_can_richedit', function($default) {
    return is_admin() ? $default : false;
});

// ====================== ENABLE & RESTRICT MEDIA LIBRARY ON FRONTEND ======================
// ====================== RESTRICT MEDIA LIBRARY TO CURRENT USER ======================
add_filter('acf/fields/file/query', 'iv_restrict_media_to_current_user', 5, 3);
add_filter('acf/fields/gallery/query', 'iv_restrict_media_to_current_user', 5, 3);
add_filter('ajax_query_attachments_args', 'iv_restrict_media_to_current_user', 5, 1); // Critical for frontend WP uploader

function iv_restrict_media_to_current_user($args, $field = null, $post_id = null) {
    if (!is_user_logged_in()) {
        return $args;
    }

    $current_user_id = get_current_user_id();
    $video_key = get_option('iv_video_field_key', '');

    // Target our video field + general frontend media calls
    $is_our_video_field = $field && ($field['key'] === $video_key || strpos($field['key'] ?? '', 'video') !== false);

    if ($is_our_video_field || (defined('DOING_AJAX') && DOING_AJAX)) {
        $args['author'] = $current_user_id;
        $args['post__not_in'] = [];
        $args['posts_per_page'] = 50;

        // For video field - only show video files
        if ($is_our_video_field) {
            $args['post_mime_type'] = 'video';
        }
    }

    return $args;
}

// ====================== MAIN SHORTCODE ======================
// ====================== MAIN SHORTCODE ======================
add_shortcode('pe_tracker_uploader', 'iv_display_submission_form');

function iv_display_submission_form() {
    if (!class_exists('ACF')) {
        return '<p style="color:red;">Error: Advanced Custom Fields Pro is required.</p>';
    }
    if (!is_user_logged_in()) {
        return '<p>Please <a href="' . wp_login_url() . '">log in</a> to access your PE Tracker.</p>';
    }

    $cpt_slug = get_option('iv_cpt_slug', 'pe_tracker_entry');
    $upload_mode = get_option('iv_upload_mode', 'both');
    $image_key = get_option('iv_image_field_key', '');
    $video_key = get_option('iv_video_field_key', '');

    // NEW: Pet Detail Field Keys
    $pet_description_key = get_option('iv_pet_description_key', '');
    $emergency_email_key = get_option('iv_emergency_email_key', '');
    $emergency_phone_key = get_option('iv_emergency_phone_key', '');

    // Build list of fields to show in form - ORDER MATTERS
    $fields = [];

    // 1. Pet Details (appear first)
    if (!empty($pet_description_key)) $fields[] = $pet_description_key;
    if (!empty($emergency_email_key)) $fields[] = $emergency_email_key;
    if (!empty($emergency_phone_key)) $fields[] = $emergency_phone_key;

    // 2. Media Fields
    if (in_array($upload_mode, ['images', 'both']) && !empty($image_key)) $fields[] = $image_key;
    if (in_array($upload_mode, ['videos', 'both']) && !empty($video_key)) $fields[] = $video_key;

    if (empty($fields)) {
        return '<p><strong>Configuration Error:</strong> No ACF fields configured.</p>';
    }

    $entry_id = pe_get_or_create_user_entry();

    $recaptcha_html = ''; // Add your recaptcha output here if needed

    ob_start();

    echo '<div class="pe-tracker-wrapper">';

    // === TOP NAVIGATION LINK ===
    echo '<div style="margin-bottom:25px; padding:15px; background:#f0f7ff; border:1px solid #b3d4ff; border-radius:8px; text-align:center;">';
    echo '<p style="margin:0; font-weight:600; font-size:1.1em;">
            📍 <a href="#pe-tracker-upload-section" style="text-decoration:underline; color:#007cba;">Manage your media files now →</a>
          </p>';
    echo '<small style="color:#555;">Upload images and/or video • Replace existing media</small>';
    echo '</div>';

    // === CURRENT MEDIA & PET INFO DISPLAY ===
    echo '<div class="current-media" style="margin: 25px 0; padding: 25px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px;">';
    echo '<h3 style="margin-top:0;">Current Media & Pet Information</h3>';

    $has_media = false;

    // Current Images
    if (!empty($image_key)) {
        $current_images = get_field($image_key, $entry_id);
        if (!empty($current_images)) {
            $has_media = true;
            echo '<p><strong>Current Images:</strong></p>';
            echo '<div style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:20px;">';
            foreach ((array)$current_images as $img) {
                echo '<img src="' . esc_url($img['url']) . '" style="max-width:160px; height:auto; border-radius:6px; border:1px solid #eee;" alt="">';
            }
            echo '</div>';
        }
    }

    // Current Video
    if (!empty($video_key)) {
        $current_video = get_field($video_key, $entry_id);
        if (!empty($current_video)) {
            $has_media = true;
            echo '<p style="margin-top:20px;"><strong>Current Video:</strong></p>';
            echo '<video width="520" height="auto" controls style="max-width:100%; background:#000; border-radius:8px;">';
            echo '<source src="' . esc_url($current_video['url']) . '" type="video/mp4">';
            echo 'Your browser does not support the video tag.';
            echo '</video><br><br>';
        }
    }

    if (!$has_media) {
        echo '<p style="color:#666; font-style:italic;">You haven\'t uploaded any media yet.</p>';
    }

    // Pet Details in Current Info
    echo '<div style="margin-top:30px; padding-top:20px; border-top:1px solid #ddd;">';
    echo '<h4>Pet Details</h4>';
    // (existing pet details display code - keep as is)
    if (!empty($pet_description_key)) {
        $description = get_field($pet_description_key, $entry_id);
        if (!empty($description)) {
            echo '<p><strong>Your Pet Description / Story:</strong><br>';
            echo '<span style="background:#fff; padding:12px; border-radius:6px; display:block; margin-top:8px; white-space:pre-wrap;">' 
                 . esc_html($description) . '</span></p>';
        }
    }
    if (!empty($emergency_email_key)) {
        $email = get_field($emergency_email_key, $entry_id);
        if (!empty($email)) echo '<p><strong>Emergency Email:</strong> ' . esc_html($email) . '</p>';
    }
    if (!empty($emergency_phone_key)) {
        $phone = get_field($emergency_phone_key, $entry_id);
        if (!empty($phone)) echo '<p><strong>Emergency Phone:</strong> ' . esc_html($phone) . '</p>';
    }
    echo '</div>';

    echo '</div>'; // end current-media

    // === UPLOAD / MANAGE MEDIA SECTION ===
    echo '<div id="pe-tracker-upload-section" style="margin-top:40px;">';
    echo '<h3>Update Your Pet Information & Media</h3>';
    echo '<p style="color:#555; margin-bottom:25px;">Fill in your pet details and upload media below.</p>';

    acf_form([
        'post_id'           => $entry_id,
        'post_title'        => false,
        'post_content'      => false,
        'fields'            => $fields,        // ← Now includes new fields
        'submit_value'      => 'Save My PE Tracker Updates',
        'return'            => add_query_arg('updated', 'true', get_permalink()),
        'form_attributes'   => ['enctype' => 'multipart/form-data'],
        'html_before_fields'=> '<input type="text" name="iv_honeypot" style="display:none;" value="">',
        'html_after_fields' => $recaptcha_html,
        'uploader'          => 'wp'
    ]);

    echo '</div>';
    echo '</div>'; // end wrapper

    return ob_get_clean();
}