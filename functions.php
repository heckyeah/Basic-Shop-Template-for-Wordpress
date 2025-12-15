<?php
/**
 * Basic Shop Theme Functions
 *
 * @package Basic_Shop_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Theme setup
 */
function basic_shop_theme_setup() {
	// Add theme support
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	// WooCommerce support
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Register navigation menus
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'basic-shop-theme' ),
		'footer'  => __( 'Footer Menu', 'basic-shop-theme' ),
	) );
}
add_action( 'after_setup_theme', 'basic_shop_theme_setup' );

/**
 * Remove WooCommerce default styles
 */
function basic_shop_theme_disable_woocommerce_styles( $styles ) {
	// Completely disable WooCommerce default stylesheets
	return array();
}
add_filter( 'woocommerce_enqueue_styles', 'basic_shop_theme_disable_woocommerce_styles' );

/**
 * Enqueue scripts and styles
 */
function basic_shop_theme_scripts() {
	// Styles - WooCommerce styles are disabled, so we only load our custom styles
	wp_enqueue_style( 'basic-shop-theme-style', get_stylesheet_uri(), array(), '1.0.0' );
	wp_enqueue_style( 'basic-shop-theme-main', get_template_directory_uri() . '/assets/css/style.css', array(), '1.0.1' );
	wp_enqueue_style( 'basic-shop-theme-woocommerce', get_template_directory_uri() . '/assets/css/woocommerce.css', array( 'basic-shop-theme-main' ), '1.0.2' );

	// Scripts
	wp_enqueue_script( 'basic-shop-theme-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'basic-shop-theme-minicart', get_template_directory_uri() . '/assets/js/minicart.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'basic-shop-theme-product-gallery', get_template_directory_uri() . '/assets/js/product-gallery.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'basic-shop-theme-last-viewed', get_template_directory_uri() . '/assets/js/last-viewed.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'basic-shop-theme-toast', get_template_directory_uri() . '/assets/js/toast.js', array( 'jquery' ), '1.0.0', true );
	
	// Product size selector (only on single product pages)
	if ( is_product() ) {
		wp_enqueue_script( 'basic-shop-theme-size-selector', get_template_directory_uri() . '/assets/js/product-size-selector.js', array( 'jquery' ), '1.0.0', true );
	}
	
	// Shop filters script (only on shop/archive pages)
	if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
		wp_enqueue_script( 'basic-shop-theme-filters', get_template_directory_uri() . '/assets/js/shop-filters.js', array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'basic-shop-theme-filters', 'basicShopFilters', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'basic-shop-filters-nonce' ),
			'shopUrl' => wc_get_page_permalink( 'shop' ),
		) );
	}
	
	// Product thumbnail slider (on shop/archive pages and single product pages for related products)
	if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() || is_product() ) {
		wp_enqueue_script( 'basic-shop-theme-thumbnail-slider', get_template_directory_uri() . '/assets/js/product-thumbnail-slider.js', array( 'jquery' ), '1.0.0', true );
	}

	// Localize scripts
	wp_localize_script( 'basic-shop-theme-minicart', 'basicShopAjax', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'basic-shop-nonce' ),
	) );

	// WooCommerce AJAX cart fragments
	if ( function_exists( 'is_woocommerce' ) ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
}
add_action( 'wp_enqueue_scripts', 'basic_shop_theme_scripts', 20 );

/**
 * Register widget areas
 */
function basic_shop_theme_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'basic-shop-theme' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Add widgets here.', 'basic-shop-theme' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'basic_shop_theme_widgets_init' );

/**
 * Customize WooCommerce output
 */
function basic_shop_theme_woocommerce_setup() {
	// Remove default WooCommerce wrappers
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

	// Remove breadcrumbs from shop page
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

	// Remove shop heading/title from shop page
	remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10 );
	
	// Remove default result count and ordering from before_shop_loop hook (we'll output them manually in a container)
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	
	// Remove star ratings from product listings
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	
	// Replace default product thumbnail with slider
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
	add_action( 'woocommerce_before_shop_loop_item_title', 'basic_shop_theme_template_loop_product_thumbnail_slider', 10 );

	// Add custom wrappers
	add_action( 'woocommerce_before_main_content', 'basic_shop_theme_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'basic_shop_theme_wrapper_end', 10 );
}
add_action( 'init', 'basic_shop_theme_woocommerce_setup' );

/**
 * Custom product thumbnail slider for shop loop
 */
function basic_shop_theme_template_loop_product_thumbnail_slider() {
	wc_get_template( 'loop/product-thumbnail-slider.php' );
}

/**
 * Start wrapper
 */
function basic_shop_theme_wrapper_start() {
	if ( is_product() ) {
		echo '<div id="primary" class="content-area content-area-fullwidth"><main id="main" class="site-main site-main-fullwidth">';
	} elseif ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
		echo '<div id="primary" class="content-area content-area-fullwidth"><main id="main" class="site-main site-main-fullwidth">';
	} else {
		echo '<div id="primary" class="content-area"><main id="main" class="site-main">';
	}
}

/**
 * End wrapper
 */
function basic_shop_theme_wrapper_end() {
	echo '</main></div>';
}

/**
 * Add last viewed products tracking
 */
function basic_shop_theme_track_product_view() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}

	global $post;
	$product_id = $post->ID;

	// Get existing viewed products
	$viewed_products = isset( $_COOKIE['basic_shop_viewed_products'] ) ? json_decode( stripslashes( $_COOKIE['basic_shop_viewed_products'] ), true ) : array();

	// Remove current product if it exists
	$viewed_products = array_diff( $viewed_products, array( $product_id ) );

	// Add current product to the beginning
	array_unshift( $viewed_products, $product_id );

	// Limit to 10 products
	$viewed_products = array_slice( $viewed_products, 0, 10 );

	// Set cookie for 30 days
	setcookie( 'basic_shop_viewed_products', json_encode( $viewed_products ), time() + ( 30 * DAY_IN_SECONDS ), '/' );
}
add_action( 'wp', 'basic_shop_theme_track_product_view' );

/**
 * Get last viewed products
 */
