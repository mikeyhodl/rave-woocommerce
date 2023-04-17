<?php
/**
 * The client-specific functionality of the plugin.
 *
 * @link       https://flutterwave.com
 * @since      2.3.2
 * @class      FLW_WC_Payment_Gateway_Sdk
 * @package    Flutterwave\WooCommerce\Client
 * @subpackage FLW_WC_Payment_Gateway/includes
 */

namespace Flutterwave\WooCommerce\Client;

use Flutterwave\WooCommerce\Contracts\FLW_WC_Payment_Gateway_Event_Handler_Interface;
use FLW_WC_Payment_Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prevent direct access to this class.
defined( 'FLUTTERWAVEACCESS' ) || exit( 'No direct script access allowed' );

require_once __DIR__ . '/class-flw-wc-payment-gateway-client.php';

/**
 * Class FLW_WC_Payment_Gateway_Sdk file.
 *
 * @package Flutterwave\WooCommerce\Client
 */
final class FLW_WC_Payment_Gateway_Sdk {
	/**
	 * @var int
	 */
	protected int $requery_count = 0;
	/**
	 * @var FLW_WC_Payment_Gateway_Client
	 */
	public FLW_WC_Payment_Gateway_Client $client;
	/**
	 * Event handler
	 *
	 * @var FLW_WC_Payment_Gateway_Event_Handler_Interface
	 */
	private FLW_WC_Payment_Gateway_Event_Handler_Interface $event_handler;
	/**
	 * @var array
	 */
	private array $data;
	/**
	 * @var string
	 */
	public static string $standard_inline_endpoint = 'https://api.flutterwave.com/v3/payments';
	/**
	 * @var string
	 */
	private string $checkout_url = 'https://checkout.flutterwave.com/v3.js';
	/**
	 * @var mixed
	 */
	private \WC_Logger $logger;
	/**
	 * @var FLW_WC_Payment_Gateway
	 */
	private FLW_WC_Payment_Gateway $gateway; //phpcs:ignore.

	/**
	 * FLW_WC_Payment_Gateway_Sdk constructor.
	 *
	 * @param $secret_key
	 * @param bool       $log_enabled
	 */
	public function __construct( $secret_key, bool $log_enabled = false ) {
		$this->client = new FLW_WC_Payment_Gateway_Client( $secret_key, $log_enabled );
		// if($log_enabled) {
		// $this->logger = wc_get_logger();
		// }
		$this->logger = wc_get_logger();
		return $this;
	}

	/**
	 * @return null
	 */
	public function __clone() {
		return null;
	}

	/**
	 * @return FLW_WC_Payment_Gateway_Client
	 */
	public function get_client(): FLW_WC_Payment_Gateway_Client {
		return $this->client;
	}

	/**
	 * @param FLW_WC_Payment_Gateway_Event_Handler_Interface $event_handler // phpcs:ignore.
	 *
	 * @return FLW_WC_Payment_Gateway_Sdk
	 */
	public function set_event_handler( FLW_WC_Payment_Gateway_Event_Handler_Interface $event_handler ): FLW_WC_Payment_Gateway_Sdk {
		$this->event_handler = $event_handler;

		return $this;
	}

