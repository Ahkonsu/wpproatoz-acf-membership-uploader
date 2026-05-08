# ACF Image & Video Frontend Submission Form

![Plugin Version](https://img.shields.io/badge/version-1.3.9-blue)
![WordPress Compatibility](https://img.shields.io/badge/WordPress-6.0%2B-green)
![PHP Compatibility](https://img.shields.io/badge/PHP-8.0%2B-green)
![License](https://img.shields.io/badge/license-GPLv2%2B-blue)

A WordPress plugin for frontend image and video submissions using Advanced Custom Fields Pro (ACF Pro). Submissions are saved as drafts with admin review tools.

## Overview
This plugin creates a secure frontend form for users to submit images and/or videos. Integrates with a configurable custom post type and ACF Pro fields. All submissions are saved as **drafts** for moderation.

## Features
- **Dual Media Support**: Images (.jpg, .jpeg, .png, .webp, .gif) and videos (.mp4, .mov, .m4v).
- **Configurable Limits**: Admin-set max file sizes (default: 1 MB images, 30 MB videos).
- **Upload Modes**: Images only, Videos only, or Both.
- **Clear Warnings**: Prominent file type/size limitation messages on the form.
- **Custom Labels**: Configurable title, description, and upload field labels.
- **Access Control**: Public (guests) or private (logged-in only).
- **Spam Protection**: reCAPTCHA v2/v3 or honeypot.
- **Admin Tools**:
  - Settings page for CPT slug, field keys, sizes, reCAPTCHA, etc.
  - Manage Submissions page to publish/private/draft.
  - Documentation tab.
- **Client-Side Enhancements**: Friendly "Choose Image"/"Choose Video" buttons and validation.

Shortcode: `[image_video_submission]`

## Installation
1. Download from GitHub Releases or clone into `/wp-content/plugins/`.
2. Activate the plugin.
3. Install/activate ACF Pro when prompted.
4. Configure in **Image & Video Submission > Settings**.

## Usage
- Add `[image_video_submission]` to a page for the form.
- Submissions appear as drafts in your CPT.
- Review/publish via **Manage Submissions**.

## Requirements
- WordPress 6.0+
- PHP 8.0+
- Advanced Custom Fields Pro

## Contributing
Fork, branch, PR – welcome! Report issues on GitHub.

## Changelog
### 1.3.9 = (2025-12-29)
- Added prominent yellow warning boxes inside uploaders showing admin-configured limits.
- Implemented client-side and server-side validation for file type/size enforcement.
- Fixed admin menu permissions and improved settings instructions for field keys.
- Forced form visibility with robust CSS overrides for theme/Elementor compatibility.
- Removed external dependencies; all assets local.

### 1.3.8 (2025-12-23)
- Finalized warning message display with strong CSS overrides.
- Improved spacing and visibility.

### 1.3.7 (2025-12-23)
- JS-based warning messages inside upload boxes.
- Dynamic text with configurable sizes.

### 1.3.6 (2025-12-23)
- Configurable max sizes, GIF support, draft submissions.

### 1.3.1 (2025-12-08)
- Documentation tab added.

### 1.3 (2025-12-08)
- Separate image/video field keys and upload modes.

### 1.2 (2025-12-08)
- Image support, dedicated admin menu.

### 1.1 (2025-12-08)
- Configurable CPT and field key.

### 1.0 (2025-06-11)
- Initial release.

## License
GPLv2 or later.