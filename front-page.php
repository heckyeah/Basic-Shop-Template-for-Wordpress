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

		<?php
		// Get the front page content
		$front_page_id = get_option( 'page_on_front' );
		
		if ( $front_page_id ) {
			// If a static page is set as front page, get its content
			$front_page = get_post( $front_page_id );
			if ( $front_page ) {
				?>
				<article id="post-<?php echo esc_attr( $front_page_id ); ?>" <?php post_class( '', $front_page_id ); ?>>
					
					<div class="entry-content">
						<?php
						// Display the content from the WordPress editor
						echo apply_filters( 'the_content', $front_page->post_content );
						?>
					</div>
				</article>
				<?php
			}
		} else {
			// Fallback: if no static page is set, use the loop
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<?php if ( get_the_title() ) : ?>
						<header class="entry-header">
							<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
						</header>
					<?php endif; ?>
					
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>
				<?php
			endwhile;
		}
		?>


	</main>
</div>

<?php
get_footer();

