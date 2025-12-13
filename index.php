<?php
/**
 * The main template file
 *
 * @package Basic_Shop_Theme
 */

get_header();
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">

		<?php
		if ( have_posts() ) :

			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header>

					<div class="entry-content">
						<?php
						the_content();
						?>
					</div>
				</article>
				<?php
			endwhile;

		else :
			?>
			<p><?php esc_html_e( 'Nothing found.', 'basic-shop-theme' ); ?></p>
			<?php
		endif;
		?>

	</main>
</div>

<?php
get_footer();

