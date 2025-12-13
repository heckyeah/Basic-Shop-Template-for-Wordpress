<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>

	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				/**
				 * This filter is documented in woocommerce/templates/cart/cart.php.
				 *
				 * @since 2.1.0
				 */
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'thumbnail' ), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				
				// Get product data
				$is_on_sale = $_product->is_on_sale();
				$regular_price = $_product->get_regular_price();
				$sale_price = $_product->get_sale_price();
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
					// Remove button
					$remove_url = esc_url( wc_get_cart_remove_url( $cart_item_key ) );
					?>
					<button type="button" class="remove remove_from_cart_button" aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ); ?>" data-product_id="<?php echo esc_attr( $product_id ); ?>" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" data-product_sku="<?php echo esc_attr( $_product->get_sku() ); ?>" data-remove-url="<?php echo esc_attr( $remove_url ); ?>">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M2 4H14M12.6667 4V13.3333C12.6667 14 12 14.6667 11.3333 14.6667H4.66667C4 14.6667 3.33333 14 3.33333 13.3333V4M5.33333 4V2.66667C5.33333 2 6 1.33333 6.66667 1.33333H9.33333C10 1.33333 10.6667 2 10.6667 2.66667V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>
					
					<?php if ( empty( $product_permalink ) ) : ?>
						<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<a href="<?php echo esc_url( $product_permalink ); ?>">
							<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
					
					<div class="minicart-item-details">
						<?php if ( empty( $product_permalink ) ) : ?>
							<div class="minicart-item-name"><?php echo wp_kses_post( $product_name ); ?></div>
						<?php else : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>" class="minicart-item-name"><?php echo wp_kses_post( $product_name ); ?></a>
						<?php endif; ?>
						
						<?php if ( $variation_data ) : ?>
							<div class="minicart-item-variation"><?php echo $variation_data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<?php endif; ?>
						
						<?php if ( $is_on_sale && $discount_percent > 0 ) : ?>
							<div class="minicart-item-sale-badge">
								<span><?php esc_html_e( 'Sale', 'basic-shop-theme' ); ?> - <?php echo esc_html( $discount_percent ); ?>% (<?php echo wc_price( -$discount_amount * $cart_item['quantity'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>)</span>
							</div>
						<?php endif; ?>
						
						<div class="minicart-item-price-wrapper">
							<?php if ( $is_on_sale && $regular_price ) : ?>
								<span class="minicart-item-price-original"><?php echo wc_price( $regular_price * $cart_item['quantity'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php endif; ?>
							<span class="minicart-item-price-current"><?php echo wc_price( $current_price * $cart_item['quantity'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						</div>
						
						<div class="minicart-item-quantity-wrapper">
							<div class="minicart-quantity-controls">
								<button type="button" class="minicart-quantity-btn minus" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Decrease quantity', 'basic-shop-theme' ); ?>">-</button>
								<input type="number" class="minicart-quantity-input" value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" min="1" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Quantity', 'basic-shop-theme' ); ?>">
								<button type="button" class="minicart-quantity-btn plus" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Increase quantity', 'basic-shop-theme' ); ?>">+</button>
							</div>
						</div>
					</div>
				</li>
				<?php
			}
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<div class="woocommerce-mini-cart__total total">
		<?php
		/**
		 * Hook: woocommerce_widget_shopping_cart_total.
		 *
		 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
		 */
		do_action( 'woocommerce_widget_shopping_cart_total' );
		?>
	</div>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<p class="woocommerce-mini-cart__tax-message">
		<?php esc_html_e( 'Tax included. Shipping calculated at checkout.', 'basic-shop-theme' ); ?>
	</p>

	<p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>
	
	<?php
	$cart_url = wc_get_cart_url();
	if ( $cart_url ) :
		?>
		<a href="<?php echo esc_url( $cart_url ); ?>" class="woocommerce-mini-cart__view-cart-link">
			<?php esc_html_e( 'VIEW CART', 'basic-shop-theme' ); ?>
		</a>
		<?php
	endif;
	?>

	<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
