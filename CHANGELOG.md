# Ekwa Slider Changelog

## Version 0.6.0 (2025-10-11)

### New Features

#### Navigation Controls Settings
- **Show/Hide Arrows**: Toggle navigation arrows on/off from settings page
- **Show/Hide Dots**: Toggle navigation dots on/off from settings page
- **Arrow Style Selector**: Choose from 5 different arrow styles:
  - Default (simple arrows)
  - Chevron (‹ ›)
  - Angle (triangle arrows)
  - Circle Arrow (arrows with circular outline)
  - Square Arrow (arrows with square outline)

### UI/UX Improvements

#### Progressive Enhancement
- Controls (arrows and dots) are now hidden until slider content is fully loaded
- Eliminates flash of controls during page load
- Smoother initial page render

#### Hover-Based Navigation
- Navigation arrows only appear when hovering over the slider
- Cleaner, less cluttered interface
- Arrows fade in smoothly on hover with 0.3s transition
- Always visible on mobile for better touch accessibility

#### Repositioned Dots
- Dots now overlay the slider at the bottom (positioned absolutely)
- Semi-transparent dark background with blur effect for better visibility
- Modern glassmorphism design with `backdrop-filter: blur(10px)`
- 20px from bottom, centered horizontally
- White dots with transparent fill, solid white center when active

#### Improved Arrow Styling
- White circular backgrounds with subtle shadow
- Larger click target (48px × 48px on desktop, 40px on mobile)
- Smooth scale animation on hover (1.1x)
- Enhanced shadow on hover for depth
- Pure CSS arrow icons (no font dependencies)
- All arrow styles use pseudo-elements for better performance

### Responsive Design
- Mobile (≤767px):
  - Controls always visible (no hover required)
  - Smaller arrow buttons (40px)
  - Adjusted dot positioning (15px from bottom)
  - Smaller dots (8px)
- Tablet & Desktop:
  - Hover-to-reveal controls
  - Larger interactive elements
  - Smooth transitions

### Technical Improvements
- Added `ekwa-slider-initialized` class after content loads
- CSS-based control visibility using `:not()` and `:hover` selectors
- Better performance with CSS-only animations
- No JavaScript required for show/hide behavior
- Arrow icons rendered with CSS pseudo-elements (no SVG files needed)
- Backdrop blur effect for modern browsers

### Settings Page Updates
- New "Navigation Controls" section with checkboxes for arrows and dots
- New "Arrow Style" dropdown with 5 style options
- Better organization of settings
- Default values: arrows ON, dots ON, default arrow style

### Files Modified
1. `includes/settings.php` - Added navigation and arrow style settings
2. `includes/shortcode.php` - Updated to respect new settings, added arrow style classes
3. `assets/css/slider-frontend.css` - Complete redesign of controls, dots positioning, arrow styles
4. `assets/js/slider-frontend.js` - Added initialized class after setup
5. `ekwa-slider.php` - Version bump to 0.6.0

### Accessibility
- All controls maintain keyboard accessibility
- Proper ARIA labels on all buttons
- Focus indicators on all interactive elements
- Controls visible when focused (not just hover)

### Browser Compatibility
- Modern browsers: Full glassmorphism effect with backdrop-filter
- Older browsers: Graceful fallback to solid background
- All arrow styles work in all browsers (CSS-based)

---

## Version 0.5.1 (2025-10-11)

### Bug Fixes
- Fixed initial transition glitch where next slide would briefly flash
- Slides now properly hidden with `opacity: 0` and `visibility: hidden` until active
- Merged CSS files for better performance and easier maintenance

### Performance
- Merged `slider-animate.css` into `slider-frontend.css`
- Single CSS file instead of two separate files
- Reduced HTTP requests

### Files Modified
- `assets/css/slider-frontend.css` - Merged animations + fixed slide visibility
- `includes/blocks/slider-content.php` - Removed separate CSS reference
- `ekwa-slider.php` - Version bump

---

## Version 0.5.0 (2025-10-11)

### New Features
- Integrated Animate.css animations for slider content blocks
- 7 animation types: fadeInDown, fadeInLeft, fadeInRight, fadeInUp, flipInX, slideInDown, slideInUp
- Dropdown selector in block editor for easy animation selection
- Configurable animation delays (0-5000ms)

### Improvements
- Smoother fade transition (700ms with cubic-bezier easing)
- True crossfade effect (both slides visible during transition)
- GPU acceleration hints for better performance

---

## Version 0.4.2 (2025-10-11)

### Bug Fixes
- Fixed slide transition issues
- Improved slide and slide-fade transitions with proper positioning
- Better animation timing with requestAnimationFrame

---

## Version 0.4.0 and earlier

Initial development versions with core slider functionality, custom post types, block editor integration, and responsive design.
