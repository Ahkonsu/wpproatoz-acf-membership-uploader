# WPProAtoZ ACF Membership Uploader


![Plugin Version](https://img.shields.io/badge/version-2.2.8-blue)
![WordPress Compatibility](https://img.shields.io/badge/WordPress-6.0%2B-green)
![PHP Compatibility](https://img.shields.io/badge/PHP-8.0%2B-green)
![License](https://img.shields.io/badge/license-GPLv2%2B-blue)

**Version 2.2.6**

Frontend ACF uploader designed for paid membership websites. One entry per user with dynamic tier limits based on Paid Memberships Pro.

![Plugin Banner](assets/banner-1544x500.png) <!-- Add a nice banner later -->

## Features

- **One Entry Per User** — Automatically creates or loads the member's single tracker entry
- **Dynamic Tier Limits** — Automatically adjusts max images (5 / 10 / etc.) and video size based on PMPro membership level
- **ACF Pro Powered** — Uses your existing Gallery + File fields
- **PMPro Account Integration** — Automatically adds "My PE Tracker" section to the membership account page
- **Spam Protection** — reCAPTCHA v3 + honeypot
- **Admin Moderation** — Submissions start as drafts (configurable)
- **Shortcode** — `[pe_tracker_uploader]`
- **Lightweight & Developer Friendly** — Built to be extended

## Requirements

- WordPress 7.0+
- PHP 8.0+
- Advanced Custom Fields Pro
- Paid Memberships Pro (recommended for tiers)
- Stripe or PayPal gateways (via PMPro)

## Installation

1. Clone or download this repository
2. Place in `/wp-content/plugins/wpproatoz-acf-membership-uploader/`
3. Activate from WordPress admin
4. Go to **Membership Uploader → Settings** and configure CPT + ACF field keys

## Usage

### Shortcode
[ pe_tracker_uploader ]

### Changelog

= 2.2.8 June 18, 2026 =
* Added configurable "Your Pet's Name" field with admin settings integration and frontend display
* Enabled editable CPT title ("My Tracker Page Title") on the [pe_tracker_uploader] My Tracker page
* Added automatic permalink/slug update when title is changed on frontend
* Improved current pet information display section
* Minor UI/UX refinements for better user experience

= 2.2.7 June 12, 2026 =
* Added code to limit registration to specific emails
* minor tweaks to code

= 2.2.6 May 29, 2026 =
* Added full public sharing support for Pet Tracker entries
* Implemented clean permalink structure using /pet-tracker-page/ slug (even for drafts)
* Added "Make Public" toggle (ACF True/False field) on frontend uploader
* Auto-publish entry when user enables "Make Public"
* Added prominent "Share Your Tracker" box on frontend with live shareable link
* Added one-click "Copy Share Link" functionality with visual feedback
* Enhanced visibility logic: Owners can always view (even drafts), public users only see published + public entries
* Improved draft notices and user feedback throughout

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