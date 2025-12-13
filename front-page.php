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
					?>
					<div class="products-grid">
						<?php
						while ( $products->have_posts() ) :
							$products->the_post();
							wc_get_template_part( 'content', 'product' );
						endwhile;
						?>
					</div>
					<?php
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