	/**
	 * @param array $clean_data  This is the data that will be sent to the server.
	 * @param int   $order_id  The order id.
	 *
	 * @return string
	 */
	private function prepare_html( array $clean_data, int $order_id ): string {
		// TODO: enqueue inline checkout script.

		$html_array = [
			'<html lang="en">',
			'<body>',
			'   <img src="' . esc_url( plugins_url( 'sdk/ajax-loader.gif', FLW_WC_PLUGIN_FILE ) ) . '" />',
			'   <script type="text/javascript" src="' . esc_url( $this->checkout_url ) . '"></script>',
			'	<script>',
			'       var isFlutterwaveCompleted = false;',
			'       document.addEventListener("DOMContentLoaded", function(event) {',
			'           FlutterwaveCheckout("", function(event) {',
			'               public_key: "' . $clean_data['public_key'] . '",',
			'               tx_ref: "' . $clean_data['tx_ref'] . '",',
			'               amount: ' . $clean_data['amount'] . ',',
			'               currency: "' . $clean_data['currency'] . '",',
			'               payment_options: "' . $clean_data['payment_options'] . '",',
			'               redirect_url: "' . $clean_data['redirect_url'] . '",',
			'               customer: {',
			'                   email: "' . $clean_data['email'] . '",',
			'                   phone_number: "' . $clean_data['phone_number'] . '",',
			'                   name: "' . $clean_data['first_name'] . ' ' . $clean_data['last_name'] . '",',
			'               },',
			'               meta: {',
			'                   consumer_id: ' . $clean_data['consumer_id'] . ',',
			'                   ip: "' . $clean_data['ip_address'] . '",',
			'               },',
			'               callback: function (data) {',
			'                   console.log(data);',
			'               },',
			'               onclose: function() {',
			'                   window.location = "?cancelled=cancelled&order_id=' . $order_id . '";',
			'               },',
			'               customizations: {',
			'                   title: "' . esc_html_e( $clean_data['title'], 'flw-payments' ) . '",',
			'                   description: "' . esc_html_e( $clean_data['description'], 'flw-payments' ) . '",',
			'                   logo: "' . $clean_data['logo'] . '",',
			'               },',
			'           });',
			'       });',
			'	</script>',
			'</body>',
			'</html>',
		];

		return implode( '', $html_array );
	}

	/**
	 * @param array $data This is the data to be sent to the payment gateway.
	 * @param int   $order_id This is the order id.
	 *
	 * @return false|string
	 */
	public function render_modal( array $data, $order_id ) {
		$clean_data = $data;
		$html       = $this->prepare_html( $clean_data, $order_id );
		$this->logger->notice( 'Loading Payment Modal for order:' . $order_id );
		echo wp_kses_post( $html );
		return wp_json_encode( $clean_data );
	}

	/**
	 * Transaction Reference.
	 *
	 * @param string $tx_ref This is the unique reference for the transaction.
	 *
	 * @return void
	 */
	public function requery_transaction( string $tx_ref ) {
		$this->requery_count ++;
		$this->logger->notice( 'Requerying Transaction....' . $tx_ref );

		if ( isset( $this->event_handler ) ) {
			$this->event_handler->on_requery( $tx_ref );
		}

		$url = $this->client::API_BASE_URL . '/' . $this->client::API_VERSION . '/transactions/verify_by_reference?tx_ref=' . $tx_ref;

		$response = $this->client->request( $url );

		if ( ! is_wp_error( $response ) ) {
			$response = json_decode( $response['body'] );
			if ( 'success' === $response->status ) {
				$this->logger->notice( 'Transaction Requeried Successfully' );

				if ( isset( $this->event_handler ) ) {
					$this->event_handler->on_successful( $response->data );
				}
			} else {
				$this->logger->notice( 'Transaction Requeried Failed' );
				$this->event_handler->on_failure( $response );
			}
		} else {
			// TODO: handle request errors.
			$this->logger->notice( 'Transaction Requeried Failed. Awaiting Webhook Verification...' );
		}

	}

	/**
	 * @param string $event_type The event type.
	 * @param object $event_data The event data.
	 */
	public function webhook_verify( string $event_type, object $event_data ) {
		$this->logger->notice( 'Webhook Verification Started' );
		$this->logger->notice( 'Event Type: ' . $event_type );

		$event_type = strtolower( $event_type );

		if ( isset( $this->event_handler ) ) {
			$this->event_handler->on_webhook( $event_type, $event_data );
		}

		// phpcs:ignore     $event_type = str_replace( '.', '_', $event_type );

		if ( method_exists( $this, 'requery_transaction' ) ) {
			$this->requery_transaction( $event_data->tx_ref );
		} else {
			$this->logger->notice( 'Webhook Verification Failed' );
		}
	}
}
