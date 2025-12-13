<?php
/**
 * The front page template
 *
 * @package Basic_Shop_Theme
 */

get_header();
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">

		<?php if ( function_exists( 'is_woocommerce' ) ) : ?>
			<section class="shop-products">
				<?php
				$args = array(
					'post_type'      => 'product',
					'posts_per_page' => 12,
					'post_status'    => 'publish',
				);

				$products = new WP_Query( $args );

				if ( $products->have_posts() ) :
					// Set up WooCommerce loop
					wc_set_loop_prop( 'name', 'homepage' );
					wc_set_loop_prop( 'columns', 4 );
					
					woocommerce_product_loop_start();
					
					while ( $products->have_posts() ) :
						$products->the_post();
						wc_get_template_part( 'content', 'product' );
					endwhile;
					
					woocommerce_product_loop_end();
					
					wp_reset_postdata();
				else :
					?>
					<p><?php esc_html_e( 'No products found.', 'basic-shop-theme' ); ?></p>
					<?php
				endif;
				?>
			</section>
		<?php else : ?>
			<section class="page-content">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</section>
		<?php endif; ?>

	</main>
</div>

<?php
get_footer();

