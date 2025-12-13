<?php
/**
 * Show success messages as toast notifications
 *
 * @package Basic_Shop_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $notices ) {
	return;
}

?>

<?php foreach ( $notices as $notice ) : 
	$message = isset( $notice['notice'] ) ? $notice['notice'] : $notice;
	$message_text = wp_strip_all_tags( $message );
?>
	<div class="woocommerce-toast-notice woocommerce-toast-success" data-toast-message="<?php echo esc_attr( $message_text ); ?>" style="display: none;">
		<?php echo wc_kses_notice( $message ); ?>
	</div>
<?php endforeach; ?>
