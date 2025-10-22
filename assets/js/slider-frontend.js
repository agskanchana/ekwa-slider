/**
 * Ekwa Slider Frontend JavaScript
 */

class EkwaSlider {
	constructor(element) {
		this.slider = element;
		this.slides = this.slider.querySelectorAll('.ekwa-slider-slide');
		this.prevButton = this.slider.querySelector('.ekwa-slider-prev');
		this.nextButton = this.slider.querySelector('.ekwa-slider-next');
		this.dots = this.slider.querySelectorAll('.ekwa-slider-dot');
		this.currentSlide = 0;
		this.totalSlides = this.slides.length;
		this.isTransitioning = false;
		this.autoplayInterval = null;
		this.autoplayDelay = 5000; // 5 seconds
		this.isMobileBanner = this.slider.classList.contains('ekwa-slider-mobile-banner');
		this.transitionStyle = this.slider.dataset.transition || 'fade';

		// Always initialize for content animations, but skip controls for mobile banner or single slides
		if (this.isMobileBanner || this.totalSlides <= 1) {
			this.initMobileBannerOrSingleSlide();
		} else {
			this.init();
		}
	}

	init() {
		this.bindEvents();
		this.setInitialState();

		// Mark slider as initialized (shows controls)
		this.slider.classList.add('ekwa-slider-initialized');

		this.startAutoplay();

		// Pause autoplay when tab is not visible
		document.addEventListener('visibilitychange', () => {
			if (document.hidden) {
				this.stopAutoplay();
			} else {
				this.startAutoplay();
			}
		});
	}

	initMobileBannerOrSingleSlide() {
		// Set initial state for mobile banner or single slide
		this.slides.forEach((slide, index) => {
			slide.style.display = index === 0 ? 'block' : 'none';
			slide.setAttribute('aria-hidden', index === 0 ? 'false' : 'true');
		});

		// Mark as initialized
		this.slider.classList.add('ekwa-slider-initialized');

		// Animate content in the first (and likely only) slide
		if (this.slides[0]) {
			this.animateSlideContent(this.slides[0]);
		}
	}

	bindEvents() {
		// Navigation buttons
		if (this.prevButton) {
			this.prevButton.addEventListener('click', () => this.prevSlide());
		}

		if (this.nextButton) {
			this.nextButton.addEventListener('click', () => this.nextSlide());
		}

		// Dots navigation
		this.dots.forEach((dot, index) => {
			dot.addEventListener('click', () => this.goToSlide(index));
		});

		// Keyboard navigation
		this.slider.addEventListener('keydown', (e) => {
			switch (e.key) {
				case 'ArrowLeft':
					e.preventDefault();
					this.prevSlide();
					break;
				case 'ArrowRight':
					e.preventDefault();
					this.nextSlide();
					break;
				case ' ':
				case 'Enter':
					if (e.target.classList.contains('ekwa-slider-dot')) {
						e.preventDefault();
						const slideIndex = parseInt(e.target.dataset.slide);
						this.goToSlide(slideIndex);
					}
					break;
			}
		});

		// Touch/swipe support
		let startX = 0;
		let startY = 0;
		let endX = 0;
		let endY = 0;
		const minSwipeDistance = 50;

		this.slider.addEventListener('touchstart', (e) => {
			startX = e.touches[0].clientX;
			startY = e.touches[0].clientY;
		}, { passive: true });

		this.slider.addEventListener('touchmove', (e) => {
			// Prevent default scrolling during horizontal swipes
			if (Math.abs(e.touches[0].clientX - startX) > Math.abs(e.touches[0].clientY - startY)) {
				e.preventDefault();
			}
		}, { passive: false });

		this.slider.addEventListener('touchend', (e) => {
			endX = e.changedTouches[0].clientX;
			endY = e.changedTouches[0].clientY;

			const deltaX = endX - startX;
			const deltaY = endY - startY;

			// Check if horizontal swipe is more significant than vertical
			if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
				if (deltaX > 0) {
					this.prevSlide();
				} else {
					this.nextSlide();
				}
			}
		}, { passive: true });

