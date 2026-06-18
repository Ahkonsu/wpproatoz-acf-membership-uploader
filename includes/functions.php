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
        'post_title'   => 'My Pet Tracker - ' . date('F Y'),
        'post_title'   => $new_title,
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


// ================================================
// PET TRACKER PUBLIC URL + VISIBILITY
// ================================================

function petracker_force_permalink($permalink, $post) {
    if (empty($post) || $post->post_type !== 'pe_tracker_entry') {
        return $permalink;
    }

    if (empty($post->post_name)) {
        if (!empty($post->post_title)) {
            $post->post_name = sanitize_title($post->post_title);
        } else {
            $post->post_name = 'pet-' . $post->ID;
        }
    }

    return home_url('/pet-tracker-page/' . $post->post_name . '/');
}
add_filter('post_type_link', 'petracker_force_permalink', 10, 2);
add_filter('preview_post_link', 'petracker_force_permalink', 10, 2);

/**
 * Auto-publish when user enables "Make Public"
 */
function petracker_auto_publish_on_public($post_id) {
    if (get_post_type($post_id) !== 'pe_tracker_entry') {
        return;
    }

    $is_public = get_field('pet_public', $post_id);
    if (!empty($is_public)) {
        wp_update_post([
            'ID'          => $post_id,
            'post_status' => 'publish'
        ]);
    }
}
add_action('acf/save_post', 'petracker_auto_publish_on_public', 20);

/**
 * Visibility Control
 */
function petracker_public_visibility_check() {
    if (!is_singular('pe_tracker_entry')) {
        return;
    }

    $post = get_queried_object();
    if (!$post || $post->post_type !== 'pe_tracker_entry') {
        return;
    }

    $is_public = get_field('pet_public', $post->ID);
    $current_user_id = get_current_user_id();
    $post_author_id  = (int) $post->post_author;

    // Owner can always view
    if (is_user_logged_in() && $current_user_id === $post_author_id) {
        return;
    }

    // Public visitors
    if (get_post_status($post->ID) !== 'publish' || empty($is_public)) {
        wp_die('This Pet Tracker is not publicly available yet.', 'Access Restricted', ['response' => 403]);
    }
}
add_action('template_redirect', 'petracker_public_visibility_check', 15);

/**
 * Restrict registration to specific emails for alpha stage (Caretaker accounts).
 * 
 * Place in: includes/class-validation.php
 * Hook into: pmpro_registration_checks (or your existing validation method).
 */
function wpproatoz_restrict_alpha_emails( $continue, $user ) {
    // Define your approved emails (add more as needed for alpha testers)
    $allowed_emails = array(
        'tester1@petrackers.com/',
        'tester2@petrackers.com',
        // Add your invite list here
    );

    // Get the email from the checkout submission
    $email = isset( $_REQUEST['bemail'] ) ? sanitize_email( $_REQUEST['bemail'] ) : '';

    if ( ! empty( $email ) && ! in_array( $email, $allowed_emails, true ) ) {
        // Optional: You could make this level-specific if needed
        // global $pmpro_level;
        // if ( $pmpro_level && $pmpro_level->id == YOUR_CARETAKER_LEVEL_ID ) { ... }

        pmpro_setMessage( 'Alpha/Beta access is currently by invite-only. ' .
                         'Please use an approved email address or ' .
                         '<a href="https://petrackers.com/alpha-beta-wait-list/" target="_blank" rel="noopener">join our wait-list here</a>.', 'pmpro_error' );
        $continue = false;
    }

    return $continue;
}
add_filter( 'pmpro_registration_checks', 'wpproatoz_restrict_alpha_emails', 10, 2 );

/**
 * Update permalink/slug when title changes on frontend
 */
function iv_update_permalink_on_title_change($post_id) {
    if (get_post_type($post_id) !== get_option('iv_cpt_slug', 'pe_tracker_entry')) {
        return;
    }

    // Only run for frontend saves from our form (or always for this CPT)
    if (wp_is_post_revision($post_id) || !is_user_logged_in()) {
        return;
    }

    $post = get_post($post_id);
    $new_slug = sanitize_title($post->post_title);

    if ($post->post_name !== $new_slug && !empty($new_slug)) {
        wp_update_post([
            'ID'        => $post_id,
            'post_name' => $new_slug,
        ]);
    }
}
add_action('acf/save_post', 'iv_update_permalink_on_title_change', 25);  // After auto-publish hook

