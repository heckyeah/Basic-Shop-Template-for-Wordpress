/**
 * Last Viewed Products Tracking
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		// Check if we're on a single product page
		if ($('body').hasClass('single-product')) {
			var productId = null;

			// Try to get product ID from body class
			var bodyClasses = $('body').attr('class');
			if (bodyClasses) {
				var match = bodyClasses.match(/product-id-(\d+)/);
				if (match) {
					productId = match[1];
				}
			}

			// Fallback: try to get from product element
			if (!productId) {
				var $product = $('.product');
				if ($product.length) {
					var productIdAttr = $product.attr('id');
					if (productIdAttr) {
						var idMatch = productIdAttr.match(/product-(\d+)/);
						if (idMatch) {
							productId = idMatch[1];
						}
					}
				}
			}

			if (productId) {
				trackProductView(productId);
			}
		}
	});

	/**
	 * Track product view in localStorage
	 */
	function trackProductView(productId) {
		var viewedProducts = getViewedProducts();
		var maxProducts = 10;

		// Remove current product if it exists
		viewedProducts = viewedProducts.filter(function(id) {
			return id !== productId.toString();
		});

		// Add current product to the beginning
		viewedProducts.unshift(productId.toString());

		// Limit to max products
		viewedProducts = viewedProducts.slice(0, maxProducts);

		// Save to localStorage
		try {
			localStorage.setItem('basic_shop_viewed_products', JSON.stringify(viewedProducts));
		} catch (e) {
			// localStorage not available, use cookie fallback
			setCookie('basic_shop_viewed_products', JSON.stringify(viewedProducts), 30);
		}
	}

	/**
	 * Get viewed products from localStorage or cookie
	 */
	function getViewedProducts() {
		try {
			var stored = localStorage.getItem('basic_shop_viewed_products');
			if (stored) {
				return JSON.parse(stored);
			}
		} catch (e) {
			// Fallback to cookie
			var cookie = getCookie('basic_shop_viewed_products');
			if (cookie) {
				try {
					return JSON.parse(cookie);
				} catch (e2) {
					return [];
				}
			}
		}
		return [];
	}

	/**
	 * Set cookie helper
	 */
	function setCookie(name, value, days) {
		var expires = '';
		if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = '; expires=' + date.toUTCString();
		}
		document.cookie = name + '=' + (value || '') + expires + '; path=/';
	}

	/**
	 * Get cookie helper
	 */
	function getCookie(name) {
		var nameEQ = name + '=';
		var ca = document.cookie.split(';');
		for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) === ' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
		}
		return null;
	}

})(jQuery);

