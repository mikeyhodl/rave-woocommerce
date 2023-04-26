<?php
/**
 * Tests for the FLW_WC_Payment_Gateway class.
 *
 * @package Flutterwave\WooCommerce\Tests\phpunit
 */

/**
 * Tests for the FLW_WC_Payment_Gateway class.
 */
class Test_FLW_WC_Payment_Gateway extends \Yoast\PHPUnitPolyfills\TestCases\TestCase {
	/**
	 * Flutterwave Gateway under test.
	 *
	 * @var \FLW_WC_Payment_Gateway
	 */
	private \FLW_WC_Payment_Gateway $gateway;

	/**
	 * Expected gateway name.
	 *
	 * @var string
	 */
	private string $expected_gateway_name = 'rave';

	/**
	 * Sets up things all tests need.
	 */
	public function set_up() {
		parent::set_up();

		$this->gateway = new \FLW_WC_Payment_Gateway();
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
		$this->assertTrue( $this->gateway->supports( 'products' ) );
	}
}
