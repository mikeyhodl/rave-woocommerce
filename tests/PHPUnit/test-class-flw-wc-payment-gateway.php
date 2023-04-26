<?php
/**
 * Tests for the FLW_WC_Payment_Gateway class.
 *
 * @package Flutterwave\WooCommerce\Tests\phpunit
 */

namespace phpunit;

class FLW_WC_Payment_Gateway_Test extends WP_Unit_Test_Case {
	/**
	 * Flutterwave Gateway under test.
	 *
	 * @var \FLW_WC_Payment_Gateway
	 */
	private \FLW_WC_Payment_Gateway $gateway;

	private string $expected_gateway_name = 'rave';

	/**
	 * Sets up things all tests need.
	 */
	public function set_up() {
		parent::set_up();

		$this->gateway         = new \FLW_WC_Payment_Gateway();

	}

	/**
	 * Tests the gateway ID.
	 */
	public function test_id() {
		$this->assertEquals( $this->expected_gateway_name, $this->gateway->id );
	}

	/**
	 * Tests the gateway has fields.
	 */
	public function test_has_fields() {
		$this->assertTrue( $this->gateway->has_fields );
	}

	/**
	 * Tests the gateway supports.
	 */
	public function test_supports() {

	}

	/**
	 * Tests the gateway is available.
	 */
	public function test_is_available() {

	}

	/**
	 * Tests the gateway payment fields.
	 */
	public function test_payment_fields() {

	}

	/**
	 * Tests the gateway process payment.
	 */
	public function test_process_payment() {

	}

	/**
	 * Tests the gateway payment complete.
	 */

	public function test_payment_complete() {

	}

	/**
	 * Tests the gateway admin options.
	 */

	public function test_admin_options() {

	}

	/**
	 * Tests the gateway payment scripts.
	 */

	public function test_payment_scripts() {


	}

	/**
	 * Tests the gateway validate order.
	 */

	public function test_validate_order() {

	}
}