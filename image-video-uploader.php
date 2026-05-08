<?php
/**
 * Plugin Name: ACF Image & Video Frontend Submission Form
 * Description: This plugin contains extra custom functions to allow front end submissions of limited items using a custom ACF Pro post type.
 * Author: WPProAtoZ
 * Author URI: https://wpproatoz.com
 * Version: 1.3.9
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: wpproatoz-acf-image-video-submission
 * Update URI: https://github.com/Ahkonsu/wpproatoz-acf-image-video-submission/releases
 * GitHub Plugin URI: https://github.com/Ahkonsu/wpproatoz-acf-image-video-submission/releases
 * GitHub Branch: main
 * Requires Plugins: advanced-custom-fields-pro
 */

require 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/Ahkonsu/wpproatoz-acf-image-video-submission/',
    __FILE__,
    'wpproatoz-acf-image-video-submission'
);
$myUpdateChecker->setBranch('main');

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'iv_add_settings_link');
function iv_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=iv-settings') . '">' . __('Settings', 'wpproatoz-acf-image-video-submission') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

function iv_scripts() {
    // Enqueue local stylesheet
    wp_enqueue_style(
        'iv-style',
        plugin_dir_url(__FILE__) . 'css/iv-style.css',
        array(),
        '1.3.9'
    );

    if (is_page() && has_shortcode(get_post()->post_content, 'image_video_submission')) {
        $recaptcha_type = get_option('iv_recaptcha_type', 'none');
        $recaptcha_site_key = get_option('iv_recaptcha_site_key', '');

        if ($recaptcha_type !== 'none' && !empty($recaptcha_site_key)) {
            $script_url = $recaptcha_type === 'v3'
                ? 'https://www.google.com/recaptcha/api.js?render=' . esc_attr($recaptcha_site_key)
                : 'https://www.google.com/recaptcha/api.js';
            wp_enqueue_script('google-recaptcha', $script_url, array(), null, true);
        }

        $upload_mode      = get_option('iv_upload_mode', 'both');
        $image_field_key  = get_option('iv_image_field_key', '');
        $video_field_key  = get_option('iv_video_field_key', '');

        wp_enqueue_script('iv-custom-js', plugin_dir_url(__FILE__) . 'iv-custom.js', array('jquery'), '1.3.9', true);

        wp_localize_script('iv-custom-js', 'ivSettings', array(
            'maxImageSize'     => get_option('iv_max_image_size_mb', 1) * 1024 * 1024,
            'maxVideoSize'     => get_option('iv_max_video_size_mb', 30) * 1024 * 1024,
            'imageMaxMB'       => get_option('iv_max_image_size_mb', 1),
            'videoMaxMB'       => get_option('iv_max_video_size_mb', 30),
            'imageTypes'       => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'],
            'videoTypes'       => ['video/mp4', 'video/quicktime', 'video/x-m4v'],
            'imageExtensions'  => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'videoExtensions'  => ['mp4', 'mov', 'm4v'],
            'imageFieldKey'    => $image_field_key,
            'videoFieldKey'    => $video_field_key,
            'uploadMode'       => $upload_mode,
            'errorImageType'   => __('Only .jpg, .jpeg, .png, .webp, .gif files are allowed for images.', 'wpproatoz-acf-image-video-submission'),
            'errorVideoType'   => __('Only .mp4, .mov, .m4v files are allowed for videos.', 'wpproatoz-acf-image-video-submission'),
            'labelTitle'       => sanitize_text_field(get_option('iv_field_label_title', 'Name')),
            'labelContent'     => sanitize_text_field(get_option('iv_field_label_content', 'Description'))
        ));
    }
}
add_action('wp_enqueue_scripts', 'iv_scripts');

require_once dirname(__FILE__) . '/class-tgm-plugin-activation.php';
add_action('tgmpa_register', 'iv_register_required_plugins');
function iv_register_required_plugins() {
    $plugins = array(
        array(
            'name'     => 'Advanced Custom Fields Pro (ACF Pro)',
            'slug'     => 'advanced-custom-fields-pro',
            'source'   => 'https://www.advancedcustomfields.com',
            'required' => true,
            'external_url' => 'https://www.advancedcustomfields.com/pro/',
        ),
    );
    $config = array(
        'id'           => 'iv-extra',
        'default_path' => '',
        'menu'         => 'tgmpa-install-plugins',
        'parent_slug'  => 'plugins.php',
        'capability'   => 'manage_options',
        'has_notices'  => true,
        'dismissable'  => true,
        'is_automatic' => false,
    );
    tgmpa($plugins, $config);
}

