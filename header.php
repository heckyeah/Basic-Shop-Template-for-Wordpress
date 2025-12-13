<?php
/**
 * The header template file
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
									<path d="M7 4V2C7 1.44772 7.44772 1 8 1H16C16.5523 1 17 1.44772 17 2V4H20C20.5523 4 21 4.44772 21 5C21 5.55228 20.5523 6 20 6H19V19C19 20.1046 18.1046 21 17 21H7C5.89543 21 5 20.1046 5 19V6H4C3.44772 6 3 5.55228 3 5C3 4.44772 3.44772 4 4 4H7ZM9 3V4H15V3H9ZM7 6V19H17V6H7Z" fill="currentColor"/>
								</svg>
							</span>
							<span class="minicart-count" id="minicart-count">
								<?php echo WC()->cart->get_cart_contents_count(); ?>
							</span>
						</a>
						<div class="minicart-dropdown" id="minicart-dropdown">
							<div class="minicart-header">
								<h3><?php esc_html_e( 'Shopping Cart', 'basic-shop-theme' ); ?></h3>
								<button class="minicart-close" id="minicart-close">&times;</button>
							</div>
							<div class="minicart-content" id="minicart-content">
								<?php woocommerce_mini_cart(); ?>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<div id="content" class="site-content">

