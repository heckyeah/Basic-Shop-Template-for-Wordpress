<?php
/**
 * Custom Product Image Gallery - 2 Column Grid
 *
 * @package Basic_Shop_Theme
 */

use Automattic\WooCommerce\Enums\ProductType;

defined( 'ABSPATH' ) || exit;

global $product;

$post_thumbnail_id = $product->get_image_id();
$attachment_ids    = $product->get_gallery_image_ids();

// Get variation images if product is variable
$variation_image_ids = array();
if ( $product->is_type( 'variable' ) ) {
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
	// Remove duplicates
	$variation_image_ids = array_unique( $variation_image_ids );
}

// Combine featured image with gallery images and variation images
$all_image_ids = array();
if ( $post_thumbnail_id ) {
	$all_image_ids[] = $post_thumbnail_id;
}
if ( $attachment_ids ) {
	// Remove main image from gallery if it's duplicated
	$attachment_ids = array_diff( $attachment_ids, array( $post_thumbnail_id ) );
	$all_image_ids = array_merge( $all_image_ids, $attachment_ids );
}
// Add variation images (excluding duplicates)
if ( ! empty( $variation_image_ids ) ) {
	// Remove any images that are already in the array
	$variation_image_ids = array_diff( $variation_image_ids, $all_image_ids );
	$all_image_ids = array_merge( $all_image_ids, $variation_image_ids );
}
?>

<div class="custom-product-gallery">
	<?php if ( ! empty( $all_image_ids ) ) : ?>
		<!-- Desktop Grid View -->
		<div class="gallery-grid">
			<?php foreach ( $all_image_ids as $index => $image_id ) : 
				$image_url = wp_get_attachment_image_url( $image_id, 'full' );
				$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
				$image_src = wp_get_attachment_image_url( $image_id, 'woocommerce_single' );
				?>
				<div class="gallery-grid-item">
					<a href="<?php echo esc_url( $image_url ); ?>" class="gallery-grid-image-link" data-lightbox="product-gallery" data-image-index="<?php echo esc_attr( $index ); ?>">
						<img 
							src="<?php echo esc_url( $image_src ); ?>" 
							alt="<?php echo esc_attr( $image_alt ? $image_alt : get_the_title() ); ?>"
							class="gallery-grid-image"
							data-image-id="<?php echo esc_attr( $image_id ); ?>"
							data-full-image="<?php echo esc_url( $image_url ); ?>"
						/>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
		
		<!-- Mobile Slider View -->
		<div class="gallery-slider">
			<div class="gallery-slider-track">
				<?php foreach ( $all_image_ids as $index => $image_id ) : 
					$image_url = wp_get_attachment_image_url( $image_id, 'full' );
					$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
					$image_src = wp_get_attachment_image_url( $image_id, 'woocommerce_single' );
					?>
					<div class="gallery-slide">
						<a href="<?php echo esc_url( $image_url ); ?>" class="gallery-slide-link" data-lightbox="product-gallery" data-image-index="<?php echo esc_attr( $index ); ?>">
							<img 
								src="<?php echo esc_url( $image_src ); ?>" 
								alt="<?php echo esc_attr( $image_alt ? $image_alt : get_the_title() ); ?>"
								class="gallery-slide-image"
								data-image-id="<?php echo esc_attr( $image_id ); ?>"
								data-full-image="<?php echo esc_url( $image_url ); ?>"
							/>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
			<button class="gallery-slider-prev" aria-label="<?php esc_attr_e( 'Previous image', 'basic-shop-theme' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<button class="gallery-slider-next" aria-label="<?php esc_attr_e( 'Next image', 'basic-shop-theme' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<div class="gallery-slider-dots"></div>
		</div>
	<?php else : ?>
		<!-- Placeholder -->
		<div class="gallery-grid">
			<div class="gallery-grid-item gallery-placeholder">
				<img 
					src="<?php echo esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ); ?>" 
					alt="<?php esc_attr_e( 'Awaiting product image', 'woocommerce' ); ?>"
					class="gallery-grid-image"
				/>
			</div>
		</div>
	<?php endif; ?>
</div>

<!-- Lightbox Modal -->
<div id="product-lightbox" class="product-lightbox">
	<button class="lightbox-close" id="lightbox-close" aria-label="<?php esc_attr_e( 'Close lightbox', 'basic-shop-theme' ); ?>">&times;</button>
	<button class="lightbox-prev" id="lightbox-prev" aria-label="<?php esc_attr_e( 'Previous image', 'basic-shop-theme' ); ?>">&#8249;</button>
	<button class="lightbox-next" id="lightbox-next" aria-label="<?php esc_attr_e( 'Next image', 'basic-shop-theme' ); ?>">&#8250;</button>
	<div class="lightbox-content">
		<img id="lightbox-image" src="" alt="">
	</div>
</div>
