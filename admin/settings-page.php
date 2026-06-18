<?php
/**
 * Admin Settings Page for WPProAtoZ ACF Membership Uploader
 */
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'iv_add_admin_menu');

function iv_add_admin_menu() {
    add_menu_page(
        'Membership Uploader',
        'Membership Uploader',
        'manage_options',
        'iv-settings',
        'iv_settings_page',
        'dashicons-format-image',
        30
    );
    add_submenu_page(
        'iv-settings',
        'Manage Submissions',
        'Manage Submissions',
        'manage_options',
        'iv-manage-submissions',
        'iv_manage_submissions_page'
    );
}

// ====================== SETTINGS PAGE ======================
function iv_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // ====================== SAVE SETTINGS ======================
    if (isset($_POST['iv_save_settings']) && check_admin_referer('iv_save_form_settings')) {
       
        $form_access = isset($_POST['iv_form_access']) && $_POST['iv_form_access'] === 'public' ? 'public' : 'private';
        $recaptcha_type = in_array($_POST['iv_recaptcha_type'] ?? '', ['v2', 'v3', 'none']) ? $_POST['iv_recaptcha_type'] : 'none';
        $recaptcha_v3_threshold = floatval($_POST['iv_recaptcha_v3_threshold'] ?? 0.5);
        $recaptcha_v3_threshold = max(0.0, min(1.0, $recaptcha_v3_threshold));
        $cpt_slug = sanitize_text_field($_POST['iv_cpt_slug'] ?? 'pe_tracker_entry');
        $upload_mode = in_array($_POST['iv_upload_mode'] ?? '', ['images', 'videos', 'both']) ? $_POST['iv_upload_mode'] : 'both';
        $image_field_key = sanitize_text_field($_POST['iv_image_field_key'] ?? '');
        $video_field_key = sanitize_text_field($_POST['iv_video_field_key'] ?? '');

        // NEW: Pet Details Field Keys
        $pet_description_key = sanitize_text_field($_POST['iv_pet_description_key'] ?? '');
        $emergency_email_key = sanitize_text_field($_POST['iv_emergency_email_key'] ?? '');
        $emergency_phone_key = sanitize_text_field($_POST['iv_emergency_phone_key'] ?? '');
        $pet_name_key = sanitize_text_field($_POST['iv_pet_name_key'] ?? '');

        $max_image_mb = max(1, min(500, intval($_POST['iv_max_image_size_mb'] ?? 1)));
        $max_video_mb = max(1, min(500, intval($_POST['iv_max_video_size_mb'] ?? 30)));

        // Save core settings
        update_option('iv_form_access', $form_access);
        update_option('iv_recaptcha_type', $recaptcha_type);
        update_option('iv_recaptcha_v3_threshold', $recaptcha_v3_threshold);
        update_option('iv_cpt_slug', $cpt_slug);
        update_option('iv_upload_mode', $upload_mode);
        update_option('iv_image_field_key', $image_field_key);
        update_option('iv_video_field_key', $video_field_key);

        // NEW: Save Pet Detail Field Keys
        update_option('iv_pet_description_key', $pet_description_key);
        update_option('iv_emergency_email_key', $emergency_email_key);
        update_option('iv_emergency_phone_key', $emergency_phone_key);
        update_option('iv_pet_name_key', $pet_name_key);

        update_option('iv_max_image_size_mb', $max_image_mb);
        update_option('iv_max_video_size_mb', $max_video_mb);

        // ====================== SAVE TIER LIMITS ======================
        $new_tier_limits = [];
        if (isset($_POST['iv_tier']) && is_array($_POST['iv_tier'])) {
            foreach ($_POST['iv_tier'] as $level_id => $data) {
                $level_id = intval($level_id);
                if ($level_id > 0) {
                    $new_tier_limits[$level_id] = [
                        'max_images' => max(1, intval($data['max_images'] ?? 5)),
                        'max_video_mb' => max(1, intval($data['max_video_mb'] ?? 30))
                    ];
                }
            }
        }
        update_option('iv_tier_limits', $new_tier_limits);

        echo '<div class="notice notice-success is-dismissible"><p>✅ Settings saved successfully.</p></div>';
    }

    // Load current values
    $form_access = get_option('iv_form_access', 'private');
    $recaptcha_type = get_option('iv_recaptcha_type', 'none');
    $recaptcha_v3_threshold = get_option('iv_recaptcha_v3_threshold', 0.5);
    $recaptcha_site_key = get_option('iv_recaptcha_site_key', '');
    $recaptcha_secret_key = get_option('iv_recaptcha_secret_key', '');
    $cpt_slug = get_option('iv_cpt_slug', 'pe_tracker_entry');
    $upload_mode = get_option('iv_upload_mode', 'both');
    $image_field_key = get_option('iv_image_field_key', '');
    $video_field_key = get_option('iv_video_field_key', '');

    // NEW: Load Pet Detail Field Keys
    $pet_name_key = get_option('iv_pet_name_key', '');
    $pet_description_key = get_option('iv_pet_description_key', '');
    $emergency_email_key = get_option('iv_emergency_email_key', '');
    $emergency_phone_key = get_option('iv_emergency_phone_key', '');
    $pet_name_key        = get_option('iv_pet_name_key', '');

    $max_image_mb = get_option('iv_max_image_size_mb', 1);
    $max_video_mb = get_option('iv_max_video_size_mb', 30);
    $tier_limits = get_option('iv_tier_limits', []);
    $pmpro_levels = function_exists('pmpro_getAllLevels') ? pmpro_getAllLevels() : [];
    ?>
    <div class="wrap">
        <h1>Membership Uploader Settings</h1>

        <div class="notice notice-warning">
            <p><strong>Critical: Use ACF Field Keys (not names or labels)</strong></p>
            <ol>
                <li>Go to <strong>Custom Fields → Field Groups</strong> in the admin.</li>
                <li>Edit the field group used for your Pet Tracker submissions.</li>
                <li>Top right: Click <strong>Screen Options</strong> → Check <strong>Field Keys</strong>.</li>
                <li>The key appears next to each field (e.g., <code>field_682e59ec3b45a</code>).</li>
                <li>Copy and paste the <strong>exact key</strong> (starts with "field_") into the boxes below.</li>
            </ol>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('iv_save_form_settings'); ?>
            <table class="form-table">

                <!-- Existing fields... -->
                <tr>
                    <th scope="row"><label for="iv_cpt_slug">Custom Post Type Slug</label></th>
                    <td>
                        <input type="text" name="iv_cpt_slug" id="iv_cpt_slug" value="<?php echo esc_attr($cpt_slug); ?>" class="regular-text">
                        <p class="description">Exact slug of your Pet Tracker CPT</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="iv_upload_mode">Upload Mode</label></th>
                    <td>
                        <select name="iv_upload_mode" id="iv_upload_mode">
                            <option value="both" <?php selected($upload_mode, 'both'); ?>>Both Images and Video</option>
                            <option value="images" <?php selected($upload_mode, 'images'); ?>>Images Only</option>
                            <option value="videos" <?php selected($upload_mode, 'videos'); ?>>Video Only</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="iv_image_field_key">ACF Image Gallery Field Key</label></th>
                    <td>
                        <input type="text" name="iv_image_field_key" value="<?php echo esc_attr($image_field_key); ?>" class="regular-text">
                        <p class="description">Field key for the Gallery field (starts with <code>field_</code>)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="iv_video_field_key">ACF Video File Field Key</label></th>
                    <td>
                        <input type="text" name="iv_video_field_key" value="<?php echo esc_attr($video_field_key); ?>" class="regular-text">
                        <p class="description">Field key for the File field (starts with <code>field_</code>)</p>
                    </td>
                </tr>

                <!-- ==================== NEW PET DETAIL FIELDS ==================== -->
                <tr>
                    <th scope="row"><label for="iv_pet_description_key">Pet Description / Story Field Key</label></th>
                    <td>
                        <input type="text" name="iv_pet_description_key" value="<?php echo esc_attr($pet_description_key); ?>" class="regular-text">
                        <p class="description">ACF Field Key for "Your Pet Description - Story" (Textarea / WYSIWYG recommended)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="iv_emergency_email_key">Emergency Email Field Key</label></th>
                    <td>
                        <input type="text" name="iv_emergency_email_key" value="<?php echo esc_attr($emergency_email_key); ?>" class="regular-text">
                        <p class="description">ACF Field Key for Emergency Contact Email</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="iv_emergency_phone_key">Emergency Phone Number Field Key</label></th>
                    <td>
                        <input type="text" name="iv_emergency_phone_key" value="<?php echo esc_attr($emergency_phone_key); ?>" class="regular-text">
                        <p class="description">ACF Field Key for Emergency Phone Number</p>
                    </td>
                </tr>
                <!-- NEW: Pet Name -->
                <tr>
                    <th scope="row"><label for="iv_pet_name_key">Your Pet's Name Field Key</label></th>
                    <td>
                        <input type="text" name="iv_pet_name_key" value="<?php echo esc_attr($pet_name_key); ?>" class="regular-text">
                        <p class="description">ACF Field Key for "Your Pet's Name" (Text field)</p>
                    </td>
                </tr>
                <!-- ========================================================== -->

                <tr>
                    <th scope="row"><label for="iv_max_image_size_mb">Max Image Size (MB)</label></th>
                    <td><input type="number" name="iv_max_image_size_mb" value="<?php echo esc_attr($max_image_mb); ?>" min="1" class="small-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="iv_max_video_size_mb">Max Video Size (MB)</label></th>
                    <td><input type="number" name="iv_max_video_size_mb" value="<?php echo esc_attr($max_video_mb); ?>" min="1" class="small-text"></td>
                </tr>

            </table>

            <!-- Membership Tier Limits -->
            <h2 style="margin-top:40px;">🎯 Membership Tier Limits</h2>
            <p><strong>Auto-populated from Paid Memberships Pro.</strong></p>

            <table class="wp-list-table widefat fixed striped" style="max-width:900px;">
                <thead>
                    <tr>
                        <th>Level ID</th>
                        <th>Level Name</th>
                        <th>Max Images</th>
                        <th>Max Video Size (MB)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pmpro_levels)): ?>
                        <tr><td colspan="4">No PMPro levels found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pmpro_levels as $level): 
                            $level_id = $level->id;
                            $current = $tier_limits[$level_id] ?? ['max_images' => 5, 'max_video_mb' => 30];
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($level_id); ?></strong></td>
                            <td><strong><?php echo esc_html($level->name); ?></strong></td>
                            <td>
                                <input type="number" name="iv_tier[<?php echo $level_id; ?>][max_images]" 
                                       value="<?php echo esc_attr($current['max_images']); ?>" class="small-text" min="1">
                            </td>
                            <td>
                                <input type="number" name="iv_tier[<?php echo $level_id; ?>][max_video_mb]" 
                                       value="<?php echo esc_attr($current['max_video_mb']); ?>" class="small-text" min="1">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <p class="submit">
                <input type="submit" name="iv_save_settings" class="button button-primary button-large" value="Save All Settings">
            </p>
        </form>
    </div>
    <?php
}

// ====================== MANAGE SUBMISSIONS PAGE ======================
// ====================== MANAGE SUBMISSIONS PAGE ======================
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