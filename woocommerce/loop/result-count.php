<?php
/**
 * Result Count
 *
 * Shows text: X Products or X of Y Products when filtered
 *
 * @package Basic_Shop_Theme
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total = isset( $total ) ? intval( $total ) : 0;
$per_page = isset( $per_page ) ? intval( $per_page ) : 0;
$current = isset( $current ) ? intval( $current ) : 1;
$all_products = isset( $all_products ) ? intval( $all_products ) : 0;
$has_filters_param = isset( $has_filters ) ? (bool) $has_filters : null;

// Always get the total product count (all products, not filtered)
if ( $all_products > 0 ) {
	$all_products_count = $all_products;
} else {
	// Get total products count (all products, not filtered)
	$all_products_query = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );
	$all_products_count = $all_products_query->found_posts;
	wp_reset_postdata();
}

// Check if filters are active
// If has_filters is passed as parameter (from AJAX), use it
// Otherwise check URL parameters (for initial page load)
if ( null !== $has_filters_param ) {
	$has_filters = $has_filters_param;
} else {
	// Check URL parameters for filters
	$has_filters = false;
	if ( isset( $_GET['filter_category'] ) || isset( $_GET['filter_min_price'] ) || 
		isset( $_GET['filter_max_price'] ) || isset( $_GET['filter_stock'] ) ||
		isset( $_GET['filter_sale'] ) || isset( $_GET['filter_rating'] ) ||
		( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) ) {
		$has_filters = true;
		
		// Check for attribute filters
		foreach ( $_GET as $key => $value ) {
			if ( strpos( $key, 'filter_' ) === 0 && $key !== 'filter_category' && 
				$key !== 'filter_min_price' && $key !== 'filter_max_price' && 
				$key !== 'filter_stock' && $key !== 'filter_sale' && $key !== 'filter_rating' ) {
				$has_filters = true;
				break;
			}
		}
	}
}

// Determine what to display
$filtered_count = $total; // Current filtered count
$total_count = $all_products_count; // Total products available

?>
<p class="woocommerce-result-count" id="shop-result-count" role="alert" aria-relevant="all">
	<?php
	if ( $has_filters && $total_count > 0 ) {
		// Show "X of Y Products" when filters are active
		printf(
			/* translators: 1: filtered results count 2: total products count */
			esc_html__( '%1$d of %2$d Products', 'basic-shop-theme' ),
			$filtered_count,
			$total_count
		);
	} else {
		// Show "X Products" when no filters (show total count)
		printf(
			/* translators: %d: total products count */
			esc_html( _n( '%d Product', '%d Products', $total_count, 'basic-shop-theme' ) ),
			$total_count
		);
	}
	?>
</p>

