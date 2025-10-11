# Ekwa Slider - Animation System

## Overview
The Ekwa Slider uses animations from [Animate.css](https://animate.style/) integrated directly into the main CSS file for content block animations. This provides professional, smooth animations with optimal performance.

## Version
Plugin Version: **0.5.1**

## Available Animations

The following animations are available for slider content blocks:

1. **fadeInDown** - Fade in from top
2. **fadeInLeft** - Fade in from left
3. **fadeInRight** - Fade in from right
4. **fadeInUp** - Fade in from bottom
5. **flipInX** - Flip in on X-axis (3D effect)
6. **slideInDown** - Slide in from top
7. **slideInUp** - Slide in from bottom

## Usage

### In Block Editor
1. Add a **Slide Item** block
2. Inside it, add one or more **Slider Content** blocks
3. For each Slider Content block:
   - Open the block settings (right sidebar)
   - Select an animation type from the dropdown
   - Set an animation delay in milliseconds (0-5000ms)
   - Add any content (text, images, buttons, etc.) inside the block

### Animation Settings
- **Animation Type**: Choose from 7 different animations
- **Animation Delay**: Delay before animation starts (in milliseconds)
  - Default: 0ms (immediate)
  - Range: 0-5000ms (0-5 seconds)
  - Step: 100ms

### Animation Duration
Default duration is **0.8 seconds** per animation.

You can modify speed using CSS custom properties:
```css
:root {
  --animate-duration: 0.8s; /* Default */
}

/* Faster animations */
.animate__animated.animate__faster {
  animation-duration: calc(var(--animate-duration) / 2); /* 0.4s */
}

/* Slower animations */
.animate__animated.animate__slower {
  animation-duration: calc(var(--animate-duration) * 3); /* 2.4s */
}
```

## Files Modified

### CSS Files (Merged)
- `assets/css/slider-frontend.css` - Now includes all slider styles AND animate.css animations in one file
  - **Note:** `slider-animate.css` has been removed and merged into the main CSS file for better performance

### Modified Files
- `includes/blocks/slider-content.php` - Removed separate CSS enqueue (animations now in main CSS)
- `assets/js/slider-content-block.js` - Dropdown selector for animations
- `assets/js/slider-frontend.js` - Retriggers animate.css animations on slide change
- `ekwa-slider.php` - Version bumped to 0.5.1

## Bug Fixes (v0.5.1)
- Fixed initial transition glitch where next slide would briefly flash
- Slides now properly hidden with `opacity: 0` and `visibility: hidden` until active
- Merged CSS files for better performance and easier maintenance

## Technical Details

### CSS Classes Applied
When an animation is selected, the following classes are added to the content block:
- `ekwa-slider-content-block` - Base class
- `animate__animated` - Animate.css base class
- `animate__[animationType]` - Specific animation class (e.g., `animate__fadeInUp`)

### Animation Delay
Animation delays are applied via inline styles:
```html
<div class="ekwa-slider-content-block animate__animated animate__fadeInUp"
     style="animation-delay: 0.5s; -webkit-animation-delay: 0.5s;">
  <!-- Content -->
</div>
```

### Animation Retriggering
When a slide becomes active, the JavaScript:
1. Finds all content blocks with `animate__animated` class
2. Removes the specific animation class (e.g., `animate__fadeInUp`)
3. Forces a reflow to reset the animation
4. Re-adds the animation class to retrigger it

This ensures animations play every time a slide is shown, not just once.

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Includes `-webkit-` prefixes for older Safari/Chrome versions
- Respects `prefers-reduced-motion` for accessibility

## Performance
- GPU-accelerated animations using `transform` and `opacity`
- Uses `translate3d()` for hardware acceleration
- Minimal CSS (only 7 animations, ~6KB minified)
- No JavaScript animation libraries required

## Future Enhancements
To add more animations from Animate.css:
1. Open the original [Animate.css](https://github.com/animate-css/animate.css/blob/main/animate.css)
2. Copy the keyframes and class definition for the desired animation
3. Add to the animate.css section in `assets/css/slider-frontend.css`
4. Add the option to the dropdown in `assets/js/slider-content-block.js`

## Performance
- Single CSS file (merged slider-frontend.css)
- Only 7 animations included (~6KB for animations)
- All animations GPU-accelerated using `transform` and `opacity`
- Uses `translate3d()` for hardware acceleration
- No JavaScript animation libraries required
- Proper CSS containment to prevent initial flash/glitch

## Accessibility
- Respects `prefers-reduced-motion` media query
- Animations are instantly completed if user has motion sensitivity settings enabled
- ARIA attributes properly maintained during transitions
