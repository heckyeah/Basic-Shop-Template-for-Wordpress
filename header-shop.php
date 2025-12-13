<?php
/**
 * The header template file for shop pages
 *
 * @package Basic_Shop_Theme
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'basic-shop-theme' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="header-container">
			<div class="site-branding">
				<?php
				if ( has_custom_logo() ) {
					the_custom_logo();
				} else {
					?>
					<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
					<?php
				}
				?>
			</div>
			<div class="navigation-link-container">
				<nav id="site-navigation" class="main-navigation">
					<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
						<span class="screen-reader-text"><?php esc_html_e( 'Primary Menu', 'basic-shop-theme' ); ?></span>
						<span class="menu-icon"></span>
					</button>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'container'      => false,
					) );
					?>
				</nav>

				<div class="header-actions">
					<?php if ( function_exists( 'is_woocommerce' ) ) : ?>
						<div class="minicart-wrapper">
							<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="minicart-trigger" id="minicart-trigger">
								<span class="minicart-icon">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<!-- Basket body -->
										<path d="M5 8L4 20C4 20.5523 4.44772 21 5 21H19C19.5523 21 20 20.5523 20 20L19 8H5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
										<!-- Basket handle -->
										<path d="M8 8C8 6.89543 8.89543 6 10 6H14C15.1046 6 16 6.89543 16 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
										<!-- Basket weave pattern -->
										<path d="M7 12H17" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity="0.4"/>
										<path d="M7 15H17" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity="0.4"/>
									</svg>
								</span>
								<span class="minicart-count" id="minicart-count">
									<?php echo WC()->cart->get_cart_contents_count(); ?>
								</span>
							</a>
						</div>
						<!-- Minicart Overlay -->
						<div class="minicart-overlay" id="minicart-overlay"></div>
						<!-- Minicart Sidebar -->
						<div class="minicart-sidebar" id="minicart-sidebar">
							<div class="minicart-header">
								<h3><?php esc_html_e( 'CART', 'basic-shop-theme' ); ?></h3>
								<button class="minicart-close" id="minicart-close" aria-label="<?php esc_attr_e( 'Close cart', 'basic-shop-theme' ); ?>">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</button>
							</div>
							<?php
							// Check if free shipping is available
							$free_shipping_available = false;
							if ( WC()->cart && ! WC()->cart->is_empty() ) {
								$packages = WC()->shipping()->get_packages();
								foreach ( $packages as $package ) {
									$shipping_methods = WC()->shipping()->calculate_shipping( $package );
									foreach ( $shipping_methods as $method ) {
										if ( strpos( $method->id, 'free_shipping' ) !== false ) {
											$free_shipping_available = true;
											break 2;
										}
									}
								}
							}
							?>
							<?php if ( $free_shipping_available ) : ?>
								<div class="minicart-shipping-message">
									<p><?php esc_html_e( 'Free shipping unlocked!', 'basic-shop-theme' ); ?></p>
								</div>
							<?php endif; ?>
							<div class="minicart-content" id="minicart-content">
								<?php
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

								<?php do_action( 'woocommerce_after_mini_cart' ); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</header>

	<div id="content" class="site-content">

