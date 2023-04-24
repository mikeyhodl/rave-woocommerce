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

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

defined( 'ABSPATH' ) || exit;

/**
 * Class Flutterwave_WC_Gateway_Blocks_Support
 *
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
	 * @var WC_Payment_Gateway|null
	 */
	protected ?WC_Payment_Gateway $gateway = null;

	/**
	 * @inheritDoc
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_rave_settings', [] );
		$gateways       = WC()->payment_gateways()->payment_gateways();
		$this->gateway  = $gateways[ $this->name ];
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
			[],
			'2.3.2',
			true
		);

		$asset_path   = dirname( FLW_WC_PLUGIN_FILE ) . '/build/index.asset.php';
		$version      = FLW_WC_VERSION;
		$dependencies = [];
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
			array_merge( [ 'flutterwave' ], $dependencies ),
			$version,
			true
		);
		wp_set_script_translations(
			'wc-flutterwave-blocks',
			'woocommerce-rave'
		);

		return [
			'wc-flutterwave-blocks',
		];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data(): array {
		// We need to call array_merge_recursive so the blocks 'button' setting doesn't overwrite.
		// what's provided from the gateway or payment request configuration.
		return array_replace_recursive(
			$this->get_gateway_javascript_params(),
			// Blocks-specific options.
			[
				'icons'    => $this->get_icons(),
				'supports' => $this->get_supported_features(),
				'isAdmin'  => is_admin(),
			]
		);
	}

	/**
	 * Returns the Flutterwave Payment Gateway JavaScript configuration object.
	 *
	 * @return mixed  the JS configuration from the Flutterwave.
	 */
	private function get_gateway_javascript_params() {
		$js_configuration = [];

		$gateways = WC()->payment_gateways()->get_available_payment_gateways();
		if ( isset( $gateways[ $this->name ] ) ) {
			$js_configuration = $gateways[ $this->name ]->javascript_params();
		}

		// Filter the JS configuration.
		return apply_filters(
			'wc_rave_params',
			$js_configuration
		);
	}

	/**
	 * Returns an array of icons for the payment method.
	 *
	 * @return array
	 */
	private function get_icons(): array {
		$icons_src = [
			'visa'       => [
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/visa.svg',
				'alt' => __( 'Visa', 'woocommerce-rave' ),
			],
			'amex'       => [
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/amex.svg',
				'alt' => __( 'American Express', 'woocommerce-rave' ),
			],
			'mastercard' => [
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/mastercard.svg',
				'alt' => __( 'Mastercard', 'woocommerce-rave' ),
			],
		];

		if ( 'USD' === get_woocommerce_currency() ) {
			$icons_src['discover'] = [
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/discover.svg',
				'alt' => _x( 'Discover', 'Name of credit card', 'woocommerce-rave' ),
			];
			$icons_src['jcb']      = [
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/jcb.svg',
				'alt' => __( 'JCB', 'woocommerce-rave' ),
			];
			$icons_src['diners']   = [
				'src' => dirname( FLW_WC_PLUGIN_FILE ) . '/assets/img/diners.svg',
				'alt' => __( 'Diners', 'woocommerce-rave' ),
			];
		}
		return $icons_src;
	}
}