function basic_shop_theme_get_last_viewed_products( $limit = 4 ) {
	$viewed_products = isset( $_COOKIE['basic_shop_viewed_products'] ) ? json_decode( stripslashes( $_COOKIE['basic_shop_viewed_products'] ), true ) : array();

	if ( empty( $viewed_products ) ) {
		return array();
	}

	// Remove current product
	global $post;
	if ( isset( $post->ID ) ) {
		$viewed_products = array_diff( $viewed_products, array( $post->ID ) );
	}

	// Limit results
	$viewed_products = array_slice( $viewed_products, 0, $limit );

	return $viewed_products;
}

/**
 * Add last viewed products section after single product summary
 */
function basic_shop_theme_display_last_viewed() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}

	$viewed_ids = basic_shop_theme_get_last_viewed_products( 4 );

	if ( empty( $viewed_ids ) ) {
		return;
	}

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => 4,
		'post__in'       => $viewed_ids,
		'orderby'        => 'post__in',
	);

	$products = new WP_Query( $args );

	if ( ! $products->have_posts() ) {
		return;
	}

	echo '<section class="last-viewed-products">';
	echo '<h2 class="section-title">' . esc_html__( 'Recently Viewed', 'basic-shop-theme' ) . '</h2>';
	echo '<ul class="products columns-4">';

	while ( $products->have_posts() ) {
		$products->the_post();
		wc_get_template_part( 'content', 'product' );
	}

	echo '</ul>';
	echo '</section>';

	wp_reset_postdata();
}
add_action( 'woocommerce_after_single_product_summary', 'basic_shop_theme_display_last_viewed', 25 );

/**
 * Render custom mini cart HTML
 */
