/**
 * Front Page Product Slider
 * Initializes Swiper slider for products on the front page
 */

(function() {
	'use strict';
	
	document.addEventListener('DOMContentLoaded', function() {
		const productSlider = document.querySelector('.product-slider-swiper');
		
		if ( productSlider && typeof Swiper !== 'undefined' ) {
			new Swiper('.product-slider-swiper', {
				slidesPerView: 4.2, // Show 4 full products + partial view of next
				spaceBetween: 20,
				loop: true,
				navigation: {
					nextEl: '.swiper-button-next',
					prevEl: '.swiper-button-prev',
				},
				breakpoints: {
					// When window width is >= 320px
					320: {
						slidesPerView: 1.2,
						spaceBetween: 10,
					},
					// When window width is >= 640px
					640: {
						slidesPerView: 2.2,
						spaceBetween: 15,
					},
					// When window width is >= 768px
					768: {
						slidesPerView: 3.2,
						spaceBetween: 20,
					},
					// When window width is >= 1024px
					1024: {
						slidesPerView: 4.2,
						spaceBetween: 20,
					},
				},
			});
		}
	});
})();

