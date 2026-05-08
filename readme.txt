=== WPProAtoZ ACF Membership Uploader ===
Contributors: Ahkonsu, wpproatoz
Donate link: https://wpproatoz.com
Tags: acf, membership, frontend upload, paid memberships pro, image upload, video upload, membership site, tiered limits
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 2.0.0
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

= 2.0.0 =
This is a major update. Please review settings after upgrading and re-enter your ACF field keys.
