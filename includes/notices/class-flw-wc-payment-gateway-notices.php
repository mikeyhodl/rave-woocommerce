<?php
/**
 * Class FLW_WC_Payment_Gateway_Notices
 *
 * @package    Flutterwave\WooCommerce
 * @subpackage FLW_WC_Payment_Gateway/includes/notices
 */

defined( 'ABSPATH' ) || exit;

/**
 * Flutterwave Payment Gateway Notices Class
 */
class FLW_WC_Payment_Gateway_Notices {

	/**
	 *  WooCommerce_not_installed
	 *
	 * @return void
	 */
	public function woocommerce_not_installed() {
		include_once dirname( FLW_WC_PLUGIN_FILE ) . '/includes/views/html-admin-missing-woocommerce.php';
	}

	/**
	 *  Woocommerce_wc_not_supported
	 *
	 * @return void
	 */
	public function woocommerce_wc_not_supported() {
		/* translators: $1. Minimum WooCommerce version. $2. Current WooCommerce version. */
		echo sprintf(  '<div class="error"><p><strong>%s</strong></p></div>', sprintf( esc_html__( 'Flutterwave WooCommerce requires WooCommerce %1$s or greater to be installed and active. WooCommerce %2$s is no longer supported.', 'woocommerce-rave' ), FLW_WC_MIN_WC_VER, WC_VERSION ) ); //phpcs:ignore
	}
}
