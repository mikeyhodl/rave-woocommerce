<?php
/**
 * Missing WooCommerce notice.
 *
 * @package Flutterwave WooCommerce
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="notice notice-error" style='text-align: center'>
	<p>
		<?php
		// Translators: %s Plugin name.
		echo sprintf( esc_html__( '%s requires WooCommerce to be installed and activated in order to serve updates.', 'flutterwave-woo' ), '<strong>' . esc_html__( 'Flutterwave WooCommerce', 'flutterwave-woo' ) . '</strong>' );
		?>
	</p>

	<?php if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && current_user_can( 'activate_plugin', 'woocommerce/woocommerce.php' ) ) : ?>
		<p>
			<?php
			$installed_plugins = get_plugins();
			if ( isset( $installed_plugins['woocommerce/woocommerce.php'] ) ) :
				?>
				<a href="<?php echo esc_url( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=woocommerce/woocommerce.php&plugin_status=active' ), 'activate-plugin_woocommerce/woocommerce.php' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Activate WooCommerce', 'flutterwave-woo' ); ?></a>
			<?php endif; ?>
			<?php if ( current_user_can( 'deactivate_plugin', 'woocommerce-rave/woocommerce-rave.php' ) ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url( 'plugins.php?action=deactivate&plugin=woocommerce-rave/woocommerce-rave.php&plugin_status=inactive', 'deactivate-plugin_woocommerce-rave/woocommerce-rave.php' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Turn off Flutterwave WooCommerce', 'flutterwave-woo' ); ?></a>
			<?php endif; ?>
		</p>
	<?php else : ?>
		<?php
		if ( current_user_can( 'install_plugins' ) ) {
			$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
		} else {
			$url = 'http://wordpress.org/plugins/woocommerce/';
		}
		?>
		<p>
			<a href="<?php echo esc_url( $url ); ?>" class="button button-primary"><?php esc_html_e( 'Install WooCommerce', 'flutterwave-woo' ); ?></a>
			<?php if ( current_user_can( 'deactivate_plugin', 'woocommerce-rave/woocommerce-rave.php' ) ) : ?>
				<a href="<?php echo esc_url( wp_nonce_url( 'plugins.php?action=deactivate&plugin=woocommerce-rave/woocommerce-rave.php&plugin_status=inactive', 'deactivate-plugin_woocommerce-rave/woocommerce-rave.php' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Turn off Flutterwave WooCommerce', 'flutterwave-woo' ); ?></a>
			<?php endif; ?>
		</p>
	<?php endif; ?>
</div>
