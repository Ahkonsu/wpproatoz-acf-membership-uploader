# WPProAtoZ ACF Membership Uploader

![Plugin Version](https://img.shields.io/badge/version-2.0.1-blue)
![WordPress Compatibility](https://img.shields.io/badge/WordPress-6.0%2B-green)
![PHP Compatibility](https://img.shields.io/badge/PHP-8.0%2B-green)
![License](https://img.shields.io/badge/license-GPLv2%2B-blue)

**Frontend ACF uploader for paid membership websites.** One entry per user with dynamic tier limits based on Paid Memberships Pro.

Built specifically for **[Petracker.com](https://petrackers.com/)** — helping pet owners quickly mobilize their community when a beloved pet goes missing.

---

## Mission Alignment

Petracker.com turns the panic of a missing pet into **immediate, pre-planned action**. This plugin powers the frontend video + image upload experience so members can rapidly create and share a pet tracker with neighbors.

---

## Features

- **One Entry Per User** — Automatically loads or creates the member’s single pet tracker
- **Dynamic Tier Limits** — Adjusts max images/videos based on PMPro membership level
- **ACF Pro Powered** — Uses your existing Gallery + File fields
- **PMPro Integration** — Adds “My PE Tracker” section to the membership account page
- **Spam Protection** — reCAPTCHA v3 + honeypot
- **Admin Moderation** — Submissions start as drafts (configurable)
- **Shortcode** — `[pe_tracker_uploader]`

---

## Requirements

- WordPress 6.0+
- Advanced Custom Fields Pro
- Paid Memberships Pro (recommended for tiered limits)

---

## Installation

1. Place the plugin in `/wp-content/plugins/wpproatoz-acf-membership-uploader/`
2. Activate from WordPress admin
3. Go to **Membership Uploader → Settings** and configure CPT + ACF field keys
4. Add the shortcode `[pe_tracker_uploader]` to a protected page

---

## Usage

See full documentation in `documentation.txt` or the WordPress admin settings page.