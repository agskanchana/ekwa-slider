# Ekwa Slider

Version: 0.1.0
Author: Ekwa

## Description
A foundational WordPress plugin that registers a private **Slides** custom post type (`ekwa_slide`) for managing slider content in the admin area. Includes an (empty placeholder) settings page for future configuration options.

## Features
- Custom Post Type: Slides (`ekwa_slide`)
  - Not publicly queryable (no front-end display yet)
  - Excluded from search
  - Hidden from Yoast SEO XML sitemaps
- Admin Settings Page (placeholder)
- Activation/Deactivation hooks with rewrite flush safety

## Roadmap Ideas
- Slider front-end shortcode or block
- Slide ordering UI / drag & drop
- Settings: transition effects, autoplay, delay
- Template overrides

## Installation
1. Upload the plugin folder to `/wp-content/plugins/` or develop directly in place.
2. Activate via WordPress Admin > Plugins.
3. Manage slides via the **Slides** menu.
4. Configure (future) options via **Ekwa Slider** settings page.

## Changelog
### 0.1.0
- Initial release with CPT and settings scaffold.

## License
GPL-2.0-or-later. See license terms at https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

