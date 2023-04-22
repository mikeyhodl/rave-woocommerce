<?php
/**
 * Class FLW_WC_Payment_Gateway_Notices
 *
 * @package    Flutterwave\WooCommerce
 * @subpackage FLW_WC_Payment_Gateway/includes/notices
 */

defined( 'ABSPATH' ) || exit;
class FLW_WC_Payment_Gateway_Notices {
	public function  woocommerce_not_installed() {
		include_once dirname( FLW_WC_PLUGIN_FILE ) . '/includes/views/html-admin-missing-woocommerce.php';
	}

	public function woocommerce_wc_not_supported() {
		/* translators: $1. Minimum WooCommerce version. $2. Current WooCommerce version. */
		echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Stripe requires WooCommerce %1$s or greater to be installed and active. WooCommerce %2$s is no longer supported.', 'woocommerce-gateway-stripe' ), FLW_WC_MIN_WC_VER, WC_VERSION ) . '</strong></p></div>';
	}
}