=== ACF Image & Video Frontend Submission Form ===
Contributors: wpproatoz, John, Grok
Tags: acf, custom post type, frontend form, image upload, video upload, recaptcha, draft submissions
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.3.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires Plugins: advanced-custom-fields-pro

== Description ==
The ACF Image & Video Frontend Submission Form plugin enables frontend submissions of images and/or videos using Advanced Custom Fields Pro (ACF Pro). Submissions are saved as drafts in a configurable custom post type for admin review.

Key features include:
- Separate support for image uploads (.jpg, .jpeg, .png, .webp, .gif) and video uploads (.mp4, .mov, .m4v).
- Configurable maximum file sizes (default: 1 MB for images, 30 MB for videos).
- Upload mode selection: Images only, Videos only, or Both.
- Prominent warning messages for file type and size limits.
- Customizable field labels.
- Public or private form access (guests or logged-in users only).
- reCAPTCHA v2 (checkbox) or v3 (invisible) support with configurable score threshold.
- Admin settings for CPT slug, separate ACF field keys, max sizes, and more.
- Manage submissions page to publish, make private, or keep as draft.
- Client-side validation with friendly "Choose Image"/"Choose Video" buttons.

Use the shortcode `[image_video_submission]` to display the form.

== Installation ==
1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin in the WordPress admin.
3. Install and activate Advanced Custom Fields Pro when prompted.
4. Go to **Image & Video Submission > Settings** to configure:
   - Custom Post Type slug.
   - Separate ACF field keys for image and video.
   - Upload mode and max file sizes.
   - Form access (public/private).
   - reCAPTCHA settings (optional).
5. Add `[image_video_submission]` shortcode to a page.

== Frequently Asked Questions ==
= What ACF field types are required? =
Image field for images, File field for videos. Use separate fields for image/video when "Both" mode is selected.

= How do I find ACF field keys? =
Edit your field group → Screen Options (top right) → check "Field Keys".

= Are submissions published automatically? =
No – all submissions are saved as drafts for admin review.

= Can guests submit? =
Yes, if "Form Access" is set to Public in settings.

= What are the file restrictions? =
Configurable in settings. Defaults: Images 1 MB (.png, .webp, .jpg, .gif); Videos 30 MB (.mp4, .mov, .m4v). Warning messages shown on form.

= How do I manage submissions? =
Go to **Image & Video Submission > Manage Submissions** to change statuses.

== Screenshots ==
1. Frontend submission form with warning messages and custom labels.
2. Admin settings page with upload mode, max sizes, field keys, and reCAPTCHA options.
3. Manage Submissions page for reviewing and publishing drafts.

== Changelog ==
= 1.3.9 = (2025-12-29)
* Added prominent yellow warning boxes inside uploaders showing admin-configured limits.
* Implemented client-side and server-side validation for file type/size enforcement.
* Fixed admin menu permissions and improved settings instructions for field keys.
* Forced form visibility with robust CSS overrides for theme/Elementor compatibility.
* Removed external dependencies; all assets local.

= 1.3.8 = (2025-12-23)
* Finalized warning messages using ACF instructions with forced visibility CSS.
* Improved uploader styling and spacing.
* Updated defaults to match current screenshot labels.

= 1.3.7 = (2025-12-23)
* Switched warning messages to JS injection inside upload box for maximum visibility.
* Added dynamic instruction text via localize_script.
* Increased uploader padding for better hint placement.

= 1.3.6 = (2025-12-23)
* Added configurable max upload sizes for images/videos.
* GIF support for images.
* Submissions saved as draft (not pending).
* Enhanced admin instructions for field keys.

= 1.3.1 = (2025-12-08)
* Added Documentation tab in admin settings.

= 1.3 = (2025-12-08)
* Separate field keys and upload mode (Images/Videos/Both).
* Independent validation and warnings.

= 1.2 = (2025-12-08)
* Added image support, dedicated admin menu, settings link.

= 1.1 = (2025-12-08)
* Configurable CPT slug and field key.

= 1.0 = (2025-06-11)
* Initial release.

== Upgrade Notice ==
= 1.3.8 =
Updated warning message display and styling. Clear caches after update.