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
	 * @var int - count of requests made.
	 */
	protected static int $count = 0;

	/**
	 * @var string - redirect url where the user will be redirected to after payment.
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
	 * @param \WC_Order $order Order object.
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
				'description' => sprintf( __( 'Payment for order %s', 'flutterwave-woo' ), $order->get_order_number() ), // phpcs:ignore
				// phpcs:ignore
//				'logo' => $this->gateway->get_option('logo'),
			),
		);
	}
}
