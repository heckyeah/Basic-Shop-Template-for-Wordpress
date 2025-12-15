<?php
/**
 * Product thumbnail slider for shop loop
 *
 * @package Basic_Shop_Theme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) ) {
	return;
}

// Get product images
$attachment_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();

// Get variation images and color mapping if product is variable
$variation_image_ids = array();
$color_variation_map = array(); // Maps color value to image ID
$color_swatches = array(); // Color swatch data
$color_attribute = null; // Initialize for scope
$color_attribute_name = null; // Initialize for scope
$color_options = array(); // Initialize for scope

if ( $product->is_type( 'variable' ) ) {
	$attributes = $product->get_attributes();
	
	// Find color attribute
	foreach ( $attributes as $attribute_name => $attribute ) {
		$attribute_id = sanitize_title( $attribute_name );
		$attribute_name_lower = strtolower( $attribute_name );
		$attribute_id_lower = strtolower( $attribute_id );
		
		// Get attribute label for additional checking
		$attribute_label = '';
		if ( $attribute->is_taxonomy() ) {
			$taxonomy_obj = get_taxonomy( $attribute_name );
			if ( $taxonomy_obj ) {
				$attribute_label = strtolower( $taxonomy_obj->label );
			}
		}
		
		// Check multiple ways: name, label, and ID (case-insensitive)
		$is_color = ( 
			strpos( $attribute_name_lower, 'color' ) !== false || 
			strpos( $attribute_name_lower, 'colour' ) !== false || 
			strpos( $attribute_id_lower, 'color' ) !== false || 
			strpos( $attribute_id_lower, 'colour' ) !== false ||
			( ! empty( $attribute_label ) && ( strpos( $attribute_label, 'color' ) !== false || strpos( $attribute_label, 'colour' ) !== false ) )
		);
		
		if ( $is_color && $attribute->get_variation() ) {
			$color_attribute = $attribute;
			$color_attribute_name = $attribute_name;
			break;
		}
	}
	
	// Get variations and map colors to images
	if ( $color_attribute ) {
		// Debug output (remove after testing)
		// error_log( 'Color attribute found for product ' . $product->get_id() . ': ' . $color_attribute_name . ' (taxonomy: ' . ( $color_attribute->is_taxonomy() ? 'yes' : 'no' ) . ')' );
		
		$variations = $product->get_children();
		
		// Get color options (reset array)
		$color_options = array();
		if ( $color_attribute->is_taxonomy() ) {
			// For taxonomy-based attributes (global attributes)
			// Get terms directly from the attribute options (these are the terms assigned to this product)
			$attribute_options = $color_attribute->get_options();
			
			// If attribute options are term IDs, get the terms
			if ( ! empty( $attribute_options ) ) {
				foreach ( $attribute_options as $option ) {
					// Option could be term ID (int) or term slug (string)
					if ( is_numeric( $option ) ) {
						// It's a term ID
						$term = get_term( $option, $color_attribute_name );
						if ( $term && ! is_wp_error( $term ) ) {
							$color_options[] = array(
								'value' => $term->slug,
								'label' => $term->name,
							);
						}
					} else {
						// It's a term slug
						$term = get_term_by( 'slug', $option, $color_attribute_name );
						if ( $term && ! is_wp_error( $term ) ) {
							$color_options[] = array(
								'value' => $term->slug,
								'label' => $term->name,
							);
						}
					}
				}
			}
			
			// Fallback: If no options from attribute, try getting all terms assigned to product
			if ( empty( $color_options ) ) {
				$terms = wc_get_product_terms( $product->get_id(), $color_attribute_name, array( 'fields' => 'all' ) );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $term ) {
						$color_options[] = array(
							'value' => $term->slug,
							'label' => $term->name,
						);
					}
				}
			}
		} else {
			// For manually created attributes
			$attribute_options = $color_attribute->get_options();
			if ( ! empty( $attribute_options ) ) {
				foreach ( $attribute_options as $option ) {
					$color_options[] = array(
						'value' => $option,
						'label' => $option,
					);
				}
			}
		}
		
		// Map each color to its variation image
		foreach ( $variations as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation && $variation instanceof WC_Product_Variation ) {
				$variation_image_id = $variation->get_image_id();
				$variation_attributes = $variation->get_attributes();
				
				// Get color value from variation
				// For taxonomy-based attributes, the key is the taxonomy name (e.g., 'pa_colour')
				// For manual attributes, the key is 'attribute_' + sanitized name
				$color_value = '';
				if ( $color_attribute->is_taxonomy() ) {
					// Global attribute: use taxonomy name directly
					if ( isset( $variation_attributes[ $color_attribute_name ] ) ) {
						$color_value = $variation_attributes[ $color_attribute_name ];
					}
				} else {
					// Manual attribute: use attribute_ prefix
					$attribute_slug = 'attribute_' . sanitize_title( $color_attribute_name );
					if ( isset( $variation_attributes[ $attribute_slug ] ) ) {
						$color_value = $variation_attributes[ $attribute_slug ];
					} elseif ( isset( $variation_attributes[ $color_attribute_name ] ) ) {
						$color_value = $variation_attributes[ $color_attribute_name ];
					}
				}
				
				if ( ! empty( $color_value ) && ! empty( $variation_image_id ) ) {
					$color_variation_map[ $color_value ] = $variation_image_id;
					$variation_image_ids[] = $variation_image_id;
				}
			}
		}
		
		// Build color swatches array
		// Show all color options - always create swatches for all available colors
		// If a color has a variation image, use it; otherwise, we'll use the main product image
		$default_image_id = $main_image_id; // Use main image as default
		
		foreach ( $color_options as $color_option ) {
			$color_value = $color_option['value'];
			$color_label = $color_option['label'];
			$color_hex = basic_shop_theme_get_color_hex( $color_label, $color_attribute_name );
			
			// Get image for this color - prefer variation image, fallback to main product image
			$image_id = isset( $color_variation_map[ $color_value ] ) ? $color_variation_map[ $color_value ] : $default_image_id;
			
			// Always add swatch for all color options
			// Use variation image if available, otherwise use default (main image or first variation image)
			$final_image_id = ! empty( $image_id ) ? $image_id : ( ! empty( $default_image_id ) ? $default_image_id : ( ! empty( $variation_image_ids[0] ) ? $variation_image_ids[0] : null ) );
			
			// Add swatch even if no image - we'll handle placeholder in the slider
			$color_swatches[] = array(
				'value' => $color_value,
				'label' => $color_label,
				'hex' => $color_hex,
				'image_id' => $final_image_id,
			);
		}
	} else {
		// No color attribute, just get variation images
		$variations = $product->get_children();
		foreach ( $variations as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation && $variation instanceof WC_Product_Variation ) {
				$variation_image_id = $variation->get_image_id();
				if ( ! empty( $variation_image_id ) ) {
					$variation_image_ids[] = $variation_image_id;
				}
			}
		}
	}
	
	// Remove duplicates
	$variation_image_ids = array_unique( $variation_image_ids );
}

// If no main image, use placeholder
if ( ! $main_image_id && empty( $attachment_ids ) && empty( $variation_image_ids ) ) {
	echo wc_placeholder_img( 'woocommerce_thumbnail' );
	return;
}

// Combine main image with gallery images and variation images
$all_image_ids = array();
if ( $main_image_id ) {
	$all_image_ids[] = $main_image_id;
}
if ( ! empty( $attachment_ids ) ) {
	// Remove main image from gallery if it's duplicated
	$attachment_ids = array_diff( $attachment_ids, array( $main_image_id ) );
	$all_image_ids = array_merge( $all_image_ids, $attachment_ids );
}
// Add variation images (excluding duplicates)
if ( ! empty( $variation_image_ids ) ) {
	// Remove any images that are already in the array
	$variation_image_ids = array_diff( $variation_image_ids, $all_image_ids );
	$all_image_ids = array_merge( $all_image_ids, $variation_image_ids );
}

// If we have color swatches, always show the slider (even with one image)
// This allows users to click swatches to see different colors
if ( ! empty( $color_swatches ) ) {
	// Ensure we have at least one image for the slider
	if ( empty( $all_image_ids ) && ! empty( $main_image_id ) ) {
		$all_image_ids[] = $main_image_id;
	}
	// If still empty, use placeholder
	if ( empty( $all_image_ids ) ) {
		$all_image_ids[] = 0; // Will use placeholder
	}
}

// If only one image and no color swatches, just show it normally
if ( count( $all_image_ids ) <= 1 && empty( $color_swatches ) ) {
	if ( ! empty( $all_image_ids[0] ) && $all_image_ids[0] > 0 ) {
		echo wp_get_attachment_image( $all_image_ids[0], 'woocommerce_thumbnail', false, array( 'class' => 'product-thumbnail-single' ) );
	} else {
		echo wc_placeholder_img( 'woocommerce_thumbnail' );
	}
	return;
}

// Multiple images - show slider
$product_id = $product->get_id();
?>
<div class="product-thumbnail-slider" data-product-id="<?php echo esc_attr( $product_id ); ?>">
	<div class="product-thumbnail-slider-wrapper">
		<?php foreach ( $all_image_ids as $index => $image_id ) : 
			// Handle placeholder (image_id = 0)
			if ( $image_id > 0 ) :
		?>
			<div class="product-thumbnail-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide-index="<?php echo esc_attr( $index ); ?>" data-image-id="<?php echo esc_attr( $image_id ); ?>">
				<?php echo wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, array( 'class' => 'product-thumbnail-image' ) ); ?>
			</div>
		<?php else : ?>
			<div class="product-thumbnail-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide-index="<?php echo esc_attr( $index ); ?>" data-image-id="0">
				<?php echo wc_placeholder_img( 'woocommerce_thumbnail', array( 'class' => 'product-thumbnail-image' ) ); ?>
			</div>
		<?php 
			endif;
		endforeach; ?>
	</div>
	<?php if ( count( $all_image_ids ) > 1 ) : ?>
		<button class="product-thumbnail-arrow product-thumbnail-arrow-prev" aria-label="<?php esc_attr_e( 'Previous image', 'basic-shop-theme' ); ?>">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 15L7 10L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
		<button class="product-thumbnail-arrow product-thumbnail-arrow-next" aria-label="<?php esc_attr_e( 'Next image', 'basic-shop-theme' ); ?>">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M8 5L13 10L8 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</button>
	<?php endif; ?>
	
	<?php 
	// Debug: Show if swatches are found (visible in page source)
	$debug_attr_name = isset( $color_attribute_name ) ? $color_attribute_name : 'none';
	$debug_attr_found = isset( $color_attribute ) && $color_attribute ? 'yes' : 'no';
	$debug_color_options = isset( $color_options ) ? count( $color_options ) : 0;
	$debug_attr_options = isset( $color_attribute ) && $color_attribute ? count( $color_attribute->get_options() ) : 0;
	echo '<!-- DEBUG Product ' . $product_id . ': Color swatches count: ' . count( $color_swatches ) . ', Color attribute found: ' . $debug_attr_found . ', Attribute name: ' . $debug_attr_name . ', Color options: ' . $debug_color_options . ', Attribute options count: ' . $debug_attr_options . ', Product type: ' . $product->get_type() . ' -->';
	
	if ( ! empty( $color_swatches ) ) : ?>
		<div class="product-thumbnail-color-swatches" data-product-id="<?php echo esc_attr( $product_id ); ?>">
			<?php foreach ( $color_swatches as $swatch_index => $swatch ) : ?>
				<button 
					type="button" 
					class="product-thumbnail-color-swatch" 
					data-color-value="<?php echo esc_attr( $swatch['value'] ); ?>"
					data-image-id="<?php echo esc_attr( $swatch['image_id'] ); ?>"
					style="background-color: <?php echo esc_attr( $swatch['hex'] ); ?>;"
					aria-label="<?php echo esc_attr( sprintf( __( 'Show %s', 'basic-shop-theme' ), $swatch['label'] ) ); ?>"
					title="<?php echo esc_attr( $swatch['label'] ); ?>"
				>
					<span class="screen-reader-text"><?php echo esc_html( $swatch['label'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