add_action('wp_head', 'iv_acf_form_head');
function iv_acf_form_head() {
    if (function_exists('acf_form_head') && is_page() && has_shortcode(get_post()->post_content, 'image_video_submission')) {
        acf_form_head();
    }
}

// Disable rich editing on frontend
add_filter('user_can_richedit', 'iv_disable_richedit_frontend');
function iv_disable_richedit_frontend($default) {
    if (!is_admin()) {
        return false;
    }
    return $default;
}

add_shortcode('image_video_submission', 'iv_display_submission_form');
function iv_display_submission_form() {
    if (!class_exists('ACF')) {
        return '<p>Error: Advanced Custom Fields Pro is required to use this form.</p>';
    }

    $cpt_slug         = get_option('iv_cpt_slug', 'image-video-submission');
    $upload_mode      = get_option('iv_upload_mode', 'both');
    $image_field_key  = get_option('iv_image_field_key', '');
    $video_field_key  = get_option('iv_video_field_key', '');

    $fields = array();
    if ($upload_mode === 'images' || $upload_mode === 'both') {
        if (!empty($image_field_key)) $fields[] = $image_field_key;
    }
    if ($upload_mode === 'videos' || $upload_mode === 'both') {
        if (!empty($video_field_key)) $fields[] = $video_field_key;
    }

    if (empty($fields)) {
    return '<p><strong>DEBUG: No fields detected.</strong> Check: Keys match exactly? Field group assigned to CPT? Upload mode correct?</p>';
}

    $form_access = get_option('iv_form_access', 'public');
    if ($form_access === 'private' && !is_user_logged_in()) {
        return '<p>Please log in to submit a submission.</p>';
    }

    // Custom labels and instructions
    $label_image = get_option('iv_field_label_image', 'Single Art Gallery Image');
    $label_video = get_option('iv_field_label_video', 'Video Upload Art Gallery');
    $max_image_mb = get_option('iv_max_image_size_mb', 1);
    $max_video_mb = get_option('iv_max_video_size_mb', 30);

    if (($upload_mode === 'images' || $upload_mode === 'both') && !empty($image_field_key)) {
        add_filter('acf/prepare_field/key=' . $image_field_key, function($field) use ($label_image, $max_image_mb) {
            $field['label'] = $label_image;
            $field['instructions'] = sprintf(__('Image files limited to .png, .webp, .jpg, .gif files only and files limited to %dMB max.', 'wpproatoz-acf-image-video-submission'), $max_image_mb);
            return $field;
        });
    }

    if (($upload_mode === 'videos' || $upload_mode === 'both') && !empty($video_field_key)) {
        add_filter('acf/prepare_field/key=' . $video_field_key, function($field) use ($label_video, $max_video_mb) {
            $field['label'] = $label_video;
            $field['instructions'] = sprintf(__('Video files limited to .mov, .m4v, .mp4 files only and files limited to %dMB max.', 'wpproatoz-acf-image-video-submission'), $max_video_mb);
            return $field;
        });
    }

    ob_start();

    if (isset($_GET['submitted']) && $_GET['submitted'] === 'true') {
        echo '<p class="iv-success-message">Thank you! Your submission has been received as a draft and is awaiting review.</p>';
    }

    $recaptcha_type     = get_option('iv_recaptcha_type', 'none');
    $recaptcha_site_key = get_option('iv_recaptcha_site_key', '');
    $recaptcha_enabled  = $recaptcha_type !== 'none' && !empty($recaptcha_site_key) && !empty(get_option('iv_recaptcha_secret_key', ''));

    $recaptcha_html = '';
    if ($recaptcha_enabled) {
        if ($recaptcha_type === 'v2') {
            $recaptcha_html = '<div style="margin: 30px 0; text-align: center;"><div class="g-recaptcha" data-sitekey="' . esc_attr($recaptcha_site_key) . '"></div></div>';
        } elseif ($recaptcha_type === 'v3') {
            $recaptcha_html = '
<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
<script>
document.addEventListener("DOMContentLoaded", function() {
    grecaptcha.ready(function() {
        grecaptcha.execute("' . esc_attr($recaptcha_site_key) . '", {action: "submit_submission"}).then(function(token) {
            document.getElementById("g-recaptcha-response").value = token;
        });
    });
});
</script>';
        }
    } else {
        $recaptcha_html = '<input type="text" name="iv_honeypot" style="display:none;" value="">';
    }

    acf_form(array(
        'post_id'     => 'new_post',
        'post_title'  => true,
        'post_content'=> true,
        'form'        => true,
        'new_post'    => array(
            'post_type'   => $cpt_slug,
            'post_status' => 'draft',
            'post_author' => is_user_logged_in() ? get_current_user_id() : 1
        ),
        'fields'      => $fields,
        'submit_value'=> __('Submit Artwork', 'wpproatoz-acf-image-video-submission'),
        'return'      => add_query_arg('submitted', 'true', get_permalink()),
        'form_attributes' => array('enctype' => 'multipart/form-data'),
        'html_before_fields' => $honeypot_html,  // Honeypot at top (invisible)
        'html_after_fields' => $recaptcha_html,  // reCAPTCHA v2 checkbox now at bottom
        'uploader'    => 'basic'
    ));

    return ob_get_clean();
}