function basic_shop_theme_render_mini_cart() {
	ob_start();
	
	do_action( 'woocommerce_before_mini_cart' );
	
	if ( WC()->cart && ! WC()->cart->is_empty() ) :
		?>
		<!-- Scrollable cart items area -->
		<div class="minicart-scrollable-content">
			<ul class="woocommerce-mini-cart cart_list product_list_widget">
				<?php
				do_action( 'woocommerce_before_mini_cart_contents' );

				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

					if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
						$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'thumbnail' ), $cart_item, $cart_item_key );
						$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
						
						// Get product data
						$is_on_sale = $_product->is_on_sale();
						$regular_price = $_product->get_regular_price();
						$current_price = $_product->get_price();
						$variation_data = wc_get_formatted_cart_item_data( $cart_item );
						$discount_percent = 0;
						$discount_amount = 0;
						
						if ( $is_on_sale && $regular_price ) {
							$discount_amount = floatval( $regular_price ) - floatval( $current_price );
							$discount_percent = round( ( $discount_amount / floatval( $regular_price ) ) * 100 );
						}
						?>
						<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
							<?php
							// Remove button URL
							$remove_url = esc_url( wc_get_cart_remove_url( $cart_item_key ) );
							?>
							
							<!-- Product Image -->
							<div class="minicart-item-image">
								<?php if ( empty( $product_permalink ) ) : ?>
									<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php else : ?>
									<a href="<?php echo esc_url( $product_permalink ); ?>">
										<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</a>
								<?php endif; ?>
							</div>
							
							<!-- Product Details -->
							<div class="minicart-item-details">
								<!-- Pricing (Top Right) -->
								<div class="minicart-item-price-wrapper">
									<?php if ( $is_on_sale && $regular_price ) : ?>
										<span class="minicart-item-price-original"><?php echo wc_price( $regular_price * $cart_item['quantity'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									<?php endif; ?>
									<span class="minicart-item-price-current"><?php echo wc_price( $current_price * $cart_item['quantity'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</div>
								
								<!-- Product Name -->
								<?php if ( empty( $product_permalink ) ) : ?>
									<div class="minicart-item-name"><?php echo wp_kses_post( strtoupper( $product_name ) ); ?></div>
								<?php else : ?>
									<a href="<?php echo esc_url( $product_permalink ); ?>" class="minicart-item-name"><?php echo wp_kses_post( strtoupper( $product_name ) ); ?></a>
								<?php endif; ?>
								
								<!-- Variation/Size -->
								<?php if ( $variation_data ) : ?>
									<div class="minicart-item-variation"><?php echo $variation_data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								<?php endif; ?>
								
								<!-- Sale Badge -->
								<?php if ( $is_on_sale && $discount_percent > 0 ) : ?>
									<div class="minicart-item-sale-badge">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M2 2L8 1L14 2V8L8 15L2 8V2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
											<path d="M6 6H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
										</svg>
										<span><?php esc_html_e( 'Sale', 'basic-shop-theme' ); ?> - <?php echo esc_html( $discount_percent ); ?>% (<?php echo wc_price( -$discount_amount * $cart_item['quantity'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>)</span>
									</div>
								<?php endif; ?>
								
								<!-- Quantity Controls and Remove Button (Same Row) -->
								<div class="minicart-item-actions">
									<div class="minicart-quantity-controls">
										<button type="button" class="minicart-quantity-btn minus" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Decrease quantity', 'basic-shop-theme' ); ?>">-</button>
										<input type="number" class="minicart-quantity-input" value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" min="1" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Quantity', 'basic-shop-theme' ); ?>">
										<button type="button" class="minicart-quantity-btn plus" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Increase quantity', 'basic-shop-theme' ); ?>">+</button>
									</div>
									
									<button type="button" class="remove minicart-remove-item" aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ); ?>" data-product_id="<?php echo esc_attr( $product_id ); ?>" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" data-product_sku="<?php echo esc_attr( $_product->get_sku() ); ?>">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M2 4H14M12.6667 4V13.3333C12.6667 14 12 14.6667 11.3333 14.6667H4.66667C4 14.6667 3.33333 14 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2 6 1.33333 6.66667 1.33333H9.33333C10 1.33333 10.6667 2 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</button>
								</div>
							</div>
						</li>
						<?php
					}
				}

			do_action( 'woocommerce_mini_cart_contents' );
			?>
		</ul>
	</div>

	<!-- Fixed buttons at bottom -->
	<div class="minicart-actions-wrapper">
		<p class="woocommerce-mini-cart__tax-message">
			<?php esc_html_e( 'Tax included. Shipping calculated at checkout.', 'basic-shop-theme' ); ?>
		</p>
		<?php
		$checkout_url = wc_get_checkout_url();
		if ( $checkout_url ) :
			// Get just the subtotal amount (no label)
			$subtotal_amount = WC()->cart->get_cart_subtotal();
			?>
			<a href="<?php echo esc_url( $checkout_url ); ?>" class="woocommerce-mini-cart__view-cart-link">
				<span class="view-cart-text"><?php esc_html_e( 'CHECKOUT', 'basic-shop-theme' ); ?></span>
				<span class="view-cart-separator">•</span>
				<span class="view-cart-amount"><?php echo $subtotal_amount; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
			<?php
		endif;
		
		// Add View Cart link
		$cart_url = wc_get_cart_url();
		if ( $cart_url ) :
			?>
			<a href="<?php echo esc_url( $cart_url ); ?>" class="woocommerce-mini-cart__view-cart-text-link">
				<?php esc_html_e( 'VIEW CART', 'basic-shop-theme' ); ?>
			</a>
			<?php
		endif;
		?>
	</div>

		<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

	<?php else : ?>

		<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

	<?php endif; ?>

	<?php
	do_action( 'woocommerce_after_mini_cart' );
	
	return ob_get_clean();
}

/**
 * Update cart item quantity via AJAX
 */
function basic_shop_theme_update_cart_quantity() {
	check_ajax_referer( 'basic-shop-nonce', 'nonce' );

	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
	$quantity      = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 0;

	if ( empty( $cart_item_key ) || $quantity < 1 ) {
		wp_send_json_error( array( 'message' => __( 'Invalid request.', 'basic-shop-theme' ) ) );
		return;
	}

	// Check if cart item exists
	if ( ! WC()->cart->find_product_in_cart( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Cart item not found.', 'basic-shop-theme' ) ) );
		return;
	}

	// Get cart item
	$cart_item = WC()->cart->get_cart_item( $cart_item_key );
	if ( ! $cart_item ) {
		wp_send_json_error( array( 'message' => __( 'Cart item not found.', 'basic-shop-theme' ) ) );
		return;
	}

	// Validate quantity
	$product = $cart_item['data'];
	
	// Check if product is sold individually
	if ( $product->is_sold_individually() && $quantity > 1 ) {
		wp_send_json_error( array( 'message' => sprintf( __( 'You can only have 1 %s in your cart.', 'woocommerce' ), $product->get_name() ) ) );
		return;
	}

	// Check stock availability
	if ( ! $product->is_in_stock() ) {
		wp_send_json_error( array( 'message' => __( 'Product is out of stock.', 'basic-shop-theme' ) ) );
		return;
	}

	// Check stock quantity
	if ( $product->managing_stock() ) {
		$stock_quantity = $product->get_stock_quantity();
		if ( $stock_quantity < $quantity ) {
			wp_send_json_error( array( 'message' => sprintf( __( 'Only %d available in stock.', 'woocommerce' ), $stock_quantity ) ) );
			return;
		}
	}

	// Update quantity
	$updated = WC()->cart->set_quantity( $cart_item_key, $quantity, true );

	if ( $updated ) {
		// Recalculate totals
		WC()->cart->calculate_totals();

		wp_send_json_success( array(
			'message'     => __( 'Quantity updated.', 'basic-shop-theme' ),
			'cart_hash'   => WC()->cart->get_cart_hash(),
			'cart_count'  => WC()->cart->get_cart_contents_count(),
		) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to update quantity.', 'basic-shop-theme' ) ) );
	}
}
add_action( 'wp_ajax_basic_shop_update_cart_quantity', 'basic_shop_theme_update_cart_quantity' );
add_action( 'wp_ajax_nopriv_basic_shop_update_cart_quantity', 'basic_shop_theme_update_cart_quantity' );

/**
 * Get cart totals only (for updating without replacing entire cart)
 */
function basic_shop_theme_get_cart_totals() {
	check_ajax_referer( 'basic-shop-nonce', 'nonce' );

	ob_start();
	?>
	<div class="minicart-actions-wrapper">
		<p class="woocommerce-mini-cart__tax-message">
			<?php esc_html_e( 'Tax included. Shipping calculated at checkout.', 'basic-shop-theme' ); ?>
		</p>
		<?php
		$checkout_url = wc_get_checkout_url();
		if ( $checkout_url ) :
			// Get just the subtotal amount (no label)
			$subtotal_amount = WC()->cart->get_cart_subtotal();
			?>
			<a href="<?php echo esc_url( $checkout_url ); ?>" class="woocommerce-mini-cart__view-cart-link">
				<span class="view-cart-text"><?php esc_html_e( 'CHECKOUT', 'basic-shop-theme' ); ?></span>
				<span class="view-cart-separator">•</span>
				<span class="view-cart-amount"><?php echo $subtotal_amount; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
			<?php
		endif;
		
		// Add View Cart link
		$cart_url = wc_get_cart_url();
		if ( $cart_url ) :
			?>
			<a href="<?php echo esc_url( $cart_url ); ?>" class="woocommerce-mini-cart__view-cart-text-link">
				<?php esc_html_e( 'VIEW CART', 'basic-shop-theme' ); ?>
			</a>
			<?php
		endif;
		?>
	</div>
	<?php
	$totals_html = ob_get_clean();

	// Get checkout button HTML separately
	ob_start();
	do_action( 'woocommerce_widget_shopping_cart_buttons' );
	$checkout_button_html = ob_get_clean();

	wp_send_json_success( array(
		'total_html' => $totals_html,
		'checkout_button_html' => $checkout_button_html,
		'cart_count' => WC()->cart->get_cart_contents_count(),
		'cart_hash' => WC()->cart->get_cart_hash(),
	) );
}
add_action( 'wp_ajax_basic_shop_get_cart_totals', 'basic_shop_theme_get_cart_totals' );
add_action( 'wp_ajax_nopriv_basic_shop_get_cart_totals', 'basic_shop_theme_get_cart_totals' );

/**
 * Get cart item price (for updating price display without replacing entire item)
 */
function basic_shop_theme_get_cart_item_price() {
	check_ajax_referer( 'basic-shop-nonce', 'nonce' );

	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';

	if ( empty( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid request.', 'basic-shop-theme' ) ) );
		return;
	}

	$cart_item = WC()->cart->get_cart_item( $cart_item_key );
	if ( ! $cart_item ) {
		wp_send_json_error( array( 'message' => __( 'Cart item not found.', 'basic-shop-theme' ) ) );
		return;
	}

	$_product = $cart_item['data'];
	$quantity = $cart_item['quantity'];
	
	// Get product data
	$is_on_sale = $_product->is_on_sale();
	$regular_price = $_product->get_regular_price();
	$current_price = $_product->get_price();
	$discount_percent = 0;
	$discount_amount = 0;
	
	if ( $is_on_sale && $regular_price ) {
		$discount_amount = floatval( $regular_price ) - floatval( $current_price );
		$discount_percent = round( ( $discount_amount / floatval( $regular_price ) ) * 100 );
	}

	$price_current = wc_price( $current_price * $quantity );
	$price_original = '';
	$sale_badge = '';

	if ( $is_on_sale && $regular_price ) {
		$price_original = '<span class="minicart-item-price-original">' . wc_price( $regular_price * $quantity ) . '</span>';
	}

	if ( $is_on_sale && $discount_percent > 0 ) {
		$sale_badge = '<span>' . esc_html__( 'Sale', 'basic-shop-theme' ) . ' - ' . esc_html( $discount_percent ) . '% (' . wc_price( -$discount_amount * $quantity ) . ')</span>';
	}

	wp_send_json_success( array(
		'price_current' => $price_current,
		'price_original' => $price_original,
		'sale_badge' => $sale_badge ? '<div class="minicart-item-sale-badge">' . $sale_badge . '</div>' : '',
	) );
}
add_action( 'wp_ajax_basic_shop_get_cart_item_price', 'basic_shop_theme_get_cart_item_price' );
add_action( 'wp_ajax_nopriv_basic_shop_get_cart_item_price', 'basic_shop_theme_get_cart_item_price' );

/**
 * Remove cart item via AJAX
 */
function basic_shop_theme_remove_cart_item() {
	check_ajax_referer( 'basic-shop-nonce', 'nonce' );

	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';

	if ( empty( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid request.', 'basic-shop-theme' ) ) );
		return;
	}

	// Check if cart item exists
	$cart_item = WC()->cart->get_cart_item( $cart_item_key );
	if ( ! $cart_item ) {
		wp_send_json_error( array( 'message' => __( 'Cart item not found.', 'basic-shop-theme' ) ) );
		return;
	}

	// Remove item from cart
	$removed = WC()->cart->remove_cart_item( $cart_item_key );

	if ( $removed ) {
		// Recalculate totals
		WC()->cart->calculate_totals();

		wp_send_json_success( array(
			'message'     => __( 'Item removed from cart.', 'basic-shop-theme' ),
			'cart_hash'   => WC()->cart->get_cart_hash(),
			'cart_count'  => WC()->cart->get_cart_contents_count(),
		) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to remove item from cart.', 'basic-shop-theme' ) ) );
	}
}
add_action( 'wp_ajax_basic_shop_remove_cart_item', 'basic_shop_theme_remove_cart_item' );
add_action( 'wp_ajax_nopriv_basic_shop_remove_cart_item', 'basic_shop_theme_remove_cart_item' );

/**
 * Update minicart via AJAX
 */
function basic_shop_theme_update_minicart() {
	check_ajax_referer( 'basic-shop-nonce', 'nonce' );

	$mini_cart = basic_shop_theme_render_mini_cart();

	$data = array(
		'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
			'#minicart-content' => $mini_cart,
			'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
		) ),
		'cart_hash' => WC()->cart->get_cart_hash(),
		'cart_count' => WC()->cart->get_cart_contents_count(),
	);

	wp_send_json( $data );
}
add_action( 'wp_ajax_basic_shop_update_minicart', 'basic_shop_theme_update_minicart' );
add_action( 'wp_ajax_nopriv_basic_shop_update_minicart', 'basic_shop_theme_update_minicart' );

/**
 * Add product ID to body class for JavaScript tracking
 */
function basic_shop_theme_body_class_product_id( $classes ) {
	if ( is_singular( 'product' ) ) {
		global $post;
		$classes[] = 'product-id-' . $post->ID;
	}
	return $classes;
}
add_filter( 'body_class', 'basic_shop_theme_body_class_product_id' );

/**
 * Configure related products
 */
function basic_shop_theme_related_products_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns'] = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'basic_shop_theme_related_products_args' );

/**
 * Remove add to cart button from shop page, related products, and last viewed products
 */
function basic_shop_theme_remove_add_to_cart_from_related( $link, $product ) {
	// Remove from shop page
	if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
		return '';
	}
	
	// Check if we're in related products or last viewed products section
	$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
	
	foreach ( $backtrace as $trace ) {
		if ( isset( $trace['function'] ) ) {
			// Check if called from related products or last viewed products
			if ( $trace['function'] === 'woocommerce_output_related_products' || 
				 $trace['function'] === 'basic_shop_theme_display_last_viewed' ) {
				return '';
			}
		}
	}
	
	return $link;
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'basic_shop_theme_remove_add_to_cart_from_related', 10, 2 );

/**
 * Remove description tab from product tabs
 */
function basic_shop_theme_remove_product_tabs( $tabs ) {
	// Remove description tab
	unset( $tabs['description'] );
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'basic_shop_theme_remove_product_tabs', 98 );

/**
 * Remove product tabs from default location
 */
function basic_shop_theme_remove_product_tabs_output() {
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
}
add_action( 'wp', 'basic_shop_theme_remove_product_tabs_output' );

/**
 * Display reviews at the bottom of the page (after related products)
 */
function basic_shop_theme_display_reviews_at_bottom() {
	if ( ! is_product() ) {
		return;
	}
	
	global $product;
	
	// Only show if reviews are enabled
	if ( ! wc_review_ratings_enabled() || ! $product->get_reviews_allowed() ) {
		return;
	}
	
	// Output reviews section
	echo '<div class="product-reviews-section">';
	wc_get_template( 'single-product-reviews.php' );
	echo '</div>';
}
add_action( 'woocommerce_after_single_product_summary', 'basic_shop_theme_display_reviews_at_bottom', 30 );

/**
 * Display product category before title
 */
function basic_shop_theme_product_category_before_title() {
	if ( ! is_product() ) {
		return;
	}
	
	global $product;
	$categories = wc_get_product_category_list( $product->get_id(), ', ', '<span class="product-category">', '</span>' );
	
	if ( $categories ) {
		echo '<div class="product-category-wrapper">' . $categories . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'woocommerce_single_product_summary', 'basic_shop_theme_product_category_before_title', 4 );

/**
 * Customize product title output
 */
function basic_shop_theme_product_title_wrapper() {
	echo '<div class="product-title-wrapper">';
}
add_action( 'woocommerce_single_product_summary', 'basic_shop_theme_product_title_wrapper', 4 );

/**
 * Close product title wrapper
 */
function basic_shop_theme_product_title_wrapper_close() {
	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'basic_shop_theme_product_title_wrapper_close', 6 );

/**
 * Get color hex code from color name
 * 
 * @param string $color_name Color name
 * @param string $taxonomy_name Taxonomy name for term meta lookup
 * @return string Hex color code or color name
 */
function basic_shop_theme_get_color_hex( $color_name, $taxonomy_name = '' ) {
	// Common color name to hex mapping
	$color_map = array(
		'red' => '#FF0000',
		'blue' => '#0000FF',
		'green' => '#008000',
		'yellow' => '#FFFF00',
		'orange' => '#FFA500',
		'purple' => '#800080',
		'pink' => '#FFC0CB',
		'black' => '#000000',
		'white' => '#FFFFFF',
		'gray' => '#808080',
		'grey' => '#808080',
		'brown' => '#A52A2A',
		'navy' => '#000080',
		'teal' => '#008080',
		'cyan' => '#00FFFF',
		'magenta' => '#FF00FF',
		'lime' => '#00FF00',
		'olive' => '#808000',
		'maroon' => '#800000',
		'silver' => '#C0C0C0',
		'gold' => '#FFD700',
		'beige' => '#F5F5DC',
		'tan' => '#D2B48C',
		'khaki' => '#F0E68C',
		'coral' => '#FF7F50',
		'salmon' => '#FA8072',
		'turquoise' => '#40E0D0',
		'violet' => '#EE82EE',
		'indigo' => '#4B0082',
	);
	
	$color_lower = strtolower( trim( $color_name ) );
	
	// Check if it's already a hex code
	if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $color_name ) ) {
		return $color_name;
	}
	
	// Check color map
	if ( isset( $color_map[ $color_lower ] ) ) {
		return $color_map[ $color_lower ];
	}
	
	// Try to get from term meta if it's a taxonomy (some plugins store hex in term meta)
	if ( ! empty( $taxonomy_name ) ) {
		$term = get_term_by( 'name', $color_name, $taxonomy_name );
		if ( $term && ! is_wp_error( $term ) ) {
			$hex = get_term_meta( $term->term_id, 'product_attribute_color', true );
			if ( ! empty( $hex ) ) {
				return $hex;
			}
		}
	}
	
	// Try CSS color name (browser will handle it)
	// Return the color name as-is, CSS will try to interpret it
	return $color_lower;
}

/**
 * Add stock quantity to variation data
 */
function basic_shop_theme_add_stock_quantity_to_variation( $variation_data, $product, $variation ) {
	if ( $variation && is_a( $variation, 'WC_Product_Variation' ) ) {
		$is_in_stock = $variation->is_in_stock();
		
		// Get stock quantity - this handles parent inheritance automatically
		// Use 'view' context to get the actual stock quantity (handles parent inheritance)
		$stock_quantity_raw = $variation->get_stock_quantity( 'view' );
		
		// Normalize stock quantity using WooCommerce helper
		$stock_quantity = wc_stock_amount( $stock_quantity_raw );
		
		// Store raw values for JavaScript
		$variation_data['stock_quantity'] = $stock_quantity !== null && $stock_quantity !== '' ? $stock_quantity : '';
		$variation_data['stock_status'] = $variation->get_stock_status();
		$variation_data['manage_stock'] = $variation->get_manage_stock();
		
		// Generate stock count HTML - only show if we have a numeric quantity
		$stock_count_html = '';
		
		if ( $is_in_stock ) {
			// Check if we have a valid numeric stock quantity
			// wc_stock_amount returns a number or empty string
			if ( $stock_quantity !== null && $stock_quantity !== false && $stock_quantity !== '' && is_numeric( $stock_quantity ) ) {
				// We have a valid stock quantity number - show it
				$stock_qty_num = absint( $stock_quantity );
				if ( $stock_qty_num > 0 ) {
					$stock_count_html = '<div class="variation-stock-count">' . sprintf( esc_html__( '%d in stock', 'woocommerce' ), $stock_qty_num ) . '</div>';
				}
			}
			// If no numeric quantity, don't show stock count (user only wants counts, not "In stock" message)
		}
		
		$variation_data['stock_count_html'] = $stock_count_html;
	}
	return $variation_data;
}
add_filter( 'woocommerce_available_variation', 'basic_shop_theme_add_stock_quantity_to_variation', 10, 3 );

/**
 * Display product attributes on single product page
 */
function basic_shop_theme_display_product_attributes() {
	global $product;
	
	if ( ! is_product() ) {
		return;
	}
	
	$attributes = $product->get_attributes();
	
	if ( empty( $attributes ) ) {
		return;
	}
	
	// Get product attributes for display
	$product_attributes = array();
	
	foreach ( $attributes as $attribute ) {
		$values = array();
		$raw_values = array(); // Store raw values for size selector
		
		if ( $attribute->is_taxonomy() ) {
			$attribute_taxonomy = $attribute->get_taxonomy_object();
			$attribute_values   = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) );
			
			foreach ( $attribute_values as $attribute_value ) {
				$value_name = esc_html( $attribute_value->name );
				$raw_values[] = $value_name; // Store raw value
				
				if ( $attribute_taxonomy->attribute_public ) {
					$values[] = '<a href="' . esc_url( get_term_link( $attribute_value->term_id, $attribute->get_name() ) ) . '" rel="tag">' . $value_name . '</a>';
				} else {
					$values[] = $value_name;
				}
			}
		} else {
			$raw_values = $attribute->get_options();
			$values = $raw_values;
			
			foreach ( $values as &$value ) {
				$value = make_clickable( esc_html( $value ) );
			}
		}
		
		$attribute_key = 'attribute_' . sanitize_title_with_dashes( $attribute->get_name() );
		$attribute_name = sanitize_title_with_dashes( $attribute->get_name() );
		
		$product_attributes[ $attribute_key ] = array(
			'label' => wc_attribute_label( $attribute->get_name() ),
			'value' => apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values ),
			'raw_values' => $raw_values, // Add raw values for size/color selector
			'attribute_name' => $attribute_name, // Add attribute name for checking if it's size/color
			'is_taxonomy' => $attribute->is_taxonomy(), // Store if it's a taxonomy for color hex lookup
			'taxonomy_name' => $attribute->is_taxonomy() ? $attribute->get_name() : '', // Store taxonomy name for color hex lookup
		);
	}
	
	/**
	 * Hook: woocommerce_display_product_attributes.
	 *
	 * @since 3.6.0.
	 * @param array $product_attributes Array of attributes to display; label, value.
	 * @param WC_Product $product Showing attributes for this product.
	 */
	$product_attributes = apply_filters( 'woocommerce_display_product_attributes', $product_attributes, $product );
	
	if ( ! empty( $product_attributes ) ) {
		wc_get_template(
			'single-product/product-attributes.php',
			array(
				'product_attributes' => $product_attributes,
				'product'            => $product,
				'attributes'         => $attributes,
			)
		);
	}
}
// Disabled - attributes removed from single product page
// add_action( 'woocommerce_single_product_summary', 'basic_shop_theme_display_product_attributes', 25 );

