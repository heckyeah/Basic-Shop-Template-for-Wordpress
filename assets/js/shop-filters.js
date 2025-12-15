/**
 * Shop Filters and Search JavaScript
 *
 * @package Basic_Shop_Theme
 */

(function($) {
	'use strict';

	var ShopFilters = {
		init: function() {
			this.bindEvents();
			this.initPriceSlider();
			this.loadInitialFilters();
		},

		bindEvents: function() {
			var self = this;

			// Search form submission
			$('.shop-search-form').on('submit', function(e) {
				e.preventDefault();
				self.performSearch();
			});

			// Filter form submission (only when Apply Filters button is clicked)
			$('#shop-filters-form').on('submit', function(e) {
				e.preventDefault();
				self.applyFilters();
			});

			// Remove auto-apply on change - filters only apply when "Apply Filters" button is clicked

			// Clear filters button
			$('#shop-filter-clear').on('click', function(e) {
				e.preventDefault();
				self.clearFilters();
			});

			// Mobile filter toggle
			$('#shop-filters-toggle').on('click', function() {
				self.openMobileFilters();
			});

			// Close mobile filters
			$('#shop-filters-close, #shop-filters-overlay').on('click', function() {
				self.closeMobileFilters();
			});

			// Pagination links (handle AJAX)
			$(document).on('click', '.woocommerce-pagination a', function(e) {
				if ($(this).attr('href').indexOf('?') !== -1) {
					e.preventDefault();
					var page = self.getPageFromUrl($(this).attr('href'));
					self.applyFilters(page);
				}
			});

			// Price slider sync with inputs
			$('.shop-filter-price-slider').on('input', function() {
				var $slider = $(this);
				var isMin = $slider.hasClass('shop-filter-price-slider-min');
				var $minInput = $('input[name="filter_min_price"]');
				var $maxInput = $('input[name="filter_max_price"]');
				var $minSlider = $('.shop-filter-price-slider-min');
				var $maxSlider = $('.shop-filter-price-slider-max');
				
				// Round to whole dollar (no cents)
				var roundedValue = Math.round(parseFloat($slider.val()) || 0);
				
				if (isMin) {
					// Min slider changed
					var maxValue = parseFloat($maxSlider.val()) || parseFloat($maxSlider.attr('max'));
					if (roundedValue > maxValue) {
						// Min can't be higher than max, set it to max
						roundedValue = maxValue;
						$slider.val(roundedValue);
					}
					$minInput.val(roundedValue);
				} else {
					// Max slider changed
					var minValue = parseFloat($minSlider.val()) || parseFloat($minSlider.attr('min'));
					if (roundedValue < minValue) {
						// Max can't be lower than min, set it to min
						roundedValue = minValue;
						$slider.val(roundedValue);
					}
					$maxInput.val(roundedValue);
				}
			});

			// Price input sync with slider and validate
			$('.shop-filter-price-input').on('input', function() {
				var $input = $(this);
				var isMin = $input.attr('name') === 'filter_min_price';
				var $minInput = $('input[name="filter_min_price"]');
				var $maxInput = $('input[name="filter_max_price"]');
				var $minSlider = $('.shop-filter-price-slider-min');
				var $maxSlider = $('.shop-filter-price-slider-max');
				
				// Round to whole dollar (no cents)
				var roundedValue = Math.round(parseFloat($input.val()) || 0);
				
				if (isMin) {
					// Min input changed
					var maxValue = parseFloat($maxInput.val()) || parseFloat($maxSlider.attr('max'));
					if (roundedValue > maxValue && $maxInput.val() !== '') {
						// Min can't be higher than max, set it to max
						roundedValue = maxValue;
						$input.val(roundedValue);
					}
					$minSlider.val(roundedValue);
				} else {
					// Max input changed
					var minValue = parseFloat($minInput.val()) || parseFloat($minSlider.attr('min'));
					if (roundedValue < minValue && $minInput.val() !== '') {
						// Max can't be lower than min, set it to min
						roundedValue = minValue;
						$input.val(roundedValue);
					}
					$maxSlider.val(roundedValue);
				}
			});
			
			// Validate on blur (when user leaves the input field)
			$('.shop-filter-price-input').on('blur', function() {
				var $input = $(this);
				var isMin = $input.attr('name') === 'filter_min_price';
				var $minInput = $('input[name="filter_min_price"]');
				var $maxInput = $('input[name="filter_max_price"]');
				var $minSlider = $('.shop-filter-price-slider-min');
				var $maxSlider = $('.shop-filter-price-slider-max');
				
				var inputValue = parseFloat($input.val());
				if (isNaN(inputValue) || inputValue === '') {
					return; // Allow empty values
				}
				
				var roundedValue = Math.round(inputValue);
				
				if (isMin) {
					// Min input blurred
					var maxValue = parseFloat($maxInput.val());
					if (!isNaN(maxValue) && roundedValue > maxValue) {
						// Min can't be higher than max, set it to max
						roundedValue = maxValue;
						$input.val(roundedValue);
						$minSlider.val(roundedValue);
					} else {
						$input.val(roundedValue);
						$minSlider.val(roundedValue);
					}
				} else {
					// Max input blurred
					var minValue = parseFloat($minInput.val());
					if (!isNaN(minValue) && roundedValue < minValue) {
						// Max can't be lower than min, set it to min
						roundedValue = minValue;
						$input.val(roundedValue);
						$maxSlider.val(roundedValue);
					} else {
						$input.val(roundedValue);
						$maxSlider.val(roundedValue);
					}
				}
			});

			// Handle filter tag removal (event delegation for dynamically added tags)
			$(document).on('click', '.shop-active-filter-tag', function(e) {
				e.preventDefault();
				var $tag = $(this);
				var filterType = $tag.data('filter-type');
				self.removeFilter(filterType, $tag);
			});
			
			// Handle color swatch clicks (radio buttons - single selection)
			$('.shop-filter-color-swatch').on('click', function(e) {
				e.preventDefault();
				var $swatch = $(this);
				var $radio = $swatch.siblings('.shop-filter-color-radio');
				
				// Uncheck all other color radios in the same group
				var radioName = $radio.attr('name');
				$('input[name="' + radioName + '"]').prop('checked', false);
				$('input[name="' + radioName + '"]').siblings('.shop-filter-color-swatch').removeClass('selected');
				
				// Check this radio and update visual state
				$radio.prop('checked', true);
				$swatch.addClass('selected');
			});
			
			// Sync radio state with swatch visual state
			$('.shop-filter-color-radio').on('change', function() {
				var $radio = $(this);
				var radioName = $radio.attr('name');
				var $swatch = $radio.siblings('.shop-filter-color-swatch');
				
				// Remove selected class from all swatches in this group
				$('input[name="' + radioName + '"]').siblings('.shop-filter-color-swatch').removeClass('selected');
				
				// Add selected class to the checked one
				if ($radio.is(':checked')) {
					$swatch.addClass('selected');
				}
			});
		},

		initPriceSlider: function() {
			// Price slider is already initialized via HTML5 range inputs
			// This function can be extended for custom slider implementation
		},

		loadInitialFilters: function() {
			// If URL has filter parameters, apply them on page load
			var urlParams = new URLSearchParams(window.location.search);
			if (urlParams.has('filter_category') || urlParams.has('filter_min_price') || 
				urlParams.has('filter_max_price') || urlParams.has('filter_stock') ||
				urlParams.has('filter_sale')) {
				this.applyFilters();
			}
		},

		performSearch: function() {
			var searchTerm = $('#shop-search-field').val();
			this.applyFilters(1, searchTerm);
		},

		applyFilters: function(page, searchTerm) {
			var self = this;
			page = page || 1;
			searchTerm = searchTerm || $('#shop-search-field').val() || '';

			// Show loading state
			this.showLoading();

			// Collect filter data
			var minPrice = $('input[name="filter_min_price"]').val();
			var maxPrice = $('input[name="filter_max_price"]').val();
			// Round price values to whole dollars (no cents)
			minPrice = minPrice ? Math.round(parseFloat(minPrice)) : '';
			maxPrice = maxPrice ? Math.round(parseFloat(maxPrice)) : '';
			
			// Validate: max price cannot be lower than min price
			if (minPrice && maxPrice && parseFloat(maxPrice) < parseFloat(minPrice)) {
				// If max is lower than min, set max to min
				maxPrice = minPrice;
				$('input[name="filter_max_price"]').val(maxPrice);
				$('.shop-filter-price-slider-max').val(maxPrice);
			}
			
			var filterData = {
				action: 'basic_shop_filter_products',
				nonce: basicShopFilters.nonce,
				search: searchTerm,
				categories: this.getSelectedCategories(),
				min_price: minPrice,
				max_price: maxPrice,
				stock_status: $('input[name="filter_stock"]:checked').val() || '',
				on_sale: $('input[name="filter_sale"]:checked').length > 0 ? '1' : '',
				page: page,
				attributes: this.getSelectedAttributes(),
			};

			// AJAX request
			$.ajax({
				url: basicShopFilters.ajaxurl,
				type: 'POST',
				data: filterData,
				success: function(response) {
					if (response.success) {
						self.updateProducts(response.data.products);
						self.updatePagination(response.data.pagination);
						self.updateResultCount(response.data.result_count);
						self.updateActiveFilters(response.data.active_filters || []);
						self.updateURL(filterData);
						self.closeMobileFilters();
					} else {
						self.showError('Failed to load products. Please try again.');
					}
					self.hideLoading();
				},
				error: function() {
					self.showError('An error occurred. Please try again.');
					self.hideLoading();
				}
			});
		},

		getSelectedCategories: function() {
			var categories = [];
			$('input[name="filter_category[]"]:checked').each(function() {
				categories.push($(this).val());
			});
			return categories;
		},

		getSelectedAttributes: function() {
			var attributes = {};
			
			// Handle checkboxes (multiple selection attributes)
			$('#shop-filters-form input[type="checkbox"][name^="filter_"]').each(function() {
				var name = $(this).attr('name');
				if (name.indexOf('filter_category') === -1 && name.indexOf('filter_sale') === -1) {
					var attrName = name.replace('filter_', '').replace('[]', '');
					var taxonomy = 'pa_' + attrName;
					if (!attributes[taxonomy]) {
						attributes[taxonomy] = [];
					}
					if ($(this).is(':checked')) {
						attributes[taxonomy].push($(this).val());
					}
				}
			});
			
			// Handle radio buttons (single selection - color attribute)
			$('#shop-filters-form input[type="radio"][name^="filter_"]').each(function() {
				var name = $(this).attr('name');
				if (name.indexOf('filter_category') === -1 && name.indexOf('filter_sale') === -1 && name.indexOf('filter_stock') === -1) {
					var attrName = name.replace('filter_', '');
					var taxonomy = 'pa_' + attrName;
					if ($(this).is(':checked')) {
						// Radio buttons are single selection, so use array with one value
						attributes[taxonomy] = [$(this).val()];
					}
				}
			});
			
			return attributes;
		},

		updateProducts: function(html) {
			$('#shop-products-grid-wrapper').html(html);
			
			// Trigger custom event for thumbnail slider initialization
			$(document).trigger('shopProductsUpdated');
			
			// Scroll to top of products
			$('html, body').animate({
				scrollTop: $('.shop-products-wrapper').offset().top - 100
			}, 300);
		},

		updatePagination: function(html) {
			$('#shop-products-pagination-wrapper').html(html);
		},

		updateResultCount: function(html) {
			$('.woocommerce-result-count').replaceWith(html);
		},

		updateActiveFilters: function(activeFilters) {
			var self = this;
			var $container = $('#shop-active-filters');
			$container.empty();
			
			if (!activeFilters || activeFilters.length === 0) {
				return;
			}
			
			activeFilters.forEach(function(filter) {
				var $tag = $('<button>', {
					class: 'shop-active-filter-tag',
					'data-filter-type': filter.type,
					'data-filter-value': filter.value || '',
					'data-filter-taxonomy': filter.taxonomy || '',
					'data-filter-attr-name': filter.attr_name || '',
					'data-filter-min-price': filter.min_price || '',
					'data-filter-max-price': filter.max_price || '',
				});
				
				// Decode HTML entities in the label
				var labelText = $('<div>').html( filter.label ).text();
				var $label = $('<span>', {
					class: 'shop-active-filter-label',
					text: labelText
				});
				
				// Add vertical separator
				var $separator = $('<span>', {
					class: 'shop-active-filter-separator'
				}).text('|');
				
				var $close = $('<span>', {
					class: 'shop-active-filter-close'
				}).html('<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 3L3 9M3 3L9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>');
				
			$tag.append($label).append($separator).append($close);
			$container.append($tag);
		});
	},

		removeFilter: function(filterType, $tag) {
			var self = this;
			
			// Remove the filter from form
			switch(filterType) {
				case 'search':
					$('#shop-search-field').val('');
					break;
				case 'category':
					var catId = $tag.data('filter-value');
					$('input[name="filter_category[]"][value="' + catId + '"]').prop('checked', false);
					break;
				case 'price':
					$('input[name="filter_min_price"]').val('');
					$('input[name="filter_max_price"]').val('');
					$('.shop-filter-price-slider-min').val($('.shop-filter-price-slider-min').attr('min'));
					$('.shop-filter-price-slider-max').val($('.shop-filter-price-slider-max').attr('max'));
					break;
				case 'stock':
					$('input[name="filter_stock"]').prop('checked', false);
					$('input[name="filter_stock"][value=""]').prop('checked', true);
					break;
				case 'sale':
					$('input[name="filter_sale"]').prop('checked', false);
					break;
				case 'attribute':
					var termSlug = $tag.data('filter-value');
					var attrName = $tag.data('filter-attr-name');
					// Check if it's a radio (color) or checkbox (other attributes)
					var $input = $('input[name="filter_' + attrName + '"][value="' + termSlug + '"]');
					if ($input.length === 0) {
						// Try checkbox format
						$input = $('input[name="filter_' + attrName + '[]"][value="' + termSlug + '"]');
					}
					$input.prop('checked', false);
					// If it's a color filter, update swatch visual state
					if ($input.hasClass('shop-filter-color-radio')) {
						$input.siblings('.shop-filter-color-swatch').removeClass('selected');
					}
					break;
			}
			
			// Apply filters without the removed one
			self.applyFilters();
		},

		updateURL: function(filterData) {
			var url = new URL(basicShopFilters.shopUrl);
			
			// Add search
			if (filterData.search) {
				url.searchParams.set('s', filterData.search);
				url.searchParams.set('post_type', 'product');
			} else {
				url.searchParams.delete('s');
				url.searchParams.delete('post_type');
			}

			// Add categories
			if (filterData.categories && filterData.categories.length > 0) {
				url.searchParams.set('filter_category', filterData.categories.join(','));
			} else {
				url.searchParams.delete('filter_category');
			}

			// Add price
			if (filterData.min_price) {
				url.searchParams.set('filter_min_price', filterData.min_price);
			} else {
				url.searchParams.delete('filter_min_price');
			}
			if (filterData.max_price) {
				url.searchParams.set('filter_max_price', filterData.max_price);
			} else {
				url.searchParams.delete('filter_max_price');
			}

			// Add stock
			if (filterData.stock_status) {
				url.searchParams.set('filter_stock', filterData.stock_status);
			} else {
				url.searchParams.delete('filter_stock');
			}

			// Add sale
			if (filterData.on_sale) {
				url.searchParams.set('filter_sale', '1');
			} else {
				url.searchParams.delete('filter_sale');
			}


			// Add attributes
			for (var attr in filterData.attributes) {
				if (filterData.attributes[attr].length > 0) {
					var attrName = attr.replace('pa_', '');
					// Check if this is a color attribute (single selection)
					var attrNameLower = attrName.toLowerCase();
					var isColorAttr = (attrNameLower.indexOf('color') !== -1 || attrNameLower.indexOf('colour') !== -1);
					
					if (isColorAttr && filterData.attributes[attr].length === 1) {
						// Color attribute: single value (radio button)
						url.searchParams.set('filter_' + attrName, filterData.attributes[attr][0]);
					} else {
						// Other attributes: multiple values (checkboxes)
						url.searchParams.set('filter_' + attrName, filterData.attributes[attr].join(','));
					}
				} else {
					var attrName = attr.replace('pa_', '');
					url.searchParams.delete('filter_' + attrName);
				}
			}

			// Update URL without page reload
			window.history.pushState({}, '', url.toString());
		},

		clearFilters: function() {
			// Clear all form inputs
			$('#shop-filters-form')[0].reset();
			$('#shop-search-field').val('');
			
			// Reset price sliders
			var priceRange = this.getPriceRange();
			$('.shop-filter-price-slider-min').val(priceRange.min);
			$('.shop-filter-price-slider-max').val(priceRange.max);
			$('input[name="filter_min_price"]').val('');
			$('input[name="filter_max_price"]').val('');
			
			// Clear color swatch visual states and uncheck radios
			$('.shop-filter-color-radio').prop('checked', false);
			$('.shop-filter-color-swatch').removeClass('selected');

			// Apply empty filters
			this.applyFilters(1, '');
		},

		getPriceRange: function() {
			// Get min/max from sliders
			var minSlider = $('.shop-filter-price-slider-min');
			var maxSlider = $('.shop-filter-price-slider-max');
			return {
				min: minSlider.attr('min') || 0,
				max: maxSlider.attr('max') || 1000
			};
		},

		openMobileFilters: function() {
			$('#shop-filters-sidebar').addClass('active');
			$('#shop-filters-overlay').addClass('active');
			$('body').addClass('filters-open');
		},

		closeMobileFilters: function() {
			$('#shop-filters-sidebar').removeClass('active');
			$('#shop-filters-overlay').removeClass('active');
			$('body').removeClass('filters-open');
		},

		showLoading: function() {
			$('#shop-products-loading').show();
			$('#shop-products-grid-wrapper').css('opacity', '0.5');
		},

		hideLoading: function() {
			$('#shop-products-loading').hide();
			$('#shop-products-grid-wrapper').css('opacity', '1');
		},

		showError: function(message) {
			// You can implement a toast notification here
			console.error(message);
		},

		getPageFromUrl: function(url) {
			var urlObj = new URL(url);
			var page = urlObj.searchParams.get('paged') || urlObj.searchParams.get('page') || 1;
			return parseInt(page, 10);
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		if ($('.shop-page-wrapper').length) {
			ShopFilters.init();
		}
	});

})(jQuery);