add_filter('acf/pre_save_post', 'iv_validate_submission', 10, 2);
function iv_validate_submission($post_id, $values) {
    if ($post_id !== 'new_post') {
        return $post_id;
    }

    // reCAPTCHA validation
    $recaptcha_type        = get_option('iv_recaptcha_type', 'none');
    $recaptcha_site_key    = get_option('iv_recaptcha_site_key', '');
    $recaptcha_secret_key  = get_option('iv_recaptcha_secret_key', '');
    $recaptcha_v3_threshold = floatval(get_option('iv_recaptcha_v3_threshold', 0.5));
    $recaptcha_enabled     = $recaptcha_type !== 'none' && !empty($recaptcha_site_key) && !empty($recaptcha_secret_key);

    if (!$recaptcha_enabled && isset($_POST['iv_honeypot']) && !empty($_POST['iv_honeypot'])) {
        wp_die('Spam detected. Please try again.', 'Submission Error', array('back_link' => true));
    }

    if ($recaptcha_enabled && isset($_POST['g-recaptcha-response'])) {
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret'   => $recaptcha_secret_key,
                'response' => sanitize_text_field($_POST['g-recaptcha-response']),
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        ));

        if (is_wp_error($response)) {
            wp_die('reCAPTCHA verification failed. Please try again.', 'Submission Error', array('back_link' => true));
        }

        $result = json_decode(wp_remote_retrieve_body($response), true);

        if (!$result['success']) {
            wp_die('reCAPTCHA verification failed. Please try again.', 'Submission Error', array('back_link' => true));
        }

        if ($recaptcha_type === 'v3' && isset($result['score']) && $result['score'] <= $recaptcha_v3_threshold) {
            wp_die('reCAPTCHA score too low. Please try again.', 'Submission Error', array('back_link' => true));
        }
    } elseif ($recaptcha_enabled) {
        wp_die('Please complete the reCAPTCHA.', 'Submission Error', array('back_link' => true));
    }

    // Server-side file validation
    $upload_mode     = get_option('iv_upload_mode', 'both');
    $image_field_key = get_option('iv_image_field_key', '');
    $video_field_key = get_option('iv_video_field_key', '');
    $max_image_bytes = get_option('iv_max_image_size_mb', 1) * 1024 * 1024;
    $max_video_bytes = get_option('iv_max_video_size_mb', 30) * 1024 * 1024;

    $allowed_image_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    $allowed_video_types = ['video/mp4', 'video/quicktime', 'video/x-m4v'];

    // Validate image
    if (($upload_mode === 'images' || $upload_mode === 'both') && !empty($image_field_key) && isset($_FILES['acf']['error'][$image_field_key]) && $_FILES['acf']['error'][$image_field_key] === UPLOAD_ERR_OK) {
        $size = $_FILES['acf']['size'][$image_field_key];
        $type = $_FILES['acf']['type'][$image_field_key];

        if ($size > $max_image_bytes) {
            wp_die(sprintf(__('Image file too large. Maximum size is %d MB.', 'wpproatoz-acf-image-video-submission'), get_option('iv_max_image_size_mb', 1)), 'Submission Error', array('back_link' => true));
        }
        if (!in_array($type, $allowed_image_types)) {
            wp_die(__('Invalid image file type. Only .jpg, .jpeg, .png, .webp, .gif allowed.', 'wpproatoz-acf-image-video-submission'), 'Submission Error', array('back_link' => true));
        }
    }

    // Validate video
    if (($upload_mode === 'videos' || $upload_mode === 'both') && !empty($video_field_key) && isset($_FILES['acf']['error'][$video_field_key]) && $_FILES['acf']['error'][$video_field_key] === UPLOAD_ERR_OK) {
        $size = $_FILES['acf']['size'][$video_field_key];
        $type = $_FILES['acf']['type'][$video_field_key];

        if ($size > $max_video_bytes) {
            wp_die(sprintf(__('Video file too large. Maximum size is %d MB.', 'wpproatoz-acf-image-video-submission'), get_option('iv_max_video_size_mb', 30)), 'Submission Error', array('back_link' => true));
        }
        if (!in_array($type, $allowed_video_types)) {
            wp_die(__('Invalid video file type. Only .mp4, .mov, .m4v allowed.', 'wpproatoz-acf-image-video-submission'), 'Submission Error', array('back_link' => true));
        }
    }

    return $post_id;
}