/**
 * Get price range for all products
 */
function basic_shop_theme_get_price_range() {
	global $wpdb;
	
	$min_price = $wpdb->get_var(
		"SELECT MIN(meta_value + 0) 
		FROM {$wpdb->postmeta} 
		WHERE meta_key IN ('_price', '_regular_price') 
		AND meta_value != ''"
	);
	
	$max_price = $wpdb->get_var(
		"SELECT MAX(meta_value + 0) 
		FROM {$wpdb->postmeta} 
		WHERE meta_key IN ('_price', '_regular_price') 
		AND meta_value != ''"
	);
	
	return array(
		'min' => floatval( $min_price ? $min_price : 0 ),
		'max' => floatval( $max_price ? $max_price : 1000 ),
	);
}

/**
 * AJAX handler for filtering products
 */
function basic_shop_theme_filter_products() {
	check_ajax_referer( 'basic-shop-filters-nonce', 'nonce' );
	
	// Get filter parameters
	$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
	$categories = isset( $_POST['categories'] ) ? array_map( 'absint', (array) $_POST['categories'] ) : array();
	// Get price values - only set if they're actually provided, not empty, and greater than 0
	// Round to whole dollars (no cents)
	$min_price = '';
	$max_price = '';
	if ( isset( $_POST['min_price'] ) && $_POST['min_price'] !== '' && $_POST['min_price'] !== null && $_POST['min_price'] !== '0' ) {
		$min_price_float = floatval( $_POST['min_price'] );
		// Only keep if it's greater than 0, and round to whole dollar
		if ( $min_price_float > 0 ) {
			$min_price = round( $min_price_float );
		}
	}
	if ( isset( $_POST['max_price'] ) && $_POST['max_price'] !== '' && $_POST['max_price'] !== null && $_POST['max_price'] !== '0' ) {
		$max_price_float = floatval( $_POST['max_price'] );
		// Only keep if it's greater than 0, and round to whole dollar
		if ( $max_price_float > 0 ) {
			$max_price = round( $max_price_float );
		}
	}
	$stock_status = isset( $_POST['stock_status'] ) ? sanitize_text_field( wp_unslash( $_POST['stock_status'] ) ) : '';
	$on_sale = isset( $_POST['on_sale'] ) && '1' === $_POST['on_sale'];
	$page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
	$attributes = isset( $_POST['attributes'] ) ? (array) $_POST['attributes'] : array();
	
	// Build query args
	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => wc_get_default_products_per_row() * wc_get_default_product_rows_per_page(),
		'paged'          => $page,
		'meta_query'     => array(),
		'tax_query'      => array( 'relation' => 'AND' ),
	);
	
	// Search - includes product tags
	if ( ! empty( $search ) ) {
		// Find product tags that match the search term
		$tag_terms = get_terms( array(
			'taxonomy'   => 'product_tag',
			'name__like' => $search,
			'hide_empty' => false,
		) );
		
		$tag_product_ids = array();
		if ( ! is_wp_error( $tag_terms ) && ! empty( $tag_terms ) ) {
			// Get product IDs that have matching tags
			$tag_ids = wp_list_pluck( $tag_terms, 'term_id' );
			$tag_query = new WP_Query( array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => 'product_tag',
						'field'    => 'term_id',
						'terms'    => $tag_ids,
						'operator' => 'IN',
					),
				),
			) );
			$tag_product_ids = $tag_query->posts;
			wp_reset_postdata();
		}
		
		// If we have tag matches, we need to combine with standard search
		if ( ! empty( $tag_product_ids ) ) {
			// Run standard search first to get those product IDs
			$standard_search_query = new WP_Query( array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				's'              => $search,
			) );
			$standard_product_ids = $standard_search_query->posts;
			wp_reset_postdata();
			
			// Combine both sets of product IDs
			$combined_product_ids = array_unique( array_merge( $standard_product_ids, $tag_product_ids ) );
			
			// Use post__in instead of 's' to search only in these products
			// But we need to preserve other filters, so we'll use a different approach
			// Store the combined IDs and use them with post__in
			if ( ! empty( $combined_product_ids ) ) {
				// If we already have post__in (from on_sale filter), intersect them
				if ( isset( $args['post__in'] ) && ! empty( $args['post__in'] ) ) {
					$args['post__in'] = array_intersect( $args['post__in'], $combined_product_ids );
				} else {
					$args['post__in'] = $combined_product_ids;
				}
				// Remove 's' since we're using post__in
				unset( $args['s'] );
			}
		} else {
			// No tag matches, use standard search
			$args['s'] = $search;
		}
	}
	
	// Categories
	if ( ! empty( $categories ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $categories,
			'operator' => 'IN',
		);
	}
	
	// Price range
	if ( '' !== $min_price || '' !== $max_price ) {
		$price_query = array(
			'key'     => '_price',
			'value'   => array( $min_price ? $min_price : 0, $max_price ? $max_price : 999999 ),
			'compare' => 'BETWEEN',
			'type'    => 'DECIMAL',
		);
		$args['meta_query'][] = $price_query;
	}
	
	// Stock status
	if ( ! empty( $stock_status ) ) {
		$args['meta_query'][] = array(
			'key'     => '_stock_status',
			'value'   => $stock_status,
			'compare' => '=',
		);
	}
	
	// On sale
	if ( $on_sale ) {
		// Get product IDs that are on sale
		$on_sale_ids = wc_get_product_ids_on_sale();
		if ( ! empty( $on_sale_ids ) ) {
			if ( ! isset( $args['post__in'] ) ) {
				$args['post__in'] = $on_sale_ids;
			} else {
				$args['post__in'] = array_intersect( $args['post__in'], $on_sale_ids );
			}
		} else {
			// No products on sale, return empty result
			$args['post__in'] = array( 0 );
		}
	}
	
	
	// Product attributes
	foreach ( $attributes as $taxonomy => $terms ) {
		if ( ! empty( $terms ) && is_array( $terms ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => sanitize_text_field( $taxonomy ),
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_text_field', $terms ),
				'operator' => 'IN',
			);
		}
	}
	
	// Execute query
	$query = new WP_Query( $args );
	
	// Set up WooCommerce loop
	wc_set_loop_prop( 'name', 'shop' );
	wc_set_loop_prop( 'columns', 4 );
	wc_set_loop_prop( 'total', $query->found_posts );
	wc_set_loop_prop( 'total_pages', $query->max_num_pages );
	wc_set_loop_prop( 'per_page', $args['posts_per_page'] );
	wc_set_loop_prop( 'current_page', $page );
	
	// Get products HTML
	ob_start();
	
	if ( $query->have_posts() ) {
		woocommerce_product_loop_start();
		
		while ( $query->have_posts() ) {
			$query->the_post();
			wc_get_template_part( 'content', 'product' );
		}
		
		woocommerce_product_loop_end();
	} else {
		wc_get_template( 'loop/no-products-found.php' );
	}
	
	$products_html = ob_get_clean();
	
	// Get pagination HTML
	ob_start();
	woocommerce_pagination();
	$pagination_html = ob_get_clean();
	
	// Get total products count (all products, not filtered) for "X of Y Products" display
	$all_products_query = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );
	$all_products_count = $all_products_query->found_posts;
	wp_reset_postdata();
	
	// Determine if filters are active
	$has_filters = false;
	if ( ! empty( $search ) || ! empty( $categories ) || 
		( '' !== $min_price || '' !== $max_price ) || 
		! empty( $stock_status ) || $on_sale || 
		! empty( $attributes ) ) {
		$has_filters = true;
	}
	
	// Get result count HTML
	ob_start();
	$result_count_args = array(
		'total'         => $query->found_posts, // Filtered count
		'per_page'     => $args['posts_per_page'],
		'current'       => $page,
		'all_products'  => $all_products_count, // Total count
		'has_filters'   => $has_filters,
	);
	wc_get_template( 'loop/result-count.php', $result_count_args );
	$result_count_html = ob_get_clean();
	
	wp_reset_postdata();
	
	// Get active filters data for display
	$active_filters = basic_shop_theme_get_active_filters_data( $search, $categories, $min_price, $max_price, $stock_status, $on_sale, $attributes );
	
	wp_send_json_success( array(
		'products'        => $products_html,
		'pagination'      => $pagination_html,
		'result_count'    => $result_count_html,
		'found'           => $query->found_posts,
		'all_products'    => $all_products_count,
		'max_pages'       => $query->max_num_pages,
		'active_filters'  => $active_filters,
	) );
}
add_action( 'wp_ajax_basic_shop_filter_products', 'basic_shop_theme_filter_products' );
add_action( 'wp_ajax_nopriv_basic_shop_filter_products', 'basic_shop_theme_filter_products' );

