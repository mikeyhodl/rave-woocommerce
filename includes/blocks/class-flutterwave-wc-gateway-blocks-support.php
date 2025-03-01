<?php
/**
 * The file that defines the Flutterwave_WC_Gateway_Blocks_Support class
 *
 * A class that defines a block type
 *
 * @link       https://flutterwave.com
 * @since      2.3.2
 *
 * @package    Flutterwave\WooCommerce
 * @subpackage FLW_WC_Payment_Gateway/includes
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Class Flutterwave_WC_Gateway_Blocks_Support
 *
 * @since 2.3.2
 * @extends AbstractPaymentMethodType
 * @package Flutterwave
 */
final class Flutterwave_WC_Gateway_Blocks_Support extends AbstractPaymentMethodType {
	/**
	 * Name of the payment method.
	 *
	 * @var string
	 */
	protected $name = 'rave';

	/**
	 * Settings from the WP options table
	 *
	 * @var WC_Payment_Gateway
	 */
	protected WC_Payment_Gateway $gateway;

	/**
	 * Initialize the Block.
	 *
	 * @inheritDoc
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_rave_settings', array() );
		$this->gateway  = new FLW_WC_Payment_Gateway();
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active(): bool {
		return $this->gateway->is_available();
	}

	/**
	 * Returns an array of supported features.
	 *
	 * @return string[]
	 */
	public function get_supported_features(): array {
		return $this->gateway->supports;
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles(): array {
		wp_register_script(
			'flutterwave',
			'https://checkout.flutterwave.com/v3.js',
			array(),
			FLW_WC_VERSION,
			true
		);

		$asset_path   = dirname( FLW_WC_PLUGIN_FILE ) . '/build/index.asset.php';
		$version      = FLW_WC_VERSION;
		$dependencies = array();
		if ( file_exists( $asset_path ) ) {
			$asset        = require $asset_path;
			$version      = is_array( $asset ) && isset( $asset['version'] )
				? $asset['version']
				: $version;
			$dependencies = is_array( $asset ) && isset( $asset['dependencies'] )
				? $asset['dependencies']
				: $dependencies;
		}
		wp_register_script(
			'wc-flutterwave-blocks',
			dirname( FLW_WC_PLUGIN_FILE ) . '/build/index.js',
			array_merge( array( 'flutterwave' ), $dependencies ),
			$version,
			true
		);
		wp_set_script_translations(
			'wc-flutterwave-blocks',
			'woocommerce-rave'
		);

		return array(
			'wc-flutterwave-blocks',
		);
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data(): array {
		return array(
			'icons'      => $this->get_icons(),
			'supports'   => array_filter( $this->get_supported_features(), array( $this->gateway, 'supports' ) ),
			'isAdmin'    => is_admin(),
			'public_key' => ( 'yes' === $this->settings['go_live'] ) ? $this->settings['live_public_key'] : $this->settings['test_public_key'],
			'asset_url'  => plugins_url( 'assets', FLW_WC_PLUGIN_FILE ),
		);
	}

	/**
	 * Returns an array of icons for the payment method.
	 *
	 * @return array
	 */
	private function get_icons(): array {
		$icons_src = array(
			'visa'       => array(
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/visa.svg',
				'alt' => __( 'Visa', 'woocommerce-rave' ),
			),
			'amex'       => array(
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/amex.svg',
				'alt' => __( 'American Express', 'woocommerce-rave' ),
			),
			'mastercard' => array(
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/mastercard.svg',
				'alt' => __( 'Mastercard', 'woocommerce-rave' ),
			),
		);

		if ( 'USD' === get_woocommerce_currency() ) {
			$icons_src['discover'] = array(
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/discover.svg',
				'alt' => _x( 'Discover', 'Name of credit card', 'woocommerce-rave' ),
			);
			$icons_src['jcb']      = array(
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/jcb.svg',
				'alt' => __( 'JCB', 'woocommerce-rave' ),
			);
			$icons_src['diners']   = array(
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/diners.svg',
				'alt' => __( 'Diners', 'woocommerce-rave' ),
			);
		}
		return $icons_src;
	}
}
