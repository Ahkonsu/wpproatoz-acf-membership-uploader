=== WPProAtoZ ACF Membership Uploader ===
Contributors: Ahkonsu, wpproatoz
Donate link: https://petrackers.com/
Tags: acf, membership, frontend upload, paid memberships pro, pet tracker, community, lost pet
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 2.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Frontend ACF-powered image + video uploader for membership sites. One entry per user with dynamic tier limits based on Paid Memberships Pro. Built for Petracker.com.

== Description ==

**WPProAtoZ ACF Membership Uploader** enables logged-in members to create and manage their pet tracker with images and video directly from the frontend.

Perfect for Petracker.com — turning the stress of a missing pet into instant community action.

**Key Features**
* One entry per user (automatically creates/edits the same post)
* Dynamic tier limits powered by Paid Memberships Pro
* Full ACF Pro integration (Gallery + File fields)
* reCAPTCHA v3 + honeypot spam protection
* Beautiful PMPro Account page integration
* Shortcode ready: `[pe_tracker_uploader]`

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin
3. Go to **Membership Uploader → Settings** and configure CPT and ACF field keys
4. Add `[pe_tracker_uploader]` shortcode to a protected page

== Frequently Asked Questions ==

= How does the one-entry-per-user system work? =
The plugin automatically finds the user's existing pet tracker or creates a new draft on first use. Members can update the same entry indefinitely.

= Does it work with membership tiers? =
Yes. It reads the user's current PMPro level and dynamically limits images and video size.

== Screenshots ==