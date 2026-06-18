=== WPProAtoZ ACF Membership Uploader ===
Contributors: Ahkonsu, wpproatoz, Grok
Donate link: https://wpproatoz.com
Tags: acf, membership, frontend upload, paid memberships pro, image upload, video upload, membership site, tiered limits
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 2.2.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Frontend ACF-powered image + video uploader for membership sites. One entry per user with dynamic tier limits based on Paid Memberships Pro.

== Description ==

**WPProAtoZ ACF Membership Uploader** is a powerful frontend submission plugin built for membership websites. It allows logged-in members to upload and manage images (gallery) and a single video directly from the frontend using Advanced Custom Fields Pro.

**Perfect for:**
- Membership / subscription sites (PE Tracker, fitness trackers, portfolio sites, etc.)
- One-entry-per-user workflows
- Tiered plans (Basic = 5 images, Premium = 10 images, etc.)

**Key Features**
* One entry per user (automatically creates or edits the same post)
* Dynamic tier limits powered by Paid Memberships Pro (images & video size)
* Full ACF Pro integration (Gallery + File fields)
* reCAPTCHA v3 + honeypot spam protection
* Draft submissions with admin moderation
* Beautiful integration with PMPro Account page
* Shortcode ready: `[pe_tracker_uploader]`
* Highly configurable via admin settings

**Requirements**
* Advanced Custom Fields Pro
* Paid Memberships Pro (for tier limits & membership features)

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin
3. Go to **Membership Uploader → Settings** and configure:
   - Custom Post Type slug
   - ACF Gallery field key
   - ACF Video field key
   – Pet Description / Story
   – Emergency Email
   – Emergency Phone Number
4. Create your membership levels in Paid Memberships Pro
5. Add the shortcode `[pe_tracker_uploader]` to a protected page or let it auto-appear on the PMPro Account page

== Frequently Asked Questions ==

= How does the one-entry-per-user system work? =
The plugin automatically finds the user's existing entry or creates a new draft on first use. Members can then edit the same entry indefinitely (add/remove images, replace video).

= Does it work with membership tiers? =
Yes. It reads the user's current PMPro level and dynamically sets the maximum number of images and video size via `acf/prepare_field`.

= Can I use it without Paid Memberships Pro? =
Yes — it falls back to basic functionality, but tier limits require PMPro.

== Screenshots ==

1. Settings page
2. Frontend form on PMPro Account page
3. Admin submissions list

== Changelog ==

= 2.2.9 June 18, 2026 =
* Added private tiered Neighbor Email List with real lost pet alert system.
* Real alerts include custom message, emergency contacts, video, and public tracker link.
* Full integration with existing ACF/PMPro frontend uploader.

= 2.2.8 June 18, 2026 =
* Added configurable "Your Pet's Name" field with admin settings integration and frontend display
* Enabled editable CPT title ("My Tracker Page Title") on the [pe_tracker_uploader] My Tracker page
* Added automatic permalink/slug update when title is changed on frontend
* Improved current pet information display section
* Minor UI/UX refinements for better user experience

= 2.2.7 June 12, 2026 =
Added code to limit registration to specific emails
minor tweaks to code

= 2.2.6 May 29, 2026 =
* Added full public sharing support for Pet Tracker entries
* Implemented clean permalink structure using /pet-tracker-page/ slug (even for drafts)
* Added "Make Public" toggle (ACF True/False field) on frontend uploader
* Auto-publish entry when user enables "Make Public"
* Added prominent "Share Your Tracker" box on frontend with live shareable link
* Added one-click "Copy Share Link" functionality with visual feedback
* Enhanced visibility logic: Owners can always view (even drafts), public users only see published + public entries
* Improved draft notices and user feedback throughout

= 2.2.5 May 28, 2026 =
Fixed "Either / Or" upload requirement — users can now upload just images, just video, or both (no more forced dual uploads)
Improved Current Media section: Removed non-functional delete checkboxes, added clean "Manage your media files" anchor link at the top
Enhanced frontend Video field to properly support WordPress Media Library selection (uploader => 'wp')
Added media library restriction to show only current user’s uploads (matching Gallery behavior)
Added three new Pet Details fields to plugin settings page:
– Pet Description / Story
– Emergency Email
– Emergency Phone Number
Integrated new Pet Details fields into frontend form (appear above Gallery/Video)
Updated "Current Media & Pet Information" section to display saved pet details alongside media
General code cleanup, improved field ordering, and better user guidance on the form

= 2.1.0 = May 22, 2026
* Major plugin structure refactor - organized into clean folders (`includes/`, `admin/`, `assets/`)
* Improved settings page with detailed ACF Field Key instructions
* Added video player display on the Pet Tracker form
* Made image and video uploads **optional** with "Delete current" functionality
* Enhanced deletion handling and validation bypass for removing media
* Better code organization and maintainability
* Updated version number and constants

= 2.0.1 =
* Working on integration for member tiers and limits on file sizes etc.

= 2.0.0 =
* Complete rewrite for membership sites
* One entry per user logic
* Paid Memberships Pro integration + dynamic tier limits
* New shortcode: [pe_tracker_uploader]
* Auto-add section to PMPro Account page
* Updated branding and code structure

= 1.3.9 =
* Original image + video submission plugin (WP Plugins A to Z)

== Upgrade Notice ==

= 2.1.0 =
This is a major structural update. The plugin has been reorganized into multiple files. Please test thoroughly after updating, especially the frontend form and delete functionality.