/**
 * Hide shop page title
 */
function basic_shop_theme_hide_shop_page_title( $show ) {
	if ( is_shop() ) {
		return false;
	}
	return $show;
}
add_filter( 'woocommerce_show_page_title', 'basic_shop_theme_hide_shop_page_title' );

/**
 * Set default product columns to 4 for shop page
 */
function basic_shop_theme_set_product_columns( $columns ) {
	if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
		return 4;
	}
	return $columns;
}
add_filter( 'loop_shop_columns', 'basic_shop_theme_set_product_columns' );

/**
 * Modify result count arguments to include total product count
 */
function basic_shop_theme_result_count_args( $args ) {
	if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
		// Get total products count (all products, not filtered)
		$all_products_query = new WP_Query( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );
		$args['all_products'] = $all_products_query->found_posts;
		wp_reset_postdata();
	}
	return $args;
}
add_filter( 'woocommerce_result_count_args', 'basic_shop_theme_result_count_args' );

/**
 * Get active filters data for display
 */
function basic_shop_theme_get_active_filters_data( $search, $categories, $min_price, $max_price, $stock_status, $on_sale, $attributes ) {
	$active_filters = array();
	
	// Search
	if ( ! empty( $search ) ) {
		$active_filters[] = array(
			'type' => 'search',
			'label' => sprintf( __( 'Search: %s', 'basic-shop-theme' ), $search ),
			'value' => $search,
		);
	}
	
	// Categories
	if ( ! empty( $categories ) ) {
		foreach ( $categories as $cat_id ) {
			$term = get_term( $cat_id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$active_filters[] = array(
					'type' => 'category',
					'label' => $term->name,
					'value' => $cat_id,
				);
			}
		}
	}
	
	// Price range - only add if user has changed from default
	$price_range = basic_shop_theme_get_price_range();
	$default_min = $price_range['min'];
	$default_max = $price_range['max'];
	
	// Convert to float for comparison, but only if values are actually set and > 0
	$min_price_val = null;
	$max_price_val = null;
	
	if ( '' !== $min_price && $min_price !== null && $min_price !== '0' ) {
		$min_price_val = floatval( $min_price );
		// If it's 0 or less, treat as not set
		if ( $min_price_val <= 0 ) {
			$min_price_val = null;
		}
	}
	
	if ( '' !== $max_price && $max_price !== null && $max_price !== '0' ) {
		$max_price_val = floatval( $max_price );
		// If it's 0 or less, treat as not set
		if ( $max_price_val <= 0 ) {
			$max_price_val = null;
		}
	}
	
	// Check if user has actually changed the price range from defaults
	$min_changed = false;
	$max_changed = false;
	
	// Min price is changed if it's set AND greater than default min
	if ( null !== $min_price_val && $min_price_val > $default_min ) {
		$min_changed = true;
	}
	
	// Max price is changed if it's set AND less than default max (and greater than 0)
	if ( null !== $max_price_val && $max_price_val > 0 && $max_price_val < $default_max ) {
		$max_changed = true;
	}
	
	if ( $min_changed || $max_changed ) {
		$price_label = '';
		$currency_symbol = html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' );
		// Use 0 decimals for whole dollars only (no cents)
		$decimals = 0;
		$decimal_separator = wc_get_price_decimal_separator();
		$thousand_separator = wc_get_price_thousand_separator();
		
		// Round prices to whole dollars
		$min_price_val = round( $min_price_val );
		$max_price_val = round( $max_price_val );
		
		if ( $min_changed && $max_changed ) {
			$min_formatted = number_format( $min_price_val, $decimals, $decimal_separator, $thousand_separator );
			$max_formatted = number_format( $max_price_val, $decimals, $decimal_separator, $thousand_separator );
			$price_label = $currency_symbol . $min_formatted . ' - ' . $currency_symbol . $max_formatted;
		} elseif ( $min_changed ) {
			$min_formatted = number_format( $min_price_val, $decimals, $decimal_separator, $thousand_separator );
			$price_label = sprintf( __( 'From %s', 'basic-shop-theme' ), $currency_symbol . $min_formatted );
		} elseif ( $max_changed ) {
			$max_formatted = number_format( $max_price_val, $decimals, $decimal_separator, $thousand_separator );
			$price_label = sprintf( __( 'Up to %s', 'basic-shop-theme' ), $currency_symbol . $max_formatted );
		}
		
		if ( ! empty( $price_label ) ) {
			$active_filters[] = array(
				'type' => 'price',
				'label' => $price_label,
				'min_price' => $min_price,
				'max_price' => $max_price,
			);
		}
	}
	
	// Stock status
	if ( ! empty( $stock_status ) ) {
		$stock_labels = array(
			'instock' => __( 'In Stock', 'basic-shop-theme' ),
			'outofstock' => __( 'Out of Stock', 'basic-shop-theme' ),
		);
		$active_filters[] = array(
			'type' => 'stock',
			'label' => isset( $stock_labels[ $stock_status ] ) ? $stock_labels[ $stock_status ] : $stock_status,
			'value' => $stock_status,
		);
	}
	
	// On sale
	if ( $on_sale ) {
		$active_filters[] = array(
			'type' => 'sale',
			'label' => __( 'On Sale', 'basic-shop-theme' ),
			'value' => '1',
		);
	}
	
	
	// Attributes
	foreach ( $attributes as $taxonomy => $terms ) {
		if ( ! empty( $terms ) && is_array( $terms ) ) {
			$attribute_obj = wc_get_attribute( wc_attribute_taxonomy_id_by_name( str_replace( 'pa_', '', $taxonomy ) ) );
			$attr_label = $attribute_obj ? $attribute_obj->name : str_replace( 'pa_', '', $taxonomy );
			
			foreach ( $terms as $term_slug ) {
				$term = get_term_by( 'slug', $term_slug, $taxonomy );
				if ( $term && ! is_wp_error( $term ) ) {
					$active_filters[] = array(
						'type' => 'attribute',
						'label' => $attr_label . ': ' . $term->name,
						'value' => $term_slug,
						'taxonomy' => $taxonomy,
						'attr_name' => str_replace( 'pa_', '', $taxonomy ),
					);
				}
			}
		}
	}
	
	return $active_filters;
}

