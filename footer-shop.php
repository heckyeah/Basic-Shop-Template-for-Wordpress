<?php
/**
 * The footer template file for shop pages
 *
 * @package Basic_Shop_Theme
 */
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="footer-container">
			<!-- First Row: 4 Columns -->
			<div class="footer-row footer-row-main">
				<div class="footer-column">
					<h3 class="footer-column-title"><?php esc_html_e( 'Information', 'basic-shop-theme' ); ?></h3>
					<?php
					if ( has_nav_menu( 'footer-information' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'footer-information',
							'menu_id'        => 'footer-information-menu',
							'container'      => false,
						) );
					} else {
						?>
						<ul>
							<li><a href="#"><?php esc_html_e( 'About Us', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Contact', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Privacy Policy', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Terms & Conditions', 'basic-shop-theme' ); ?></a></li>
						</ul>
						<?php
					}
					?>
				</div>
				<div class="footer-column">
					<h3 class="footer-column-title"><?php esc_html_e( 'Explore', 'basic-shop-theme' ); ?></h3>
					<?php
					if ( has_nav_menu( 'footer-explore' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'footer-explore',
							'menu_id'        => 'footer-explore-menu',
							'container'      => false,
						) );
					} else {
						?>
						<ul>
							<li><a href="#"><?php esc_html_e( 'Shop', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Collections', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'New Arrivals', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Sale', 'basic-shop-theme' ); ?></a></li>
						</ul>
						<?php
					}
					?>
				</div>
				<div class="footer-column">
					<h3 class="footer-column-title"><?php esc_html_e( 'Help', 'basic-shop-theme' ); ?></h3>
					<?php
					if ( has_nav_menu( 'footer-help' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'footer-help',
							'menu_id'        => 'footer-help-menu',
							'container'      => false,
						) );
					} else {
						?>
						<ul>
							<li><a href="#"><?php esc_html_e( 'FAQ', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Shipping', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Returns', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Size Guide', 'basic-shop-theme' ); ?></a></li>
						</ul>
						<?php
					}
					?>
				</div>
				<div class="footer-column">
					<h3 class="footer-column-title"><?php esc_html_e( 'Other', 'basic-shop-theme' ); ?></h3>
					<?php
					if ( has_nav_menu( 'footer-other' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'footer-other',
							'menu_id'        => 'footer-other-menu',
							'container'      => false,
						) );
					} else {
						?>
						<ul>
							<li><a href="#"><?php esc_html_e( 'Blog', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Newsletter', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Gift Cards', 'basic-shop-theme' ); ?></a></li>
							<li><a href="#"><?php esc_html_e( 'Careers', 'basic-shop-theme' ); ?></a></li>
						</ul>
						<?php
					}
					?>
				</div>
			</div>

			<!-- Second Row: 3/4 and 1/4 Columns -->
			<div class="footer-row footer-row-bottom">
				<div class="footer-column footer-column-quote">
					<p class="footer-quote"><?php esc_html_e( 'Kia kaha, kia maia, kia manawanui - Be strong, be brave, be steadfast', 'basic-shop-theme' ); ?></p>
				</div>
				<div class="footer-column footer-column-social">
					<p class="footer-site-name"><?php bloginfo( 'name' ); ?></p>
					<div class="footer-social-icons">
						<a href="https://facebook.com" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Facebook', 'basic-shop-theme' ); ?>">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<circle cx="12" cy="12" r="11" fill="currentColor"/>
								<path d="M13.5 8.5H15.5V6.5H13.5C12.4 6.5 11.5 7.4 11.5 8.5V10.5H9.5V12.5H11.5V17.5H13.5V12.5H15.5L16.5 10.5H13.5V8.5Z" fill="white"/>
							</svg>
						</a>
						<a href="https://instagram.com" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Instagram', 'basic-shop-theme' ); ?>">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<rect x="3" y="3" width="18" height="18" rx="4" ry="4" fill="white" stroke="currentColor" stroke-width="2"/>
								<circle cx="12" cy="12" r="3.5" stroke="currentColor" stroke-width="1.5" fill="none"/>
								<circle cx="12" cy="12" r="1.5" fill="none" stroke="currentColor" stroke-width="1.5"/>
								<circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>
							</svg>
						</a>
						<a href="https://youtube.com" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'YouTube', 'basic-shop-theme' ); ?>">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<rect x="2" y="6" width="20" height="12" rx="2" ry="2" fill="currentColor"/>
								<path d="M10 9.5L15.5 12L10 14.5V9.5Z" fill="white"/>
							</svg>
						</a>
					</div>
				</div>
			</div>
		</div>
	</footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>