		// Pause autoplay on hover/focus
		this.slider.addEventListener('mouseenter', () => this.stopAutoplay());
		this.slider.addEventListener('mouseleave', () => this.startAutoplay());
		this.slider.addEventListener('focusin', () => this.stopAutoplay());
		this.slider.addEventListener('focusout', () => this.startAutoplay());
	}

	setInitialState() {
		this.slides.forEach((slide, index) => {
			slide.style.display = index === 0 ? 'block' : 'none';
			slide.setAttribute('aria-hidden', index === 0 ? 'false' : 'true');
		});

		if (this.dots.length > 0) {
			this.updateDots();
		}

		// Animate first slide content
		if (this.slides[0]) {
			this.animateSlideContent(this.slides[0]);
		}
	}

	animateSlideContent(slideElement) {
		// Find all content blocks with animate__animated class (animate.css)
		const contentBlocks = slideElement.querySelectorAll('.ekwa-slider-content-block.animate__animated');

		contentBlocks.forEach(block => {
			// Get the animation class (e.g., animate__fadeInUp)
			const animationClass = Array.from(block.classList).find(cls => cls.startsWith('animate__') && cls !== 'animate__animated');

			if (animationClass) {
				// Remove and re-add the animation class to retrigger it
				block.classList.remove(animationClass);

				// Force reflow to ensure animation restarts
				void block.offsetWidth;

				// Re-add the animation class
				block.classList.add(animationClass);
			}
		});
	}

	goToSlide(slideIndex) {
		if (this.isTransitioning || slideIndex === this.currentSlide || slideIndex < 0 || slideIndex >= this.totalSlides) {
			return;
		}

		this.isTransitioning = true;
		const currentSlideElement = this.slides[this.currentSlide];
		const nextSlideElement = this.slides[slideIndex];

		// Apply transition based on style
		this.applyTransition(currentSlideElement, nextSlideElement, slideIndex);
	}

	applyTransition(currentSlide, nextSlide, slideIndex) {
		const transitionDuration = 500;
		const isForward = slideIndex > this.currentSlide || (this.currentSlide === this.totalSlides - 1 && slideIndex === 0);

		switch (this.transitionStyle) {
			case 'slide':
				this.slideTransition(currentSlide, nextSlide, isForward, transitionDuration);
				break;
			case 'slide-fade':
				this.slideFadeTransition(currentSlide, nextSlide, isForward, transitionDuration);
				break;
			case 'zoom':
				this.zoomTransition(currentSlide, nextSlide, transitionDuration);
				break;
			case 'fade':
			default:
				this.fadeTransition(currentSlide, nextSlide, transitionDuration);
				break;
		}

		// Update current slide index
		this.currentSlide = slideIndex;

		// Update dots
		this.updateDots();

		// Trigger animations on new slide content after transition
		setTimeout(() => {
			this.animateSlideContent(nextSlide);
			this.isTransitioning = false;
		}, transitionDuration);
	}

	fadeTransition(currentSlide, nextSlide, duration) {
		// Use longer duration for smoother fade
		const fadeDuration = 700;

		// Remove active class from current slide
		currentSlide.classList.remove('active');
		currentSlide.classList.add('transitioning-out');

		// Add active class to next slide immediately
		nextSlide.classList.add('active');
		nextSlide.setAttribute('aria-hidden', 'false');

		// Start crossfade animation
		requestAnimationFrame(() => {
			currentSlide.classList.add('fade-out');
			nextSlide.classList.add('fade-in');

			setTimeout(() => {
				// Cleanup current slide
				currentSlide.setAttribute('aria-hidden', 'true');
				currentSlide.classList.remove('fade-out', 'transitioning-out');

				// Cleanup next slide
				nextSlide.classList.remove('fade-in');
			}, fadeDuration);
		});
	}

	slideTransition(currentSlide, nextSlide, isForward, duration) {
		const direction = isForward ? 'left' : 'right';
		const container = this.slider.querySelector('.ekwa-slider-container');

		// Set container to relative positioning to contain absolute slides
		const originalHeight = container.offsetHeight;
		container.style.minHeight = originalHeight + 'px';

		// Position both slides absolutely for simultaneous animation
		currentSlide.style.position = 'absolute';
		currentSlide.style.top = '0';
		currentSlide.style.left = '0';
		currentSlide.style.width = '100%';
		currentSlide.style.zIndex = '1';

		nextSlide.style.position = 'absolute';
		nextSlide.style.top = '0';
		nextSlide.style.left = '0';
		nextSlide.style.width = '100%';
		nextSlide.style.zIndex = '2';
		nextSlide.style.display = 'block';
		nextSlide.setAttribute('aria-hidden', 'false');

		// Add transition classes
		requestAnimationFrame(() => {
			currentSlide.classList.add('slide-out-' + direction);
			nextSlide.classList.add('slide-in-' + direction);

			setTimeout(() => {
				// Hide and cleanup current slide
				currentSlide.style.display = 'none';
				currentSlide.setAttribute('aria-hidden', 'true');
				currentSlide.classList.remove('slide-out-' + direction);

				// Cleanup next slide classes and styles
				nextSlide.classList.remove('slide-in-' + direction);
				nextSlide.style.position = '';
				nextSlide.style.top = '';
				nextSlide.style.left = '';
				nextSlide.style.width = '';
				nextSlide.style.zIndex = '';

				// Reset current slide styles
				currentSlide.style.position = '';
				currentSlide.style.top = '';
				currentSlide.style.left = '';
				currentSlide.style.width = '';
				currentSlide.style.zIndex = '';

				// Reset container height
				container.style.minHeight = '';
			}, duration);
		});
	}

	slideFadeTransition(currentSlide, nextSlide, isForward, duration) {
		const direction = isForward ? 'left' : 'right';
		const container = this.slider.querySelector('.ekwa-slider-container');

		// Set container to relative positioning to contain absolute slides
		const originalHeight = container.offsetHeight;
		container.style.minHeight = originalHeight + 'px';

		// Position both slides absolutely for simultaneous animation
		currentSlide.style.position = 'absolute';
		currentSlide.style.top = '0';
		currentSlide.style.left = '0';
		currentSlide.style.width = '100%';
		currentSlide.style.zIndex = '1';

		nextSlide.style.position = 'absolute';
		nextSlide.style.top = '0';
		nextSlide.style.left = '0';
		nextSlide.style.width = '100%';
		nextSlide.style.zIndex = '2';
		nextSlide.style.display = 'block';
		nextSlide.setAttribute('aria-hidden', 'false');

		// Add transition classes
		requestAnimationFrame(() => {
			currentSlide.classList.add('slide-fade-out-' + direction);
			nextSlide.classList.add('slide-fade-in-' + direction);

			setTimeout(() => {
				// Hide and cleanup current slide
				currentSlide.style.display = 'none';
				currentSlide.setAttribute('aria-hidden', 'true');
				currentSlide.classList.remove('slide-fade-out-' + direction);

				// Cleanup next slide classes and styles
				nextSlide.classList.remove('slide-fade-in-' + direction);
				nextSlide.style.position = '';
				nextSlide.style.top = '';
				nextSlide.style.left = '';
				nextSlide.style.width = '';
				nextSlide.style.zIndex = '';

				// Reset current slide styles
				currentSlide.style.position = '';
				currentSlide.style.top = '';
				currentSlide.style.left = '';
				currentSlide.style.width = '';
				currentSlide.style.zIndex = '';

				// Reset container height
				container.style.minHeight = '';
			}, duration);
		});
	}	zoomTransition(currentSlide, nextSlide, duration) {
		currentSlide.classList.add('zoom-out');

		setTimeout(() => {
			currentSlide.style.display = 'none';
			currentSlide.setAttribute('aria-hidden', 'true');
			currentSlide.classList.remove('zoom-out');

			nextSlide.style.display = 'block';
			nextSlide.setAttribute('aria-hidden', 'false');
			nextSlide.classList.add('zoom-in');

			setTimeout(() => {
				nextSlide.classList.remove('zoom-in');
			}, duration);
		}, duration / 2);
	}

	nextSlide() {
		const nextIndex = (this.currentSlide + 1) % this.totalSlides;
		this.goToSlide(nextIndex);
	}

	prevSlide() {
		const prevIndex = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
		this.goToSlide(prevIndex);
	}

	updateDots() {
		this.dots.forEach((dot, index) => {
			dot.classList.toggle('active', index === this.currentSlide);
		});
	}

	startAutoplay() {
		if (this.totalSlides <= 1) return;

		this.stopAutoplay();
		this.autoplayInterval = setInterval(() => {
			this.nextSlide();
		}, this.autoplayDelay);
	}

	stopAutoplay() {
		if (this.autoplayInterval) {
			clearInterval(this.autoplayInterval);
			this.autoplayInterval = null;
		}
	}

	destroy() {
		this.stopAutoplay();
		// Remove event listeners if needed for cleanup
	}
}

// Initialize sliders when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
	const sliders = document.querySelectorAll('.ekwa-slider');
	sliders.forEach(slider => {
		new EkwaSlider(slider);
	});
});

// Reinitialize if new sliders are added dynamically
window.EkwaSlider = EkwaSlider;