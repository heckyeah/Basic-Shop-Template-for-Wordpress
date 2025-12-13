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
	wp_enqueue_script( 'basic-shop-theme-minicart', get_template_directory_uri() . '/assets/js/minicart.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'basic-shop-theme-product-gallery', get_template_directory_uri() . '/assets/js/product-gallery.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'basic-shop-theme-last-viewed', get_template_directory_uri() . '/assets/js/last-viewed.js', array( 'jquery' ), '1.0.0', true );
	wp_enqueue_script( 'basic-shop-theme-toast', get_template_directory_uri() . '/assets/js/toast.js', array( 'jquery' ), '1.0.0', true );

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

	// Add custom wrappers
	add_action( 'woocommerce_before_main_content', 'basic_shop_theme_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'basic_shop_theme_wrapper_end', 10 );
}
add_action( 'init', 'basic_shop_theme_woocommerce_setup' );

/**
 * Start wrapper
 */
function basic_shop_theme_wrapper_start() {
	if ( is_product() ) {
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
 * Remove add to cart button from Related Products and Last Viewed Products
 */
function basic_shop_theme_remove_add_to_cart_from_related( $link, $product ) {
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

