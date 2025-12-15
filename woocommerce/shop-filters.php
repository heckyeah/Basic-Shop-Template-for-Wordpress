<?php
/**
 * Shop Filters Template
 *
 * @package Basic_Shop_Theme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Get current filter values from URL
$current_search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$current_categories = isset( $_GET['filter_category'] ) ? array_map( 'absint', (array) $_GET['filter_category'] ) : array();
// Round prices to whole dollars (no cents)
$current_min_price = isset( $_GET['filter_min_price'] ) ? round( floatval( $_GET['filter_min_price'] ) ) : '';
$current_max_price = isset( $_GET['filter_max_price'] ) ? round( floatval( $_GET['filter_max_price'] ) ) : '';
$current_stock = isset( $_GET['filter_stock'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_stock'] ) ) : '';
$current_sale = isset( $_GET['filter_sale'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_sale'] ) ) : '';

// Get product categories
$categories = get_terms( array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
) );

// Get all product attributes
$attribute_taxonomies = wc_get_attribute_taxonomies();
$product_attributes = array();

foreach ( $attribute_taxonomies as $attribute ) {
	$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
	) );
	
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$product_attributes[ $taxonomy ] = array(
			'label' => $attribute->attribute_label,
			'name'  => $attribute->attribute_name,
			'terms' => $terms,
		);
	}
}

// Get current attribute filter values
$current_attributes = array();
foreach ( $product_attributes as $taxonomy => $data ) {
	$filter_key = 'filter_' . $data['name'];
	if ( isset( $_GET[ $filter_key ] ) ) {
		$value = $_GET[ $filter_key ];
		// Check if this is the color attribute (single selection - radio)
		$is_color_attr = false;
		$attribute_name_lower = strtolower( $data['name'] );
		$attribute_label_lower = strtolower( $data['label'] );
		$is_color_attr = ( 
			strpos( $attribute_name_lower, 'color' ) !== false || 
			strpos( $attribute_name_lower, 'colour' ) !== false || 
			strpos( $attribute_label_lower, 'color' ) !== false || 
			strpos( $attribute_label_lower, 'colour' ) !== false
		);
		
		if ( $is_color_attr ) {
			// Color attribute: single value (radio button)
			$current_attributes[ $taxonomy ] = array( sanitize_text_field( $value ) );
		} else {
			// Other attributes: multiple values (checkboxes)
			// Handle both array and comma-separated string
			if ( is_array( $value ) ) {
				$current_attributes[ $taxonomy ] = array_map( 'sanitize_text_field', $value );
			} else {
				$current_attributes[ $taxonomy ] = array_map( 'sanitize_text_field', explode( ',', $value ) );
			}
		}
	}
}

// Get price range
$price_range = basic_shop_theme_get_price_range();
?>

<form class="shop-filters-form" id="shop-filters-form" method="get" action="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
	<?php if ( $current_search ) : ?>
		<input type="hidden" name="s" value="<?php echo esc_attr( $current_search ); ?>" />
		<input type="hidden" name="post_type" value="product" />
	<?php endif; ?>

	<div class="shop-filters-content">
		<!-- Categories Filter -->
		<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
			<div class="shop-filter-group">
				<h3 class="shop-filter-title"><?php esc_html_e( 'Categories', 'basic-shop-theme' ); ?></h3>
				<div class="shop-filter-options">
					<?php foreach ( $categories as $category ) : ?>
						<label class="shop-filter-checkbox">
							<input 
								type="checkbox" 
								name="filter_category[]" 
								value="<?php echo esc_attr( $category->term_id ); ?>"
								<?php checked( in_array( $category->term_id, $current_categories, true ) ); ?>
							/>
							<span class="shop-filter-checkbox-label">
								<?php echo esc_html( $category->name ); ?>
								<span class="shop-filter-count">(<?php echo esc_html( $category->count ); ?>)</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Price Range Filter -->
		<div class="shop-filter-group">
			<h3 class="shop-filter-title"><?php esc_html_e( 'Price Range', 'basic-shop-theme' ); ?></h3>
			<div class="shop-filter-price-range">
				<div class="shop-filter-price-inputs">
					<label>
						<span class="shop-filter-price-label"><?php esc_html_e( 'Min', 'basic-shop-theme' ); ?></span>
						<input 
							type="number" 
							name="filter_min_price" 
							class="shop-filter-price-input" 
							placeholder="<?php echo esc_attr( strip_tags( wc_price( $price_range['min'] ) ) ); ?>"
							min="<?php echo esc_attr( $price_range['min'] ); ?>"
							max="<?php echo esc_attr( $price_range['max'] ); ?>"
							step="1"
							value="<?php echo esc_attr( $current_min_price ? round( $current_min_price ) : '' ); ?>"
						/>
					</label>
					<span class="shop-filter-price-separator">-</span>
					<label>
						<span class="shop-filter-price-label"><?php esc_html_e( 'Max', 'basic-shop-theme' ); ?></span>
						<input 
							type="number" 
							name="filter_max_price" 
							class="shop-filter-price-input" 
							placeholder="<?php echo esc_attr( strip_tags( wc_price( $price_range['max'] ) ) ); ?>"
							min="<?php echo esc_attr( $price_range['min'] ); ?>"
							max="<?php echo esc_attr( $price_range['max'] ); ?>"
							step="1"
							value="<?php echo esc_attr( $current_max_price ? round( $current_max_price ) : '' ); ?>"
						/>
					</label>
				</div>
				<div class="shop-filter-price-range-slider" id="shop-filter-price-range-slider">
					<input 
						type="range" 
						class="shop-filter-price-slider shop-filter-price-slider-min" 
						min="<?php echo esc_attr( $price_range['min'] ); ?>"
						max="<?php echo esc_attr( $price_range['max'] ); ?>"
						step="1"
						value="<?php echo esc_attr( $current_min_price ? round( $current_min_price ) : $price_range['min'] ); ?>"
					/>
					<input 
						type="range" 
						class="shop-filter-price-slider shop-filter-price-slider-max" 
						min="<?php echo esc_attr( $price_range['min'] ); ?>"
						max="<?php echo esc_attr( $price_range['max'] ); ?>"
						step="1"
						value="<?php echo esc_attr( $current_max_price ? round( $current_max_price ) : $price_range['max'] ); ?>"
					/>
				</div>
			</div>
		</div>

		<!-- Color Filter (if color attribute exists) -->
		<?php
		$color_attribute = null;
		$color_taxonomy = null;
		foreach ( $product_attributes as $taxonomy => $data ) {
			$attribute_name_lower = strtolower( $data['name'] );
			$attribute_label_lower = strtolower( $data['label'] );
			$attribute_id = sanitize_title( $data['name'] );
			$attribute_id_lower = strtolower( $attribute_id );
			
			// Check multiple ways: name, label, and ID (case-insensitive)
			$is_color = ( 
				strpos( $attribute_name_lower, 'color' ) !== false || 
				strpos( $attribute_name_lower, 'colour' ) !== false || 
				strpos( $attribute_label_lower, 'color' ) !== false || 
				strpos( $attribute_label_lower, 'colour' ) !== false || 
				strpos( $attribute_id_lower, 'color' ) !== false || 
				strpos( $attribute_id_lower, 'colour' ) !== false
			);
			
			if ( $is_color ) {
				$color_attribute = $data;
				$color_taxonomy = $taxonomy;
				break;
			}
		}
		
		// Debug: Output available attributes (visible in page source - view source to see)
		echo '<!-- DEBUG: Available attributes: ';
		foreach ( $product_attributes as $tax => $data ) {
			echo esc_html( $tax ) . ' => name: ' . esc_html( $data['name'] ) . ', label: ' . esc_html( $data['label'] ) . ', terms: ' . count( $data['terms'] ) . ' | ';
		}
		echo 'Color attribute found: ' . ( $color_attribute ? 'YES - taxonomy: ' . esc_html( $color_taxonomy ) . ', terms count: ' . count( $color_attribute['terms'] ) : 'NO' );
		echo ' -->';
		
		// Check if we found a color attribute and it has terms
		if ( $color_attribute ) {
			// Verify terms is an array and not empty
			if ( ! is_array( $color_attribute['terms'] ) || empty( $color_attribute['terms'] ) ) {
				// Try to get terms again without hide_empty
				$terms = get_terms( array(
					'taxonomy'   => $color_taxonomy,
					'hide_empty' => false,
				) );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$color_attribute['terms'] = $terms;
				}
			}
		}
		
		if ( $color_attribute && ! empty( $color_attribute['terms'] ) && is_array( $color_attribute['terms'] ) ) :
			$current_color_values = isset( $current_attributes[ $color_taxonomy ] ) ? $current_attributes[ $color_taxonomy ] : array();
			// For single selection, get the first selected color or empty
			$selected_color = ! empty( $current_color_values ) ? $current_color_values[0] : '';
		?>
			<div class="shop-filter-group shop-filter-group-color">
				<h3 class="shop-filter-title"><?php echo esc_html( $color_attribute['label'] ); ?></h3>
				<div class="shop-filter-color-swatches">
					<?php foreach ( $color_attribute['terms'] as $term ) : 
						// Skip if term is not an object
						if ( ! is_object( $term ) || ! isset( $term->slug ) ) {
							continue;
						}
						$color_hex = basic_shop_theme_get_color_hex( $term->name, $color_taxonomy );
						$is_selected = ( $term->slug === $selected_color );
					?>
						<label class="shop-filter-color-swatch-label">
							<input 
								type="radio" 
								name="filter_<?php echo esc_attr( $color_attribute['name'] ); ?>" 
								value="<?php echo esc_attr( $term->slug ); ?>"
								class="shop-filter-color-radio"
								<?php checked( $is_selected ); ?>
							/>
							<span 
								class="shop-filter-color-swatch <?php echo $is_selected ? 'selected' : ''; ?>"
								style="background-color: <?php echo esc_attr( $color_hex ); ?>;"
								title="<?php echo esc_attr( $term->name ); ?>"
								data-color-slug="<?php echo esc_attr( $term->slug ); ?>"
							>
								<span class="screen-reader-text"><?php echo esc_html( $term->name ); ?></span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Product Attributes Filters (excluding color) -->
		<?php foreach ( $product_attributes as $taxonomy => $data ) : 
			// Skip color attribute as it's handled separately above
			if ( $taxonomy === $color_taxonomy ) {
				continue;
			}
			$current_attr_values = isset( $current_attributes[ $taxonomy ] ) ? $current_attributes[ $taxonomy ] : array();
		?>
			<div class="shop-filter-group">
				<h3 class="shop-filter-title"><?php echo esc_html( $data['label'] ); ?></h3>
				<div class="shop-filter-options">
					<?php foreach ( $data['terms'] as $term ) : ?>
						<label class="shop-filter-checkbox">
							<input 
								type="checkbox" 
								name="filter_<?php echo esc_attr( $data['name'] ); ?>[]" 
								value="<?php echo esc_attr( $term->slug ); ?>"
								<?php checked( in_array( $term->slug, $current_attr_values, true ) ); ?>
							/>
							<span class="shop-filter-checkbox-label">
								<?php echo esc_html( $term->name ); ?>
								<span class="shop-filter-count">(<?php echo esc_html( $term->count ); ?>)</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>

		<!-- Stock Status Filter -->
		<div class="shop-filter-group">
			<h3 class="shop-filter-title"><?php esc_html_e( 'Stock Status', 'basic-shop-theme' ); ?></h3>
			<div class="shop-filter-options">
				<label class="shop-filter-radio">
					<input 
						type="radio" 
						name="filter_stock" 
						value=""
						<?php checked( $current_stock, '' ); ?>
					/>
					<span class="shop-filter-radio-label"><?php esc_html_e( 'All', 'basic-shop-theme' ); ?></span>
				</label>
				<label class="shop-filter-radio">
					<input 
						type="radio" 
						name="filter_stock" 
						value="instock"
						<?php checked( $current_stock, 'instock' ); ?>
					/>
					<span class="shop-filter-radio-label"><?php esc_html_e( 'In Stock', 'basic-shop-theme' ); ?></span>
				</label>
				<label class="shop-filter-radio">
					<input 
						type="radio" 
						name="filter_stock" 
						value="outofstock"
						<?php checked( $current_stock, 'outofstock' ); ?>
					/>
					<span class="shop-filter-radio-label"><?php esc_html_e( 'Out of Stock', 'basic-shop-theme' ); ?></span>
				</label>
			</div>
		</div>

		<!-- Sale Filter -->
		<div class="shop-filter-group">
			<h3 class="shop-filter-title"><?php esc_html_e( 'On Sale', 'basic-shop-theme' ); ?></h3>
			<div class="shop-filter-options">
				<label class="shop-filter-checkbox">
					<input 
						type="checkbox" 
						name="filter_sale" 
						value="1"
						<?php checked( $current_sale, '1' ); ?>
					/>
					<span class="shop-filter-checkbox-label"><?php esc_html_e( 'Show only products on sale', 'basic-shop-theme' ); ?></span>
				</label>
			</div>
		</div>

	</div>

	<div class="shop-filters-actions">
		<button type="button" class="shop-filter-clear" id="shop-filter-clear">
			<?php esc_html_e( 'Clear', 'basic-shop-theme' ); ?>
		</button>
		<button type="submit" class="shop-filter-apply">
			<?php esc_html_e( 'Apply', 'basic-shop-theme' ); ?>
		</button>
	</div>
</form>