add_action('wp_head', 'iv_add_custom_styles');
function iv_add_custom_styles() {
    echo '<style>
    .iv-success-message { color: #008000; font-weight: bold; margin-bottom: 20px; text-align: center; }
    .acf-basic-uploader {
        text-align: center;
        padding: 40px 30px;
        border: 3px dashed #ccc;
        border-radius: 10px;
        background: #f9f9f9;
        margin-bottom: 30px;
    }
    .acf-basic-uploader.has-value { border-style: solid; }
    .acf-field .description {
        display: block !important;
        visibility: visible !important;
        color: #e67e22 !important;
        font-style: italic !important;
        font-size: 1.1em !important;
        text-align: center !important;
        margin: 15px 0 25px 0 !important;
        line-height: 1.5;
        padding: 0 20px;
    }
    .acf-field-image .acf-actions a[data-name="edit"],
    .acf-field-file .acf-actions a[data-name="edit"],
    .acf-field-image .file-info,
    .acf-field-file .file-info {
        display: none !important;
    }
    </style>';
}

add_action('admin_menu', 'iv_add_admin_menu');
function iv_add_admin_menu() {
    // Top-level menu
    add_menu_page(
        'Image & Video Submission Settings',
        'Image & Video Submission',
        'manage_options',
        'iv-settings',
        'iv_settings_page',
        'dashicons-format-image',
        30
    );

    // Settings submenu
    add_submenu_page(
        'iv-settings',
        'Settings',
        'Settings',
        'manage_options',
        'iv-settings',
        'iv_settings_page'
    );

    // Manage Submissions submenu (now safely under our own menu)
    add_submenu_page(
        'iv-settings',
        'Manage Submissions',
        'Manage Submissions',
        'manage_options',
        'iv-manage-submissions',
        'iv_manage_submissions_page'
    );
}

function iv_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (isset($_POST['iv_save_settings']) && check_admin_referer('iv_save_form_settings')) {
        $form_access = isset($_POST['iv_form_access']) && $_POST['iv_form_access'] === 'public' ? 'public' : 'private';
        $recaptcha_type = in_array($_POST['iv_recaptcha_type'] ?? '', ['v2', 'v3', 'none']) ? $_POST['iv_recaptcha_type'] : 'none';
        $recaptcha_v3_threshold = floatval($_POST['iv_recaptcha_v3_threshold'] ?? 0.5);
        $recaptcha_v3_threshold = max(0.0, min(1.0, $recaptcha_v3_threshold));
        $cpt_slug = sanitize_text_field($_POST['iv_cpt_slug'] ?? 'image-video-submission');
        $upload_mode = in_array($_POST['iv_upload_mode'] ?? '', ['images', 'videos', 'both']) ? $_POST['iv_upload_mode'] : 'both';
        $image_field_key = sanitize_text_field($_POST['iv_image_field_key'] ?? '');
        $video_field_key = sanitize_text_field($_POST['iv_video_field_key'] ?? '');

        $max_image_mb = max(1, min(500, intval($_POST['iv_max_image_size_mb'] ?? 1)));
        $max_video_mb = max(1, min(1000, intval($_POST['iv_max_video_size_mb'] ?? 30)));

        update_option('iv_form_access', $form_access);
        update_option('iv_recaptcha_type', $recaptcha_type);
        update_option('iv_recaptcha_v3_threshold', $recaptcha_v3_threshold);
        update_option('iv_recaptcha_site_key', sanitize_text_field($_POST['iv_recaptcha_site_key'] ?? ''));
        update_option('iv_recaptcha_secret_key', sanitize_text_field($_POST['iv_recaptcha_secret_key'] ?? ''));
        update_option('iv_field_label_title', sanitize_text_field($_POST['iv_field_label_title'] ?? 'Name'));
        update_option('iv_field_label_content', sanitize_text_field($_POST['iv_field_label_content'] ?? 'Description'));
        update_option('iv_field_label_image', sanitize_text_field($_POST['iv_field_label_image'] ?? 'Single Art Gallery Image'));
        update_option('iv_field_label_video', sanitize_text_field($_POST['iv_field_label_video'] ?? 'Video Upload Art Gallery'));
        update_option('iv_cpt_slug', $cpt_slug);
        update_option('iv_upload_mode', $upload_mode);
        update_option('iv_image_field_key', $image_field_key);
        update_option('iv_video_field_key', $video_field_key);
        update_option('iv_max_image_size_mb', $max_image_mb);
        update_option('iv_max_video_size_mb', $max_video_mb);

        echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>';
    }

    $form_access            = get_option('iv_form_access', 'public');
    $recaptcha_type         = get_option('iv_recaptcha_type', 'none');
    $recaptcha_v3_threshold = get_option('iv_recaptcha_v3_threshold', 0.5);
    $recaptcha_site_key     = get_option('iv_recaptcha_site_key', '');
    $recaptcha_secret_key    = get_option('iv_recaptcha_secret_key', '');
    $field_label_title      = get_option('iv_field_label_title', 'Name');
    $field_label_content    = get_option('iv_field_label_content', 'Description');
    $field_label_image      = get_option('iv_field_label_image', 'Single Art Gallery Image');
    $field_label_video      = get_option('iv_field_label_video', 'Video Upload Art Gallery');
    $cpt_slug               = get_option('iv_cpt_slug', 'image-video-submission');
    $upload_mode            = get_option('iv_upload_mode', 'both');
    $image_field_key        = get_option('iv_image_field_key', '');
    $video_field_key        = get_option('iv_video_field_key', '');
    $max_image_mb           = get_option('iv_max_image_size_mb', 1);
    $max_video_mb           = get_option('iv_max_video_size_mb', 30);

    $doc_content = file_get_contents(plugin_dir_path(__FILE__) . 'documentation.txt');
    if ($doc_content === false) {
        $doc_content = '<p>Documentation file not found.</p>';
    }
    ?>
    <div class="wrap">
        <h1>Image & Video Submission Settings</h1>
<div class="notice notice-warning">
    <p><strong>Critical: Use ACF Field Keys (not names or labels)</strong></p>
    <ol>
        <li>Go to <strong>Custom Fields > Field Groups</strong> in admin.</li>
        <li>Edit the field group used for your submissions.</li>
        <li>Top right: Click <strong>Screen Options</strong> → Check <strong>Field Keys</strong>.</li>
        <li>The key appears next to each field (e.g., <code>field_682e59ec3b45a</code>).</li>
        <li>Copy and paste the <strong>exact key</strong> (starts with "field_") into the boxes below.</li>
        <li>Field must be <strong>Image</strong> type for images, <strong>File</strong> type for videos.</li>
        <li>Field group must be assigned to your CPT (or test with "All post types").</li>
    </ol>
    <p>If keys are wrong/missing, the form will be blank with no error shown.</p>
</div>

        <h2 class="nav-tab-wrapper">
            <a href="#iv-settings-tab" class="nav-tab nav-tab-active">Settings</a>
            <a href="#iv-docs-tab" class="nav-tab">Documentation</a>
        </h2>

        <div id="iv-settings-tab-content" class="iv-tab-content active">
            <form method="post" action="">
                <?php wp_nonce_field('iv_save_form_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="iv_cpt_slug">Custom Post Type Slug</label></th>
                        <td>
                            <input type="text" name="iv_cpt_slug" id="iv_cpt_slug" value="<?php echo esc_attr($cpt_slug); ?>" class="regular-text">
                            <p class="description"><strong>Required:</strong> Exact slug of your custom post type (e.g., "artwork").</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_upload_mode">Upload Mode</label></th>
                        <td>
                            <select name="iv_upload_mode" id="iv_upload_mode">
                                <option value="both" <?php selected($upload_mode, 'both'); ?>>Both Images and Videos</option>
                                <option value="images" <?php selected($upload_mode, 'images'); ?>>Images Only</option>
                                <option value="videos" <?php selected($upload_mode, 'videos'); ?>>Videos Only</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_image_field_key">ACF Image Field Key</label></th>
                        <td>
                            <input type="text" name="iv_image_field_key" id="iv_image_field_key" value="<?php echo esc_attr($image_field_key); ?>" class="regular-text">
                            <p class="description"><strong>Required for images:</strong> Exact ACF field key (e.g., field_abc123). NOT label/name. Enable in Screen Options.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_video_field_key">ACF Video Field Key</label></th>
                        <td>
                            <input type="text" name="iv_video_field_key" id="iv_video_field_key" value="<?php echo esc_attr($video_field_key); ?>" class="regular-text">
                            <p class="description"><strong>Required for videos:</strong> Exact ACF field key (e.g., field_abc123). NOT label/name. Enable in Screen Options.</p>
                        </td>
                    </tr>

                    <tr id="iv_image_size_row">
                        <th scope="row"><label for="iv_max_image_size_mb">Max Image Size (MB)</label></th>
                        <td>
                            <input type="number" name="iv_max_image_size_mb" id="iv_max_image_size_mb" value="<?php echo esc_attr($max_image_mb); ?>" min="1" max="500" step="1" class="small-text">
                            <p class="description">Maximum file size for images (default: 1 MB).</p>
                        </td>
                    </tr>

                    <tr id="iv_video_size_row">
                        <th scope="row"><label for="iv_max_video_size_mb">Max Video Size (MB)</label></th>
                        <td>
                            <input type="number" name="iv_max_video_size_mb" id="iv_max_video_size_mb" value="<?php echo esc_attr($max_video_mb); ?>" min="1" max="1000" step="1" class="small-text">
                            <p class="description">Maximum file size for videos (default: 30 MB).</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_form_access">Form Access</label></th>
                        <td>
                            <label><input type="radio" name="iv_form_access" value="public" <?php checked($form_access, 'public'); ?>> Public (anyone can submit)</label><br>
                            <label><input type="radio" name="iv_form_access" value="private" <?php checked($form_access, 'private'); ?>> Private (logged-in users only)</label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_recaptcha_type">reCAPTCHA Type</label></th>
                        <td>
                            <select name="iv_recaptcha_type" id="iv_recaptcha_type">
                                <option value="none" <?php selected($recaptcha_type, 'none'); ?>> None (honeypot only)</option>
                                <option value="v2" <?php selected($recaptcha_type, 'v2'); ?>> v2 Checkbox</option>
                                <option value="v3" <?php selected($recaptcha_type, 'v3'); ?>> v3 Invisible</option>
                            </select>
                        </td>
                    </tr>

                    <tr id="iv_recaptcha_v3_threshold_row" style="display: <?php echo $recaptcha_type === 'v3' ? 'table-row' : 'none'; ?>;">
                        <th scope="row"><label for="iv_recaptcha_v3_threshold">v3 Score Threshold</label></th>
                        <td>
                            <input type="number" name="iv_recaptcha_v3_threshold" id="iv_recaptcha_v3_threshold" value="<?php echo esc_attr($recaptcha_v3_threshold); ?>" step="0.1" min="0" max="1" class="small-text">
                            <p class="description">Default 0.5</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_recaptcha_site_key">reCAPTCHA Site Key</label></th>
                        <td><input type="text" name="iv_recaptcha_site_key" id="iv_recaptcha_site_key" value="<?php echo esc_attr($recaptcha_site_key); ?>" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_recaptcha_secret_key">reCAPTCHA Secret Key</label></th>
                        <td><input type="text" name="iv_recaptcha_secret_key" id="iv_recaptcha_secret_key" value="<?php echo esc_attr($recaptcha_secret_key); ?>" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_field_label_title">Title Field Label ("Name")</label></th>
                        <td><input type="text" name="iv_field_label_title" value="<?php echo esc_attr($field_label_title); ?>" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_field_label_content">Content Field Label ("Description")</label></th>
                        <td><input type="text" name="iv_field_label_content" value="<?php echo esc_attr($field_label_content); ?>" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_field_label_image">Image Upload Label</label></th>
                        <td><input type="text" name="iv_field_label_image" value="<?php echo esc_attr($field_label_image); ?>" class="regular-text"></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="iv_field_label_video">Video Upload Label</label></th>
                        <td><input type="text" name="iv_field_label_video" value="<?php echo esc_attr($field_label_video); ?>" class="regular-text"></td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="iv_save_settings" class="button button-primary" value="Save Settings">
                </p>
            </form>
        </div>

        <div id="iv-docs-tab-content" class="iv-tab-content">
            <?php echo wp_kses_post($doc_content); ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.iv-tab-content').removeClass('active');
                $(target + '-content').addClass('active');
            });

            var recaptchaType = $('#iv_recaptcha_type');
            var thresholdRow = $('#iv_recaptcha_v3_threshold_row');
            function toggleThreshold() {
                thresholdRow.css('display', recaptchaType.val() === 'v3' ? 'table-row' : 'none');
            }
            toggleThreshold();
            recaptchaType.on('change', toggleThreshold);

            var uploadMode = $('#iv_upload_mode');
            function toggleSizeRows() {
                var mode = uploadMode.val();
                $('#iv_image_size_row').css('display', (mode === 'images' || mode === 'both') ? 'table-row' : 'none');
                $('#iv_video_size_row').css('display', (mode === 'videos' || mode === 'both') ? 'table-row' : 'none');
            }
            toggleSizeRows();
            uploadMode.on('change', toggleSizeRows);
        });
        </script>

        <style>
        .iv-tab-content { display: none !important; }
        .iv-tab-content.active { display: block !important; }
        .iv-tab-content pre { background: #f1f1f1; padding: 15px; overflow-x: auto; }
        </style>
    </div>
    <?php
}

