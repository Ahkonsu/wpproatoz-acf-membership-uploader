<?php
/**
 * Frontend Functionality for WPProAtoZ ACF Membership Uploader
 */

if (!defined('ABSPATH')) exit;

// ====================== FRONTEND SCRIPTS & STYLES ======================
function iv_scripts() {
    wp_enqueue_style('iv-style', IV_PLUGIN_URL . 'assets/css/iv-style.css', [], IV_PLUGIN_VERSION);

    // Only load on pages with our shortcode
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
            'imageMaxMB'   => get_option('iv_max_image_size_mb', 1),
            'videoMaxMB'   => get_option('iv_max_video_size_mb', 30),
        ]);
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

// ====================== MAIN SHORTCODE ======================
// *** display submission form***

add_shortcode('pe_tracker_uploader', 'iv_display_submission_form');

function iv_display_submission_form() {
    if (!class_exists('ACF')) {
        return '<p style="color:red;">Error: Advanced Custom Fields Pro is required.</p>';
    }

    if (!is_user_logged_in()) {
        return '<p>Please <a href="' . wp_login_url() . '">log in</a> to access your PE Tracker.</p>';
    }

    $cpt_slug     = get_option('iv_cpt_slug', 'pe_tracker_entry');
    $upload_mode  = get_option('iv_upload_mode', 'both');
    $image_key    = get_option('iv_image_field_key', '');
    $video_key    = get_option('iv_video_field_key', '');

    $fields = [];
    if (in_array($upload_mode, ['images', 'both']) && !empty($image_key)) $fields[] = $image_key;
    if (in_array($upload_mode, ['videos', 'both']) && !empty($video_key)) $fields[] = $video_key;

    if (empty($fields)) {
        return '<p><strong>Configuration Error:</strong> No ACF fields configured.</p>';
    }

    $entry_id = pe_get_or_create_user_entry();

    ob_start();

    if (isset($_GET['updated']) && $_GET['updated'] === 'true') {
        echo '<p class="iv-success-message">✅ Your PE Tracker has been updated successfully!</p>';
    }

    // === CURRENT MEDIA + DELETE OPTIONS ===
    echo '<div class="current-media" style="margin-bottom: 25px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px;">';

    // Current Images
    if (!empty($image_key)) {
        $current_images = get_field($image_key, $entry_id);
        if (!empty($current_images)) {
            echo '<p><strong>Current Images:</strong></p>';
            echo '<div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px;">';
            foreach ((array)$current_images as $img) {
                echo '<img src="' . esc_url($img['url']) . '" style="max-width:120px; height:auto;" alt="">';
            }
            echo '</div>';
            echo '<label><input type="checkbox" name="iv_delete_images" value="1"> 🗑️ Delete all current images</label><br><br>';
        }
    }

    // Current Video + Player
    if (!empty($video_key)) {
        $current_video = get_field($video_key, $entry_id);
        if (!empty($current_video)) {
            echo '<p><strong>Current Video:</strong></p>';
            echo '<video width="520" height="auto" controls style="max-width:100%; background:#000;">';
            echo '<source src="' . esc_url($current_video['url']) . '" type="video/mp4">';
            echo 'Your browser does not support the video tag.';
            echo '</video><br><br>';
            echo '<label><input type="checkbox" name="iv_delete_video" value="1"> 🗑️ Delete current video</label><br><br>';
            echo '<input type="hidden" name="iv_delete_video" value="0">'; // Safety fallback
        }
    }

    echo '</div>';

    // ACF Form
    $recaptcha_type = get_option('iv_recaptcha_type', 'none');
    $recaptcha_html = ($recaptcha_type === 'none') 
        ? '<input type="text" name="iv_honeypot" style="display:none;" value="">' 
        : '';

    acf_form([
        'post_id'           => $entry_id,
        'post_title'        => false,
        'post_content'      => false,
        'fields'            => $fields,
        'submit_value'      => 'Save My PE Tracker Updates',
        'return'            => add_query_arg('updated', 'true', get_permalink()),
        'form_attributes'   => ['enctype' => 'multipart/form-data'],
        'html_before_fields'=> '<input type="text" name="iv_honeypot" style="display:none;" value="">',
        'html_after_fields' => $recaptcha_html,
        'uploader'          => 'basic'
    ]);

    return ob_get_clean();
}

//end front end submission form