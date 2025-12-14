/**
 * Product Size and Color Selectors
 * Handles button-style size and color selection on single product pages
 * Also handles variation selectors for variable products
 *
 * @package Basic_Shop_Theme
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Handle size button clicks (product attributes)
		$(document).on('click', '.product-size-button', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var $selector = $button.closest('.product-size-selector');
			
			// Remove selected class from all buttons in this selector
			$selector.find('.product-size-button').removeClass('selected');
			
			// Add selected class to clicked button
			$button.addClass('selected');
			
			// Update the label to show selected size
			var selectedSize = $button.data('size-value');
			var $label = $selector.siblings('.product-size-label');
			if ($label.length) {
				var labelText = $label.text().split(':')[0]; // Get label without current size
				$label.text(labelText + ': ' + selectedSize.toUpperCase());
			}
		});
		
		// Handle color button clicks (product attributes)
		$(document).on('click', '.product-color-button', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var $selector = $button.closest('.product-color-selector');
			
			// Remove selected class from all buttons in this selector
			$selector.find('.product-color-button').removeClass('selected');
			
			// Add selected class to clicked button
			$button.addClass('selected');
			
			// Update the label to show selected color
			var selectedColor = $button.data('color-value');
			var $label = $selector.siblings('.product-color-label');
			if ($label.length) {
				var labelText = $label.text().split(':')[0]; // Get label without current color
				$label.text(labelText + ': ' + selectedColor.toUpperCase());
			}
		});
		
		// Handle variation size button clicks
		$(document).on('click', '.variation-size-button', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var $selector = $button.closest('.variation-size-selector');
			var $variationItem = $selector.closest('.variation-item');
			var $form = $selector.closest('.variations_form');
			var attributeName = $selector.data('attribute');
			var selectedValue = $button.data('value');
			var selectedLabel = $button.data('label');
			
			// Remove selected class from all buttons in this selector
			$selector.find('.variation-size-button').removeClass('selected');
			
			// Add selected class to clicked button
			$button.addClass('selected');
			
			// Update the hidden select field - find it in the .variations table by attribute name
			var $hiddenSelect = $form.find('.variations select[name="' + attributeName + '"]');
			if ($hiddenSelect.length) {
				$hiddenSelect.val(selectedValue).trigger('change');
			}
			
			// Update the label
			var $label = $variationItem.find('.variation-size-label');
			if ($label.length) {
				var $span = $label.find('.selected-size-value');
				$span.text(selectedLabel.toUpperCase());
			}
		});
		
		// Handle variation color button clicks
		$(document).on('click', '.variation-color-button', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var $selector = $button.closest('.variation-color-selector');
			var $variationItem = $selector.closest('.variation-item');
			var $form = $selector.closest('.variations_form');
			var attributeName = $selector.data('attribute');
			var selectedValue = $button.data('value');
			var selectedLabel = $button.data('label');
			
			// Remove selected class from all buttons in this selector
			$selector.find('.variation-color-button').removeClass('selected');
			
			// Add selected class to clicked button
			$button.addClass('selected');
			
			// Update the hidden select field - find it in the .variations table by attribute name
			var $hiddenSelect = $form.find('.variations select[name="' + attributeName + '"]');
			if ($hiddenSelect.length) {
				$hiddenSelect.val(selectedValue).trigger('change');
			}
			
			// Update the label
			var $label = $variationItem.find('.variation-color-label');
			if ($label.length) {
				var $span = $label.find('.selected-color-value');
				$span.text(selectedLabel.toUpperCase());
			}
		});
		
		// Handle variation dropdown changes (sync with hidden select)
		$(document).on('change', '.variation-dropdown', function() {
			var $dropdown = $(this);
			var $form = $dropdown.closest('.variations_form');
			var attributeName = $dropdown.data('attribute');
			var selectedValue = $dropdown.val();
			
			// Update the hidden select field - find it in the .variations table by attribute name
			var $hiddenSelect = $form.find('.variations select[name="' + attributeName + '"]');
			if ($hiddenSelect.length) {
				$hiddenSelect.val(selectedValue).trigger('change');
			}
		});
		
		// Initialize selected values on page load
		$('.variations_form').each(function() {
			var $form = $(this);
			
			// Set initial selected size
			$form.find('.variation-size-selector').each(function() {
				var $selector = $(this);
				var $variationItem = $selector.closest('.variation-item');
				var attributeName = $selector.data('attribute');
				var $hiddenSelect = $form.find('.variations select[name="' + attributeName + '"]');
				
				if ($hiddenSelect.length) {
					var selectedValue = $hiddenSelect.val();
					
					// Check if a button is already marked as selected
					var $selectedButton = $selector.find('.variation-size-button.selected');
					if ($selectedButton.length && !selectedValue) {
						selectedValue = $selectedButton.data('value');
						$hiddenSelect.val(selectedValue).trigger('change');
					} else if (!selectedValue) {
						// No value selected, select the first button
						var $firstButton = $selector.find('.variation-size-button').first();
						if ($firstButton.length) {
							selectedValue = $firstButton.data('value');
							$hiddenSelect.val(selectedValue).trigger('change');
							$firstButton.addClass('selected');
						}
					} else {
						// Hidden select has a value, sync the button
						var $button = $selector.find('.variation-size-button[data-value="' + selectedValue + '"]');
						if ($button.length) {
							$button.addClass('selected');
							// Update label
							var selectedLabel = $button.data('label');
							var $label = $variationItem.find('.variation-size-label');
							if ($label.length) {
								var $span = $label.find('.selected-size-value');
								$span.text(selectedLabel ? selectedLabel.toUpperCase() : '');
							}
						}
					}
				}
			});
			
			// Set initial selected color
			$form.find('.variation-color-selector').each(function() {
				var $selector = $(this);
				var $variationItem = $selector.closest('.variation-item');
				var attributeName = $selector.data('attribute');
				var $hiddenSelect = $form.find('.variations select[name="' + attributeName + '"]');
				
				if ($hiddenSelect.length) {
					var selectedValue = $hiddenSelect.val();
					
					// Check if a button is already marked as selected
					var $selectedButton = $selector.find('.variation-color-button.selected');
					if ($selectedButton.length && !selectedValue) {
						selectedValue = $selectedButton.data('value');
						$hiddenSelect.val(selectedValue).trigger('change');
					} else if (!selectedValue) {
						// No value selected, select the first button
						var $firstButton = $selector.find('.variation-color-button').first();
						if ($firstButton.length) {
							selectedValue = $firstButton.data('value');
							$hiddenSelect.val(selectedValue).trigger('change');
							$firstButton.addClass('selected');
						}
					} else {
						// Hidden select has a value, sync the button
						var $button = $selector.find('.variation-color-button[data-value="' + selectedValue + '"]');
						if ($button.length) {
							$button.addClass('selected');
							// Update label
							var selectedLabel = $button.data('label');
							var $label = $variationItem.find('.variation-color-label');
							if ($label.length) {
								var $span = $label.find('.selected-color-value');
								$span.text(selectedLabel ? selectedLabel.toUpperCase() : '');
							}
						}
					}
				}
			});
			
			// Sync dropdown with hidden select on page load
			$form.find('.variation-dropdown').each(function() {
				var $dropdown = $(this);
				var attributeName = $dropdown.data('attribute');
				var $hiddenSelect = $form.find('.variations select[name="' + attributeName + '"]');
				
				if ($hiddenSelect.length) {
					var selectedValue = $hiddenSelect.val();
					if (selectedValue) {
						$dropdown.val(selectedValue);
					} else {
						// Sync dropdown value to hidden select
						var dropdownValue = $dropdown.val();
						if (dropdownValue) {
							$hiddenSelect.val(dropdownValue).trigger('change');
						}
					}
				}
			});
		});
	});

})(jQuery);

