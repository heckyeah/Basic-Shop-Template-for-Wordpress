/**
 * Product Thumbnail Slider
 * Handles image slider functionality for product listings on shop page
 * Features: Arrow navigation, click and drag (desktop and touch)
 */

(function($) {
	'use strict';

	// Initialize sliders when DOM is ready
	$(document).ready(function() {
		initProductThumbnailSliders();
	});

	// Re-initialize sliders after AJAX product updates
	$(document).on('updated_wc_div', function() {
		initProductThumbnailSliders();
	});

	// Re-initialize sliders after custom AJAX updates
	$(document).on('shopProductsUpdated', function() {
		initProductThumbnailSliders();
	});

	/**
	 * Initialize all product thumbnail sliders
	 */
	function initProductThumbnailSliders() {
		$('.product-thumbnail-slider').each(function() {
			const $slider = $(this);
			const sliderId = $slider.data('product-id');
			
			// Skip if already initialized
			if ($slider.data('slider-initialized')) {
				return;
			}

			// Mark as initialized
			$slider.data('slider-initialized', true);

			const $slides = $slider.find('.product-thumbnail-slide');
			const $wrapper = $slider.find('.product-thumbnail-slider-wrapper');
			const $prevArrow = $slider.find('.product-thumbnail-arrow-prev');
			const $nextArrow = $slider.find('.product-thumbnail-arrow-next');
			const $colorSwatches = $slider.find('.product-thumbnail-color-swatch');
			let currentSlide = 0;
			let isDragging = false;
			let hasDragged = false;
			let dragStartX = 0;
			let dragStartY = 0;
			let dragCurrentX = 0;
			let dragCurrentY = 0;
			let dragThreshold = 50; // Minimum distance to trigger slide change

			// Only proceed if there are multiple slides
			if ($slides.length <= 1) {
				return;
			}

			/**
			 * Show specific slide
			 * @param {number} index - Slide index to show
			 * @param {boolean} deselectSwatches - If true, deselect all color swatches
			 */
			function showSlide(index, deselectSwatches) {
				// Ensure index is within bounds
				if (index < 0) {
					index = $slides.length - 1;
				} else if (index >= $slides.length) {
					index = 0;
				}

				// Update slides
				$slides.removeClass('active');
				$slides.eq(index).addClass('active');

				currentSlide = index;
				
				// Handle color swatches
				if ($colorSwatches.length > 0) {
					if (deselectSwatches) {
						// Deselect all swatches
						$colorSwatches.removeClass('active');
					} else {
						// Update active color swatch if it matches the current image
						const currentImageId = $slides.eq(index).data('image-id');
						if (currentImageId) {
							$colorSwatches.removeClass('active');
							const $matchingSwatch = $colorSwatches.filter('[data-image-id="' + currentImageId + '"]');
							if ($matchingSwatch.length > 0) {
								$matchingSwatch.addClass('active');
							}
						}
					}
				}
			}

			/**
			 * Go to next slide
			 */
			function nextSlide() {
				showSlide(currentSlide + 1, true); // Deselect swatches when using arrows
			}

			/**
			 * Go to previous slide
			 */
			function prevSlide() {
				showSlide(currentSlide - 1, true); // Deselect swatches when using arrows
			}

			/**
			 * Handle mouse up and reset drag state
			 */
			function handleMouseUp(e) {
				if (isDragging) {
					isDragging = false;
					$wrapper.css('cursor', 'grab');
					$('body').css('user-select', '');
					
					const deltaX = dragStartX - dragCurrentX;
					const deltaY = dragStartY - dragCurrentY;
					const absDeltaX = Math.abs(deltaX);
					const absDeltaY = Math.abs(deltaY);
					
					// Only trigger slide change if horizontal movement is greater than vertical (horizontal drag)
					// and exceeds threshold
					if (absDeltaX > absDeltaY && absDeltaX > dragThreshold) {
						if (deltaX > 0) {
							// Dragged left - next slide
							nextSlide();
						} else {
							// Dragged right - previous slide
							prevSlide();
						}
					}
					
					// Reset drag values after a short delay to allow click prevention
					setTimeout(function() {
						dragStartX = 0;
						dragStartY = 0;
						dragCurrentX = 0;
						dragCurrentY = 0;
						hasDragged = false;
					}, 100);
					
					// Prevent link click if we dragged
					if (hasDragged && absDeltaX > dragThreshold) {
						e.preventDefault();
						e.stopPropagation();
						return false;
					}
				}
			}

			/**
			 * Handle mouse move during drag
			 */
			function handleMouseMove(e) {
				if (isDragging) {
					dragCurrentX = e.clientX;
					dragCurrentY = e.clientY;
					// Mark as dragged if moved more than 5px
					if (Math.abs(dragStartX - dragCurrentX) > 5 || Math.abs(dragStartY - dragCurrentY) > 5) {
						hasDragged = true;
					}
				}
			}

			// Arrow click handlers
			$nextArrow.on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				nextSlide();
				return false;
			});

			$prevArrow.on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				prevSlide();
				return false;
			});

			// Mouse drag functionality - bind to wrapper
			$wrapper.on('mousedown', function(e) {
				// Only start drag if clicking on the image area, not on links or arrows
				if ($(e.target).closest('a').length === 0 && 
					$(e.target).closest('.product-thumbnail-arrow').length === 0 &&
					!$(e.target).is('a')) {
					
					isDragging = true;
					hasDragged = false;
					dragStartX = e.clientX;
					dragStartY = e.clientY;
					dragCurrentX = e.clientX;
					dragCurrentY = e.clientY;
					
					$wrapper.css('cursor', 'grabbing');
					$('body').css('user-select', 'none');
					
					// Prevent default to stop text selection and link behavior
					e.preventDefault();
					e.stopPropagation();
					
					// Bind mouse move and mouse up to document for better tracking
					$(document).on('mousemove.productSlider' + sliderId, handleMouseMove);
					$(document).on('mouseup.productSlider' + sliderId, handleMouseUp);
				}
			});

			// Touch/swipe support for mobile
			let touchStartX = 0;
			let touchStartY = 0;
			let touchEndX = 0;
			let touchEndY = 0;

			$wrapper.on('touchstart', function(e) {
				const touch = e.originalEvent.touches[0];
				touchStartX = touch.clientX;
				touchStartY = touch.clientY;
			});

			$wrapper.on('touchmove', function(e) {
				// Prevent default to allow smooth dragging
				e.preventDefault();
			});

			$wrapper.on('touchend', function(e) {
				const touch = e.originalEvent.changedTouches[0];
				touchEndX = touch.clientX;
				touchEndY = touch.clientY;
				
				handleSwipe();
			});

			function handleSwipe() {
				const deltaX = touchStartX - touchEndX;
				const deltaY = touchStartY - touchEndY;
				const absDeltaX = Math.abs(deltaX);
				const absDeltaY = Math.abs(deltaY);
				const swipeThreshold = 50;

				// Only trigger if horizontal movement is greater than vertical and exceeds threshold
				if (absDeltaX > absDeltaY && absDeltaX > swipeThreshold) {
					if (deltaX > 0) {
						// Swipe left - next slide
						nextSlide();
					} else {
						// Swipe right - previous slide
						prevSlide();
					}
				}
			}

			// Set initial cursor style
			$wrapper.css('cursor', 'grab');
			
			// Color swatch click handlers
			$colorSwatches.on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
				
				const $swatch = $(this);
				const imageId = $swatch.data('image-id');
				
				if (!imageId) {
					return false;
				}
				
				// Find the slide with this image ID
				const $targetSlide = $slides.filter('[data-image-id="' + imageId + '"]');
				const targetSlideIndex = $slides.index($targetSlide);
				
				if (targetSlideIndex !== -1) {
					showSlide(targetSlideIndex);
					
					// Update active swatch
					$colorSwatches.removeClass('active');
					$swatch.addClass('active');
				}
				
				return false;
			});
			
			// Prevent parent link click when clicking arrows or swatches, or when dragging
			$slider.on('click', function(e) {
				// Prevent link navigation for arrow clicks and color swatch clicks
				if ($(e.target).closest('.product-thumbnail-arrow').length ||
				    $(e.target).closest('.product-thumbnail-color-swatch').length) {
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
				// Prevent link if we dragged
				if (hasDragged) {
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
			});
			
			// Also prevent link click on the wrapper if we dragged
			$wrapper.on('click', function(e) {
				if (hasDragged) {
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
			});
			
			// Clean up event handlers when slider is removed (for AJAX updates)
			$slider.on('remove', function() {
				$(document).off('mousemove.productSlider' + sliderId);
				$(document).off('mouseup.productSlider' + sliderId);
			});
		});
	}

})(jQuery);
