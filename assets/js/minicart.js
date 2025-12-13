/**
 * Minicart Functionality
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		var $minicartTrigger = $('#minicart-trigger');
		var $minicartSidebar = $('#minicart-sidebar');
		var $minicartOverlay = $('#minicart-overlay');
		var $minicartClose = $('#minicart-close');
		var $minicartContent = $('#minicart-content');
		var $minicartCount = $('#minicart-count');
		var $body = $('body');

		// State management
		var state = {
			pageReady: false,
			updating: false,
			updateQueue: [],
			quantities: {},
			debounceTimer: null
		};

		// Initialize - mark page as ready after DOM is fully loaded
		setTimeout(function() {
			state.pageReady = true;
			initializeQuantities();
		}, 300);

		// Initialize quantity tracking
		function initializeQuantities() {
			$('.minicart-quantity-input').each(function() {
				var $input = $(this);
				var cartItemKey = $input.data('cart-item-key');
				if (cartItemKey) {
					state.quantities[cartItemKey] = parseInt($input.val()) || 1;
				}
			});
		}

		// Open/close minicart sidebar
		function openMinicart() {
			$minicartSidebar.addClass('active');
			$minicartOverlay.addClass('active');
			$body.addClass('minicart-open');
		}

		function closeMinicart() {
			$minicartSidebar.removeClass('active');
			$minicartOverlay.removeClass('active');
			$body.removeClass('minicart-open');
		}

		// Toggle minicart sidebar
		$minicartTrigger.on('click', function(e) {
			e.preventDefault();
			if ($minicartSidebar.hasClass('active')) {
				closeMinicart();
			} else {
				openMinicart();
			}
		});

		// Close minicart
		$minicartClose.on('click', function(e) {
			e.preventDefault();
			closeMinicart();
		});

		// Close minicart when clicking overlay
		$minicartOverlay.on('click', function(e) {
			e.preventDefault();
			closeMinicart();
		});

		// Close minicart on escape key
		$(document).on('keydown', function(e) {
			if (e.key === 'Escape' && $minicartSidebar.hasClass('active')) {
				closeMinicart();
			}
		});

		// Quantity update function - handles the actual AJAX call
		function updateQuantity(cartItemKey, quantity, $input) {
			// Prevent if already updating or page not ready
			if (!state.pageReady || state.updating) {
				return false;
			}

			// Validate quantity
			quantity = Math.max(1, parseInt(quantity) || 1);

			// Check if quantity actually changed
			if (state.quantities[cartItemKey] === quantity) {
				return false;
			}

			// Store original for rollback
			var originalQty = state.quantities[cartItemKey];
			var $item = $input.closest('.woocommerce-mini-cart-item');
			var $priceCurrent = $item.find('.minicart-item-price-current');
			var $priceOriginal = $item.find('.minicart-item-price-original');
			var $quantityInput = $item.find('.minicart-quantity-input');
			var $saleBadge = $item.find('.minicart-item-sale-badge');
			
			// Store original price text for rollback
			var originalPriceText = $priceCurrent.text();

			// Set updating state
			state.updating = true;
			state.quantities[cartItemKey] = quantity;
			$quantityInput.prop('disabled', true);
			$item.addClass('updating');

			// Make AJAX request
			$.ajax({
				url: basicShopAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'basic_shop_update_cart_quantity',
					nonce: basicShopAjax.nonce,
					cart_item_key: cartItemKey,
					quantity: quantity
				},
				success: function(response) {
					if (response.success) {
						// Update totals without replacing entire cart
						updateCartTotals();
						
						// Update cart count
						if (response.data && response.data.cart_count !== undefined) {
							$minicartCount.text(response.data.cart_count);
						}
						
						// Get updated item price from server
						updateItemPrice(cartItemKey, $item);
					} else {
						// Rollback on error
						state.quantities[cartItemKey] = originalQty;
						$quantityInput.val(originalQty);
						$priceCurrent.text(originalPriceText);
						if (response.data && response.data.message) {
							alert(response.data.message);
						}
					}
				},
				error: function() {
					// Rollback on error
					state.quantities[cartItemKey] = originalQty;
					$quantityInput.val(originalQty);
					$priceCurrent.text(originalPriceText);
				},
				complete: function() {
					$quantityInput.prop('disabled', false);
					$item.removeClass('updating');
					state.updating = false;
					
					// Process queue if any
					processUpdateQueue();
				}
			});

			return true;
		}

		// Update individual item price (without replacing entire item)
		function updateItemPrice(cartItemKey, $item) {
			$.ajax({
				url: basicShopAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'basic_shop_get_cart_item_price',
					nonce: basicShopAjax.nonce,
					cart_item_key: cartItemKey
				},
				success: function(response) {
					if (response.success && response.data) {
						// Update price display
						if (response.data.price_current) {
							$item.find('.minicart-item-price-current').html(response.data.price_current);
						}
						if (response.data.price_original) {
							var $priceOriginal = $item.find('.minicart-item-price-original');
							if (response.data.price_original) {
								if ($priceOriginal.length) {
									$priceOriginal.html(response.data.price_original);
								} else {
									$item.find('.minicart-item-price-wrapper').prepend('<span class="minicart-item-price-original">' + response.data.price_original + '</span>');
								}
							} else {
								$priceOriginal.remove();
							}
						}
						if (response.data.sale_badge) {
							var $saleBadge = $item.find('.minicart-item-sale-badge');
							if (response.data.sale_badge) {
								if ($saleBadge.length) {
									$saleBadge.html(response.data.sale_badge);
								} else {
									$item.find('.minicart-item-variation').after('<div class="minicart-item-sale-badge">' + response.data.sale_badge + '</div>');
								}
							} else {
								$saleBadge.remove();
							}
						}
					}
				}
			});
		}

		// Update only cart totals (not entire cart HTML)
		function updateCartTotals() {
			$.ajax({
				url: basicShopAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'basic_shop_get_cart_totals',
					nonce: basicShopAjax.nonce
				},
				success: function(response) {
					if (response.success && response.data && response.data.total_html) {
						// Find and replace the actions wrapper
						var $actionsWrapper = $minicartContent.find('.minicart-actions-wrapper');
						
						if ($actionsWrapper.length) {
							// Replace the entire actions wrapper
							$actionsWrapper.replaceWith(response.data.total_html);
						} else {
							// If wrapper doesn't exist, append it after the scrollable content
							var $scrollableContent = $minicartContent.find('.minicart-scrollable-content');
							if ($scrollableContent.length) {
								$scrollableContent.after(response.data.total_html);
							}
						}
						
						// Update cart count
						if (response.data.cart_count !== undefined) {
							$minicartCount.text(response.data.cart_count);
						}
					}
				}
			});
		}

		// Process update queue
		function processUpdateQueue() {
			if (state.updateQueue.length > 0 && !state.updating) {
				var item = state.updateQueue.shift();
				updateQuantity(item.key, item.quantity, item.$input);
			}
		}

		// Quantity controls - Plus button
		$(document).on('click', '.minicart-quantity-btn.plus', function(e) {
			e.preventDefault();
			
			if (!state.pageReady || state.updating) {
				return false;
			}

			var $btn = $(this);
			var $input = $btn.siblings('.minicart-quantity-input');
			var cartItemKey = $input.data('cart-item-key');
			
			if (!cartItemKey) {
				return false;
			}

			var currentVal = parseInt($input.val()) || 1;
			var newVal = currentVal + 1;
			
			// Update input value immediately for better UX
			$input.val(newVal);
			
			// Update quantity
			if (!updateQuantity(cartItemKey, newVal, $input)) {
				// If update failed, queue it
				state.updateQueue.push({
					key: cartItemKey,
					quantity: newVal,
					$input: $input
				});
			}
			
			return false;
		});

		// Quantity controls - Minus button
		$(document).on('click', '.minicart-quantity-btn.minus', function(e) {
			e.preventDefault();
			
			if (!state.pageReady || state.updating) {
				return false;
			}

			var $btn = $(this);
			var $input = $btn.siblings('.minicart-quantity-input');
			var cartItemKey = $input.data('cart-item-key');
			
			if (!cartItemKey) {
				return false;
			}

			var currentVal = parseInt($input.val()) || 1;
			
			if (currentVal > 1) {
				var newVal = currentVal - 1;
				
				// Update input value immediately for better UX
				$input.val(newVal);
				
				// Update quantity
				if (!updateQuantity(cartItemKey, newVal, $input)) {
					// If update failed, queue it
					state.updateQueue.push({
						key: cartItemKey,
						quantity: newVal,
						$input: $input
					});
				}
			}
			
			return false;
		});

		// Update quantity on input change (with debouncing)
		$(document).on('input change', '.minicart-quantity-input', function(e) {
			if (!state.pageReady) {
				return false;
			}

			var $input = $(this);
			var cartItemKey = $input.data('cart-item-key');
			
			if (!cartItemKey) {
				return false;
			}

			var quantity = parseInt($input.val()) || 1;
			
			// Ensure minimum quantity
			if (quantity < 1) {
				quantity = 1;
				$input.val(quantity);
			}

			// Clear existing debounce timer
			clearTimeout(state.debounceTimer);

			// Debounce the update to prevent rapid fire
			state.debounceTimer = setTimeout(function() {
				if (!updateQuantity(cartItemKey, quantity, $input)) {
					// If update failed, queue it
					state.updateQueue.push({
						key: cartItemKey,
						quantity: quantity,
						$input: $input
					});
				}
			}, 500); // 500ms debounce
		});

		// Remove item from minicart - use more specific selector
		$(document).on('click', '.minicart-content .minicart-remove-item, .minicart-content .remove', function(e) {
			// Prevent all default behavior and stop propagation
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			
			if (state.updating) {
				return false;
			}

			var $btn = $(this);
			var cartItemKey = $btn.data('cart-item-key');
			
			if (!cartItemKey) {
				return false;
			}

			state.updating = true;
			var $item = $btn.closest('.woocommerce-mini-cart-item');
			$item.addClass('removing');

			$.ajax({
				url: basicShopAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'basic_shop_remove_cart_item',
					nonce: basicShopAjax.nonce,
					cart_item_key: cartItemKey
				},
				success: function(response) {
					if (response.success) {
						// Update cart count immediately
						if (response.data && response.data.cart_count !== undefined) {
							$minicartCount.text(response.data.cart_count);
						}
						
						// Update totals immediately
						updateCartTotals();
						
						// Remove item with fade out
						$item.fadeOut(200, function() {
							$(this).remove();
							
							// Check if cart is now empty
							var remainingItems = $minicartContent.find('.woocommerce-mini-cart-item').length;
							if (remainingItems === 0) {
								// Refresh entire minicart to show empty state
								refreshMinicart();
							} else {
								// Re-initialize quantities for remaining items
								initializeQuantities();
							}
						});
					} else {
						// Show error and restore item
						$item.removeClass('removing');
						if (response.data && response.data.message) {
							alert(response.data.message);
						}
						state.updating = false;
					}
				},
				error: function() {
					$item.removeClass('removing');
					state.updating = false;
					alert('Failed to remove item. Please try again.');
				},
				complete: function() {
					// Only set updating to false if not successful (success handler handles it)
					if (!state.updating) {
						// Already handled in error/success
					}
				}
			});

			return false;
		});
		
		// Prevent WooCommerce's default remove handler from firing (if it still exists)
		$(document).on('click', '.minicart-content .remove_from_cart_button', function(e) {
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			return false;
		});

		// Refresh entire minicart (used after remove or add)
		function refreshMinicart() {
			if (state.updating) {
				return;
			}

			state.updating = true;

			$.ajax({
				url: basicShopAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'basic_shop_update_minicart',
					nonce: basicShopAjax.nonce
				},
				success: function(response) {
					if (response.fragments && response.fragments['#minicart-content']) {
						// Fade out, update, fade in to prevent flickering
						$minicartContent.fadeOut(100, function() {
							$(this).html(response.fragments['#minicart-content']).fadeIn(100);
							initializeQuantities();
						});
					}

					// Update cart count
					if (response.cart_count !== undefined) {
						$minicartCount.text(response.cart_count);
					}
				},
				complete: function() {
					state.updating = false;
				}
			});
		}

		// Update minicart when cart is updated via WooCommerce
		$(document.body).on('added_to_cart', function(event, fragments, cart_hash) {
			if (!$minicartSidebar.hasClass('active')) {
				openMinicart();
			}
			refreshMinicart();
			
			if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.cart_count) {
				$minicartCount.text(wc_add_to_cart_params.cart_count);
			}
		});

		// Handle WooCommerce cart fragments (only if not already updating)
		$(document.body).on('wc_fragment_refresh wc_fragments_refreshed', function(event, fragments) {
			if (!state.pageReady || state.updating) {
				return;
			}
			
			// Only refresh if cart hash changed significantly
			if (fragments && fragments['div.widget_shopping_cart_content']) {
				refreshMinicart();
			}
		});

		// Update cart count on page load
		if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.cart_count) {
			$minicartCount.text(wc_add_to_cart_params.cart_count);
		}
	});

})(jQuery);