//backend test ajax handler for pettracker email list
add_action('wp_ajax_iv_send_test_pet_alert', 'iv_send_test_pet_alert');
function iv_send_test_pet_alert() {
    // Basic security - enhance with nonce later
    if (!is_user_logged_in()) wp_send_json_error('Not logged in');
    
    $user_id = get_current_user_id();
    $contacts = get_field('pettracker_neighbor_contacts', 'user_' . $user_id);
    
    if (empty($contacts)) {
        wp_send_json_error('No contacts in your list.');
    }
    
    $sent = 0;
    foreach ($contacts as $c) {
        $email = $c['pettracker_neighbor_contact__email'] ?? '';
        if (!empty($email) && is_email($email)) {
            wp_mail(
                $email,
                'Test Alert from PetTracker',
                "This is a test notification from your neighbor on Petracker.com.\n\nA real alert would include their pet's video and details.\n\nStay vigilant!",
                'From: alerts@petrackers.com'
            );
            $sent++;
        }
    }
    wp_send_json_success('Test alert sent to ' . $sent . ' neighbors.');
}

// bacend real ajax hadler
add_action('wp_ajax_iv_send_real_pet_alert', 'iv_send_real_pet_alert');
function iv_send_real_pet_alert() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }
    
    $user_id   = get_current_user_id();
    $entry_id  = pe_get_or_create_user_entry();
    $contacts  = get_field('pettracker_neighbor_contacts', 'user_' . $user_id);
    $custom    = sanitize_textarea_field($_POST['custom_message'] ?? '');
    
    if (empty($contacts)) {
        wp_send_json_error('No neighbors in your list.');
    }
    
    // Gather pet data
    $pet_name_key     = get_option('iv_pet_name_key', '');
    $pet_desc_key     = get_option('iv_pet_description_key', '');
    $emergency_email_key = get_option('iv_emergency_email_key', '');
    $emergency_phone_key = get_option('iv_emergency_phone_key', '');
    $video_key        = get_option('iv_video_field_key', '');
    $public_toggle_key = 'pet_public';
    
    $pet_name     = get_field($pet_name_key, $entry_id) ?: 'Your Pet';
    $description  = get_field($pet_desc_key, $entry_id) ?: '';
    $emergency_email = get_field($emergency_email_key, $entry_id) ?: '';
    $emergency_phone = get_field($emergency_phone_key, $entry_id) ?: '';
    $video        = get_field($video_key, $entry_id);
    $video_url    = $video ? $video['url'] : '';
    $is_public    = get_field($public_toggle_key, $entry_id);
    $tracker_url  = get_permalink($entry_id);
    
    $subject = "🚨 LOST PET ALERT: " . $pet_name;
    
    $message = "EMERGENCY: " . $pet_name . " is missing!\n\n";
    if ($custom) {
        $message .= "Last seen / additional details: " . $custom . "\n\n";
    }
    if ($description) {
        $message .= "Pet Description: " . $description . "\n\n";
    }
    
    // Emergency Contact Info
    if ($emergency_email || $emergency_phone) {
        $message .= "Emergency Contact:\n";
        if ($emergency_email) $message .= "Email: " . $emergency_email . "\n";
        if ($emergency_phone) $message .= "Phone: " . $emergency_phone . "\n";
        $message .= "\n";
    }
    
    // Shareable Tracker Link (only if public)
    if ($is_public && $tracker_url) {
        $message .= "📍 View full details and video here: " . esc_url($tracker_url) . "\n\n";
    } else {
        $message .= "Note: Public sharing is not yet enabled on the tracker page.\n\n";
    }
    
    if ($video_url) {
        $message .= "🎥 Video of " . $pet_name . ": " . $video_url . "\n\n";
    }
    
    $message .= "Please help by checking your area and sharing with others. Thank you!\n\n";
    $message .= "- Sent via Petracker.com Neighborhood Safety Network";
    
    $sent = 0;
    foreach ($contacts as $c) {
        $email = $c['pettracker_neighbor_contact__email'] ?? '';
        if (is_email($email)) {
            wp_mail($email, $subject, $message, 'From: alerts@petrackers.com');
            $sent++;
        }
    }
    
    wp_send_json_success(['sent' => $sent]);
}