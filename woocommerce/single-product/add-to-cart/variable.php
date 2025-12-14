<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>
		<table class="variations" cellspacing="0" role="presentation" style="display: none;">
			<tbody>
				<?php foreach ( $attributes as $attribute_name => $options ) :
					$attribute_id = sanitize_title( $attribute_name );
					$attribute_label = wc_attribute_label( $attribute_name );
					$attribute_slug = 'attribute_' . $attribute_id;
				?>
					<tr>
						<td class="value">
							<?php
							wc_dropdown_variation_attribute_options(
								array(
									'options'   => $options,
									'attribute' => $attribute_name,
									'product'   => $product,
									'class'     => 'variation-select-hidden',
									'required'  => true,
								)
							);
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="variations-wrapper">
			<?php foreach ( $attributes as $attribute_name => $options ) : 
				$attribute_id = sanitize_title( $attribute_name );
				$attribute_label = wc_attribute_label( $attribute_name );
				$attribute_slug = 'attribute_' . $attribute_id;
				$is_size = ( strpos( $attribute_name, 'size' ) !== false || strpos( $attribute_id, 'size' ) !== false );
				$is_color = ( strpos( $attribute_name, 'color' ) !== false || strpos( $attribute_name, 'colour' ) !== false || strpos( $attribute_id, 'color' ) !== false || strpos( $attribute_id, 'colour' ) !== false );
				
				// Get terms if taxonomy - same logic as WooCommerce
				$terms = array();
				$option_values = array();
				if ( taxonomy_exists( $attribute_name ) ) {
					$terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) );
					foreach ( $terms as $term ) {
						if ( in_array( $term->slug, $options, true ) ) {
							$option_values[] = array(
								'value' => $term->slug,
								'label' => apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute_name, $product ),
							);
						}
					}
				} else {
					foreach ( $options as $option ) {
						$option_values[] = array(
							'value' => $option,
							'label' => apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute_name, $product ),
						);
					}
				}
			?>
				<div class="variation-item variation-item--<?php echo esc_attr( $attribute_id ); ?>">
					<?php if ( $is_size ) : ?>
						<!-- Size Button Selector -->
						<label class="variation-label variation-size-label">
							<?php echo esc_html( strtoupper( $attribute_label ) ); ?>: <span class="selected-size-value"><?php echo ! empty( $option_values[0] ) ? esc_html( strtoupper( $option_values[0]['label'] ) ) : ''; ?></span>
						</label>
						<div class="variation-size-selector" data-attribute="<?php echo esc_attr( $attribute_slug ); ?>">
							<?php 
							$first_size = true;
							foreach ( $option_values as $option_data ) :
								$option_value = $option_data['value'];
								$option_label = $option_data['label'];
							?>
								<button 
									type="button" 
									class="variation-size-button <?php echo $first_size ? 'selected' : ''; ?>" 
									data-value="<?php echo esc_attr( $option_value ); ?>"
									data-label="<?php echo esc_attr( $option_label ); ?>"
									aria-label="<?php echo esc_attr( sprintf( __( 'Select %s %s', 'woocommerce' ), $attribute_label, $option_label ) ); ?>"
								>
									<?php echo esc_html( strtoupper( $option_label ) ); ?>
								</button>
							<?php 
								$first_size = false;
							endforeach; 
							?>
						</div>
					<?php elseif ( $is_color ) : ?>
						<!-- Color Swatch Selector -->
						<label class="variation-label variation-color-label">
							<?php echo esc_html( strtoupper( $attribute_label ) ); ?>: <span class="selected-color-value"><?php echo ! empty( $option_values[0] ) ? esc_html( strtoupper( $option_values[0]['label'] ) ) : ''; ?></span>
						</label>
						<div class="variation-color-selector" data-attribute="<?php echo esc_attr( $attribute_slug ); ?>">
							<?php 
							$first_color = true;
							foreach ( $option_values as $option_data ) :
								$option_value = $option_data['value'];
								$option_label = $option_data['label'];
								$color_hex = basic_shop_theme_get_color_hex( $option_label, $attribute_name );
							?>
								<button 
									type="button" 
									class="variation-color-button <?php echo $first_color ? 'selected' : ''; ?>" 
									data-value="<?php echo esc_attr( $option_value ); ?>"
									data-label="<?php echo esc_attr( $option_label ); ?>"
									data-color-hex="<?php echo esc_attr( $color_hex ); ?>"
									style="background-color: <?php echo esc_attr( $color_hex ); ?>;"
									aria-label="<?php echo esc_attr( sprintf( __( 'Select %s %s', 'woocommerce' ), $attribute_label, $option_label ) ); ?>"
									title="<?php echo esc_attr( $option_label ); ?>"
								>
									<span class="screen-reader-text"><?php echo esc_html( $option_label ); ?></span>
								</button>
							<?php 
								$first_color = false;
							endforeach; 
							?>
						</div>
					<?php else : ?>
						<!-- Default Dropdown -->
						<label class="variation-label variation-dropdown-label" for="<?php echo esc_attr( $attribute_id ); ?>">
							<?php echo esc_html( $attribute_label ); ?>:
						</label>
						<select 
							class="variation-dropdown" 
							id="<?php echo esc_attr( $attribute_id ); ?>"
							name="<?php echo esc_attr( $attribute_slug ); ?>"
							data-attribute="<?php echo esc_attr( $attribute_slug ); ?>"
						>
							<option value=""><?php echo esc_html( apply_filters( 'woocommerce_variation_default_option_name', __( 'Choose an option', 'woocommerce' ), $product, $attribute_name ) ); ?></option>
							<?php
							foreach ( $option_values as $option_data ) {
								echo '<option value="' . esc_attr( $option_data['value'] ) . '">' . esc_html( $option_data['label'] ) . '</option>';
							}
							?>
						</select>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			
			<?php
			/**
			 * Filters the reset variation button.
			 *
			 * @since 2.5.0
			 *
			 * @param string  $button The reset variation button HTML.
			 */
			echo wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#" aria-label="' . esc_attr__( 'Clear options', 'woocommerce' ) . '">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) );
			?>
		</div>
		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
				/**
				 * Hook: woocommerce_before_single_variation.
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * Hook: woocommerce_after_single_variation.
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );

