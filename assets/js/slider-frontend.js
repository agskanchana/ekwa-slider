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

		// Don't initialize controls for mobile banner or single slides
		if (this.slider.classList.contains('ekwa-slider-mobile-banner') || this.totalSlides <= 1) {
			return;
		}

		this.init();
	}

	init() {
		this.bindEvents();
		this.setInitialState();
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
		const contentBlocks = slideElement.querySelectorAll('.ekwa-slider-content-block.has-animation');

		contentBlocks.forEach(block => {
			const delay = parseInt(block.dataset.animationDelay) || 0;

			// Reset animation state
			block.classList.remove('animate');

			// Trigger animation after delay
			setTimeout(() => {
				block.classList.add('animate');
			}, delay);
		});
	}

	goToSlide(slideIndex) {
		if (this.isTransitioning || slideIndex === this.currentSlide || slideIndex < 0 || slideIndex >= this.totalSlides) {
			return;
		}

		this.isTransitioning = true;
		const currentSlideElement = this.slides[this.currentSlide];
		const nextSlideElement = this.slides[slideIndex];

		// Reset animations on current slide content
		const currentContentBlocks = currentSlideElement.querySelectorAll('.ekwa-slider-content-block.has-animation');
		currentContentBlocks.forEach(block => block.classList.remove('animate'));

		// Fade out current slide
		currentSlideElement.classList.add('fade-out');

		setTimeout(() => {
			// Hide current slide
			currentSlideElement.style.display = 'none';
			currentSlideElement.setAttribute('aria-hidden', 'true');
			currentSlideElement.classList.remove('fade-out');

			// Show next slide
			nextSlideElement.style.display = 'block';
			nextSlideElement.setAttribute('aria-hidden', 'false');
			nextSlideElement.classList.add('fade-in');

			// Update current slide index
			this.currentSlide = slideIndex;

			// Update dots
			this.updateDots();

			// Trigger animations on new slide content
			this.animateSlideContent(nextSlideElement);

			// Clean up animation class
			setTimeout(() => {
				nextSlideElement.classList.remove('fade-in');
				this.isTransitioning = false;
			}, 500);

		}, 250);
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