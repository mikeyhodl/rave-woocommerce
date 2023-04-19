<?php
/**
 * Plugin Name: Flutterwave WooCommerce
 * Plugin URI: https://developer.flutterwave.com/
 * Description: Official WooCommerce payment gateway for Flutterwave.
 * Version: 2.3.2
 * Author: Flutterwave Developers
 * Author URI: http://flutterwave.com/us
 * License: MIT License
 * Text Domain: flutterwave-woo
 * WC requires at least:   3.0.0
 * WC tested up to:        4.9.2
 * Requires at least:      5.6
 * Requires PHP:           7.4
 *
 * @package Flutterwave WooCommerce
 **/

defined( 'ABSPATH' ) || exit;
define( 'FLUTTERWAVEACCESS', 1 );
define( 'FLW_WC_PLUGIN_FILE', __FILE__ );
define( 'FLW_WC_DIR_PATH', plugin_dir_path( FLW_WC_PLUGIN_FILE ) );
define( 'FLW_WC_VERSION', '2.3.2' );

/**
 * Initialize Flutterwave WooCommerce payment gateway.
 */
function flw_woocommerce_rave_init() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	require_once FLW_WC_DIR_PATH . 'includes/class-flw-wc-payment-gateway.php';
	if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {

		require_once FLW_WC_DIR_PATH . 'includes/class-flw-wc-payment-gateway-subscriptions.php';

	}

	add_filter( 'woocommerce_payment_gateways', 'flw_woocommerce_add_rave_gateway', 99 );
}

add_action( 'plugins_loaded', 'flw_woocommerce_rave_init', 99 );

/**
 * Add the Settings link to the plugin
 *
 * @param  array $links Existing links on the plugin page.
 *
 * @return array Existing links with our settings link added
 */
function flw_plugin_action_links( array $links ): array {

	$rave_settings_url = esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=checkout&section=rave' ) );
	array_unshift( $links, "<a title='Flutterwave Settings Page' href='$rave_settings_url'>Settings</a>" );

	return $links;

}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'flw_plugin_action_links' );

/**
 * Add the Gateway to WooCommerce
 *
 * @param  array $methods Existing gateways in WooCommerce.
 *
 * @return array Gateway list with our gateway added
 */
function flw_woocommerce_add_rave_gateway( array $methods ): array {

	if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {

		$methods[] = 'FLW_WC_Payment_Gateway_Subscriptions';

	} else {

		$methods[] = 'FLW_WC_Payment_Gateway';
	}

	return $methods;
}