function iv_manage_submissions_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Permission denied.'));
    }

    $cpt_slug = get_option('iv_cpt_slug', 'image-video-submission');

    if (isset($_GET['action'], $_GET['post_id'], $_GET['_wpnonce']) && in_array($_GET['action'], ['publish', 'private', 'draft'])) {
        $post_id = intval($_GET['post_id']);
        $action  = sanitize_text_field($_GET['action']);
        if (wp_verify_nonce($_GET['_wpnonce'], 'iv_status_' . $post_id)) {
            wp_update_post(array('ID' => $post_id, 'post_status' => $action));
            echo '<div class="notice notice-success"><p>Status updated.</p></div>';
        }
    }

    $submissions = new WP_Query(array(
        'post_type'      => $cpt_slug,
        'posts_per_page' => -1,
        'post_status'    => array('pending', 'publish', 'draft', 'private')
    ));
    ?>
    <div class="wrap">
        <h1>Manage Submissions</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead><tr><th>Title</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ($submissions->have_posts()) : while ($submissions->have_posts()) : $submissions->the_post(); ?>
                <tr>
                    <td><a href="<?php echo get_edit_post_link(); ?>"><?php the_title(); ?></a></td>
                    <td><?php echo ucfirst(get_post_status()); ?></td>
                    <td><?php echo get_the_date(); ?></td>
                    <td>
                        <?php
                        $nonce = wp_create_nonce('iv_status_' . get_the_ID());
                        if (get_post_status() !== 'publish') echo '<a href="' . add_query_arg(array('action' => 'publish', 'post_id' => get_the_ID(), '_wpnonce' => $nonce)) . '" class="button">Publish</a> ';
                        if (get_post_status() !== 'private') echo '<a href="' . add_query_arg(array('action' => 'private', 'post_id' => get_the_ID(), '_wpnonce' => $nonce)) . '" class="button">Private</a> ';
                        if (get_post_status() !== 'draft') echo '<a href="' . add_query_arg(array('action' => 'draft', 'post_id' => get_the_ID(), '_wpnonce' => $nonce)) . '" class="button">Draft</a>';
                        ?>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="4">No submissions.</td></tr>
            <?php endif; wp_reset_postdata(); ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>