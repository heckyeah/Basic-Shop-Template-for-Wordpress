/**
 * Toast Notification System for WooCommerce Messages
 */
(function($) {
	'use strict';

	// Create toast container if it doesn't exist
	function createToastContainer() {
		if ($('#woocommerce-toast-container').length === 0) {
			$('body').append('<div id="woocommerce-toast-container"></div>');
		}
	}

	// Show toast notification
	function showToast(message, type) {
		createToastContainer();
		
		var $container = $('#woocommerce-toast-container');
		var toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
		
		// Determine icon based on type
		var icon = '';
		switch(type) {
			case 'success':
				icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
				break;
			case 'error':
				icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
				break;
			case 'info':
			default:
				icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 18.3333C14.6024 18.3333 18.3333 14.6024 18.3333 10C18.3333 5.39763 14.6024 1.66667 10 1.66667C5.39763 1.66667 1.66667 5.39763 1.66667 10C1.66667 14.6024 5.39763 18.3333 10 18.3333Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 13.3333V10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 6.66667H10.0083" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
				break;
		}
		
		// Create toast element
		var $toast = $('<div class="woocommerce-toast woocommerce-toast-' + type + '" id="' + toastId + '">' +
			'<div class="woocommerce-toast-icon">' + icon + '</div>' +
			'<div class="woocommerce-toast-message">' + message + '</div>' +
			'<button class="woocommerce-toast-close" aria-label="Close">' +
				'<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">' +
					'<path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
				'</svg>' +
			'</button>' +
		'</div>');
		
		// Append to container
		$container.append($toast);
		
		// Trigger animation
		setTimeout(function() {
			$toast.addClass('show');
		}, 10);
		
		// Auto dismiss after 5 seconds
		var dismissTimer = setTimeout(function() {
			dismissToast($toast);
		}, 5000);
		
		// Close button handler
		$toast.find('.woocommerce-toast-close').on('click', function() {
			clearTimeout(dismissTimer);
			dismissToast($toast);
		});
		
		// Pause timer on hover
		$toast.on('mouseenter', function() {
			clearTimeout(dismissTimer);
		});
		
		// Resume timer on mouse leave
		$toast.on('mouseleave', function() {
			dismissTimer = setTimeout(function() {
				dismissToast($toast);
			}, 2000);
		});
	}

	// Dismiss toast
	function dismissToast($toast) {
		$toast.removeClass('show');
		setTimeout(function() {
			$toast.remove();
		}, 300);
	}

	// Process WooCommerce toast notices on page load
	function processWooCommerceToasts() {
		$('.woocommerce-toast-notice').each(function() {
			var $notice = $(this);
			var message = $notice.data('toast-message') || $notice.text().trim();
			var type = 'info';
			
			if ($notice.hasClass('woocommerce-toast-success')) {
				type = 'success';
			} else if ($notice.hasClass('woocommerce-toast-error')) {
				type = 'error';
			}
			
			// Show toast and remove the hidden notice
			showToast(message, type);
			$notice.remove();
		});
	}

	// Initialize on document ready
	$(document).ready(function() {
		// Process any existing toasts
		processWooCommerceToasts();
		
		// Also process after a short delay to catch dynamically added notices
		setTimeout(processWooCommerceToasts, 500);
	});

	// Expose showToast globally for use in other scripts
	window.showWooCommerceToast = showToast;

})(jQuery);