/**
 * Get active filters for initial page load
 */
function basic_shop_theme_get_initial_active_filters() {
	$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$categories = isset( $_GET['filter_category'] ) ? array_map( 'absint', explode( ',', $_GET['filter_category'] ) ) : array();
	// Round prices to whole dollars (no cents)
	$min_price = isset( $_GET['filter_min_price'] ) ? round( floatval( $_GET['filter_min_price'] ) ) : '';
	$max_price = isset( $_GET['filter_max_price'] ) ? round( floatval( $_GET['filter_max_price'] ) ) : '';
	$stock_status = isset( $_GET['filter_stock'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_stock'] ) ) : '';
	$on_sale = isset( $_GET['filter_sale'] ) && '1' === $_GET['filter_sale'];
	
	// Get attributes
	$attributes = array();
	$attribute_taxonomies = wc_get_attribute_taxonomies();
	foreach ( $attribute_taxonomies as $attribute ) {
		$filter_key = 'filter_' . $attribute->attribute_name;
		if ( isset( $_GET[ $filter_key ] ) ) {
			$value = $_GET[ $filter_key ];
			$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
			if ( is_array( $value ) ) {
				$attributes[ $taxonomy ] = array_map( 'sanitize_text_field', $value );
			} else {
				$attributes[ $taxonomy ] = array_map( 'sanitize_text_field', explode( ',', $value ) );
			}
		}
	}
	
	return basic_shop_theme_get_active_filters_data( $search, $categories, $min_price, $max_price, $stock_status, $on_sale, $attributes );
}

