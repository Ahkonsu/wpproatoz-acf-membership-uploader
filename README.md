# WPProAtoZ ACF Membership Uploader

**Version 2.0.0**

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

- WordPress 6.0+
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
```shortcode
[pe_tracker_uploader]
