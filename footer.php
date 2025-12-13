<?php
/**
 * The footer template file
 *
 * @package Basic_Shop_Theme
 */
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="footer-container">
			<?php
			if ( is_active_sidebar( 'sidebar-1' ) ) {
				?>
				<aside class="widget-area">
					<?php dynamic_sidebar( 'sidebar-1' ); ?>
				</aside>
				<?php
			}
			?>

			<?php
			wp_nav_menu( array(
				'theme_location' => 'footer',
				'menu_id'        => 'footer-menu',
				'container'      => false,
			) );
			?>

			<div class="site-info">
				<p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'basic-shop-theme' ); ?></p>
			</div>
		</div>
	</footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>

