<?php
/**
 * The client-specific functionality of the plugin.
 *
 * @link       https://flutterwave.com
 * @since      2.3.2
 * @class      FLW_WC_Payment_Gateway_Request
 * @package    Flutterwave\WooCommerce\Client
 * @subpackage FLW_WC_Payment_Gateway/includes
 */

namespace Flutterwave\WooCommerce\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FLW_WC_Gateway_Request file.
 *
 * @package Flutterwave\WooCommerce\Client
 */
final class FLW_WC_Payment_Gateway_Request {

	/**
	 * @var int
	 */
	protected static int $count = 0;

	/**
	 * @var string
	 */
	protected string $notify_url;
	/**
	 * Endpoint request for
	 *
	 * @var string
	 */
	protected string $base_url = 'https://api.flutterwave.com/v3/';

	/**
	 *  Pointer to gateway making the request.
	 */
	public function __construct() {
		 $this->notify_url = WC()->api_request_url( 'FLW_WC_Payment_Gateway' );
	}

	/**
	 * This method prepares the payload for the request
	 *
	 * @param \WC_Order $order
	 *
	 * @return array
	 */
	public function get_prepared_payload( \WC_Order $order ): array {
		$order_id = $order->get_id();
		$txnref   = 'WOOC_' . $order_id . '_' . time();
		return array(
			'amount'          => $order->get_total(),
			'tx_ref'          => $txnref,
			'currency'        => $order->get_currency(),
			'payment_options' => 'card',
			'redirect_url'    => $this->notify_url . '?order_id=' . $order_id,
			'customer'        => array(
				'email'        => $order->get_billing_email(),
				'phone_number' => $order->get_billing_phone(),
				'name'         => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			),
			'meta'            => array(
				'consumer_id' => $order->get_customer_id(),
				'ip_address'  => $order->get_customer_ip_address(),
				'user-agent'  => $order->get_customer_user_agent(),
			),
			'customizations'  => array(
				'title'       => get_bloginfo( 'name' ),
				'description' => sprintf( __( 'Payment for order %s', 'flw-payments' ), $order->get_order_number() ),
				// phpcs:ignore
//				'logo' => $this->gateway->get_option('logo'),
			),
		);
	}

	/**
	 * @param $paylaod
	 *
	 * @return void
	 */
	private function get_flutterwave_standard_url( $paylaod ) {
		$response = $this->client->request( $this->endpoint, 'POST', $paylaod );

		// TODO: Test on null response.
		if ( ! is_null( $response ) && isset( $response->data->link ) ) {
			return $response->data->link;
		}

		if ( is_null( $response ) && self::$count < 3 ) {
			self::$count++;
			$this->get_flutterwave_standard_url( $paylaod );
		}
		self::$count = 0;
		wc_add_notice( 'Unable to connect to Flutterwave Standard Service .', 'error' );
	}
}
