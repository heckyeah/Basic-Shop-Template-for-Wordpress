/**
 * Custom Product Gallery Functionality - 2 Column Grid (Desktop) / Slider (Mobile)
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		var $gallery = $('.custom-product-gallery');
		if (!$gallery.length) {
			return;
		}

		var $galleryImages = $('.gallery-grid-image');
		var $gallerySlides = $('.gallery-slide');
		var $lightbox = $('#product-lightbox');
		var $lightboxImage = $('#lightbox-image');
		var $lightboxClose = $('#lightbox-close');
		var $lightboxPrev = $('#lightbox-prev');
		var $lightboxNext = $('#lightbox-next');
		var $galleryLinks = $('.gallery-grid-image-link, .gallery-slide-link');
		
		var currentImageIndex = 0;
		var images = [];
		
		// Slider elements
		var $slider = $('.gallery-slider');
		var $sliderTrack = $('.gallery-slider-track');
		var $sliderPrev = $('.gallery-slider-prev');
		var $sliderNext = $('.gallery-slider-next');
		var $sliderDots = $('.gallery-slider-dots');
		var currentSlide = 0;
		var isDragging = false;
		var startPos = 0;
		var currentTranslate = 0;
		var prevTranslate = 0;
		var animationID = 0;
		var hasMoved = false;

		// Build images array from gallery (works for both grid and slider)
		$galleryImages.length ? $galleryImages.each(function(index) {
			var $img = $(this);
			images.push({
				fullImage: $img.data('full-image') || $img.closest('a').attr('href'),
				imageId: $img.data('image-id')
			});
		}) : $gallerySlides.each(function(index) {
			var $img = $(this).find('img');
			images.push({
				fullImage: $img.data('full-image') || $img.closest('a').attr('href'),
				imageId: $img.data('image-id')
			});
		});

		// Gallery image click handler (grid and slider)
		$galleryLinks.on('click', function(e) {
			e.preventDefault();
			var $link = $(this);
			var imageIndex = parseInt($link.data('image-index'), 10);
			var fullImageUrl = $link.attr('href');
			
			currentImageIndex = imageIndex;
			openLightbox(fullImageUrl);
		});
		
		// Initialize slider if it exists
		if ($slider.length && $gallerySlides.length > 0) {
			initSlider();
		}

		// Open lightbox
		function openLightbox(imageUrl) {
			if (!imageUrl) {
				return;
			}
			
			$lightboxImage.attr('src', imageUrl);
			$lightbox.addClass('active');
			$('body').addClass('lightbox-open');
			updateNavigationButtons();
		}

		// Close lightbox
		function closeLightbox() {
			$lightbox.removeClass('active');
			$('body').removeClass('lightbox-open');
		}

		// Navigate to previous image
		function showPreviousImage() {
			if (currentImageIndex > 0) {
				currentImageIndex--;
			} else {
				currentImageIndex = images.length - 1;
			}
			updateLightboxImage();
		}

		// Navigate to next image
		function showNextImage() {
			if (currentImageIndex < images.length - 1) {
				currentImageIndex++;
			} else {
				currentImageIndex = 0;
			}
			updateLightboxImage();
		}

		// Update lightbox image
		function updateLightboxImage() {
			if (images[currentImageIndex]) {
				$lightboxImage.attr('src', images[currentImageIndex].fullImage);
			}
			updateNavigationButtons();
		}

		// Update navigation button visibility
		function updateNavigationButtons() {
			if (images.length <= 1) {
				$lightboxPrev.hide();
				$lightboxNext.hide();
			} else {
				$lightboxPrev.show();
				$lightboxNext.show();
			}
		}

		// Event handlers
		$lightboxClose.on('click', function(e) {
			e.preventDefault();
			closeLightbox();
		});

		$lightboxPrev.on('click', function(e) {
			e.preventDefault();
			showPreviousImage();
		});

		$lightboxNext.on('click', function(e) {
			e.preventDefault();
			showNextImage();
		});

		// Close on background click
		$lightbox.on('click', function(e) {
			if ($(e.target).is($lightbox)) {
				closeLightbox();
			}
		});

		// Keyboard navigation
		$(document).on('keydown', function(e) {
			if ($lightbox.hasClass('active')) {
				if (e.key === 'Escape') {
					closeLightbox();
				} else if (e.key === 'ArrowLeft') {
					showPreviousImage();
				} else if (e.key === 'ArrowRight') {
					showNextImage();
				}
			}
		});

		// Initialize
		if (images.length > 0) {
			updateNavigationButtons();
		}
		
		// Slider Functions
		function initSlider() {
			createDots();
			updateSlider();
			
			// Navigation buttons
			$sliderPrev.on('click', function(e) {
				e.preventDefault();
				if (currentSlide > 0) {
					currentSlide--;
					updateSlider();
				}
			});
			
			$sliderNext.on('click', function(e) {
				e.preventDefault();
				if (currentSlide < $gallerySlides.length - 1) {
					currentSlide++;
					updateSlider();
				}
			});
			
			// Touch events for swipe
			$sliderTrack.on('touchstart', touchStart);
			$sliderTrack.on('touchmove', touchMove);
			$sliderTrack.on('touchend', touchEnd);
			
			// Prevent link clicks during drag/swipe
			$sliderTrack.find('.gallery-slide-link').on('click', function(e) {
				if (hasMoved) {
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
			});
		}
		
		function createDots() {
			$sliderDots.empty();
			var totalSlides = $gallerySlides.length;
			
			if (totalSlides > 1) {
				var $counter = $('<div>')
					.addClass('gallery-slider-counter')
					.attr('aria-label', 'Slide ' + (currentSlide + 1) + ' of ' + totalSlides);
				
				var $prevBtn = $('<button>')
					.attr('aria-label', 'Previous slide')
					.addClass('gallery-slider-counter-prev')
					.html('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>')
					.on('click', function(e) {
						e.preventDefault();
						e.stopPropagation();
						if (currentSlide > 0) {
							currentSlide--;
							updateSlider();
						}
					});
				
				var $current = $('<span>')
					.addClass('gallery-slider-current')
					.text((currentSlide + 1));
				
				var $separator = $('<span>')
					.addClass('gallery-slider-counter-separator')
					.text('/');
				
				var $total = $('<span>')
					.addClass('gallery-slider-total')
					.text(totalSlides);
				
				var $nextBtn = $('<button>')
					.attr('aria-label', 'Next slide')
					.addClass('gallery-slider-counter-next')
					.html('<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>')
					.on('click', function(e) {
						e.preventDefault();
						e.stopPropagation();
						if (currentSlide < totalSlides - 1) {
							currentSlide++;
							updateSlider();
						}
					});
				
				$counter.append($prevBtn);
				$counter.append($current);
				$counter.append($separator);
				$counter.append($total);
				$counter.append($nextBtn);
				
				$sliderDots.append($counter);
			}
		}
		
		function updateSlider() {
			var translateX = -currentSlide * 100;
			$sliderTrack.css('transform', 'translateX(' + translateX + '%)');
			
			// Update counter
			var $current = $sliderDots.find('.gallery-slider-current');
			if ($current.length) {
				$current.text(currentSlide + 1);
			}
			
			// Update counter navigation buttons
			var $counterPrev = $sliderDots.find('.gallery-slider-counter-prev');
			var $counterNext = $sliderDots.find('.gallery-slider-counter-next');
			if ($counterPrev.length) {
				$counterPrev.prop('disabled', currentSlide === 0);
			}
			if ($counterNext.length) {
				$counterNext.prop('disabled', currentSlide === $gallerySlides.length - 1);
			}
			
			// Update navigation buttons
			$sliderPrev.toggle(currentSlide > 0);
			$sliderNext.toggle(currentSlide < $gallerySlides.length - 1);
			
			// Update current image index for lightbox
			currentImageIndex = currentSlide;
		}
		
		function touchStart(e) {
			hasMoved = false;
			isDragging = true;
			startPos = getPositionX(e);
			animationID = requestAnimationFrame(animation);
			$sliderTrack.addClass('grabbing');
		}
		
		function touchMove(e) {
			if (!isDragging) return;
			e.preventDefault();
			hasMoved = true;
			var currentPosition = getPositionX(e);
			currentTranslate = prevTranslate + currentPosition - startPos;
		}
		
		function touchEnd(e) {
			if (!isDragging) return;
			cancelAnimationFrame(animationID);
			isDragging = false;
			$sliderTrack.removeClass('grabbing');
			
			var movedBy = currentTranslate - prevTranslate;
			
			if (movedBy < -50 && currentSlide < $gallerySlides.length - 1) {
				currentSlide++;
			}
			if (movedBy > 50 && currentSlide > 0) {
				currentSlide--;
			}
			
			setPositionByIndex();
			
			// Reset after a short delay to allow click event
			setTimeout(function() {
				hasMoved = false;
			}, 100);
		}
		
		function setPositionByIndex() {
			currentTranslate = -currentSlide * $sliderTrack[0].offsetWidth;
			prevTranslate = currentTranslate;
			updateSlider();
		}
		
		function animation() {
			setSliderPosition();
			if (isDragging) {
				requestAnimationFrame(animation);
			}
		}
		
		function setSliderPosition() {
			$sliderTrack.css('transform', 'translateX(' + currentTranslate + 'px)');
		}
		
		function getPositionX(e) {
			return e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
		}
	});

})(jQuery);
