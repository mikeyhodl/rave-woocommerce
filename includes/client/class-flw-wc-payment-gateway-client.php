<?php
/**
 * The client-specific functionality of the plugin.
 *
 * @link       https://flutterwave.com
 * @since      2.3.2
 *
 * @package    FLW_WC_Payment_Gateway
 * @subpackage FLW_WC_Payment_Gateway/includes
 */

declare(strict_types=1);

namespace Flutterwave\WooCommerce\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prevent direct access to this class.
defined( 'FLUTTERWAVEACCESS' ) || exit( 'No direct script access allowed' );

require_once FLW_WC_DIR_PATH . 'includes/client/class-flw-wc-payment-gateway-sdk.php';

define( 'FLW_WC_PAYMENT_GATEWAY_URL', plugin_dir_url( FLW_WC_PLUGIN_FILE ) );
define( 'FLW_WC_PAYMENT_GATEWAY_VERSION', 'v3' );



/**
 * Class FLW_WC_Payment_Gateway_Client
 *
 * @package Flutterwave\WooCommerce
 */
class FLW_WC_Payment_Gateway_Client {

	const API_VERSION  = 'v3';
	const API_BASE_URL = 'https://api.flutterwave.com';
	/**
	 * @var string
	 */
	private string $secret_key;
	/**
	 * @var \WC_Logger_Interface
	 */
	private \WC_Logger_Interface $logger;
	/**
	 * @var array|string[]
	 */
	private array $headers;

	/**
	 * @param string $secret_key - The secret key for the merchant.
	 * @param bool   $is_logging_enabled - Whether to log the request or not.
	 */
	public function __construct(
		string $secret_key,
		bool $is_logging_enabled = false
	) {
		$this->logger     = \wc_get_logger();
		$this->secret_key = $secret_key;
		$this->setup();
		if ( $is_logging_enabled ) {
			$this->logger->log( 'info', 'Logging enabled', array( 'source' => 'flutterwave' ) );
		}
	}

	/**
	 * @return void
	 */
	private function setup() {
		$this->headers = [
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $this->secret_key,
		];
	}

	/**
	 * @param string     $url - The url to make the request to.
	 * @param string     $method - The method to use for the request.
	 * @param array|null $body - The body of the request.
	 *
	 * @return array|\WP_Error
	 */
	public function request( $url, string $method = 'GET', $body = null ) {
		$body = wp_json_encode( $body, JSON_UNESCAPED_SLASHES );

		$args = [
			'method'  => $method,
			'headers' => $this->headers,
		];

		if ( 'GET' !== $method ) {
			$args['body'] = $body;
		}

		return wp_safe_remote_request( $url, $args ); // return the body of the response json.
	}

	/**
	 * @param $response
	 *
	 * @return void
	 */
	public function handle_error( $response ) {
		if ( is_wp_error( $response ) ) {
			$this->logger->log( 'error', $response->get_error_message(), array( 'source' => 'flutterwave' ) );
			wc_add_notice( 'Unable to connect to Flutterwave. Please Try Again', 'error' );
		}
	}
}
