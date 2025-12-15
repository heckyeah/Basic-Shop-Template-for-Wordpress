<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * @package Basic_Shop_Theme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 */
do_action( 'woocommerce_before_main_content' );
?>

<div class="shop-page-wrapper">
	<!-- Search Bar -->
	<div class="shop-search-wrapper">
		<form role="search" method="get" class="woocommerce-product-search shop-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="shop-search-field"><?php esc_html_e( 'Search for:', 'woocommerce' ); ?></label>
			<input 
				type="search" 
				id="shop-search-field" 
				class="shop-search-field" 
				placeholder="<?php echo esc_attr__( 'Search products&hellip;', 'woocommerce' ); ?>" 
				value="<?php echo esc_attr( get_query_var( 's' ) ); ?>" 
				name="s" 
				autocomplete="off"
			/>
			<button type="submit" class="shop-search-submit" aria-label="<?php esc_attr_e( 'Search', 'woocommerce' ); ?>">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
					<path d="m19 19-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
				</svg>
			</button>
			<input type="hidden" name="post_type" value="product" />
		</form>
	</div>

	<!-- Filter Popup -->
	<aside class="shop-filters-sidebar" id="shop-filters-sidebar">
		<div class="shop-filters-header">
			<h2><?php esc_html_e( 'Filters', 'basic-shop-theme' ); ?></h2>
			<button class="shop-filters-close" id="shop-filters-close" aria-label="<?php esc_attr_e( 'Close filters', 'basic-shop-theme' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
		</div>
		<?php
		// Include filter template
		wc_get_template( 'shop-filters.php' );
		?>
	</aside>

	<!-- Products Grid -->
	<div class="shop-products-wrapper">
			<div class="shop-products-content">
				<?php if ( woocommerce_product_loop() ) : ?>
					<div class="shop-products-toolbar">
					<!-- Filter Toggle Button -->
					<button class="shop-filters-toggle" id="shop-filters-toggle" aria-label="<?php esc_attr_e( 'Toggle filters', 'basic-shop-theme' ); ?>">
						<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<!-- Funnel/Filter icon -->
							<path d="M2 4h16l-2 4-2 4-2 4h-4l-2-4-2-4-2-4z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
							<path d="M4 8h12M6 12h8M8 16h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
						</svg>
						<span><?php esc_html_e( 'Filters', 'basic-shop-theme' ); ?></span>
					</button>
						<?php
						/**
						 * Hook: woocommerce_before_shop_loop.
						 *
						 * @hooked woocommerce_output_all_notices - 10
						 */
						do_action( 'woocommerce_before_shop_loop' );
						?>
						<!-- Result Count and Sorting Container -->
						<div class="shop-toolbar-info">
							<?php
							// Output result count
							wc_get_template( 'loop/result-count.php' );
							// Output sorting dropdown (use function to set up required variables)
							woocommerce_catalog_ordering();
							?>
						</div>
					</div>

					<!-- Active Filters -->
					<div class="shop-active-filters" id="shop-active-filters">
						<?php
						// Display active filters on initial page load
						$initial_filters = basic_shop_theme_get_initial_active_filters();
						if ( ! empty( $initial_filters ) ) :
							foreach ( $initial_filters as $filter ) :
								?>
								<button class="shop-active-filter-tag" 
									data-filter-type="<?php echo esc_attr( $filter['type'] ); ?>"
									data-filter-value="<?php echo esc_attr( $filter['value'] ?? '' ); ?>"
									data-filter-taxonomy="<?php echo esc_attr( $filter['taxonomy'] ?? '' ); ?>"
									data-filter-attr-name="<?php echo esc_attr( $filter['attr_name'] ?? '' ); ?>"
									data-filter-min-price="<?php echo esc_attr( $filter['min_price'] ?? '' ); ?>"
									data-filter-max-price="<?php echo esc_attr( $filter['max_price'] ?? '' ); ?>">
									<span class="shop-active-filter-label"><?php echo esc_html( $filter['label'] ); ?></span>
									<span class="shop-active-filter-close">
										<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M10.5 3.5L3.5 10.5M3.5 3.5L10.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</span>
								</button>
								<?php
							endforeach;
						endif;
						?>
					</div>

					<div class="shop-products-loading" id="shop-products-loading" style="display: none;">
						<div class="loading-spinner"></div>
						<p><?php esc_html_e( 'Loading products...', 'basic-shop-theme' ); ?></p>
					</div>

					<div class="shop-products-grid-wrapper" id="shop-products-grid-wrapper">
						<?php
						woocommerce_product_loop_start();

						if ( wc_get_loop_prop( 'total' ) ) {
							while ( have_posts() ) {
								the_post();

								/**
								 * Hook: woocommerce_shop_loop.
								 */
								do_action( 'woocommerce_shop_loop' );

								wc_get_template_part( 'content', 'product' );
							}
						}

						woocommerce_product_loop_end();
						?>
					</div>

					<div class="shop-products-pagination-wrapper" id="shop-products-pagination-wrapper">
						<?php
						/**
						 * Hook: woocommerce_after_shop_loop.
						 *
						 * @hooked woocommerce_pagination - 10
						 */
						do_action( 'woocommerce_after_shop_loop' );
						?>
					</div>
				<?php else : ?>
					<div class="shop-no-products" id="shop-no-products">
						<?php
						/**
						 * Hook: woocommerce_no_products_found.
						 *
						 * @hooked wc_no_products_found - 10
						 */
						do_action( 'woocommerce_no_products_found' );
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<!-- Filter Overlay -->
<div class="shop-filters-overlay" id="shop-filters-overlay"></div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );

