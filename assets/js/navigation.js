/**
 * Navigation Menu Toggle Functionality
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		var $menuToggle = $('.menu-toggle');
		var $navigation = $('.main-navigation');
		var $menu = $navigation.find('ul');

		// Toggle menu on button click
		$menuToggle.on('click', function(e) {
			e.preventDefault();
			var isExpanded = $(this).attr('aria-expanded') === 'true';
			
			// Toggle aria-expanded attribute
			$(this).attr('aria-expanded', !isExpanded);
			
			// Toggle menu visibility
			$menu.toggleClass('menu-open');
		});

		// Close menu when clicking outside
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.main-navigation').length && !$(e.target).closest('.menu-toggle').length) {
				$menuToggle.attr('aria-expanded', 'false');
				$menu.removeClass('menu-open');
			}
		});

		// Close menu on escape key
		$(document).on('keydown', function(e) {
			if (e.key === 'Escape' && $menu.hasClass('menu-open')) {
				$menuToggle.attr('aria-expanded', 'false');
				$menu.removeClass('menu-open');
				$menuToggle.focus();
			}
		});

		// Close menu when clicking on a menu link (for mobile/tablet)
		$menu.find('a').on('click', function() {
			if ($(window).width() <= 1024) {
				$menuToggle.attr('aria-expanded', 'false');
				$menu.removeClass('menu-open');
			}
		});
	});

})(jQuery);

